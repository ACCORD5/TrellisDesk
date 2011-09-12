<div class="content_block">
    <h1>{$lang['view_category']}</h1>
    {if $categories}
    <div class="groupbox">{$lang['sub_categories']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    {foreach $categories as $c}
    <tr>
        <td width="23%" class="option2"><a href="{$td_url}/index.php?page=kb&amp;act=cat&amp;id={$c['id']}">{$c['name']}</a></td>
        <td width="72%" class="row1-med"><a href="{$td_url}/index.php?page=kb&amp;act=cat&amp;id={$c['id']}">{if $c['description']}{$c['description']}{else}<i>{$lang['no_description']}</i>{/if}</a></td>
        <td width="5%" class="option2" align="center"><a href="{$td_url}/index.php?act=kb&amp;page=cat&amp;id={$c['id']}">{$c['articles']}</a></td>
    </tr>
    {/foreach}
    </table><br />
    {/if}
    <div class="groupbox">{$c['name']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    {if $articles}
    {foreach $articles as $a}
    <tr>
        <td width="67%" class="option2"><a href="{$td_url}/index.php?page=kb&amp;act=view&amp;id={$a['id']}">{$a['title']}</a></td>
        <td width="18%" class="option2"><form action="#">{$a['rate_stars']}</form></td>
        <td width="15%" class="option2" align="center">{$a['views']} {$lang['views']}</td>
    </tr>
    <tr>
        <td colspan="3" class="row1-med"><a href="{$td_url}/index.php?page=kb&amp;act=view&amp;id={$a['id']}">{if $a['description']}{$a['description']}{else}<i>{$lang['no_description']}</i>{/if}</a></td>
    </tr>
    {/foreach}
    <tr>
        <td class="pagelinks" colspan="3">{$page_links}</td>
    </tr>
    {else}
    <tr>
        <td class="option1" align="center">{$lang['no_articles']}</td>
    </tr>
    {/if}
    </table>
</div>