<?

dotnet::Imports("System.Diagnostics.StackTrace");
dotnet::Imports("System.Linq.Enumerable");

class View {
	
	public static function Display($variables) {
		$name = StackTrace::GetCallerMethodName();
		$path = "html/$name.html";
		$html = file_get_contents($path);
		
		# 在这里需要按照键名称长度倒叙排序，防止出现可能的错误替换
		$names = array_keys($variables);
		$names = Enumerable::OrderByDescending($variables, function($k) {
			return $k;
		});
		
		$reorder = array();
		
		for ($names as $key) {
			$reorder[$key] = $variables[$key];
		}
		
		foreach ($reorder as $name => $value) {
			$html = str_replace($name, $value, $html);
		}
		
		echo $html
	}
}

?>