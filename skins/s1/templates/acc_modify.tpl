<div class="content_block">
    <h1>{$lang['my_account']}</h1>
    <form action="{$td_url}/index.php?page=account&amp;act=doedit" method="post">
    {if $error}<div class="critical">{$error}</div>{/if}
    <div class="groupbox">{$lang['modify_account_information']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="2" class="subbox">{$lang['general_info']}</td>
    </tr>
    <tr>
        <td class="option1" width="30%"><label for="time_zone">{$lang['time_zone']}</label></td>
        <td class="row1" width="70%"><select name="time_zone" id="time_zone">{html_options options=$time_zone_options selected=$user['time_zone']}</select>&nbsp;&nbsp;{$lang['time_is_now']} {$time_now}</td>
    </tr>
    <tr>
        <td class="option2">{$lang['daylights_savings']}</td>
        <td class="row2">{html_radios name="time_dst" options=$dts_options selected=$user['time_dst'] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    <tr>
        <td class="option1"><label for="user_lang">{$lang['language']}</label></td>
        <td class="row1"><select name="user_lang" id="user_lang"{if !$user['g_change_lang']} disabled="disabled"{/if}>{html_options options=$lang_options selected=$user['lang']}</select></td>
    </tr>
    <tr>
        <td class="option2"><label for="user_skin">{$lang['skin']}</label></td>
        <td class="row2"><select name="user_skin" id="user_skin"{if !$user['g_change_skin']} disabled="disabled"{/if}>{html_options options=$skin_options selected=$user['skin']}</select></td>
    </tr>
    <tr>
        <td class="option1">{$lang['rich_text_editor']}</td>
        <td class="row1">{html_radios name="rte_enable" options=$enable_disable_radio selected=$user['rte_enable'] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    {if $cpfields}
    {foreach $cpfields as $f}
    {$fields_rows = $fields_rows+1}
    {if $fields_rows & 1}{$field_class = 2}{else}{$field_class = 1}{/if}
    {if $f['required']}{$validate[] = "cpf_{$f['id']}"}{/if}
    {if $f['type'] == "textfield"}
    <tr>
        <td class="option{$field_class}"><label for="cpf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><input type="text" name="cpf_{$f['id']}" id="cpf_{$f['id']}" value="{$cpfdata[$f['id']]}" size="{$f['extra']['size']}" /></td>
    </tr>
    {elseif $f['type'] == "textarea"}
    <tr>
        <td class="option{$field_class}"><label for="cpf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><textarea name="cpf_{$f['id']}" id="cpf_{$f['id']}" cols="{$f['extra']['cols']}" rows="{$f['extra']['rows']}">{$cpfdata[$f['id']]}</textarea></td>
    </tr>
    {elseif $f['type'] == "dropdown"}
    <tr>
        <td class="option{$field_class}"><label for="cpf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><select name="cpf_{$f['id']}" id="cpf_{$f['id']}">{html_options options=$f['extra'] selected=$cpfdata[$f['id']]}</select></td>
    </tr>
    {elseif $f['type'] == "checkbox"}
    <tr>
        <td class="option{$field_class}">{$f['name']}</td>
        <td class="row{$field_class}">
            {foreach $f['extra'] as $key => $name}
            <input type="checkbox" name="cpf_{$f['id']}_{$key}" id="cpf_{$f['id']}_{$key}" value="1" class="ckbox"{if $cpfdata[$f['id']][$key]} checked="checked"{/if} /> <label for="cpf_{$f['id']}_{$key}">{$name}</label>&nbsp;&nbsp;
            {/foreach}
        </td>
    </tr>
    {elseif $f['type'] == "radio"}
    <tr>
        <td class="option{$field_class}">{$f['name']}</td>
        <td class="row{$field_class}">{html_radios name="cpf_{$f['id']}" options=$f['extra'] selected=$cpfdata[$f['id']] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    {/if}
    {/foreach}
    {/if}
    <tr>
        <td class="subbox" colspan="2">{$lang['email_preferences']}</td>
    </tr>
    {if $cache['settings']['eunotify']['enable']}
    <tr>
        <td class="option1">{$lang['email_notifications']}</td>
        <td class="row1">{html_radios name="email_enable" options=$enable_disable_radio selected=$user['email_enable'] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    <tr>
        <td class="option2">{$lang['email_type']}</td>
        <td class="row2">{html_radios name="email_type" options=$email_type_options selected=$user['email_type'] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    <tr>
        <td class="option1" valign="top">{$lang['email_ticket']}</td>
        <td class="row1">{html_radios name="email_ticket" options=$enable_disable_radio selected=$user['email_ticket'] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    <tr>
        <td class="option1" valign="top">{$lang['email_action']}</td>
        <td class="row1">{html_radios name="email_action" options=$enable_disable_radio selected=$user['email_action'] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    <tr>
        <td class="option2" valign="top">{$lang['email_news']}</td>
        <td class="row2">{html_radios name="email_news" options=$enable_disable_radio selected=$user['email_news'] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    <tr>
        <td class="row1" colspan="2">{$lang['email_admin_pref_warning']}</td>
    </tr>
    {/if}
    </table>
    <div class="formtail"><input type="submit" name="submit" id="edit" value="{$lang['update_account']}" class="button" /></div>
    </form>
    {if $validate}
    <script type="text/javascript">
    //<![CDATA[
    {foreach $validate as $f}
    {lv_field name=$f}
    {lv_rule name=$f type="presence"}
    {/foreach}
    //]]>
    </script>
    {/if}
</div>