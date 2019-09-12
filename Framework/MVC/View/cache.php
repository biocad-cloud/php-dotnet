<?php

class ViewCache {
    
    public static function doCache($path, $language) {
        # 在配置文件之中开启了缓存选项
        $cache = self::getCachePath($path);

        # 在调试模式下总是不使用cache
        # 为了将cache的信息也输出到调试终端，在这里设置条件为调试模式或者缓存文件
        # 不存在都会进行缓存的生成
        if (APP_DEBUG || (!file_exists($cache)) || (filesize($cache) <= 0)) {
            # 当缓存文件不存在的时候，生成缓存，然后返回
            console::log("Cache file will save at: <code>$cache</code>");

            # 将html片段合并为一个完整的html文档
            # 得到了完整的html模板
            $html      = file_get_contents($path);

            console::log("Template size = <strong>" . strlen($html) . "</strong> characters.");

            $cachePage = View::interpolate_includes($html, $path);
            $cachePage = View::valueAssign($cachePage, $language);

            console::log("Cache data created!");
            console::log("sizeof cache is " . strlen($cachePage));

            if (DotNetRegistry::HtmlMinifyOfCache()) {
                # 进行html文件的压缩
                $cachePage = \MVC\Views\HtmlMinifier::minify($cachePage);
                console::log("sizeof cache after minify is: " . strlen($cachePage)); 
            }

            $cacheDir  = dirname($cache);
            
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0777, true);
            }
            file_put_contents($cache, $cachePage);

            debugView::LogEvent("HTML view cache created!");
        } else {
            debugView::LogEvent("HTML view cache hits!");
        }

        $cache = realpath($cache);
        $html  = file_get_contents($cache);

        debugView::LogEvent("Cache=$cache");

        return [$html, $cache];
    }

    /**
	 * 获取目标html文档梭对应的缓存文件的文件路径
	*/
	private static function getCachePath($path) {
		// temp/{yyymmmdd}/viewName
		$version = filemtime($path);
		$temp    = dotnet::getMyTempDirectory();
		# 因为缓存的路径和主html文件的修改时间相关，所以如果只是文档碎片被更新了
		# 会因为主html文件没有被修改的原因而没有更新cache
		# 在这里使用app version来更新缓存
		$appVer  = DotNetRegistry::Read("APP_VERSION", "0.0.0");
		$file    = basename($path);
		# 2018-09-18 从下面的代码之中可以看见，因为缓存页面是和用户请求有关的
		# 所以没有办法为每一个视图页面生成缓存页面
		$path    = md5($_SERVER["REQUEST_URI"]);
		$cache   = "$temp/$appVer/$file/$version/$path.html";

		return $cache;
	}
}