declare module php_debugger {
    /**
     * 按照id来获取得到HTML节点元素
    */
    function $pick(id: string): HTMLElement;
}
declare module php_debugger {
    /**
     * 初始化页面最下方的调试器标签页
    */
    function initTabUI(): void;
}
declare module php_debugger.serviceWorker {
    const debuggerGuid: string;
    /**
     * 每一秒钟执行一次服务器查询
    */
    function doInit(): void;
}
