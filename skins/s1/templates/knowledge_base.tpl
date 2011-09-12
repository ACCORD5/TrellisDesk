<div class="content_block">
    <h1>{$lang['knowledge_base']}</h1>
    <div class="groupbox">{$lang['search']}</div>
    <form action="{$td_url}/index.php?page=kb&amp;act=search" method="post">
    <div class="option1">
        <textarea name="search" id="search" style="width: 98%; height: 35px;">{$lang['keywords_phrase']}</textarea><br /><br />
        <input type="submit" name="submit" id="dosearch" value="{$lang['search_button']}" class="button" />
    </div>
    </form><br />
    <div class="groupbox">{$lang['categories']}</div>
    <table width="100%" cellpadding="0" cellspacing="0">
    {if $categories}
    {foreach $categories as $c}
    <tr>
        <td width="23%" class="option2"><a href="{$td_url}/index.php?page=kb&amp;act=cat&amp;id={$c['id']}">{$c['name']}</a></td>
        <td width="72%" class="row1-med"><a href="{$td_url}/index.php?page=kb&amp;act=cat&amp;id={$c['id']}">{if $c['description']}{$c['description']}{else}<i>{$lang['no_description']}</i>{/if}</a></td>
        <td width="5%" class="option2" align="center"><a href="{$td_url}/index.php?act=kb&amp;page=cat&amp;id={$c['id']}">{$c['articles']}</a></td>
    </tr>
    {/foreach}
    {else}
    <tr>
        <td class="option1" align="center">{$lang['no_categories']}</td>
    </tr>
    {/if}
    </table>
</div>