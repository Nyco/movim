<section>
    <h3>{$c->__('post.help')}</h3>
    <ul class="thick flex">
        <li class="block">
            <span class="icon">
                <i class="md md-format-size"></i>
            </span>
            <p># Title H1</p>
            <p>## Title H2…</p>
        </li>
        <li class="block">
            <span class="icon">
                <i class="md md-format-bold"></i>
            </span>
            <p>**bolded**</p>
            <p>__bolded__</p>
        </li>
        <li class="block">
            <span class="icon">
                <i class="md md-format-italic"></i>
            </span>
            <p>*emphasis*</p>
            <p>_emphasis_</p>
        </li>
        <li class="block">
            <span class="icon">
                <i class="md md-format-quote"></i>
            </span>
            <p>> Quoted line</p>
            <p>> Quoted line</p>
        </li>
        <li class="block">
            <span class="icon">
                <i class="md md-format-list-bulleted"></i>
            </span>
            <p>* Item 1</p>
            <p>* Item 2</p>
        </li>
        <li class="block">
            <span class="icon">
                <i class="md md-format-list-numbered"></i>
            </span>
            <p>1. Item 1</p>
            <p>2. Item 2</p>
        </li>
        <li class="block">
            <span class="icon">
                <i class="md md-functions"></i>
            </span>
            <p>`Sourcecode`</p>
        </li>
        <li class="block large">
            <span class="icon">
                <i class="md md-insert-link"></i>
            </span>
            <p>[my text](http://my_url/)</p>
        </li>
        <li class="block large">
            <span class="icon">
                <i class="md md-insert-photo"></i>
            </span>
            <p>![Alt text](http://my_image_url/)</p>
        </li>
    </ul>
    <ul class="active">
        <li class="subheader">{$c->__('post.help_more')}</li>
        <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">
            <li class="condensed action">
                <div class="action">
                    <i class="md md-chevron-right"></i>
                </div>
                <span class="icon color bubble blue">
                    <i class="md md-star"></i>
                </span>
                <span>
                    {$c->__('post.help_manual')}
                </span>
                <p>http://daringfireball.net/projects/markdown/syntax</p>
            </li>
        </a>
    </ul>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
</div>
