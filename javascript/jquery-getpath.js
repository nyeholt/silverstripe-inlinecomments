/**
 * Unsure of license - please see
 * 
 * http://efreedom.com/Question/1-2068272/Getting-JQuery-Selector-Element
 */

jQuery.fn.getPath = function () {
    if (this.length != 1) throw 'Requires one element.';
    var path, node = this;

    while (node.length) {
        var realNode = node[0], name = realNode.nodeName.toLowerCase();
        if (!name) break;
        name = name.toLowerCase();

        var parent = node.parent();

        var siblings = parent.children(name);
        if (siblings.length > 1) { 
            name += ':eq(' + siblings.index(realNode) + ')';
        }

        path = name + (path ? '>' + path : '');
        node = parent;
    }

    return path;
};

