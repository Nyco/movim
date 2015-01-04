(function(){
    var app = angular.module("roster", []);

    /* Controller for Rostermenu */
    app.controller("RosterMenuController", function($scope){
        $scope.lsJid = localStorage.getItem("username").replace("@", "at");
        $scope.lsRoster = localStorage.getObject($scope.lsJid + "_Roster") || {};
        $scope.lsOfflineShown = "offlineShown" in $scope.lsRoster ? $scope.lsRoster.offlineShown : false;
        
        this.checkoutAddJid = function(event){
            if(event.key == "Enter")
                Roster_ajaxSearchContact(event.target.value);
        };
        
        this.showHideOffline = function() {
            if(!$scope.lsOfflineShown){
                document.querySelector('ul#rosterlist').className = 'offlineshown';
                $scope.lsOfflineShown = true;
            }
            else{
                document.querySelector('ul#rosterlist').className = '';
                $scope.lsOfflineShown = false;
            }
        }
    });

    /* Controller for Rosterlist */
    app.controller("RosterController", function($scope){
        /* Cache variables */ 
        $scope.lsJid = localStorage.getItem("username").replace("@", "at");
        $scope.lsRoster = localStorage.getObject($scope.lsJid + "_Roster") || {};
        $scope.lsGroupState = "groupState" in $scope.lsRoster ? $scope.lsRoster.groupState : {};
        
        //this.cache = localStorage.getObject($scope.lsJid + '_cache');
        $scope.contacts = /*this.cache && ("Roster" in this.cache) && ("contactsList" in this.cache.Roster) ? localStorage.getObject($scope.lsJid + '_cache').Roster.contactsList : */[];
        $scope.groups = [];
        
        /* Dictionaries */
        $scope.lookupgroups = {};
        $scope.lookupjid = {};

        $scope.initContacts = function(list){
            //if($scope.contacts.length == 0){
                //console.log("NO cache");
                for(var i = 0; i < list.length; i++){
                    /* New group */
                    if(!(list[i].groupname in $scope.lookupgroups)){
                        el = {
                            'agroup': list[i].groupname,
                            'agroupitems': [],
                            'tombstone': false,
                        };
                        $scope.pushInPlace(el, $scope.contacts, groupnameCompare);
                        
                        /* Create a reference in the localstorage for toggling */
                        $scope.lsGroupState["rosterGroup_"+list[0].groupname] = true;
                    }
                    /* New jid */
                    if(!(list[i].jid in $scope.lookupjid)){
                        el = {
                            'ajid': list[i].jid,
                            'atruename': list[i].rosterview.name,
                            'aval': list[i].value,
                            'ajiditems': [],
                            'tombstone': false,
                        };
                        $scope.pushInPlace(el, $scope.lookupgroups[list[i].groupname].agroupitems, jidAvalCompare);
                    }
                    /* New ressource (can't just push the whole set of same jid because there is no set) */
                    if(!$scope.isInJidItems(list[i].jid, list[i].ressource)){
                        $scope.pushInPlace(list[i], $scope.lookupjid[list[i].jid].ajiditems, ressourceCompare);
                    }
                }
            //}
            /* Rebound from cache */
            /*else{
                console.log("cache");
                for(var i = 0; i < $scope.contacts.length; i++){
                    if(!$scope.contacts[i].tombstone){
                        $scope.lookupgroups[$scope.contacts[i].agroup] = $scope.contacts[i];
                        for(var j = 0; j < $scope.contacts[i].agroupitems.length; j++){
                            if(!$scope.contacts[i].agroupitems[j].tombstone)
                                $scope.lookupjid[$scope.contacts[i].agroupitems[j].ajid] = $scope.contacts[i].agroupitems[j];
                            else // Cleanup tombstoned jid
                                $scope.contacts[i].agroupitems.splice(j, 1);
                        }
                    }
                    else // Cleanup tombstoned groups 
                        $scope.contacts.splice(i, 1);
                }
            }*/
            $scope.$apply();
        };
        
        $scope.initGroups = function(list){
            for(var i in list){
                if(!("rosterGroup_"+i in $scope.lsGroupState)){
                    list[i] = true;
                    $scope.lsGroupState["rosterGroup_"+i] = true;
                }
                else list[i] = $scope.lsGroupState["rosterGroup_"+i];
            }
            
            $scope.groups = list;
            $scope.$apply();
        };

        $scope.deleteContact = function(jid){
            $scope.lookupjid[jid].tombstone = true;
            
            $scope.$apply();
        };
        
        $scope.isInJidItems = function(jid, ressource){
            l = $scope.lookupjid[jid].ajiditems.length;
            for(var i = 0; i < l; i++){
                if($scope.lookupjid[jid].ajiditems[i].ressource == ressource)
                    return true;
            }
            return false;
        };
        
        $scope.pushInPlace = function(element, array, comparer){
            if(array === $scope.contacts){
                dico = $scope.lookupgroups;
                key = "agroup";
            } else {
                dico = $scope.lookupjid;
                key = "ajid";
            }
            
            /* Put element in the right place inside array */
            index = locationOf(element, array, comparer); 
            array.splice(index, 0, element);
            
            /* Update dictionary from the appropriate index */
            for(var i=index; i<array.length; i++){
                dico[array[i][key]] = array[i];
            }
        };

        $scope.updateContact = function(list){
            if($scope.contacts === null) $scope.contacts = [];
            /* Group change */
            if((list[0].jid in $scope.lookupjid) 
                && !($scope.lookupjid[list[0].jid].ajiditems[0].groupname == list[0].groupname)){
                /* Kill jid from old location or whole group if it's the only jid */
                oldgroupname = $scope.lookupjid[list[0].jid].ajiditems[0].groupname;
                if($scope.lookupgroups[oldgroupname].agroupitems.length == 1)
                    $scope.lookupgroups[oldgroupname].tombstone = true;
                else{
                    $scope.lookupjid[list[0].jid].tombstone = true;
                }
            }
            /* New group is not in the list */
            if(!(list[0].groupname in $scope.lookupgroups)) {
                /* Create group */
                el = {
                    'agroup': list[0].groupname,
                    'agroupitems': [],
                    'tombstone': false,
                };
                $scope.pushInPlace(el, $scope.contacts, groupnameCompare);
                /* Reference in the localstorage for toggling */
                $scope.lsGroupState["rosterGroup_"+list[0].groupname] = true;
            }
                
            /* Jid is in the list and no group change */
            if(list[0].jid in $scope.lookupjid 
                && ($scope.lookupjid[list[0].jid].ajiditems[0].groupname == list[0].groupname))
            {
                $scope.lookupjid[list[0].jid].aval = list[0].value;
                $scope.lookupjid[list[0].jid].atruename = list[0].rosterview.name;
                $scope.lookupjid[list[0].jid].ajiditems = list;
                $scope.lookupgroups[list[0].groupname].agroupitems.sort(jidAvalCompare);
            }
            else{
                el = {
                    'ajid':     list[0].jid,
                    'atruename':     list[0].rosterview.name,
                    'aval':     list[0].value,
                    'ajiditems': list,
                    'tombstone': false,
                };
                $scope.pushInPlace(el, $scope.lookupgroups[list[0].groupname].agroupitems, jidAvalCompare);
            }
            $scope.$apply();
        };

        this.showHideGroup = function(g){
            ls = $scope.lsGroupState["rosterGroup_"+g];
            if(ls === null){
                ls = $scope.lsGroupState.rosterGroup_Ungrouped;
                g = "Ungrouped";
            }

            ls = !ls;

            $scope.lsGroupState["rosterGroup_"+g] = ls;
            $scope.groups[g] = ls;
        };

        this.postChatAction = function(c){
            eval(c.rosterview.openchat);
        };
        
        this.postJingleAction = function(c){
            Popup.close(); 
            Popup.open(c.jid + "/" + c.ressource);
        };

        this.groupIsShown = function(grp){
            if(typeof $scope.groups[grp] != "undefined"){
                return $scope.groups[grp];
            }
            else return $scope.groups["Ungrouped"];
        };

        this.offlineIsShown = function(){
            if("offlineShown" in $scope.lsRoster && $scope.lsRoster.offlineShown)
                return "offlineshown";
            else
                return "";
        };

        this.getContactTitle = function(c){
            title = c.rosterview.name.toLowerCase() + " - " + c.jid;
            if(c.status) title += " - " + c.status;
            return title;
        };

        this.getContactClient = function(c){
            liclass = "";
            if(c.rosterview.client)
                liclass = "client " + c.rosterview.client;
            return liclass;
        };
        
        this.getJidStatusRessource = function(c){
            lititle = c.jid;
            if(c.status != '') lititle += " - " + c.status;
            lititle += " - " + c.ressource;
            return lititle;
        };
        
        this.getPresenceInactiveClient = function(c){
            liclass = c.rosterview.presencetxt + " " + contact.rosterview.inactive;
            if(c.client) liclass += " client " + c.client;
            return liclass;
        };
    });
})();

