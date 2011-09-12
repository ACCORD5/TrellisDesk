<div class='content_block'>
    <table width='100%' cellpadding='0' cellspacing='0'>
    <tr>
        <td width='1%' style='padding:0 8px 5px 8px'>
            <a href='{$td_url}/index.php?page=tickets&amp;code=open'><img src='{$img_url}/ticket_icon.gif' alt='{$lang['open_ticket']}' /></a>
        </td>
        <td width='46%'>
            <span class='blbig'><a href='{$td_url}/index.php?page=tickets&amp;code=open'>{$lang['open_ticket']}</a></span>
        </td>
        <td width='1%' style='padding:0 8px 5px 8px'>
            <a href='{$td_url}/index.php?page=kb'><img src='{$img_url}/kb_icon.gif' alt='{$lang['knowledge_base']}' /></a>
        </td>
        <td width='52%'>
            <span class='blbig'><a href='{$td_url}/index.php?page=kb'>{$lang['knowledge_base']}</a></span>
        </td>
    </tr>
    {if $announcements}
    <tr>
        <td colspan='4'>
            <h2>{if $cache['config']['enable_news_page']}<a href='{$td_url}/index.php?page=news'>{/if}{$lang['announcements']}{if $cache['config']['enable_news_page']}</a>{/if}</h2>
            {foreach $announcements $an}
            <h3>{if $cache['config']['enable_news_page']}<a href='{$td_url}/index.php?page=news&amp;code=view&amp;id={$an['id']}'>{/if}{$an['title']}{if $cache['config']['enable_news_page']}</a>{/if}<span class='date'> -- {$an['date']}</span></h3>
            <p>
                {$an['excerpt']}
            </p>
            {/foreach}
        </td>
    </tr>
    {/if}
    <tr>
        <td colspan='4'>
            <h2>{$lang['ticket_overview']}</h2>
            <div class='sboutline'>
            <table width='100%' cellpadding='3' cellspacing='1' class='smtable'>
            <tr>
                <th width='6%'>{$lang['id']}</th>
                <th width='28%'>{$lang['subject']}</th>
                <th width='11%'>{$lang['priority']}</th>
                <th width='22%'>{$lang['department']}</th>
                <th width='20%'>{$lang['submitted']}</th>
                <th width='13%'>{$lang['status']}</th>
            </tr>
            {if $user['id'] OR $user['s_tkey']}
            {if $tickets_ov}
            {foreach $tickets_ov $tov}
            <tr>
                <td class='row1'><a href='{$td_url}/index.php?page=tickets&amp;code=view&amp;id={$tov['id']}'>{$tov['id']}</a></td>
                <td class='row1'><a href='{$td_url}/index.php?page=tickets&amp;code=view&amp;id={$tov['id']}'>{$tov['subject']}</a></td>
                <td class='row1'>{$tov['priority']}</td>
                <td class='row1'>{$tov['dname']}</td>
                <td class='row1'>{$tov['date']}</td>
                <td class='row1'>{$tov['status']}</td>
            </tr>
            {/foreach}
            {else}
            <tr>
                <td class='row1' colspan='6' align='center'>{$lang['no_tickets']}</td>
            </tr>
            {/if}
            {else}
            <tr>
                <td class='row1' colspan='6' align='center'>{$lang['no_login_tickets']}</td>
            </tr>
            {/if}
            </table>
            </div><br />
        </td>
    </tr>
    <tr>
        <td width='47%' colspan='2' valign='top'>
            <h2>{$lang['recently_added_articles']}</h2>
            <p class='lspace'>
                {foreach $recent_articles $ra}
                <a href='{$td_url}/index.php?page=article&amp;code=view&amp;id={$ra['id']}' title='{$ra['description']}'>{$ra['name']}</a><br />
                {/foreach}
            </p>
        </td>
        <td width='53%' colspan='2' valign='top'>
            <h2>{$lang['most_popular_articles']}</h2>
            <p class='lspace'>
                {foreach $popular_articles $pa}
                <a href='{$td_url}/index.php?page=article&amp;code=view&amp;id={$pa['id']}' title='{$pa['description']}'>{$pa['name']}</a><br />
                {/foreach}
            </p>
        </td>
    </tr>
    </table>
</div>