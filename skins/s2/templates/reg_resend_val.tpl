{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.user.value && ! form.email.value )
        {
            alert('{/literal}{$lang['err_no_user_or_email']}{literal}');
            form.user.focus();
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <form action='{$td_url}/index.php?page=register&amp;code=dosendval' method='post' onsubmit='return validate_form(this)'>
    {$token_resend_val}
    <h2>{$lang['resend_val']}</h2>
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <table width='100%' cellpadding='3' cellspacing='1'>
    <tr>
        <td class='row1' width='22%'><label for='user'>{$lang['username']}</label></td>
        <td width='78%'><input type='text' name='user' id='user' value='{$input['user']}' size='35' /></td>
    </tr>
    <tr>
        <td colspan='2'>OR</td>
    </tr>
    <tr>
        <td class='row1'><label for='email'>{$lang['email_address']}</label></td>
        <td><input type='text' name='email' id='email' value='{$input['email']}' size='35' /></td>
    </tr>
    <tr>
        <td colspan='2'><input type='submit' class='submit' name='submit' id='resend' value='{$lang['resend_val_button']}' /></td>
    </tr>
    </table>
    </form>
</div>