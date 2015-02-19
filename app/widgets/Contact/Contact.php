<?php

use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Action\Vcard\Get;

class Contact extends WidgetCommon
{
    function load()
    {
        $this->registerEvent('roster_updateitem_handle', 'onContactEdited');
        $this->registerEvent('vcard_get_handle', 'onVcardReceived');
    }

    public function onVcardReceived($packet)
    {
        $contact = $packet->content;
        $this->ajaxGetContact($contact->jid);
    }

    public function onContactEdited($packet)
    {
        Notification::append(null, $this->__('edit.updated'));
    }

    function ajaxClear()
    {
        $html = $this->prepareEmpty();
        RPC::call('movim_fill', 'contact_widget', $html);
    }

    function ajaxGetContact($jid)
    {
        $html = $this->prepareContact($jid);
        $header = $this->prepareHeader($jid);
        
        Header::fill($header);
        RPC::call('movim_fill', 'contact_widget', $html);
        RPC::call('MovimTpl.showPanel');
    }

    function ajaxEditSubmit($form)
    {
        $rd = new UpdateItem;
        $rd->setTo(echapJid($form['jid']))
           ->setFrom($this->user->getLogin())
           ->setName(htmlspecialchars($form['alias']))
           ->setGroup(htmlspecialchars($form['group']))
           ->request();
    }

    function ajaxRefreshVcard($jid)
    {
        $r = new Get;
        $r->setTo(echapJid($jid))->request();
    }

    function ajaxEditContact($jid)
    {
        $rd = new \Modl\RosterLinkDAO();
        $groups = $rd->getGroups();
        $rl     = $rd->get($jid);

        $view = $this->tpl();

        if(isset($rl)) {
            $view->assign('submit', 
                $this->call(
                    'ajaxEditSubmit', 
                    "movim_parse_form('manage')"));
            $view->assign('contact', $rl);
            $view->assign('groups', $groups);
        }

        Dialog::fill($view->draw('_contact_edit', true));
    }

    function ajaxChat($jid)
    {
        $c = new Chats;
        $c->ajaxOpen($jid);
        
        RPC::call('movim_redirect', $this->route('chat'));
    }

    function ajaxDeleteContact($jid)
    {
        $view = $this->tpl();

        $view->assign('jid', $jid);

        Dialog::fill($view->draw('_contact_delete', true));
    }

    function prepareHeader($jid)
    {
        $cd = new \Modl\ContactDAO;
        $cr  = $cd->getRosterItem($jid);

        $view = $this->tpl();
        
        $view->assign('jid', $jid);

        if(isset($cr)) {
            $view->assign('contactr', $cr);
            $view->assign('edit', 
                $this->call(
                    'ajaxEditContact', 
                    "'".$cr->jid."'"));
            $view->assign('delete', 
                $this->call(
                    'ajaxDeleteContact', 
                    "'".$cr->jid."'"));
        } else {
            $view->assign('contactr', null);
            $c  = $cd->get($jid);
            if(isset($c)) {
                $view->assign('contact', $c);
            } else {
                $view->assign('contact', null);
            }
        }

        return $view->draw('_contact_header', true);
    }

    function prepareEmpty($jid = null)
    {
        if($jid == null) {
            $cd = new \modl\ContactDAO();
            $users = $cd->getAllPublic(0, 10);
            if($users != null){
                $view = $this->tpl();
                $view->assign('users', array_reverse($users));
                return $view->draw('_contact_explore', true);
            } else { 
                return '';
            }
        } else {
            $view = $this->tpl();
            $view->assign('jid', $jid);
            return $view->draw('_contact_empty', true);
        }
    }

    function prepareContact($jid)
    {
        $cd = new \Modl\ContactDAO;
        $c  = $cd->get($jid, true);

        if($c == null) {
            $c = new \Modl\Contact;
            $c->jid = $jid;

            $this->ajaxRefreshVcard($jid);
        }
        
        $cr = $cd->getRosterItem($jid);

        $view = $this->tpl();

        $pd = new \Modl\PostnDAO;
        $gallery = $pd->getGallery($jid);

        if(isset($c)) {
            $view->assign('mood', getMood());

            $view->assign('contact', $c);
            $view->assign('contactr', $cr);

            $view->assign('gallery', $gallery);

            $view->assign('chat', 
                $this->call(
                    'ajaxChat', 
                    "'".$c->jid."'"));

            return $view->draw('_contact', true);
        } elseif(isset($cr)) {
            $view->assign('contact', null);
            $view->assign('contactr', $cr);

            $view->assign('chat', 
                $this->call(
                    'ajaxChat', 
                    "'".$cr->jid."'"));
            
            return $view->draw('_contact', true);
        } else {
            return $this->prepareEmpty($jid);
        }
    }

    function display()
    {
    }
}
