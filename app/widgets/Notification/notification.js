var DesktopNotification = Notification;

var Notification = {
    inhibed : false,
    focused : false,
    tab_counter1 : 0,
    tab_counter2 : 0,
    tab_counter1_key : 'chat',
    tab_counter2_key : 'news',
    document_title : document.title,
    notifs_key : '',
    favicon : null,

    inhibit : function(sec) {
        Notification.inhibed = true;

        if(sec == null) sec = 5;

        setTimeout(function() {
                Notification.inhibed = false;
            },
            sec*1000);
    },
    refresh : function(keys) {
        var counters = document.querySelectorAll('.counter');
        for(i = 0; i < counters.length; i++) {
            var n = counters[i];
            if(n.dataset.key != null
            && keys[n.dataset.key] != null) {
                n.innerHTML = keys[n.dataset.key];
            }
        }

        for(var key in keys) {
            var counter = keys[key];
            Notification.setTab(key, counter);
        }

        Notification.displayTab();
    },
    counter : function(key, counter) {
        var counters = document.querySelectorAll('.counter');
        for(i = 0; i < counters.length; i++) {
            var n = counters[i];
            if(n.dataset.key != null
            && n.dataset.key == key) {
                n.innerHTML = counter;
            }
        }

        Notification.setTab(key, counter);
        Notification.displayTab();
    },
    setTab : function(key, counter) {
        if(counter == '') counter = 0;

        if(Notification.tab_counter1_key == key) {
            Notification.tab_counter1 = counter;
        }
        if(Notification.tab_counter2_key == key) {
            Notification.tab_counter2 = counter;
        }
    },
    displayTab : function() {
        if(Notification.tab_counter1 == 0 && Notification.tab_counter2 == 0) {
            document.title = Notification.document_title;
            Notification.favicon.badge(0);
        } else {
            Notification.favicon.badge(Notification.tab_counter1 + Notification.tab_counter2);
            document.title = '(' + Notification.tab_counter1 + '/' + Notification.tab_counter2 + ') ' + Notification.document_title;
        }
    },
    current : function(key) {
        Notification.notifs_key = key;
        Notification_ajaxCurrent(Notification.notifs_key);
    },
    toast : function(html) {
        target = document.getElementById('toast');
        
        if(target) {
            target.innerHTML = html;
        }
        
        setTimeout(function() {
            target = document.getElementById('toast');
            target.innerHTML = '';
            },
            3000);
    },
    snackbar : function(html, time) {
        if(Notification.inhibed == true) return;

        target = document.getElementById('snackbar');
        
        if(target) {
            target.innerHTML = html;
        }
        
        setTimeout(function() {
            target = document.getElementById('snackbar');
            target.innerHTML = '';
            },
            time*1000);
    },
    desktop : function(title, body, picture) {
        if(Notification.inhibed == true
        || Notification.focused) return;

        var notification = new DesktopNotification(title, { icon: picture, body: body });
    }
}

MovimWebsocket.attach(function() {
    Notification.favicon = new Favico({
        animation: 'none',
        fontStyle: 'normal',
        bgColor: '#FF5722'
    });
    Notification.document_title = document.title;
    Notification_ajaxGet();
    Notification.current(Notification.notifs_key);
});

document.onblur = function() {
    Notification.focused = false;
    Notification_ajaxCurrent('blurred');
}
document.onfocus = function() {
    Notification.focused = true;
    Notification.current(Notification.notifs_key);
    Notification_ajaxClear(Notification.notifs_key);
}

/*
window.addEventListener('load', function () {
    DesktopNotification.requestPermission(function (status) {
    // This allows to use Notification.permission with Chrome/Safari
    if(DesktopNotification.permission !== status) {
        DesktopNotification.permission = status;
    }
  });
});
*/
