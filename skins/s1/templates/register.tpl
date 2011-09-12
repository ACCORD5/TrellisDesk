<div class="content_block">
    <h1>{$lang['register']}</h1>
    {if $error}
    <div class="critical">{$error}</div>
    {/if}
    <form action="{$td_url}/index.php?page=register&amp;act=doadd" method="post">
    {$token_register}
    <div class="groupbox">{$lang['account_info']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="option1" width="22%"><label for="name">{$lang['username']}</label></td>
        <td class="row1" width="78%"><input type="text" name="name" id="name" value="{$input['name']}" size="35" /></td>
    </tr>
    <tr>
        <td class="option2"><label for="email">{$lang['email_address']}</label></td>
        <td class="row2"><input type="text" name="email" id="email" value="{$input['email']}" size="35" /></td>
    </tr>
    <tr>
        <td class="option1"><label for="pass">{$lang['password']}</label></td>
        <td class="row1"><input type="password" name="pass" id="pass" size="35" /></td>
    </tr>
    <tr>
        <td class="option2"><label for="passb">{$lang['password_confirm']}</label></td>
        <td class="row2"><input type="password" name="passb" id="passb" size="35" /></td>
    </tr>
    {if $cpfields}
    {foreach $cpfields as $f}
    {$fields_rows = $fields_rows+1}
    {if $fields_rows & 1}{$field_class = 1}{else}{$field_class = 2}{/if}
    {if $f['required']}{$validate[] = "cpf_{$f['id']}"}{/if}
    {if $f['type'] == "textfield"}
    <tr>
        <td class="option{$field_class}"><label for="cpf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><input type="text" name="cpf_{$f['id']}" id="cpf_{$f['id']}" value="{$input["cpf_{$f['id']}"]}" size="{$f['extra']['size']}" /></td>
    </tr>
    {elseif $f['type'] == "textarea"}
    <tr>
        <td class="option{$field_class}"><label for="cpf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><textarea name="cpf_{$f['id']}" id="cpf_{$f['id']}" cols="{$f['extra']['cols']}" rows="{$f['extra']['rows']}">{$input["cpf_{$f['id']}"]}</textarea></td>
    </tr>
    {elseif $f['type'] == "dropdown"}
    <tr>
        <td class="option{$field_class}"><label for="cpf_{$f['id']}">{$f['name']}</label></td>
        <td class="row{$field_class}"><select name="cpf_{$f['id']}" id="cpf_{$f['id']}">{html_options options=$f['extra'] selected=$input["cpf_{$f['id']}"]}</select></td>
    </tr>
    {elseif $f['type'] == "checkbox"}
    <tr>
        <td class="option{$field_class}">{$f['name']}</td>
        <td class="row{$field_class}">
            {foreach $f['extra'] as $key => $name}
            <input type="checkbox" name="cpf_{$f['id']}_{$key}" id="cpf_{$f['id']}_{$key}" value="1" class="ckbox"{if $input["cpf_{$f['id']}_{$key}"]} checked="checked"{/if} /> <label for="cpf_{$f['id']}_{$key}">{$name}</label>&nbsp;&nbsp;
            {/foreach}
        </td>
    </tr>
    {elseif $f['type'] == "radio"}
    <tr>
        <td class="option{$field_class}">{$f['name']}</td>
        <td class="row{$field_class}">{html_radios name="cpf_{$f['id']}" options=$f['extra'] selected=$input["cpf_{$f['id']}"] label_ids=true separator="&nbsp;&nbsp;"}</td>
    </tr>
    {/if}
    {/foreach}
    {/if}
    {if $antispam}
    {$fields_rows = $fields_rows+1}
    {if $fields_rows & 1}{$field_class = 1}{else}{$field_class = 2}{/if}
    {$validate[] = "antispam"}
    <tr>
        <td class="option{$field_class}"><label for="antispam">{$lang['antispam_field']}</label></td>
        <td class="row{$field_class}">{$antispam}</td>
    </tr>
    {/if}
    </table>
    <div class="formtail"><input type="submit" name="submit" id="create" value="{$lang['create_account_button']}" class="button" /></div>
    </form>
    <script type="text/javascript">
    {lv_field name="name"}
    {lv_rule name="name" type="presence"}
    {lv_field name="email"}
    {lv_rule name="email" type="presence"}
    {lv_rule name="email" type="email"}
    {lv_field name="pass"}
    {lv_rule name="pass" type="presence"}
    {lv_field name="passb"}
    {lv_rule name="passb" type="presence"}
    {lv_rule name="passb" type="match" against="pass"}
    {if $validate}
    {foreach $validate as $f}
    {lv_field name=$f}
    {lv_rule name=$f type="presence"}
    {/foreach}
    {/if}
    {focus name="name"}
    </script>
</div>