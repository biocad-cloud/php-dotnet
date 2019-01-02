var php_debugger;
(function (php_debugger) {
    /**
     * 按照id来获取得到HTML节点元素
    */
    function $pick(id) {
        return document.getElementById(id.substr(1));
    }
    php_debugger.$pick = $pick;
})(php_debugger || (php_debugger = {}));
/// <reference path="helper.ts" />
var php_debugger;
(function (php_debugger) {
    /**
     * ��ʼ��ҳ�����·��ĵ�������ǩҳ
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
        };
        open.onclick = function () {
            trace.style.display = 'block';
            this.style.display = 'none';
            container.style.display = 'block';
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
        // ��ʾ��һҳ��ǩҳ����������������
        tab_tit[0].click();
    }
})(php_debugger || (php_debugger = {}));
/// <reference path="tabUI.ts" />
php_debugger.initTabUI();
//# sourceMappingURL=js_worker.js.map