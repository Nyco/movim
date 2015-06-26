<div>
    <span id="menu" class="on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
    <span class="on_desktop icon"><i class="zmdi zmdi-pages"></i></span>
    <form>
        <div>
            <div class="select">
                <select onchange="window[this.value].apply(this, [this.options[this.selectedIndex].dataset['server']]);" name="language" id="language">
                    <option value="Groups_ajaxSubscriptions" selected="selected">{$c->__('groups.subscriptions')}</option>
                    {loop="$servers"}
                        {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
                            <option value="Groups_ajaxDisco" data-server="{$value->server}">{$value->server} ({$value->number})</option>
                        {/if}
                    {/loop}
                </select>
            </div>
        </div>
    </form>
</div>
