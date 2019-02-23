<?php

Imports("System.Diagnostics.StackTrace");
Imports("System.Linq.Enumerable");
Imports("Microsoft.VisualBasic.Strings");
Imports("MVC.View.foreach");
Imports("MVC.View.inline");
Imports("MVC.View.volist");
Imports("Debugger.Ubench.Ubench");

# 2019-02-23
#
# 视图模块应该支持两种类型的模板文件
#
# 1. *.html 采用file_get_content + echo的方式进行输出，采用字符串替换进行数据填充
# 2. *.php/*.phtml 采用include方式加载，采用变量进行数据填充

/**
 * html user interface view handler
 * 
 * 如果需要显示调试器输出窗口内容，则还需要你的WebApp继承实现``MVC.controller``模块
 * 
 * @author xieguigang
*/
class View {
	
	/**
	 * Generate a html ``<script>`` tag for write json data.
	 * 
	 * @param mixed $data any data to generate json string
	 * @param string $id The id attribute of the script tag.
	 * @param boolean Encoding the generate json text into base64 string? By default is no.
	 * 
	 * @return string script tag data with a given id. 
	*/
	public static function ScriptTagData($id, $data, $base64 = false) {
		$data = json_encode($data);
		$data = $base64 ? base64_encode($data) : $data;

		return "<script id='$id' type='application/json'>$data</script>";
	}

	/**
	 * 从html文件夹之中读取和当前函数同名的文件并显示出来
	 * 
	 * @param array $vars 需要在页面上进行显示的文本变量的值的键值对集合
	 * @param string $lang 页面的语言文件，默认为中文语言，这个参数默认不需要进行指定，
	 *                     渲染引擎可以自动从url参数之中读取语言配置项，如果指定了这个参数的话，
	 *                     系统会强制使用这个语言项进行页面显示
	*/
	public static function Display($vars = NULL, $lang = null) {
		View::Show(self::getViewFileAuto(StackTrace::GetCallerMethodName()), $vars, $lang);
	}

	/** 
	 * 如果控制器配置了view参数，则返回自定义路径，反之返回自动构建的查找路径
	 * 
	 * @param string $name The controller name.
	*/
	private static function getViewFileAuto($name) {
		$wwwroot = DotNetRegistry::GetMVCViewDocumentRoot();
		
		if (strpos($wwwroot, "/") == 0) {
			# 是一个绝对路径，则直接使用
			# do nothing
		} else {
			# 是一个相对路径，则需要和 SITE_PATH拼接一下
			$wwwroot = trim($wwwroot, ".");
			$wwwroot = SITE_PATH . "/" . $wwwroot;
		}

		# 假若直接放在和index.php相同文件夹之下，那么apache服务器会优先读取
		# index.html这个文件的，这就导致无法正确的通过这个框架来启动Web程序了
		# 所以html文件规定放在html文件夹之中
		$wwwroot = str_replace("\\", "/", $wwwroot);

		# 优先使用用户单独为控制器定义的路径
		if (!empty(dotnet::$controller)) {
			$path = dotnet::$controller->getView();
			
			# 可能是绝对路径，也可能是相对路径，需要处理一下
			if (strpos($path, "/") == 0) {
				# 是一个绝对路径
			} else {
				# 是一个相对路径，则需要进行一些额外的处理
				$path = trim($path, ".");
				$path = "$wwwroot/$path";
			}

			$path = realpath($path);
		} else {
			$path = self::assertFileType($wwwroot, $name);
		}

		if ($path === false) {
			self::missingTemplate($wwwroot, $name);
		} else {
			console::log("Current workspace: " . getcwd());
			console::log("HTML document path is: $path");
			console::log("View name='$name'");
			console::log("View wwwroot='$wwwroot'");
		}

		return $path;
	}
	
	/** 
	 * 这个函数返回的路径已经是realpath了
	 * 
	 * @return string 如果文件不存在的话，则返回false
	*/
	private static function assertFileType($wwwroot, $name) {
		$viewTypes = ["html", "php", "phtml"];

		foreach($viewTypes as $type) {
			$path = realpath("$wwwroot/$name.$type");

			if (!(empty($path) || $path == false)) {
				return $path;
			}
		}

		return false;
	}

