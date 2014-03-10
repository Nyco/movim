<?php

/**
 * @package Widgets
 *
 * @file ServerNodes.php
 * This file is part of MOVIM.
 *
 * @brief The Profile widget
 *
 * @author Timothée    Jaussoin <edhelas_at_gmail_dot_com>
 *
 * @version 1.0
 * @date 20 October 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class ServerNodes extends WidgetCommon
{
    function WidgetLoad()
    {
        $this->registerEvent('discoitems', 'onDiscoItems');
        $this->registerEvent('discoerror', 'onDiscoError');
        $this->registerEvent('creationsuccess', 'onCreationSuccess');
        $this->registerEvent('creationerror', 'onCreationError');
    }

    function display()
    {
        if($_GET['s'] != null) {
            $this->view->assign('server', $this->prepareServer($_GET['s']));
            $this->view->assign('get_nodes', $this->genCallAjax('ajaxGetNodes', "'".$_GET['s']."'"));
        }
    }
    
    function onDiscoError($error)
    {
        RPC::call('movim_fill', 'servernodeshead', '');
    }
    
    function onDiscoItems($server) {
        $submit = $this->genCallAjax('ajaxCreateGroup', "movim_parse_form('groupCreation')");
        
        list($type) = explode('.', $server);
        
        if(!in_array($type, array('conference', 'muc', 'discussion', 'chat'))) {
            $head = '
                <a 
                    class="button icon add color green" 
                    onclick="movim_toggle_display(\'#groupCreation\')">
                    '.t("Create a new group").'
                </a>';
                
            $html = '
                <div class="popup" id="groupCreation">
                    <form name="groupCreation">
                        <fieldset>
                            <legend>'.t('Give a friendly name to your group').'</legend>
                            <div class="element large mini">
                                <input name="title" placeholder="'.t('My Little Pony - Fan Club').'"/>
                            </div>
                            <input type="hidden" name="server" value="'.$server.'"/>
                        </fieldset>
                        <div class="menu">
                            <a 
                                class="button color icon yes blue merged left"
                                onclick="'.$submit.'"
                            >'.
                                    t('Add').'
                            </a><a 
                                class="button icon no black merged right" 
                                onclick="movim_toggle_display(\'#groupCreation\')"
                            >'.
                                    t('Close').'
                            </a>
                        </div>
                    </form>
                </div>';
        } else
            $head = '';
            
        $html .= $this->prepareServer($server);
        
        RPC::call('movim_fill', 'servernodeshead', $head);
        RPC::call('movim_fill', 'servernodeslist', $html);
        RPC::commit();
    }
    
    function prepareServer($server) {
        $nd = new \modl\ItemDAO();
        $items = $nd->getItems($server);
        
        if($items == null)
            return '';

        $html = '<ul class="list">';
        
        foreach($items as $i) {
            if(substr($i->node, 0, 20) != 'urn:xmpp:microblog:0') {
                $tags = '';
                if($i->num != null)
                    $tags .= '<span class="tag">'.$i->num.'</span>';
            
                if($i->subscription == 'subscribed')
                    $tags .= '<span class="tag green">'.t('Subscribed').'</span>';
                    
                $url = '';
                if($i->node != null) {
                    $url = 'href="'.Route::urlize('node', array($i->server, $i->node)).'"';
                } elseif($i->jid != null && !filter_var($i->jid, FILTER_VALIDATE_EMAIL)) {
                    $url = 'href="'.Route::urlize('server', array($i->jid)).'"';
                } else {
                    $tags .= '<span class="tag">'.$i->jid.'</span>';
                }
            
                $html .= '
                    <li>
                        <a '.$url.'>'.
                            $i->getName().
                            $tags.'
                        </a>
                    </li>';
            }
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    function onCreationSuccess($items)
    {        
        $html = '<a href="
            '.Route::urlize('node', array($items[0], $items[1])).'
            ">'.$items[2].'</a>';

        RPC::call('movim_fill', 'servernodes', $html);
        RPC::commit();
    }
    
    function onCreationError($error) {
        RPC::call('movim_fill', 'servernodes', '');
        RPC::commit();
    }

    function ajaxGetNodes($server)
    {
        $nd = new modl\ItemDAO();
        $nd->deleteItems($server);
        
        $r = new moxl\PubsubDiscoItems();
        $r->setTo($server)->request();
    }
    
    function ajaxCreateGroup($data)
    {
        //make a uri of the title
        $uri = stringToUri($data['title']);
        
        $r = new moxl\GroupCreate();
        $r->setTo($data['server'])->setNode($uri)->setData($data['title'])
          ->request();
    }
}
