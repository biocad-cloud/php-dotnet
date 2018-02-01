<?

class DataURI {
	
	private $base64;
	private $type;
	
	function __construct($path) {
		$data = file_get_contents($path);
		$data = base64_encode($data);
		
		$this->base64 = $data;
		$this->type   = mime_content_type($path);
	}
	
	public function getURI() {
		$type   = $this->type;
		$base64 = $this->base64;
		
		return "data:$type;base64,$base64";
	}
}

?>