	private static function missingTemplate($wwwroot, $name) {
		# 文件丢失？
		# 或者当前的网站在文件夹下，而不是根文件夹
		$msg = "The view template file {" . "$wwwroot/$name.html" . "} not exists 
			on the server's filesystem, please consider defined: 
			<ul>
				<li>1. a valid <code>SITE_PATH</code> constant or </li>
				<li>2. a valid <code>MVC_VIEW_ROOT</code> configuration</li>
			</ul>
			before we load php.NET framework; Or check the file name of the 
			html view is correct or not.
			<br />
			<br />
			Current value:
			<ul>
				<li>SITE_PATH = " . SITE_PATH . "</li>
				<li>wwwroot = $wwwroot</li>
			</ul>";
		
		dotnet::PageNotFound($msg);
	}

	/**
	 * 显示指定的文件路径的html文本的内容
	 * 
	 * @param string $path html页面模板文件的文件路径，可以为html或者php文件，这两种文件会以不同的方式进行处理
	 * @param array $vars 需要进行填充的变量列表
	 * @param string $lang 语言配置值，一般不需要指定，框架会根据url参数配置自动加载
	*/
	public static function Show($path, $vars = NULL, $lang = null, $suppressDebug = false) {
		debugView::LogEvent("[Begin] Render html view");

		$bench = new \Ubench();
		# 2018-08-09 如果在这里使用run，以如下的方式进行调用lambda函数的话
		# 堆栈信息将会无法正常的产生，所以在这里使用普通的代码调用形式

		/*
			$html  = $bench->run(function() use ($path, $vars, $lang, $suppressDebug) {
				return self::Load($path, $vars, $lang, $suppressDebug);
			});
		*/

		# 这个普通的函数调用方式所得到的堆栈信息是正常的
		$bench->start();
		$html = self::Load($path, $vars, $lang, $suppressDebug);
		$bench->end();

		debugView::AddItem("benchmark.template", $bench->getTime(true));
		debugView::LogEvent("[Finish] Render html view");

		echo $html;
	}
	
	/**
	 * 获取目标html文档梭对应的缓存文件的文件路径
	*/
	private static function getCachePath($path) {
		// temp/{yyymmmdd}/viewName
		$version = filemtime($path);
		$temp    = dotnet::getMyTempDirectory();
		# 因为缓存的路径和主html文件的修改时间相关，所以如果只是文档碎片被更新了
		# 会因为主html文件没有被修改的原因而没有更新cache
		# 在这里使用app version来更新缓存
		$appVer  = DotNetRegistry::Read("APP_VERSION", "0.0.0");
		$file    = basename($path);
		# 2018-09-18 从下面的代码之中可以看见，因为缓存页面是和用户请求有关的
		# 所以没有办法为每一个视图页面生成缓存页面
		$path    = md5($_SERVER["REQUEST_URI"]);
		$cache   = "$temp/$appVer/$file/$version/$path.html";

		return $cache;
	}

	/**
	 * 加载指定路径的html文档并对其中的占位符利用vars字典进行填充
	 * 这个函数还会额外的处理includes关系
	 * 
	 * @param string $path 视图模板的HTML文本文件的路径
	 * @param array $vars 需要进行填充的变量列表
	 * @param string $lang 语言
	 * @param boolean $suppressDebug 是否禁用调试器输出
	*/
	public static function Load($path, $vars = NULL, $lang = null, $suppressDebug = false) {
		global $_DOC;
		
		if (Strings::Empty($lang)) {
			$lang = dotnet::GetLanguageConfig()["lang"];	
		}			
		
		$lang = self::LoadLanguage($path, $lang, NULL);
		$vars = self::unionPhpDocs($_DOC, $vars);

		if (file_exists($path)) {
			$html = self::loadTemplate($path, $lang);
		} else {
			# 给出文件不存在的警告信息
			return "HTML document view <strong>&lt;$path></strong> could not be found!";
		}
		
		if (!$suppressDebug) {
			debugView::DebugVars($vars);
		}

		return View::InterpolateTemplate($html, $vars);
	}

