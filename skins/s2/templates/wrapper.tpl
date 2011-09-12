<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>{$title}</title>
    <link href='{$tpl_url}/style.css' rel='stylesheet' type='text/css' media='all' />
    <script src='{$td_url}/includes/scripts/common.js' type='text/javascript'></script>
</head>
<body>
<div id='wrap'>
    <div id='header'>
        <a href='{$td_url}/index.php' title='{$lang['trellis_desk']}'><img src='{$img_url}/header.jpg' alt='{$lang['trellis_desk']}' /></a>
    </div>
    <div id='hold_left'>
        {if $wrapper_type != 2}
        <ul id='menu'>
            <li id='menu_left'><a href='{$td_url}/index.php'>{$lang['home']}</a></li>{if $cache['config']['enable_news'] && $cache['config']['enable_news_page']}<li><a href='{$td_url}/index.php?page=news'>{$lang['news']}</a></li>{/if}<li><a href='{$td_url}/index.php?page=tickets&amp;code=history'>{$lang['ticket_history']}</a></li><li><a href='{$td_url}/index.php?page=tickets&amp;code=open'>{$lang['open_ticket']}</a></li>{if $cache['config']['enable_kb'] && $user['g_kb_access']}<li><a href='{$td_url}/index.php?page=kb'>{$lang['knowledge_base']}</a></li>{/if}<li id='menu_right'><a href='{$td_url}/index.php?page=myaccount'>{$lang['my_account']}</a></li>
        </ul>
        <div id='navbar'>
            &raquo; <a href='{$td_url}/index.php'>{$td_name}</a> {$nav_links}
        </div>
        {/if}
        {if $wrapper_type == 1}
        <div id='content'>
            {include $sub_tpl}
        </div>
        {elseif $wrapper_type == 2}
        <div id='redirect'>
            <h2>{$lang['please_wait']}</h2>
            <p>{$lang['thank_you']}</p>
            <p>{$redirect_msg}</p>
            <h3><span class='date'>{$lang['transfer_you']}&nbsp;<a href='{$redirect_url}'>{$lang['click_here']}</a>.</span></h3>
        </div>
        {elseif $wrapper_type == 3}
        <div id='error'>
            <h2>{$lang['error']}</h2>
            <p>{$lang['error_has_occured']}</p>
            <p><span class='errortxt'>{$error_msg}</span></p>
            <h3><span class='date'>{$lang['try_again']}</span></h3>
        </div>
        {elseif $wrapper_type == 4}
        <div id='error'>
            <h2>{$lang['error']}</h2>
            <p>{$lang['error_has_occured']}</p>
            <p><span class='errortxt'>{$error_msg}</span></p>
            <h3><span class='date'>{$lang['try_again']}</span></h3>
            <form action='{$td_url}/index.php?page=login' method='post'>
            {$token_e_login}
            <input type='hidden' name='extra_l' value='{$extra_l}' />
            <h2>{$lang['log_in']}</h2>
            <p>
                <input type='text' name='username' id='usernameb' value='{$lang['username']}' onfocus="clear_value(this, '{$lang['username']}')" onblur="reset_value(this, '{$lang['username']}')" size='30' />
            </p>
            <p>
                <input type='password' name='password' id='passwordb' value='{$lang['password']}' onfocus="clear_value(this, '{$lang['password']}')" onblur="reset_value(this, '{$lang['password']}')" size='30' />
            </p>
            <p>
                <label for='remember'><input type='checkbox' name='remember' id='remembebr' value='1' checked='checked' class='ckbox' /> <span class='bluetxt'>{$lang['remember_me']}</span></label>
            </p>
            <p>
                <input type='submit' class='submit' name='submit' id='loginb' value='{$lang['log_in_button']}' />
            </p>
            </form>
        </div>
        {/if}
    </div>
    <div class='sidebar'>
    {if $wrapper_type != 2}
    {if $user['id']}
        <div class='boutline'>
        <h2>{$lang['my_account']}</h2>
        <p>
            {$lang['welcome']} <b><a href='{$td_url}/index.php?page=myaccount'>{$user['name']}</a></b>.
        </p>
        <p>
            <b>{$lang['your_tickets']}</b><br />
            {if $tickets}
            {foreach $tickets $yt}
            <a href='{$td_url}/index.php?page=tickets&amp;code=view&amp;id={$yt['id']}'>{$yt['subject']}</a><br />
            {/foreach}
            {else}
            {$lang['no_tickets_short']}<br />
            {/if}<br />
            <a href='{$td_url}/index.php?page=myaccount'>{$lang['my_account']}</a>{if $user['g_acp_access']} | <a href='{$td_url}/admin.php'>{$lang['admin']}</a>{/if} | <a href='{$td_url}/index.php?page=logout&amp;key={$user['login_key']}'>{$lang['logout']}</a>
        </p>
        </div>
    {elseif $user['s_tkey']}
        <div class='boutline'>
        <h2>{$lang['my_account']}</h2>
        <p>
            {$lang['welcome']} <b>{$user['s_uname']} ({$lang['guest']})</b>.
        </p>
        <p>
            <b>{$lang['your_tickets']}</b><br />
            {if $tickets}
            {foreach $tickets $yt}
            <a href='{$td_url}/index.php?page=tickets&amp;code=view&amp;id={$yt['id']}'>{$yt['subject']}</a><br />
            {/foreach}
            {else}
            {$lang['no_tickets_short']}<br />
            {/if}<br />
            {if $cache['config']['guest_upgrade']}<a href='{$td_url}/index.php?page=register&amp;code=upgrade'>{$lang['upgrade_account']}</a> | {/if}<a href='{$td_url}/index.php?page=logout'>{$lang['logout']}</a>
        </p>
        </div>
    {else}
        <div class='boutline'>
        <form action='{$td_url}/index.php?page=login' method='post'>
        {$token_g_login}
        <input type='hidden' name='extra_l' value='{$extra_l}' />
        <h2>{$lang['log_in']}</h2>
        <p>
            <input type='text' name='username' id='username' value='{$lang['username']}' onfocus="clear_value(this, '{$lang['username']}')" onblur="reset_value(this, '{$lang['username']}')" />
        </p>
        <p>
            <input type='password' name='password' id='password' value='{$lang['password']}' onfocus="clear_value(this, '{$lang['password']}')" onblur="reset_value(this, '{$lang['password']}')" />
        </p>
        <p>
            <label for='remember'><input type='checkbox' name='remember' id='remember' value='1' checked='checked' class='ckbox' /> <span class='bluetxt'>{$lang['remember_me']}</span></label>
        </p>
        <p>
            <input type='submit' class='submit' name='submit' id='login' value='{$lang['log_in_button']}' />
        </p>
        <p>
            <a href='{$td_url}/index.php?page=register'>{$lang['register']}</a> | <a href='{$td_url}/index.php?page=register&amp;code=sendval'>{$lang['resend_val']}</a>
        </p>
        <p>
            <a href='{$td_url}/index.php?page=register&amp;code=forgot'>{$lang['forgot_pass']}</a>
        </p>
        </form>
        </div>
    {/if}
    {if $cache['config']['enable_kb'] && $user['g_kb_access']}
    <br />
    <div class='boutline'>
    <form action='{$td_url}/index.php?page=kb&amp;code=search' method='post'>
    {$token_g_search}
    <h2>{$lang['search']}</h2>
    <p>
        <input type='text' name='keywords' id='keywords' value='{$lang['enter_keywords']}' onfocus="clear_value(this, '{$lang['enter_keywords']}')" onblur="reset_value(this, '{$lang['enter_keywords']}')" />
    </p>
    <p>
        <input type='submit' class='submit' name='submit' id='search' value='{$lang['search_button']}' />
    </p>
    </form>
    </div>
    {/if}
    {/if}
       </div>
    <br style='clear:both;' />
    {$copyright}
</div>
</body>
</html>