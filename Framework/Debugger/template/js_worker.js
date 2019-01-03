var php_debugger;
(function (php_debugger) {
    /**
     * 按照id来获取得到HTML节点元素
    */
    function $pick(id) {
        return document.getElementById(id.substr(1));
    }
    php_debugger.$pick = $pick;
    function $new(tagName, attrs, html) {
        if (html === void 0) { html = null; }
        var node = document.createElement(tagName);
        if (attrs) {
            Object.keys(attrs).forEach(function (name) { return node.setAttribute(name, attrs[name]); });
        }
        if (html) {
            node.innerHTML = html;
        }
        return node;
    }
    php_debugger.$new = $new;
})(php_debugger || (php_debugger = {}));
/// <reference path="helper.ts" />
var php_debugger;
(function (php_debugger) {
    /**
     * 初始化页面最下方的调试器标签页
    */
    function initTabUI() {
        var open = php_debugger.$pick('#think_page_trace_open');
        var close = php_debugger.$pick('#think_page_trace_close').childNodes[1];
        var trace = php_debugger.$pick('#think_page_trace_tab');
        var container = close.parentNode;
        var closeDebuggerTab = function () {
            trace.style.display = 'none';
            container.style.display = 'none';
            open.style.display = 'block';
            php_debugger.serviceWorker.StopWorker();
        };
        open.onclick = function () {
            trace.style.display = 'block';
            open.style.display = 'none';
            container.style.display = 'block';
            php_debugger.serviceWorker.StartWorker();
        };
        close.onclick = closeDebuggerTab;
        attachTabSwitch();
        closeDebuggerTab();
    }
    php_debugger.initTabUI = initTabUI;
    var tab_cont = "think_page_trace_tab_cont";
    function attachTabSwitch() {
        var tab = null;
        var contentTab;
        var contentTabs = php_debugger.$pick("#" + tab_cont).getElementsByClassName(tab_cont);
        var tab_tit = php_debugger.$pick('#think_page_trace_tab_tit').getElementsByTagName('span');
        for (var i = 0; i < tab_tit.length; i++) {
            tab = tab_tit[i];
            tab.onclick = (function (i) {
                return function () {
                    for (var j = 0; j < contentTabs.length; j++) {
                        contentTab = contentTabs[j];
                        contentTab.style.display = 'none';
                        tab_tit[j].style.color = '#999';
                    }
                    contentTab = contentTabs[i];
                    contentTab.style.display = 'block';
                    tab_tit[i].style.color = '#000';
                    $(".jsonview").show();
                    $(".jsonview-container").show();
                };
            })(i);
        }
        // 显示第一页标签页：调试器参数概览
        tab_tit[0].click();
    }
})(php_debugger || (php_debugger = {}));
var php_debugger;
(function (php_debugger) {
    var serviceWorker;
    (function (serviceWorker) {
        serviceWorker.debuggerGuid = php_debugger.$pick("#debugger_guid").innerText;
        serviceWorker.debuggerApi = "/index.php?app=php.NET&api=debugger";
        /**
         * 服务器返回来的是大于这个checkpoint数值的所有的后续新增记录
        */
        var checkpoints = {};
        /**
         * 当前的这个后台轮询线程的句柄值
        */
        var timer;
        /**
         * 每一秒钟执行一次服务器查询
        */
        function doInit() {
            // 初始化所有的checkpoint
            Object.keys({
                SQL: null
            }).forEach(function (itemName) { return checkpoints[itemName] = 0; });
            // doinit函数是在脚本起始运行的时候被调用的
            // 但是最开始的时候调试器的标签页还没有打开
            // 所以没有必要一开始就启动后台线程
            // serviceWorker.StartWorker();
        }
        serviceWorker.doInit = doInit;
        function StartWorker() {
            try {
                serviceWorker.StopWorker();
            }
            catch (ex) {
                // do nothing
                // just ignore the error
            }
            timer = setInterval(fetch, 1000);
        }
        serviceWorker.StartWorker = StartWorker;
        function StopWorker() {
            clearInterval(timer);
        }
        serviceWorker.StopWorker = StopWorker;
        /**
         * 对服务器进行调试器输出结果请求
         *
         * 假设服务器上一定会存在一个``index.php``文件？
        */
        function fetch() {
            $.post(serviceWorker.debuggerApi + "&guid=" + serviceWorker.debuggerGuid, checkpoints, function (info) {
                if (checkpoints["SQL"] != info.SQL.lastCheckPoint) {
                    checkpoints["SQL"] = info.SQL.lastCheckPoint;
                    appendSQL(info.SQL.data);
                }
            });
        }
        serviceWorker.fetch = fetch;
        function appendSQL(SQLlogs) {
            var mysqlLogs = php_debugger.$pick("mysql-logs");
            SQLlogs.forEach(function (log) { return mysqlLogs.appendChild(php_debugger.$new("li", {
                style: "border-bottom:1px solid #EEE;font-size:14px;padding:0 12px"
            }, log.SQL + " [ RunTime:" + log.runtime + " ]")); });
        }
    })(serviceWorker = php_debugger.serviceWorker || (php_debugger.serviceWorker = {}));
})(php_debugger || (php_debugger = {}));
/// <reference path="tabUI.ts" />
/// <reference path="serviceWorker.ts" />
php_debugger.initTabUI();
php_debugger.serviceWorker.doInit();
//# sourceMappingURL=js_worker.js.map