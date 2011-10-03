<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

define( 'IN_TD', true );

#=============================
# Lets Play Nice With Output
#=============================

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

define( 'TD_INSTALL', "./" );
define( "TD_PATH", "../" );
define( 'TD_INC', TD_PATH ."includes/" );
define( 'TD_CLASS', TD_PATH ."includes/classes/class_" );
define( 'TD_FUNC', TD_PATH ."includes/functions/func_" );
define( 'TD_SRC', TD_PATH ."sources/" );
define( 'TD_SKIN', TD_PATH ."skin/" );

#=============================
# Main Class
#=============================

require_once TD_INC . "trellis.php";
require_once TD_INSTALL . "trellis_install.php";
$trellis = new trellis_install();

$install = new td_install( $trellis );
$install->run();

class td_install {

    private $output            = '';
    private $checks_fatal    = 0;

    #=======================================
    # @ Constructor
    #=======================================

    public function __construct(&$trellis)
    {
        $this->trellis = $trellis;
    }

    #=======================================
    # @ Run
    #=======================================

    function run()
    {
        #=============================
        # Security Checks
        #=============================

        if ( file_exists( TD_INSTALL .'install.lock' ) && $this->trellis->input['step'] != 'security' ) $this->locked();

        if ( $this->trellis->input['step'] && $this->trellis->input['step'] != 'check' )
        {
             $this->trellis->initialize();

             if ( $this->trellis->input['step'] != 'security' ) $this->prechecks();
        }

        #=============================
        # Next Step...
        #=============================

        switch ( $this->trellis->input['step'] )
        {
            case 'check':
                $this->check();
            break;
            case 1:
                $this->step_1();
            break;
            case 2:
                $this->step_2();
            break;
            case 3:
                $this->step_3();
            break;
            case 4:
                $this->step_4();
            break;
            case 5:
                $this->step_5();
            break;
            case 6:
                $this->step_6();
            break;
            case 7:
                $this->step_7();
            break;
            case 'adv':
                #$this->step_adv();
                die( 'Coming in future releases!' ); # TODO: advanced installation
            break;
            case 'install':
                $this->step_install();
            break;
            case 'security':
                $this->security();
            break;

            default:
                $this->welcome();
            break;
        }
    }

    #=======================================
    # @ Welcome
    #=======================================

    private function welcome()
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed(); // Trust me, we have to put this in for each function. Trying to include it globally is a mess.

        #=============================
        # Version Check
        #=============================

