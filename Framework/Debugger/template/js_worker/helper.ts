module php_debugger {

    /**
     * 按照id来获取得到HTML节点元素
    */
    export function $pick(id: string): HTMLElement {
        return document.getElementById(id.substr(1));
    }
}


