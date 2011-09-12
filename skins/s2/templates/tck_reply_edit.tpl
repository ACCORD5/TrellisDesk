{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.message.value )
        {
            alert('{/literal}{$lang['err_no_reply']}{literal}');
            form.message.focus();
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <h2>{$lang['edit_reply']}</h2>
    <form action='{$td_url}/index.php?page=tickets&amp;code=doeditreply&amp;id={$r['id']}' method='post' onsubmit='return validate_form(this)'>
    {$token_reply_edit}
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <p><textarea name='message' id='message' cols='70' rows='8'>{$r['message']}</textarea></p>
    <p><input type='submit' class='submit' name='submit' id='send' value='{$lang['edit_reply_button']}' /></p>
    </form>
</div>