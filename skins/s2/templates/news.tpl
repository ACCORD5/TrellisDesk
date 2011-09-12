<div class='content_block'>
    <h2>{$lang['news']}</h2>
    {if $news}
    {foreach $news $an}
    <h3><a href='{$td_url}/index.php?page=news&amp;code=view&amp;id={$an['id']}'>{$an['title']}</a><span class='date'> -- {$an['date']}</span></h3>
    <p>
        {$an['content']}
    </p>
    {if $cache['config']['news_comments']}<p class='desc'><a href='{$td_url}/index.php?page=news&amp;code=view&amp;id={$an['id']}#comments'>({$an['comments']}) {$lang['comments']}</a></p>{/if}
    {/foreach}
    {/if}
</div>