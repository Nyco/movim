<div id="post_widget">
    {$c->prepareEmpty()}
    {if="$nodeid"}
        <script type="text/javascript">
            MovimWebsocket.attach(function() {
                Post_ajaxGetPost('{$nodeid}');
            });
        </script>
    {/if}
</div>
