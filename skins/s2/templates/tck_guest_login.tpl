<div class='content_block'>
    <h2>{$lang['guest_login']}</h2>
    <p>{$lang['guest_login_info']}</p>
    <form action='{$td_url}/index.php?page=glogin' method='post'>
    {$token_gt_login}
    <p>
        <input type='text' name='email_address' id='email_address' value='{$lang['email_address']}' onfocus="clear_value(this, '{$lang['email_address']}')" onblur="reset_value(this, '{$lang['email_address']}')" size='30' />
    </p>
    <p>
        <input type='text' name='ticket_key' id='ticket_key' value='{$lang['ticket_key']}' onfocus="clear_value(this, '{$lang['ticket_key']}')" onblur="reset_value(this, '{$lang['ticket_key']}')" size='30' />
    </p>
    <p>
        <input type='submit' class='submit' name='submit' id='loginb' value='{$lang['log_in_button']}' />
    </p>
    </form>
</div>