<?php

/**
 * @package Widgets
 *
 * @file Roster.php
 * This file is part of MOVIM.
 *
 * @brief The Roster widget
 *
 * @author Jaussoin Timothée <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 30 August 2010
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

use Moxl\Xec\Action\Roster\GetList;

class RosterAngular extends WidgetBase
{
    private $grouphtml;

    function load()
    {
        $this->addcss('roster.css');
        $this->addjs('angular.js');
        $this->addjs('angular-filters.js');
        $this->addjs('roster.js');
        $this->registerEvent('roster', 'onRoster');
        $this->registerEvent('rosterupdateditem', 'onRoster');
        $this->registerEvent('contactadd', 'onRoster');
        $this->registerEvent('contactremove', 'onRoster');
        /*$this->registerEvent('presence', 'onPresence');*/
    }

    function display()
    {
        /*$this->view->assign('offline_shown',  '');
        $offline_state = Cache::c('offlineshown');

        $bool = Cache::c('rostershow');
        if($bool)
            $this->view->assign('roster_show', 'hide');
        else
            $this->view->assign('roster_show', '');

        if($offline_state == true)
            $this->view->assign('offline_shown',  'offlineshown');

        $this->view->assign('toggle_cache', $this->genCallAjax('ajaxToggleCache', "'offlineshown'"));
        $this->view->assign('search_contact', $this->genCallAjax('ajaxSearchContact','this.value'));
        
        $this->view->assign('rosterlist', $this->prepareRoster());*/
    }

    function onPresence($packet)
    {
        $c = $packet->content;

        if($c != null) {
            $html = $this->prepareContact($c, $this->getCaps());

            if($c[0]->groupname == null)
                $group = t('Ungrouped');
            else
                $group = $c[0]->groupname;

            RPC::call(
            'movim_delete', 
            $c[0]->jid, 
            $html /* this second parameter is just to bypass the RPC filter */);

            RPC::call('movim_append', 'group'.$group, $html);
            RPC::call('sortRoster');
        }
    }

    function onRoster($jid)
    {
        $results = $this->prepareRosterAngular();
        RPC::call('initContacts', $results['contacts']);
        RPC::call('initGroups', $results['groups']);
    }

    /**
     * @brief Force the roster refresh
     * @returns
     */
    function ajaxRefreshRoster()
    {
        $r = new GetList;
        $r->request();
    }


    private function getCaps() {
        $capsdao = new \Modl\CapsDAO();
        $caps = $capsdao->getAll();

        $capsarr = array();
        foreach($caps as $c) {
            $capsarr[$c->node] = $c;
        }

        return $capsarr;
    }

    /**
     * @brief Toggling boolean variables in the Cache
     * @param $param
     * @returns 
     */
    /*function ajaxToggleCache($param){
        //$bool = !currentValue
        $bool = (Cache::c($param) == true) ? false : true;
        //toggling value in cache
        Cache::c($param, $bool);
        //$offline = new value of wether offline are shown or not
        $offline = Cache::c('offlineshown');
        
        if($param == 'offlineshown') {
            if($bool)
                Notification::appendNotification($this->__('roster.show_disconnected'), 'success');
            else
                Notification::appendNotification($this->__('roster.hide_disconnected'), 'success');
            RPC::call('showRoster', $bool);
        } else {
            if($bool)
                Notification::appendNotification($this->__('roster.hide_group',substr($param, 5)), 'success');
            else
                Notification::appendNotification($this->__('roster.show_group',substr($param, 5)), 'success');
            RPC::call('rosterToggleGroup', $param, $bool, $offline);

        }
        
        RPC::call('focusContact');
        RPC::commit();
    }*/
    
    /**
     *  @brief Search for a contact to add
     */
    function ajaxSearchContact($jid) {
        if(filter_var($jid, FILTER_VALIDATE_EMAIL)) {
            RPC::call('movim_redirect', Route::urlize('friend', $jid));
            RPC::commit();
        } else 
            Notification::appendNotification($this->__('roster.jid_error'), 'info');
    }
    
    

