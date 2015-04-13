{if="isset($attachements.pictures)"}
    <header
        class="big"
        style="
            background-image: url('{$attachements['pictures'][0]['href']}');">
    </header>
{/if}

<article>
    <header>
        <ul class="thick">
            <li class="condensed">
                {if="$post->node == 'urn:xmpp:microblog:0'"}
                    <a href="{$c->route('contact', $post->getContact()->jid)}">
                        {$url = $post->getContact()->getPhoto('s')}
                        {if="$url"}
                            <span class="icon bubble">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span class="icon bubble color {$post->getContact()->jid|stringToColor}">
                                <i class="md md-person"></i>
                            </span>
                        {/if}
                    </a>
                {else}
                <!--<a href="{$c->route('node', array($post->jid, $post->node))}">-->
                    <span class="icon bubble color {$post->node|stringToColor}">{$post->node|firstLetterCapitalize}</span>
                <!--</a>-->
                {/if}
                <span {if="$post->title != null"}title="{$post->title|strip_tags}"{/if}>
                    {if="$post->title != null"}
                        {$post->title}
                    {else}
                        {$c->__('post.default_title')}
                    {/if}
                </span>
                <p>
                    {if="$post->node == 'urn:xmpp:microblog:0'  && $post->getContact()->getTrueName() != ''"}
                        <a href="{$c->route('contact', $post->getContact()->jid)}">{$post->getContact()->getTrueName()}</a> - 
                    {/if}
                    {$post->published|strtotime|prepareDate}
                </p>
            </li>
        </ul>
    </header>

    <section>
        {$post->contentcleaned}
    </section>

    <footer>
        <ul class="middle divided spaced">
            {if="isset($attachements.links)"}
                {loop="$attachements.links"}
                    <li>
                        <span class="icon small"><img src="http://icons.duckduckgo.com/ip2/{$value.url.host}.ico"/></span>
                        <a href="{$value.href}" class="alternate" target="_blank">
                            <span>{$value.href|urldecode}</span>
                        </a>
                    </li>
                {/loop}
            {/if}
            {if="isset($attachements.files)"}
                {loop="$attachements.files"}
                    <li>
                        <a
                            href="{$value.href}"
                            class="enclosure"
                            type="{$value.type}"
                            target="_blank">
                            <span class="icon small gray">
                                <span class="md md-attach-file"></span>
                            </span>
                            <span>{$value.href|urldecode}</span>
                        </a>
                    </li>
                {/loop}
            {/if}
            {if="isset($attachements.pictures)"}
                {loop="$attachements.pictures"}
                    <li>
                        <a href="{$value.href}">
                            <img
                                src="{$value.href}"
                                rel="{$value.rel}"
                                type="{$value.type}"/>
                        </a>
                    </li>
                {/loop}
            {/if}
        </ul>
        {if="$post->isMine()"}
            <ul class="thick">
                <li class="action">
                    <form>
                        <div class="action">
                            <div class="checkbox">
                                <input
                                    type="checkbox"
                                    id="privacy"
                                    name="privacy"
                                    {if="$post->privacy"}
                                        checked
                                    {/if}
                                    onclick="Post_ajaxTogglePrivacy('{$post->nodeid}')">
                                <label for="privacy"></label>
                            </div>
                        </div>
                    </form>
                    <span class="icon bubble color red">
                        <i class="md md-public"></i>
                    </span>
                    <span>
                        <a target="_blank" href="{$c->route('blog', array($post->origin))}">
                            {$c->__('post.public')}
                        </a>
                    </span>
                </li>
            </ul>
        {/if}
    </footer>

    <div id="comments"></div>
</article>