window.onunload = window.onbeforeunload = function(e){
    var lsjid = angular.element(roster).scope().lsJid;
    
    // Cache Roster list in jid_cache.Roster 
    /*if(localStorage.getObject(lsjid + "_cache") === null)
        localStorage.setObject(lsjid + "_cache", {"Roster": {"contactsList": angular.element(roster).scope().contacts}});
    else{
        var nv = localStorage.getObject(lsjid + "_cache");
        nv.Roster = {"contactsList": angular.element(roster).scope().contacts};
        localStorage.setObject(lsjid + "_cache", nv);
    }
    */
    
    // Move this to disconnection moment ?? 
    // Keep group states in jid_Roster.groupStates 
    angular.element(roster).scope().lsRoster.groupState = angular.element(roster).scope().lsGroupState;
    angular.element(roster).scope().lsRoster.offlineShown = angular.element(rostermenu).scope().lsOfflineShown;
    localStorage.setObject(lsjid + "_Roster", angular.element(roster).scope().lsRoster);
};

/* Functions to call angular inner functions */
function initContacts(tab){
    if(tab.length == 0)
        angular.element(roster).scope().contacts = null;
    else
        angular.element(roster).scope().initContacts(JSON.parse(tab));

    Roster.refresh();
}

function initGroups(tab){
    angular.element(roster).scope().initGroups(JSON.parse(tab));
}

