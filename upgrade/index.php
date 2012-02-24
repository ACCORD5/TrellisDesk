<?php

/*
#======================================================
|    Trellis Desk
|    =====================================
|    By DJ Tarazona (dj@accord5.com)
|    (c) 2008 ACCORD5
|    http://www.trellisdesk.com/
|    =====================================
|    Email: sales@accord5.com
#======================================================
|    @ Version: v1.0 RC 2 Build 10032251
|    @ Version Int: 100.3.2.251
|    @ Version Num: 10032251
|    @ Build: 0251
#======================================================
|    | Trellis Desk Upgrade Center
#======================================================
*/

#=============================
# Safe and Secure
#=============================

ob_start();

ini_set( 'register_globals', 0 );

if ( function_exists('date_default_timezone_get') )
{
     date_default_timezone_set( date_default_timezone_get() );
}

if ( @ini_get( 'register_globals' ) )
{
    while ( list( $key, $value ) = each( $_REQUEST ) )
    {
        unset( $$key );
    }
}

#=============================
# Itsy Bitsy Stuff
#=============================

define( 'VER_NUM', 10032251 );
define( 'VER_HUM', 'v1.0 RC 2' );

error_reporting( E_ERROR | E_WARNING | E_PARSE );

#=============================
# Define Our Paths
#=============================

define( "TD_PATH", "../" );
define( 'TD_INC', TD_PATH ."includes/" );
define( 'TD_SRC', TD_PATH ."sources/" );
define( 'TD_SKIN', TD_PATH ."skin/" );

define( 'TD_DEBUG', false );

#=============================
# Main Class
#=============================

require_once TD_INC . "ifthd.php";
$ifthd = new ifthd(1);

#=============================
# Cleaning Time!
#=============================

if ( ! $ifthd->input['do_login'] && $ifthd->cache->data['usessions'] )
{
    $killed = 0;
    $to_cache = array();
            
    foreach( $ifthd->cache->data['usessions'] as $usid => $usdata )
    {
        if ( $usdata['s_time'] < ( time() - ( 60 * 60 ) ) )
        {
            $killed = 1;
        }
        else
        {
            $to_cache[ $usid ] = $usdata;
        }
    }
    
    $ifthd->core->add( 'usessions', $to_cache, 1 );
}

#=============================
# The Good Stuff
#=============================

if ( $ifthd->input['do_login'] )
{
    $ifthd->user = do_login();
}
else
{
    $ifthd->user = load_session();
}

if ( $ifthd->input['do'] )
{
    require_once "./up_". $ifthd->input['do'] ."/index.php";
    $run_class = 'up_'. $ifthd->input['do'];
    $run = new $run_class();
    $run->ifthd =& $ifthd;
    $run->auto_run();
}
else
{
    show_main();
}

#=============================
# Da Functions
#=============================

