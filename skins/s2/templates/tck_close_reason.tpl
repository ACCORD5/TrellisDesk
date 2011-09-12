{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.reason.value )
        {
            alert('{/literal}{$lang['err_no_reason']}{literal}');
            form.reason.focus();
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <h2>{$lang['closing_ticket']}: {$t['subject']}</h2>
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <p>{$lang['enter_close_reason']}</p>
    <form action='{$td_url}/index.php?page=tickets&amp;code=close&amp;id={$t['id']}&amp;final=1' method='post' onsubmit='return validate_form(this)'>
    {$token_close_reason}
    <p><textarea name='reason' id='reason' cols='70' rows='2'>{$input['reason']}</textarea></p>
    <p><input type='submit' class='submit' name='submit' id='close' value='{$lang['close_ticket_button']}' /></p>
    </form>
</div>