function updateContact(tab){
    angular.element(roster).scope().updateContact(JSON.parse(tab));
}

function deleteContact(jid){
    angular.element(roster).scope().deleteContact(jid);
}
 

/* === PushInPlace subfunctions === */
function locationOf(element, array, comparer, start, end) {
    if (array.length === 0)
        return 0;
    start = start || 0;
    end = end || array.length;
    var pivot = (start + end) >> 1;  // >>1 = /2
    var c = comparer(element, array[pivot]);
    if ((end - start) <= 1){
        return (c == -1) ? pivot : pivot+1;
    }
    
    switch (c) {
        case -1: return locationOf(element, array, comparer, start, pivot);
        case 0: return pivot;
        case 1: return locationOf(element, array, comparer, pivot, end);
    };
};

/* Object comparison functions */
var groupnameCompare = function(a, b) {
    return a.agroup.localeCompare(b.agroup);
};

var ressourceCompare = function(a, b) {
    n = a.value - b.value;
    return n ? n < 0 ? -1 : 1 : 0;
};
/* Presence + alphabetical comparison */
var jidAvalCompare = function(a, b) {
    n = a.aval - b.aval;
    if(n == 0){
        n = a.atruename.localeCompare(b.atruename);
    }
    return n ? n < 0 ? -1 : 1 : 0;
};


/* === Old functions still in use === */
MovimWebsocket.attach(function(){
    Roster_ajaxGetRoster();
});

var Roster = {
    init : function() {
        var search      = document.querySelector('#rostersearch');
        var roster      = document.querySelector('#roster');
        var rosterlist  = document.querySelector('#rosterlist');
        
        var roster_classback      = document.querySelector('#roster').className;
        var rosterlist_classback  = document.querySelector('#rosterlist').className;

        roster.onblur  = function() {
            roster.className = roster_classback;
            rosterlist.className = rosterlist_classback;
        };
        search.oninput = function(event) {
            if(search.value.length > 0) {
                roster.className = 'search';
                rosterlist.className = 'offlineshown';
            } else {
                roster.className = roster_classback;
                rosterlist.className = rosterlist_classback;
            }

            // We clear the old search
            var selector_clear = '#rosterlist div > li:not(.subheader)';
            var li = document.querySelectorAll(selector_clear);

            for(i = 0; i < li.length; i++) {
                li.item(i).className = '';
            }

            // We select the interesting li
            var selector = '#rosterlist div > li[title*="' + search.value.toLowerCase() + '"]:not(.subheader)';
            var li = document.querySelectorAll(selector);

            for(i = 0; i < li.length; i++) {
                li.item(i).className = 'found';
            }
        };
    },
    refresh: function() {
        var items = document.querySelectorAll('#rosterlist div > li:not(.subheader)');

        var i = 0;
        while(i < items.length -1)
        {
            items[i].onclick = function(e) {
                Contact_ajaxGetContact(this.id);
                Roster.reset(items);
                movim_add_class(this, 'active');
                document.querySelector('#roster').className = '';
            }
            i++;
        }
    },

    reset: function(list) {
        for(i = 0; i < list.length; i++) {
            movim_remove_class(list[i], 'active');
        }
    },

    setFound : function(jid) {
        document.querySelector('input[name=searchjid]').value = jid;
    }
}

MovimWebsocket.attach(function() {
    Roster.refresh();
});


movim_add_onload(function(){    
    Roster.init();
});
