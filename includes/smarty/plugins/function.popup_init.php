<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */


/**
 * Smarty {popup_init} function plugin
 *
 * Type:     function<br>
 * Name:     popup_init<br>
 * Purpose:  initialize overlib
 * @link http://smarty.php.net/manual/en/language.function.popup.init.php {popup_init}
 *          (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param array $params parameters
 * @param object $smarty Smarty object
 * @param object $template template object
 * @return string 
 */
function smarty_function_popup_init($params, $smarty, $template)
{
    $zindex = 1000;
    
    if (!empty($params['zindex'])) {
        $zindex = $params['zindex'];
    }
    
    if (!empty($params['src'])) {
        return '<div id="overDiv" style="position:absolute; visibility:hidden; z-index:'.$zindex.';"></div>' . "\n"
         . '<script type="text/javascript" language="JavaScript" src="'.$params['src'].'"></script>' . "\n";
    } else {
        trigger_error("popup_init: missing src parameter",E_USER_WARNING);
    }
}

/* vim: set expandtab: */

?>
