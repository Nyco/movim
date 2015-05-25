<div id="{$jid}_discussion" class="contained">
    <section id="{$jid}_messages">
        <ul class="{if="$muc"}thin simple{else}middle{/if}" id="{$jid}_conversation"></ul>
    </section>
    <div id="{$jid}_state"></div>
</div>
<div class="chat_box">
    <ul class="thin">
        <li class="action">
            <span class="icon gray emojis_open" onclick="Chat_ajaxSmiley()">
                {$c->ajaxSmileyGet('😃')}
            </span>
            <div class="action" data-jid="{$jid}" onclick="Chat.sendMessage(this.dataset.jid, {if="$muc"}true{else}false{/if})">
                <i class="md md-send"></i>
            </div>
            <form>
                <div>
                     <textarea 
                        rows="1"
                        id="chat_textarea"
                        data-jid="{$jid}"
                        onkeypress="
                            if(event.keyCode == 13) {
                                state = 0;
                                Chat.sendMessage(this.dataset.jid, {if="$muc"}true{else}false{/if});
                                return false;
                            } else {
                                {if="!$muc"}
                                if(state == 0 || state == 2) {
                                    state = 1;
                                    {$composing}
                                    since = new Date().getTime();
                                }
                                {/if}
                            }
                            "
                        onkeyup="
                            movim_textarea_autoheight(this);
                            {if="!$muc"}
                            setTimeout(function()
                            {
                                if(state == 1 && since+5000 < new Date().getTime()) {
                                    state = 2;
                                    {$paused}
                                }
                            },5000);
                            {/if}
                            "
                        placeholder="{$c->__('chat.placeholder')}"
                    ></textarea>
                </div>
            </form>
        </li>
    </ul>
</div>
