<div class="content_block">
    <h1>{$lang['viewing_ticket']}</h1>
    {if $error}<div class="critical">{$error}</div>{/if}
    {if $alert}<div class="alert">{$alert}</div>{/if}
    <div class="groupbox">
        <div style="float:right">
            <a href="{$td_url}/index.php?page=tickets&amp;act=print&amp;id={$t['mask']}">{$lang['print']}</a>
            {if $t['can_escalate']} | <a href="{$td_url}/index.php?page=tickets&amp;act=doescalate&amp;id={$t['mask']}" onclick="return sure_escalate()">{$lang['escalate']}</a>{/if}
            {if $t['can_close']} | <a href="{$td_url}/index.php?page=tickets&amp;act=doclose&amp;id={$t['mask']}" onclick="return sure_close()">{$lang['close']}</a>{/if}
            {if $t['can_reopen']} | <a href="{$td_url}/index.php?page=tickets&amp;act=doreopen&amp;id={$t['mask']}" onclick="return sure_reopen()">{$lang['reopen']}</a>{/if}
        </div>
        {$t['subject']}
    </div>
    <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td class="option1-med" width="20%">{$lang['ticket_id']}</td>
        <td class="row1-med" width="30%">{if $t['escalated']}<img src="{$img_url}/icon_escalate.png" alt="E" style="vertical-align:middle;margin-bottom:2px" />&nbsp;{/if}{$t['mask']}</td>
        <td class="option1-med" width="20%">{$lang['replies']}</td>
        <td class="row1-med" width="30%">{$t['replies']}</td>
    </tr>
    <tr>
        <td class="option2-med">{$lang['priority']}</td>
        <td class="row2-med"><img src="{$td_url}/images/priorities/{$t['priority_icon']}" alt="{$t['priority_human']}" class="prioritybox" style="vertical-align:middle;margin-bottom:2px" />&nbsp;&nbsp;{$t['priority_human']}</td>
        <td class="option2-med">{$lang['last_reply']}</td>
        <td class="row2-med">{$t['last_reply_human']}</td>
    </tr>
    <tr>
        <td class="option1-med">{$lang['department']}</td>
        <td class="row1-med">{$t['dname']}</td>
        <td class="option1-med">{$lang['last_replier']}</td>
        <td class="row1-med">{$t['last_uname']}</td>
    </tr>
    <tr>
        <td class="option2-med">{$lang['submitted']}</td>
        <td class="row2-med">{$t['date_human']}</td>
        <td class="option2-med">{$lang['status']}</td>
        <td class="row2-med">{$t['status_human']}</td>
    </tr>
    {if $cdfields}
    {$fields_count = 0}{$fields_rows = 0}
    {foreach $cdfields as $f}
    {$fields_count = $fields_count+1}
    {if $fields_count & 1}
    {$fields_rows = $fields_rows+1}
    <tr>
    {/if}
    {if $fields_rows & 1}{$field_class = 1}{else}{$field_class = 2}{/if}
        <td class="option{$field_class}-med">{$f['name']}</td>
        <td class="row{$field_class}-med"{if $fields_count == count($cdfields) && $fields_count & 1} colspan="3"{/if}>
        {if $f['type'] == "checkbox"}
            {foreach $f['extra'] as $key => $name}
            <input type="checkbox" name="cdf_{$f['id']}_{$key}" id="cdf_{$f['id']}_{$key}" value="1" class="ckbox"{if $cdfdata[$f['id']][$key]} checked="checked"{/if} disabled="disabled" /> {$name}&nbsp;&nbsp;&nbsp;
            {/foreach}
        {elseif $f['type'] == "dropdown" || $f['type'] == "radio"}
        {if $f['extra'][$cdfdata[$f['id']]]}{$f['extra'][$cdfdata[$f['id']]]}{else}--{/if}
        {else}
        {if $cdfdata[$f['id']]}{$cdfdata[$f['id']]}{else}--{/if}
        {/if}
        </td>
    {if ! ($fields_count & 1)}</tr>{/if}
    {/foreach}
    {if $fields_count == count($cdfields) && $fields_count & 1}</tr>{/if}
    {/if}
    </table>

    <br />
    <div class="groupbox">"{$t['subject']}" by {$t['uname']}{if $t['can_edit']}<div style="float:right"><a href="{$td_url}/index.php?page=tickets&amp;act=edit&amp;id={$t['mask']}">{$lang['edit']}</a></div>{/if}</div>
    <div class="row1 post">
        {$t['message']}
    </div>

    {if $replies}
    {$replies_count = 0}
    {foreach $replies as $r}
    {$replies_count = $replies_count+1}
    {if $replies_count & 1}{$reply_class = 2}{else}{$reply_class = 1}{/if}
    <a id="r{$r['id']}" name="r{$r['id']}"></a>
    <div class="subbox{if $r['staff']}staff{/if}">
        <div class="links" style="float:right">{$r['time_ago']}{if $r['can_edit']}&nbsp;&nbsp;<a href="{$td_url}/index.php?page=tickets&amp;act=editreply&amp;id={$r['id']}"><img src="{$img_url}/edit_icon.gif" alt="{$lang['edit']}" style="vertical-align:middle" /></a>{/if}{if $r['can_delete']}&nbsp;&nbsp;<a href="{$td_url}/index.php?page=tickets&amp;act=dodelreply&amp;id={$r['id']}" onclick="return sure_delete_reply()"><img src="{$img_url}/delete_icon.gif" alt="{$lang['delete']}" style="vertical-align:middle" /></a>{/if}{$r['rate_imgs_solo']}</div>
        {$r['uname']} -- {$r['date_human']}
    </div>
    <div class="row{$reply_class} post">
        {$r['message']}
    </div>
    {/foreach}
    {else}
    <div class="option2-mini">{$lang['no_replies']}</div>
    {/if}
    <br />

    {if $t['can_reply']}
    {if $error_reply}<div class="critical">{$error_reply}</div>{/if}
    <div class="groupbox">{$lang['send_reply']}</div>
    <form action="{$td_url}/index.php?page=tickets&amp;act=doaddreply&amp;id={$t['mask']}" method="post" enctype="multipart/form-data">
    <div class="option1"><textarea name="message" id="message" rows="7" cols="100" style="width: 98%; height: 140px;"></textarea></div>
    {if $upload_form}
    <div class="option2">{$upload_form}</div>
    {/if}
    <div class="formtail"><input type="submit" name="submit" id="send" value="{$lang['send_reply_button']}" class="button" /></div>
    </form>
    {/if}
