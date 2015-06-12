var Chats = {
    refresh: function() {
        var items = document.querySelectorAll('ul#chats_widget_list li:not(.subheader)');
        var i = 0;
        while(i < items.length)
        {
            if(items[i].dataset.jid != null) {
                items[i].onclick = function(e) {
                    Rooms.refresh();

                    Chat_ajaxGet(this.dataset.jid);
                    Chats.reset(items);
                    Notification_ajaxClear('chat|' + this.dataset.jid);
                    Notification.current('chat|' + this.dataset.jid);
                    movim_add_class(this, 'active');

                    MovimTpl.scrollPanel();
                }

                items[i].onmousedown = function(e) {
                    if(e.which == 2) {
                        Notification_ajaxClear('chat|' + this.dataset.jid);
                        Notification.current('chat');
                        Chats_ajaxClose(this.dataset.jid);
                        MovimTpl.hidePanel();
                    }
                }
            }

            movim_remove_class(items[i], 'active');

            i++;
        }

        Notification_ajaxGet();
        
        //Chat_ajaxGet();

        /*if(window.innerWidth > 1024 && !MovimTpl.isPanel()) {
            Notification.notifs_key = 'chat|' + items[0].dataset.jid;
            Notification_ajaxCurrent(Notification.notifs_key);
            items[0].click();
        }*/
    },

    reset: function(list) {
        for(i = 0; i < list.length; i++) {
            movim_remove_class(list[i], 'active');
        }
    }
}

movim_add_onload(function(){    
    Notification.current('chat');
});

MovimWebsocket.attach(function() {
    Chats.refresh();
});
