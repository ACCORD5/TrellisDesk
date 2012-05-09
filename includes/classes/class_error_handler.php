<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_error_handler {

    private static $log_path;

    #=======================================
    # @ Constructor
    #=======================================

    function __construct($debug=0)
    {
        define( 'TDEH_DEBUG', $debug );

        set_error_handler( array( 'td_class_error_handler', 'error_handler' ) );
    }

    #=======================================
    # @ Error Handler
    #=======================================

    public static function error_handler($errno, $errmsg, $errfile, $errline, $errcontext)
    {
        $process = false;

        if ( $errno == E_WARNING || $errno == E_USER_ERROR || TDEH_DEBUG == 3 ) $process = true;

        if ( ! $process )
        {
            if( TDEH_DEBUG == 1 )
            {
                if ( $errno == E_USER_WARNING ) $process = true;
            }
            elseif( TDEH_DEBUG == 2 )
            {
                if ( $errno == E_USER_WARNING || $errno == E_USER_NOTICE ) $process = true;
            }
        }

           if ( error_reporting() == 0 ) $process = false;

        if ( $process )
        {
            $log_msg = "[". gmdate( 'm/d/Y h:i:s P' ) ."] ";

            switch( $errno )
            {
                case E_USER_ERROR:
                    echo "<strong>TD Fatal Error:</strong> ";
                    $log_msg .= "TD Fatal Error: ";
                    break;

                case E_USER_WARNING:
                    echo "<strong>TD Warning:</strong> ";
                    $log_msg .= "TD Warning: ";
                    break;

                case E_USER_NOTICE:
                    echo "<strong>TD Notice:</strong> ";
                    $log_msg .= "TD Notice: ";
                    break;

                case E_WARNING:
                    echo "<strong>PHP Warning:</strong> ";
                    $log_msg .= "PHP Warning: ";
                    break;

                case E_NOTICE:
                    echo "<strong>PHP Notice:</strong> ";
                    $log_msg .= "PHP Notice: ";
                    break;

                case E_STRICT:
                    echo "<strong>PHP Strict:</strong> ";
                    $log_msg .= "PHP Strict: ";
                    break;

                default:
                    echo "<strong>Unknown Error (". $errno ."):</strong> ";
                    $log_msg .= "Unknown Error (". $errno ."): ";
                    break;
            }

            echo $errmsg ." in <strong>". $errfile ."</strong> on line <strong>". $errline ."</strong>";
            $log_msg .= $errmsg ." in '". $errfile ."' on line ". $errline ."\n";
            echo "<br />\n";

            ob_start();
            debug_print_backtrace();
            $trace = ob_get_contents();
            ob_end_clean();

            $trace = str_replace( '#', "\t#", $trace );

            $log_msg .= $trace;

            td_class_error_handler::log_error( $log_msg );

            if ( $errno == E_USER_ERROR ) exit(1);
        }

        return true;
    }

    #=======================================
    # @ Log Error
    #=======================================

    private function log_error($errmsg)
    {
        if ( td_class_error_handler::$log_path ) @error_log( $errmsg, 3, td_class_error_handler::$log_path );
    }

    #=======================================
    # @ Set Log Path
    #=======================================

    public function set_log_path($path)
    {
        td_class_error_handler::$log_path = $path;
    }

}

?>