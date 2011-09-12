<div class='content_block'>
    <h1>{$lang['guest_login']}</h1>
    {if $error}
    <div class='critical'>{$error}</div>
    {/if}
    <form action='{$td_url}/index.php?page=glogin' method='post'>
    {$token_gt_login}
    <div class='groupbox'>{$lang['account_info']}</div>
    <div class='option1'>{$lang['guest_login_info']}</div>
    <div class='option2'><input type='text' name='email_address' id='email_address' value='{$lang['email_address']}' onfocus="clear_value(this, '{$lang['email_address']}')" onblur="reset_value(this, '{$lang['email_address']}')" size='30' /></div>
    <div class='option1'><input type='text' name='ticket_key' id='ticket_key' value='{$lang['ticket_key']}' onfocus="clear_value(this, '{$lang['ticket_key']}')" onblur="reset_value(this, '{$lang['ticket_key']}')" size='30' /></div>
    <div class='formtail'><input type='submit' name='submit' id='loginb' value='{$lang['log_in_button']}' class='button' /></div>
    </form>
</div>