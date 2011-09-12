<div class="content_block">
    <h1>{$lang['tickets']}</h1>
    <div class="groupbox">{$lang['ticket_list']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <th width="5%" align="left">{$lang['id']}</th>
        <th width="27%" align="left">{$lang['subject']}</th>
        <th width="14%" align="left">{$lang['priority']}</th>
        <th width="20%" align="left">{$lang['department']}</th>
        <th width="20%" align="left">{$lang['last_reply']}</th>
        <th width="14%" align="left">{$lang['status']}</th>
    </tr>
    {if $tickets}
    {foreach $tickets as $t}
    {$ticket_rows = $ticket_rows+1}
    {if $ticket_rows & 1}{$ticket_class = 1}{else}{$ticket_class = 2}{/if}
    <tr>
        <td class="option{$ticket_class}-mini"><a href="{$td_url}/index.php?page=tickets&act=view&id={$t['mask']}">{$t['mask']}</a></td>
        <td class="option{$ticket_class}-mini"><a href="{$td_url}/index.php?page=tickets&act=view&id={$t['mask']}">{if $t['escalated']}<img src='{$img_url}/icon_escalate.png' alt='E' style='vertical-align:middle;margin-bottom:2px' />&nbsp;{/if}{$t['subject']}</a></td>
        <td class="option{$ticket_class}-mini"><img src="{$td_url}/images/priorities/{$t['icon_regular']}" alt="{$t['pname']}" class="prioritybox" />&nbsp;&nbsp;{$t['pname']}</td>
        <td class="row{$ticket_class}-mini">{$t['dname']}</td>
        <td class="row{$ticket_class}-mini">{$t['last_reply_human']}</td>
        <td class="option{$ticket_class}-mini">{$t['status_abbr']}</td>
    </tr>
    {/foreach}
    <tr>
        <td class="pagelinks" colspan="6">{$page_links}</td>
    </tr>
    {else}
    <tr>
        <td class="option1" colspan="6" align="center">{$lang['no_tickets']}</td>
    </tr>
    {/if}
    </table>
</div>