<div class="content_block">
    <h1>{$lang['resend_val']}</h1>
    {if $error}
    <div class="critical">{$error}</div>
    {/if}
    <form action="{$td_url}/index.php?page=register&amp;act=dosendval" method="post">
    <div class="groupbox">{$lang['account_info']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="option1" width="22%"><label for="name">{$lang['username']}</label></td>
        <td class="row1" width="78%"><input type="text" name="name" id="name" value="{$input['name']}" size="35" /></td>
    </tr>
    <tr>
        <td class="option2-med" colspan="2">{$lang['or']}</td>
    </tr>
    <tr>
        <td class="option1"><label for="email">{$lang['email_address']}</label></td>
        <td class="row1"><input type="text" name="email" id="email" value="{$input['email']}" size="35" /></td>
    </tr>
    </table>
    <div class="formtail"><input type="submit" name="submit" id="resend" value="{$lang['resend_val_button']}" class="button" /></div>
    </form>
</div>