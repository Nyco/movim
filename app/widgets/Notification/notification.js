var DesktopNotification = Notification;

var Notification = {
    inhibed : false,
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
    },
    counter : function(key, counter) {
        var counters = document.querySelectorAll('.counter');
        for(i = 0; i < counters.length; i++) {
            var n = counters[i];
            if(n.dataset.key != null
            && n.dataset.key == key) {
                //setTimeout(function() {
                    n.innerHTML = counter;
                //}, 2000);
            }
        }
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
        if(Notification.inhibed == true) return;

        var notification = new DesktopNotification(title, { icon: picture, body: body });
    }
}

MovimWebsocket.attach(function() {
    Notification_ajaxGet();
    Notification_ajaxCurrent('');
});

/**
 * Set a global var for widgets to see if document is focused
 */
var document_focus = true;
var document_title = document.title;
var messages_cpt = 0;
var posts_cpt = 0;
document.onblur = function() { document_focus = false; }
document.onfocus = function() { document_focus = true; messages_cpt = 0; movim_show_cpt(); }

function movim_show_cpt() {
    if(messages_cpt == 0 && posts_cpt == 0)
        document.title = document_title;
    else
        document.title = '(' + messages_cpt + '/' + posts_cpt + ') ' + document_title;
}

/**
 * @brief Increment the counter of the title
 */
function movim_title_inc() {
	messages_cpt++;
	movim_show_cpt();
}

function movim_posts_unread(cpt) {
    posts_cpt = cpt;
    movim_show_cpt();
}

function movim_desktop_notification(title, body, image) {
    var notification = new Notification(title, { icon: image, body: body });
    //notification.onshow = function() { setTimeout(this.cancel(), 15000); }
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
