<?php

# 因为在package.php之中本模块是属于第一个加载的，Imports函数还无法使用
# 所以在这里直接使用 include <文件路径> 来显式导入

# 2018-3-8 因为这个函数之中需要调用Microsoft.VisualBasic.Strings模块
# 可能会因为在本脚本的头部进行引用其他的脚本文件的时候，这个模块的脚本还
# 没有被加载，所以会导致出现无法找到类Strings的错误
# 在这里显式的引入一次这个文件即可解决问题
bootstrapLoader::imports(".Microsoft.VisualBasic.Strings");

/**
 * PHP WEB programming utils from php.NET framework
*/
class Utils {

    /**
     * 获取得到序列之中的第一个元素
    */
    public static function First($array) {
        foreach($array as $key => $value) {
            return $value;
        }
    }

    public static function getRealFileType($filename) {
        return using(new FileStream($filename, "rb"), function($image) {
            // 只读2字节  
            $bin      = $image->Read(2);
            $strInfo  = @unpack("C2chars", $bin);
            $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
            $fileType = File::GetType($typeCode);
            
            return $fileType;
        });
    }

    /** 
     * 如果经过检查之后发现不是图片数据，则这个函数会返回False
    */
    public static function imagetype($filename) {
        $type   = self::getRealFileType($filename);
        $images = ["jpg", "gif", "bmp", "png"];
        
        if (in_array($type, $images)) {
            return $type;
        } else {
            return false;
        }
    }

    #region "OSS工具"

    /**
	 * 在OSS文件系统之上创建文件夹的时候，不可以直接使用``mkdir``命令递归创建路径
	 * 所以尝试使用这个新的函数来完成文件夹的递归创建操作
	 *
	 * @param string $directory 请注意，这个参数必须要使用全路径
     * @param string $ossMountRoot OSS文件系统的挂载点，如果所传入的$directory参数不是
     * 从这个挂载点开始的，则会被认为是普通的文件系统，将会使用普通的``mkdir``进行文件夹的
     * 创建操作
	*/
	public static function OSSmkdir($directory, $ossMountRoot = "/mnt/ossfs") {
		// 2017-10-9 在这里直接使用realpath函数会挂掉
		// 可能是因为$directory文件夹必须要存在于文件系统之上的原因吧
		$directory = str_replace(array("\\","//"), "/", $directory);
		
		// 假设OSS文件系统是固定被挂载在/mnt/ossfs文件夹处的
		// 如果下面等于False，则说明不是移动到OSS文件系统，而是普通的文件系统
		// 则直接使用php的mkdir函数就好了
		//
		// 因为假若/mnt/ossfs在最开始的话，函数会返回0
		// 因为0等同于False，所以在这里需要在$directory前面加一个字符使strpos等于1
		if (strpos("@" . $directory, $ossMountRoot) != 1) {
            # 这个文件夹不是从OSS的挂载点起始的，则直接使用普通的mkdir
			mkdir($directory, 0777, true);
		} else {
            $names = preg_split("/[\\/]+/", $directory);
            $cur   = getcwd();
            
            chdir("/");
            self::mkdir_internal($names, 0);		
            chdir($cur);
        }
	}
	
	private static function mkdir_internal($names, $i) {
		if ($i == count($names) - 1) {
			return;
		} else {
			if (!file_exists($names[$i])) {
				if (!mkdir($names[$i], 640)) {
					// echo "FALSE!";
				}
			}
			chdir($names[$i]);
			
			self::mkdir_internal($names, $i + 1);
			chdir("..");
		}
	}
    #endregion

