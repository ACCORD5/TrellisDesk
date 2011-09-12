<div class="content_block">
    <h1>{$lang['open_ticket']}</h1>
    {if $error}<div class="critical">{$error}</div>{/if}
    <form action="{$td_url}/index.php?page=tickets&amp;act=add&amp;step=2" method="post">
    <div class="groupbox">{$lang['select_depart']}</div>
    <div class="option1">
    <table width="100%" cellpadding="4" cellspacing="0">
    {foreach $departs as $d}
    <tr>
        <td width="1%"><input type="radio" name="did" id="d_{$d['id']}" value="{$d['id']}" class="radio" /></td>
        <td width="99%"><label for="d_{$d['id']}">{$d['name']}</label><br /><span style="font-weight:normal">{$d['description']}</span></td>
    </tr>
    {/foreach}
    </table>
    </div>
    <div class="formtail"><input type="submit" name="submit" id="send" value="{$lang['open_ticket_button']}" class="button" /></div>
    </form>
</div>