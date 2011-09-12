<div class='content_block'>
    <h2>{$lang['ticket_history']}</h2>
    <div class='sboutline'>
        <table width='100%' cellpadding='3' cellspacing='1'>
        <tr>
            <th width='10%'>{$lang['id']}</th>
            <th width='16%'>{$lang['priority']}</th>
            <th width='28%'>{$lang['department']}</th>
            <th width='28%'>{$lang['submitted']}</th>
            <th width='17%'>{$lang['status']}</th>
        </tr>
        {if $htickets}
        {foreach $htickets $t}
        <tr>
            <td class='row2' colspan='5'><a href='{$td_url}/index.php?page=tickets&amp;code=view&amp;id={$t['id']}'>{$t['subject']}</a></td>
        </tr>
        <tr>
            <td class='row1'><a href='{$td_url}/index.php?page=tickets&amp;code=view&amp;id={$t['id']}'>{$t['id']}</a></td>
            <td class='row1'>{$t['priority']}</td>
            <td class='row1'>{$t['dname']}</td>
            <td class='row1'>{$t['date']}</td>
            <td class='row1'>{$t['status']}</td>
        </tr>
        {/foreach}
        {else}
        <tr>
            <td class='row2' colspan='5'>{$lang['no_tickets']}</td>
        </tr>
        {/if}
        </table>
    </div>
</div>