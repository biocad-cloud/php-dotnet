<?php

dotnet::Imports("System.Diagnostics.StackTrace");
dotnet::Imports("System.Linq.Enumerable");
dotnet::Imports("Microsoft.VisualBasic.Strings");

/*
 * html view handler
 */
class View {
	
	// 从html文件夹之中读取和当前函数同名的文件并显示出来
	public static function Display($vars = NULL) {

		$name    = StackTrace::GetCallerMethodName();
		$wwwroot = DotNetRegistry::GetMVCViewDocumentRoot();

		# 假若直接放在和index.php相同文件夹之下，那么apache服务器会优先读取
		# index.html这个文件的，这就导致无法正确的通过这个框架来启动Web程序了
		# 所以html文件规定放在html文件夹之中
		$path = "$wwwroot/$name.html";

		if (dotnet::$debug) {
			echo $path . "\n\n";
		}

		View::Show($path, $vars);
	}
	
	// 显示指定的文件路径的html文本的内容
	public static function Show($path, $vars = NULL) {
		echo self::Load($path, $vars);
	}
	
	// 加载指定路径的html文档并对其中的占位符利用vars字典进行填充
	// 这个函数还会额外的处理includes关系
	public static function Load($path, $vars = NULL) {
		return View::InterpolateTemplate(file_get_contents($path), $vars);
	}

	public static function InterpolateTemplate($html, $vars) {
		# 将html片段合并为一个完整的html文档
		$html = View::interpolate_includes($html, $path);
		# 假设在html文档里面总是会存在url简写的，则在这里需要进行替换处理
		$html = View::AssignController($html);

		# 没有需要进行设置的变量字符串，则直接在这里返回html文件
		if (!$vars) {
			return $html;			
		} else {
			return View::Assign($html, $vars);
		}
	}

	private static function interpolate_includes($html, $path) {
		$pattern = "#[$]\{.+?\}#";
		$dirName = dirname($path);
		
		if (preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER) > 0) { 
			$matches = $matches[0];
			
			# ${includes/head.html}
			foreach ($matches as $s) { 
				$path    = Strings::Mid($s, 2, strlen($s) - 3);
				$path    = "$dirName/$path";
				$include = file_get_contents($path);
				$html    = Strings::Replace($html, $s, $include);				
			}
		}
		
		return $html;
	}
	
	/*
	 * html页面之上存在有额外的需要进行设置的字符串变量参数
	 * 在这里进行字符串替换操作
	 */
	public static function Assign($html, $vars) {
		
		# 在这里需要按照键名称长度倒叙排序，防止出现可能的错误替换		
		$vars = Enumerable::OrderByKeyDescending($vars, function($key) {
			return strlen($key);
		});	
		
		// print_r($vars);

		# 变量的名称$name的值为名称字符串，例如 id
		# 而在html文件之中需要进行申明的形式则是 {$id}
		# 需要在这里需要进行额外的字符串链接操作才能够正常的替换掉目标		
		foreach ($vars as $name => $value) {
			$name = '{$' . $name . '}';
			$html = Strings::Replace($html, $name, $value);
		}
		
		return $html;
	}

	public static function AssignController($html) {

		# 为了方便，在html里面的控制器的链接可能为简写形式
		# 例如：{index/upload}
		# 则根据控制器的解析规则，应该在这个函数之中被拓展为
		# 结果url字符串：/index.php?app=upload

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
}
?>