	/**
	 * 将php注释写入到html的meta信息之中
	 * 
	 * 在这里主要是使用php的注释文档进行填充html的head部分所定义的meta数据的信息
	 * 如果需要进行填充的话，需要html文档之中有title，description，authors变量
	 * title变量是单独的<title>标签标记，description和authors则写入在<meta>标签里面
	 * 
	 * @param DocComment $_DOC
	 * @param array $vars
	 * 
	 * @return array
	*/
	private static function unionPhpDocs($_DOC, $vars) {
		if (empty($_DOC)) {
			return $vars;
		}

		if (!empty($vars) && count($vars) > 0) {
			if (!array_key_exists("title", $vars)) {
				$vars["title"] = $_DOC->title;
			}
			if (!array_key_exists("description", $vars)) {
				$vars["description"] = $_DOC->summary;
			}
			if (!array_key_exists("authors", $vars) && !empty($_DOC->authors)) {
				$vars["authors"] = join(", ", $_DOC->authors);
			}
		} else {
			# $vars是空的
			$vars = [
				"title"       => $_DOC->title, 
				"description" => $_DOC->summary,
				"authors"     => Strings::Join($_DOC->authors, ", ")
			]; 
		}

		return $vars;
	}

	/**
	 * Load or read from cache for get html template
	 * 
	 * @param string $path The file path of the html template file
	 * @param array $language 这个函数除了加载模板文件，还会将语言文本数据渲染到模板上面
	 * 
	 * @return string
	*/
	private static function loadTemplate($path, $language) {
		$usingCache = DotNetRegistry::Read("CACHE", false);

		if ($usingCache && !Strings::Empty($path)) {			
			# 在配置文件之中开启了缓存选项
			$cache = self::getCachePath($path);			

			# 在调试模式下总是不使用cache
			# 为了将cache的信息也输出到调试终端，在这里设置条件为调试模式或者缓存文件
			# 不存在都会进行缓存的生成
			if (APP_DEBUG || !file_exists($cache)) {
				# 当缓存文件不存在的时候，生成缓存，然后返回
				
				# 将html片段合并为一个完整的html文档
				# 得到了完整的html模板
				$html      = file_get_contents($path);
				$cachePage = View::interpolate_includes($html, $path);
				$cachePage = View::valueAssign($cachePage, $language);
				$cacheDir  = dirname($cache);
				
				if (!file_exists($cacheDir)) {
					mkdir($cacheDir, 0777, true);
				}				
				file_put_contents($cache, $cachePage);

				debugView::LogEvent("HTML view cache created!");
			} else {
				debugView::LogEvent("HTML view cache hits!");
			}

			$cache = realpath($cache);			
			$html  = file_get_contents($cache);

			debugView::LogEvent("Cache=$cache");
		} else {
			$cache = 'disabled';
			# 不使用缓存，需要进行页面模板的拼接渲染
			$html  = file_get_contents($path);
			$html  = View::interpolate_includes($html, $path);
			$html  = View::valueAssign($html, $language);

			debugView::LogEvent("Cache=disabled");
		}	

		debugView::AddItem("cache.path", $cache);
		
		return $html;
	}

	/**
	 * 加载html视图页面的语言数据
	 * 
	 * @param string $lang 语言的标识符，例如:enUS, zhCN
	 * @param string $path html文件的文件路径
	*/
	private static function LoadLanguage($path, $lang, $vars = NULL) {
		$name = pathinfo($path);
		$name = $name['filename'];
		$lang = dirname($path) . "/$name.$lang.php";		

		if (!file_exists($lang)) { 
			return $vars;
		}

		$lang = include_once $lang;
		
		# 2018-4-26
		# PHP Fatal error:  Can't use function return value in write context
		# 在这里需要使用empty函数来判断是否是空值，不可以直接使用！操作符
		#
		if (empty($lang)) {
			return $vars;
		}

		if ($vars && count($vars) > 0) {
			# 用户在Controller里面所定义的vars的优先级要高于lang之中的定义值
			# 所以在这里会覆盖掉lang之中的值
			foreach($vars as $key => $value) {
				$lang[$key] = $value;
			}

			$vars = $lang;
		} else {
			# vars是空的，则直接用lang替换掉vars
			$vars = $lang;
		}

		return $vars;
	}

	/**
	 * @var array
	*/
	private static $join = [];

