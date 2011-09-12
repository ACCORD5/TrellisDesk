{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.subject.value )
        {
            alert('{/literal}{$lang['err_no_subject']}{literal}');
            form.subject.focus();
            return false;
        }

        if ( ! form.message.value )
        {
            alert('{/literal}{$lang['err_no_message']}{literal}');
            form.message.focus();
            return false;
        }
    }

</script>
{literal}
<div class='content_block'>
    <form action='{$td_url}/index.php?page=tickets&amp;code=submit' method='post' onsubmit='return validate_form(this)' enctype='multipart/form-data'>
    {$token_sub_b}
    <input type='hidden' name='department' value='{$input['department']}' />
    <h2>{$lang['open_ticket']}: {$cache['depart'][ $input['department'] ]['name']}</h2>
    {if $error}
    <div id='smallerror'>
        <p>{$error} {$error_extra}</p>
    </div>
    {/if}
    <table width='100%' cellpadding='3' cellspacing='1'>
    {if $user['s_tkey']}
    <input type='hidden' name='name' value='{$user['s_uname']}' />
    <input type='hidden' name='email' value='{$user['s_email']}' />
    {elseif $user['id'] == 0}
    <tr>
        <td class='row1' width='15%'><label for='name'>{$lang['name']}</label></td>
        <td width='85%'><input type='text' name='name' id='name' value='{$input['name']}' size='35' /></td>
    </tr>
    <tr>
        <td class='row1' width='15%'><label for='email'>{$lang['email']}</label></td>
        <td width='85%'><input type='text' name='email' id='email' value='{$input['email']}' size='35' /></td>
    </tr>
    {/if}
    <tr>
        <td class='row1' width='15%'><label for='subject'>{$lang['subject']}</label></td>
        <td width='85%'><input type='text' name='subject' id='subject' value='{$input['subject']}' size='35' /></td>
    </tr>
    <tr>
        <td class='row1'><label for='priority'>{$lang['priority']}</label></td>
        <td><select name='priority' id='priority'>{$priority_drop}</select></td>
    </tr>
    {if $cdfields}
    {foreach $cdfields $cdf}
    {if $cdf['type'] == 'textfield'}
    <tr>
        <td class='row1'><label for='cdf_{$cdf['fkey']}'>{$cdf['name']}</label></td>
        <td><input type='text' name='cdf_{$cdf['fkey']}' id='cdf_{$cdf['fkey']}' value='{$cdf['value']}' size='45' /> {$cdf['optional']}</td>
    </tr>
    {elseif $cdf['type'] == 'textarea'}
    <tr>
        <td class='row1'><label for='cdf_{$cdf['fkey']}'>{$cdf['name']}</label></td>
        <td><textarea name='cdf_{$cdf['fkey']}' id='cdf_{$cdf['fkey']}' cols='50' rows='3'>{$cdf['value']}</textarea> {$cdf['optional']}</td>
    </tr>
    {elseif $cdf['type'] == 'dropdown'}
    <tr>
        <td class='row1'><label for='cdf_{$cdf['fkey']}'>{$cdf['name']}</label></td>
        <td><select name='cdf_{$cdf['fkey']}' id='cdf_{$cdf['fkey']}'>{$cdf['options']}</select> {$cdf['optional']}</td>
    </tr>
    {elseif $cdf['type'] == 'checkbox'}
    <tr>
        <td class='row1'><label for='cdf_{$cdf['fkey']}'>{$cdf['name']}</label></td>
        <td><input type='checkbox' name='cdf_{$cdf['fkey']}' id='cdf_{$cdf['fkey']}' value='1' class='ckbox'{if $cdf['value']} checked='checked'{/if} />{if $cdf['extra']} <label for='cdf_{$cdf['fkey']}'>{$cdf['extra']}</label>{/if}</td>
    </tr>
    {elseif $cdf['type'] == 'radio'}
    <tr>
        <td class='row1'>{$cdf['name']}</td>
        <td>{$cdf['options']} {$cdf['optional']}</td>
    </tr>
    {/if}
    {/foreach}
    {/if}
    <tr>
        <td colspan='2'><textarea name='message' id='message' cols='70' rows='8'>{$input['message']}</textarea></td>
    </tr>
    {if $cache['config']['ticket_attachments'] && $user['g_ticket_attach'] && $cache['depart'][ $input['department'] ]['allow_attach']}
    <tr>
        <td colspan='2'><input type='file' name='attachment' id='attachment' size='32' />{$upload_info}</td>
    </tr>
    {/if}
    {if $cache['config']['use_captcha'] && $user['id'] == 0}
    <tr>
        <td class='row1'><label for='captcha'>{$lang['captcha']}</label></td>
        <td><img src='{$td_url}/index.php?page=captcha&amp;code=create&amp;width=100&amp;height=18&amp;fontsize=8' alt='{$lang['captcha']}' style='vertical-align:bottom' /> <input type='text' name='captcha' id='captcha' size='12' /></td>
    </tr>
    {/if}
    {if $user['id'] == 0 && $cache['config']['guest_ticket_emails']}
    <tr>
        <td colspan='2'><label for='guest_email'><input type='checkbox' name='guest_email' id='guest_email' value='1' class='ckbox' selected='selected' /> {$lang['guest_ticket_notification']}</label></td>
    </tr>
    {/if}
    <tr>
        <td colspan='2'><input type='submit' class='submit' name='submit' id='send' value='{$lang['open_ticket_button']}' /></td>
    </tr>
    </table>
    </form>
</div>