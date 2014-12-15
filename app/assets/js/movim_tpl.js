/**
 * Movim Javascript Template functions
 * 
 * These are the default callback functions that users may (or may not) use.
 *
 * Note that all of them take only one parameter. Don't be fooled by this, the
 * expected parameter is actually an array containing the real parameters. These
 * are checked before use.
 *
 * Look at the comments for help.
 */

// movim_append(div, text)
function movim_append(id, html)
{
    target = document.getElementById(id);
    if(target) {
        target.insertAdjacentHTML('beforeend', html);
    }
}
// movim_prepend(div, text)
function movim_prepend(id, html)
{
    target = document.getElementById(id);
    if(target) {
        target.insertAdjacentHTML('afterbegin', html);
    }
}
// movim_fill(div, text)
function movim_fill(id, html)
{
    target = document.getElementById(id);
    if(target) {
        target.innerHTML = html;
    }
}
// movim_delete(div)
function movim_delete(id)
{
    target = document.getElementById(id);
    if(target)
        target.parentNode.removeChild(target);
}

var MovimTpl = {
    init : function() {
        if(document.getElementById('back') != null)
            document.getElementById('back').style.display = 'none';
    },
    showPanel : function() {
        movim_add_class('main section > div:first-child:nth-last-child(2) ~ div', 'enabled');
        document.getElementById('menu').style.display = 'none';
        document.getElementById('back').style.display = '';
    },
    hidePanel : function() {
        movim_remove_class('main section > div:first-child:nth-last-child(2) ~ div', 'enabled');
        document.getElementById('menu').style.display = '';
        document.getElementById('back').style.display = 'none';
    },
    showMenu : function() {
        movim_add_class('body > nav', 'active');
    }
}

movim_add_onload(function() {
    MovimTpl.init();
});