        if ( $vcheck = $this->version_check() )
        {
            if ( $vcheck > $this->trellis->version_number )
            {
                $vcheck_msg = '<strong>A more recent release of Trellis Desk is available for download. We highly recommend <a href="http://www.accord5.com/trellis/download">downloading</a> the latest version before continuing.</strong>';
            }
            else
            {
                $vcheck_msg = 'You have the latest version of Trellis Desk downloaded and ready to install.';
            }
        }
        else
        {
            $vcheck_msg = 'We were unable to check for the latest version of Trellis Desk. Please be sure that you have the latest release available before continuing.';

            $vcheck = 'Unknown';
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        <div class='acpwelcome'><img src='<! IMG_DIR !>/td_welcome_acphome.png' alt='Welcome to Trellis Desk 2' /></div>
                        ". $this->trellis->skin->group_title( 'Begin Installation' ) ."
                        ". $this->trellis->skin->group_row_with_p( '<p>Hello and welcome to the Trellis Desk Install Center. The Install Center will guide you through the Trellis Desk installation process. At any time, you can go back and make changes to previous steps by using the navigation buttons on the bottom of each page. On behalf of ACCORD5, we thank you for choosing and supporting Trellis Desk.</p><p><span class="dotunderline">Downloaded Version ID:</span> '. $this->trellis->version_number .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="dotunderline">Latest Available Version ID:</span> '. $vcheck .'</p><p>'. $vcheck_msg .'</p>' ) ."
                        ". $this->trellis->skin->formtail( '<a href="index.php?step=check" class="button">Next &raquo;</a>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 0 );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Check
    #=======================================

    private function check()
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed();

        #=============================
        # Permission Checks
        #=============================

        $checks_html = '';

        $checks_html = "<table width='100%' cellpadding='0' cellspacing='0'>";

        $checks_html .= $this->check_row_start( 'PHP Version ('. PHP_VERSION .')' );
        $checks_html .= $this->check_row_end( version_compare( PHP_VERSION, '5.2', '>=' ), array( 'fail_msg' => 'Trellis Desk cannot be installed as it requires PHP version <b>5.2.0</b> or later.' ) );

        $checks_html .= $this->check_row_start( 'Safe Mode' );
        $checks_html .= $this->check_row_end( ( ! ini_get('safe_mode') ), array( 'warn_msg' => 'Safe mode in PHP is enabled. You can continue, however this may result in unexpected behavior from Trellis Desk.' ) );

        $memory_limit_bytes = $this->return_bytes( ini_get('memory_limit') );
        $memory_limit_ini = ini_get('memory_limit');

        if ( $memory_limit_ini == '-1' )
        {
            $memory_limit = 'No Limit';
        }
        elseif ( $memory_limit_bytes )
        {
            $memory_limit = $this->format_size( $memory_limit_bytes );
        }
        else
        {
            $memory_limit = 'No Limit';
        }

        $checks_html .= $this->check_row_start( 'Memory Limit ('. $memory_limit .')' );
        $checks_html .= $this->check_row_end( ( ! ( $memory_limit_ini && $memory_limit_ini != '-1' && $memory_limit_bytes < 16777216 ) ), array( 'warn_msg' => 'Your PHP\'s memory limit is set to '. $memory_limit .'. We recommend that this value is set to 16 MB or more.' ) );

        $file_uploads = ini_get('file_uploads');

        $checks_html .= $this->check_row_start( 'File Uploads' );
        $checks_html .= $this->check_row_end( $file_uploads, array( 'warn_msg' => 'File uploads are not enabled in PHP, therefore you will not be able to use attachments in Trellis Desk.' ) );

        if ( $file_uploads )
        {
            $upload_max_filesize_bytes = $this->return_bytes( ini_get('upload_max_filesize') );
            $upload_max_filesize = $this->format_size( $upload_max_filesize_bytes );

            $checks_html .= $this->check_row_start( 'Maximum Upload Size ('. $upload_max_filesize .')' );
            $checks_html .= $this->check_row_end( ( $upload_max_filesize_bytes >= 2097152 ), array( 'warn_msg' => 'PHP\'s maximum file upload size is set to '. $upload_max_filesize .'. For your convenience, we recommend that this value is set to at least 2 MB.' ) );

            $post_max_size_bytes = $this->return_bytes( ini_get('post_max_size') );

            $checks_html .= $this->check_row_start( 'Maximum POST Size ('. $this->format_size( $post_max_size_bytes ) .')' );
            $checks_html .= $this->check_row_end( ( $post_max_size_bytes >= $upload_max_filesize_bytes ), array( 'warn_msg' => 'PHP\'s maximum POST size is less than the maximum file upload size, therefore your file uploads will be limited to '. $this->format_size( $post_max_size_bytes ) .'.' ) );
        }

        $checks_html .= "</table>
                <br />
                <div class='groupbox'>File &amp; Folder Permissions</div>
                <table width='100%' cellpadding='0' cellspacing='0'>";

        if ( ! $config_file = file_exists( TD_PATH .'config.php' ) )
        {
            if ( ! $config_file_dist = file_exists( TD_PATH .'config.php.dist' ) )
            {
                $checks_html .= $this->check_row_start( 'Configuration Filea' );
                $checks_html .= $this->check_row_end( $config_file_dist, array( 'fail_msg' => 'Trellis Desk could not locate <i>'. TD_PATH .'config.php</i>. Please upload <i>config.php.dist</i> and rename it to <i>config.php</i>.' ) );
            }
            else
            {
                if ( ! @rename( TD_PATH .'config.php.dist', TD_PATH .'config.php' ) )
                {
                    $checks_html .= $this->check_row_start( 'Configuration File' );
                    $checks_html .= $this->check_row_end( 0, array( 'fail_msg' => 'Trellis Desk coult not rename <i>'. TD_PATH .'config.php.dist</i>. Please rename <i>config.php.dist</i> to <i>config.php</i>.' ) );
                }
                else
                {
                    $config_file = true;
                }
            }
        }

        if ( $config_file )
        {
            if ( ! is_writable( TD_PATH .'config.php' ) ) @chmod( TD_PATH .'config.php', 0777 );

            $checks_html .= $this->check_row_start( 'Configuration File' );
            $checks_html .= $this->check_row_end( is_writable( TD_PATH .'config.php' ), array( 'fail_msg' => 'Trellis Desk does not have permission to write to <i>'. TD_PATH .'config.php</i>. Please CHMOD this file to 0777.' ) );
        }

        $check_perms = array(
                             array( 'title' => 'Cache Folders', 'path' => $this->trellis->config['cache_path'], 'check' => array( $this->trellis->config['cache_path'] .'htmlpurifier', $this->trellis->config['cache_path'] .'trellis' ) ),
                             array( 'title' => 'Data Folder', 'path' => $this->trellis->config['data_path'] ),
                             array( 'title' => 'Languages Folder', 'path' => TD_PATH .'languages/' ),
                             array( 'title' => 'Logs Folder', 'path' => $this->trellis->config['logs_path'] ),
                             array( 'title' => 'Skins Folder', 'path' => TD_PATH .'skins/' ),
                             array( 'title' => 'Skin Compile Folder', 'path' => $this->trellis->config['skin_compile_path'] ),
                             array( 'title' => 'Temp Folder', 'path' => $this->trellis->config['temp_path'] ),
                             );

        if ( $file_uploads ) $check_perms[] = array( 'title' => 'Uploads Folder', 'path' => $this->trellis->config['data_path'] .'uploads/' );

        foreach ( $check_perms as &$ck )
        {
            $checks_html .= $this->check_row_start( $ck['title'] );

            if ( ! is_writable( $ck['path'] ) ) @chmod( $ck['path'], 0777 );

            if ( ! $path_writeable = is_writable( $ck['path'] ) )
            {
                $checks_html .= $this->check_row_end( $path_writeable, array( 'fail_msg' => 'Trellis Desk does not have permission to write to <i>'. substr( $ck['path'], 0, -1 ) .'</i>. Please CHMOD this folder to 0777.' ) );

                continue;
            }

            $checks_failed = array();

            if ( is_array( $ck['check'] ) )
            {
                foreach ( $ck['check'] as $path )
                {
                    if ( ! is_writable( $path ) ) @chmod( $path, 0777 );

                    if ( ! is_writable( $path ) ) $checks_failed[] = $path;
                }

                if ( ! empty( $checks_failed ) )
                {
                    $checks_html .= $this->check_row_end( false, array( 'fail_msg' => 'Trellis Desk does not have permission to write to <i>'. implode( ', ', $checks_failed ) .'</i>. Please CHMOD '. ( ( count( $checks_failed ) > 1 ) ? 'these files / folders' : 'this file / folder' ) .' to 0777.' ) );
                }
            }

            if ( empty( $checks_failed ) ) $checks_html .= $this->check_row_end( true );
        }

        # TODO: check for whirlpool hash

        $checks_html .= "</table>";

        // Write to Install Cache
        if ( ! $this->checks_fatal )
        {
             $this->trellis->initialize();

             $this->trellis->cache->add( 'install', array( 'do_checks' => 1 ) );
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Installed Software &amp; Configuration' ) ."
                        ". $checks_html ."
                        ". $this->trellis->skin->formtail( '<a href="index.php" class="button">&laquo; Previous</a>&nbsp;<a href="index.php?step=check" class="button">Check Again</a>'. ( ( ! $this->checks_fatal ) ? '&nbsp;<a href="index.php?step=1" class="button">Next &raquo;</a>' : '' ) ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 'check' );
        $this->trellis->skin->set_progress_bar( 3 );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Step 1
    #=======================================

    private function step_1()
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed();

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Guided or Advanced' ) ."
                        ". $this->trellis->skin->group_row_with_p( '<p>The Install Center offers you two ways to complete your Trellis Desk installation. You can choose to continue with the guided installation, or select the advanced option if you are a more experienced user. The guided installation will take you through a few more steps and allow you to configure common settings and features. The advanced installation will allow you to finish in fewer steps, while only collecting information necessary to complete the installation. We strongly recommend the guided installation for new users.</p>' ) ."
                        ". $this->trellis->skin->formtail( '<a href="index.php?step=check" class="button">&laquo; Previous</a>&nbsp;<a href="index.php?step=adv" class="button">Advanced &raquo;</a>&nbsp;<a href="index.php?step=2" class="button">Guided &raquo;</a>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 1 );
        $this->trellis->skin->set_progress_bar( 15 );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Step 2
    #=======================================

    private function step_2($params=array())
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed();

        #=============================
        # Prepopulate
        #=============================

        $prepopulate = array();

        if ( ! isset( $this->trellis->cache->data['install']['config']['db_host'] ) ) ( $this->trellis->config['db_host'] ) ? $prepopulate['db_host'] = $this->trellis->config['db_host'] : $prepopulate['db_host'] = 'localhost';
        if ( ! isset( $this->trellis->cache->data['install']['config']['db_port'] ) ) ( $this->trellis->config['db_port'] ) ? $prepopulate['db_port'] = $this->trellis->config['db_port'] : $prepopulate['db_port'] = 3306;
        if ( ! isset( $this->trellis->cache->data['install']['config']['db_user'] ) ) if ( $this->trellis->config['db_user'] ) $prepopulate['db_user'] = $this->trellis->config['db_user'];
        if ( ! isset( $this->trellis->cache->data['install']['config']['db_pass'] ) ) if ( $this->trellis->config['db_pass'] ) $prepopulate['db_pass'] = $this->trellis->config['db_pass'];
        if ( ! isset( $this->trellis->cache->data['install']['config']['db_name'] ) ) if ( $this->trellis->config['db_name'] ) $prepopulate['db_name'] = $this->trellis->config['db_name'];
        if ( ! isset( $this->trellis->cache->data['install']['config']['db_prefix'] ) ) ( $this->trellis->config['db_prefix'] ) ? $prepopulate['db_prefix'] = $this->trellis->config['db_prefix'] : $prepopulate['db_prefix'] = 'td_';

        if ( ! empty( $prepopulate ) )
        {
            $prepopulate = array_merge( (array)$this->trellis->cache->data['install']['config'], $prepopulate );

            $this->trellis->cache->add( 'install', array( 'config' => $prepopulate ) );
        }

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] )
        {
            $this->output .= $this->trellis->skin->error_wrap( $params['error'] );
            $this->trellis->skin->preserve_input = 1;
        }

