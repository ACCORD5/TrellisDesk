<form action='{$td_url}/index.php?page=tickets&amp;code=submit' method='post'>
{$token_sub_c}
<input type='hidden' name='final' value='1' />
<input type='hidden' name='subject' value='{$input['subject']}' />
<input type='hidden' name='department' value='{$input['department']}' />
<input type='hidden' name='priority' value='{$input['priority']}' />
<input type='hidden' name='message' value='{$input['message']}' />
{if $user['id'] == 0}
<input type='hidden' name='name' value='{$input['name']}' />
<input type='hidden' name='email' value='{$input['email']}' />
<input type='hidden' name='guest_email' value='{$input['guest_email']}' />
{/if}
{$cdfields}
{$attach_field}
<div class='content_block'>
    <h2>{$lang['article_suggestions']}</h2>
    <p>{$lang['suggestions_explained']}</p>
    {foreach $suggestions $a}
    <h3><a href='{$td_url}/index.php?page=article&amp;code=view&amp;id={$a['id']}' target='_blank'>{$a['name']}</a><span class='date'> -- {$a['date']} -- {$lang['relevance']} {$a['score']}</span></h3>
    <div class='sdesc'>{$a['description']}</div>
    {/foreach}
    <br />

    <h2>{$lang['continue_ticket_submit']}</h2>
    <p>{$lang['no_suggestions_helped']}</p>
    {if $error}
    <div id='smallerror'>
        <p>{$error}</p>
    </div>
    {/if}
    {if $cache['config']['use_captcha'] && $user['id'] == 0}
    <table width='100%' cellpadding='0' cellspacing='0' class='fakep'>
    <tr>
        <td class='row1' width='1'><label for='captcha'>{$lang['captcha']}</label></td>
        <td style='padding-left:5px'><img src='{$td_url}/index.php?page=captcha&amp;code=create&amp;width=100&amp;height=18&amp;fontsize=8' alt='{$lang['captcha']}' style='vertical-align:bottom' /> <input type='text' name='captcha' id='captcha' size='12' /></td>
    </tr>
    </table>
    {/if}
    <p><input type='submit' class='submit' name='submit' id='send' value='{$lang['open_ticket_button']}' /></p>
</div>
</form>