/*=========*/
    /**
     * @brief Get data from to database to pass it on to angular in JSON
     * @param
     * @returns $result: a json for the contacts and one for the groups
     */
    function prepareRosterAngular(){
        //Contacts
        $contactdao = new \Modl\ContactDAO();
        $contacts = $contactdao->getRoster();
        
        $capsarr = $this->getCaps();
        
        if(isset($contacts)) {
            foreach($contacts as &$c) {
                if($c->groupname == '')
                    $c->groupname = $this->__('roster.ungrouped');
                
                $ac = $c->toArray();
                $this->prepareContactAngular($ac, $c, $capsarr);
                $c = $ac;
            }
        }
        $result['contacts'] = json_encode($contacts);
        
        //Groups
        $rd = new \Modl\RosterLinkDAO();
        $groups = $rd->getGroups();
        if(!in_array("ungrouped"))$groups[] = "ungrouped";
        $groups = array_flip($groups);
        $result['groups'] = json_encode($groups);
        
        return $result;
    }

    /**
     * @brief Get data for contacts display in roster
     * @param   &$c: the contact as an array and by reference,
     *          $oc: the contact as an object,
     *          $caps: an array of capabilities
     * @returns
     */
    function prepareContactAngular(&$c, $oc, $caps){
        $arr = array();
        $jid = false;

        $presencestxt = getPresencesTxt();
        
        // We add some basic information
        $c['rosterview']   = array();
        $c['rosterview']['avatar']   = $oc->getPhoto('s');
        $c['rosterview']['name']     = $oc->getTrueName();
        $c['rosterview']['friendpage']     = $this->route('friend', $oc->jid);

        // Some data relative to the presence
        if($oc->last != null && $oc->last > 60)
            $c['rosterview']['inactive'] = 'inactive';
        else
            $c['rosterview']['inactive'] = '';

        if($oc->value && $oc->value != 5){
            if($oc->value && $oc->value == 6) {
                $c['rosterview']['presencetxt'] = 'server_error';
            } else {
                $c['rosterview']['presencetxt'] = $presencestxt[$oc->value];
            }
            $c['value'] = intval($c['value']);
        } else {
            $c['rosterview']['presencetxt'] = 'offline';
            $c['value'] = 5;
        }

        // An action to open the chat widget
        $c['rosterview']['openchat']
            = $this->genCallWidget("Chat","ajaxOpenTalk", "'".$oc->jid."'");

        $c['rosterview']['type']   = '';
        $c['rosterview']['client'] = '';
        $c['rosterview']['jingle'] = false;

        // About the entity capability
        if($caps && isset($caps[$oc->node.'#'.$oc->ver])) {
            $cap  = $caps[$oc->node.'#'.$oc->ver];
            $c['rosterview']['type'] = $cap->type;
            
            $client = $cap->name;
            $client = explode(' ',$client);
            $c['rosterview']['client'] = strtolower(preg_replace('/[^a-zA-Z0-9_ \-()\/%-&]/s', '', reset($client)));

            // Jingle support
            $features = $cap->features;
            $features = unserialize($features);
            if(array_search('urn:xmpp:jingle:1', $features) !== null
            && array_search('urn:xmpp:jingle:apps:rtp:audio', $features) !== null
            && array_search('urn:xmpp:jingle:apps:rtp:video', $features) !== null
            && (  array_search('urn:xmpp:jingle:transports:ice-udp:0', $features)
               || array_search('urn:xmpp:jingle:transports:ice-udp:1', $features))
            ){
                $c['rosterview']['jingle'] = true;
            }
        }

        // Tune
        $c['rosterview']['tune'] = false;
        
        if(($oc->tuneartist != null && $oc->tuneartist != '') 
            || ($oc->tunetitle  != null && $oc->tunetitle  != ''))
            $c['rosterview']['tune'] = true;
    }

}


?>
