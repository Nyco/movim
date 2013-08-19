<?php

/**
 * @package Widgets
 *
 * @file Chat.php
 * This file is part of MOVIM.
 * 
 * @brief A jabber chat widget.
 *
 * @author Guillaume Pasquet <etenil@etenilsrealm.nl>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 * 
 * See COPYING for licensing information.
 */

class Chat extends WidgetBase
{
    function WidgetLoad()
    {
        $this->addcss('chat.css');
        $this->addjs('chat.js');
        $this->registerEvent('message', 'onMessage');
        $this->registerEvent('messagepublished', 'onMessagePublished');
        $this->registerEvent('composing', 'onComposing');
        $this->registerEvent('paused', 'onPaused');
        $this->registerEvent('attention', 'onAttention');
        $this->registerEvent('presence', 'onPresence');

        $this->view->assign('chats', $this->prepareChats());
    }
    
    function onPresence($presence)
    {
        $arr = $presence->getPresence();

        $rc = new \modl\ContactDAO();
        $contact = $rc->getRosterItem(echapJid($presence->jid));

        if(isset($contact) && $contact->chaton == 2) {
            $txt = array(
                    1 => t('Online'),
                    2 => t('Away'),
                    3 => t('Do Not Disturb'),
                    4 => t('Extended Away'),
                    5 => t('Offline'),
                );
        
            
            $html = '
                <div class="message presence">
                    <span class="date">'.date('G:i', time()).'</span>'.
                    prepareString(htmlentities($txt[$arr['presence']], ENT_COMPAT, "UTF-8")).'
                </div>';

            RPC::call('movim_append',
                           'messages'.$arr['jid'],
                           $html); 
                           
            RPC::call('scrollTalk',
                           'messages'.$arr['jid']);
        } elseif(!isset($contact)) {
            RPC::call('movim_fill', 'chats', $this->prepareChats());
            RPC::call('scrollAllTalks');
        }
    }
    
    function onMessage($message)
    {
        if($message->key == $message->from) {
            $key = $message->from;
            $jid = $message->to;
        } else {
            $key = $message->to;
            $jid = $message->from;
        }

        if($message->key != $message->from)
            RPC::call('notify');

        $rd = new \modl\RosterLinkDAO();

        $rc = new \modl\ContactDAO();
        $contact = $rc->getRosterItem(echapJid($jid));
        
        if(isset($contact) && $contact->chaton == 0) {
            $contact->chaton = 2;
            $rd->setChat($jid, 2);
            
            $evt = new Event();
            $evt->runEvent('openchat');  

            RPC::call('movim_prepend',
                           'chats',
                           $this->prepareChat($contact));
            RPC::call('scrollAllTalks');
        } else if(isset($contact) && $message->body != '') {
            $html = $this->prepareMessage($message);

            if($contact->chaton == 1) {
                RPC::call('colorTalk',
                            'messages'.$contact->jid);
            }
            
            RPC::call('movim_append',
                           'messages'.$contact->jid,
                           $html);
            
            RPC::call('hideComposing',
                           $contact->jid); 

            RPC::call('hidePaused',
                           $contact->jid); 
                           
            RPC::call('scrollTalk',
                           'messages'.$contact->jid);
        } 
    
        // Muc case
        elseif($message->ressource != '') {
            $html = $this->prepareMessage($message, true);
            RPC::call('movim_append',
                           'messages'.$message->from,
                           $html);
            RPC::call('scrollTalk',
                           'messages'.$message->from);
        }
    }
    
    function onMessagePublished($jid)
    {
        Notification::appendNotification(t('Message Published'), 'success');
    }
    
    function onComposing($jid)
    {       
        $rd = new \modl\RosterLinkDAO();
        $contact = $rd->get(echapJid($jid));
        
        if(in_array($contact->chaton, array(1, 2))) {
            RPC::call('showComposing',
                       $contact->jid);
                           
            RPC::call('scrollTalk',
                      'messages'.$contact->jid);
        }
    }

