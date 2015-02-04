<div id="admincomp" class="tabelem" title="{$c->__('admin.compatibility')}">
    <div>
        <figure>
            <div id="webserver">

            </div>
            <div id="movim-daemon" class="link vertical disabled"><i class="md md-settings"></i></div>
            <div id="movim-browser" class="link horizontal success"><i class="md md-open-in-browser"></i></div>
            <div id="browser-daemon" class="link horizontal error"><i class="md md-settings-ethernet"></i></div>
            <div id="daemon-xmpp" class="link horizontal error"><i class="md md-import-export"></i></div>
            <div id="movim-database" class="link vertical {if="$dbconnected"}success {if="$dbinfos > 0"}warning{/if} {else}error{/if}">
                <i class="md md-data-usage"></i>
            </div>
            <div id="movim-api" class="link horizontal disabled"><i class="md md-cloud"></i></div>
            <div id="browser_block">
                Browser
            </div>
            <div id="movim_block">
                Movim Core
            </div>
            <div id="daemon_block">
                Movim Daemon
            </div>
            <div id="database_block" class="{if="$dbconnected"}success {if="$dbinfos > 0"}warning{/if} {else}error{/if}">
                Database
            </div>
            <div id="api_block">
                API
            </div>
            <div id="xmpp_block">
                XMPP
            </div>
        </figure>
    </div>

    <ul>
        <li class="subheader">
            {$c->__('compatibility.info')}
        </li>
        
        {if="$dbconnected"}
            {if="$dbinfos > 0"}
                <li>
                    <span class="icon bubble color orange"><i class="md md-refresh"></i></span>
                    <span>The database need to be updated, go to the database panel to fix this</span>
                </li>
            {else}
                <script type="text/javascript">AdminTest.databaseOK = true</script>
            {/if}
        {else}
            <li class="condensed">
                <span class="icon bubble color red"><i class="md md-data-usage"></i></span>
                <span>Database connection error</span>
                <p>Check if database configuration exist in the <code>config/</code> folder and fill it with proper values</p>
            </li>
        {/if}

        <li id="websocket_error">
            <span class="icon bubble color red">
                <i class="md md-settings-ethernet"></i> 
            </span>
            <span>
                WebSocket connection error, check if the Movim Daemon is running and is reacheable
            </span> 
        </li>
        
        <li id="xmpp_websocket_error">
            <span class="icon bubble color red">
                <i class="md md-settings-ethernet"></i>
            </span>
            <span>
                XMPP Websocket connection error, please check the validity of the URL given in the General Configuration. <code>{$websocketurl}</code>
            </span>
        </li>

        {if="!$c->version()"}
            <li class="condensed">
                <span class="icon color bubble red">
                    <i class="md md-sync-problem"></i>
                </span>
                <span>{$c->__('compatibility.php1', PHP_VERSION)}</span>
                <p>{$c->__('compatibility.php2')}</p>
            </li>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!extension_loaded('imagick')"}
            <li>
                <span class="icon color bubble red">
                    <i class="md md-image"></i>
                </span>
                <span>
                    {$c->__('compatibility.imagick')}
                </span>
            </div>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!$c->testDir(DOCUMENT_ROOT)"}
            <li>
                <span class="icon color bubble red">
                    <i class="md md-folder"></i>
                </span>
                <span>{$c->__('compatibility.rights')}</span>
            </li>
            <script type="text/javascript">AdminTest.disableMovim()</script>
        {/if}

        {if="!$_SERVER['HTTP_MOD_REWRITE']"}
            <li>
                <span class="icon bubble color orange">
                    <i class="md md-mode-edit"></i>
                </span>
                <span>{$c->__('compatibility.rewrite')}</span>
            </li>
        {/if}
    </ul>
    <script type="text/javascript">AdminTest.testXMPPWebsocket('{$websocketurl}');</script>
</div>