</div>
<script type="text/javascript">
//<![CDATA[
function sure_close() {
    if ( confirm("{$lang['confirm_close']}") ) {
        return true;
    }
    else {
        return false;
    }
}

function sure_reopen() {
    if ( confirm("{$lang['confirm_reopen']}") ) {
        return true;
    }
    else {
        return false;
    }
}

function sure_escalate() {
    if ( confirm("{$lang['confirm_escalate']}") ) {
        return true;
    }
    else {
        return false;
    }
}

function sure_delete_reply() {
    if ( confirm("{$lang['confirm_delete_reply']}") ) {
        return true;
    }
    else {
        return false;
    }
}

function amithumbsup(rate)
{
    document.images["thumbsup_"+rate].src = "{$img_url}/thumbs_up_hover.gif";
}

function unamithumbsup(rate)
{
    document.images["thumbsup_"+rate].src = "{$img_url}/thumbs_up.gif";
}

function amithumbsdown(rate)
{
    document.images["thumbsdown_"+rate].src = "{$img_url}/thumbs_down_hover.gif";
}

function unamithumbsdown(rate)
{
    document.images["thumbsdown_"+rate].src = "{$img_url}/thumbs_down.gif";
}
//]]>
</script>
{if $scroll}{scroll element="{$scroll}"}{/if}