    function onPaused($jid)
    {        
        $rd = new \modl\RosterLinkDAO();
        $contact = $rd->get(echapJid($jid));
        
        if(in_array($contact->chaton, array(1, 2))) {
            RPC::call('showPaused',
                       $contact->jid);
                           
            RPC::call('scrollTalk',
                      'messages'.$contact->jid);
        }
    }
    
    function onAttention($jid)
    {        
        $rc = new \modl\ContactDAO();
        $contact = $rc->getRosterItem(echapJid($jid));
        
        $html = '
            <div style="font-weight: bold; color: black;" class="message" >
                <span class="date">'.date('G:i', time()).'</span>'.
                t('%s needs your attention', $contact->getTrueName()).'
            </div>';

        RPC::call('movim_append',
                       'messages'.$jid,
                       $html); 
                       
        RPC::call('scrollTalk',
                       'messages'.$jid);
    }
    
    
    /**
     * Open a new talk
     *
     * @param string $jid
     * @return void
     */
    function ajaxOpenTalk($jid) 
    {        
        $rc = new \modl\ContactDAO();
        $contact = $rc->getRosterItem(echapJid($jid));

        if(
            isset($contact) 
         && $contact->chaton == 0 
         && !in_array($contact->presence, array(5, 6))) {
             
            $contact->chaton = 2;
            
            $rd = new \modl\RosterLinkDAO();
            $rd->setChat(echapJid($jid), 2);

            RPC::call('movim_prepend',
                           'chats',
                           $this->prepareChat($contact));
                               
            RPC::call('scrollAllTalks');

            RPC::commit();
        }
        
        $evt = new Event();
        $evt->runEvent('openchat');
    }
    
    /**
     * Send a message
     *
     * @param string $to
     * @param string $message
     * @return void
     */
    function ajaxSendMessage($to, $message, $muc = false)
    {        
        $m = new \modl\Message();
        
        $m->key     = $this->user->getLogin();
        $m->to      = echapJid($to);
        $m->from    = $this->user->getLogin();
    
        global $session;
        
        $m->type    = 'chat';
        $m->ressource = $session['ressource'];
    
        if($muc) {
            $m->type = 'groupchat';
            $m->ressource = $session['user'];
            $m->from = $to;
        }
        
        $m->body    = rawurldecode($message);
        
        $m->published = date('Y-m-d H:i:s');
        $m->delivered = date('Y-m-d H:i:s');
    
        $md = new \modl\MessageDAO();
        $md->set($m);

        $evt = new Event();
        $evt->runEvent('message', $m);  
        
        // We decode URL codes to send the correct message to the XMPP server
        $m = new \moxl\MessagePublish();
        $m->setTo($to)
          ->setContent(htmlspecialchars(rawurldecode($message)));
        if($muc)
            $m->setMuc();
        $m->request();
    }

    /**
     * Send a "composing" message
     * 
     * @param string $to
     * @return void
     */
    function ajaxSendComposing($to)
    {
        $mc = new \moxl\MessageComposing();
        $mc->setTo($to)
           ->request();
    }

    /**
     * Send a "paused" message
     * 
     * @param string $to
     * @return void
     */
    function ajaxSendPaused($to)
    {
        $mp = new \moxl\MessagePaused();
        $mp->setTo($to)
           ->request();
    }
    
    /**
     * Close a talk
     *
     * @param string $jid
     * @return void
     */
    function ajaxCloseTalk($jid) 
    {                
        $rd = new \modl\RosterLinkDAO();
        $contacts = $rd->getChats();

        foreach($contacts as $contact) {
            if(
                $contact->jid == echapJid($jid) 
                && (
                    (int)$contact->chaton == 1 
                 || (int)$contact->chaton == 2)
            ) {
                $contact->chaton = 0;
                $rd->setNow($contact);
            }
        }
        
        RPC::call('movim_delete',
                   'chat'.echapJid($jid));
        
        $evt = new Event();
        $evt->runEvent('closechat');
    }
    
