module php_debugger {

    /**
     * 按照id来获取得到HTML节点元素
    */
    export function $pick(id: string): HTMLElement {
        return document.getElementById(id.substr(1));
    }

    export function $new(tagName: string, attrs?: {
        style?: string
    }, html: string = null): HTMLElement {
        var node = document.createElement(tagName);

        if (attrs) {
            Object.keys(attrs).forEach(name => node.setAttribute(name, attrs[name]));
        }
        if (html) {
            node.innerHTML = html;
        }

        return node;
    }
}