    /**
     * Does this php web server is running on a Windows server?
     * 
     * @return boolean
    */
    public static function IsWindowsOS() {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * @return boolean
    */
    public static function IsSessionStart() {
        return session_status() !== PHP_SESSION_NONE;
    }

    /**
     * 将只含有一个元素的``[key => value]``键值对转换为数组返回，
     * 可以使用``list($key, $value)``来进行赋值
     * 
     * @param array $table [key => value]
     * @return array
    */
    public static function Tuple($table) {
        if (!$table || count($table) == 0) {
            return [];
        } else {
            $keys  = array_keys($table);
            $value = $table[$keys[0]];
            
            return [$keys[0], $value];
        }
    }

    public static function TupleToObj($tuples,
        $keyName   = "key", 
        $valueName = "value") {

        $list = [];

        foreach($tuples as $tuple) {
            $key   = $tuple[$keyName];
            $value = $tuple[$valueName];

            $list[$key] = $value;
        }

        return $list;
    }

    public static function KeyValueTuple($array, 
        $keyName   = "key", 
        $valueName = "value") {

        $tuple = [];

        foreach($array as $key => $value) {
            $tuple[] = [
                $keyName   => $key, 
                $valueName => $value
            ];
        }

        return $tuple;
    }

    /**
     * Get mime content type based on the file extension shuffix name
     * 
     * @param string $file
    */
    public static function get_MIMEcontentType($file) {    
        // mime_content_type函数会将js/css文件解释为text/plain或者text/html
        // 在这里需要修复一下这个bug
        $ext = strtolower(pathinfo($file)["extension"]);

        if ($ext == "js") {
            return "text/javascript";
        } else if ($ext == "css") {
            return "text/css";     
        } else if ($ext == "gif") {
            return "image/gif";
        } else if ($ext == "jpg") {
            return "image/jpeg";
        } else if ($ext == "png") {
            return "image/png";
        } else if ($ext == "svg") {
            return "image/svg+xml";
        } else if ($ext == "pdf") {
            return "application/pdf";
        }

        if (class_exists('finfo')) {
            $result = new finfo();

            if (is_resource($result) === true) {
                return $result->file($file, FILEINFO_MIME_TYPE);
            } else {
                $mime = \mime_content_type($file);
            }
        } else {
            $mime = NULL;
        }
    
        if (empty($mime) || false == $mime) {
            return "application/octet-stream";
        } else {
            return $mime;
        }
    }

    /** 
     * 一般是用于处理向用户传输比较大的文本文件的传输操作
     * 
     * @param string $filepath 目标文件路径
    */
    public static function PrintLargeText($filepath) {
        self::doDataTransfer($filepath, filesize($filepath));
    }

    /** 
     * 执行不限速的文件传输操作
     * 
     * @param string $filepath 目标文件路径
    */
    private static function doDataTransfer($filepath, $file_size) {
        $fp         = fopen($filepath, "r");
        $file_count = 0; 
        $buffer     = 1024; 

        //向浏览器返回数据
        while(!feof($fp) && $file_count < $file_size) { 
            $file_con    = fread($fp, $buffer); 
            $file_count += $buffer; 
            echo $file_con; 
        } 

        fclose($fp); 
    }

    /**
     * 具有限速功能的文件下载函数 
     * 
     * @param string $file 待文件下载的文件路径
     * @param integer $rateLimit 文件下载的限速大小，小于等于零表示不限速，这个函数参数的单位为字节Byte
     * @param string $renameAs 可以在这里重设所下载的文件的文件名
     * 
    */
    public static function PushDownload($file, $rateLimit = -1, $mime = null, $renameAs = null, $isdata = false, $filetransferMode = true) {
        if (!$isdata) {
            # file object is a disk file

            # 2018-6-18 有些服务器上面mime_content_type函数可能无法使用
            # 所以在这里添加了一个可选参数来手动指定文件类型
            if (empty($mime) || false == $mime) {
                $mime = self::get_MIMEcontentType($file);
            }

            if (!$renameAs) {
                $renameAs = basename($file);
            }

            $file_size = filesize($file); 
        } else {
            # is the file data itself
            $renameAs  = empty($renameAs) ? "file" : $renameAs ;
            $mime      = empty($mime) ? self::get_MIMEcontentType($renameAs) : $mime;
            $file_size = strlen($file);
        }

        if ($filetransferMode) {
            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=' . $renameAs);
            // 告诉浏览器，这是二进制文件
            header("Content-Transfer-Encoding: binary"); 
            header('Cache-control: private');
            header("Content-type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Accept-Length: $file_size");
            header("Content-Length: $file_size");
        } else {
            header("Content-Length: $file_size");
        }        
        
        header('Content-Type:' . $mime);
        header("Accept-Ranges: bytes");
        
        ob_end_clean();

        if(!$isdata) {
            if ($rateLimit <= 0) {
                Utils::doDataTransfer($file, $file_size);
            } else {
                Utils::flushFileWithRateLimits($file, $rateLimit);
            }
        } else {
            echo $file;
        }
    }

    private static function flushFileWithRateLimits($filepath, $rateLimit) {
        $file = fopen($filepath, "r");

        while(!feof($file)) {

            // send the current file part to the browser
            print fread($file, round($rateLimit * 1024));
            // flush the content to the browser
            flush();
            // sleep one second
            sleep(1);
        }

        fclose($file);
    }

    /**
     * 函数返回当前的请求的完整URL
     * 
     * @return string
    */
    public static function URL($includeHostName = true) {
        if (IS_CLI) {
            return "";
        } else if ($includeHostName) {
            return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        } else {
            return $_SERVER["REQUEST_URI"];
        }        
    }
	
	/**
	 * 获取当前时间点的Unix时间戳
     * 
     * @return integer
	*/
	public static function UnixTimeStamp() {
		return time();
    }
    
    /**
     * 返回符合MySql所要求的格式的当前时间的字符串值
     * 
     * @param bool $MySqlStyle 返回的字符串格式是否是MySql数据库所要求的格式，默认是
     * @return string
    */
    public static function Now($MySqlStyle = TRUE) {
        if ($MySqlStyle) {
            return date('Y-m-d H:i:s',   time());
        } else {
            $milliseconds = time();
            $seconds      = $milliseconds / 1000;
            $remainder    = round($seconds - ($seconds >> 0), 3) * 1000;

            return date('Y:m:d H:i:s.', $milliseconds) . $remainder;
        }        
    }

    /**
     * 函数返回指定长度的随机ASCII字符串
     * 
     * @param integer $len The resulted string length
     * @param boolean $justDigits
     * 
     * @return string A specific length random ascii string 
    */
    public static function RandomASCIIString($len, $justDigits = false) {
		$s = "";
                
        if ($justDigits) {
            $template = "0123456789";
        } else {
            $template = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }

		$template = str_split($template);
        $max      = count($template) - 1;
        
		for ($i = 0; $i < $len; $i++) {
			$index = rand(0, $max);
			$s = $s . $template[$index];
		}
		
		return $s;
    }
    
    /**
     * 获取给定文件路径的文件拓展名
     * 
     * @param string $path 所给定的文件路径
     * 
     * @return string 函数返回不带小数点的文件拓展名
    */
    public static function GetExtensionSuffix($path) {
        $array  = Strings::Split($path, ".");
        $suffix = array_values(array_slice($array, -1));
        $suffix = $suffix[0];

        return $suffix;
    }

    /**
     * 判断这个文件路径是否是以特定的文件拓展名结尾的？这个函数大小写不敏感
     * 
     * @param string $path 给定的文件名或者文件路径
     * @param string $ext 文件拓展名，这个文件拓展名不带小数点
     * 
     * @return bool 目标文件夹是否是以指定的文件拓展名结尾？
    */
    public static function WithSuffixExtension($path, $ext) {
        $suffix = self::GetExtensionSuffix($path);
        return Strings::LCase($suffix) == Strings::LCase($ext);
    }

    /**
	 * 文件数据格式显示转换
	*/ 
	public static function UnitSize($byte) {

	    if ($byte < 1024) {
	    	$unit="B";
	    } else if ($byte < 10240) {
	      	$byte=self::round_dp($byte/1024, 2);
	      	$unit="KB";
	    } else if ($byte < 102400) {
	      	$byte=self::round_dp($byte/1024, 2);
	      	$unit="KB";
	    } else if ($byte < 1048576) {
	      	$byte=self::round_dp($byte/1024, 2);
	      	$unit="KB";
	    } else if ($byte < 10485760) {
	      	$byte=self::round_dp($byte/1048576, 2);
	      	$unit="MB";
	    } else if ($byte < 104857600) {
	      	$byte=self::round_dp($byte/1048576,2);
	      	$unit="MB";
	    } else if ($byte < 1073741824) {
	      	$byte=self::round_dp($byte/1048576, 2);
	      	$unit="MB";
	    } else {
	      	$byte=self::round_dp($byte/1073741824, 2);
	      	$unit="GB";
	    }

		$byte .= $unit;

		return $byte;
	}

	private static function round_dp($num, $dp) {		
        $sh = pow(10, $dp);
	  	return round($num * $sh) / $sh;
    }

    /**
     * 一个安全的数组读取函数，同时支持数组或者std类对象
     * 
     * 阻止出现警告提示： Notice: Undefined index: blabla...
     * 
     * @param array $array 
     * @param string $key 数组之中的元素的引用键名，可以使用|来表示多个键名，这些键名表示或关系，
     *      返回第一个被查找到的键名的值，这个表达式通常用来表示别名查找
    */
    public static function ReadValue($array, $key, $default = null) {
        if ($array == false || is_null($array) || empty($array)) {
            return $default;
        } else if (is_array($array) || ($array instanceof ArrayAccess)) {

            if (array_key_exists($key, $array)) {
                return $array[$key];
            } else {
                $keys = explode("|", $key);

                if (count($keys) > 1) {
                    # 需要进行别名查找
                    foreach($keys as $aliasKey) {
                        if (array_key_exists($aliasKey, $array)) {
                            return $array[$aliasKey];
                        }
                    }
                }
    
                return $default;
            }

        } else if (is_object($array)) {
        
            if (method_exists($array, $key)) {
                return $array->{$key}();
            } else if (property_exists($array, $key)) {
                return $array->{$key};
            } else {
                return $default;
            }

        } else {
            throw new dotnetException("Input object should be an array or std object!");
        }
    }
    
    /**
     * 进行数组的克隆
     * 
     * @param array $array 
     * 
     * @return array 如果array函数参数是空值，则这个函数会返回空集合
    */
    public static function ArrayCopy($array) {
        if (empty($array)) {
            return [];
        } else {
            return (new ArrayObject($array))->getArrayCopy();
        }
    }

    /**
     * 对字典数组之中的对象进行重新排序
     * 
     * @param array $array
     * @param string[] $orderKeys
     * 
     * @return array
    */
    public static function ArrayReorder($array, $orderKeys) {
        $new = [];

        foreach($orderKeys as $key) {
            $new[$key] = $array[$key];
        }

        return $new;
    }

    /**
     * 加密或者解密消息字符串
     * 
     * @param string $string 字符串，明文或密文
     * @param string $operation DECODE表示解密，其它表示加密
     * @param string $key 密匙
     * @param integer $expiry 密文有效期
     * 
     * @return string 加密之后的密文或者解密之后的明文
    */
    public static function AuthCode($string, $operation = 'DECODE', $key = '', $expiry = 0) {   
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙   
        $ckey_length = 4;
        // 密匙   
        $key  = md5($key ? $key : DotNetRegistry::DefaultAuthKey());   
        // 密匙a会参与加解密   
        $keya = md5(substr($key, 0, 16));   
        // 密匙b会用来做数据完整性验证   
        $keyb = md5(substr($key, 16, 16));   
        // 密匙c用于变化生成的密文   
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';   
        // 参与运算的密匙   
        $cryptkey   = $keya . md5($keya.$keyc);   
        $key_length = strlen($cryptkey);   
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)， 
        // 解密时会通过这个密匙验证数据完整性   
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
        $string = $operation == 'DECODE' ? 
            base64_decode(substr($string, $ckey_length)) : 
            sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string.$keyb), 0, 16) . $string;
        $string_length = strlen($string);   
        $result = '';   
        $box    = range(0, 255);   
        $rndkey = array();   

        // 产生密匙簿   
        for($i = 0; $i <= 255; $i++) {   
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);   
        }   

        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度   
        for($j = $i = 0; $i < 256; $i++) {   
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;   
            $tmp = $box[$i];   
            $box[$i] = $box[$j];   
            $box[$j] = $tmp;   
        }   

        // 核心加解密部分   
        for($a = $j = $i = 0; $i < $string_length; $i++) {   
            $a       = ($a + 1) % 256;   
            $j       = ($j + $box[$a]) % 256;   
            $tmp     = $box[$a];   
            $box[$a] = $box[$j];   
            $box[$j] = $tmp;   
            // 从密匙簿得出密匙进行异或，再转成字符   
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));   
        }   

        if($operation == 'DECODE') {  

            // 验证数据有效性，请看未加密明文的格式   
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {   
                return substr($result, 26);   
            } else {   
                return '';   
            }   

        } else {   

            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因   
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码   
            return $keyc.str_replace('=', '', base64_encode($result));   
        }
    }

    /**
     * 将字符串文本转换为字符数组
     * 
     * @param string $str 字符串文本
     * 
     * @return string[] 输入的字符串文本参数经过分割之后得到的字符的数组
    */
    public static function Chars($str) {
        return str_split($str);
    }

    /**
     * 判断当前的这个数据库查询结果是否是空？
     * 
     * @return boolean
    */
    public static function isDbNull($result) {
        if (empty($result) || $result == false) {
            return true;
        } else if (is_array($result) && count($result) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 返回目标子字符串在给定的字符串之上的所有位置的集合
     * 
     * @param string $str 待查找的一个给定的字符串
     * @param string $find 用于进行位置查找的目标子字符串
     * 
     * @param integer[] 返回顶点位置的集合数组
    */
    public static function Indices($str, $find) {
        $index = [];
        $i     = 0;

        while (true) {
            $i = strpos($str, $find, $i);

            if ($i === false) {
                return $index;
            } else {
                array_push($index, $i);
                $i = $i + 1;
            }
        }
    }

    /**
     * 获取消息请求的客户端的ip地址
    */
    public static function UserIPAddress() {
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                return $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                return $_SERVER["HTTP_CLIENT_IP"];
            } else {
                return $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")){
                return getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                return getenv("HTTP_CLIENT_IP");
            } else {
                return getenv("REMOTE_ADDR");
            }
        }

        return false;
    }

    /**
     * 创建缩略图（需要gd插件的支持）
     * 
     * > http://webcheatsheet.com/php/create_thumbnail_images.php
     * 
     * @param string $pathToImages 图片的文件夹路径或者一个图片的文件路径
     * @param string $pathToThumbs 缩略图的输出的文件夹路径
     * @param integer $thumbWidth 所生成的缩略图的最大宽度
     * 
     * @return integer 成功的数目
    */
    public static function createThumbs($pathToImages, $pathToThumbs, $thumbWidth = 120) {
        if (!file_exists($pathToImages)) {
            return false;
        }

        if (is_file($pathToImages)) {
            $pathToImages = [$pathToImages];
        } else {
            // is a directory
            $tmp = [];
            // open the directory
            $dir = opendir( $pathToImages );

            // loop through it, looking for any/all JPG files:
            while (false !== ($fname = readdir( $dir ))) {
                array_push($tmp, "{$pathToImages}/{$fname}");
            }

            $pathToImages = $tmp;

            // close the directory
            closedir( $dir );
        }

        if (!file_exists($pathToThumbs)) {
            mkdir($pathToThumbs, 0777, true);
        }

        $n = 0;

        foreach($pathToImages as $image) {
            $fileName  = explode("/", $image);
            $fileName  = end($fileName);
            $thumbPath = "$pathToThumbs/$fileName";

            if (self::ImageThumbs($image, $thumbPath, $thumbWidth)) {
                $n++;
            }
        }

        return $n;
    }

    public static function LoadImage($path, $ext = null) {       
        if (!file_exists($path)) {
            return null;
        } else if (empty($ext)) {
            $info = pathinfo($path);
            $ext  = strtolower($info['extension']);
        }

        if ($ext === 'jpg') {
            return imagecreatefromjpeg($path);
        } else if ($ext === "png") {
            return imagecreatefrompng($path);
        } else {
            return null;
        }
    }

    /**
     * 生成原始图片的缩略图
     * 
     * @param string $rawImage 输入的文件路径
     * @param string $thumbImage 输出的缩略图文件路径
     * @param integer $thumbWidth
    */
    public static function ImageThumbs($rawImage, $thumbImage, $thumbWidth = 120, $ext = null) {
        // load image and get image size
        $img = self::LoadImage($rawImage, $ext);

        if (!$img) {
            return false;
        }

        $width  = imagesx($img);
        $height = imagesy($img);
        // calculate thumbnail size
        $new_width  = $thumbWidth;
        $new_height = floor( $height * ( $thumbWidth / $width ) );
        // create a new temporary image
        $tmp_img = imagecreatetruecolor( $new_width, $new_height );

        // copy and resize old image into new image 
        imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

        // save thumbnail into a file
        imagepng($tmp_img, $thumbImage);

        return true;
    }
}