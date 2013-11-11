/**
 * Movim Base
 * 
 * Some basic functions essential for Movim
 */ 

var movimPollHandlers = new Array();
var onloaders = new Array();

/**
 * @brief Adds a function to the onload event
 * @param function func
 */
function movim_add_onload(func)
{
    onloaders.push(func);
}

/**
 * @brief Function that is run once the page is loaded.
 */
function movim_onload()
{
    for(var i = 0; i < onloaders.length; i++) {
        if(typeof(onloaders[i]) === "function")
    	    onloaders[i]();
    }
}

/**
 * Set a global var for widgets to see if document is focused
 */
var document_focus = true;
var document_title = document.title;
var messages_cpt = 1;
document.onblur = function() { document_focus = false; }
document.onfocus = function() { document_focus = true; document.title = document_title; messages_cpt = 1; }

/**
 * @brief Increment the counter of the title
 */
function movim_title_inc() {
	document.title='[' + messages_cpt + '] ' + document_title ;
	messages_cpt++;
}

/**
 * TODO : remove this function
 */
function movim_change_class(params) {
    var node = document.getElementById(params[0]);
    var tmp;
    for (var i = 0; i < node.childNodes.length; i++) {
        tmp=node.childNodes[i];
        tmpClass = tmp.className;
        if (typeof tmpClass != "undefined" && tmp.className.match(/.*protect.*/)) {
            privacy = node.childNodes[i];
            break;
        }
    }      

    privacy.className = params[1];
    privacy.title = params[2];
}

/**
 * Geolocalisation function
 * TODO : remove this function
 */

function setPosition(node) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition( 
            function (position) {
                var poss = position.coords.latitude +','+position.coords.longitude;
                node.value = poss;
            }, 
            // next function is the error callback
            function (error) { }
            );
    }
}
