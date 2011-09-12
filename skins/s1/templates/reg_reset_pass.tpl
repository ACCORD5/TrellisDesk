<div class="content_block">
    <h1>{$lang['reset_password']}</h1>
    {if $error}
    <div class="critical">{$error}</div>
    {/if}
    <form action="{$td_url}/index.php?page=register&amp;act=doresetpass&amp;key={$input['key']}" method="post">
    <div class="groupbox">{$lang['account_info']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="option1" width="28%"><label for="new_pass">{$lang['new_password']}</label></td>
        <td class="row1" width="72%"><input type="password" name="new_pass" id="new_pass" size="30" /></td>
    </tr>
    <tr>
        <td class="option2"><label for="new_pass_b">{$lang['new_password_confirm']}</label></td>
        <td class="row2"><input type="password" name="new_pass_b" id="new_pass_b" size="30" /></td>
    </tr>
    </table>
    <div class="formtail"><input type="submit" name="submit" id="change" value="{$lang['reset_pass_button']}" class="button" /></div>
    </form>
    <script type="text/javascript">
    {lv_field name="new_pass"}
    {lv_rule name="new_pass" type="presence"}
    {lv_field name="new_pass_b"}
    {lv_rule name="new_pass_b" type="presence"}
    {lv_rule name="new_pass_b" type="match" against="new_pass"}
    {focus name="current_pass"}
    </script>
</div>