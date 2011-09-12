<div class="content_block">
    <h1>{$lang['news']}</h1>
    {if $news}
    {foreach $news as $n}
    <div class="groupbox"><div style="float:right">{$n['date_human']}</div><a href="{$td_url}/index.php?page=news&amp;act=view&amp;id={$n['id']}">{$n['title']}</a></div>
    <div class="row1 post">
        {$n['content']}
    </div>
    {if $n['comments']}<div class="option2-mini"><span class="date"><a href="{$td_url}/index.php?page=news&amp;act=view&amp;id={$n['id']}#comments">({$n['comments']}) {$lang['comments']}</a></span></div>{/if}
    {/foreach}
    <div class="pagelinks">{$page_links}</div>
    {/if}
</div>