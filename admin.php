<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

define( 'IN_TD', true );
define( 'IN_TDA', true );

#=============================
# Lets Play Nice With Output
#=============================

ob_end_clean();
ob_start();

#=============================
# Safe and Secure
#=============================

if ( function_exists('date_default_timezone_get') )
{
     date_default_timezone_set( date_default_timezone_get() );
}

ini_set( 'register_globals', 0 );

if ( @ini_get( 'register_globals' ) )
{
    foreach ( $_REQUEST as $key => $value )
    {
        unset( $$key );
    }
}

#=============================
# Define Our Paths
#=============================

define( "TD_PATH", str_replace( '//', '/', dirname(__FILE__).'/' ) );
define( 'TD_INC', TD_PATH ."includes/" );
define( 'TD_CLASS', TD_PATH ."includes/classes/class_" );
define( 'TD_FUNC', TD_PATH ."includes/functions/func_" );
define( 'TD_SRC', TD_PATH ."sources/" );
define( 'TD_SKIN', TD_PATH ."skins/" );
define( 'TD_ADMIN', TD_PATH ."admin/" );

#=============================
# Main Class
#=============================

require_once TD_INC . "trellis.php";
require_once TD_INC . "trellis_admin.php";
$trellis = new trellis_admin();
$trellis->initialize();

#=============================
# Other Junk
#=============================

$choice = array(
                'admin'        => array(
                                     'about',
                                     'home',
                                     'settings',
                                    ),

                'manage'    => array(
                                     'articles',
                                     'categories',
                                     'cdfields',
                                     'cpfields',
                                     'departs',
                                     'flags',
                                     'groups',
                                     'news',
                                     'pages',
                                     'priorities',
                                     'rtemplates',
                                     'statuses',
                                     'tickets',
                                     'users',
                                    ),

                'look'        => array(
                                     'emails',
                                     'langs',
                                     'skins',
                                    ),

                'tools'        => array(
                                     'backup',
                                     'cache',
                                     'logs',
                                     'maint',
                                     'settings',
                                     'stats',
                                    ),
               );

#=============================
# Require & Run
#=============================

$folder = $trellis->input['section'];

if ( $folder && in_array( $trellis->input['page'], $choice[ $folder ] ) ) $required = $trellis->input['page'];

if ( ! isset( $required ) )
{
    if ( $trellis->input['section'] == 'manage' )
    {
        $folder = 'manage';
        $required = 'tickets';
    }
    elseif ( $trellis->input['section'] == 'look' )
    {
        $folder = 'look';
        $required = 'skins';
    }
    elseif ( $trellis->input['section'] == 'tools' )
    {
        $folder = 'tools';
        $required = 'settings';
    }
    else
    {
        $folder = 'admin';
        $required = 'home';
    }
}

$required = "ad_". $required;

require_once TD_ADMIN . $folder ."/". $required .".php";

$required = 'td_'.  $required;

$run = new $required();
$run->trellis =& $trellis;

$run->auto_run();

?>