<?php

use Moxl\Xec\Action\Message\Composing;
use Moxl\Xec\Action\Message\Paused;
use Moxl\Xec\Action\Message\Publish;

use Moxl\Xec\Action\Muc\GetConfig;
use Moxl\Xec\Action\Muc\SetConfig;
use Moxl\Xec\Action\Muc\SetSubject;

use Respect\Validation\Validator;

class Chat extends WidgetCommon
{
    function load()
    {
        $this->addjs('chat.js');
        //$this->addjs('chat_otr.js');
        $this->addcss('chat.css');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('composing', 'onComposing');
        $this->registerEvent('paused', 'onPaused');
        $this->registerEvent('gone', 'onGone');
        $this->registerEvent('subject', 'onConferenceSubject');
        $this->registerEvent('muc_getconfig_handle', 'onRoomConfig');
        $this->registerEvent('muc_setconfig_handle', 'onRoomConfigSaved');
        //$this->registerEvent('muc_setsubject_handle', 'onRoomSubjectChanged');
        //$this->registerEvent('presence', 'onPresence');
    }

    /*
     * Disabled for the moment, it SPAM a bit too much the user
    function onPresence($packet)
    {
        $contacts = $packet->content;
        if($contacts != null){
            $contact = $contacts[0];

            if($contact->value < 5) {
                $avatar = $contact->getPhoto('s');
                if($avatar == false) $avatar = null;

                $presences = getPresences();
                $presence = $presences[$contact->value];

                Notification::append('presence', $contact->getTrueName(), $presence, $avatar, 4);
            }
        }
    }*/

    function onMessage($packet, $mine = false)
    {
        $message = $packet->content;
        $cd = new \Modl\ContactDAO;

        if($message->session == $message->jidto) {
            $from = $message->jidfrom;

            $contact = $cd->getRosterItem($from);
            if($contact == null)
                $contact = $cd->get($from);
            
            if($contact != null
            && !preg_match('#^\?OTR#', $message->body)
            && $message->type != 'groupchat') {
                $avatar = $contact->getPhoto('s');
                if($avatar == false) $avatar = null;
                Notification::append('chat|'.$from, $contact->getTrueName(), $message->body, $avatar, 4);
            }

            RPC::call('movim_fill', $from.'_state', '');     
        // If the message is from me
        } else {
            $from = $message->jidto;
            $contact = $cd->get();
        }

        $me = $cd->get();
        if($me == null) {
            $me = new \Modl\Contact;
        }

        if(preg_match('#^\?OTR#', $message->body)) {
            if(!$mine) {
                //RPC::call('ChatOTR.receiveMessage', $message->body);
            }
        } else {
            RPC::call('Chat.appendMessage', $this->prepareMessage($message));
        }
        RPC::call('MovimTpl.scrollPanel');
    }

    function onComposing($array)
    {
        $this->setState($array, $this->__('message.composing'));
    }

    function onPaused($array)
    {
        $this->setState($array, $this->__('message.paused'));
    }

    function onGone($array)
    {
        $this->setState($array, $this->__('message.gone'));
    }

    function onConferenceSubject($packet)
    {
        $header = $this->prepareHeaderRoom($packet->content->jidfrom);
        Header::fill($header);
    }

    function onRoomConfig($packet)
    {
        list($config, $room) = array_values($packet->content);

        $view = $this->tpl();
        
        $xml = new \XMPPtoForm();
        $form = $xml->getHTML($config->x->asXML());

        $view->assign('form', $form);
        $view->assign('room', $room);

        Dialog::fill($view->draw('_chat_config_room', true), true);
    }

    function onRoomConfigSaved($packet)
    {
        Notification::append(false, $this->__('chatroom.config_saved'));
    }
/*
    function onRoomSubjectChanged($packet)
    {
        Notification::append(false, $this->__('chatroom.suject_changed'));
    }
*/
    private function setState($array, $message)
    {
        list($from, $to) = $array;
        if($from == $this->user->getLogin()) {
            $jid = $to;
        } else {
            $jid = $from;
        }

        $view = $this->tpl();
        $view->assign('message', $message);

        $html = $view->draw('_chat_state', true);

        RPC::call('movim_fill', $jid.'_state', $html);
        RPC::call('MovimTpl.scrollPanel');
    }

