<?php

use Moxl\Xec\Action\Presence\Muc;
use Moxl\Xec\Action\Bookmark\Get;
use Moxl\Xec\Action\Bookmark\Set;

class Chats extends WidgetCommon
{
    function load()
    {
        $this->addjs('chats.js');
        $this->registerEvent('carbons', 'onMessage');
        $this->registerEvent('message', 'onMessage');
        //$this->registerEvent('bookmark_set_handle', 'onBookmark');
        $this->registerEvent('presence', 'onPresence', 'chat');
    }

    function onMessage($packet)
    {
        $message = $packet->content;

        if($message->type != 'groupchat') {
            // If the message is from me
            if($message->session == $message->jidto) {
                $from = $message->jidfrom;
            } else {
                $from = $message->jidto;
            }

            $chats = Cache::c('chats');
            if(!array_key_exists($from, $chats)) {
                $this->ajaxOpen($from);
            } else {
                // TODO notification overwrite issue
                RPC::call('movim_replace', $from.'_chat_item', $this->prepareChat($from));
                RPC::call('Chats.refresh');

                $n = new Notification;
                $n->ajaxGet();
            }
        }
    }

    function onPresence($packet)
    {
        $contacts = $packet->content;
        if($contacts != null){
            $c = $contacts[0];
            $chats = Cache::c('chats');
            if(array_key_exists($c->jid, $chats)) {
                RPC::call('movim_replace', $c->jid.'_chat_item', $this->prepareChat($c->jid));
                RPC::call('Chats.refresh');

                $n = new Notification;
                $n->ajaxGet();
            }
        }
    }

    function ajaxOpen($jid)
    {
        $chats = Cache::c('chats');
        if($chats == null) $chats = array();

        if(!array_key_exists($jid, $chats)
        && $jid != $this->user->getLogin()
        && $jid != '') {
            $chats[$jid] = 1;
        } else {
            unset($chats[$jid]);
        }

        $chats[$jid] = 1;

        Cache::c('chats', $chats);

        RPC::call('movim_prepend', 'chats_widget_list', $this->prepareChat($jid));
        RPC::call('Chats.refresh');
    }

    function ajaxClose($jid)
    {
        $chats = Cache::c('chats');
        unset($chats[$jid]);
        Cache::c('chats', $chats);

        RPC::call('movim_delete', $jid.'_chat_item');

        RPC::call('Chats.refresh');
        RPC::call('Chat.empty');
        RPC::call('MovimTpl.hidePanel');
    }

    /**
     * @brief Display the add chat form
     */
    function ajaxAdd()
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $view->assign('top', $cd->getTop(10));

        Dialog::fill($view->draw('_chats_add', true), true);
    }

    /**
     * @brief Display the extended list
     */
    function ajaxAddExtend()
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $contacts = $cd->getRosterSimple();
        $view->assign('contacts', $contacts);

        RPC::call('movim_fill', 'add_extend', $view->draw('_chats_add_extend', true));
    }

    function prepareChats()
    {
        $chats = Cache::c('chats');

        $view = $this->tpl();
        $view->assign('chats', array_reverse($chats));
        
        return $view->draw('_chats', true);
    }

    function prepareChat($jid)
    {
        $view = $this->tpl();

        $cd = new \Modl\ContactDAO;
        $md = new \modl\MessageDAO();

        $presencestxt = getPresencesTxt();

        $cr = $cd->getRosterItem($jid);
        if(isset($cr)) {
            if($cr->value != null) {
                $view->assign('presence', $presencestxt[$cr->value]);
            }
            $view->assign('contact', $cr);
        } else {
            $view->assign('contact', $cd->get($jid));
        }

        $m = $md->getContact($jid, 0, 1);
        if(isset($m)) {
            $view->assign('message', $m[0]);
        }

        return $view->draw('_chats_item', true);
    }

    function display()
    {
        $this->view->assign('list', $this->prepareChats());
    }
}
