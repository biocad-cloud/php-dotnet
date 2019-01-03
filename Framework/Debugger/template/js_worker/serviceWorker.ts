module php_debugger.serviceWorker {

    export const debuggerGuid: string = $pick("#debugger_guid").innerText;
    export const debuggerApi: string = "/index.php?app=php.NET&api=debugger";

    /**
     * 服务器返回来的是大于这个checkpoint数值的所有的后续新增记录
    */
    var checkpoints = {};

    /**
     * 每一秒钟执行一次服务器查询
    */
    export function doInit() {
        // 初始化所有的checkpoint
        Object.keys(<debuggerInfo>{
            SQL: null
        }).forEach(itemName => checkpoints[itemName] = 0);

        setInterval(fetch, 1000);
    }

    /**
     * 对服务器进行调试器输出结果请求
     * 
     * 假设服务器上一定会存在一个``index.php``文件？
    */
    function fetch() {
        $.post(`${debuggerApi}&guid=${debuggerGuid}`, checkpoints, function (info: debuggerInfo) {
            if (checkpoints["SQL"] != info.SQL.lastCheckPoint) {
                checkpoints["SQL"] = info.SQL.lastCheckPoint;
                appendSQL(info.SQL.data);
            }
        });
    }

    function appendSQL(SQLlogs: SQLlog[]) {
        var mysqlLogs = $pick("mysql-logs");

        SQLlogs.forEach(log => mysqlLogs.appendChild($new(
            "li", {
                style: "border-bottom:1px solid #EEE;font-size:14px;padding:0 12px"
            },
            `${log.SQL} [ RunTime:${log.runtime} ]`))
        );
    }

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