        # TODO: add help tooltips where necessary (apply to all install pages)

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "index.php?step=3", 'db_info', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( 'Database Setup', 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row_with_p( '<p>Trellis Desk requires a MySQL database to store your data such as users and tickets. Please enter your database information below. If you are unsure about this information, contact your hosting provider.', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Host', $this->trellis->skin->textfield( array( 'name' => 'db_host', 'value' => $this->trellis->cache->data['install']['config']['db_host'] ) ), 'a', '25%', '75%' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Port', $this->trellis->skin->textfield( array( 'name' => 'db_port', 'value' => $this->trellis->cache->data['install']['config']['db_port'], 'length' => 5 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Username', $this->trellis->skin->textfield( array( 'name' => 'db_user', 'value' => $this->trellis->cache->data['install']['config']['db_user'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Password', $this->trellis->skin->password( array( 'name' => 'db_pass', 'value' => $this->trellis->cache->data['install']['config']['db_pass'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Database', $this->trellis->skin->textfield( array( 'name' => 'db_name', 'value' => $this->trellis->cache->data['install']['config']['db_name'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Table Prefix', $this->trellis->skin->textfield( array( 'name' => 'db_prefix', 'value' => $this->trellis->cache->data['install']['config']['db_prefix'], 'length' => 5 ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( '<a href="index.php?step=1" class="button">&laquo; Previous</a>&nbsp;'. $this->trellis->skin->submit_button( 'store_db', 'Next &raquo;' ) ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 2 );
        $this->trellis->skin->set_progress_bar( $this->get_progress() );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Step 3
    #=======================================

    private function step_3($params=array())
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed();

        #=============================
        # Store Data
        #=============================

        if ( $this->trellis->input['store_db'] )
        {
            $config = array(
                            'db_host'    => $_POST['db_host'],
                            'db_port'    => $_POST['db_port'],
                            'db_user'    => $_POST['db_user'],
                            'db_pass'    => $_POST['db_pass'],
                            'db_name'    => $_POST['db_name'],
                            'db_prefix'    => $_POST['db_prefix'],
                            );

            $this->trellis->cache->add( 'install', array( 'config' => $config, 'info_db' => 0 ) );

            #=============================
            # Check MySQL
            #=============================

            # CHECK: is db_port working? no matter what port i set it to, it always connects. maybe php always uses a default port?

            # TODO: validate db_prefix (not harmful to sql query, etc)

            if ( ! @mysql_connect( $this->trellis->cache->data['install']['config']['db_host'] .':'. $this->trellis->cache->data['install']['config']['db_port'], $this->trellis->cache->data['install']['config']['db_user'], $this->trellis->cache->data['install']['config']['db_pass'] ) ) $this->step_2( array( 'error' => 'Could not connect to the MySQL Server. Please check to verify that your MySQL information is correct and try again.' ) );

            $mysql_ver = mysql_get_server_info();

            if ( strpos( $mysql_ver, '-' ) ) $mysql_ver = substr( $mysql_ver, 0, strpos( $mysql_ver, '-' ) );

            if ( version_compare( $mysql_ver, '4.1', '<' ) ) $this->step_2( array( 'error' => 'Sorry, Trellis Desk cannot be installed as it requires MySQL version 4.1 or later.' ) );

            if ( ! @mysql_select_db( $this->trellis->cache->data['install']['config']['db_name'] ) ) $this->step_2( array( 'error' => 'Could not connect to the MySQL Database. Please check that your MySQL credentails are correct and try again.' ) );

            $this->trellis->cache->add( 'install', array( 'info_db' => 1 ) );
        }

        #=============================
        # Prepopulate
        #=============================

        $prepopulate = array();

        if ( ! isset( $this->trellis->cache->data['install']['admin']['time_dst'] ) ) $prepopulate['time_dst'] = 2;
        if ( ! isset( $this->trellis->cache->data['install']['admin']['rte_enable'] ) ) $prepopulate['rte_enable'] = 1;

        if ( ! empty( $prepopulate ) )
        {
            $prepopulate = array_merge( (array)$this->trellis->cache->data['install']['admin'], $prepopulate );

            $this->trellis->cache->add( 'install', array( 'admin' => $prepopulate ) );
        }

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] )
        {
            $this->output .= $this->trellis->skin->error_wrap( $params['error'] );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->trellis->load_functions('drop_downs');

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "index.php?step=4", 'admin_info', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( 'Create Admin User', 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row_with_p( '<p>You will now create your admin user for your Trellis Desk installation. This user has the highest level of access and always overrides any restrictive permissions. Please choose these credentials carefully.', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Username', $this->trellis->skin->textfield( array( 'name' => 'admin_name', 'value' => $this->trellis->cache->data['install']['admin']['name'] ) ), 'a', '25%', '75%' ) ."
                        ". $this->trellis->skin->group_table_row( 'Email', $this->trellis->skin->textfield( array( 'name' => 'admin_email', 'value' => $this->trellis->cache->data['install']['admin']['email'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Password', $this->trellis->skin->password( array( 'name' => 'admin_pass', 'value' => $this->trellis->cache->data['install']['admin']['pass'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Password Confirm', $this->trellis->skin->password( array( 'name' => 'admin_pass_b', 'value' => $this->trellis->cache->data['install']['admin']['pass'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Time Zone', "<select name='admin_time_zone' id='admin_time_zone'>". $this->trellis->func->drop_downs->time_zone_drop( $this->trellis->cache->data['install']['admin']['time_zone'], 0) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Daylight Savings', $this->trellis->skin->custom_radio( 'admin_time_dst', array( 0 => 'Inactive', 1 => 'Active', 2 => 'Auto' ), $this->trellis->cache->data['install']['admin']['time_dst'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Rich Text Editor', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'admin_rte_enable', 'value' => $this->trellis->cache->data['install']['admin']['rte_enable'] ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( '<a href="index.php?step=2" class="button">&laquo; Previous</a>&nbsp;'. $this->trellis->skin->submit_button( 'store_admin', 'Next &raquo;' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'admin_name'    => array( array( 'type' => 'presence' ) ),
                                 'admin_email'    => array( array( 'type' => 'presence' ), array( 'type' => 'email' ) ),
                                 'admin_pass'    => array( array( 'type' => 'presence' ) ),
                                 'admin_pass_b'    => array( array( 'type' => 'presence' ), array( 'type' => 'match', 'params' => array( 'against' => 'admin_pass' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 3 );
        $this->trellis->skin->set_progress_bar( $this->get_progress() );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Step 4
    #=======================================

    private function step_4($params=array())
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed();

        #=============================
        # Store Data
        #=============================

        if ( $this->trellis->input['store_admin'] )
        {
            $admin = array(
                            'name'            => $this->trellis->input['admin_name'],
                            'email'            => $this->trellis->input['admin_email'],
                            'pass'            => $this->trellis->input['admin_pass'],
                            'time_zone'        => $this->trellis->input['admin_time_zone'],
                            'time_dst'        => $this->trellis->input['admin_time_dst'],
                            'rte_enable'    => $this->trellis->input['admin_rte_enable'],
                            );

            $this->trellis->cache->add( 'install', array( 'admin' => $admin, 'info_admin' => 0 ) );

            #=============================
            # Check Admin
            #=============================

            if ( ! $this->trellis->cache->data['install']['admin']['name'] ) $this->step_3( array( 'error' => 'Please enter a username.' ) );
            if ( ! $this->trellis->cache->data['install']['admin']['email'] ) $this->step_3( array( 'error' => 'Please enter an email address.' ) );
            if ( ! $this->trellis->cache->data['install']['admin']['pass'] ) $this->step_3( array( 'error' => 'Please enter a password.' ) );

            if ( ! $this->trellis->validate_email( $this->trellis->cache->data['install']['admin']['email'] ) ) $this->step_3( array( 'error' => 'Please enter a valid email address.' ) );
            if ( $this->trellis->input['admin_pass'] != $this->trellis->input['admin_pass_b'] ) $this->step_3( array( 'error' => 'Your passwords do not match.' ) );

            $this->trellis->cache->add( 'install', array( 'info_admin' => 1 ) );
        }

        #=============================
        # Prepopulate
        #=============================

        $prepopulate = array();

        if ( ! isset( $this->trellis->cache->data['install']['antispam']['port'] ) ) $prepopulate['port'] = 80;
        if ( ! isset( $this->trellis->cache->data['install']['antispam']['protect_registration'] ) ) $prepopulate['protect_registration'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['antispam']['protect_tickets'] ) ) $prepopulate['protect_tickets'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['antispam']['protect_forgot_pass'] ) ) $prepopulate['protect_forgot_pass'] = 1;

        if ( ! empty( $prepopulate ) )
        {
            $prepopulate = array_merge( (array)$this->trellis->cache->data['install']['antispam'], $prepopulate );

            $this->trellis->cache->add( 'install', array( 'antispam' => $prepopulate ) );
        }

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] )
        {
            $this->output .= $this->trellis->skin->error_wrap( $params['error'] );
            $this->trellis->skin->preserve_input = 1;
        }

        # TODO: update More information link

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "index.php?step=5", 'antispam_info', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( 'Configure Anti-Spam Settings', 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row_with_p( '<p>Trellis Desk offers three methods to fight spam: <a href="http://akismet.com/">Akismet</a>, <a href="http://www.ejeliot.com/pages/php-captcha">PhpCatpcha</a>, and <a href="http://recaptcha.net/">reCAPTCHA</a>. You can choose to enable and configure one of these methods below. <a href="#">More information.</a></p><p>After you have installed Trellis Desk, please verify that your anti-spam method works correctly (if enabled). You can make any necessary changes to the settings in the ACP.</p>', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Enable Anti-Spam', $this->trellis->skin->yes_no_radio( array( 'name' => 'antispam_enable', 'value' => $this->trellis->cache->data['install']['antispam']['enable'] ) ), 'a', '30%', '70%' ) ."
                        ". $this->trellis->skin->group_table_row( 'Method', $this->trellis->skin->drop_down( array( 'name' => 'antispam_method', 'options' => array( 'akismet' => 'Akismet', 'phpcaptcha' => 'PhpCaptcha', 'recaptcha' => 'reCAPTCHA' ), 'value' => $this->trellis->cache->data['install']['antispam']['method'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Akismet Key', $this->trellis->skin->textfield( array( 'name' => 'antispam_akismet_key', 'value' => $this->trellis->cache->data['install']['antispam']['akismet_key'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'reCAPTCHA Public Key', $this->trellis->skin->textfield( array( 'name' => 'antispam_recaptcha_key_public', 'value' => $this->trellis->cache->data['install']['antispam']['recaptcha_key_public'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'reCAPTCHA Private Key', $this->trellis->skin->textfield( array( 'name' => 'antispam_recaptcha_key_private', 'value' => $this->trellis->cache->data['install']['antispam']['recaptcha_key_private'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'API Port', $this->trellis->skin->textfield( array( 'name' => 'antispam_port', 'value' => $this->trellis->cache->data['install']['antispam']['port'], 'length' => 5 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Use SSL', $this->trellis->skin->yes_no_radio( array( 'name' => 'antispam_ssl', 'value' => $this->trellis->cache->data['install']['antispam']['ssl'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Protect Registration Form', $this->trellis->skin->yes_no_radio( array( 'name' => 'antispam_protect_registration', 'value' => $this->trellis->cache->data['install']['antispam']['protect_registration'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Protect Guest Ticket Form', $this->trellis->skin->yes_no_radio( array( 'name' => 'antispam_protect_tickets', 'value' => $this->trellis->cache->data['install']['antispam']['protect_tickets'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Protect Forgot Password Form', $this->trellis->skin->yes_no_radio( array( 'name' => 'antispam_protect_forgot_pass', 'value' => $this->trellis->cache->data['install']['antispam']['protect_forgot_pass'] ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( '<a href="index.php?step=3" class="button">&laquo; Previous</a>&nbsp;'. $this->trellis->skin->submit_button( 'store_antispam', 'Next &raquo;' ) ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 4 );
        $this->trellis->skin->set_progress_bar( $this->get_progress() );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Step 5
    #=======================================

    private function step_5($params=array())
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed();

        #=============================
        # Store Data
        #=============================

        if ( $this->trellis->input['store_antispam'] )
        {
            $antispam = array(
                            'enable'                => $this->trellis->input['antispam_enable'],
                            'method'                => $this->trellis->input['antispam_method'],
                            'akismet_key'            => $this->trellis->input['antispam_akismet_key'],
                            'recaptcha_key_public'    => $this->trellis->input['antispam_recaptcha_key_public'],
                            'recaptcha_key_private'    => $this->trellis->input['antispam_recaptcha_key_private'],
                            'port'                    => $this->trellis->input['antispam_port'],
                            'ssl'                    => $this->trellis->input['antispam_ssl'],
                            'protect_registration'    => $this->trellis->input['antispam_protect_registration'],
                            'protect_tickets'        => $this->trellis->input['antispam_protect_tickets'],
                            'protect_forgot_pass'    => $this->trellis->input['antispam_protect_forgot_pass'],
                            );

            $this->trellis->cache->add( 'install', array( 'antispam' => $antispam, 'info_antispam' => 0 ) );

            #=============================
            # Check Antispam
            #=============================

            if ( $this->trellis->cache->data['install']['antispam']['enable'] )
            {
                $this->trellis->load_antispam_from_array( $this->trellis->cache->data['install']['antispam'] );

                if ( ! $this->trellis->antispam->check_system() ) $this->step_4( array( 'error' => $this->trellis->antispam->get_error() ) );
            }

            $this->trellis->cache->add( 'install', array( 'info_antispam' => 1 ) );
        }

        #=============================
        # Prepopulate
        #=============================

        $prepopulate = array();

        if ( ! isset( $this->trellis->cache->data['install']['email']['enable'] ) ) $prepopulate['enable'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['email']['out_address'] ) && $this->trellis->cache->data['install']['admin']['email'] ) $prepopulate['out_address'] = $this->trellis->cache->data['install']['admin']['email'];
        if ( ! isset( $this->trellis->cache->data['install']['email']['smtp_host'] ) ) $prepopulate['smtp_host'] = 'localhost';
        if ( ! isset( $this->trellis->cache->data['install']['email']['smtp_port'] ) ) $prepopulate['smtp_port'] = 25;
        if ( ! isset( $this->trellis->cache->data['install']['email']['smtp_timeout'] ) ) $prepopulate['smtp_timeout'] = 10;
        if ( ! isset( $this->trellis->cache->data['install']['email']['html'] ) ) $prepopulate['html'] = 1;

        if ( ! empty( $prepopulate ) )
        {
            $prepopulate = array_merge( (array)$this->trellis->cache->data['install']['email'], $prepopulate );

            $this->trellis->cache->add( 'install', array( 'email' => $prepopulate ) );
        }

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] )
        {
            $this->output .= $this->trellis->skin->error_wrap( $params['error'] );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "index.php?step=6", 'email_info', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( 'Configure Email Settings', 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row_with_p( '<p>Configure your outgoing email settings below (used for email notifications, email verification messages, mass emails, etc).</p><p>After you have installed Trellis Desk, please verify that your outgoing emails work correctly (if enabled). You can make any necessary changes to the settings in the ACP.</p>', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Enable Outgoing Emails', $this->trellis->skin->yes_no_radio( array( 'name' => 'email_enable', 'value' => $this->trellis->cache->data['install']['email']['enable'] ) ), 'a', '30%', '70%' ) ."
                        ". $this->trellis->skin->group_table_row( 'Outgoing Email Address', $this->trellis->skin->textfield( array( 'name' => 'email_out_address', 'value' => $this->trellis->cache->data['install']['email']['out_address'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Method', $this->trellis->skin->drop_down( array( 'name' => 'email_transport', 'options' => array( 'smtp' => 'SMTP', 'sendmail' => 'Sendmail', 'mail' => 'Mail' ), 'value' => $this->trellis->cache->data['install']['email']['transport'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'SMTP Host', $this->trellis->skin->textfield( array( 'name' => 'email_smtp_host', 'value' => $this->trellis->cache->data['install']['email']['smtp_host'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'SMTP Port', $this->trellis->skin->textfield( array( 'name' => 'email_smtp_port', 'value' => $this->trellis->cache->data['install']['email']['smtp_port'], 'length' => 5 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'SMTP Username', $this->trellis->skin->textfield( array( 'name' => 'email_smtp_user', 'value' => $this->trellis->cache->data['install']['email']['smtp_user'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'SMTP Password', $this->trellis->skin->password( array( 'name' => 'email_smtp_pass', 'value' => $this->trellis->cache->data['install']['email']['smtp_pass'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'SMTP Encryption', $this->trellis->skin->drop_down( array( 'name' => 'email_smtp_encryption', 'options' => array( 0 => 'None', 'ssl' => 'SSL', 'tls' => 'TLS' ), 'value' => $this->trellis->cache->data['install']['email']['smtp_encryption'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'SMTP Timeout', $this->trellis->skin->textfield( array( 'name' => 'email_smtp_timeout', 'value' => $this->trellis->cache->data['install']['email']['smtp_timeout'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Sendmail Path / Command', $this->trellis->skin->textfield( array( 'name' => 'email_sendmail_command', 'value' => $this->trellis->cache->data['install']['email']['sendmail_command'], 'length' => 5 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Enable HTML Emails', $this->trellis->skin->yes_no_radio( array( 'name' => 'email_html', 'value' => $this->trellis->cache->data['install']['email']['html'] ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( '<a href="index.php?step=4" class="button">&laquo; Previous</a>&nbsp;'. $this->trellis->skin->submit_button( 'store_email', 'Next &raquo;' ) ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 5 );
        $this->trellis->skin->set_progress_bar( $this->get_progress() );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Step 6
    #=======================================

    private function step_6($params=array())
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed();

        #=============================
        # Store Data
        #=============================

        if ( $this->trellis->input['store_email'] )
        {
            $email = array(
                            'enable'            => $this->trellis->input['email_enable'],
                            'out_address'        => $this->trellis->input['email_out_address'],
                            'transport'            => $this->trellis->input['email_transport'],
                            'smtp_host'            => $this->trellis->input['email_smtp_host'],
                            'smtp_port'            => $this->trellis->input['email_smtp_port'],
                            'smtp_user'            => $this->trellis->input['email_smtp_user'],
                            'smtp_pass'            => $this->trellis->input['email_smtp_pass'],
                            'smtp_encryption'    => $this->trellis->input['email_smtp_encryption'],
                            'smtp_timeout'        => $this->trellis->input['email_smtp_timeout'],
                            'sendmail_command'    => $this->trellis->input['email_sendmail_command'],
                            'html'                => $this->trellis->input['email_html'],
                            );

            $this->trellis->cache->add( 'install', array( 'email' => $email, 'info_email' => 0 ) );

            #=============================
            # Check Email
            #=============================

            if ( $this->trellis->cache->data['install']['email']['enable'] && ( $this->trellis->cache->data['install']['email']['transport'] == 'smtp' || $this->trellis->cache->data['install']['email']['transport'] == 'sendmail' ) )
            {
                $this->trellis->load_email_from_array( $this->trellis->cache->data['install']['email'] );

                if ( ! $this->trellis->email->test() )
                {
                    if ( $this->trellis->cache->data['install']['email']['transport'] == 'smtp' )
                    {
                        $transports = stream_get_transports();

                        ( $this->trellis->cache->data['install']['email']['smtp_encryption'] ) ? $tp = $this->trellis->cache->data['install']['email']['smtp_encryption'] : $tp = 'tcp';

                        if ( ! in_array( $tp, $transports ) )
                        {
                            if ( $this->trellis->cache->data['install']['email']['smtp_encryption'] == 'tls' )
                            {
                                $error_msg = 'Unable to connect to SMTP server. TLS transport is not supported by server.';
                            }
                            elseif ( $this->trellis->cache->data['install']['email']['smtp_encryption'] == 'ssl' )
                            {
                                $error_msg = 'Unable to connect to SMTP server. SSL transport is not supported by server.';
                            }
                            else
                            {
                                $error_msg = 'Unable to connect to SMTP server. TCP transport is not supported by server.';
                            }
                        }
                        else
                        {
                            $error_msg = 'Unable to connect to SMTP server.';
                        }

                        if ( $this->trellis->email->get_exception() ) $error_msg .= ' The following error was returned.<br /><br />'. $this->trellis->email->get_exception();
                    }
                    elseif ( $this->trellis->cache->data['install']['email']['transport'] == 'sendmail' )
                    {
                            $error_msg = 'Unable to connect to Sendmail transport.';
                    }

                    $this->step_5( array( 'error' => $error_msg ) );
                }
            }

            $this->trellis->cache->add( 'install', array( 'info_email' => 1 ) );
        }

        #=============================
        # Prepopulate
        #=============================

        $prepopulate = array();

        if ( ! isset( $this->trellis->cache->data['install']['other']['pass_key'] ) ) if ( $this->trellis->config['pass_key'] ) $prepopulate['pass_key'] = $this->trellis->config['pass_key'];
        if ( ! isset( $this->trellis->cache->data['install']['other']['cookie_key'] ) ) if ( $this->trellis->config['cookie_key'] ) $prepopulate['cookie_key'] = $this->trellis->config['cookie_key'];
        if ( ! isset( $this->trellis->cache->data['install']['other']['session_key'] ) ) if ( $this->trellis->config['session_key'] ) $prepopulate['session_key'] = $this->trellis->config['session_key'];
        if ( ! isset( $this->trellis->cache->data['install']['other']['rss_key'] ) ) if ( $this->trellis->config['rss_key'] ) $prepopulate['rss_key'] = $this->trellis->config['rss_key'];
        if ( ! isset( $this->trellis->cache->data['install']['other']['hd_name'] ) ) $prepopulate['hd_name'] = 'Trellis Desk';
        if ( ! isset( $this->trellis->cache->data['install']['other']['validation_email'] ) ) $prepopulate['validation_email'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['other']['ticket_mask'] ) ) $prepopulate['ticket_mask'] = '%A%A%A-%n%n%n%n';
        if ( ! isset( $this->trellis->cache->data['install']['other']['ticket_suggest'] ) ) $prepopulate['ticket_suggest'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['other']['reply_rating'] ) ) $prepopulate['reply_rating'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['other']['news_comments'] ) ) $prepopulate['news_comments'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['other']['kb_comments'] ) ) $prepopulate['kb_comments'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['other']['kb_rating'] ) ) $prepopulate['kb_rating'] = 1;
        if ( ! isset( $this->trellis->cache->data['install']['other']['vcheck_share'] ) ) $prepopulate['vcheck_share'] = 2;

        if ( ! isset( $this->trellis->cache->data['install']['other']['hd_url'] ) )
        {
            $url = str_replace( "/install/index.php", "", $_SERVER['HTTP_REFERER'] );
            $url = str_replace( "/install/", "", $url );
            $url = str_replace( "/install", "", $url );
            $url = str_replace( "index.php", "", $url );
            $url = substr( $url, 0, strpos( $url, '?' ) );
            $url = str_replace( "?", "", $url );

            $prepopulate['hd_url'] = $url;
        }

        if ( ! empty( $prepopulate ) )
        {
            $prepopulate = array_merge( (array)$this->trellis->cache->data['install']['other'], $prepopulate );

            $this->trellis->cache->add( 'install', array( 'other' => $prepopulate ) );
        }

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] )
        {
            $this->output .= $this->trellis->skin->error_wrap( $params['error'] );
            $this->trellis->skin->preserve_input = 1;
        }

        # TODO: without trailing slash on help desk url reminder

        # TODO: add absolute path?

        # TODO: add option to generate random keys

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "index.php?step=7", 'other_info', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( 'Security', 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row_with_p( '<p>Trellis Desk encryptions sensitive information such as passwords. Keys (salts) are used during encryption to increase security. Please enter a unique string for each security key below. Each key can be a sentence, phrase, random characters / numbers, etc.</p>', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Password Key', $this->trellis->skin->textfield( array( 'name' => 'other_pass_key', 'value' => $this->trellis->cache->data['install']['other']['pass_key'] ) ), 'a', '30%', '70%' ) ."
                        ". $this->trellis->skin->group_table_row( 'Cookie Key', $this->trellis->skin->textfield( array( 'name' => 'other_cookie_key', 'value' => $this->trellis->cache->data['install']['other']['cookie_key'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Session Key', $this->trellis->skin->textfield( array( 'name' => 'other_session_key', 'value' => $this->trellis->cache->data['install']['other']['session_key'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'RSS Key', $this->trellis->skin->textfield( array( 'name' => 'other_rss_key', 'value' => $this->trellis->cache->data['install']['other']['rss_key'] ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        <br />
                        ". $this->trellis->skin->start_group_table( 'Common Settings', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Help Desk Name', $this->trellis->skin->textfield( array( 'name' => 'other_hd_name', 'value' => $this->trellis->cache->data['install']['other']['hd_name'] ) ), 'a', '30%', '70%' ) ."
                        ". $this->trellis->skin->group_table_row( 'Help Desk URL', $this->trellis->skin->textfield( array( 'name' => 'other_hd_url', 'value' => $this->trellis->cache->data['install']['other']['hd_url'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Require Email Validation', $this->trellis->skin->yes_no_radio( array( 'name' => 'other_validation_email', 'value' => $this->trellis->cache->data['install']['other']['validation_email'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Require Admin Validation', $this->trellis->skin->yes_no_radio( array( 'name' => 'other_validation_admin', 'value' => $this->trellis->cache->data['install']['other']['validation_admin'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Ticket Mask', $this->trellis->skin->textfield( array( 'name' => 'other_ticket_mask', 'value' => $this->trellis->cache->data['install']['other']['ticket_mask'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Enable KB Suggestions', $this->trellis->skin->yes_no_radio( array( 'name' => 'other_ticket_suggest', 'value' => $this->trellis->cache->data['install']['other']['ticket_suggest'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Enable Reply Rating', $this->trellis->skin->yes_no_radio( array( 'name' => 'other_reply_rating', 'value' => $this->trellis->cache->data['install']['other']['reply_rating'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Enable News Comments', $this->trellis->skin->yes_no_radio( array( 'name' => 'other_news_comments', 'value' => $this->trellis->cache->data['install']['other']['news_comments'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Enable KB Comments', $this->trellis->skin->yes_no_radio( array( 'name' => 'other_kb_comments', 'value' => $this->trellis->cache->data['install']['other']['kb_comments'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Enable KB Rating', $this->trellis->skin->yes_no_radio( array( 'name' => 'other_kb_rating', 'value' => $this->trellis->cache->data['install']['other']['kb_rating'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Share Statistics with ACCORD5', $this->trellis->skin->custom_radio( array( 'name' => 'other_vcheck_share', 'options' => array( 2 => 'Yes', 1 => 'Yes, Anonymously', 0 => 'No' ), 'value' => $this->trellis->cache->data['install']['other']['vcheck_share'] ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( '<a href="index.php?step=5" class="button">&laquo; Previous</a>&nbsp;'. $this->trellis->skin->submit_button( 'store_other', 'Next &raquo;' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'other_hd_name' => array( array( 'type' => 'presence' ) ),
                                 'other_ticket_mask' => array( array( 'type' => 'presence' ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 6 );
        $this->trellis->skin->set_progress_bar( $this->get_progress() );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Step 7
    #=======================================

    private function step_7($params=array())
    {
        $this->output = '';

        if ( $this->trellis->config['start'] ) $this->already_installed();

        #=============================
        # Store Data
        #=============================

        if ( $this->trellis->input['store_other'] )
        {
            $other = array(
                            'pass_key'            => $this->trellis->input['other_pass_key'],
                            'cookie_key'        => $this->trellis->input['other_cookie_key'],
                            'session_key'        => $this->trellis->input['other_session_key'],
                            'rss_key'            => $this->trellis->input['other_rss_key'],
                            'hd_name'            => $this->trellis->input['other_hd_name'],
                            'hd_url'            => $this->trellis->input['other_hd_url'],
                            'validation_email'    => $this->trellis->input['other_validation_email'],
                            'validation_admin'    => $this->trellis->input['other_validation_admin'],
                            'ticket_mask'        => $this->trellis->input['other_ticket_mask'],
                            'ticket_suggest'    => $this->trellis->input['other_ticket_suggest'],
                            'reply_rating'        => $this->trellis->input['other_reply_rating'],
                            'news_comments'        => $this->trellis->input['other_news_comments'],
                            'kb_comments'        => $this->trellis->input['other_kb_comments'],
                            'kb_rating'            => $this->trellis->input['other_kb_rating'],
                            'vcheck_share'        => $this->trellis->input['other_vcheck_share'],
                            );

            $this->trellis->cache->add( 'install', array( 'other' => $other, 'info_other' => 0 ) );

            #=============================
            # Check Other
            #=============================

            if ( ! $this->trellis->cache->data['install']['other']['hd_name'] ) $this->step_6( array( 'error' => 'Please enter a help desk name.' ) );
            if ( ! $this->trellis->cache->data['install']['other']['hd_url'] ) $this->step_6( array( 'error' => 'Please the help desk URL.' ) );
            if ( ! $this->trellis->cache->data['install']['other']['ticket_mask'] ) $this->step_6( array( 'error' => 'Please enter a ticket mask.' ) );

            # TODO: validate URL (check for appropriate format / characters)

            $this->trellis->cache->add( 'install', array( 'info_other' => 1 ) );
        }

        #=============================
        # Check Steps
        #=============================

        $steps = array( 'info_db' => 2, 'info_admin' => 3, 'info_antispam' => 4, 'info_email' => 5, 'info_other' => 6 );

        foreach ( $steps as $sc => $sn )
        {
            if ( ! $this->trellis->cache->data['install'][ $sc ] )
            {
                $step_func = 'step_'. $sn;

                $this->$step_func( array( 'error' => 'You must complete this step before continuing.' ) );
            }
        }

        $this->trellis->cache->add( 'install', array( 'info_review' => 1 ) );

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] ) $this->output .= $this->trellis->skin->error_wrap( $params['error'] );

        if ( $this->trellis->input['passwords'] )
        {
            $db_pass = $this->trellis->cache->data['install']['config']['db_pass'];
            $admin_pass = $this->trellis->cache->data['install']['admin']['pass'];
        }
        else
        {
            $db_pass = $admin_pass = '<a href="index.php?step=7&amp;passwords=1">Show Passwords</a>';
        }

        $this->trellis->load_functions('tickets');

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( 'Review Installation', 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row_with_p( '<p>The Install Center is now ready to begin the automated installation process. Please verify that all the information below is correct (note that not all information is shown). If you need to make any changes, you may do so by using the navigation buttons at the bottom of each page, or by clicking on the appriopriate step on the left sidebar menu.</p><p>When you are ready to begin, click Install.', 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( 'Database Information' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Host', $this->trellis->cache->data['install']['config']['db_host'], 'a', '25%', '75%' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Port', $this->trellis->cache->data['install']['config']['db_port'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Username', $this->trellis->cache->data['install']['config']['db_user'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Password', $db_pass, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Database', $this->trellis->cache->data['install']['config']['db_name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'MySQL Table Prefix', $this->trellis->cache->data['install']['config']['db_prefix'], 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( 'Admin User' ) ."
                        ". $this->trellis->skin->group_table_row( 'Username', $this->trellis->cache->data['install']['admin']['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Email', $this->trellis->cache->data['install']['admin']['email'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Password', $admin_pass, 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( 'Common Settings' ) ."
                        ". $this->trellis->skin->group_table_row( 'Help Desk Name', $this->trellis->cache->data['install']['other']['hd_name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Help Desk URL', $this->trellis->cache->data['install']['other']['hd_url'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Ticket Mask', $this->trellis->cache->data['install']['other']['ticket_mask'] .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ticket Mask sample: '. $this->trellis->func->tickets->generate_mask( 1, $this->trellis->cache->data['install']['other']['ticket_mask'] ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( '<a href="index.php?step=6" class="button">&laquo; Previous</a>&nbsp;<a href="index.php?step=install&amp;do=1" class="button">Install &raquo;</a>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 7 );
        $this->trellis->skin->set_progress_bar( 62 );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Install
    #=======================================

    private function step_install()
    {
        $this->output = '';

        #=============================
        # Check Steps
        #=============================

        $steps = array( 'info_db' => 2, 'info_admin' => 3, 'info_antispam' => 4, 'info_email' => 5, 'info_other' => 6, 'info_review' => 7 );

        foreach ( $steps as $sc => $sn )
        {
            if ( ! $this->trellis->cache->data['install'][ $sc ] )
            {
                $step_func = 'step_'. $sn;

                $this->$step_func( array( 'error' => 'You must complete this step before continuing.' ) );
            }
        }

        #=============================
        # Run Install Step
        #=============================

        switch ( $this->trellis->input['do'] )
        {
            case 1:
                $this->install_languages();
            break;
            case 2:
                $this->install_skins();
            break;
            case 3:
                $this->install_other();
            break;
            case 4:
                $this->install_initialize();
            break;

            default:
                $this->step_7();
            break;
        }
    }

    #=======================================
    # @ Do Install Languages
    #=======================================

    private function install_languages($params=array())
    {
        $this->output = '';

        # TODO: install languages

        if ( ! $this->trellis->cache->data['install']['do_languages'] )
        {
            $this->trellis->cache->add( 'install', array( 'do_languages' => 1 ) );
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Installing Languages' ) ."
                        ". $this->trellis->skin->group_row_with_p( '<p>The Install Center is installing your language files...</p><p>If the next step does not automatically start within five seconds, <a href="index.php?step=install&amp;do=2">click here</a>.</p>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 'install' );
        $this->trellis->skin->set_progress_bar( 70 );

        header( 'Refresh: 1; URL=index.php?step=install&do=2' );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Install Skins
    #=======================================

    private function install_skins()
    {
        $this->output = '';

        if ( ! $this->trellis->cache->data['install']['do_languages'] ) $this->install_languages();

        # TODO: install skins
        # TODO: show next message (when this is displayed, skins will already be installed. the previous step should say installing skins)

        if ( ! $this->trellis->cache->data['install']['do_skins'] )
        {
            $this->trellis->cache->add( 'install', array( 'do_skins' => 1 ) );
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Installing Skins' ) ."
                        ". $this->trellis->skin->group_row_with_p( '<p>The Install Center is installing your skin files...</p><p>If the next step does not automatically start within five seconds, <a href="index.php?step=install&amp;do=3">click here</a>.</p>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 'install' );
        $this->trellis->skin->set_progress_bar( 78 );

        header( 'Refresh: 1; URL=index.php?step=install&do=3' );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Install Other
    #=======================================

    private function install_other()
    {
        $this->output = '';

        if ( ! $this->trellis->cache->data['install']['do_skins'] ) $this->install_skins();

        if ( ! $this->trellis->cache->data['install']['do_other'] )
        {
            #=============================
            # Write Configuration File
            #=============================

            if ( ! $handle = @fopen( TD_PATH .'config.php', 'w' ) ) $this->step_7( array( 'error' => 'Could not write to the configuration file.' ) );

            $path = str_replace( "/install/index.php", "", $_SERVER['SCRIPT_FILENAME'] );
            $path = str_replace( "/install/", "", $path );
            $path = str_replace( "/install", "", $path );
            $path = str_replace( "index.php", "", $path );

            $dir = dirname( dirname( __FILE__ ) );
            $position = strrpos( $path, '/' ) + 1;
            $cookie_path = substr($path, $position);

            $flatfile_key = $this->generate_key( 6 );

            $file_data = "<?php\n\n";

            $file_data .= "\$config['db_host'] = '". $this->trellis->cache->data['install']['config']['db_host'] ."';\n";
            $file_data .= "\$config['db_port'] = '". $this->trellis->cache->data['install']['config']['db_port'] ."';\n";
            $file_data .= "\$config['db_user'] = '". $this->trellis->cache->data['install']['config']['db_user'] ."';\n";
            $file_data .= "\$config['db_pass'] = '". $this->trellis->cache->data['install']['config']['db_pass'] ."';\n";
            $file_data .= "\$config['db_name'] = '". $this->trellis->cache->data['install']['config']['db_name'] ."';\n";
            $file_data .= "\$config['db_prefix'] = '". $this->trellis->cache->data['install']['config']['db_prefix'] ."';\n";
            $file_data .= "\$config['db_shutdown_queries'] = true;\n\n";
            $file_data .= "\$config['pass_key'] = '". $this->trellis->cache->data['install']['other']['pass_key'] ."';\n";
            $file_data .= "\$config['cookie_key'] = '". $this->trellis->cache->data['install']['other']['cookie_key'] ."';\n";
            $file_data .= "\$config['session_key'] = '". $this->trellis->cache->data['install']['other']['session_key'] ."';\n";
            $file_data .= "\$config['rss_key'] = '". $this->trellis->cache->data['install']['other']['rss_key'] ."';\n";
            $file_data .= "\$config['flatfile_key'] = '". $flatfile_key ."';\n\n";
            $file_data .= "\$config['hd_url'] = '". $this->trellis->cache->data['install']['other']['hd_url'] ."';\n";
            $file_data .= "\$config['data_path'] = '". $path ."/data/';\n";
            $file_data .= "\$config['cache_path'] = \$config['data_path'] .'cache/';\n";
            $file_data .= "\$config['logs_path'] = \$config['data_path'] .'logs/';\n";
            $file_data .= "\$config['skin_compile_path'] = \$config['data_path'] .'skin_compile/';\n";
            $file_data .= "\$config['temp_path'] = \$config['data_path'] .'temp/';\n\n";
            $file_data .= "\$config['debug_level'] = ". $this->trellis->config['debug_level'] .";\n";
            $file_data .= "\$config['fallback_lang'] = 'en';\n";
            $file_data .= "\$config['fallback_skin'] = 1;\n";
            $file_data .= "\$config['acp_session_timeout'] = 60;\n\n";
            $file_data .= "\$config['start'] = '". time() ."';\n\n";

            $file_data .= "?>";

            if ( ! @fwrite( $handle, $file_data ) ) $this->step_7( array( 'error' => 'Could not write to the configuration file.' ) );

            @fclose($handle);

            #=============================
            # Apply Flat-File Key
            #=============================

            $this->trellis->cache->clear_all();
            $this->trellis->cache->set_file_key( $flatfile_key );

            #=============================
            # Run SQL Queries
            #=============================

            $upload_path = $path ."/data/uploads";

            $pwsalt = '';
            $rksalt = '';

            while( strlen( $pwsalt ) < 16 ) $pwsalt .= chr( rand( 32, 126 ) );
            while( strlen( $rksalt ) < 8 ) $rksalt .= chr( rand( 32, 126 ) );

            $rksalt .= uniqid( rand(), true );

            $pwhash = hash( 'whirlpool', $pwsalt . $this->trellis->cache->data['install']['admin']['pass'] . $this->trellis->cache->data['install']['other']['pass_key'] );
            $rkhash = md5( $rksalt . $this->trellis->cache->data['install']['other']['rss_key'] );

            if ( ! @mysql_connect( $this->trellis->cache->data['install']['config']['db_host'] .':'. $this->trellis->cache->data['install']['config']['db_port'], $this->trellis->cache->data['install']['config']['db_user'], $this->trellis->cache->data['install']['config']['db_pass'] ) ) $this->step_2( array( 'error' => 'Could not connect to the MySQL Server.' ) );

            if ( ! @mysql_select_db( $this->trellis->cache->data['install']['config']['db_name'] ) ) $this->step_7( array( 'error' => 'Could not connect to the MySQL Database.' ) );

            // Prefix
            $db_prefix = $this->trellis->cache->data['install']['config']['db_prefix'];

            # CHECK: special characters in database password, etc

            require_once TD_INSTALL . 'sql_queries.php';

            foreach( $SQL as $sql_query )
            {
                if ( ! @mysql_query( $sql_query ) ) $this->step_7( array( 'error' => 'An error encountered while trying to run the following SQL Query.<br /><br />'. $sql_query .'<br /><br />MySQL returned the following error.<br /><br />'. mysql_error() .'<br /><br />'. mysql_errno() ) );
            }

            $this->trellis->cache->add( 'install', array( 'do_other' => 1 ) );
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Installing' ) ."
                        ". $this->trellis->skin->group_row_with_p( '<p>The Install Center is running other install processes...</p><p>If the next step does not automatically start within five seconds, <a href="index.php?step=install&amp;do=4">click here</a>.</p>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 'install' );
        $this->trellis->skin->set_progress_bar( 86 );

        header( 'Refresh: 1; URL=index.php?step=install&do=4' );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Install Initialize
    #=======================================

    private function install_initialize()
    {
        $this->output = '';

        if ( ! $this->trellis->cache->data['install']['do_other'] ) $this->install_other();

        if ( ! $this->trellis->cache->data['install']['finished'] )
        {
            $this->trellis->load_database();

            #=============================
            # Initialize Cache
            #=============================

            $this->trellis->check_cache();

            #=============================
            # Clear Install Cache
            #=============================

            $this->trellis->cache->add( 'install', array( 'finished' => 1 ), 1 ); // Clears (replaces) install cache
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Installing' ) ."
                        ". $this->trellis->skin->group_row_with_p( '<p>The Install Center is finishing the installation...</p><p>If the next step does not automatically start within five seconds, <a href="index.php?step=security">click here</a>.</p>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 'install' );
        $this->trellis->skin->set_progress_bar( 96 );

        header( 'Refresh: 1; URL=index.php?step=security' );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Security
    #=======================================

    private function security()
    {
        $this->output = '';

        #=============================
        # Permission Checks
        #=============================

        if ( ! $this->trellis->cache->data['install']['finished'] ) $this->step_7();

        $checks_html = '';

        $checks_html = "<table width='100%' cellpadding='0' cellspacing='0'>";

        if ( is_writable( TD_PATH .'config.php' ) ) @chmod( TD_PATH .'config.php', 0755 );

        $checks_html .= $this->check_row_start( 'Configuration File' );
        $checks_html .= $this->check_row_end( ! is_writable( TD_PATH .'config.php' ), array( 'warn_msg' => 'Your configuration file is still writeable. We strongly recommend changing the permissions on <i>'. TD_PATH .'config.php</i> to 0755.' ) );

        if ( $handle = @fopen( TD_INSTALL .'install.lock', 'w' ) )
        {
            @fwrite( $handle, time() );

            @fclose($handle);
        }

        $checks_html .= $this->check_row_start( 'Install Center Lock' );
        $checks_html .= $this->check_row_end( file_exists( TD_INSTALL .'install.lock' ), array( 'warn_msg' => 'The Install Center was unable to create a lock file to prevent unauthorized re-installations. We strongly recommend renaming or deleting this <i>install</i> directory. (Note that once you rename / delete this directory, you will no longer be able to access this security check page.)' ) );

        $checks_html .= $this->check_row_start( 'Installation Cache' );
        $checks_html .= $this->check_row_end( ( count( $this->trellis->cache->data['install'] ) <= 1 ), array( 'fail_msg' => 'Your installation data still exists in the cache. This means that someone could steal sensitive information such as your database password. Please manually delete <i>'. $this->trellis->config['cache_path'] . base64_encode( 'install'. ( ( $this->trellis->config['flatfile_key'] ) ? '.'. $this->trellis->config['flatfile_key'] : '' ) ) .'.A5</i>. (Note that once you rename / delete this file, you will no longer be able to access this security check page.)' ) );

        # TODO: update security documentation link

        $checks_html .= "<tr>
                    <td class='option1' colspan='2'>More Security Information</td>
                    </tr>
                    <tr>
                        <td class='infopop' colspan='2' style='padding-top:8px;font-size:12px;'>If you would like to learn more about what you can do to keep Trellis Deck secure, check out our <a href='#'>documentation</a>.<br /><br />Please note that anyone can access this page. If an outside user enters the address to this page in their browser, they can see your security checks. Therefore we strongly recommending renaming or deleting this <i>install</i> directory for added security.</td>
                    </tr>
                    </table>";

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Security Checks' ) ."
                        ". $checks_html ."
                        ". $this->trellis->skin->formtail( '<a href="index.php?step=security" class="button">Check Again</a>&nbsp;<a href="'. $this->trellis->config['hd_url'] .'/admin.php" class="button">Go To My Trellis Desk ACP &raquo;</a>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 'security' );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Locked
    #=======================================

    private function locked()
    {
        $this->output = '';

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Install Center Locked' ) ."
                        ". $this->trellis->skin->group_row_with_p( '<p>The Install Center has been locked. To continue, please delete the <i>'. TD_INSTALL .'install.lock</i> file.</p>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 0 );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Already Installed
    #=======================================

    private function already_installed()
    {
        $this->output = '';

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->group_title( 'Already Installed' ) ."
                        ". $this->trellis->skin->group_row_with_p( '<p>Good news! Trellis Desk is already installed. If you would like to re-install, please rename or delete your configuration file (<i>'. TD_PATH .'config.php</i>).</p>' ) ."
                        ". $this->trellis->skin->formtail( '<a href="'. $this->trellis->config['hd_url'] .'/admin.php" class="button">Go To My Trellis Desk ACP &raquo;</a>' ) ."
                        </div>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->set_step( 'security' );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Version Check
    #=======================================

    private function version_check()
    {
        $version_check_url = 'http://dev.sogonphp.com/stuff/versioncheck.php';

        if ( ini_get('allow_url_fopen') )
        {
            $opts = array( 'http' => array(
                                           'method'        => 'GET',
                                           'timeout'    => 4,
                                           ) );

            $context = stream_context_create( $opts );

            $response = @ file_get_contents( $version_check_url, null, $context );
        }
        elseif ( function_exists('curl_version') )
        {
            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 4 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 4 );
            curl_setopt( $ch, CURLOPT_URL, $version_check_url );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

            $response = curl_exec($ch);

            curl_close($ch);
        }

        return $response;
    }

    #=======================================
    # @ Get Progress
    #=======================================

    private function get_progress()
    {
        $progress = 18;

        if ( $this->trellis->cache->data['install']['info_db'] ) $progress += 10;
        if ( $this->trellis->cache->data['install']['info_admin'] ) $progress += 10;
        if ( $this->trellis->cache->data['install']['info_antispam'] ) $progress += 7;
        if ( $this->trellis->cache->data['install']['info_email'] ) $progress += 7;
        if ( $this->trellis->cache->data['install']['info_other'] ) $progress += 10;

        return $progress;
    }

    #=======================================
    # @ Check Row Start
    #=======================================

    private function check_row_start($title)
    {
        return "<tr>
                    <td class='option1' width='80%'>". $title ."</td>
                    <td class='option1' width='20%' align='right'>";
    }

    #=======================================
    # @ Check Row End
    #=======================================

    private function check_row_end($result, $params=array())
    {
        if ( $result )
        {
            return "<span style='color:#49701B'>Passed</span></td>
                    </tr>";
        }
        elseif ( $params['warn_msg'] )
        {
            return "<span style='color:#D85C08'>Warning</span></td>
                        </tr>
                        <tr>
                            <td class='infopop' colspan='2' style='padding-top:8px;font-size:12px;'>". $params['warn_msg'] ."</td>
                        </tr>";
        }
        elseif ( $params['fail_msg'] )
        {
            $this->checks_fatal = 1;

            return "<span style='color:#AC241A'>Failed</span></td>
                    </tr>
                    <tr>
                        <td class='infopop' colspan='2' style='padding-top:8px;font-size:12px;color:#AC241A'>". $params['fail_msg'] ."</td>
                    </tr>";
        }
        else
        {
            return "<span style='color:#AC241A'>Failed</span></td>
                        </tr>";
        }
    }

    #=======================================
    # @ Prechecks
    #=======================================

    private function prechecks()
    {
        if ( ! $this->trellis->cache->data['install']['do_checks'] ) $this->check();
    }

    #=======================================
    # @ Generate Key
    #=======================================

    private function generate_key($length)
    {
        $string = '';

        for ( $i=0; $i<$length; $i++ )
        {
            $type = mt_rand( 0, 2 );

            if ( $type == 0 )
            {
                $string .= chr( 65 + mt_rand( 0, 25 ) );
            }
            elseif ( $type == 1 )
            {
                $string .= chr( 97 + mt_rand( 0, 25 ) );
            }
            elseif ( $type == 2 )
            {
                $string .= mt_rand( 0, 9 );
            }
        }

        return $string;
    }

    #=======================================
    # @ Return Bytes
    #=======================================

    private function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val{strlen($val)-1});

        switch($last)
        {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    #=======================================
    # @ Format Size
    #=======================================

    private function format_size($bytes)
    {
        if ( $bytes < 1024 )
        {
            return $bytes .' Bytes';
        }

        $kb = $bytes / 1024;

        if ( $kb < 1024 )
        {
            return round( $kb, 2 ) .' KB';
        }

        $mb = $kb / 1024;

        if ( $mb < 1024 )
        {
            return round( $mb, 2 ) .' MB';
        }
    }

}

?>