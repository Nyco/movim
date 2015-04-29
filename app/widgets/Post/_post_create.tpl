<form name="post" class="padded_top_bottom">
    <input type="hidden" name="to" value="{$to}">
    <input type="hidden" name="node" value="urn:xmpp:microblog:0">
    <div>
        <input type="text" name="title" placeholder="{$c->__('post.title')}">
        <label for="title">{$c->__('post.title')}</label>
    </div>
    <div>
        <textarea name="content" placeholder="{$c->__('post.content')}" onkeyup="movim_textarea_autoheight(this);"></textarea>
        <label for="content">{$c->__('post.content')}</label>
    </div>
    <div>
        <input
            type="url"
            name="embed"
            placeholder="http://myawesomewebsite.com/ or http://mynicepictureurl.com/"
            onPaste="var e=this; setTimeout(function(){Post_ajaxEmbedTest(e.value);}, 4);"
        >
        <label for="embed">{$c->__('post.link')}</label>
        <ul class="middle flex active">
            <li class="subheader">{$c->__('post.embed_tip')}</li>
            <a class="block" target="_blank" href="http://imgur.com/">
                <li class="block action">
                    <div class="action">
                        <i class="md md-chevron-right"></i>
                    </div>
                    <span class="bubble icon">
                        <img src="https://userecho.com/s/logos/2015/2015.png">
                    </span>
                    Imgur
                </li>
            </a>
            <a class="block" target="_blank" href="https://www.flickr.com/">
                <li class="action">
                    <div class="action">
                        <i class="md md-chevron-right"></i>
                    </div>
                    <span class="bubble icon">
                        <img src="https://www.flickr.com/apple-touch-icon.png">
                    </span>
                    Flickr
                </li>
            </a>
        </ul>

        <article>
            <section>
                <content id="preview"></content>
            </section>
        </article>
        <div id="gallery"></div>
    </div>
</form>
