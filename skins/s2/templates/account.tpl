<div class='content_block'>
    <h2>{$lang['account_overview']}</h2>
    <table width='100%' cellpadding='3' cellspacing='1'>
    <tr>
        <td class='row1' width='20%'>{$lang['username']}</td>
        <td width='80%'>{$user['name']}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['email']}</td>
        <td>{$user['email']}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['title']}</td>
        <td>{$user['title']}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['group']}</td>
        <td>{$cache['groups'][ $user['ugroup'] ]['g_name']}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['joined']}</td>
        <td>{$human_joined}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['tickets']}</td>
        <td>{$user['tickets']}</td>
    </tr>
    {foreach $cpfields $cpf}
    <tr>
        <td class='row1'>{$cpf['name']}</td>
        <td>{$cpf['value']}</td>
    </tr>
    {/foreach}
    <tr>
        <td colspan='2'><br /><a href='{$td_url}/index.php?page=myaccount&amp;code=edit'>{$lang['modify_account']}</a> | <a href='{$td_url}/index.php?page=myaccount&amp;code=email'>{$lang['change_email']}</a> | <a href='{$td_url}/index.php?page=myaccount&amp;code=pass'>{$lang['change_password']}</a></td>
    </tr>
    </table>
</div>