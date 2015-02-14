{if="$items"}
    {if="$page == 0"}
        <div id="menu_refresh"></div>
        <ul class="thick active divided" id="menu_wrapper">
    {/if}
    
    {loop="$items"}
        <li class="condensed" data-id="{$value->nodeid}"
            {if="$value->title != null"}
                title="{$value->title|strip_tags}"
            {else}
                title="{$c->__('menu.contact_post')}"
            {/if}
        >
            {if="current(explode('.', $value->jid)) == 'nsfw'"}
                <span class="icon bubble color red">
                    <i class="md md-warning"></i>
                </span>
            {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                <span class="icon bubble">
                    <!--<i class="md md-create"></i>-->
                    <img src="{$value->getContact()->getPhoto('s')}">
                </span>                    
            {else}
                <span class="icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
            {/if}

            {if="$value->title != null"}
                <span>{$value->title}</span>
            {else}
                <span>{$c->__('menu.contact_post')}</span>
            {/if}

            <span class="info">{$value->published|strtotime|prepareDate}</span>
            
            {if="$value->node == 'urn:xmpp:microblog:0'"}
                <p class="wrap">{$value->getContact()->getTrueName()}</p>
            {else}
                <p class="wrap">{$value->node}</p>
            {/if}
        </li>
    {/loop}
    <li onclick="{$history} this.parentNode.removeChild(this);">
        <span class="icon"><i class="md md-history"></i></span>
        {$c->__('post.older')}
    </li>
        
    {if="$page == 0"}
        </ul>
    {/if}
    
{elseif="$page == 0"}
    <div id="menu_refresh"></div>
    <br/>
    <ul id="menu_wrapper">
        <li class="condensed">
            <span class="icon orange">
                <i class="md md-format-align-left"></i>
            </span>
            <span>{$c->__('menu.empty_title')}</span>
            <p>{$c->__('menu.empty')}</p>
        </li>
    </ul>
{/if}
