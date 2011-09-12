{literal}
<script type='text/javascript'>

    function sure_close()
    {
        if ( confirm('{/literal}{$lang['confirm_close']}{literal}') )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function sure_escalate()
    {
        if ( confirm('{/literal}{$lang['confirm_escalate']}{literal}') )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function sure_delete_reply()
    {
        if ( confirm('{/literal}{$lang['confirm_delete_reply']}{literal}') )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function validate_form(form)
    {
        if ( ! form.message.value )
        {
            alert('{/literal}{$lang['err_no_message']}{literal}');
            form.message.focus();
            return false;
        }
    }

    function amithumbsup(rate)
    {
        document.images['thumbsup_'+rate].src = '{/literal}{$img_url}{literal}/thumbs_up_hover.gif';
    }

    function unamithumbsup(rate)
    {
        document.images['thumbsup_'+rate].src = '{/literal}{$img_url}{literal}/thumbs_up.gif';
    }

    function amithumbsdown(rate)
    {
        document.images['thumbsdown_'+rate].src = '{/literal}{$img_url}{literal}/thumbs_down_hover.gif';
    }

    function unamithumbsdown(rate)
    {
        document.images['thumbsdown_'+rate].src = '{/literal}{$img_url}{literal}/thumbs_down.gif';
    }

</script>
{/literal}
<div class='content_block'>
    <div class='bluestripbig'><div style='float:right' class='blinks'><a href='{$td_url}/index.php?page=tickets&amp;code=print&amp;id={$t['id']}'>{$lang['print']}</a>{if $t['links']} | {$t['links']}{/if}</div><div align='left'>{$lang['viewing_ticket']}</div></div>
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

    <h3>{$t['subject']}{$t['edit_link']}</h3>
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
    <br />

    {if $t['status'] != 6}
    <h2>{$lang['send_reply']}</h2>
    <form action='{$td_url}/index.php?page=tickets&amp;code=reply&amp;id={$t['id']}' method='post' onsubmit='return validate_form(this)' enctype='multipart/form-data'>
    {$token_ticket_reply}
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <p><textarea name='message' id='message' cols='70' rows='8'>{$input['message']}</textarea></p>
    {if $cache['config']['ticket_attachments'] && $user['g_ticket_attach'] && $cache['depart'][ $t['did'] ]['allow_attach']}
    <p><input type='file' name='attachment' id='attachment' size='32' />{$upload_info}</p>
    {/if}
    <p><input type='submit' class='submit' name='submit' id='send' value='{$lang['send_reply_button']}' /></p>
    </form>
    {/if}
</div>