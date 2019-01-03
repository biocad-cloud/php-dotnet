module php_debugger.serviceWorker {

    export const debuggerGuid: string = $pick("#debugger_guid").innerText;
    export const debuggerApi: string = "/index.php?app=php.NET&api=debugger";

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
    export const workerInterval = 5000;

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
    }

    export function StartWorker() {
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
        $.post(`${debuggerApi}&guid=${debuggerGuid}`, checkpoints, function (info: debuggerInfo) {
            if (checkpoints["SQL"] != info.SQL.lastCheckPoint) {
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
            `${log.SQL} [ RunTime:${log.runtime} ]`))
        );
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