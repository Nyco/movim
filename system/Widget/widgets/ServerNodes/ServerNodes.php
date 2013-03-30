<?php

/**
 * @package Widgets
 *
 * @file ServerNodes.php
 * This file is part of MOVIM.
 *
 * @brief The Profile widget
 *
 * @author Timothée	Jaussoin <edhelas_at_gmail_dot_com>
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
        $this->registerEvent('disconodes', 'onDiscoNodes');
        $this->registerEvent('creationsuccess', 'onCreationSuccess');
    }

    function onDiscoNodes($items)
    {
        $html = '<ul class="list">';

        foreach($items[0] as $item) {
            $html .= '
                <li>
                    <a href="?q=node&s='.$item->attributes()->jid.'&n='.$item->attributes()->node.'">'.
                        $item->attributes()->node. ' - '.
                        $item->attributes()->name.'
                    </a>
                </li>';
        }

        $html .= '</ul>';
        
        $submit = $this->genCallAjax('ajaxCreateGroup', "movim_parse_form('groupCreation')");
        
        $html .= '<div class="popup" id="groupCreation">
                <form name="groupCreation">
                    <fieldset>
                        <legend>'.t('Give a friendly name to your group').'</legend>
                        <div class="element large mini">
                            <input name="title" placeholder="'.t('My Little Pony - Fan Club').'"/>
                        </div>
                        <input type="hidden" name="server" value="'.$items[1].'"/>
                    </fieldset>
                    <a 
                        class="button tiny icon yes black merged left"
                        onclick="'.$submit.'"
                    >'.
                            t('Add').'
                    </a><a 
                        class="button tiny icon black merged right" 
                        onclick="movim_toggle_display(\'#groupCreation\')"
                    >'.
                            t('Close').'
                    </a>
                </form>
            </div>';

        RPC::call('movim_fill', 'servernodes', $html);
        RPC::commit();
    }
    
    function onDiscoItems($items)
    {
        $html = '<ul class="list">';

        foreach($items as $item) {
            $html .= '
                <li>
                    <a href="?q=server&s='.$item->attributes()->jid.'">'.
                        $item->attributes()->jid. ' - '.
                        $item->attributes()->name.'
                    </a>
                </li>';
        }

        $html .= '</ul>';

        RPC::call('movim_fill', 'servernodes', $html);
        RPC::commit();
    }
    
    function onCreationSuccess($items)
    {
        $html = '<a class="button tiny icon" href="http://localhost/movim-ptrans/?q=node&s='.
            $items[0].'&n='.
            $items[1].'">'.$items[2].'</a>';

        RPC::call('movim_fill', 'servernodes', $html);
        RPC::commit();
    }

    function ajaxGetNodes($server)
    {
        $r = new moxl\GroupServerGetNodes();
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

    function build()
    {
    ?>
        <a class="button tiny icon" onclick="movim_toggle_display('#groupCreation')"><?php echo t("Create a new group");?></a>
        <div id="newGroupForm"></div>
        <div class="tabelem protect red" id="servernodes" title="<?php echo t('Groups');?>">
            <script type="text/javascript"><?php echo $this->genCallAjax('ajaxGetNodes', "'".$_GET['s']."'"); ?></script>
        </div>
    <?php
    }
}