function show_main()
{
    global $ifthd;
    
    set_steps( 'Upgrade Trellis Desk', array( 0 => 'Introduction' ) );
    set_titles( array( 0 => 'Welcome to Trellis Desk' ) );    
    
    $content = "";
    $remote_check = 0;
    
    if ( ini_get('allow_url_fopen') )
    {
        $version_check = file_get_contents( 'http://core.accord5.com/trellis/version_check.php?type=text' );
        
        if ( intval( $version_check ) > VER_NUM )
        {
            $version_txt = "<span style='color:#D85C08'>There is a newer version of Trellis Desk available for <a href='http://www.accord5.com/trellis'>download</a>.  We recommended downloading the latest version before continuing the upgrade.</span>";
        }
        else
        {
            $version_txt = "It looks like you have the latest version of Trellis Desk downloaded (not installed).";
            
            $remote_check = 1;
        }
    }
    else
    {
        $version_txt = "Due to your PHP's security settings, we were unable to check for the latest version of Trellis Desk.  We recommend checking the <a href='http://www.accord5.com/trellis'>Trellis Desk product page</a> to make sure you have the latest version.";
        
        $remote_check = 1;
    }
    
    $content .= "<div class='groupbox'>About the Upgrade Center</div>
                <div class='option1'>Hello and welcome to the Trellis Desk Upgrade Center. The Upgrade Center will guide you through the upgrade process to the latest version of Trellis Desk.  We highly recommend that you backup all files and databases before continuing.  On behalf of ACCORD5, we thank you for choosing and supporting Trellis Desk.</div>
                <br />
                <div class='groupbox'>Upgrade History</div>
                <table width='100%' cellpadding='0' cellspacing='0'>
                <tr>
                    <th width='12%' align='left'>ID</th>
                    <th width='34%' align='left'>Name</th>
                    <th width='26%' align='left'>Date</th>
                    <th width='28%' align='left'>User</th>
                </tr>";
    
    $ifthd->core->db->construct( array(
                                          'select'    => 'all',
                                          'from'        => 'upg_history',
                                          'order'        => array( 'verid' => 'DESC' ),
                                  )       );

    $ifthd->core->db->execute();
    
    $row_count = 0; // Initialize for Security

    while( $u = $ifthd->core->db->fetch_row() )
    {
        if ( ! $our_ver )
        {
            $our_ver = $u['verid'];
        }
        
        $row_count ++;
        
        ( $row_count & 1 ) ? $row_class = 'option1-med' : $row_class = 'option2-med';
        
        $u['date'] = $ifthd->ift_date( $u['date'], '', 0, 0, 1 );
        
        $content .= "<tr>
                <td class='{$row_class}'>{$u['verid']}</td>
                <td class='{$row_class}'>{$u['verhuman']}</td>
                <td class='{$row_class}'>{$u['date']}</td>
                <td class='{$row_class}'>{$u['username']}</td>
            </tr>";
    }

    if ( $our_ver < VER_NUM )
    {
        $upgrades = array( '10031234', '10032251' );

        while( list( , $upgrade ) = each( $upgrades ) )
        {
            if ( $next_upgrade )
            {
                if ( $upgrade < $next_upgrade && $upgrade > $our_ver )
                {
                    $next_upgrade = $upgrade;
                }
            }
            else
            {
                if ( $upgrade > $our_ver )
                {
                    $next_upgrade = $upgrade;
                }
            }
        }

        if ( ! file_exists( "./up_". $next_upgrade ."/index.php" ) )
        {
            $error = "Could not find required upgrade file './up_". $next_upgrade ."/index.php'.";
            
            $next_upgrade = 0;
        }
    }
    else
    {
        $alert = "Trellis Desk is up-do-date!";
    }
    
    if ( $next_upgrade && $remote_check )
    {
        $version_txt .= " Click the button below to begin the upgrade process.";
    }
                
    $content .= "</table>
                <br />
                <div class='groupbox'>Version Check</div>
                <div class='option1'>Your Version: ". VER_HUM ." (". VER_NUM .")<br /><br />". $version_txt ."</div>";
    
    if ( $next_upgrade )
    {
        $content .= "<div class='formtail'><div class='fb_pad'><a href='index.php?do=". $next_upgrade ."' class='fake_button'>Begin Upgrade!</a></div></div>";
    }
    
    if ( $error ) $error = "<div class='critical'>{$error}</div>";
    
    if ( $alert ) $alert = "<div class='alert'>{$alert}</div>";
        
    do_output( $error . $alert . $content, 0 );
}

function do_login()
{
    global $ifthd;
    
    if ( ! $ifthd->input['username'] )
    {
        show_login( 'Please enter a username.' );
    }
    
    if ( ! $ifthd->input['password'] )
    {
        show_login( 'Please enter a password.' );
    }
    
    $ifthd->core->db->construct( array(
                                           'select'    => array( 'id', 'name', 'password', 'pass_salt', 'ugroup' ),
                                           'from'        => 'users',
                                            'where'    => array( 'name|lower', '=', strtolower( $ifthd->input['username'] ) ),
                                            'limit'    => array( 0, 1 ),
                                     )     );

    $ifthd->core->db->execute();

    if ( ! $ifthd->core->db->get_num_rows() )
    {
        show_login( 'Username / password combination incorrect.' ); 
    }

    $mem = $ifthd->core->db->fetch_row();

    if ( sha1( md5( $ifthd->input['password'] . $mem['pass_salt'] ) ) == $mem['password'] )
    {
        if ( $mem['ugroup'] != 4 )
        {
            show_login( 'You do not have permission to access the Upgrade Center.' ); 
        }
        
        $new_session = md5( 'u' . time() . $mem['id'] . uniqid( rand(), true ) );

        $to_cache[ $new_session ] = array(
                                        's_id'            => $new_session,
                                        's_uid'            => $mem['id'],
                                        's_uname'        => $mem['name'],
                                        's_ipadd'        => $ifthd->input['ip_address'],
                                        's_time'        => time(),
                                        );
        
        $ifthd->core->add( 'usessions', $to_cache );
        
        $ifthd->set_cookie( 'upgsid', $new_session, time() + ( 60 * 60 ) );
        
        return $mem;
    }
    else
    {
        show_login( 'Username / password combination incorrect.' ); 
    }
}

