<div class='content_block'>
    <form action='{$td_url}/index.php?page=tickets&amp;code=open&amp;step=2' method='post' onsubmit='return validate_form(this)'>
    {$token_sub_a}
    <h2>{$lang['open_ticket']}</h2>
    <p>{$lang['select_depart']}</p>
    <table width='100%' cellpadding='4' cellspacing='0'>
    {$depart_opts}
    </table>
    <p><input type='submit' class='submit' name='submit' id='send' value='{$lang['open_ticket_button']}' /></p>
    </form>
</div>