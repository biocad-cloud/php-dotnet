<?php

/**
 * PHP WEB programming utils from php.NET framework
*/
class Utils {

    /**
     * 具有限速功能的文件下载函数 
     * 
     * @param string $filepath 待文件下载的文件路径
     * @param integer $rateLimit 文件下载的限速大小，小于等于零表示不限速，这个函数参数的单位为字节Byte
     */
    public static function PushDownload($filepath, $rateLimit = -1) {
       
        header('Content-Description: File Transfer');
        header('Cache-control: private');
        header('Content-Type:'                  . mime_content_type($filepath));
        header('Content-Length:'                . filesize($filepath));
        header('Content-Disposition: filename=' . basename($filepath));
    
        flush();

        if ($rateLimit <= 0) {

            # 不限速
            readfile($filepath);

        } else {
            Utils::flushFileWithRateLimits($filepath, $rateLimit);
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
    */
    public static function URL() {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
	
	/*
	 * 获取当前时间点的Unix时间戳
	 */
	public static function UnixTimeStamp() {
		return time();
    }
    
    /**
     * 返回符合MySql所要求的格式的当前时间的字符串值
     * 
     * @param bool $MySqlStyle 返回的字符串格式是否是MySql数据库所要求的格式，默认是
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
    */
    public static function RandomASCIIString($len) {
		$s = "";
		$template = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$template = str_split($template);
		
		for ($i = 0; $i < $len; $i++) {
			$index = rand(0, count($template));
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
        # 2018-3-8 因为这个函数之中需要调用Microsoft.VisualBasic.Strings模块
        # 可能会因为在本脚本的头部进行引用其他的脚本文件的时候，这个模块的脚本还
        # 没有被加载，所以会导致出现无法找到类Strings的错误
        # 在这里显式的引入一次这个文件即可解决问题
        include_once dotnet::GetDotnetManagerDirectory() . "/Microsoft/VisualBasic/Strings.php";

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
}

?>