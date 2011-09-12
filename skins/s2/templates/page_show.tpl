<div class='content_block'>
    {if $p['type']}
    {include $template}
    {else}
    <h2>{$p['name']}</h2>
    <p>{$p['content']}</p>
    {/if}
</div>