<?php

Imports("System.Diagnostics.StackTrace");
Imports("System.Linq.Enumerable");
Imports("Microsoft.VisualBasic.Strings");
Imports("MVC.View.foreach");
Imports("MVC.View.inline");

/**
 * html user interface view handler
*/
class View {
	
	/**
	 * 从html文件夹之中读取和当前函数同名的文件并显示出来
	 * 
	 * @param array $vars 需要在页面上进行显示的文本变量的值的键值对集合
	 * @param string $lang 页面的语言文件，默认为中文语言
	*/
	public static function Display($vars = NULL, $lang = "zhCN") {

		$name    = StackTrace::GetCallerMethodName();
		$wwwroot = DotNetRegistry::GetMVCViewDocumentRoot();

		# 假若直接放在和index.php相同文件夹之下，那么apache服务器会优先读取
		# index.html这个文件的，这就导致无法正确的通过这个框架来启动Web程序了
		# 所以html文件规定放在html文件夹之中
		$path = "$wwwroot/$name.html";

		if (APP_DEBUG) {
			echo $path . "\n\n";
		}

		View::Show($path, $vars, $lang);
	}
	
	/**
	 * 显示指定的文件路径的html文本的内容
	*/
	public static function Show($path, $vars = NULL, $lang = "zhCN") {
		echo self::Load($path, $vars, $lang);
	}
	
	/**
	 * 加载指定路径的html文档并对其中的占位符利用vars字典进行填充
	 * 这个函数还会额外的处理includes关系
	*/
	public static function Load($path, $vars = NULL, $lang = "zhCN") {
		$vars = self::LoadLanguage($path, $lang, $vars);
		$html = file_get_contents($path);
		return View::InterpolateTemplate($html, $vars, $path);
	}

	/**
	 * 加载html视图页面的语言数据
	 * 
	 * @param string $lang 语言的标识符，例如:enUS, zhCN
	 * @param string $path html文件的文件路径
	*/
	private static function LoadLanguage($path, $lang, $vars) {
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

	private static $join = [];

	public static function Push($name, $value) {
		if ($name == "*") {
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
	public static function InterpolateTemplate($html, $vars, $path = NULL) {
		# 将html片段合并为一个完整的html文档
		$html = View::interpolate_includes($html, $path);	
		
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
				$path    = Strings::Mid($s, 3, strlen($s) - 3);				
				$path    = realpath("$dirName/$path");			

				# echo $path . "\n\n";

				# 读取获得到了文档的碎片
				# 可能在当前的文档碎片里面还会存在依赖
				# 则继续递归下去
				$include = file_get_contents($path);
				$include = self::interpolate_includes($include, $path);

				$html    = Strings::Replace($html, $s, $include);				
			}
		}
		
		return $html;
	}

	/**
	 * html页面之上存在有额外的需要进行设置的字符串变量参数
	 * 在这里进行字符串替换操作
	*/
	public static function Assign($html, $vars) {
		
		# 在这里需要按照键名称长度倒叙排序，防止出现可能的错误替换		
		$vars = Enumerable::OrderByKeyDescending($vars, function($key) {
			return strlen($key);
		});		
		
		# 变量的名称$name的值为名称字符串，例如 id
		# 而在html文件之中需要进行申明的形式则是 {$id}
		# 需要在这里需要进行额外的字符串链接操作才能够正常的替换掉目标		
		foreach ($vars as $name => $value) {
			$name = '{$' . $name . '}';

			if (is_array($value)) {
				# 这个可能是后面的foreach循环的数据源
				# 不做任何处理？？
				#
				# DO NOTHING
			} else {
				$html = Strings::Replace($html, $name, $value);
			}			
		}	
		
		# 处理数组循环变量，根据模板生成表格或者列表
		$html = MVC\Views\ForEachView::InterpolateTemplate($html, $vars);
		# 处理内联的表达式，例如if条件显示
		$html = MVC\Views\InlineView::RenderInlineTemplate($html);
		# 最后将完整的页面里面的url简写按照路由规则还原
		$html = Router::AssignController($html);

		return $html;
	}
}
?>