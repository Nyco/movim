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

class Roster extends WidgetBase
{
    private $grouphtml;

    function WidgetLoad()
    {
    	$this->addcss('roster.css');
    	$this->addjs('roster.js');
		$this->registerEvent('roster', 'onRoster');
        $this->registerEvent('contactadd', 'onRoster');
        $this->registerEvent('contactremove', 'onRoster');
		$this->registerEvent('presence', 'onPresence');
		//this->registerEvent('vcard', 'onVcard');

        $this->cached = false;
    }

	function onPresence($presence)
	{
	    $arr = $presence->getPresence();
	    RPC::call('incomingPresence',
                      RPC::cdata($arr['jid']), RPC::cdata($arr['presence_txt']));
	}

    /*function onVcard($contact)
    {
        $query = \Presence::query()->select()
                           ->where(array(
                                   'key' => $this->user->getLogin(),
                                   'jid' => $contact->getData('jid')))
                           ->limit(0, 1);
        $data = \Presence::run_query($query);

        $c = array();
        $c[0] = $contact;
        $c[1] = $data[0];

        $html = $this->prepareRosterElement($c, true);
        RPC::call('movim_fill', 'roster'.$contact->getData('jid'), RPC::cdata($html));
    }*/

    function onRoster()
    {
		$html = $this->prepareRoster();
        RPC::call('movim_fill', 'rosterlist', RPC::cdata($html));
        RPC::call('sortRoster');
    }

	/**
     * @brief Force the roster refresh
     * @returns 
     * 
     * 
     */
    function ajaxRefreshRoster()
	{
        $r = new moxl\RosterGetList();
        $r->request();
	}

	/**
     * @brief Generate the HTML for a roster contact
     * @param $contact 
     * @param $inner 
     * @returns 
     * 
     * 
     */
    function prepareRosterElement($contact, $inner = false)
	{
        if(isset($contact[1]))
            $presence = $contact[1]->getPresence();
        $start =
            '<li
                class="';
                    if(isset($presence['presence']))
                        $start .= ''.$presence['presence_txt'].' ';
                    else
                        $start .= 'offline ';

                    if($contact[0]->getData('jid') == $_GET['f'])
                        $start .= 'active ';
        $start .= '"
                id="roster'.$contact[0]->getData('jid').'"
             >';

        $middle = '<div class="chat on" onclick="'.$this->genCallWidget("Chat","ajaxOpenTalk", "'".$contact[0]->getData('jid')."'").'"></div>
                 <a
					title="'.$contact[0]->getData('jid');
                    if($presence['status'] != '')
                        $middle .= ' - '.htmlentities($presence['status']);
                    if($presence['ressource'] != '')
                        $middle .= ' ('.$presence['ressource'].')';
        $middle .= '"';
        $middle .= ' href="?q=friend&f='.$contact[0]->getData('jid').'"
                 >
                    <img class="avatar"  src="'.Contact::getPhotoFromJid('xs', $contact[0]->getData('jid')).'" />'.
                    '<span>'.$contact[0]->getTrueName();
						if($contact[0]->getData('rosterask') == 'subscribe')
							$middle .= " #";
                        if($presence['ressource'] != '')
                            $middle .= ' ('.$presence['ressource'].')';
            $middle .= '</span>
                 </a>';
        $end = '</li>';

        if($inner == true)
            return $middle;
        else
            return $start.$middle.$end;
	}
    
    /**
     * @brief Create the HTML for a roster group and add the title
     * @param $contacts 
     * @param $i 
     * @returns html
     * 
     * 
     */
    private function prepareRosterGroup($contacts, &$i)
    {
        // We get the current name of the group
        $currentgroup = $contacts[$i][0]->getData('group');
        
        // Temporary array to prevent duplicate contact
        $duplicate = array();
        
        // We grab all the contacts of the group 
        $grouphtml = '';
        while(isset($contacts[$i][0]) && $contacts[$i][0]->getData('group') == $currentgroup) {
            if(!in_array($contacts[$i][0]->getData('jid'), $duplicate)) {
                $grouphtml .= $this->prepareRosterElement($contacts[$i]);
                array_push($duplicate, $contacts[$i][0]->getData('jid'));
            }
            
            $i++;
        } 
        
        // And we add the title at the head of the group 
        if($currentgroup == '')
            $currentgroup = t('Ungrouped');
            
        $grouphtml = '<div><h1>'.$currentgroup.' - '.count($duplicate).'</h1>'.$grouphtml.'</div>';
        
        return $grouphtml;
    }

