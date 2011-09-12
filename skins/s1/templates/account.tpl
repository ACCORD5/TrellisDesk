<div class="content_block">
    <h1>{$lang['my_account']}</h1>
    {if $alert}<div class="alert">{$alert}</div>{/if}
    <div class="groupbox">{$lang['account_overview']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="option1" width="20%">{$lang['username']}</td>
        <td class="row1" width="80%">{$user['name']}</td>
    </tr>
    <tr>
        <td class="option2">{$lang['email']}</td>
        <td class="row2">{$user['email']}</td>
    </tr>
    <tr>
        <td class="option1">{$lang['group']}</td>
        <td class="row1">{$user['g_name']}</td>
    </tr>
    <tr>
        <td class="option2">{$lang['joined']}</td>
        <td class="row2">{$user['joined_human']}</td>
    </tr>
    <tr>
        <td class="option1">{$lang['total_tickets']}</td>
        <td class="row1">{$user['tickets_total']}</td>
    </tr>
    <tr>
        <td class="option2">{$lang['open_tickets']}</td>
        <td class="row2">{$user['tickets_open']}</td>
    </tr>
    {if $cpfields}
    {$fields_rows = 0}
    {foreach $cpfields as $f}
    {$fields_rows = $fields_rows+1}
    {if $fields_rows & 1}{$field_class = 1}{else}{$field_class = 2}{/if}
    <tr>
        <td class="option{$field_class}">{$f['name']}</td>
        <td class="row{$field_class}">
            {if $f['type'] == "checkbox"}
                {foreach $f['extra'] as $key => $name}
                <input type="checkbox" name="cpf_{$f['id']}_{$key}" id="cpf_{$f['id']}_{$key}" value="1" class="ckbox"{if $cpfdata[$f['id']][$key]} checked="checked"{/if} disabled="disabled" /> {$name}&nbsp;&nbsp;&nbsp;
                {/foreach}
            {elseif $f['type'] == "dropdown" || $f['type'] == "radio"}
            {$f['extra'][$cpfdata[$f['id']]]}
            {else}
            {$cpfdata[$f['id']]}
            {/if}
        </td>
    </tr>
    {/foreach}
    {/if}
    </table>
    <div class="formtail"><div class="fb_pad"><a href="{$td_url}/index.php?page=account&amp;act=edit" class="fake_button">{$lang['modify_account']}</a>&nbsp;&nbsp;<a href="{$td_url}/index.php?page=account&amp;act=email" class="fake_button">{$lang['change_email']}</a>&nbsp;&nbsp;<a href="{$td_url}/index.php?page=account&amp;act=pass" class="fake_button">{$lang['change_password']}</a></div></div>
</div>