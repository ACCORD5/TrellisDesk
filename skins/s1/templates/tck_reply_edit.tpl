<div class="content_block">
    <h1>{$lang['tickets']}</h1>
    {if $error}<div class="critical">{$error}</div>{/if}
    <div class="groupbox">{$lang['edit_reply']}</div>
    <form action="{$td_url}/index.php?page=tickets&amp;act=doeditreply&amp;id={$r['id']}" method="post">
    <div class="option1"><textarea name="message" id="message" rows="6" cols="100" style="width: 98%; height: 140px;">{$r['message']}</textarea></div>
    <div class="formtail"><input type="submit" name="submit" id="send" value="{$lang['edit_reply_button']}" class="button" /></div>
    </form>
</div>