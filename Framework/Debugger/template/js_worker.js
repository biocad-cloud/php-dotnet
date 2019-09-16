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
            Object.keys(attrs)
                .forEach(function (name) {
                if (name == "onclick") {
                    node.onclick = attrs[name];
                }
                else {
                    node.setAttribute(name, attrs[name]);
                }
            });
        }
        if (html) {
            if (typeof html == "string") {
                node.innerHTML = html;
            }
            else {
                node.appendChild(html);
            }
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
        var close = php_debugger.$pick('#think_page_trace_close_button');
        var trace = php_debugger.$pick('#think_page_trace_tab');
        var container = php_debugger.$pick('#think_page_trace_close');
        var closeTable = php_debugger.$pick("#mysql-close");
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
        closeTable.onclick = function () {
            php_debugger.$pick("#mysql-query-display-page").style.display = "none";
            php_debugger.$pick("#mysql-logs").style.display = "block";
        };
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
        serviceWorker.debuggerSqlApi = "/index.php?app=php.NET&api=sql_query";
        /**
         * 服务器返回来的是大于这个checkpoint数值的所有的后续新增记录
        */
        var checkpoints = {};
        /**
         * 当前的这个后台轮询线程的句柄值
        */
        var timer;
        /**
         * 更新的时间间隔过短，可能会影响调试
        */
        serviceWorker.workerInterval = 5000;
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
            fetch();
        }
        serviceWorker.doInit = doInit;
        function StartWorker() {
            checkpoints["guid"] = serviceWorker.debuggerGuid;
            try {
                serviceWorker.StopWorker();
            }
            catch (ex) {
                // do nothing
                // just ignore the error
            }
            timer = setInterval(fetch, serviceWorker.workerInterval);
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
            $.post(serviceWorker.debuggerApi, checkpoints, function (info) {
                if (info.SQL.lastCheckPoint > 0 && checkpoints["SQL"] != info.SQL.lastCheckPoint) {
                    checkpoints["SQL"] = info.SQL.lastCheckPoint;
                    appendSQL(info.SQL.data);
                }
            });
        }
        serviceWorker.fetch = fetch;
        function appendSQL(SQLlogs) {
            var mysqlLogs = php_debugger.$pick("#mysql-logs");
            SQLlogs.forEach(function (log) { return mysqlLogs.appendChild(php_debugger.$new("li", {
                style: "border-bottom:1px solid #EEE;font-size:14px;padding:0 12px"
            }, sql(log))); });
        }
        function showQuery(sql) {
            php_debugger.$pick("#mysql").innerHTML = sql;
            $.post(serviceWorker.debuggerSqlApi, {
                sql: sql,
                guid: serviceWorker.debuggerGuid
            }, function (table) {
                var display = php_debugger.$pick("#mysql-query-display");
                php_debugger.$pick("#mysql-logs").style.display = "none";
                php_debugger.$pick("#mysql-query-display-page").style.display = "block";
                display.innerHTML = "";
                if (table.code == 0) {
                    // table rows data
                    var rowDatas = table.info;
                    var titles = Object.keys(rowDatas[0]);
                    var thead = php_debugger.$new("thead");
                    var tbody = php_debugger.$new("tbody");
                    var r = void 0;
                    for (var _i = 0, titles_1 = titles; _i < titles_1.length; _i++) {
                        var td = titles_1[_i];
                        thead.appendChild(php_debugger.$new("th", {}, td));
                    }
                    for (var _a = 0, rowDatas_1 = rowDatas; _a < rowDatas_1.length; _a++) {
                        var row = rowDatas_1[_a];
                        r = php_debugger.$new("tr");
                        for (var i = 0; i < titles.length; i++) {
                            r.appendChild(php_debugger.$new("td", {}, row[titles[i]]));
                        }
                        tbody.appendChild(r);
                    }
                    display.appendChild(thead);
                    display.appendChild(tbody);
                }
                else {
                    // error message
                    display.innerHTML = "<span style=\"font-style: bold; color: red\">" + table.info + "</span>";
                }
            });
        }
        function sql(log) {
            var sql = log.SQL;
            if (sql.indexOf("SELECT ") == 0) {
                var div = php_debugger.$new("div", {}, "");
                div.appendChild(php_debugger.$new("a", {
                    href: "javascript:void(0);",
                    onclick: function () {
                        showQuery(log.SQL);
                    }
                }, log.SQL));
                div.appendChild(php_debugger.$new("span", {}, " [ RunTime:" + log.runtime + " ]"));
                return div;
            }
            else {
                return log.SQL + " [ RunTime:" + log.runtime + " ]";
            }
        }
    })(serviceWorker = php_debugger.serviceWorker || (php_debugger.serviceWorker = {}));
})(php_debugger || (php_debugger = {}));
/// <reference path="tabUI.ts" />
/// <reference path="serviceWorker.ts" />
php_debugger.initTabUI();
php_debugger.serviceWorker.doInit();
//# sourceMappingURL=js_worker.js.map