<?php

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
	*/
	public static function HandleRequest($app) {
		$argv = $_GET;
		
		if (!$_GET || count($_GET) == 0) {
			# index.html as default
			$page = "index";
		} else {
			$page = $_GET["app"];
		}		
				
		if (method_exists($app, $page)) {
			$app->{$page}();
		} else {
			$message = "Web app `<strong>$page</strong>` is not available in this controller!";
			dotnet::PageNotFound($message);
		}
		
		# 在末尾输出调试信息？
		if (APP_DEBUG) {
			
		}
	}
		
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
	 * 表示在api/user.php文件之中
	 * 
	 * 如果在更深一层文件夹之中，则可以简写为``{<api/user>security/modify_password}``
	 * 表示在api/user/security.php文件之中
	 * 
	 * 不过并不建议将php控制器文件放在很深的文件夹之中，添加这个前缀只是为了方便对控制器
	 * 按照功能进行分组，便于组织项目代码
	 * 
	 * @param string $html 包含有路由器规则占位符的HTML文档 
	*/
	public static function AssignController($html) {
		# 设置简写字符串的匹配的规则
		# 文件名除了一些在文件系统上的非法字符串之外，其他的字符串都是能够被匹配上的
		# 但是在这里规定文件名只能够使用数字字母以及小数点下划线
		$fileNamePattern = '[a-zA-Z0-9\_\.]+';
		# php之中的标识符则只允许字母，数字和下划线
		$identifierPattern = "[a-zA-Z0-9\_]+";
		$pattern = "($fileNamePattern)/($identifierPattern)";
		$pattern = "#$pattern#";

		# 使用正则匹配出所有的简写之后，对里面的字符串数据按照/作为分隔符拆开
		# 然后拓展为正确的url
		if (preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER) > 0) {
			$matches = $matches[0];
			
			foreach ($matches as $s) {
				$tokens = Strings::Split($s, "/");
				$file   = $tokens[0];
				$app    = $tokens[1];
				$url    = "$file.php?app=$app";
				# 双引号下{}会被识别为字符串插值的操作
				# 但是在单引号直接插入变量进行插值却失效了
				# 所以在这里使用单引号加字符串连接来构建查找对象
				$find   = '{'. $s .'}';

				$html   = Strings::Replace($html, $find, $url);
			}
		}

		return $html;
	}

	/**
     * UTF-8 aware parse_url() replacement.
     * 
     * @return array
     */
    public static function mb_parse_url($url) {
        $enc_url = preg_replace_callback(
            '%[^:/@?&=#]+%usD',
            function ($matches) {
                return urlencode($matches[0]);
            },
            $url
        );
        
        $parts = parse_url($enc_url);
        
        if($parts === false) {
            throw new \InvalidArgumentException('Malformed URL: ' . $url);
        }
        
        foreach($parts as $name => $value) {
            $parts[$name] = urldecode($value);
        }
        
        return $parts;
    }
}
?>