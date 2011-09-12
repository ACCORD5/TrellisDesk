<div class="content_block">
    <h1>{$lang['my_account']}</h1>
    <form action="{$td_url}/index.php?page=account&amp;act=dopass" method="post">
    {if $error}
    <div class="critical">{$error}</div>
    {/if}
    <div class="groupbox">{$lang['change_password']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="option1" width="28%"><label for="current_pass">{$lang['current_password']}</label></td>
        <td class="row1" width="72%"><input type="password" name="current_pass" id="current_pass" size="30" /></td>
    </tr>
    <tr>
        <td class="option2"><label for="new_pass">{$lang['new_password']}</label></td>
        <td class="row2"><input type="password" name="new_pass" id="new_pass" size="30" /></td>
    </tr>
    <tr>
        <td class="option1"><label for="new_pass_b">{$lang['new_password_confirm']}</label></td>
        <td class="row1"><input type="password" name="new_pass_b" id="new_pass_b" size="30" /></td>
    </tr>
    </table>
    <div class="formtail"><input type="submit" name="submit" id="change" value="{$lang['change_pass_button']}" class="button" /></div>
    </form>
</div>
<script type="text/javascript">
//<![CDATA[
{lv_field name="current_pass"}
{lv_rule name="current_pass" type="presence"}
{lv_field name="new_pass"}
{lv_rule name="new_pass" type="presence"}
{lv_field name="new_pass_b"}
{lv_rule name="new_pass_b" type="presence"}
{lv_rule name="new_pass_b" type="match" against="new_pass"}
{focus name="current_pass"}
//]]>
</script>