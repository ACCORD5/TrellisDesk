<div class="content_block">
    <h1>{$lang['my_account']}</h1>
    <form action="{$td_url}/index.php?page=account&amp;act=doemail" method="post">
    {if $error}
    <div class="critical">{$error}</div>
    {/if}
    <div class="groupbox">{$lang['change_email_address']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="option1" width="32%"><label for="new_email">{$lang['new_email_address']}</label></td>
        <td class="row1" width="68%"><input type="text" name="new_email" id="new_email" value="{$input['new_email']}" size="35" /></td>
    </tr>
    <tr>
        <td class="option2"><label for="new_email_b">{$lang['new_email_address_confirm']}</label></td>
        <td class="row2"><input type="text" name="new_email_b" id="new_email_b" value="{$input['new_email_b']}" size="35" /></td>
    </tr>
    </table>
    <div class="formtail"><input type="submit" name="submit" id="change" value="{$lang['change_email_button']}" class="button" /></div>
    </form>
</div>
<script type="text/javascript">
//<![CDATA[
{lv_field name="new_email"}
{lv_rule name="new_email" type="presence"}
{lv_rule name="new_email" type="email"}
{lv_field name="new_email_b"}
{lv_rule name="new_email_b" type="presence"}
{lv_rule name="new_email_b" type="email"}
{lv_rule name="new_email_b" type="match" against="new_email"}
{focus name="new_email"}
//]]>
</script>