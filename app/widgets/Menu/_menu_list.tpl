<div id="menu_refresh"></div>
{if="$items"}
    <ul class="thick active divided">
        {loop="$items"}
            <li class="condensed" data-id="{$value->nodeid}">
                {if="current(explode('.', $value->jid)) == 'nsfw'"}
                    <span class="icon bubble color red">
                        <i class="fa fa-exclamation-triangle"></i>
                    </span>
                {else}
                    <span class="icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                {/if}

                <span>{$value->title}</span><span class="info">{$value->published|strtotime|prepareDate}</span>
                <p class="wrap">{$value->node}</p>
            </li>
        {/loop}

        <li onclick="{$history} this.parentNode.removeChild(this);">
            <span class="icon"><i class="md md-history"></i></span>
            {$c->__('post.older')}
        </li>
    </ul>
{elseif="$page == 0"}
    <br/>
    <ul>
        <li class="condensed">
            <span class="icon bubble color orange">
                <i class="md md-format-align-left"></i>
            </span>
            <span>{$c->__('menu.empty_title')}</span>
            <p>{$c->__('menu.empty')}</p>
        </li>
    </ul>
{/if}
