<div id="{$jid}_discussion" class="actions fixed contained">
    <div id="{$jid}_messages">
        {$messages}
    </div>
    <div>
        <ul>
            <li>
                <span class="icon gray">
                    <i class="md md-create"></i>
                </span>
                <div class="control" onclick="{$smiley}">
                    <i class="md md-mood"></i>
                </div>
                <form>
                    <div>
                         <textarea 
                            rows="1"
                            id="textarea{$contact->jid}"
                            onkeypress="
                                if(event.keyCode == 13) {
                                    state = 0;
                                    {$send}
                                    return false;
                                } else {
                                    if(state == 0 || state == 2) {
                                        state = 1;
                                        {$composing}
                                        since = new Date().getTime();
                                    }
                                }
                                "
                            onkeyup="
                                movim_textarea_autoheight(this);
                                setTimeout(function()
                                {
                                    if(state == 1 && since+5000 < new Date().getTime()) {
                                        state = 2;
                                        {$paused}
                                    }
                                },5000); 
                                "
                            placeholder="{$c->__('chat.placeholder')}"
                        ></textarea>
                        <label>Your message</label>
                    </div>
                </form>
            </li>
        </ul>
    </div>
</div>
