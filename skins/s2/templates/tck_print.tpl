<h2>{$lang['viewing_ticket']}</h2>
<div class='sboutline'>
    <table width='100%' cellpadding='3' cellspacing='1'>
    <tr>
        <td class='row1' width='20%'>{$lang['ticket_id']}</td>
        <td class='row3' width='30%'>{$t['id']}</td>
        <td class='row1' width='20%'>{$lang['replies']}</td>
        <td class='row3' width='30%'>{$t['replies']}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['priority']}</td>
        <td class='row3'>{$t['priority']}</td>
        <td class='row1'>{$lang['last_reply']}</td>
        <td class='row3'>{$t['last_reply']}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['department']}</td>
        <td class='row3'>{$cache['depart'][ $t['did'] ]['name']}</td>
        <td class='row1'>{$lang['last_replier']}</td>
        <td class='row3'>{$t['last_uname']}</td>
    </tr>
    <tr>
        <td class='row1'>{$lang['submitted']}</td>
        <td class='row3'>{$t['date']}</td>
        <td class='row1'>{$lang['status']}</td>
        <td class='row3'>{$t['status_human']}</td>
    </tr>
    {if $cdfields_left}
    {foreach $cdfields_left $cdfl}
    <tr>
        <td class='row1'>{$cdfl['name']}</td>
        <td class='row3'{if $cdfl['colspan']} colspan='{$cdfl['colspan']}'{/if}>{$cdfl['value']}</td>
    {if $cdfl['colspan']}</tr>{/if}
    {if $cdfields_right[ $cdfl['count'] ]}
        <td class='row1'>{$cdfields_right[ $cdfl['count'] ]['name']}</td>
        <td class='row3'>{$cdfields_right[ $cdfl['count'] ]['value']}</td>
    </tr>
    {/if}
    {/foreach}
    {/if}
    </table>
    </div>

    {if $t['close_reason']}
    <div id='smallerror'>
        <p>{$lang['close_msg_a']} {$t['close_uname']} {$lang['close_msg_b']}<br /><br />{$t['close_reason']}</p>
    </div>
    {/if}

    <h3>{$t['subject']}</h3>
    <p>
        {$t['message']}
    </p>
    {if $t['attach_id']}
    <p class='addesc'>{$lang['download_attachment']} <a href='{$td_url}/index.php?page=tickets&amp;code=attachment&amp;id={$t['attach_id']}'>{$t['original_name']}</a> ({$t['size']})<p>
    {/if}
    <br />

    {if $replies}
    {foreach $replies $r}
    <a name='reply{$r['id']}'></a>
    <div class='{$r['class']}'><div style='float:left'>{$r['uname']} -- {$r['date']}</div><div align='right'>{$r['time_ago']}{$r['rate_imgs']}</div></div>
    <p>
        {$r['message']}
    </p>
    {if $r['attach_id']}
    <p class='addesc'>{$lang['download_attachment']} <a href='{$td_url}/index.php?page=tickets&amp;code=attachment&amp;id={$r['attach_id']}'>{$r['original_name']}</a> ({$r['size']})<p>
    {/if}
    {/foreach}
    {else}
    <div class='disabled'><p>{$lang['no_replies']}</p></div>
    {/if}
</div>