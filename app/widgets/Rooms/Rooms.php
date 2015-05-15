<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark\Get;
use Moxl\Xec\Action\Bookmark\Set;
use Moxl\Xec\Action\Presence\Unavailable;

use Respect\Validation\Validator;

class Rooms extends WidgetBase
{
    function load()
    {
        $this->addjs('rooms.js');
        $this->registerEvent('bookmark_set_handle', 'onBookmark');
        $this->registerEvent('presence_muc_handle', 'onConnected');
        $this->registerEvent('presence_unavailable_handle', 'onDisconnected');
    }

    function onBookmark()
    {
        RPC::call('movim_fill', 'rooms_widget', $this->prepareRooms());
        Notification::append(null, $this->__('bookmarks.updated'));
        RPC::call('Rooms.refresh');
        RPC::call('MovimTpl.hidePanel');
    }

    function onConnected()
    {
        RPC::call('movim_fill', 'rooms_widget', $this->prepareRooms());
        Notification::append(null, $this->__('chatrooms.connected'));
        RPC::call('Rooms.refresh');
    }

    function onDisconnected()
    {
        // We reset the Chat view
        $c = new Chat();
        $c->ajaxGet();

        RPC::call('movim_fill', 'rooms_widget', $this->prepareRooms());
        Notification::append(null, $this->__('chatrooms.disconnected'));
        RPC::call('Rooms.refresh');
    }

    /**
     * @brief Display the add room form
     */
    function ajaxAdd()
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $view->assign('me', $cd->get());

        Dialog::fill($view->draw('_rooms_add', true));
    }

    /**
     * @brief Display the remove room confirmation
     */
    function ajaxRemoveConfirm($room)
    {
        if(!$this->validateRoom($room)) return;

        $view = $this->tpl();

        $view->assign('room', $room);

        Dialog::fill($view->draw('_rooms_remove', true));
    }

    /**
     * @brief Display the room list
     */
    function ajaxList($room)
    {
        if(!$this->validateRoom($room)) return;

        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $view->assign('list', $cd->getPresences($room));

        Dialog::fill($view->draw('_rooms_list', true), true);
    }

    /**
     * @brief Remove a room
     */
    function ajaxRemove($room)
    {
        if(!$this->validateRoom($room)) return;

        $cd = new \modl\ConferenceDAO();
        $cd->deleteNode($room);
        
        $this->setBookmark();
    }

    /**
     * @brief Join a chatroom
     */
    function ajaxJoin($room, $nickname = false)
    {
        if(!$this->validateRoom($room)) return;

        $p = new Muc;
        $p->setTo($room);

        if($nickname == false) {
            $s = Session::start();
            $nickname = $s->get('username');
        }

        if($nickname == false || $nickname == null) {
            $session = \Sessionx::start();
            $nickname = $session->username;
        }

        $p->setNickname($nickname);

        $p->request();
    }

    /**
     * @brief Exit a room
     *
     * @param string $room
     */
    function ajaxExit($room)
    {
        if(!$this->validateRoom($room)) return;

        $s = Session::start();
        $resource = $s->get('username');

        if($resource == null) {
            $session = \Sessionx::start();
            $resource = $session->username;
        }

        $pu = new Unavailable;
        $pu->setTo($room)
           ->setResource($resource)
           ->setMuc()
           ->request();
    }

    /**
     * @brief Confirm the room add
     */
    function ajaxChatroomAdd($form) 
    {
        if(!filter_var($form['jid'], FILTER_VALIDATE_EMAIL)) {
            Notification::append(null, $this->__('chatrooms.bad_id'));
        } elseif(trim($form['name']) == '') {
            Notification::append(null, $this->__('chatrooms.empty_name'));
        } else {
            $item = array(
                    'type'      => 'conference',
                    'name'      => $form['name'],
                    'autojoin'  => $form['autojoin'],
                    'nick'      => $form['nick'],
                    'jid'       => $form['jid']);   
            $this->setBookmark($item);
            RPC::call('Dialog.clear');
        }
    }
    
    public function setBookmark($item = false) 
    {
        $arr = array();

        if($item) {
            array_push($arr, $item);
        }
        
        $sd = new \modl\SubscriptionDAO();
        $cd = new \modl\ConferenceDAO();

        foreach($sd->getSubscribed() as $s) {
            array_push($arr,
                array(
                    'type'      => 'subscription',
                    'server'    => $s->server,
                    'title'     => $s->title,
                    'subid'     => $s->subid,
                    'tags'      => unserialize($s->tags),
                    'node'      => $s->node));   
        }

        foreach($cd->getAll() as $c) {
            array_push($arr,
                array(
                    'type'      => 'conference',
                    'name'      => $c->name,
                    'autojoin'  => $c->autojoin,
                    'nick'      => $c->nick,
                    'jid'       => $c->conference)); 
        }

        
        $b = new Set;
        $b->setArr($arr)
          ->setTo($this->user->getLogin())
          ->request();
    }

    function checkConnected($room, $resource = false)
    {
        if(!$this->validateRoom($room)) return;
        if($resource && !$this->validateResource($resource)) {
            Notification::append(null, $this->__('chatrooms.bad_id'));
            return;
        }

        $pd = new \modl\PresenceDAO();

        if($resource == false) {
            $session = \Sessionx::start();
            $resource = $session->user;
        }

        $presence = $pd->getPresence($room, $resource);

        if($presence != null) {
            return true;
        } else {
            return false;
        }
    }

    function prepareRooms()
    {
        $view = $this->tpl();
        $cod = new \modl\ConferenceDAO();
        $view->assign('conferences', $cod->getAll());
        $view->assign('room', $this->get('r'));

        return $view->draw('_rooms', true);
    }

    /**
     * @brief Validate the room 
     *
     * @param string $room
     */
    private function validateRoom($room)
    {
        $validate_server = Validator::email()->noWhitespace()->length(6, 40);
        if(!$validate_server->validate($room)) return false;
        else return true;
    }

    /**
     * @brief Validate the resource 
     *
     * @param string $resource
     */
    private function validateResource($resource)
    {
        $validate_resource = Validator::string()->length(6, 40);
        if(!$validate_resource->validate($resource)) return false;
        else return true;
    }

    function display()
    {
        $this->view->assign('list', $this->prepareRooms());
    }
}
