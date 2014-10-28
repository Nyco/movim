<div id="roster" class="{$roster_show}" ng-controller="RosterController as rosterCtrl">
    <input 
        type="text" 
        name="search" 
        id="rostersearch" 
       
        autocomplete="off" 
        placeholder="{$c->__('roster.search');}"/>
        
    <ul id="rosterlist" class="{$offline_shown}">
        <div ng-repeat="(key, value) in contacts | groupBy: 'groupname'" 
            id="group{{key}}" 
            ng-class="{groupshown: rosterCtrl.groupIsShown(key) == 'true'}">
            <h1>{{key}}</h1>
                <ul class="contact">
                    <li ng-repeat="contact in value" class="{{contact.rosterview.presencetxt}} {{contact.rosterview.inactive}}" ng-class="{client {{contact.rosterview.client}}: contact.rosterview.client}">
                        
                        <div class="chat on" ng-click="rosterCtrl.postChatAction(contact)" >
                        </div>
                        <a href="{{contact.rosterview.friendpage}}">
                            <img
                                class="avatar"
                                src="{{contact.rosterview.avatar}}"
                                alt="avatar"
                            />{{contact.rosterview.name}}
                            <span class="ressource">{if="$value.status != ''"}{$value.status} -{/if} {$value.ressource}</span>
                        </a>
                    </li>
                </ul>
        </div>
        
    </ul>
</div>

<div id="rostermenu" class="menubar">
    <ul class="menu">
        <li 
            class="show_hide body_infos on_mobile"
            onclick="
                movim_remove_class('body', 'roster'),
                movim_toggle_class('body', 'infos')"
            title="{$c->__('roster.show_hide')}">
            <a class="about" href="#"></a>
        </li>

        <li class="on_mobile">
            <a class="conf" title="{$c->__('page.configuration')}" href="{$c->route('conf')}">
            </a>
        </li>
        <li class="on_mobile">
            <a class="help" title="{$c->__('page.help')}" href="{$c->route('help')}">
            </a>
        </li>

        <li 
            class="show_hide body_roster on_mobile"
            onclick="
                movim_remove_class('body', 'infos'),
                movim_toggle_class('body', 'roster')"
            title="{$c->__('roster.show_hide')}">
            <a class="down" href="#"></a>
        </li>

        <li title="{$c->__('button.add')}">
            <label class="plus" for="addc"></label>
            <input type="checkbox" id="addc"/>
            <div class="tabbed">    
                <div class="message">                  
                    {$c->__('roster.add_contact_info1')}<br />
                    {$c->__('roster.add_contact_info2')}
                </div>  
                <input 
                    name="searchjid" 
                    class="tiny" 
                    type="email"
                    title="{$c->__('roster.jid')}"
                    placeholder="user@server.tld" 
                    onkeypress="
                        if(event.keyCode == 13) {
                            {$search_contact}
                            return false;
                        }"
                />
            </div>
        </li>

        <li 
            onclick="showHideOffline()"
            title="{$c->t('Show/Hide')}">
            <a class="users" href="#"></a>
        </li>

    </ul>
</div>
