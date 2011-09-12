{literal}
<script type='text/javascript'>

    function validate_form(form)
    {
        if ( ! form.comment.value )
        {
            alert('{/literal}{$lang['err_no_comment']}{literal}');
            form.comment.focus();
            return false;
        }
    }

</script>
{/literal}
<div class='content_block'>
    <h2>{$lang['edit_comment']}</h2>
    <form action='{$td_url}/index.php?page=news&amp;code=doedit&amp;id={$comment['id']}' method='post' onsubmit='return validate_form(this)'>
    {$token_edit_comment}
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    <p><textarea name='comment' id='comment' cols='70' rows='8'>{$comment['comment']}</textarea></p>
    <p><input type='submit' class='submit' name='submit' id='send' value='{$lang['edit_comment_button']}' /></p>
    </form>
</div>