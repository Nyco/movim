<span id="back" class="icon" onclick="MovimTpl.hidePanel()"><i class="md md-arrow-back"></i></span>

<ul class="active">
    <li onclick="Chats_ajaxClose('{$jid}'); MovimTpl.hidePanel();">
        <span class="icon">
            <i class="md md-close"></i>
        </span>
    </li>
</ul>
{if="$contact != null"}
    <h2>{$contact->getTrueName()}</h2>
{else}
    <h2>{$jid}</h2>
{/if}
