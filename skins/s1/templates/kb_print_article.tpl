<h2>{$article.name}<span class='date'> -- {$article.date}</span></h2>
{$article.article}
<br />

{if $comments}
{foreach from=$comments item=c}
<a name='com{$c.id}'></a>
<div class='bluestrip'><div style='float:left'>{$c.uname} -- {$c.date}</div><div align='right'>{$c.time_ago}</div></div>
<p>
    {$c.comment}
</p>
{/foreach}<br />
{elseif $cache.config.allow_kb_comment && ! $article.dis_comments}
<div class='disabled'><p>{$lang['no_comments']}</p></div>
{/if}