	/**
     * @brief Here we generate the roster
     * @returns 
     * 
     * 
     */
    function prepareRoster()
	{
        $query = RosterLink::query()->join('Presence',
                                              array('RosterLink.jid' =>
                                                    'Presence.jid'))
                                     ->where(
                                        array(
                                            'RosterLink`.`key' => $this->user->getLogin(),
                                            array(
                                                'RosterLink`.`rostersubscription!' => 'none',
                                                'RosterLink`.`rostersubscription!' => '',
                                                'RosterLink`.`rostersubscription!' => 'vcard',
                                                '|RosterLink`.`rosterask' => 'subscribe')))
                                     ->orderby('RosterLink.group', true);

        $contactsq = RosterLink::run_query($query);

        $contacts = array();
        
        foreach($contactsq as $c) {
            $p = $c[1]->getPresence();
            if(isset($p['jid'])) {
                $query = Presence::query()->where(
                                                array(
                                                    'key' => $this->user->getLogin(),
                                                    'jid' => $c[0]->getData('jid')))
                                        ->orderby('presence', false);
                $presences = Presence::run_query($query);
                if(isset($presences[0]))
                    array_push($contacts, array($c[0], $presences[0]));
            }
            else
                array_push($contacts, array($c[0], $c[1]));
        }

        $html = '';
        $group = '';

        if($contacts != false) {
            $i = 0;
            
            while($i < count($contacts))
                $html .= $this->prepareRosterGroup($contacts, $i);

        } else {
            $html .= '<script type="text/javascript">setTimeout(\''.$this->genCallAjax('ajaxRefreshRoster').'\', 1500);</script>';
        }

        return $html;
	}
    
    /**
     * @brief Adding a new contact from the Rostermenu
     * @param $jid 
     * @param $alias 
     * @returns 
     * 
     * 
     */
    function ajaxAddContact($jid, $alias) {
        $r = new moxl\RosterAddItem();
        $r->setTo($jid)
          ->request();
          
        $p = new moxl\PresenceSubscribe();
        $p->setTo($jid)
          ->request();
    }
    
    function build()
    {
    ?>
        <div id="roster">
            <ul id="rosterlist">
            <?php echo $this->prepareRoster(); ?>
            </ul>
            <div id="rostermenu" class="menubar">
                <form id="addcontact">
                    <div class="element large">
                        <label for="addjid"><?php echo t('JID'); ?></label>
                        <input 
                            id="addjid" 
                            class="tiny" 
                            placeholder="user@server.tld" 
                            onfocus="myFocus(this);" 
                            onblur="myBlur(this);"
                        />
                    </div>
                    <div class="element large">
                        <label for="addalias"><?php echo t('Alias'); ?></label>
                        <input 
                            id="addalias"
                            type="text"
                            class="tiny" 
                            placeholder="<?php echo t('Alias'); ?>" 
                            onfocus="myFocus(this);" 
                            onblur="myBlur(this);"
                        />
                    </div>
                    <a 
                        class="button tiny icon no merged left"
                        href="#"
                        id="addrefuse"
                        onclick="cancelAddJid();">
                        <?php echo t('Cancel'); ?>
                    </a><a 
                        class="button tiny icon yes merged right" 
                        href="#" 
                        id="addvalidate" 
                        onclick="<?php $this->callAjax("ajaxAddContact", "getAddJid()", "getAddAlias()"); ?> cancelAddJid();">
                        <?php echo t('Send request'); ?>
                    </a>
                </form> 

                <ul>
                    <li onclick="addJid(this)"; style="float: right;" title="<?php echo t('Add'); ?>">
                        <a href="#">+</a>
                    </li>
                    <li onclick="showRoster(this);" style="float: right;" title="<?php echo t('Show/Hide'); ?>">
                        <a href="#">◐</a>
                    </li>
                    <li>
                        <input type="text" name="search" id="request" autocomplete="off" onkeyup="rosterSearch(event);" onclick="focusContact();" placeholder="<?php echo t('Search');?>"/>
                    </li>
                </ul>
            </div>
            <div class="config_button" onclick="<?php $this->callAjax('ajaxRefreshRoster');?>"></div>
            <script type="text/javascript">sortRoster();</script>
        </div>
    <?php
    }
}

?>
