<div class='content_block'>
    <h2>{$lang['search_results']}</h2>
    {if $results}
    {foreach $results $a}
    <h3><a href='{$td_url}/index.php?page=article&amp;code=view&amp;id={$a['id']}'>{$a['name']}</a><span class='date'> -- {$a['date']} -- {$lang['relevance']} {$a['score']}</span></h3>
    <div class='sdesc'>{$a['description']}</div>
    {/foreach}
    {else}
    <p><span class='date'>{$lang['no_articles_found']}</span></p>
    {/if}
</div>