<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>{$title}</title>
    <link href="{$css_url}/jquery-ui.css" rel="stylesheet" type="text/css" media="screen" />
    <link href="{$css_url}/style.css" rel="stylesheet" type="text/css" media="all" />
    <script src="{$js_url}/global.js" type="text/javascript"></script>
</head>
<body>
<div id="wrap">
    <div id="header">
        <a href="{$td_url}/index.php" title="{$lang['trellis_desk']}"><img src="{$img_url}/header.jpg" alt="{$lang['trellis_desk']}" /></a>
    </div>
    <div id="hold_left">
        <div id="menu">
            <div id="menuleft"></div>
            <div id="menucenter">
                <a href="{$td_url}/index.php">{$lang['home']}</a>
                {if $cache['settings']['news']['enable'] && $cache['settings']['news']['page']}<a href="{$td_url}/index.php?page=news">{$lang['news']}</a>{/if}
                <a href="{$td_url}/index.php?page=tickets">{$lang['ticket_history']}</a>
                <a href="{$td_url}/index.php?page=tickets&amp;act=add">{$lang['open_ticket']}</a>
                {if $cache['settings']['kb']['enable'] && $user['g_kb_access']}<a href="{$td_url}/index.php?page=kb">{$lang['knowledge_base']}</a>{/if}
                <a href="{$td_url}/index.php?page=account">{$lang['my_account']}</a></div>
            <div id="menuright"></div>
        </div>
        <div id="navbar">
            <div id="navbar_inline">
            &raquo; <a href="{$td_url}/index.php">{$td_name}</a> {$nav_links}
            </div>
        </div>
        {if $wrapper_type == 1}
        <div id="content">
            {include file="$sub_tpl"}
        </div>
        {elseif $wrapper_type == 2}
        <div id="content">
        <div class="content_block">
            <div class="alert">
                {$redirect_msg}<br /><br />
                <span class="small">{$lang['transfer_you']}&nbsp;<a href="{$redirect_url}">{$lang['click_here']}</a>.</span>
            </div>
        </div>
        </div>
        {elseif $wrapper_type == 3 || $wrapper_type == 4}
        <div id="content">
        <div class="content_block">
            <div class="critical">
                {$error_msg}<br /><br />
                <span class="small">{$lang['error_small_print']}</span>
            </div>
            {if $wrapper_type == 4}
            <form action="{$self}" method="post">
            <input type="hidden" name="do_login" value="1" />
            <div class="groupbox">{$lang['log_in']}</div>
            <div class="option1"><input type="text" name="username" id="username" value="{$lang['username']}" size="30" /></div>
            <div class="option2"><input type="password" name="password" id="password" value="{$lang['password']}" size="30" /></div>
            <div class="option1-med"><label for="rememberb"><input type="checkbox" name="remember" id="rememberb" value="1" checked="checked" class="ckbox" /> {$lang['remember_me']}</label></div>
            <div class="formtail"><input type="submit" name="submit" id="loginb" value="{$lang['log_in_button']}" class="button" /></div>
            </form>
            {/if}
        </div>
        </div>
        {/if}
    </div>
    {if $wrapper_type != 2}
    <div class="sidebar">
    {if $user['id']}
        <h2>{$lang['my_account']}</h2>
        <div class="row1-med">
            {$lang['welcome']} <b><a href="{$td_url}/index.php?page=myaccount">{$user['name']}</a></b>.<br /><br />
            <a href="{$td_url}/index.php?page=account">{$lang['my_account']}</a> | <a href="{$td_url}/index.php?act=logout">{$lang['logout']}</a>
        </div>
    {elseif $user['s_tkey']}
        <h2>{$lang['my_account']}</h2>
        <div class="row1-med">
            {$lang['welcome']} <b>{$user['s_uname']} ({$lang['guest']})</b>.<br /><br />
            <b>{$lang['your_tickets']}</b><br />
            {if $tickets}
            {foreach $tickets as $yt}
            <a href="{$td_url}/index.php?page=tickets&amp;act=view&amp;id={$yt['id']}">{$yt['subject']}</a><br />
            {/foreach}
            {else}
            {$lang['no_tickets_short']}<br />
            {/if}<br />
            {if $cache['settings']['user']['guest_upgrade']}<a href="{$td_url}/index.php?page=register&amp;act=upgrade">{$lang['upgrade_account']}</a> | {/if}<a href="{$td_url}/index.php?page=logout">{$lang['logout']}</a>
        </div>
    {else}
        <h2>{$lang['log_in']}</h2>
        <div class="row1-med">
            {if $wrapper_type != 4}
            <form action="{$self}" method="post">
            <input type="hidden" name="do_login" value="1" />
            <input type="text" name="username" id="username" value="{$lang['username']}" /><br />
            <input type="password" name="password" id="password" value="{$lang['password']}" style="margin-top:5px"/><br />
            <div style="margin-top:5px"><label for="remember"><input type="checkbox" name="remember" id="remember" value="1" checked="checked" class="ckbox" /> <span class="bluetxt">{$lang['remember_me']}</span></label></div>
            <input type="submit" class="submit" name="submit" id="login" value="{$lang['log_in_button']}" style="margin-top:5px"/><br /><br />
            </form>
            {/if}
            <a href="{$td_url}/index.php?page=register">{$lang['register']}</a> | <a href="{$td_url}/index.php?page=register&amp;act=resendval">{$lang['resend_val']}</a><br />
            <a href="{$td_url}/index.php?page=register&amp;act=forgotpass">{$lang['forgot_pass']}</a>
        </div>
    {/if}
    {if $cache['settings']['kb']['enable'] && $user['g_kb_access']}
    {if $input['page'] != 'kb' || $input['act']}
    <br />
    <form action="{$td_url}/index.php?page=kb&amp;act=search" method="post">
    <h2>{$lang['search']}</h2>
    <div class="row1-med">
    <input type="text" name="search" id="search" value="{$lang['enter_keywords']}" /><br /><br />
    <input type="submit" name="submit" id="dosearch" value="{$lang['search_button']}" />
    </div>
    </form>
    {else}
    {if $articles_recent}
    <br />
    <h2>{$lang['articles_recent']}</h2>
    <div class="row1-med">
    {foreach $articles_recent as $a}
    <a href="{$td_url}/index.php?page=kb&amp;act=view&amp;id={$a['id']}" title="{$a['description']}">{$a['title']}</a><br />
    {/foreach}
    </div>
    {/if}
    {if $articles_most_viewed}
    <br />
    <h2>{$lang['articles_most_viewed']}</h2>
    <div class="row1-med">
    {foreach $articles_most_viewed as $a}
    <a href="{$td_url}/index.php?page=kb&amp;act=view&amp;id={$a['id']}" title="{$a['description']}">{$a['title']}</a><br />
    {/foreach}
    </div>
    {/if}
    {if $articles_top_rated}
    <br />
    <h2>{$lang['articles_top_rated']}</h2>
    <div class="row1-med">
    {foreach $articles_top_rated as $a}
    <a href="{$td_url}/index.php?page=kb&amp;act=view&amp;id={$a['id']}" title="{$a['description']}">{$a['title']}</a><br />
    {/foreach}
    </div>
    {/if}
    {/if}
    {/if}
       </div>
    {/if}
    <br style="clear:both;" />
    <!-- Trellis Desk is a free product. All we ask is that you keep the copyright intact. -->
    <!-- Purchase our copyright removal service at http://www.accord5.com/copyright  -->
    <div style="margin-right: 10px">
    {$copyright}
    </div>
</div>
</body>
</html>