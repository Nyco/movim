{if="!BROWSER_COMP"}
    <div class="message warning">
        {$c->__('error.too_old')}
    </div>
{else}
<div id="login_widget">
    <div id="sessions" class="dialog actions"></div>

    <div class="dialog">
        <section>
            <span class="info">{$c->__('connected')} {$connected} / {$pop}</span>
            <h3>{$c->__('page.login')}</h3>
            <form
                data-action="{$submit}"
                name="login">
                <div>
                    <input type="email" name="login" id="login" autofocus required disabled
                        placeholder="{$c->__('form.username')}"/>
                    <label for="login">{$c->__('form.username')}</label>
                </div>
                <div>
                    <input type="password" name="pass" id="pass" autocomplete="off" required disabled
                        placeholder="{$c->__('form.password')}"/>
                    <label for="pass">{$c->__('form.password')}</label>
                </div>
                <div>
                    <ul class="simple thin">
                        <li>
                            <div class="control">
                                <input
                                    type="submit"
                                    disabled
                                    data-loading="{$c->__('button.connecting')}"
                                    value="{$c->__('button.come_in')}"
                                    class="button flat"/> 
                            </div>
                            <a id="return_sessions" class="button flat" href="#" onclick="Login.backToChoose()">
                                {$c->__('account.title')}
                            </a>
                        </li>
                    </ul>
                </div>
            </form>

            <ul class="thin simple">
                <li class="new_account">
                    <span>{$c->__('form.no_account')}
                        <a class="" href="{$c->route('account')}">
                            {$c->__('form.create_one')}
                        </a>
                    </span>
                </li>
            </ul>
        </section>
    </div>
</div>

{if="isset($info) && $info != ''"}
    <div class="message warning">
        {$info}
    </div>
{/if}
<div id="error_websocket" class="snackbar">
    {$c->__('error.websocket')}
</div>

{/if}


            <!--<div class="clear"></div>-->
            <!--
            <div class="clear"></div>-->
        
            <!--<ul id="loginhelp">
                {if="$whitelist_display == true"}
                    <li id="whitelist">
                        <p>{$c->__('whitelist.info')}</p>
                        <p style="font-weight:bold; text-align:center; margin:0.5em;">{$whitelist}</p>
                        <p>{$c->__('whitelist.info2', '<a href="http://pod.movim.eu">', '</a>')}</p>
                    </li>
                {else}
                    <li id="jabber">{$c->__('account.jabber')}
                        <a href="#" onclick="fillExample('demonstration@movim.eu', 'demonstration');">
                            {$c->__('account.demo')}
                        </a>
                    </li>
                    <li id="gmail">
                        {$gmail}
                    </li>
                    <li id="facebook">
                        {$facebook}
                    </li>
                {/if}
            </ul>-->