function load_session()
{
    global $ifthd;
    
    if ( $upg_sid = $ifthd->get_cookie( 'upgsid' ) )
    {
        if ( ! $ifthd->cache->data['usessions'][ $upg_sid ] )
        {
            show_login();
        }
        
        if ( $ifthd->cache->data['usessions'][ $upg_sid ]['s_time'] < ( time() - ( 60 * 60 ) ) )
        {
            show_login();
        }
        
        if ( $ifthd->cache->data['usessions'][ $upg_sid ]['s_ipadd'] != $ifthd->input['ip_address'] )
        {
            show_login();
        }
        
        $ifthd->core->db->construct( array(
                                               'select'    => array( 'id', 'name', 'ugroup' ),
                                               'from'        => 'users',
                                                'where'    => array( 'id', '=', $ifthd->cache->data['usessions'][ $upg_sid ]['s_uid'] ),
                                                'limit'    => array( 0, 1 ),
                                         )     );
    
        $ifthd->core->db->execute();
    
        if ( ! $ifthd->core->db->get_num_rows() )
        {
            show_login(); 
        }
    
        $mem = $ifthd->core->db->fetch_row();
        
        if ( $mem['ugroup'] != 4 )
        {
            show_login( 'You do not have permission to access the Upgrade Center.' ); 
        }
        
        return $mem;
    }
    else
    {
        show_login();
    }
}

function show_login($error='')
{
    global $ifthd;
    
    $ifthd->delete_cookie( 'upgsid' );
    
    set_steps( 'Upgrade Trellis Desk', array( 0 => 'Login' ) );
    set_titles( array( 0 => 'Login to the Upgrade Center' ) );    
    
    $content = "";
    
    if ( $error ) $content .= "<div class='critical'>{$error}</div>";
    
    $content .= "<form action='index.php' method='post'>
                <input type='hidden' name='do_login' value='1' />
                <div class='groupbox'>Log In</div>
                <div class='option1'><input type='text' name='username' id='username' value='Username' onfocus=\"clear_value(this, 'Username')\" onblur=\"reset_value(this, 'Username')\" size='30' /></div>
                <div class='option2'><input type='password' name='password' id='password' value='password' onfocus=\"clear_value(this, 'password')\" onblur=\"reset_value(this, 'password')\" size='30' /></div>
                <div class='formtail'><input type='submit' name='submit' id='login' value='Log In' class='button' /></div>
                </form>";
    
    do_output( $content, 0 );
}

