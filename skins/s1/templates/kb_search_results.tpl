<div class='content_block'>
    <h1>{$lang['knowledge_base']}</h1>
    <div class='groupbox'>{$lang['search_results']}</div>
    {if $results}
    {foreach from=$results item=a}
    <div class='subbox'><a href='{$td_url}/index.php?page=article&amp;code=view&amp;id={$a.id}'>{$a.name}</a><span class='date'> -- {$a.date} -- {$lang['relevance']} {$a.score}</span></div>
    <div class='row{$a.class}-mini'><a href='{$td_url}/index.php?page=article&amp;code=view&amp;id={$a.id}'>{$a.description}</a></div>
    {/foreach}
    {else}
    <div class='option1'>{$lang['no_articles_found']}</div>
    {/if}
</div>