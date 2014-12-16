<section>
    <ul class="simple">
        <li class="subheader">{$c->__('roster.search')}</li>
        <li>
            <form>
                <div>
                    <input 
                        name="searchjid" 
                        type="email"
                        title="{$c->__('roster.jid')}"
                        placeholder="user@server.tld"
                        onkeyup="if(this.validity.valid == true) { {$search} }"
                    />
                    <label for="searchjid">{$c->__('roster.add_contact_info1')}</label>
                </div>
            </form>
        </li>
    </ul>
    <div id="search_results">

    </div>
</section>
<div class="actions">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="{$calllogout} movim_toggle_class('#logoutlist', 'show');" class="button flat">
        {$c->__('button.add')}
    </a>
</div>
