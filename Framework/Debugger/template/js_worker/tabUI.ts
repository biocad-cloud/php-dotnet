(() => {

    function $pick(id) {
        return document.getElementById(id.substr(1));
    }

    var tab_tit = $pick('#think_page_trace_tab_tit').getElementsByTagName('span');
    var tab_cont = $pick('#think_page_trace_tab_cont').getElementsByClassName("think_page_trace_tab_cont");
    var open = $pick('#think_page_trace_open');
    var close = $pick('#think_page_trace_close').childNodes[1];
    var trace = $pick('#think_page_trace_tab');
    var tab = null;

    open.onclick = function () {
        trace.style.display = 'block';
        this.style.display = 'none';
        close.parentNode.style.display = 'block';
    }
    close.onclick = function () {
        trace.style.display = 'none';
        this.parentNode.style.display = 'none';
        open.style.display = 'block';
    }

    for (var i = 0; i < tab_tit.length; i++) {
        tab = tab_tit[i];
        tab.onclick = (function (i) {
            return function () {
                for (var j = 0; j < tab_cont.length; j++) {
                    tab_cont[j].style.display = 'none';
                    tab_tit[j].style.color = '#999';
                }
                tab_cont[i].style.display = 'block';
                tab_tit[i].style.color = '#000';

                $(".jsonview").show();
                $(".jsonview-container").show();
            }
        })(i)
    }

    close.onclick();
    tab_tit[0].click();
})();