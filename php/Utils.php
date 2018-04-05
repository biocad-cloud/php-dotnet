<?php

class Utils {

    /**
     * 具有限速功能的文件下载函数 
     * 
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
     */
    public static function WithSuffixExtension($path, $ext) {
        $suffix = self::GetExtensionSuffix($path);
        return Strings::LCase($suffix) == Strings::LCase($ext);
    }
}

?>