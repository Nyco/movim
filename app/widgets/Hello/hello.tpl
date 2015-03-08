<div id="hello_widget">
    <ul class="flex active middle">
        <li class="subheader block large">Active contacts</li>
        {loop="$top"}
            <li tabindex="{$key+1}" class="block action {if="$value->getPlace()"}condensed{/if}"
                onclick="Hello_ajaxChat('{$value->jid}')">
                {$url = $value->getPhoto('s')}
                {if="$url"}
                    <span
                        class="icon bubble
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span
                        class="icon bubble color {$value->jid|stringToColor}
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <i class="md md-person"></i>
                    </span>
                {/if}

                <span>{$value->getTrueName()}</span>
                <p class="wrap">{$value->getPlace()}</p>
            </li>
        {/loop}
        <a class="block large" href="{$c->route('chat')}">
            <li>
                <span class="icon">
                    <i class="md md-forum"></i>
                </span>
                <span>{$c->__('hello.chat')}</span>
            </li>
        </a>
    </ul>
    {if="$c->supported('pubsub')"}
    <div class="card active">
        <ul id="news" class="flex medium" onclick="movim_reload('{$c->route('news')}')">
            <li class="subheader block large">News</li>
            {loop="$news"}
                <li class="block condensed" data-id="{$value->nodeid}"
                    {if="$value->title != null"}
                        title="{$value->title|strip_tags}"
                    {else}
                        title="{$c->__('menu.contact_post')}"
                    {/if}
                >
                    {if="current(explode('.', $value->origin)) == 'nsfw'"}
                        <span class="icon bubble color red">
                            <i class="md md-warning"></i>
                        </span>
                    {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                        {$url = $value->getContact()->getPhoto('s')}
                        {if="$url"}
                            <span class="icon bubble">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span
                                class="icon bubble color {$value->getContact()->jid|stringToColor}">
                                <i class="md md-person"></i>
                            </span>
                        {/if}
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
            
            <li class="action block large">
                <div class="action">
                    <i class="md md-chevron-right"></i>
                </div>
                <span class="icon">
                    <i class="md md-view-list"></i>
                </span>
                <span>{$c->__('hello.news')}</span>
            </li>
        </ul>
    </div>
    {/if}
    <ul class="active">
        <a href="{$c->route('blog', array($jid))}" target="_blank">
            <li class="condensed action">
                <div class="action">
                    <i class="md md-chevron-right"></i>
                </div>
                <span class="icon">
                    <i class="md md-wifi-tethering"></i>
                </span>
                <span>{$c->__('hello.blog_title')}</span>
                <p>{$c->__('hello.blog_text')}</p>
            </li>
        </a>
    </ul>
</div>
