declare module php_debugger {
    /**
     * 按照id来获取得到HTML节点元素
    */
    function $pick(id: string): HTMLElement;
    function $new(tagName: string, attrs?: {
        style?: string;
    }, html?: string): HTMLElement;
}
declare module php_debugger {
    /**
     * 初始化页面最下方的调试器标签页
    */
    function initTabUI(): void;
}
declare module php_debugger.serviceWorker {
    const debuggerGuid: string;
    const debuggerApi: string;
    /**
     * 更新的时间间隔过短，可能会影响调试
    */
    const workerInterval = 5000;
    /**
     * 每一秒钟执行一次服务器查询
    */
    function doInit(): void;
    function StartWorker(): void;
    function StopWorker(): void;
    /**
     * 对服务器进行调试器输出结果请求
     *
     * 假设服务器上一定会存在一个``index.php``文件？
    */
    function fetch(): void;
    interface SQLlog {
        time: string;
        SQL: string;
        runtime: string;
    }
    interface debuggerInfo {
        SQL: checkPointValue<SQLlog>;
    }
    interface checkPointValue<T> {
        lastCheckPoint: number;
        data: T[];
    }
}