    /**
     * @brief Show the smiley list
     */
    function ajaxSmiley()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_chat_smiley', true));
    }

    /**
     * @brief Get the path of a emoji
     */
    function ajaxSmileyGet($string)
    {
        return prepareString($string, true);
    }

    /**
     * @brief Get a discussion
     * @parem string $jid
     */
    function ajaxGet($jid = null)
    {
        if($jid == null) {
            RPC::call('movim_fill', 'chat_widget', $this->prepareEmpty());
        } else {
            $html = $this->prepareChat($jid);
            
            $header = $this->prepareHeader($jid);
            
            Header::fill($header);
            RPC::call('movim_fill', 'chat_widget', $html);
            RPC::call('MovimTpl.scrollPanel');
            RPC::call('MovimTpl.showPanel');

            $this->prepareMessages($jid);
        }
    }

    /**
     * @brief Get a chatroom
     * @parem string $jid
     */
    function ajaxGetRoom($room)
    {
        if(!$this->validateJid($room)) return;

        $html = $this->prepareChat($room, true);
        
        $header = $this->prepareHeaderRoom($room);
        
        Header::fill($header);
        RPC::call('movim_fill', 'chat_widget', $html);
        RPC::call('MovimTpl.scrollPanel');
        RPC::call('MovimTpl.showPanel');

        $this->prepareMessages($room, true);
    }

    /**
     * @brief Send a message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxSendMessage($to, $message, $muc = false, $resource = false) {
        if($message == '')
            return;
        
        $m = new \Modl\Message();
        $m->session = $this->user->getLogin();
        $m->jidto   = echapJid($to);
        $m->jidfrom = $this->user->getLogin();
        
        $session    = \Sessionx::start();
        
        $m->type    = 'chat';
        $m->resource = $session->resource;
        
        if($muc) {
            $m->type        = 'groupchat';
            $m->resource    = $session->user;
            $m->jidfrom     = $to;
        }
        
        $m->body      = rawurldecode($message);
        $m->published = gmdate('Y-m-d H:i:s');
        $m->delivered = gmdate('Y-m-d H:i:s');

        if(!preg_match('#^\?OTR#', $m->body)) {
            $md = new \Modl\MessageDAO();
            $md->set($m);
        }

        /* Is it really clean ? */
        $packet = new Moxl\Xec\Payload\Packet;
        $packet->content = $m;
        $this->onMessage($packet, true);

        if($resource != false) {
            $to = $to . '/' . $resource;
        }

        // We decode URL codes to send the correct message to the XMPP server
        $m = new Publish;
        $m->setTo($to);
        $m->setContent(htmlspecialchars(rawurldecode($message)));

        if($muc) {
            $m->setMuc();
        }

        $m->request();
    }

    /**
     * @brief Send a "composing" message
     * 
     * @param string $to
     * @return void
     */
    function ajaxSendComposing($to) {
        if(!$this->validateJid($to)) return;

        $mc = new Composing;
        $mc->setTo($to)->request();
    }
    
    /**
     * @brief Send a "paused" message
     * 
     * @param string $to
     * @return void
     */
    function ajaxSendPaused($to) {
        if(!$this->validateJid($to)) return;

        $mp = new Paused;
        $mp->setTo($to)->request();
    }

    /**
     * @brief Configure a room
     *
     * @param string $room
     */
    function ajaxGetRoomConfig($room)
    {
        if(!$this->validateJid($room)) return;

        $gc = new GetConfig;
        $gc->setTo($room)
           ->request();
    }

    /**
     * @brief Save the room configuration
     *
     * @param string $room
     */
    function ajaxSetRoomConfig($data, $room)
    {
        if(!$this->validateJid($room)) return;

        $sc = new SetConfig;
        $sc->setTo($room)
           ->setData($data)
           ->request();
    }

    /**
     * @brief Get the subject form of a chatroom
     */
    function ajaxGetSubject($room)
    {
        if(!$this->validateJid($room)) return;

        $view = $this->tpl();

        $md = new \Modl\MessageDAO;
        $s = $md->getRoomSubject($room);

        $view->assign('room', $room);
        $view->assign('subject', $s);

        Dialog::fill($view->draw('_chat_subject', true));
    }

    /**
     * @brief Change the subject of a chatroom
     */
    function ajaxSetSubject($room, $form)
    {
        if(!$this->validateJid($room)) return;

        $validate_subject = Validator::string()->length(0, 200);
        if(!$validate_subject->validate($form->subject->value)) return;

        $p = new SetSubject;
        $p->setTo($room)
          ->setSubject($form->subject->value)
          ->request();
    }

    /**
     * @brief Prepare the contact header
     * 
     * @param string $jid
     */
    function prepareHeader($jid)
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;

        $cr = $cd->getRosterItem($jid);
        if(isset($cr)) {
            $contact = $cr;
        } else {
            $contact = $cd->get($jid);
        }
        
        $view->assign('contact', $contact);
        $view->assign('jid', $jid);

        return $view->draw('_chat_header', true);
    }

    /**
     * @brief Prepare the contact header
     * 
     * @param string $jid
     */
    function prepareHeaderRoom($room)
    {
        $view = $this->tpl();

        $md = new \Modl\MessageDAO;
        $s = $md->getRoomSubject($room);

        $cd = new \Modl\ConferenceDAO;
        $c = $cd->get($room);

        $pd = new \Modl\PresenceDAO;
        $p = $pd->getMyPresenceRoom($room);

        $view->assign('room', $room);
        $view->assign('subject', $s);
        $view->assign('presence', $p);
        $view->assign('conference', $c);

        return $view->draw('_chat_header_room', true);
    }

    function prepareChat($jid, $muc = false)
    {
        $view = $this->tpl();

        $view->assign('jid', $jid);

        $jid = echapJS($jid);

        $view->assign('composing', $this->call('ajaxSendComposing', "'" . $jid . "'"));
        $view->assign('paused', $this->call('ajaxSendPaused', "'" . $jid . "'"));

        $view->assign('smiley', $this->call('ajaxSmiley'));

        $view->assign('emoji', prepareString('😀'));
        $view->assign('muc', $muc);

        return $view->draw('_chat', true);
    }

    function prepareMessages($jid)
    {
        if(!$this->validateJid($jid)) return;

        $md = new \Modl\MessageDAO();
        $messages = $md->getContact(echapJid($jid), 0, 30);

        $messages = array_reverse($messages);

        foreach($messages as $message) {
            $this->prepareMessage($message);
        }

        $view = $this->tpl();
        $view->assign('jid', $jid);

        $cd = new \Modl\ContactDAO;
        $contact = $cd->get($jid);
        $me = $cd->get();
        if($me == null) {
            $me = new \Modl\Contact;
        }

        $view->assign('contact', $contact);
        $view->assign('me', false);
        $left = $view->draw('_chat_bubble', true);

        $view->assign('contact', $me);
        $view->assign('me', true);
        $right = $view->draw('_chat_bubble', true);

        $room = $view->draw('_chat_bubble_room', true);

        RPC::call('Chat.setBubbles', $left, $right, $room);
        RPC::call('Chat.appendMessages', $messages);
    }

    function prepareMessage(&$message)
    {
        if(isset($message->html)) {
            $message->body = prepareString($message->html);
        } else {
            $message->body = prepareString(htmlentities($message->body , ENT_COMPAT,'UTF-8'));
        }

        if($message->type == 'groupchat') {
            $message->color = stringToColor($message->session.$message->resource.$message->jidfrom.$message->type);
        }

        $message->published = prepareDate(strtotime($message->published));

        return $message;
    }

    function prepareEmpty()
    {
        $view = $this->tpl();
        return $view->draw('_chat_empty', true);
    }

    /**
     * @brief Validate the jid
     *
     * @param string $jid
     */
    private function validateJid($jid)
    {
        $validate_jid = Validator::email()->noWhitespace()->length(6, 60);
        if(!$validate_jid->validate($jid)) return false;
        else return true;
    }

    function display()
    {
        $validate_jid = Validator::email()->length(6, 40);

        $this->view->assign('jid', false);
        if($validate_jid->validate($this->get('f'))) {
            $this->view->assign('jid', $this->get('f'));
        }
    }
}
