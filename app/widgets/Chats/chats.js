var Chats = {
    refresh: function() {
        var items = document.querySelectorAll('ul#chats_widget_list li:not(.subheader)');
        var i = 0;
        while(i < items.length)
        {
            items[i].onclick = function(e) {
                MovimTpl.showPanel();
                Chat_ajaxGet(this.dataset.jid);
                Chats.reset(items);
                movim_add_class(this, 'active');
            }
            i++;
        }

        items[0].click();
    },

    reset: function(list) {
        for(i = 0; i < list.length; i++) {
            movim_remove_class(list[i], 'active');
        }
    }
}

MovimWebsocket.attach(function() {
    Chats.refresh();
});
