<div class="content_block">
    <h1>{$lang['viewing_article']}</h1>
    {if $error}<div class="critical">{$error}</div>{/if}
    {if $alert}<div class="alert">{$alert}</div>{/if}
    <div class="groupbox">
        <div style="float:right">{if $a['rate_stars']}{$a['rate_stars']}&nbsp;|&nbsp;{/if}{$a['date_human']} | <a href="{$td_url}/index.php?page=kb&amp;act=print&amp;id={$a['id']}">{$lang['print']}</a>
        </div>
    {$a['title']}
    </div>
    <div class="row1 post">
        {$a['content']}
    </div>

    {if $comments}
    {$comments_count = 0}
    <br />
    <a name="comments"></a>
    <div class="groupbox">{$lang['comments']}</div>
    {foreach $comments as $c}
    {$comments_count = $comments_count+1}
    {if $comments_count & 1}{$comments_class = 2}{else}{$comments_class = 1}{/if}
    <a id="c{$c['id']}" name="c{$c['id']}"></a>
    <div class="subbox{if $c['staff']}staff{/if}">
        <div class="links" style="float:right">{$c['time_ago']}{if $c['can_edit']}&nbsp;&nbsp;<a href="{$td_url}/index.php?page=kb&amp;act=editcomment&amp;id={$c['id']}"><img src="{$img_url}/edit_icon.gif" alt="{$lang['edit']}" id="edit_{$c['id']}" style="vertical-align: middle" /></a>{/if}{if $c['can_delete']}&nbsp;&nbsp;<a href="{$td_url}/index.php?page=kb&amp;act=dodeletecomment&amp;id={$c['id']}" onclick="return sure_delete()"><img src="{$img_url}/delete_icon.gif" alt="{$lang['delete']}" id="delete_{$c['id']}" style="vertical-align: middle" /></a>{/if}
        </div>{$c['uname']} -- {$c['date_human']}
    </div>
    <div class="row{$comments_class} post">
        {$c['message']}
    </div>
    {/foreach}
    {elseif $a['can_comment']}
    <div class="option2-mini">{$lang['no_comments']}</div>
    {/if}

    {if $a['can_comment']}
    <br />
    {if $error_comment}<div class="critical">{$error_comment}</div>{/if}
    <div class="groupbox">{$lang['add_a_comment']}</div>
    <form action="{$td_url}/index.php?page=kb&amp;act=doaddcomment&amp;id={$a['id']}" method="post">
    <div class="option1"><textarea name="message" id="message" rows="6" cols="100" style="width: 98%; height: 100px;"></textarea></div>
    <div class="formtail"><input type="submit" name="submit" id="send" value="{$lang['add_comment_button']}" class="button" /></div>
    </form>
    {/if}
</div>
{* modify with caution *}
<script type="text/javascript">
//<![CDATA[
$(function(){
    $('.auto-submit-star').rating({
        callback: function(value, link){
        goToUrl('{$td_url}/index.php?page=kb&act=dorate&id={$a['id']}&amount='+value);
        }
    });
});
function sure_delete() {
    if ( confirm("{$lang['confirm_delete']}") ) {
        return true;
    }
    else {
        return false;
    }
}
//]]>
</script>
{if $scroll}{scroll element="{$scroll}"}{/if}