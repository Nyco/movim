<?php

use Moxl\Xec\Action\Message\Publish;

class Chat extends WidgetCommon
{
    function load()
    {
        $this->addjs('chat.js');
        $this->registerEvent('message', 'onMessage');
    }

    function onMessage($packet)
    {
        $message = $packet->content;

        \movim_log('HOP');
        \movim_log('SESSION '.$message->session);
        \movim_log('TO '.$message->jidto);
        \movim_log('FROM '.$message->jidfrom);

        // If the message is from me
        if($message->session == $message->jidto) {
            $from = $message->jidfrom;
        } else {
            $from = $message->jidto;
        }

        RPC::call('movim_fill', $from.'_messages', $this->prepareMessages($from));
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
     * @brief Get a discussion
     * @parem string $jid
     */
    function ajaxGet($jid)
    {
        $html = $this->prepareChat($jid);
        
        $header = $this->prepareHeader($jid);
        
        Header::fill($header);
        RPC::call('movim_fill', 'chat_widget', $html);
        RPC::call('MovimTpl.scrollPanel');
    }

    /**
     * @brief Send a message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxSendMessage($to, $message, $muc = false, $ressource = false) {
        if($message == '')
            return;
        
        $m = new \Modl\Message();
        $m->session = $this->user->getLogin();
        $m->jidto   = echapJid($to);
        $m->jidfrom = $this->user->getLogin();
        
        $session    = \Sessionx::start();
        
        $m->type    = 'chat';
        $m->ressource = $session->ressource;
        
        if($muc) {
            $m->type        = 'groupchat';
            $m->ressource   = $session->user;
            $m->jidfrom     = $to;
        }
        
        $m->body      = rawurldecode($message);
        $m->published = date('Y-m-d H:i:s');
        $m->delivered = date('Y-m-d H:i:s');
        
        $md = new \Modl\MessageDAO();
        $md->set($m);

        /* Is it really clean ? */
        $packet = new Moxl\Xec\Payload\Packet;
        $packet->content = $m;
        $this->onMessage($packet, true);

        if($ressource != false) {
            $to = $to . '/' . $ressource;
        }

        // We decode URL codes to send the correct message to the XMPP server
        $m = new Publish;
        $m->setTo($to);
        $m->setContent(htmlspecialchars(rawurldecode($message)));

        /*if($muc) {
            $m->setMuc();
        }*/

        $m->request();
    }

    function prepareHeader($jid)
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        
        $view->assign('contact', $cd->get($jid));
        $view->assign('jid', $jid);

        return $view->draw('_chat_header', true);
    }

    function prepareChat($jid)
    {
        $view = $this->tpl();
        
        $view->assign('jid', $jid);
        $view->assign('messages', $this->prepareMessages($jid));

        $view->assign(
            'send',
            $this->call(
                'ajaxSendMessage',
                "'" . $jid . "'",
                "Chats.sendMessage(this, '" . $jid . "')")
            );
        $view->assign('smiley', $this->call('ajaxSmiley'));

        return $view->draw('_chat', true);
    }

    function prepareMessages($jid)
    {
        $md = new \Modl\MessageDAO();
        $messages = $md->getContact(echapJid($jid), 0, 10);
        $messages = array_reverse($messages);
        
        $cd = new \Modl\ContactDAO;
        $view = $this->tpl();
        
        $view->assign('jid', $jid);
        $view->assign('contact', $cd->get($jid));
        $view->assign('me', $cd->get());
        $view->assign('messages', $messages);

        return $view->draw('_chat_messages', true);
    }

    function display()
    {

    }
}