function do_output($content, $step=0)
{
    global $ifthd;
    
    $wrapper = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Trellis Desk :: Upgrade Center</title>
    <style type="text/css" media="all">
        @import "../includes/global.css";
        @import "../includes/local.css";
    </style>
    <script src='../includes/scripts/common.js' type='text/javascript'></script>
    <script src='../includes/scripts/prototype.js' type='text/javascript'></script>
    <script src='../includes/scripts/scriptaculous.js' type='text/javascript'></script>
</head>
<body>
<div id="acpwrap">

    <!-- GLOBAL: Header block -->
    <div id="header">
        <div class="lefty">
        <img src="../images/default/upgrade_logo.jpg" alt="Trellis Desk Upgrade Center" width="274" height="56" />
        </div>
    </div>

    <!-- GLOBAL: Navigation bar -->
    <div id="navbar">
        <div class="righty">
        </div>
        <div class="lefty">
        </div>
        <ul>
            <li class="current"><a href="index.php">Upgrade Center</a></li>
            <li><a href="http://docs.accord5.com/Upgrading_Trellis_Desk" target="_blank">Getting Started</a></li>
            <li><a href="http://docs.accord5.com" target="_blank">Documentation</a></li>
            <li><a href="../install/">Install Center</a></li>
            <li><a href="http://customer.accord5.com/trellis" target="_blank">Help &amp; Support</a></li>
        </ul>
    </div>

    <!-- GLOBAL: Content block -->
    <div id="content">
        <div id="acpblock">
        
            <!-- GLOBAL: Page ID -->
            <p class="pageid">Upgrade Center</p>
            
            <!-- GLOBAL: Info bar -->
            <div id="infobar">Upgrade your Trellis Desk installation to the latest available version.</div>
            
            <!-- GLOBAL: ACP inner container -->
            <!-- This is where the action happens! -->
            <div id="acpinner">
            
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td width="22%" valign="top">
                
                <!-- LEFT SIDE -->
                <!-- GLOBAL: ACP page menu -->
                <div id="acpmenu">
                    <% STEPS %>
                    <div id="acphelp"><a href="http://docs.accord5.com">View product documentation</a></div>
                </div>
                                
                </td>
                <td width="78%" valign="top">
                
                <!-- RIGHT SIDE -->
                <!-- GLOBAL: ACP page content -->
                <div id="acppage">
                    <h1><% TITLE %></h1>
                    <% CONTENT %>
                </div>

                </td>
            </tr>
            </table>

            </div>
            <br class="end" />

            <!-- GLOBAL: Copyright bar -->
            <div id='powerbar'>
                <div class='righty' style='font-weight: normal'>Designed by ACCORD5 in California</div>
                <div class='lefty'>Powered by Trellis Desk <% VERSION %>, &copy; <% YEAR %> <a href='http://www.accord5.com/' target='_blank'>ACCORD5</a></div>
            </div>

        </div>
    </div>
    <div id="close">
    <div class="righty"></div>
    <div class="lefty"></div>
    </div>
</div>
</body>
</html>
EOF;
    
    $wrapper = str_replace( "<% STEPS %>"    , build_steps_list($step)    , $wrapper );
    $wrapper = str_replace( "<% TITLE %>"    , get_page_title($step)        , $wrapper );
    $wrapper = str_replace( "<% CONTENT %>"    , $content                    , $wrapper );
    $wrapper = str_replace( "<% VERSION %>"    , VER_HUM                    , $wrapper );
    $wrapper = str_replace( "<% YEAR %>"    , date('Y')                    , $wrapper );

    header ('Content-type: text/html; charset=utf-8');

    print $wrapper;
    
    $ifthd->core->shut_down();

    exit();
}

function set_steps($our_category, $our_steps)
{
    global $category, $steps;
    
    $category = $our_category;
    $steps = $our_steps;
}

function set_titles($our_titles)
{
    global $titles;
    
    $titles = $our_titles;
}

function build_steps_list($step)
{
    global $category, $input, $steps;
    
    $html = '<div class="menucat"><a href="index.php">'. $category .'</a></div><ul>';
    
    while( list( $num, $name ) = each( $steps ) )
    {
        if ( $step == $num )
        {
            $html .= "<li><b>". $name ."</b></li>";
        }
        else
        {
            $html .= "<li>". $name ."</li>";
        }
    }
    
    $html .= '</ul>';
    
    return $html;
}

function get_page_title($step)
{    
    global $titles;
    
    return $titles[ $step ];
}

class td_parser {

    function startElement($parser, $name, $attr)
    {
        $this->xml_current_element = $name;
        
        $my_var = 'xml_'. $name;
        $this->$my_var = "";
    }

    function endElement($parser, $name)
    {
        $elements = array( 'tname', 'tcontent' );
        $elementsb = array( 'sk_name', 'sk_img_dir', 'sk_author', 'sk_author_email', 'sk_author_web', 'sk_notes', 'sk_css' );

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

        if( strcmp( $name, "skin_info" ) == 0 )
        {
            while ( list( , $element ) = each( $elementsb ) )
            {
                $my_var = 'xml_'. $element;
                $this->xml_skin_info[ $element ] = base64_decode( preg_replace( "/\s/", "", $this->$my_var ) );
            }
        }
    }

    function characterData($parser, $data)
    {
        $elements = array( 'tname', 'tcontent', 'sk_name', 'sk_img_dir', 'sk_author', 'sk_author_email', 'sk_author_web', 'sk_notes', 'sk_css' );

        while ( list( , $element ) = each( $elements ) )
        {
            if( $this->xml_current_element == $element )
            {
                $my_var = 'xml_'. $element;
                $this->$my_var .= $data;
            }
        }
    }

    function parseFile($xml_file)
    {
        $this->xml_skin_info = array();
        $this->xml_templates = array();
        
        $xml_parser = xml_parser_create();

        xml_set_object( $xml_parser, $this );

        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "characterData");

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

        return array( $this->xml_skin_info, $this->xml_templates );
    }
}

?>