    function ajaxHideTalk($jid)
    {
        $rd = new \modl\RosterLinkDAO();
        $contact = $rd->get(echapJid($jid));
        
        if($contact->chaton == 1)
            $contact->chaton = 2;
        else 
            $contact->chaton = 1;
        $rd->setNow($contact);
        
        RPC::call('scrollTalk',
                   'messages'.$contact->jid);
        RPC::commit();
    }
    
    function prepareMessage($message, $muc = false) {
        if($message->body != '') {
            $html = '<div class="message ';
                if($message->key == $message->from)
                    $html.= 'me';
                   
            $content = $message->body;
                    
            if(preg_match("#^/me#", $message->body)) {
                $html .= ' own ';
                $content = '** '.substr($message->body, 4);
            }
            
            if(preg_match("#^\?OTR:#", $message->body)) {
                $html .= ' crypt ';
                $content = t('Encrypted message');
            }
            
            
            $c = new \modl\Contact();
                    
            $html .= '">
                <img class="avatar" src="'.$c->getPhoto('xs', $message->from).'" />
                <span class="date">'.date('H:i', strtotime($message->published)).'</span>';
            
            if($muc != false)
                $html .= '
                    <span class="ressource '.$this->colorNameMuc($message->ressource).'">'.
                        $message->ressource.'
                    </span>';
                
            $html.= prepareString(htmlentities($content, ENT_COMPAT, "UTF-8")).'</div>';
            return $html;
        } else {
            return '';
        }
    }
    
    function prepareChats()
    {
        $rc = new \modl\ContactDAO();
        $contacts = $rc->getRosterChat();
        
        $html = '';

        if(isset($contacts)) {
            foreach($contacts as $contact) {
                $html .= trim($this->prepareChat($contact));
            }
        }
        
        $bk = Cache::c('bookmark');
        if(is_array($bk))
            foreach($bk as $b) {
                if($b['type'] == 'conference') 
                    $html .= trim($this->prepareMuc($b['jid']));
            }
        
        return $html;
    }
    
    function prepareMuc($jid)
    {
        // Zeu messages
        $md = new \modl\MessageDAO();
        $messages = $md->getContact($jid, 0, 10);
        
        $messageshtml = '';
        
        if(!empty($messages)) {
            $day = '';
            foreach($messages as $m) {
                if($day != date('d',strtotime($m->published))) {
                    $messageshtml .= '<div class="message presence">'.prepareDate(strtotime($m->published), false).'</div>';
                    $day = date('d',strtotime($m->published));
                }
                $messageshtml .= $this->prepareMessage($m, true);
            }
        }
        
        // Zeu muc list
        $pd = new \modl\PresenceDAO();
        $presences = $pd->getJid($jid);

        $mucview = $this->tpl();
        $mucview->assign('jid', $jid);
        $mucview->assign('messageshtml', $messageshtml);
        $mucview->assign('muclist', $presences);
        $mucview->assign('toggle', 
                            $this->genCallAjax(
                                "ajaxToggleMuc", "'".$jid."'")
                        );
        $mucview->assign('sendmessage',
                            $this->genCallAjax(
                                'ajaxSendMessage', 
                                "'".$jid."'", 
                                "sendMessage(this, '".$jid."')",
                                "true"
                        ));
        
        $sess = \Session::start(APP_NAME);
        $state = $sess->get('muc'.$jid);
        
        if($state == 1) {
            $mucview->assign('tabstyle', 'style="display: none;"');            
            $mucview->assign('panelstyle', 'style="display: block;"');
        } else {
            $mucview->assign('tabstyle', '');            
            $mucview->assign('panelstyle', '');   
        }

        $html = $mucview->draw('_chat_muc', true);

        return $html;
    }
    
    function ajaxToggleMuc($jid)
    {
        $sess = \Session::start(APP_NAME);
        $state = $sess->get('muc'.$jid);
        if($state == 1)
            $sess->set('muc'.$jid, 0);
        else
            $sess->set('muc'.$jid, 1);
    }
    
