<div class="comments" id="{$post->nodeid}comments">
    {$comments}
    <a 
        class="getcomments icon chat" 
        onclick="{$getcomments} this.innerHTML = '{$c->t('Loading comments ...')}'"
    >{$c->t('Get the comments')}</a>
</div>
<div class="comments">
    <a class="addcomment icon chat"
        onclick="
        this.parentNode.querySelector('#commentsubmit').style.display = 'table'; 
        this.style.display ='none'">{$c->t('Add a comment')}</a>
    <table id="commentsubmit">
        <tr>
            <td>
                <textarea 
                    id="{$post->nodeid}commentcontent" 
                    onkeyup="movim_textarea_autoheight(this);"></textarea>
            </td>
        </tr>
        <tr class="commentsubmitrow">
            <td style="width: 100%;"></td>
            <td>
                <a
                    onclick="
                            if(document.getElementById('{$post->nodeid}commentcontent').value != '') {
                                {$publishcomment}
                                document.getElementById('{$post->nodeid}commentcontent').value = '';
                            }"
                    class="button color green icon yes"
                >
                    {$c->t("Submit")}
                </a>
            </td>
        </tr>
    </table>
</div>
