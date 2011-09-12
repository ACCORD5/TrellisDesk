{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.user.value )
        {
            alert('{/literal}{$lang['err_no_user_short']}{literal}');
            form.user.focus();
            return false;
        }

        if ( ! form.email.value )
        {
            alert('{/literal}{$lang['err_no_email_valid']}{literal}');

            form.email.focus();
            return false;
        }

        if ( form.pass.value != form.passb.value )
        {
            alert('{/literal}{$lang['err_no_pass_match']}{literal}');
            form.pass.focus();
            return false;
        }

        if ( ! form.pass.value || ! form.passb.value )
        {
            alert('{/literal}{$lang['err_no_pass_short']}{literal}');
            form.pass.focus();
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <h1>{$lang['upgrade_account']}</h1>
    {if $error}
    <div class='critical'>{$error}</div>
    {/if}
    <form action='{$td_url}/index.php?page=register&amp;code=doupgrade' method='post' onsubmit='return validate_form(this)'>
    {$token_upgrade}
    <div class='groupbox'>{$lang['account_info']}</div>
    <div class='option1'>{$lang['upgrade_msg']}</div>
    <table width='100%' cellpadding='0' cellspacing='0'>
    <tr>
        <td class='option2' width='22%'><label for='user'>{$lang['username']}</label></td>
        <td class='row2' width='78%'><input type='text' name='user' id='user' value='{$input.user}' size='35' /></td>
    </tr>
    <tr>
        <td class='option1'><label for='pass'>{$lang['password']}</label></td>
        <td class='row1'><input type='password' name='pass' id='pass' size='35' /></td>
    </tr>
    <tr>
        <td class='option2'><label for='passb'>{$lang['password_confirm']}</label></td>
        <td class='row2'><input type='password' name='passb' id='passb' size='35' /></td>
    </tr>
    {if $cpfields}
    {foreach $cpfields $cpf}
    {if $cpf.type == 'textfield'}
    <tr>
        <td class='option{$cpf.class}'><label for='cpf_{$cpf.fkey}'>{$cpf.name}</label></td>
        <td class='row{$cpf.class}'><input type='text' name='cpf_{$cpf.fkey}' id='cpf_{$cpf.fkey}' value='{$cpf.value}' size='45' /> {$cpf.optional}</td>
    </tr>
    {elseif $cpf.type == 'textarea'}
    <tr>
        <td class='option{$cpf.class}'><label for='cpf_{$cpf.fkey}'>{$cpf.name}</label></td>
        <td class='row{$cpf.class}'><textarea name='cpf_{$cpf.fkey}' id='cpf_{$cpf.fkey}' cols='50' rows='3'>{$cpf.value}</textarea> {$cpf.optional}</td>
    </tr>
    {elseif $cpf.type == 'dropdown'}
    <tr>
        <td class='option{$cpf.class}'><label for='cpf_{$cpf.fkey}'>{$cpf.name}</label></td>
        <td class='row{$cpf.class}'><select name='cpf_{$cpf.fkey}' id='cpf_{$cpf.fkey}'>{$cpf.options}</select> {$cpf.optional}</td>
    </tr>
    {elseif $cpf.type == 'checkbox'}
    <tr>
        <td class='option{$cpf.class}'><label for='cpf_{$cpf.fkey}'>{$cpf.name}</label></td>
        <td class='row{$cpf.class}'><input type='checkbox' name='cpf_{$cpf.fkey}' id='cpf_{$cpf.fkey}' value='1' class='ckbox'{if $cpf.value} checked='checked'{/if} />{if $cpf.extra} <label for='cpf_{$cpf.fkey}'>{$cpf.extra}</label>{/if}</td>
    </tr>
    {elseif $cpf.type == 'radio'}
    <tr>
        <td class='option{$cpf.class}'>{$cpf.name}</td>
        <td class='row{$cpf.class}'>{$cpf.options} {$cpf.optional}</td>
    </tr>
    {/if}
    {/foreach}
    {/if}
    {if $cache.config.use_captcha}
    <tr>
        <td class='option{$class_captcha}'><label for='captcha'>{$lang['captcha']}</label></td>
        <td class='row{$class_captcha}'><img src='{$td_url}/index.php?page=captcha&amp;code=create&amp;width=100&amp;height=18&amp;fontsize=8' alt='{$lang['captcha']}' style='vertical-align:bottom' /> <input type='text' name='captcha' id='captcha' size='12' /></td>
    </tr>
    {/if}
    </table>
    <div class='formtail'><input type='submit' name='submit' id='upgrade' value='{$lang['upgrade_account_button']}' class='button' /></div>
    </form>
</div>