    function colorNameMuc($jid)
    {
        $colors = array(
            1 => 'purple',
            2 => 'wine',
            3 => 'yellow',
            4 => 'orange',
            5 => 'green',
            6 => 'red',
            7 => 'blue');
            
        $s = base_convert($jid, 32, 8);
        return $colors[$s[7]];
    }
    
    // Prepare Chat
    function prepareChat($contact)
    {
        $md = new \modl\MessageDAO();
        $messages = $md->getContact(echapJid($contact->jid), 0, 10);
        
        $messageshtml = '';

        if(!empty($messages)) {
            $day = '';
            foreach($messages as $m) {
                if($day != date('d',strtotime($m->published))) {
                    $messageshtml .= '<div class="message presence">'.prepareDate(strtotime($m->published), false).'</div>';
                    $day = date('d',strtotime($m->published));
                }
                $messageshtml .= $this->prepareMessage($m);
            }
        }
        
        $style = '';
        $tabstyle = '';
        $panelstyle = '';
        if($contact->chaton == 2) {
            $tabstyle = ' style="display: none;" ';            
            $panelstyle = ' style="display: block;" ';
        }

        $html = '
            <div class="chat" 
                 onclick="this.querySelector(\'textarea\').focus()"
                 id="chat'.$contact->jid.'">
                <div class="panel" '.$panelstyle.'>
                    <div class="head" >
                        <span class="chatbutton cross" onclick="'.$this->genCallAjax("ajaxCloseTalk", "'".$contact->jid."'").'"></span>
                        <span class="chatbutton arrow" onclick="'.$this->genCallAjax("ajaxHideTalk", "'".$contact->jid."'").' hideTalk(this)"></span>
                        <a class="name" href="'.Route::urlize('friend',$contact->jid).'">
                            '.$contact->getTrueName().'
                        </a>
                    </div>
                    <div class="messages" id="messages'.$contact->jid.'">
                        '.$messageshtml.'
                        <div style="display: none;" class="message composing" id="composing'.$contact->jid.'">'.t('Composing...').'</div>
                        <div style="display: none;" class="message composing" id="paused'.$contact->jid.'">'.t('Paused...').'</div>                        
                    </div>
                    
                    <div class="text">
                         <textarea 
                            rows="1"
                            id="textarea'.$contact->jid.'"
                            onkeypress="
                                    if(event.keyCode == 13) {
                                        '.$this->genCallAjax(
                                            'ajaxSendMessage', 
                                            "'".$contact->jid."'", 
                                            "sendMessage(this, '".$contact->jid."')"
                                        ).'
                                        lastkeypress = new Date().getTime()+1000;
                                        return false;
                                    }

                                    if(lastkeypress < new Date().getTime())
                                        '.$this->genCallAjax('ajaxSendComposing', "'".$contact->jid."'").'

                                    lastkeypress = new Date().getTime()+1000;
                                "
                            onkeyup="
                                movim_textarea_autoheight(this);
                                var val = this.value;
                                setTimeout(function()
                                {
                                    if(lastkeypress < new Date().getTime() && val != \'\') {
                                    '.$this->genCallAjax('ajaxSendPaused', "'".$contact->jid."'").'
                                        lastkeypress = new Date().getTime()+1000;
                                    }
                                },1100); // Listen for 2 seconds of silence
                            "
                        ></textarea>
                    </div>
                </div>
                
                <div class="tab" '.$tabstyle.' onclick="'.$this->genCallAjax("ajaxHideTalk", "'".$contact->jid."'").' showTalk(this);">
                    <div class="name">
                        <img class="avatar"  src="'.$contact->getPhoto('xs').'" />'.$contact->getTrueName().'
                    </div>
                </div>
            </div>

            ';
        return $html;
        
/* This is the un optimized system to send "composing" and "paused"
 * 
                            onkeyup="
                                movim_textarea_autoheight(this);
                                "
                            onkeypress="
                                if(event.keyCode == 13) {
                                    '.$this->genCallAjax('ajaxSendMessage', "'".$contact->jid."'", "sendMessage(this, '".$contact->jid."')").'
                                    return false;
                                }"
     
    * */
    }
}
