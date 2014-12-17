<?php

use Moxl\Xec\Action\Roster\UpdateItem;

class Contact extends WidgetCommon
{
    function load()
    {
        $this->registerEvent('roster_updateitem_handle', 'onContactEdited');
    }

    public function onContactEdited($packet)
    {
        Notification::appendNotification($this->__('edit.updated'));
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
            $users = $cd->getAllPublic();

            $view = $this->tpl();
            $view->assign('users', array_reverse($users));
            return $view->draw('_contact_explore', true);
        } else {
            $view = $this->tpl();
            $view->assign('jid', $jid);
            return $view->draw('_contact_empty', true);
        }
    }

    function prepareContact($jid)
    {
        $cd = new \Modl\ContactDAO;
        $c  = $cd->get($jid);
        $cr = $cd->getRosterItem($jid);

        $view = $this->tpl();

        if(isset($c)) {
            $view->assign('mood', getMood());

            $view->assign('contact', $c);
            $view->assign('contactr', $cr);
            return $view->draw('_contact', true);
        } elseif(isset($cr)) {
            $view->assign('contact', null);
            $view->assign('contactr', $cr);
            return $view->draw('_contact', true);
        } else {
            return $this->prepareEmpty($jid);
        }
    }

    function display()
    {
    }
}
