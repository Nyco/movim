<div id="roster" ng-controller="RosterController as rosterCtrl">        
    <form>
        <div>
            <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
            <label for="search">{$c->__('roster.search')}</label>
        </div>
    </form>
    <ul id="rosterlist" class="{{rosterCtrl.offlineIsShown()}} active">
        <span ng-hide="contacts != null" class="nocontacts">
            {$c->__('roster.no_contacts')}
            <br />
            <br />
            <a class="button color green" href="{$c->route('explore')}"><i class="fa fa-compass"></i> {$c->__('page.explore')}</a>
        </span>
        
        <li class="subheader search">Results **FIXME**</li>
        <div ng-show="contacts != null && !group.tombstone" ng-repeat="group in contacts" id="group{{group.agroup}}" ng-class="{groupshown: rosterCtrl.groupIsShown(group.agroup)}" >
            <li class="subheader" ng-click="rosterCtrl.showHideGroup(group.agroup)">{{group.agroup}}</li>
            <li ng-repeat="myjid in group.agroupitems" ng-hide="myjid.tombstone" id="{{myjid.ajid}}" class="{{myjid.ajiditems[0].rosterview.presencetxt}}" ng-attr-title="{{rosterCtrl.getContactTitle(myjid.ajiditems[0])}}">
                <!-- Rostersearch look this way for an angularJS solution http://www.bennadel.com/blog/2487-filter-vs-nghide-with-ngrepeat-in-angularjs.htm -->
                <ul class="contact">
                    <li ng-repeat="contact in myjid.ajiditems" class="{{contact.rosterview.presencetxt}} {{contact.rosterview.inactive}} condensed" ng-class="rosterCtrl.getContactClient(contact)" >
                        <span class="icon bubble">
                            <img
                                class="avatar"
                                src="{{contact.rosterview.avatar}}"
                                alt="avatar"
                            />
                        </span>
                        <div class="chat on" ng-click="rosterCtrl.postChatAction(contact)" ></div>
                        <div ng-if="contact.rosterview.type == 'handheld'" class="infoicon mobile"></div>
                        <div ng-if="contact.rosterview.type == 'web'" class="infoicon web"></div>
                        <div ng-if="contact.rosterview.type == 'bot'" class="infoicon bot"></div>
                        <div ng-if="contact.rosterview.tune" class="infoicon tune"></div>
                        <div
                            ng-if="contact.rosterview.jingle"
                            class="infoicon jingle"
                            ng-click="rosterCtrl.postJingleAction(contact)">
                        </div>

                        <a href="{{contact.rosterview.friendpage}}">
                            {{contact.rosterview.name}}
                        </a>
                            <p class="wrap">
                                <span ng-if="contact.status != ''">{{contact.status}} -</span>
                                 {{contact.ressource}}
                            </p>

                        </a>
                    </li>
                </ul>
            </li>
        </div>
    </ul>
</div>
<!--
<div id="rostermenu" class="menubar" ng-controller="RosterMenuController as rosterMenuCtrl">
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
                    ng-keypress="rosterMenuCtrl.checkoutAddJid(event=$event)"
                />

            </div>
        </li>

        <li 
            ng-click="rosterMenuCtrl.showHideOffline()"
            title="{$c->__('roster.show_hide')}">
            <a class="users" href="#"></a>
        </li>

    </ul>
</div>
-->
