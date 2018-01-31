<?php

class CURLExtensions {

	/*
	 * 模拟提交数据函数
	 */ 
	public static function POST($url, $data = NULL) { 
		$curl = curl_init(); 
		
		curl_setopt($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);                      // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);                      // 从证书中检查SSL加密算法是否存在
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);                      // 使用自动跳转
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);                         // 自动设置Referer
		curl_setopt($curl, CURLOPT_POST, 1);                                // 发送一个常规的Post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                      // Post提交的数据包
		curl_setopt($curl, CURLOPT_COOKIEFILE, ‘cookie.txt’);               // 读取上面所储存的Cookie信息
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);                            // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 0);                              // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);                      // 获取的信息以文件流的形式返回
		
		$tmpInfo = curl_exec($curl);                                        // 执行操作
		
		if (curl_errno($curl)) {
			echo ‘Errno’.curl_error($curl);
		}
		curl_close($curl); 
		
		return $tmpInfo; 
	}
}

?>