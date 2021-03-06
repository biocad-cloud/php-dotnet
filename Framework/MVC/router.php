<?php

imports("Microsoft.VisualBasic.Strings");
imports("Microsoft.VisualBasic.Extensions.StringHelpers");
imports("php.URL");

/**
 * REST api handler
 *
 * 这个路由器模块的功能主要是解析所请求的url字符串，然后转换为对Controller的反射调用
*/
class Router {
		
	/**
	 * 进行自动处理请求主要是用户通过实例化一个class之后
	 * 这个函数会对url的解析结果从class实例对象之中匹配出
     * 相同的函数名然后进行调用	 
	 *
	 * @param object $app 控制器对象实例
	 * @param array $request CLI后台服务模块所需要的，使用这个参数来模拟$_GET输入
	*/
	public static function HandleRequest($app, $request = NULL) {
		$exist_app = method_exists($app, $page = self::getApp($request));
	
		# 2019-05-13 当使用empty判断的时候，假设$request是[]空数组，则empty的结果和null判断的结果一致，会产生bug
		# 所以在这里应该是使用is_array来进行判断
		if (!is_array($request)) {
			if ($exist_app) {
				exit($app->{$page}());
			} else {
				$message = "Web app `<strong>$page</strong>` is not available in controller!";
				dotnet::PageNotFound($message);
			}
		} else {
			if ($exist_app) {
				return $app->{$page}($request);
			} else {
				return 404;
			}
		}
	}

	/**
	 * 获取当前所访问的应用程序的名称
	 * 
	 * ```
	 * XXXX.php?app=xxxxx
	 * ```
	 * 
	 * @param array $request The url request parsed query data, by default is using ``$_GET``
	 *    if this request array is nothing.
	 * 
	 * @return string Web app name.
	*/
	public static function getApp($request = NULL) {
		$argv = (!is_array($request)) ? $_GET : $request;

		# 20191226
		# 对于调试器api，其控制器变量为api
		# 如果用户的web app模块之中定义了index控制器函数
		# 则会因为没有app变量而返回index字符串
		# 导致请求被分配到了index控制器之上
		# 所以为了兼容调试器的请求，在下面应该添加一个api控制器变量的判断
		if (empty($argv) || count($argv) == 0) {
			# index.html as default
			$page = "index";
		} else if (!array_key_exists("app", $argv)) {
			# probably is a http request of debugger api calls
			if (array_key_exists("api", $argv)) {
				# this name is not exists in controller module
				# i sure for this
				return "this_is_a_debugger_api_calls!!!";
			} else {
				# redirect to index controller
				return "index";
			}
		} else {
			$page = $argv["app"];
		}

		return $page;
	}

	/**
	 * 设置简写字符串的匹配的规则
	 * 文件名除了一些在文件系统上的非法字符串之外，其他的字符串都是能够被匹配上的
	 * 但是在这里规定文件名只能够使用数字字母以及小数点下划线
	*/
	const fileNamePattern = '[a-zA-Z0-9\_\.]+';
	/**
	 * php之中的标识符则只允许字母，数字和下划线
	*/
	const identifierPattern = "[a-zA-Z0-9\_]+";

	/**
	 * 为了方便，在html里面的控制器的链接可能为简写形式，例如：``{index/upload}``
	 * 则根据控制器的解析规则，应该在这个函数之中被拓展为结果url字符串：
	 * 
	 * ``/index.php?app=upload``
	 * 
	 * 在上面的例子之中index为php文件名，upload则是控制器之中的一个控制器api函数
	 * 
	 * 如果控制器的php文件不位于根目录下，则可以通过添加前缀的tag的方式进行区分标识：
	 * 
	 * 例如，控制器php文件在api文件夹下面的user.php文件之中，则可以简写为：
	 * ``{<api>user/modify_password}`` 
	 * 表示在``api/user.php``文件之中
	 * 
	 * 如果在更深一层文件夹之中，则可以简写为``{<api/user>security/modify_password}``
	 * 表示在``api/user/security.php``文件之中
	 * 
	 * 但是不过并不建议将php控制器文件放在很深的文件夹之中，添加这个前缀只是为了方便对控制器
	 * 按照功能进行分组，便于组织项目代码
	 * 
	 * @param string $html 包含有路由器规则占位符的HTML文档 
	*/
	public static function AssignController($html) {
		$fileName   = self::fileNamePattern;
		$identifier = self::identifierPattern; 

		# <api/user>security/modify_password
		$pattern = "((<$fileName(/$fileName)*>)?$fileName)/($identifier)";
		$pattern = "#\{$pattern\}#";

		# 使用正则匹配出所有的简写之后，对里面的字符串数据按照/作为分隔符拆开
		# 然后拓展为正确的url
		if (preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER) > 0) {
			$html = self::assignImpl($html, $matches[0]);
		}

		return $html;
	}

	/**
	 * 在这个函数之中实现了具体的解析和赋值的功能 
	*/
	private static function assignImpl($html, $matches) {
		foreach ($matches as $s) {
			$s   = trim($s, "{}");
			$dir = StringHelpers::GetStackValue($s, "<", ">");

			if (Strings::Len($dir) > 0) {
				# 因为在这里需要使用dir变量进行替换，所以dir应该在route变量的后面，
				# 即在完成替换之后才赋值
				$route = Strings::Replace($s, "<$dir>", "");
				$dir   = "/$dir";
			} else {
				$dir   = "";
				$route = $s; 
			}

			$tokens = Strings::Split($route, "/");
			$file   = $tokens[0];
			$app    = $tokens[1];
			$url    = "$dir/$file.php?app=$app";
			
			# 双引号下{}会被识别为字符串插值的操作
			# 但是在单引号直接插入变量进行插值却失效了
			# 所以在这里使用单引号加字符串连接来构建查找对象
			$find = '{'. $s .'}';
			$html = Strings::Replace($html, $find, $url);
			$s    = Strings::Replace($s, "<", "&lt;");

			console::log("<span style='color:blue;'><strong>$s</strong></span> =&gt; $url");
		}

		return $html;
	}
}