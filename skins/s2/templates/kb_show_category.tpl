<div class='content_block'>
    <h2>{$lang['view_category']}</h2>
    {foreach $articles $a}
    <h3><a href='{$td_url}/index.php?page=article&amp;code=view&amp;id={$a['id']}'>{$a['name']}</a><span class='date'> -- {$a['date']}</span></h3>
    <p><div class='desc'>{$a['description']}</div></p>
    {/foreach}
</div>