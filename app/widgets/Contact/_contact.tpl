{if="$contact != null"}
    {$url = $contact->getPhoto('s')}

    <header class="big"
        {if="$url"}
            style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0) 100%), url('{$contact->getPhoto('xxl')}');"
        {else}
            style="background-color: rgba(62,81,181,1);"
        {/if}
        >
        <ul class="thick">
            <li>
                {if="$url"}
                    <span class="icon bubble">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="icon bubble color {$contact->jid|stringToColor}">
                        <i class="md md-person"></i>
                    </span>
                {/if}
                <span>
                    <h2>{$contact->getTrueName()}</h2>
                </span>
            </li>
            {if="$caps"}
                <li>
                    <span class="icon">
                        <i class="md
                            {if="$caps->type == 'handheld' || $caps->type == 'phone'"}
                                md-phone-android
                            {elseif="$caps->type == 'bot'"}
                                md-memory
                            {else}
                                md-laptop
                            {/if}
                        ">
                        </i>
                    </span>
                    <span>
                        {$caps->name}
                        {if="isset($clienttype[$caps->type])"}
                            - {$clienttype[$caps->type]}
                        {/if}
                    </span>
                </li>
            {/if}
        </ul>
    </header>
    <br />

    <ul class="flex">
        {if="$contact->delay != null"}
        <li class="condensed block">
            <span class="icon brown"><i class="md md-restore"></i></span>
            <span>{$c->__('last.title')}</span>
            <p>{$contact->delay}</p>
        </li>
        {/if}

        {if="$contact->fn != null"}
        <li class="condensed block">
            <span class="icon gray">{$contact->fn|firstLetterCapitalize}</span>
            <span>{$c->__('general.name')}</span>
            <p>{$contact->fn}</p>
        </li>
        {/if}

        {if="$contact->nickname != null"}
        <li class="condensed block">
            <span class="icon gray">{$contact->nickname|firstLetterCapitalize}</span>
            <span>{$c->__('general.nickname')}</span>
            <p>{$contact->nickname}</p>
        </li>
        {/if}

        {if="strtotime($contact->date) != 0"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-cake"></i></span>
            <span>{$c->__('general.date_of_birth')}</span>
            <p>{$contact->date|strtotime|prepareDate:false}</p>
        </li>
        {/if}

        {if="$contact->url != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-link"></i></span>
            <span>{$c->__('general.website')}</span>
            <p class="wrap">
                {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                    <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                {else}
                    {$contact->url}
                {/if}
            </p>
        </li>
        {/if}

        {if="$contact->email != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-mail"></i></span>
            <span>{$c->__('general.email')}</span>
            <p><img src="{$contact->getPhoto('email')}"/></p>
        </li>
        {/if}

        {if="$contact->getMarital() != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-people"></i></span>
            <span>{$c->__('general.marital')}</span>
            <p>{$contact->getMarital()}</p>
        </li>
        {/if}

        {if="$contact->getGender() != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-face-unlock"></i></span>
            <span>{$c->__('general.gender')}</span>
            <p>{$contact->getGender()}</p>
        </li>
        {/if}

        {if="$contactr->delay != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-av-timer"></i></span>
            <span>{$c->__('last.title')}</span>
            <p>{$contactr->delay|strtotime|prepareDate}</p>
        </li>
        {/if}

        {if="$contact->description != null && trim($contact->description) != ''"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-format-align-justify"></i></span>
            <span>{$c->__('general.about')}</span>
            <p class="all">{$contact->description}</p>
        </li>
        {/if}

        {if="$contact->mood != null"}
        {$moods = unserialize($contact->mood)}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-mood"></i></span>
            <span>{$c->__('mood.title')}</span>
            <p>{loop="$moods"}
                {$mood[$value]}
                {/loop}
            </p>
        </li>
        {/if}
    </ul>

    {if="$blog != null"}
        <ul class="middle active">
            <li class="subheader">{$c->__('blog.last')}</li>
            <a href="{$c->route('blog', array($contact->jid))}" target="_blank">
                <li class="block large condensed action">
                    <div class="action">
                        <i class="md md-chevron-right"></i>
                    </div>
                    <span class="icon">
                        <i class="md md-wifi-tethering"></i>
                    </span>
                    <span class="info">{$blog[0]->published|strtotime|prepareDate}</span>
                    <span>{$blog[0]->title}</span>
                    <p class="more">{$blog[0]->contentcleaned|strip_tags:'<img><img/>'}</p>
                </li>
            </a>
        </ul>
    {/if}

    <br />

    {if="$contact->tuneartist || $contact->tunetitle"}
    <ul class="flex">
        <li class="subheader block large">{$c->__('general.tune')}</li>

        {$img_array = $c->getLastFM($contact)}
        <li class="
            block
            {if="$contact->tunetitle"}condensed{/if}
            {if="isset($img_array[1]) && $img_array[1] != ''"} action{/if}
            ">
            {if="isset($img_array[1]) && $img_array[1] != ''"}
                <div class="action">
                    <a href="{$img_array[1]}" target="_blank">
                        <i class="md md-radio"></i>
                    </a>
                </div>
            {/if}
            <span class="icon bubble">
                {if="isset($img_array[0]) && $img_array[0] != ''"}
                    <img src="{$img_array[0]}"/>
                {else}
                    <i class="md md-play-circle-fill"></i>
                {/if}
            </span>
            <span>
                {if="$contact->tuneartist"}
                    {$contact->tuneartist} -
                {/if}
                {if="$contact->tunesource"}
                    {$contact->tunesource}
                {/if}
            </span>

            {if="$contact->tunetitle"}
                <p>{$contact->tunetitle}</p>
            {/if}
        </li>
    </ul>
    {/if}

    <div class="clear"></div>
    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
    <br />
    <ul class="flex">
        <li class="subheader block large">{$c->__('position.legend')}</li>

        {if="$contact->adrlocality != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-location-city"></i></span>
            <span>{$c->__('position.locality')}</span>
            <p>
                {$contact->adrlocality}
            </p>
        </li>
        {/if}
        {if="$contact->adrcountry != null"}
        <li class="condensed block">
            <span class="icon gray"><i class="md md-place"></i></span>
            <span>{$c->__('position.country')}</span>
            <p>
                {$contact->adrcountry}
            </p>
        </li>
        {/if}
    </ul>
    {/if}

    <div class="clear"></div>
    {if="$contact->twitter != null || $contact->skype != null || $contact->yahoo != null"}
    <ul class="flex">
        <li class="subheader block large">{$c->__('general.accounts')}</li>

        {if="$contact->twitter != null"}
        <li class="condensed block">
            <span class="icon gray">T</span>
            <span>Twitter</span>
            <p>
                <a
                    target="_blank"
                    href="https://twitter.com/{$contact->twitter}">
                    @{$contact->twitter}
                </a>
            </p>
        </li>
        {/if}
        {if="$contact->skype != null"}
        <li class="condensed block">
            <span class="icon gray">S</span>
            <span>Skype</span>
            <p>
                <a
                    target="_blank"
                    href="callto://{$contact->skype}">
                    {$contact->skype}
                </a>
            </p>
        </li>
        {/if}
        {if="$contact->yahoo != null"}
        <li class="condensed block">
            <span class="icon gray">Y</span>
            <span>Yahoo!</span>
            <p>
                <a
                    target="_blank"
                    href="ymsgr:sendIM?{$contact->yahoo}">
                    {$contact->yahoo}
                </a>
            </p>
        </li>
        {/if}
    </ul>
    {/if}

    {if="isset($gallery)"}
        <br />
        <h3 class="padded">{$c->__('page.gallery')}</h3>
        <br />
        <ul class="grid active">
            {loop="$gallery"}
                {$attachements = $value->getAttachements()}
                <li style="background-image: url('{$attachements['pictures'][0]['href']}');"
                    onclick="movim_reload('{$c->route('news', $value->nodeid)}')">
                    <nav>
                        {$attachements['pictures'][0]['title']}
                    </nav>
                </li>
            {/loop}
        </ul>
    {/if}

    {if="$contactr && $contactr->rostersubscription != 'both'"}
        <div class="card">
            <ul class="middle">
                <li class="condensed">
                    {if="$contactr->rostersubscription == 'to'"}
                        <span class="icon gray">
                            <i class="md md-call-received"></i>
                        </span>
                        <span>{$c->__('subscription.to')}</span>
                        <p>{$c->__('subscription.to_text')}</p>
                        <a class="button flat" onclick="Notifs_ajaxAccept('{$contactr->jid}')">
                            {$c->__('subscription.to_button')}
                        </a>
                    {/if}
                    {if="$contactr->rostersubscription == 'from'"}
                        <span class="icon gray">
                            <i class="md md-call-made"></i>
                        </span>
                        <span>{$c->__('subscription.from')}</span>
                        <p>{$c->__('subscription.from_text')}</p>
                        <a class="button flat" onclick="Notifs_ajaxAsk('{$contactr->jid}')">
                            {$c->__('subscription.from_button')}
                        </a>
                    {/if}
                    {if="$contactr->rostersubscription == 'none'"}
                        <span class="icon gray">
                            <i class="md md-do-not-disturb"></i>
                        </span>

                        <span>{$c->__('subscription.nil')}</span>
                        <p>{$c->__('subscription.nil_text')}</p>
                        <a class="button flat" onclick="Notifs_ajaxAsk('{$contactr->jid}')">
                            {$c->__('subscription.nil_button')}
                        </a>
                    {/if}
                </li>
            </ul>
        </div>
    {/if}

    <a onclick="{$chat}" class="button action color red">
        <i class="md md-chat"></i>
    </a>
{else}
    <ul class="thick">
        <li>
            <span class="icon bubble"><img src="{$contactr->getPhoto('l')}"></span>
            <h2>{$contactr->getTrueName()}</h2>
        </li>
    </ul>
{/if}
<br />
