module php_debugger.serviceWorker {

    export const debuggerGuid: string = $pick("#debugger_guid").innerText;
    export const debuggerApi: string = "/index.php?app=php.NET&api=debugger";
    export const debuggerSqlApi: string = "/index.php?app=php.NET&api=sql_query";

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
    export const workerInterval = 1000;

    /**
     * 每一秒钟执行一次服务器查询
    */
    export function doInit() {
        // 初始化所有的checkpoint
        Object.keys(<debuggerInfo>{
            SQL: null
        }).forEach(itemName => checkpoints[itemName] = 0);

        // doinit函数是在脚本起始运行的时候被调用的
        // 但是最开始的时候调试器的标签页还没有打开
        // 所以没有必要一开始就启动后台线程
        // serviceWorker.StartWorker();
        fetch();
    }

    export function StartWorker() {
        checkpoints["guid"] = debuggerGuid;

        try {
            serviceWorker.StopWorker();
        } catch (ex) {
            // do nothing
            // just ignore the error
        }

        timer = setInterval(fetch, workerInterval);
    }

    export function StopWorker() {
        clearInterval(timer);
    }

    /**
     * 对服务器进行调试器输出结果请求
     * 
     * 假设服务器上一定会存在一个``index.php``文件？
    */
    export function fetch() {
        $.post(debuggerApi, checkpoints, function (info: debuggerInfo) {
            if (info.SQL.lastCheckPoint > 0 && checkpoints["SQL"] != info.SQL.lastCheckPoint) {
                checkpoints["SQL"] = info.SQL.lastCheckPoint;
                appendSQL(info.SQL.data);
            }
        });
    }

    function appendSQL(SQLlogs: SQLlog[]) {
        var mysqlLogs = $pick("#mysql-logs");

        SQLlogs.forEach(log => mysqlLogs.appendChild($new(
            "li", {
                style: "border-bottom:1px solid #EEE;font-size:14px;padding:0 12px"
            },
            sql(log)))
        );
    }

    export function showQuery(sql: string) {
        $pick("#mysql").innerHTML = sql;
        $.post(debuggerSqlApi, {
            sql: sql,
            guid: debuggerGuid
        }, function (table: IMsg<{}[]>) {
            let display = $pick("#mysql-query-display");

            $pick("#mysql-logs").style.display = "none";
            $pick("#mysql-query-display-page").style.display = "block";

            display.innerHTML = "";

            if (table.code == 0) {
                // table rows data
                let rowDatas: {}[] = <{}[]>table.info;
                let titles: string[] = Object.keys(rowDatas[0]);
                let thead: HTMLElement = $new("thead");
                let tbody: HTMLElement = $new("tbody");
                let r: HTMLElement;

                for (let td of titles) {
                    thead.appendChild($new("th", {}, td));
                }

                for (let row of rowDatas) {
                    r = $new("tr");

                    for (var i: number = 0; i < titles.length; i++) {
                        r.appendChild($new("td", {}, row[titles[i]]));
                    }

                    tbody.appendChild(r);
                }

                display.appendChild(thead);
                display.appendChild(tbody);

            } else {
                // error message
                display.innerHTML = `<span style="font-style: bold; color: red">${<string>table.info}</span>`;
            }
        });
    }

    function sql(log: SQLlog): HTMLElement | string {
        let sql = log.SQL;

        if (sql.indexOf("SELECT ") == 0) {
            let div = $new("div", {}, "");

            div.appendChild($new("a", {
                href: "javascript:void(0);",
                onclick: function () {
                    showQuery(log.SQL);
                }
            }, log.SQL));
            div.appendChild($new("span", {}, ` [ RunTime:${log.runtime} ]`));

            return div;
        } else {
            return `${log.SQL} [ RunTime:${log.runtime} ]`;
        }
    }

    export interface IMsg<T> {
        code: number;
        info: string | T;
    }

    export interface SQLlog {
        time: string;
        SQL: string;
        runtime: string;
    }

    export interface debuggerInfo {
        SQL: checkPointValue<SQLlog>;
    }

    export interface checkPointValue<T> {
        lastCheckPoint: number;
        data: T[];
    }
}