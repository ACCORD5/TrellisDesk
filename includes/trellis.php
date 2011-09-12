<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class trellis {

    public $version_name = '2.0 Alpha 4';
    public $version_short = '2.0 a4';
    public $version_number = '20014000';
    public $config = array();
    public $lang = array();
    private $htmlpurifier;
    private $htmlp_configs;
    private $htmlp_purifiers;

    #=======================================
    # @ Constructor
    #=======================================

    function __construct()
    {
        #=============================
        # Start Execution Timer
        #=============================

        $this->start_timer();

        #=============================
        # Load Config
        #=============================

        if ( ! file_exists( TD_PATH .'config.php' ) ) header('Location: install/');

        require_once TD_PATH .'config.php';

        define( 'TD_DEBUG', $config['debug_level'] );

        $this->config = $config;

        #=============================
        # Error Handler
        #=============================

        require_once TD_CLASS .'error_handler.php';

        if ( TD_DEBUG )
        {
            ini_set( 'display_errors', 1 );
            error_reporting( E_ALL | E_NOTICE );
        }

        $this->error = new td_class_error_handler( TD_DEBUG );

        if ( $this->config['logs_path'] ) $this->error->set_log_path( $this->config['logs_path'] .'error.log' );

        #=============================
        # Load Cache
        #=============================

        require_once TD_CLASS .'cache.php';

        $this->cache = new td_class_cache( $this->config['cache_path'] .'trellis/', $this->config['flatfile_key'] );

        #=============================
        # Load Database
        #=============================

        require_once TD_CLASS .'mysql.php';

        $this->db = new td_class_db_mysql( array( 'host' => $this->config['db_host'], 'port' => $this->config['db_port'], 'user' => $this->config['db_user'], 'pass' => $this->config['db_pass'], 'name' => $this->config['db_name'], 'prefix' => $this->config['db_prefix'], 'shutdown_queries' => $this->config['db_shutdown_queries'] ) );

        #=============================
        # Load Templates
        #=============================

        require_once TD_FUNC .'template.php';
    }

    #=======================================
    # @ Initialize
    #=======================================

    public function initialize()
    {
        #=============================
        # Get Incoming Data
        #=============================

        $this->input = $this->get_post();

        #=============================
        # Antispam
        #=============================

        # FIXME: Should not have to do this.  Allow plugin system to specify if session is requried, etc.

        if ( $this->input['page'] == 'antispam' )
        {
            $this->load_antispam();

            $antispam_func = $this->input['act'];

            $this->antispam->$antispam_func();

            exit();
        }

        #=============================
        # Check Cache
        #=============================

        $this->check_cache();

        #=============================
        # Check Tacks
        #=============================

        $this->check_tasks();

        #=============================
        # Load Session
        #=============================

        require_once TD_CLASS .'session.php';

        $this->session = new td_class_session();
        $this->session->trellis = &$this;

        if ( $this->input['do_login'] )
        {
            $this->user = $this->session->do_login();
        }
        elseif ( $this->input['do_glogin'] )
        {
            $this->user = $this->session->do_guest_login();
        }
        else
        {
            $this->user = $this->session->load_session();
        }

        #=============================
        # Logout
        #=============================

        if ( $this->input['act'] == 'logout' )
        {
            $this->user = $this->session->do_logout();
        }

        #=============================
        # Load Skin
        #=============================

        $this->load_skin();
    }

    #=======================================
    # @ Shut Down
    #=======================================

    public function shut_down()
    {
        #=============================
        # Update Session
        #=============================

        register_shutdown_function( array( $this->session, 'update_session' ) ); // #TODO: must be first so that pending system messages are written to database and ready before redirect completes. even as a shutdown function it might be too slow?

        #=============================
        # Send Emails
        #=============================

        if ( $this->email ) register_shutdown_function( array( $this->email, 'send_emails' ) );

        #=============================
        # Run Queries and Write Cache
        #=============================

        register_shutdown_function( array( $this->db, 'shut_down' ) );

        register_shutdown_function( array( $this->cache, 'shut_down' ) );
    }

    #=======================================
    # @ Load Functions
    # Loads the defined functions file.
    #=======================================

    function load_functions($nameb)
    {
        if ( ! is_array( $nameb ) )
        {
            $nameb = array( $nameb );
        }

        while( list( , $name ) = each( $nameb ) )
        {
            if( ! $this->func->$name )
            {
                if ( file_exists( TD_FUNC . $name .'.php' ) )
                {
                    require_once TD_FUNC . $name .'.php';

                    $class_name = 'td_func_'. $name;

                    $this->func->$name = new $class_name();
                    $this->func->$name->trellis = &$this;
                }
                else
                {
                    trigger_error( "Core - Functions file not found: ". $name, E_USER_ERROR );
                }
            }
        }
    }

    #=======================================
    # @ Load Language
    # Loads the defined language file.
    #=======================================

    function load_lang($name)
    {
        if ( ! $this->user['lang'] )
        {
            if ( ! $this->user['lang'] = $this->cache->data['misc']['default_lang'] )
            {
                $this->user['lang'] = $this->config['fallback_lang'];
            }
        }

        require_once TD_PATH .'languages/'. $this->cache->data['langs'][ $this->user['lang'] ]['key'] .'/lang_'. $name .'.php';

        $this->add_lang($lang);
    }

    #=======================================
    # @ Add Language
    #=======================================

    function add_lang($lang)
    {
        $this->lang = array_merge( (array)$lang , (array)$this->lang );
    }

    #=======================================
    # @ Load Skin
    #=======================================

    public function load_skin()
    {
        require_once TD_CLASS .'skin.php';

        $this->skin = new td_class_skin( $this );
    }

    #=======================================
    # @ Load Email
    #=======================================

    function load_email($test=false)
    {
        if ( ! $this->email )
        {
            require_once TD_CLASS .'email.php';

            $config = array(
                            'transport'            => $this->cache->data['settings']['email']['transport'],
                            'smtp_host'            => $this->cache->data['settings']['email']['smtp_host'],
                            'smtp_port'            => $this->cache->data['settings']['email']['smtp_port'],
                            'smtp_encryption'    => $this->cache->data['settings']['email']['smtp_encryption'],
                            'smtp_user'            => $this->cache->data['settings']['email']['smtp_user'],
                            'smtp_pass'            => $this->cache->data['settings']['email']['smtp_pass'],
                            'smtp_timeout'        => $this->cache->data['settings']['email']['smtp_timeout'],
                            'sendmail_command'    => $this->cache->data['settings']['email']['sendmail_command'],
                            );

            foreach ( $config as $id => $c )
            {
                if ( $id != 'transport' || $id != 'smtp_encryption' ) $config[ $id ] = $this->prepare_output( $c, array( 'html' => 1, 'entity' => 1 ) );
            }

            if ( $test ) $config['test'] = $test;

            $this->email = new td_class_email( $this, $config );
        }
    }

    #=======================================
    # @ Load Anti-Spam
    #=======================================

    function load_antispam()
    {
        if ( ! file_exists( TD_CLASS . $this->cache->data['settings']['antispam']['method'] .'.php' ) ) trigger_error( "Anti-Spam - Class file not found: ". $this->cache->data['settings']['antispam']['method'], E_USER_WARNING );

        require_once TD_CLASS . $this->cache->data['settings']['antispam']['method'] .'.php';

        $class = 'td_class_'. $this->cache->data['settings']['antispam']['method'];

        $this->antispam = new $class( $this, $this->cache->data['settings']['antispam'] );
    }

    #=======================================
    # @ Time Functions
    # Tick Tock. :P
    #=======================================

       function start_timer()
    {
        $temp_time = explode(" ", microtime() );

        $this->start_time = $temp_time[1] + $temp_time[0];
    }

    function end_timer()
    {
        $temp_time = explode(" ", microtime() );

        $this->end_time = $temp_time[1] + $temp_time[0];

        return round( ( $this->end_time - $this->start_time ) ,5 );
    }

    #=======================================
    # @ Get Post
    # Combines incoming $_GET and $_POST
    # plus sanitizes data.
    #=======================================

    function get_post()
    {
        $data = array();

        #=============================
        # $_GET Data
        #=============================

        if ( is_array( $_GET ) )
        {
            while ( list( $n, $v ) = each( $_GET ) )
            {
                if ( is_array( $_GET[$n] ) )
                {
                    while ( list( $n2, $v2 ) = each( $_GET[$n] ) )
                    {
                          $data[ $this->sanitize_data($n)][ $this->sanitize_data($n2) ] = $this->sanitize_data($v2);
                    }
                }
                else
                {
                    $data[ $this->sanitize_data($n) ] = $this->sanitize_data($v);
                }
            }
        }

        #=============================
        # $_POST Data
        #=============================

        if ( is_array( $_POST ) )
        {
            while ( list( $n, $v ) = each( $_POST ) )
            {
                if ( is_array( $_POST[$n] ) )
                {
                    while ( list( $n2, $v2 ) = each( $_POST[$n] ) )
                    {
                          $data[ $this->sanitize_data($n) ][ $this->sanitize_data($n2) ] = $this->sanitize_data($v2);
                    }
                }
                else
                {
                    $data[ $this->sanitize_data($n) ] = $this->sanitize_data($v);
                }
            }
        }

        #=============================
        # Other Junk
        #=============================

        $data['ip_address'] = $this->sanitize_data( $_SERVER['REMOTE_ADDR'] );

        return $data;
    }

    #=======================================
    # @ Sanitize Data
    # Cleans incoming data (HTML Characters,
    # backslashes, etc).
    #=======================================

    function sanitize_data($data, $noquotes=0, $notrim=0)
    {
        if ( ! $data ) return false;

        if ( $noquotes )
        {
            if ( get_magic_quotes_gpc() )
            {
                $data = htmlentities( $data, ENT_COMPAT, 'UTF-8' );
            }
            else
            {
                $data = htmlentities( addslashes( $data ), ENT_COMPAT, 'UTF-8' );
            }
        }
        else
        {
            if ( get_magic_quotes_gpc() )
            {
                $data = stripslashes( htmlentities( $data, ENT_QUOTES, 'UTF-8' ) );
            }
            else
            {
                $data = htmlentities( $data, ENT_QUOTES, 'UTF-8' );
            }
        }

        if ( ! $notrim ) $data = trim( $data );

        // Unicode
        #$data = preg_replace( "/&amp;#([0-9]+);/s", "&#\\1;", $data );

        return $data;
    }

    #=======================================
    # @ Validate Email
    # Checks to make sure the supplied email
    # address is valid.
    #=======================================

    function validate_email($email)
    {
        if ( preg_match( "/^([0-9a-zA-Z])([0-9a-zA-Z,\.,_,\-,\+]+)[@]([0-9a-zA-Z])([0-9a-zA-Z,\.,_,\-]+)[.]([0-9a-zA-Z]{2})([0-9a-zA-Z]*)$/", $email ) )
        {
            return $email;
        }

        return false;
    }

    #=======================================
    # @ Shorten String
    # Takes a string and shortens it to the
    # specified length.
    #=======================================

    function shorten_str($txt, $length, $add=1)
    {
        if ( strlen( $txt ) > $length )
        {
            if ( $add == 1 )
            {
                $txt = substr( $txt, 0, ( $length - 3) ) . "...";
                $txt = preg_replace( "/&(#(\d+;?)?)?\.\.\.$/", "...", $txt );
            }
            else
            {
                $txt = substr( $txt, 0, $length );
            }
        }

        return $txt;
    }

    #=======================================
    # @ Convert HTML
    # Converts HTML tags.
    #=======================================

    function convert_html($txt)
    {
        /*$txt = str_replace( '&amp;', '&', $txt );
        $txt = str_replace( '&#039;', '\'', $txt );
        $txt = str_replace( '&#39;', '\'', $txt );
        $txt = str_replace( '&quot;', '"', $txt );
        $txt = str_replace( '&lt;', '<', $txt );
        $txt = str_replace( '&gt;', '>', $txt );
        $txt = str_replace( '&#40;', '(', $txt );
        $txt = str_replace( '&#41;', ')', $txt );*/

        $txt = html_entity_decode( $txt, ENT_QUOTES, 'UTF-8' );

        return $txt;
    }

    #=======================================
    # @ Prepare Email
    #=======================================

    function prepare_email($data, $source_html, $type='html')
    {
        # CHECK: this is just a mess =/

        if ( $type == 'html' )
        {
            $po_params = array( 'linkify' => 1 );

            if ( $source_html )
            {
                $po_params['html'] = 1;
            }
            else
            {
                $po_params['nl2br'] = 1;
                $po_params['paragraphs'] = 1; # CHECK: added this because i added it below... ?
            }
        }
        elseif ( $type == 'plain' )
        {
            $po_params = array();

            if ( $source_html )
            {
                $data = $this->convert_html( $data ); # TODO: convert b, li, etc tags for email

                $data = str_replace( "\r\n", "", $data ); # TODO: does \n do it? or do we need to check \r, etc? see right below as well
            }
            else
            {
                $po_params['nl2br'] = 1;
                $po_params['paragraphs'] = 1;
            }
        }

        $data = $this->prepare_output( $data, $po_params );

        if ( $type == 'plain' )
        {
            $data = str_replace( "\n", "", $data ); # TODO: why only \n? not sure but it works! see below as well
            $data = preg_replace( '/\<br(\s*)?\/?\>/i', "\n", $data );
            $data = preg_replace( '/\<p(\s*)?\>/i', "", $data );
            $data = str_replace( '</p>', "\n\n", $data );
            $data = preg_replace( '/\n\n$/', "", $data );
            $data = strip_tags( $data );
            $data = $this->convert_html( $data );
            $data = preg_replace( "/\p{Zl}/u", "", $data ); # CHECK: Gets rid of unicode U+2028 line separator character. I think HTMLPurifier is adding this and its making the plaintext emails have extra line breaks.
        }

        return $data;
    }

    #=======================================
    # @ Prepare Output
    #=======================================

    function prepare_output($data, $params=array())
    {
        # FIXME: HTMLPurifier could have some performance / memory issues.  Can we cache our of configs so we don't have so many config objects?  Same with purifier objects? CONFIGS NOW CACHED. 50% PERFORMANCE IMPROVEMENT. :)

        if ( ! $this->htmlpurifier ) require_once TD_INC .'htmlpurifier/HTMLPurifier.standalone.php';

        $key = base64_encode( serialize( array( 'html' => $params['html'], 'paragraphs' => $params['paragraphs'], 'linkify' => $params['linkify'] ) ) );

        if ( ! $this->htmlp_configs[ $key ] )
        {
            $config = HTMLPurifier_Config::createDefault();

            $config->set( 'Cache.SerializerPath', $this->config['cache_path'] .'htmlpurifier/' );
            $config->set( 'Output.Newline', "\n" );

            if ( ! $params['html'] )
            {
                if ( $params['paragraphs'] ) $config->set( 'AutoFormat.AutoParagraph', true );

                $config->set( 'HTML.Allowed', 'p,br,a[href]' );

                if ( ! $params['linkify'] ) $config->set( 'AutoFormat.DisplayLinkURI', true );
            }

            if ( $params['linkify'] ) $config->set( 'AutoFormat.Linkify', true );

            $this->htmlp_configs[ $key ] = $config;
        }

        if ( $params['html'] )
        {
            $data = $this->convert_html( $data );
        }

        $purifier = new HTMLPurifier( $this->htmlp_configs[ $key ] );

        $data = $purifier->purify( $data );

        if ( ! $params['html'] && $params['nl2br'] )
        {
            $data = str_replace( "\n\n", "", $data );

            $data = nl2br( $data );
        }

        return $data;
    }

    #=======================================
    # @ Check Cache
    # Checks to make sure all cache files
    # are present.  If they aren't, then
    # the cache is rebuilt.
    #=======================================

    public function check_cache()
    {
        $check = array( 'settings', 'departs', 'categories', 'groups', 'news', 'langs', 'skins', 'pfields', 'dfields', 'rtemplates', 'priorities', 'staff', 'flags', 'statuses', 'misc' );
        $to_cache = array();

        foreach ( $check as $ck )
        {
            if ( ! is_array( $this->cache->data[ $ck ] ) ) $to_cache[] = $ck;
        }

        if ( ! empty( $to_cache ) )
        {
            $this->load_functions( 'rebuild' );

            foreach( $to_cache as $cache )
            {
                $func_name = $cache .'_cache';

                $this->func->rebuild->$func_name();
            }
        }
    }

    #=======================================
    # @ Check / Run Tasks
    # Checks to see if any tasks need to be
    # run and runs them if so.
    #=======================================

    function check_tasks()
    {
        # TODO: move to cron!

        /*if ( $this->cache->data['tasks']['close_auto'] < time() - ( 60 * 60 ) )
        {
            $this->check_close_auto();
        }*/
    }

    #=======================================
    # @ Check Fields
    # Checks input fields for the required
    # length defined in array.
    #=======================================

    function check_fields($required)
    {
        while ( list( $req, $length ) = each( $required ) )
        {
            if ( $length )
            {
                if ( strlen( $this->input[ $req ] ) < $length )
                {
                    $this->skin->error('fill_form_lengths');
                }
            }
            else
            {
                if ( ! $this->input[ $req ] )
                {
                    $this->skin->error('fill_form_completely');
                }
            }
        }
    }

    #=======================================
    # @ TD Date
    # Applies format and offsets to date.
    #=======================================

    function td_timestamp($params=array())
    {
        $return = '';

        #=============================
        # Relative
        #=============================

        if ( $params['format'] == 'relative' )
        {
            $time_diff = time() - $params['time'];

            if ( ! $params['cutoff'] || $time_diff < $params['cutoff'] )
            {
                if ( $time_diff < 60 )
                {
                    return $this->lang['less_than_a_minute_ago'];
                }
                elseif ( $time_diff < 3600 )
                {
                    return floor( $time_diff / 60 ) .' '. $this->lang['minutes_ago'];
                }
                elseif ( $time_diff < 86400 )
                {
                    return floor( $time_diff / 3600 ) .' '. $this->lang['hours_ago'];
                }
                elseif ( $time_diff < 604800 )
                {
                    return floor( $time_diff / 86400 ) .' '. $this->lang['days_ago'];
                }
                elseif ( $time_diff < 2629743 )
                {
                    return floor( $time_diff / 604800 ) .' '. $this->lang['weeks_ago'];
                }
                else
                {
                    return floor( $time_diff / 2629743 ) .' '. $this->lang['months_ago'];
                }
            }
        }

        #=============================
        # Human Friendly
        #=============================

        if ( $params['human'] )
        {
            $today = mktime( 0, 0, 0, date('n'), date('j'), date('Y') );

            $today_diff = $today - $params['time'];

            if ( $today_diff < ( 60 * 60 * 24 ) )
            {
                $return .= $this->lang['today'];
            }
            elseif ( $today_diff < ( 60 * 60 * 48 ) )
            {
                $return .= $this->lang['yesterday'];
            }
        }

        #=============================
        # Time Adjustments
        #=============================

        if ( ! isset( $params['time_zone'] ) ) $params['time_zone'] = $this->user['time_zone'];
        if ( ! isset( $params['dst'] ) ) $params['dst'] = $this->user['time_dst'];

        $params['time'] += ( $this->cache->data['settings']['general']['time_offset'] * 60 );  // General Configuration Offset

        if ( $params['offset'] ) $params['time'] += ( $params['offset'] );  // Specific

        $params['time'] += ( $params['time_zone'] * 60 * 60 ); // User Time Zone

        if ( $params['dst'] == 1 ) // User DST
        {
            $params['time'] += ( 60 * 60 );
        }
        elseif ( $params['dst'] == 2 )
        {
            if ( gmdate('I') ) $params['time'] += ( 60 * 60 );
        }

        #=============================
        # Continue Human Friendly
        #=============================

        if ( $params['human'] )
        {
            if ( $today_diff < ( 60 * 60 * 48 ) ) return $return .', '. gmdate( $this->cache->data['settings']['general']['tformat_time'], $params['time'] );
        }

        #=============================
        # Format
        #=============================

        if ( $params['format'] == 'short' )
        {
            $params['format'] = $this->cache->data['settings']['general']['tformat_short'];
        }
        elseif ( $params['format'] == 'long' )
        {
            $params['format'] = $this->cache->data['settings']['general']['tformat_long'];
        }
        elseif ( $params['format'] == 'date' )
        {
            $params['format'] = $this->cache->data['settings']['general']['tformat_date'];
        }
        elseif ( $params['format'] == 'time' )
        {
            $params['format'] = $this->cache->data['settings']['general']['tformat_time'];
        }
        elseif ( ! $params['format'] )
        {
            $params['format'] = $this->cache->data['settings']['general']['tformat_short'];
        }

        return gmdate( $params['format'], $params['time'] );
    }

    #=======================================
    # @ Set Cookie
    # Sets a cookie. :P
    #=======================================

    function set_cookie($name, $value, $time='')
    {
        if ( ! $time )
        {
            $time = time() + 60*60*24*365; // Sec*Min*Hrs*Days
        }

        if ( $this->cache->data['settings']['general']['cookie_prefix'] ) $name = $this->cache->data['settings']['general']['cookie_prefix'] . $name;

        @setcookie( $name, $value, $time, $this->cache->data['settings']['general']['cookie_path'], $this->cache->data['settings']['general']['cookie_domain'] );
    }

    #=======================================
    # @ Get Cookie
    # Safely gets a cookie.
    #=======================================

    function get_cookie($name)
    {
        $cookie_data = ""; // Initialize for Security

        if ( $this->cache->data['settings']['general']['cookie_prefix'] ) $name = $this->cache->data['settings']['general']['cookie_prefix'] . $name;

        if ( isset( $_COOKIE[$name] ) )
        {
            $cookie_data = $this->sanitize_data( $_COOKIE[$name] );
        }

        return $cookie_data;
    }

    #=======================================
    # @ Delete Cookie
    # Deletes a cookie. :P
    #=======================================

    function delete_cookie($name)
    {
        @setcookie( $name, 0, time() - ( 60*60*24*365 ), $this->cache->data['settings']['general']['cookie_path'], $this->cache->data['settings']['general']['cookie_domain'] );
    }

    #=======================================
    # @ Log
    # Log an action into the database.
    #=======================================

    function log($params=array())
    {
        if ( ! $this->cache->data['settings']['log']['enable'] ) return true;

        if ( IN_TDA === true )
        {
            if ( ! $this->cache->data['settings']['log']['acp'] ) return true;
        }
        else
        {
            if ( ! $this->cache->data['settings']['log']['nonacp'] ) return true;
        }

        if ( ! $params['msg'] ) return false;
        if ( ! $params['type'] ) return false;

        if ( ! $this->cache->data['settings']['log'][ $params['type'] ] ) return true;

        if ( ! $params['level'] ) $params['level'] = 1;

        if ( is_array( $params['msg'] ) )
        {
            $action = vsprintf( $this->lang[ 'log_'. array_shift( $params['msg'] ) ], $params['msg'] );
        }
        else
        {
            $action = ( $this->lang[ 'log_'. $params['msg'] ] ) ? $this->lang[ 'log_'. $params['msg'] ] : $params['msg'];
        }

        $db_array = array(
                          'uid'                => $this->user['id'],
                          'action'            => $action,
                          'type'            => $params['type'],
                          'level'            => $params['level'],
                          'content_type'    => $params['content_type'],
                          'content_id'        => $params['content_id'],
                          'admin'            => ( ( IN_TDA === true ) ? 1 : 0 ),
                          'date'            => time(),
                          'ipadd'            => $this->input['ip_address'],
                         );

        if ( $params['extra'] ) $db_array['extra'] = serialize( $params['extra'] );
        if ( $params['uid'] ) $db_array['uid'] = $params['uid'];

        $this->db->construct( array(
                                            'insert'    => 'logs',
                                          'set'        => $db_array,
                                     )      );

        $this->db->next_shutdown();
        $this->db->execute();
    }

    #=======================================
    # @ Process Data
    #=======================================

    function process_data($fields, $data)
    {
        $return = array();

        if ( is_array( $fields ) && is_array( $data ) )
        {
            while( list( $name, $type ) = each( $fields ) )
            {
                if ( array_key_exists( $name, $data ) )
                {
                    if ( $type == 'int' )
                    {
                        $return[ $name ] = intval( $data[ $name ] );
                    }
                    elseif ( $type == 'serialize' )
                    {
                        $return[ $name ] = serialize( $data[ $name ] );
                    }
                    elseif ( $type == 'string' )
                    {
                        $return[ $name ] = $data[ $name ];
                    }
                }
            }
        }

        return $return;
    }

    #=======================================
    # @ Page Links
    #=======================================

    public function page_links($params=array())
    {
        # TODO: convert to smarty custom function (ex: {pagination text="{$lang['pagination']}" list=false} list=false by default so omit. send current page / total via source code prior ($this->set_pagination_total_items = 100, etc)

        if ( ! $params['start'] ) $params['start'] = $this->input['st'];

        if ( ! $params['start'] ) $params['start'] = 0;

        if ( $params['total'] > $params['per_page'] )
        {
            $total_pages = ceil( $params['total'] / $params['per_page'] );
        }
        else
        {
               $total_pages = 1;
        }

        $current_page = ( $params['start'] / $params['per_page'] ) + 1;

        $html = sprintf( $this->lang['pagination'], $current_page, $total_pages ) .' &nbsp; ';

        if ( $total_pages > 5 ) $over = 1;

        // Display First Page Link
        if ( $current_page > 3 && $total_pages > 5 )
        {
            if ( ( $current_page - 3 ) == 1 )
            {
                $html .= '<a href="'. $params['url'] .'&amp;st=0">1</a>&nbsp;';
            }
            else
            {
                $html .= '<a href="'. $params['url'] .'&amp;st=0">1...</a>&nbsp;';
            }
        }

        // Show Page Numbers
        for ( $i = 1; $i <= $total_pages; $i++ )
        {
            if ( $i != $current_page )
            {
                if ( $over )
                {
                    if ( $i >= ( $current_page - 2 ) && $i <= ( $current_page + 2 ) )
                    {
                        $html .= '<a href="'. $params['url'] .'&amp;st='. ( $params['per_page'] * ( $i - 1 ) ) .'">'. $i .'</a>&nbsp;';
                    }
                }
                else
                {
                    $html .= '<a href="'. $params['url'] .'&amp;st='. ( $params['per_page'] * ( $i - 1 ) ) .'">'. $i .'</a>&nbsp;';
                }
            }
            else
            {
                $html .= '<a href="'. $params['url'] .'&amp;st='. $params['start'] .'" class="thispage">'. $i .'</a>&nbsp;';
            }
        }

        // Display Last Page Link
        if ( ( $current_page + 2 ) < $total_pages && $total_pages > 5 )
        {
            if ( ( $current_page + 3 ) == $total_pages )
            {
                $html .= '<a href="'. $params['url'] .'&amp;st='. ( $params['per_page'] * ( ( $i - 1 ) - 1 ) ) .'">'. $total_pages .'</a>&nbsp;';
            }
            else
            {
                $html .= '<a href="'. $params['url'] .'&amp;st='. ( $params['per_page'] * ( ( $i - 1 ) - 1 ) ) .'">...'. $total_pages .'</a>&nbsp;';
            }
        }

        return $html;
    }

    #=======================================
    # @ startElement
    # Start element handler.
    #=======================================

    function startElement($parser, $name, $attr)
    {
        $this->xml_current_element = $name;

        $my_var = 'xml_'. $name;
        $this->$my_var = "";

        if( strcmp( $name, "language_pack" ) == 0 )
        {
            $this->xml_lang_abbr = base64_decode( preg_replace( "/\s/", "", $attr["abbr"] ) );
            $this->xml_lang_name = base64_decode( preg_replace( "/\s/", "", $attr["name"] ) );
        }

        if( strcmp( $name, "lang_file" ) == 0 )
        {
            $this->xml_lang_file = $attr["name"];
        }
    }

    #=======================================
    # @ endElement
    # End element handler.
    #=======================================

    function endElement($parser, $name)
    {
        $elements = array( 'lang_file', 'lang_key', 'lang_replace' );

        if( strcmp( $name, "lang_bit" ) == 0 )
        {
            while ( list( , $element ) = each( $elements ) )
            {
                $my_var = 'xml_'. $element;
                $temp[ $element ] = base64_decode( preg_replace( "/\s/", "", $this->$my_var ) );
            }

            $this->xml_lang_bits[] = $temp;

            $this->xml_lang_key = "";
            $this->xml_lang_replace = "";
        }

        if( strcmp( $name, "lang_file" ) == 0 )
        {
            #$this->xml_lang_file = "";
        }
    }

    #=======================================
    # @ characterData
    # Character data handler.
    #=======================================

    function characterData($parser, $data)
    {
        $elements = array( 'lang_key', 'lang_replace' );

        while ( list( , $element ) = each( $elements ) )
        {
            if( $this->xml_current_element == $element )
            {
                $my_var = 'xml_'. $element;
                #$data = trim($data);
                $this->$my_var .= $data;
            }
        }
    }

    #=======================================
    # @ startElementB
    # Start element handler.
    #=======================================

    function startElementB($parser, $name, $attr)
    {
        $this->xml_current_element = $name;

        /*if( strcmp( $name, "skin_file" ) == 0 )
        {
            $this->xml_skin_file = base64_decode( $attr["name"] );
        }*/

        $my_var = 'xml_'. $name;
        $this->$my_var = "";
    }

    #=======================================
    # @ endElementB
    # End element handler.
    #=======================================

    function endElementB($parser, $name)
    {
        $elements = array( 'tname', 'tcontent' );
        $elementsb = array( 'sk_name', 'sk_img_dir', 'sk_author', 'sk_author_email', 'sk_author_web', 'sk_notes', 'sk_css' );
        $elementsc = array( 'filename', 'content', 'path' );

        if( strcmp( $name, "template" ) == 0 )
        {
            while ( list( , $element ) = each( $elements ) )
            {
                $my_var = 'xml_'. $element;
                $temp[ $element ] = base64_decode( preg_replace( "/\s/", "", $this->$my_var ) );
            }

            $this->xml_templates[] = $temp;

            $this->xml_tname = "";
            $this->xml_tcontent = "";
        }

        if( strcmp( $name, "image" ) == 0 )
        {
            while ( list( , $element ) = each( $elementsc ) )
            {
                $my_var = 'xml_'. $element;
                $tempb[ $element ] = base64_decode( preg_replace( "/\s/", "", $this->$my_var ) );
            }

            $this->xml_skin_images[] = $tempb;

            $this->xml_filename = "";
            $this->xml_content = "";
            $this->xml_path = "";
        }

        if( strcmp( $name, "skin_info" ) == 0 )
        {
            while ( list( , $element ) = each( $elementsb ) )
            {
                $my_var = 'xml_'. $element;
                $this->xml_skin_info[ $element ] = base64_decode( preg_replace( "/\s/", "", $this->$my_var ) );
            }
        }
    }

    #=======================================
    # @ characterDataB
    # Character data handler.
    #=======================================

    function characterDataB($parser, $data)
    {
        $elements = array( 'tname', 'tcontent', 'sk_name', 'sk_img_dir', 'sk_author', 'sk_author_email', 'sk_author_web', 'sk_notes', 'sk_css', 'filename', 'content', 'path' );

        while ( list( , $element ) = each( $elements ) )
        {
            if( $this->xml_current_element == $element )
            {
                $my_var = 'xml_'. $element;
                #$data = trim($data);
                $this->$my_var .= $data;
            }
        }
    }

    #=======================================
    # @ parseFile
    # Finally, lets parse the XML file.
    #=======================================

    function parseFile($xml_file, $type=1)
    {
        $xml_parser = xml_parser_create();

        xml_set_object( $xml_parser, $this );

        if ( $type == 1 )
        {
            xml_set_element_handler($xml_parser, "startElement", "endElement");
            xml_set_character_data_handler($xml_parser, "characterData");
        }
        elseif ( $type == 2 )
        {
            xml_set_element_handler($xml_parser, "startElementB", "endElementB");
            xml_set_character_data_handler($xml_parser, "characterDataB");
        }

        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);

        if( !( $fp = fopen( $xml_file, "r" ) ) )
        {
            die("Cannot open ". $xml_file);
        }

        while( ( $data = fread( $fp, 4096 ) ) )
        {
            if( !xml_parse( $xml_parser, $data, feof($fp) ) )
            {
                die( sprintf("XML error at line %d column %d ", xml_get_current_line_number($xml_parser), xml_get_current_column_number($xml_parser) ) );
            }
        }

        xml_parser_free($xml_parser);

        if ( $type == 1 )
        {
            return $this->xml_lang_bits;
        }
        else
        {
            return array( $this->xml_skin_info, $this->xml_templates, $this->xml_skin_images );
        }
    }

    #=======================================
    # @ Implode Keys
    # Similar to implode() function except
    # uses keys instead of values
    #=======================================

    function implode_keys($glue, $array)
    {
        foreach( $array as $key => $value )
        {
            $ret[] = $key;
        }

        return implode($glue, $ret);
    }

    #=======================================
    # @ Create Token
    # Create form token hash.
    #=======================================

    function create_token($type)
    {
        if ( $this->cache->data['settings']['security']['form_tokens'] )
        {
            $token = strrev( md5( 't'. uniqid( rand(), true ) ) );

            $db_array = array(
                              'token'    => $token,
                              'type'    => $type,
                              'ipadd'    => $this->input['ip_address'],
                              'date'    => time(),
                             );

            $this->db->construct( array(
                                                'insert'    => 'tokens',
                                              'set'        => $db_array,
                                         )      );

            $this->db->next_shutdown();
            $this->db->execute();

            return "<input type='hidden' name='token' value='{$token}' />";
        }
        else
        {
            return "";
        }
    }

    #=======================================
    # @ Check Ticket Auto Close
    # Check tickets for ones that need to
    # be auto closed.
    #=======================================

    function check_close_auto()
    {
        $this->db->construct( array(
                                            'select'    => array( 'id', 'uid' ),
                                            'from'    => 'tickets',
                                            'where'    => array( array( 'close_date', '<=', time() ), array( 'close_date', '!=', 0, 'and' ), array( 'status', '=', 4, 'and' ) ),
                                    )     );

        $this->db->execute();

        if ( $this->db->get_num_rows() )
        {
            while( $t = $this->db->fetch_row() )
            {
                $users[ $t['uid'] ] ++;

                $tickets[] = $t['id'];
            }

            $this->db->construct( array(
                                                'update'    => 'tickets',
                                                'set'        => array( 'close_reason' => 'No response from customer.', 'status' => 6 ),
                                                'where'    => array( 'id', 'in', $tickets ),
                                        )     );

            $this->db->next_shutdown();
            $this->db->execute();

            while( list( $uid, $mtickets ) = each( $users ) )
            {
                $this->db->next_no_quotes('set');

                $this->db->construct( array(
                                                    'update'    => 'users',
                                                    'set'        => array( 'open_tickets' => 'open_tickets-'. $mtickets ),
                                                     'where'    => "id = '". $uid ."'",
                                                     'limit'    => array( 1 ),
                                             )     );

                $this->db->next_shutdown();
                $this->db->execute();
            }

            $this->r_ticket_stats(1);
        }

        $to_cache = array(); // Initialize for Security

        $to_cache['close_auto'] = time();

        $this->core->add( 'tasks', $to_cache );
    }

    #=======================================
    # @ Format Size
    # Convert size into appropriate format.
    #=======================================

    function format_size($bytes)
    {
        if ( $bytes < 1024 )
        {
            return $bytes .' '. $this->lang['size_byte'];
        }

        $kb = $bytes / 1024;

        if ( $kb < 1024 )
        {
            return round( $kb, 2 ) .' '. $this->lang['size_kilobyte'];
        }

        $mb = $kb / 1024;

        if ( $mb < 1024 )
        {
            return round( $mb, 2 ) .' '. $this->lang['size_megabyte'];
        }
    }

    #=======================================
    # @ Check To See If We Are Banned
    #=======================================

    function ban_check( $data=array() )
    {
        while ( list( $type, $value ) = each( $data ) )
        {
            if ( $type == 'username' )
            {
                return false; // TO BE COMPLETED
            }
        }

        return false;
    }

    #=======================================
    # @ Get Username
    #=======================================

    function get_username($id)
    {
        if ( ! intval( $id ) ) return false;

        if ( intval( $id ) == $this->user['id'] ) return $this->user['name'];

        $this->db->construct( array(
                                                   'select'    => array( 'name' ),
                                                   'from'        => 'users',
                                                    'where'    => array( 'id', '=', intval( $id ) ),
                                                    'limit'    => array( 0, 1 ),
                                             )     );

        $this->db->execute();

        $m = $this->db->fetch_row();

        return $m['name'];
    }

    #=======================================
    # @ Generate SQL Sort
    #=======================================

    public function generate_sql_sort( $params=array() )
    {
        $return = array();

        if ( $params['default_sort'] )
        {
            if ( ! $this->input['sort'] ) $this->input['sort'] = $params['default_sort'];
            if ( ! $this->input['order'] ) $this->input['order'] = $params['default_order'];
        }

        $gen_url = $params['base_url'];

        foreach ( $params['options'] as $id => $name )
        {
            if ( $id == $this->input['sort'] )
            {
                if ( $this->input['order'] == 'desc' )
                {
                    $link_order = 'asc';
                    $img_order = '&nbsp;<img src="<! IMG_DIR !>/arrow_down.gif" alt="{lang.down}" />';
                }
                else
                {
                    $link_order = 'desc';
                    $img_order = '&nbsp;<img src="<! IMG_DIR !>/arrow_up.gif" alt="{lang.up}" />';
                }
            }
            else
            {
                $link_order = 'asc';
                $img_order = '';
            }

            $url = $gen_url .'&amp;sort='. $id .'&amp;order='. $link_order;

            $return[ 'link_'. $id ] = '<a href="'. $url .'">'. $name . $img_order .'</a>';
        }

        $return['sort'] = $this->input['sort'];

        ( $this->input['order'] == 'desc' ) ? $return['order'] = 'DESC' : $return['order'] = 'ASC';

        return $return;
    }

    #=======================================
    # @ Send Message
    #=======================================

    public function send_message( $type, $msg )
    {
        return $this->session->add_message( $type, $msg );
    }

    #=======================================
    # @ Get Messages
    #=======================================

    public function get_messages()
    {
        return $this->session->get_messages();
    }

    #=======================================
    # @ Clear Messages
    #=======================================

    public function clear_messages()
    {
        return $this->session->clear_messages();
    }
}

?>