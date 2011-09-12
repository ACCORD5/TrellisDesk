<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>{$title}</title>
    <link href='{$tpl_url}/style.css' rel='stylesheet' type='text/css' media='all' />
    {literal}<style type="text/css" media="all">
    body {
        background: #FFF;
    }
    </style>{/literal}
</head>
<body>
<div id='print_wrap'>
    {include file="$sub_tpl"}
    <br style='clear:both;' />
    {$copyright}
</div>
</body>
</html>