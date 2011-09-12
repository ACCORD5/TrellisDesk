{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.message.value )
        {
            alert('{/literal}{$lang['err_no_message']}{literal}');
            form.message.focus();
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <h1>{$lang['edit_ticket']}</h1>
    {if $error}
    <div class='critical'>{$error}</div>
    {/if}
    <form action='{$td_url}/index.php?page=tickets&amp;act=doedit&amp;id={$t['mask']}' method='post' onsubmit='return validate_form(this)'>
    <div class='groupbox'>{$t.subject}</div>
    <div class='option1'><textarea name='message' id='message' rows='8' cols='100' style='width: 98%; height: 140px;'>{$t.message}</textarea></div>
    <div class='formtail'><input type='submit' name='submit' id='send' value='{$lang['edit_ticket_button']}' class='button' /></div>
    </form>
</div>