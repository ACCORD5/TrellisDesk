{literal}
<script type="text/javascript">

    function amirate(rate)
    {
        rating = Math.round(rate);

        for ( x = 1; x < rate + 1; x++ )
        {
            document.images['rate'+x].src = '{/literal}{$img_url}{literal}/rate_hover.gif';
        }
    }

    function unamirate(rating)
    {
        rating = Math.round(rating);

        for ( x = 1; x <= rating; x++ )
        {
            document.images['rate'+x].src = '{/literal}{$img_url}{literal}/rate_on.gif';
        }

        for ( x = x; x <= 5; x++ )
        {
            document.images['rate'+x].src = '{/literal}{$img_url}{literal}/rate_off.gif';
        }
    }

    function validate_form(form)
    {
        if ( ! form.comment.value )
        {
            alert('{/literal}{$lang['err_no_comment']}{literal}');
            form.comment.focus();
            return false;
        }
    }

    function sure_delete()
    {
        if ( confirm('{/literal}{$lang['confirm_delete']}{literal}') )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <div class='bluestripbig'><div style='float:right'>{$rate}<span class='blinks' style='vertical-align:top'> | <a href='{$td_url}/index.php?page=article&amp;code=print&amp;id={$article['id']}'>{$lang['print']}</a></span></div><div align='left'>{$lang['viewing_article']}</div></div>
    <h3>{$article['name']} <span class='date'>-- {$article['date']}</span></h3>
    <div class='fakep'>
        {$article['article']}
    </div><br />

    {if $comments}
    {foreach $comments $c}
    <a name='com{$c['id']}'></a>
    <div class='bluestrip'><div style='float:left'>{$c['uname']} -- {$c['date']}</div><div align='right'>{$c['time_ago']}{if $user['g_com_edit_all']} <span class='response_imgs'><a href='{$td_url}/index.php?page=article&amp;code=edit&amp;id={$c['id']}'><img src='{$img_url}/edit_icon.gif' alt='{$lang['edit']}' id='edit_{$c['id']}' /></a></span>{/if}{if $user['g_com_edit_all'] && $user['g_com_delete_all']} |{/if}{if $user['g_com_delete_all']} <span class='response_imgs'><a href='{$td_url}/index.php?page=article&amp;code=delete&amp;id={$c['id']}' onclick='return sure_delete()'><img src='{$img_url}/delete_icon.gif' alt='{$lang['delete']}' id='delete_{$c['id']}' /></a></span>{/if}</div></div>
    <p>
        {$c['comment']}
    </p>
    {/foreach}<br />
    {elseif $cache['config']['allow_kb_comment'] && ! $article['dis_comments']}
    <div class='disabled'><p>{$lang['no_comments']}</p></div>
    {/if}

    {if $show_comment_form}
    <h2>{$lang['add_a_comment']}</h2>
    <form action='{$td_url}/index.php?page=article&amp;code=comment&amp;id={$article['id']}' method='post' onsubmit='return validate_form(this)'>
    {$token_add_comment}
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <p><textarea name='comment' id='comment' cols='70' rows='8'>{$input['comment']}</textarea></p>
    <p><input type='submit' class='submit' name='submit' id='send' value='{$lang['add_comment_button']}' /></p>
    </form>
    {/if}
</div>