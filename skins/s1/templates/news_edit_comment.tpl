<div class="content_block">
    <h1>{$lang['news']}</h1>
    {if $error}<div class="critical">{$error}</div>{/if}
    <div class="groupbox">{$lang['edit_comment']}</div>
    <form action="{$td_url}/index.php?page=news&amp;act=doeditcomment&amp;id={$c['id']}" method="post">
    <div class="option1"><textarea name="message" id="message" rows="8" cols="100" style="width: 98%; height: 140px;">{$c['message']}</textarea></div>
    <div class="formtail"><input type="submit" name="submit" id="send" value="{$lang['edit_comment_button']}" class="button" /></div>
    </form>
</div>