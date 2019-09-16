module php_debugger {

    export interface Sub {
        (): void;
    }

    /**
     * 按照id来获取得到HTML节点元素
    */
    export function $pick(id: string): HTMLElement {
        return document.getElementById(id.substr(1));
    }

    export function $new(tagName: string, attrs?: {
        style?: string,
        href?: string,
        onclick?: Sub
    }, html: string | HTMLElement = null): HTMLElement {
        var node = document.createElement(tagName);

        if (attrs) {
            Object.keys(attrs)
                .forEach(function (name) {
                    if (name == "onclick") {
                        node.onclick = attrs[name];
                    } else {
                        node.setAttribute(name, attrs[name]);
                    }
                });
        }
        if (html) {
            if (typeof html == "string") {
                node.innerHTML = html;
            } else {
                node.appendChild(html);
            }
        }

        return node;
    }
}


