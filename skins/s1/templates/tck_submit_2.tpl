<div class="content_block">
    <h1>{$lang['open_ticket']}</h1>
    {if $error}<div class="critical">{$error}</div>{/if}
    <form action="{$td_url}/index.php?page=tickets&amp;act=doadd" method="post" enctype="multipart/form-data">
    <input type="hidden" name="did" value="{$input['did']}" />
    <div class="groupbox">{$dname}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    {if $user.s_tkey}
    <input type="hidden" name="name" value="{$user['s_uname']}" />
    <input type="hidden" name="email" value="{$user['s_email']}" />
    {elseif $user.id == 0}
    <tr>
        <td class="option1" width="15%"><label for="name">{$lang['name']}</label></td>
        <td class="row1" width="85%"><input type="text" name="name" id="name" value="{$input['name']}" size="35" /></td>
    </tr>
    <tr>
        <td class="option2" width="15%"><label for="email">{$lang['email']}</label></td>
        <td class="row2"><input type="text" name="email" id="email" value="{$input['email']}" size="35" /></td>
    </tr>
    {/if}
    <tr>
        <td class="option1" width="15%"><label for="subject">{$lang['subject']}</label></td>
        <td class="row1"><input type="text" name="subject" id="subject" value="{$input['subject']}" size="35" /></td>
    </tr>
    <tr>
        <td class="option2"><label for="priority">{$lang['priority']}</label></td>
        <td class="row2"><select name="priority" id="priority">{html_options options=$priority_options selected=$input['priority']}</select></td>
    </tr>
    {if $cdfields}
    {foreach $cdfields as $f}
    {$fields_rows = $fields_rows+1}
    {if $fields_rows & 1}{$field_class = 1}{else}{$field_class = 2}{/if}
    {if $f['required']}{$validate[] = "cdf_{$f['id']}"}{/if}
    {if $f['type'] == "textfield"}
    <tr>
        <td class="option{$field_class}"><label for="cdf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><input type="text" name="cdf_{$f['id']}" id="cdf_{$f['id']}" value="{$cdfdata[$f['id']]}" size="{$f['extra']['size']}" /></td>
    </tr>
    {elseif $f['type'] == "textarea"}
    <tr>
        <td class="option{$field_class}"><label for="cdf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><textarea name="cdf_{$f['id']}" id="cdf_{$f['id']}" cols="{$f['extra']['cols']}" rows="{$f['extra']['rows']}">{$cdfdata[$f['id']]}</textarea></td>
    </tr>
    {elseif $f['type'] == "dropdown"}
    <tr>
        <td class="option{$field_class}"><label for="cdf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><select name="cdf_{$f['id']}" id="cdf_{$f['id']}">{html_options options=$f['extra'] selected=$cdfdata[$f['id']]}</select></td>
    </tr>
    {elseif $f['type'] == "checkbox"}
    <tr>
        <td class="option{$field_class}">{$f['name']}</td>
        <td class="row{$field_class}">
            {foreach $f['extra'] as $key => $name}
            <input type="checkbox" name="cdf_{$f['id']}_{$key}" id="cdf_{$f['id']}_{$key}" value="1" class="ckbox"{if $cdfdata[$f['id']][$key]} checked="checked"{/if} /> <label for="cdf_{$f['id']}_{$key}">{$name}</label>&nbsp;&nbsp;
            {/foreach}
        </td>
    </tr>
    {elseif $f['type'] == "radio"}
    <tr>
        <td class="option{$field_class}">{$f['name']}</td>
        <td class="row{$field_class}">{html_radios name="cdf_{$f['id']}" options=$f['extra'] selected=$cdfdata[$f['id']] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    {/if}
    {/foreach}
    {/if}
    {$fields_rows = $fields_rows+1}
    {if $fields_rows & 1}{$field_class = 1}{else}{$field_class = 2}{/if}
    <tr>
        <td class="row{$field_class}" colspan="2"><textarea name="message" id="message" rows="8" cols="100" style="width: 98%; height: 140px;">{$input['message']}</textarea></td>
    </tr>
    {if $cache.config.use_captcha && $user.id == 0}
    {$fields_rows = $fields_rows+1}
    {if $fields_rows & 1}{$field_class = 1}{else}{$field_class = 2}{/if}
    <tr>
        <td class="option{$field_class}"><label for="captcha">{$lang['captcha']}</label></td>
        <td class="row{$field_class}"><img src="{$td_url}/index.php?page=captcha&amp;code=create" alt="{$lang['captcha']}" style="vertical-align:middle;margin-bottom:2px" />&nbsp;&nbsp;&nbsp;<input type="text" name="captcha" id="captcha" size="12" /></td>
    </tr>
    {/if}
    {if $user.id == 0 && $cache.config.guest_ticket_emails}
    <tr>
        <td class="option{$class_guest}" colspan="2"><label for="guest_email"><input type="checkbox" name="guest_email" id="guest_email" value="1" class="ckbox" selected="selected" /> {$lang['guest_ticket_notification']}</label></td>
    </tr>
    {/if}
    </table>
    {if $upload_form}
    
    {/if}
    <div class="formtail"><input type="submit" name="submit" id="send" value="{$lang['open_ticket_button']}" class="button" /></div>
    </form>
</div>
<script type="text/javascript">
//<![CDATA[
{lv_field name="subject"}
{lv_rule name="subject" type="presence"}
{foreach $validate as $f}
{lv_field name=$f}
{lv_rule name=$f type="presence"}
{/foreach}
//]]>
</script>