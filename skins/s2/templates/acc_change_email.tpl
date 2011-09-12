{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.new_email.value )
        {
            alert('{/literal}{$lang['err_no_email_valid']}{literal}');

            form.new_email.focus();
            return false;
        }

        if ( form.new_email.value != form.new_email_b.value )
        {
            alert('{/literal}{$lang['err_no_email_match']}{literal}');
            form.new_email.focus();
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <form action='{$td_url}/index.php?page=myaccount&amp;code=doemail' method='post' onsubmit='return validate_form(this)'>
    {$token_account_email}
    <h2>{$lang['change_email_address']}</h2>
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <table width='100%' cellpadding='3' cellspacing='1'>
    <tr>
        <td class='row1' width='32%'><label for='new_email'>{$lang['new_email_address']}</label></td>
        <td width='68%'><input type='text' name='new_email' id='new_email' value='{$input['new_email']}' size='35' /></td>
    </tr>
    <tr>
        <td class='row1'><label for='new_email_b'>{$lang['new_email_address_confirm']}</label></td>
        <td><input type='text' name='new_email_b' id='new_email_b' value='{$input['new_email_b']}' size='35' /></td>
    </tr>
    <tr>
        <td colspan='2' class='padtop'><input type='submit' class='submit' name='submit' id='change' value='{$lang['change_email_button']}' /></td>
    </tr>
    </table>
    </form>
</div>