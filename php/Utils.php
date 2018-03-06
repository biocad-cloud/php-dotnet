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
}

?>