	/**
	 * 这个函数的调用会使框架的缓存机制失效
	 * 
	 * @param string $name 变量名称，如果这个参数是``*``的话，表示将value数组之中的所有对象
	 *                     都推送到输出页面上进行渲染
	 * @param mixed $value 变量的值，如果name是字符串``*``的话，这个参数必须是一个字典数组
	*/
	public static function Push($name, $value) {
		if ($name == "*") {

			# value 必须是一个数组
			if (!is_array($value)) {
				throw new error("Value must be an array when variable name is ``*``!");
			}

			foreach($value as $key => $val) {
				self::$join[$key] = $val;
			}
		} else {
			self::$join[$name] = $value;
		}		
	}

	/**
	 * Create user html document based on the html template 
	 * and the given configuration data.
	*/
	public static function InterpolateTemplate($html, $vars) {		
		# 没有需要进行设置的变量字符串，则直接在这里返回html文件
		if (!$vars && !self::$join) {
			# 假设在html文档里面总是会存在url简写的，
			# 则在这里需要进行替换处理		
			return Router::AssignController($html);
		} else {
			if (!$vars) {
				$vars = self::$join;
			} else if (!self::$join) {
				// do nothing
			} else {
				$vars = array_merge($vars, self::$join);
			}

			return View::Assign($html, $vars);
		}
	}

	/**
	 * 这个函数将html片段进行拼接，得到完整的html文档，函数需要使用文档所在的路径来
	 * 获取文档碎片的引用文件位置
	 * 
	 * @param path html模版文件的文件位置
	*/
	private static function interpolate_includes($html, $path) {
		if (!$path) {
			return $html;
		}

		$pattern = "#[$]\{.+?\}#";
		$dirName = dirname($path);
		
		if (preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER) > 0) { 
			$matches = $matches[0];
			
			# ${includes/head.html}
			foreach ($matches as $s) { 
				$path = Strings::Mid($s, 3, strlen($s) - 3);
				$path = realpath("$dirName/$path");

				if (APP_DEBUG && !empty($path)) {
					console::log("Found template segment: $path");
				}

				# 读取获得到了文档的碎片
				# 可能在当前的文档碎片里面还会存在依赖
				# 则继续递归下去
				$include = file_get_contents($path);
				$include = self::interpolate_includes($include, $path);

				$html = Strings::Replace($html, $s, $include);				
			}
		}
		
		return $html;
	}

	/**
	 * 进行简单的字符串替换操作
	 * 
	 * @param string $html 模板html文本
	 * @param array $vars 需要进行渲染的数据
	*/
	private static function valueAssign($html, $vars) {
		# 在这里需要按照键名称长度倒叙排序，防止出现可能的错误替换		
		// $vars = Enumerable::OrderByKeyDescending($vars, function($key) {
		// 		return strlen($key);
		// });		
		if (empty($vars)) {
			return $html;
		}

		# 变量的名称$name的值为名称字符串，例如 id
		# 而在html文件之中需要进行申明的形式则是 {$id}
		# 需要在这里需要进行额外的字符串链接操作才能够正常的替换掉目标		
		foreach ($vars as $name => $value) {
			$name = '{$' . $name . '}';

			if (is_array($value)) {
				# 这个可能是后面的foreach循环的数据源
				# 不做任何处理？？
				#
				# 给出警告
				console::log("[$name] is an array, will render with <code>foreach</code> or <code>volist</code>.");
			} else {
				$html = Strings::Replace($html, $name, $value);
			}
		}

		return $html;
	}

	/**
	 * html页面之上存在有额外的需要进行设置的字符串变量参数
	 * 在这里进行字符串替换操作
	 * 
	 * @param string $html 模板html文本
	 * @param array $vars 需要进行渲染的数据
	*/
	public static function Assign($html, $vars) {
		$html = self::valueAssign($html, $vars);
		# 处理数组循环变量，根据模板生成表格或者列表
		$html = MVC\Views\ForEachView::InterpolateTemplate($html, $vars);
		# 可以使用foreach标签的同时，也支持部分的thinkphp的volist标签语法
		$html = MVC\Views\volistViews::InterpolateTemplate($html, $vars);
		# 处理内联的表达式，例如if条件显示
		$html = MVC\Views\InlineView::RenderInlineTemplate($html);
		# 最后将完整的页面里面的url简写按照路由规则还原
		$html = Router::AssignController($html);

		return $html;
	}
}
?>