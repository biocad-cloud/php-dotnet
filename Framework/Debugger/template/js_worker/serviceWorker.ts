module php_debugger.serviceWorker {

    export const debuggerGuid: string = $pick("#debugger_guid").innerText;

    /**
     * 每一秒钟执行一次服务器查询
    */
    export function doInit() {
        setInterval(fetch, 1000);
    }

    /**
     * 对服务器进行调试器输出结果请求
     * 
     * 假设服务器上一定会存在一个``index.php``文件？
    */
    function fetch() {

    }
}