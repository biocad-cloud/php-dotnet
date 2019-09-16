/// <reference path="helper.ts" />

module php_debugger {

    /**
     * 初始化页面最下方的调试器标签页
    */
    export function initTabUI() {
        var open = $pick('#think_page_trace_open');
        var close: HTMLElement = <any>$pick('#think_page_trace_close_button');
        var trace = $pick('#think_page_trace_tab');
        var container: HTMLElement = <any>$pick('#think_page_trace_close');
        var closeTable: HTMLElement = <any>$pick("#mysql_close");
        var closeDebuggerTab = function () {
            trace.style.display = 'none';
            container.style.display = 'none';
            open.style.display = 'block';

            serviceWorker.StopWorker();
        };

        open.onclick = function () {
            trace.style.display = 'block';
            open.style.display = 'none';
            container.style.display = 'block';

            serviceWorker.StartWorker();
        }
        close.onclick = closeDebuggerTab;
        closeTable.onclick = function () {
            $pick("#mysql-query-display-page").style.display = "none";
        }

        attachTabSwitch();
        closeDebuggerTab();
    }

    const tab_cont: string = "think_page_trace_tab_cont";

    function attachTabSwitch() {
        var tab: HTMLElement = null;
        var contentTab: HTMLElement;
        var contentTabs = $pick(`#${tab_cont}`).getElementsByClassName(tab_cont);
        var tab_tit = $pick('#think_page_trace_tab_tit').getElementsByTagName('span');

        for (var i: number = 0; i < tab_tit.length; i++) {
            tab = tab_tit[i];
            tab.onclick = (function (i) {
                return function () {
                    for (var j = 0; j < contentTabs.length; j++) {
                        contentTab = <any>contentTabs[j];
                        contentTab.style.display = 'none';
                        tab_tit[j].style.color = '#999';
                    }
                    contentTab = <any>contentTabs[i];
                    contentTab.style.display = 'block';
                    tab_tit[i].style.color = '#000';

                    $(".jsonview").show();
                    $(".jsonview-container").show();
                }
            })(i);
        }

        // 显示第一页标签页：调试器参数概览
        tab_tit[0].click();
    }
}