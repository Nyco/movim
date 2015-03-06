<li onclick="{$dialog} MovimTpl.hideMenu()" class="condensed action">
    <div class="action">
        <i class="md md-edit"></i>
    </div>
    <span class="icon bubble status {$presencetxt[$presence->value]}" style="background-image: url({$me->getPhoto('m')})"></span>
    <span>{$me->getTrueName()}</span>
    <p class="wrap">{$presence->status}</p>
</li>
<a class="classic {if="!$c->supported('pubsub')"}disabled{/if}" href="{$c->route('conf')}">
    <li>
        <span class="icon">
            <i class="md md-settings"></i>
        </span>
        <span>{$c->__('page.configuration')}</span>
    </li>
</a>
<a class="classic" href="{$c->route('help')}">
    <li>
        <span class="icon">
            <i class="md md-help"></i>
        </span>
        <span>{$c->__('page.help')}</span>
    </li>
</a>
