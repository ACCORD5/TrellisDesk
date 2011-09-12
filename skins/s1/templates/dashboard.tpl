<div class="content_block">
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="1%" style="padding:0 8px 7px 8px">
            <a href="{$td_url}/index.php?page=tickets&amp;act=open"><img src="{$img_url}/ticket_icon.gif" alt="{$lang['open_ticket']}" /></a>
        </td>
        <td width="46%">
            <span class="blbig"><a href="{$td_url}/index.php?page=tickets&amp;act=open">{$lang['open_ticket']}</a></span>
        </td>
        {if $cache['settings']['kb']['enable'] && $user['g_kb_access']}
        <td width="1%" style="padding:0 8px 7px 8px">
            <a href="{$td_url}/index.php?page=kb"><img src="{$img_url}/kb_icon.gif" alt="{$lang['knowledge_base']}" /></a>
        </td>
        <td width="52%">
            <span class="blbig"><a href="{$td_url}/index.php?page=kb">{$lang['knowledge_base']}</a></span>
        </td>
        {else}
        <td width="1%" style="padding:0 8px 7px 8px">
            <a href="{$td_url}/index.php?page=tickets&amp;act=history"><img src="{$img_url}/kb_icon.gif" alt="{$lang['ticket_history']}" /></a>
        </td>
        <td width="52%">
            <span class="blbig"><a href="{$td_url}/index.php?page=tickets&amp;act=history">{$lang['ticket_history']}</a></span>
        </td>
        {/if}
    </tr>
    {if $news}
    <tr>
        <td colspan="4">
            <div class="groupbox">{if $cache['settings']['news']['page']}<a href="{$td_url}/index.php?page=news">{/if}{$lang['news']}{if $cache['settings']['news']['page']}</a>{/if}</div>
            {foreach $news as $n}
            <div class="subbox">{if $cache['settings']['news']['page']}<a href="{$td_url}/index.php?page=news&amp;act=view&amp;id={$n['id']}">{/if}{$n['title']}{if $cache['settings']['news']['page']}</a>{/if}<span class="date"> -- {$n['date_human']}</span></div>
            <div class="row1">
                {if $n['excerpt']}
                {$n['excerpt']}
                {else}
                {if $cache['settings']['news']['page']}<a href="{$td_url}/index.php?page=news&amp;act=view&amp;id={$n['id']}">{$lang['read_on']}</a>{else}<i>{$lang['no_excerpt']}</i>{/if}
                {/if}
            </div>
            {/foreach}
            <br />
        </td>
    </tr>
    {/if}
    <tr>
        <td colspan="4">
            <div class="groupbox">{$lang['tickets_overview']}</div>
            <table width="100%" cellpadding="3" cellspacing="0" class="smtable">
            <tr>
                <th width="5%" align="left">{$lang['id']}</th>
                <th width="27%" align="left">{$lang['subject']}</th>
                <th width="14%" align="left">{$lang['priority']}</th>
                <th width="20%" align="left">{$lang['department']}</th>
                <th width="20%" align="left">{$lang['last_reply']}</th>
                <th width="14%" align="left">{$lang['status']}</th>
            </tr>
            {if $user['id'] or $user['s_tkey']}
            {if $tickets}
            {foreach $tickets as $t}
            {$ticket_rows = $ticket_rows+1}
            {if $ticket_rows & 1}{$ticket_class = 1}{else}{$ticket_class = 2}{/if}
            <tr>
                <td class="option{$ticket_class}-mini"><a href="{$td_url}/index.php?page=tickets&amp;act=view&amp;id={$t['mask']}">{$t['mask']}</a></td>
                <td class="option{$ticket_class}-mini"><a href="{$td_url}/index.php?page=tickets&amp;act=view&amp;id={$t['mask']}">{if $t['escalated']}<img src='{$img_url}/icon_escalate.png' alt='E' style='vertical-align:middle;margin-bottom:2px' />&nbsp;{/if}{$t['subject']}</a></td>
                <td class="option{$ticket_class}-mini"><img src="{$td_url}/images/priorities/{$t['icon_regular']}" alt="{$t['pname']}" class="prioritybox" />&nbsp;&nbsp;{$t['pname']}</td>
                <td class="row{$ticket_class}-mini">{$t['dname']}</td>
                <td class="row{$ticket_class}-mini">{$t['last_reply_human']}</td>
                <td class="option{$ticket_class}-mini">{$t['status_abbr']}</td>
            </tr>
            {/foreach}
            {else}
            <tr>
                <td class="option1" colspan="6" align="center">{$lang['no_tickets']}</td>
            </tr>
            {/if}
            {else}
            <tr>
                <td class="option1" colspan="6" align="center">{$lang['no_tickets_login']}</td>
            </tr>
            {/if}
            </table>
            <br />
        </td>
    </tr>
    </table>
</div>