<div class='content_block'>
    <h2>{$lang['knowledge_base']}</h2>
    <h3 style='margin-top: 4px'>{$lang['search']}</h3>
    <form action='{$td_url}/index.php?page=kb&amp;code=search' method='post'>
    {$token_kb_search}
    <p>
        <textarea name='keywords' id='keywordsb' cols='70' rows='2' onfocus="clear_value(this, '{$lang['keywords_phrase']}')" onblur="reset_value(this, '{$lang['keywords_phrase']}')">{$lang['keywords_phrase']}</textarea>
    </p>
    <p>
        <input type='submit' class='submit' name='submit' id='searchb' value='{$lang['search_button']}' />
    </p>
    </form>
    <h3>{$lang['categories']}</h3>
    <table width='100%' cellpadding='3' cellspacing='1' class='padnotop'>
    <tr>
        <td width='50%' valign='top'>
        {foreach $cats_left $cl}
        <span class='ctitle'><a href='{$td_url}/index.php?page=kb&amp;code=cat&amp;id={$cl['id']}'>{$cl['name']} ({$cl['articles']})</a></span>
        <div class='bldesc'>{$cl['description']}</div>
        {/foreach}
        </td>
        <td width='50%' valign='top'>
        {foreach $cats_right $cr}
        <span class='ctitle'><a href='{$td_url}/index.php?page=kb&amp;code=cat&amp;id={$cr['id']}'>{$cr['name']} ({$cr['articles']})</a></span>
        <div class='bldesc'>{$cr['description']}</div>
        {/foreach}
        </td>
    </tr>
    </table>
</div>