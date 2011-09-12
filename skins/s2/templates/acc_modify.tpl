<div class='content_block'>
    <form action='{$td_url}/index.php?page=myaccount&amp;code=doedit' method='post'>
    {$token_account_edit}
    <h2>{$lang['modify_account_information']}</h2>
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <table width='100%' cellpadding='3' cellspacing='1'>
    <tr>
        <td colspan='2' class='padnotop'><h5>{$lang['general_info']}</h5></td>
    </tr>
    <tr>
        <td class='row1'><label for='time_zone'>{$lang['time_zone']}</label></td>
        <td><select name='time_zone' id='time_zone'>{$time_zone_drop}</select>&nbsp;&nbsp;{$lang['time_is_now']} {$time_now}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['dst_active']}</td>
        <td>
            <label for='dst_active1'><input type='radio' name='dst_active' id='dst_active1' value='1' class='radio'{$select['dst_active_a']} /> {$lang['yes']}</label>&nbsp;&nbsp;
            <label for='dst_active0'><input type='radio' name='dst_active' id='dst_active0' value='0' class='radio'{$select['dst_active_b']} /> {$lang['no']}</label>
        </td>
    </tr>
    <tr>
        <td class='row1'><label for='user_lang'>{$lang['language']}</label></td>
        <td><select name='user_lang' id='user_lang'{$lang_dis}>{$lang_drop}</select></td>
    </tr>
    <tr>
        <td class='row1'><label for='user_skin'>{$lang['skin']}</label></td>
        <td><select name='user_skin' id='user_skin'{$skin_dis}>{$skin_drop}</select></td>
    </tr>
    <tr>
        <td class='row1'>{$lang['rich_text_editor']}</td>
        <td>
            <label for='rte_enable1'><input type='radio' name='rte_enable' id='rte_enable1' value='1' class='radio'{$select['rte_enable_a']} /> {$lang['enabled']}</label>&nbsp;&nbsp;
            <label for='rte_enable0'><input type='radio' name='rte_enable' id='rte_enable0' value='0' class='radio'{$select['rte_enable_b']} /> {$lang['disabled']}</label>
        </td>
    </tr>
    {if $cpfields}
    {foreach $cpfields $cpf}
    {if $cpf['type'] == 'textfield'}
    <tr>
        <td class='row1'><label for='cpf_{$cpf['fkey']}'>{$cpf['name']}</label></td>
        <td><input type='text' name='cpf_{$cpf['fkey']}' id='cpf_{$cpf['fkey']}' value='{$cpf['value']}' size='45' /> {$cpf['optional']}</td>
    </tr>
    {elseif $cpf['type'] == 'textarea'}
    <tr>
        <td class='row1'><label for='cpf_{$cpf['fkey']}'>{$cpf['name']}</label></td>
        <td><textarea name='cpf_{$cpf['fkey']}' id='cpf_{$cpf['fkey']}' cols='50' rows='3'>{$cpf['value']}</textarea> {$cpf['optional']}</td>
    </tr>
    {elseif $cpf['type'] == 'dropdown'}
    <tr>
        <td class='row1'><label for='cpf_{$cpf['fkey']}'>{$cpf['name']}</label></td>
        <td><select name='cpf_{$cpf['fkey']}' id='cpf_{$cpf['fkey']}'>{$cpf['options']}</select> {$cpf['optional']}</td>
    </tr>
    {elseif $cpf['type'] == 'checkbox'}
    <tr>
        <td class='row1'><label for='cpf_{$cpf['fkey']}'>{$cpf['name']}</label></td>
        <td><input type='checkbox' name='cpf_{$cpf['fkey']}' id='cpf_{$cpf['fkey']}' value='1' class='ckbox'{if $cpf['value']} checked='checked'{/if} />{if $cpf['extra']} <label for='cpf_{$cpf['fkey']}'>{$cpf['extra']}</label>{/if}</td>
    </tr>
    {elseif $cpf['type'] == 'radio'}
    <tr>
        <td class='row1'>{$cpf['name']}</td>
        <td>{$cpf['options']} {$cpf['optional']}</td>
    </tr>
    {/if}
    {/foreach}
    {/if}
    <tr>
        <td colspan='2'><h4>{$lang['email_preferences']}</h4></td>
    </tr>
    <tr>
        <td class='row1' width='22%'>{$lang['email_notifications']}</td>
        <td width='78%'>
            <label for='email_notify1'><input type='radio' name='email_notify' id='email_notify1' value='1' class='radio'{$select['email_notify_a']} /> {$lang['enabled']}</label>&nbsp;&nbsp;
            <label for='email_notify0'><input type='radio' name='email_notify' id='email_notify0' value='0' class='radio'{$select['email_notify_b']} /> {$lang['disabled']}</label>
        </td>
    </tr>
    <tr>
        <td class='row1'>{$lang['email_type']}</td>
        <td>
            <label for='email_html0'><input type='radio' name='email_html' id='email_html0' value='0' class='radio'{$select['email_html_b']} /> {$lang['plain_text']}</label>&nbsp;&nbsp;
            <label for='email_html1'><input type='radio' name='email_html' id='email_html1' value='1' class='radio'{$select['email_html_a']} /> {$lang['html']}</label>
        </td>
    </tr>
    <tr>
        <td class='row1' valign='top'>{$lang['notifications_for']}</td>
        <td>
            <label for='email_new_ticket'><input type='checkbox' name='email_new_ticket' id='email_new_ticket' value='1' class='ckbox'{$select['email_new_ticket']} /> {$lang['new_ticket']}</label>&nbsp;&nbsp;
            <label for='email_ticket_reply'><input type='checkbox' name='email_ticket_reply' id='email_ticket_reply' value='1' class='ckbox'{$select['email_ticket_reply']} /> {$lang['new_reply']}</label>&nbsp;&nbsp;
            <label for='email_announce'><input type='checkbox' name='email_announce' id='email_announce' value='1' class='ckbox'{$select['email_announce']} /> {$lang['announcements']}</label>
            {if $user['g_acp_access']}
            <div style='margin-top:3px'><label for='email_staff_new_ticket'><input type='checkbox' name='email_staff_new_ticket' id='email_staff_new_ticket' value='1' class='ckbox'{$select['email_staff_new_ticket']} /> {$lang['email_staff_new_ticket']}</label></div>
            <div style='margin-top:3px'><label for='email_staff_ticket_reply'><input type='checkbox' name='email_staff_ticket_reply' id='email_staff_ticket_reply' value='1' class='ckbox'{$select['email_staff_ticket_reply']} /> {$lang['email_staff_ticket_reply']}</label></div>
            {/if}
        </td>
    </tr>
    <tr>
        <td colspan='2' class='padtop'><input type='submit' class='submit' name='submit' id='edit' value='{$lang['update_account']}' /></td>
    </tr>
    </table>
    </form>
</div>