function removeDiff(id, html, id2) {
    target = document.getElementById(id);
    if(target) {
        target.insertAdjacentHTML('beforeend', html);

        var nodes = target.childNodes;

        for(i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            setTimeout(function() {
                n.parentNode.removeChild(n);
                },
                6000);
        }
    }
}
