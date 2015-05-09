<li
    id="{$contact->jid}_chat_item"
    data-jid="{$contact->jid}"
    class="
        {if="isset($message)"}condensed{/if}
        {if="$contact->last > 60"} inactive{/if}
        {if="$caps && in_array($caps->type, array('handheld', 'phone'))"}
            action
        {/if}
        "
    title="{$contact->jid}">
    {if="$caps && in_array($caps->type, array('handheld', 'phone'))"}
        <div class="action">
            <i class="md md-smartphone"></i>
        </div>
    {/if}
    <span data-key="chat|{$contact->jid}" class="counter bottom"></span>
    {$url = $contact->getPhoto('s')}
    {if="$url"}
        <span class="icon bubble {if="isset($presence)"}status {$presence}{/if}">
            <img src="{$url}">
        </span>
    {else}
        <span class="icon bubble color {$contact->jid|stringToColor} {if="isset($presence)"}status {$presence}{/if}">
            <i class="md md-person"></i>
        </span>
    {/if}
    <span>{$contact->getTrueName()}</span>
    {if="isset($message)"}
        <span class="info">{$message->published|strtotime|prepareDate}</span>
        {if="preg_match('#^\?OTR#', $message->body)"}
            <p><i class="md md-lock"></i> {$c->__('message.encrypted')}</p>
        {else}
            <p>{$message->body|prepareString|strip_tags}</p>
        {/if}
    {/if}
</li>
