<?php

class Notification extends WidgetCommon
{
    function load()
    {
        $this->addjs('notification.js');
    }

    /**
     * @brief Notify something
     *
     * @param string $key The key to group the notifications
     * @param string $title The displayed title
     * @param string $body The displayed body
     * @param string $body The displayed URL
     * @param integer $time The displayed time (in secondes)
     * @param integer $action An action
     * @return void
     */    
    static function append($key = null, $title, $body = null, $picture = null, $time = 2, $action = null)
    {
        // In this case we have an action confirmation
        if($key == null) {
            RPC::call('Notification.toast', $title);
            return;
        }
        
        $session = Session::start();
        $notifs = $session->get('notifs');

        $notifs_key = $session->get('notifs_key');
        if($notifs_key != null && $key == $notifs_key) return;

        if($notifs == null) $notifs = array();

        $explode = explode('|', $key);
        $first = reset($explode);
        
        if(array_key_exists($first, $notifs)) {
            $notifs[$first]++;
        } else {
            $notifs[$first] = 1;
        }

        RPC::call('Notification.counter', $first, $notifs[$first]);

        if(array_key_exists($key, $notifs)) {
            $notifs[$key]++;
        } else {
            $notifs[$key] = 1;
        }

        RPC::call('Notification.counter', $key, $notifs[$key]);

        $n = new Notification;
        RPC::call('Notification.snackbar', $n->prepareSnackbar($title, $body, $picture), $time);
        RPC::call('Notification.desktop', $title, $body, $picture);

        $session->set('notifs', $notifs);
    }

    /**
     * @brief Clear the counter of a key
     *
     * @param string $key The key to group the notifications
     * @return void
     */  
    function ajaxClear($key)
    {
        $session = Session::start();
        $notifs = $session->get('notifs');

        if($notifs != null && array_key_exists($key, $notifs)) {
            $counter = $notifs[$key];
            unset($notifs[$key]);

            RPC::call('Notification.counter', $key, '');

            $explode = explode('|', $key);
            $first = reset($explode);

            if(array_key_exists($first, $notifs)) {
                $notifs[$first] = $notifs[$first] - $counter;

                if($notifs[$first] <= 0) {
                    unset($notifs[$first]);
                    RPC::call('Notification.counter', $first, '');
                } else {
                    RPC::call('Notification.counter', $first, $notifs[$first]);
                }
            }
        }

        $session->set('notifs', $notifs);
    }

    /**
     * @brief Get akk the keys
     * @return void
     */  
    function ajaxGet()
    {
        $session = Session::start();
        $notifs = $session->get('notifs');
        if($notifs != null) RPC::call('Notification.refresh', $notifs);
    }

    /**
     * @brief Set the current used key (to prevent notifications on current view)
     *
     * @param string $key
     * @return void
     */  
    function ajaxCurrent($key)
    {
        $session = Session::start();
        $session->set('notifs_key', $key);
    }

    function prepareSnackbar($title, $body = false, $picture = false)
    {
        $view = $this->tpl();
        
        $view->assign('title', $title);
        $view->assign('body', $body);
        $view->assign('picture', $picture);

        return $view->draw('_notification', true);
    }
    
    /*
    static function appendNotification($message, $type = 'info')
    {
        $id = md5($message.$type);

        switch($type) {
            case 'success':
                $icon = 'fa-check-circle';
                break;
            case 'info':
                $icon = 'fa-info-circle';
                break;
            case 'warning':
                $icon = 'fa-warning';
                break;
            case 'error':
                $icon = 'fa-times-circle';
                break;
            default:
                $icon = 'fa-info-circle';
                break;
        }*/
        
        /*$html = '
            <div class="notif notificationAnim '.$type.'" id="'.$id.'">
                <i class="fa '.$icon.'"></i> '.$message.'
            </div>';*/
    /*    $html = $message;

        RPC::call('removeDiff', 'toast', $html, $id);
    }

    function onPubsubError($error) {
        Notification::appendNotification($error, 'error');
    }

    function onMoxlError($arr) {
        Notification::appendNotification($arr[1], 'error');
    }*/
}
