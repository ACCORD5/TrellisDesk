<?php

/**
 * Trellis Desk
 *
 * @version    2.0
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

define( 'IN_TD', true );

#=============================
# Lets Play Nice With Output
#=============================

ob_start();
ob_end_clean();

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
define( 'TD_SKIN', TD_PATH ."skins/" ); # TODO: TD_LANG contstant?

#=============================
# Main Class
#=============================

require_once TD_INC ."trellis.php";
$trellis = new trellis();
$trellis->initialize();

#=============================
# Other Junk
#=============================

$choice = array(
                'kb'            => 'knowledgebase',
                'account'        => 'account',
                'feed'            => 'feed',
                'dashboard'        => 'dashboard',
                'pages'            => 'pages',
                'news'            => 'news',
                'register'        => 'register',
                'tickets'        => 'tickets',
               );

#=============================
# Require & Run
#=============================

$required = $choice[ $trellis->input['page'] ];

if ( ! isset( $required ) ) $required = 'dashboard';

require_once TD_SRC . $required .".php";

$required_class = 'td_source_'. $required;

$run = new $required_class();
$run->trellis =& $trellis;

$run->auto_run();

?>