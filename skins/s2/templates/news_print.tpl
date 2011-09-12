<h2>{$n['title']} <span class='date'>-- {$n['date']}</span></h2>
<p style='padding-bottom:4px'>
    {$n['content']}
</p>
{if $comments}
<a name='comments'></a>
{foreach $comments $c}
<a name='com{$c['id']}'></a>
<div class='bluestrip'><div style='float:left'>{$c['uname']} -- {$c['date']}</div><div align='right'>{$c['time_ago']}</div></div>
<p>
    {$c['comment']}
</p>
{/foreach}
{elseif $cache['config']['news_comments'] && ! $n['dis_comments']}
<div class='disabled'><p>{$lang['no_comments']}</p></div>
{/if}