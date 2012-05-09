<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_iskin extends td_class_askin {

    private $progress_bar = null;
    private $current_step = null;
    protected $javascript = '';

    #=======================================
    # @ Constructor
    #=======================================

    function __construct($trellis)
    {
        $this->trellis = $trellis;

        $this->wrapper = $this->get_wrapper();
    }

    #=======================================
    # @ Do Output
    #=======================================

    public function do_output($extra='')
    {
        #=============================
        # Sidebar
        #=============================

        $steps = array(
                       0            => 'Welcome',
                       'check'        => 'System Checks',
                       1            => 'Guided or Advanced',
                       2            => 'Database Information',
                       3            => 'Create Admin User',
                       4            => 'Configure Anti-Spam Settings',
                       5            => 'Configure Email Settings',
                       6            => 'Configure Other Settings',
                       7            => 'Review Setup',
                       'install'    => 'A Little Bit of Magic', # TODO: generate language, skin, and configuration files
                       'security'    => 'Installation Complete',
                       );

        $menu_items = array();

        foreach ( $steps as $key => $title )
        {
            if ( isset( $this->current_step ) && $this->current_step === $key )
            {
                $menu_items[] = '<a href="index.php?step='. $key .'"><strong>'. $title .'</strong></a>';
            }
            else
            {
                $menu_items[] = '<a href="index.php?step='. $key .'">'. $title .'</a>';
            }
        }

        if ( isset( $this->progress_bar ) )
        {
            $menu_items[] = '<div style="margin:1px 0 0 0;float:right"><strong>'. $this->progress_bar .'%</strong></div><div id="progress" style="margin:5px 30px 0 0;height:12px"></div>';

            $this->add_javascript( '$("#progress").progressbar({ value: '. $this->progress_bar .' });' );
        }

        $this->trellis->skin->add_sidebar_list( 'Install Trellis Desk', $menu_items );

        #=============================
        # Gather Some Info
        #=============================

        $exe_time = $this->trellis->end_timer();

        /**********************************************************************/
        /* REMOVAL OF THE COPYRIGHT WITHOUT PURCHASING COPYRIGHT REMOVAL WILL */
        /* VIOLATE THE LICENSE YOU AGREED TO WHEN DOWNLOADING AND REGISTERING */
        /* THIS PORDUCT.  IF THIS HAPPENS, IT COULD RESULT IN REMOVAL OF THIS */
        /* SYSTEM AND POSSIBLY CRIMINAL CHARGES.  THANK YOU FOR UNDERSTANDING */
        /***********************************************************************/

        $copyright = "UG93ZXJlZCBieSA8c3Ryb25nPjxhIGhyZWY9Imh0dHA6Ly93d3cuYWNjb3JkNS5jb20vdHJlbGxpcyIgdGFyZ2V0PSJfYmxhbmsiPlRyZWxsaXMgRGVzazwvYT4gdjIuMDwvc3Ryb25nPiAmbmJzcDsgJmNvcHk7IDIwMTAgPGEgaHJlZj0iaHR0cDovL3d3dy5hY2NvcmQ1LmNvbSIgdGFyZ2V0PSJfYmxhbmsiPkFDQ09SRDU8L2E+PGJyIC8+PGVtPkRlc2lnbmVkIGJ5IDxhIGhyZWY9Imh0dHA6Ly93d3cuYWNjb3JkNS5jb20iPkFDQ09SRDU8L2E+IGluIENhbGlmb3JuaWE8L2VtPg==";

        #=============================
        # Generate HTML
        #=============================

        $output = $this->get_wrapper();

        $this->add_javascript( '$("button, input:submit, a.button").button();' );

        $output = str_replace("<% SIDEBAR %>"        , $this->get_sidebar()             , $output);
        $output = str_replace("<% MAIN_CONTENT %>"    , $this->outhtml                 , $output);
        $output = str_replace("<% COPYRIGHT %>"        , base64_decode( $copyright )    , $output);
        $output = str_replace("<% JAVASCRIPT %>"    , $this->get_javascript_html()    , $output);

        #=============================
        # Language
        #=============================

        foreach( $this->trellis->lang as $lang_key => $lang_value )
        {
            $output = str_replace("{lang.". $lang_key ."}", $lang_value, $output);
        }

        #=============================
        # Final Bits! :D
        #=============================

        $output = str_replace("<! IMG_DIR !>"    , "../skins/s1/images/"                                            , $output);
        $output = str_replace("<! USER_NAME !>"    , $this->trellis->user['name']                                    , $output);
        $output = str_replace("<! USER_ID !>"    , $this->trellis->user['id']                                    , $output);
        $output = str_replace("<! TD_NAME !>"    , $this->trellis->cache->data['settings']['general']['hd_name']    , $output);
        $output = str_replace("<! TD_URL !>"    , $this->trellis->config['hd_url']                                , $output);
        $output = str_replace("<! IMG_URL !>"    , $this->trellis->config['hd_url'] ."/images/". $this->img_dir    , $output);

        header ('Content-type: text/html; charset=utf-8');

        print $output;

        if ( TD_DEBUG )
        {
            echo "<br /><br />------------------<br /><br />". $this->trellis->db->queries_ran;
        }

        ob_flush();
        flush();

        $this->trellis->shut_down();

        exit();
    }

    #=======================================
    # @ Set Progress Bar
    #=======================================

    public function set_progress_bar($percent)
    {
        $this->progress_bar = $percent;
     }

    #=======================================
    # @ Set Step
    #=======================================

    public function set_step($step)
    {
        $this->current_step = $step;
     }

    #=======================================
    # @ Add To Javascript
    #=======================================

    public function add_javascript($html)
    {
        $this->javascript .= $html;
     }

    #=======================================
    # @ Get Javascript HTML
    #=======================================

    public function get_javascript_html()
    {
        if ( $this->javascript )
        {
            return "\n" .'<script type="text/javascript">' ."\n". '$(function() {'. "\n" . $this->javascript ."\n" .'});' ."\n". '</script>';
        }
        else
        {
            return false;
        }
    }

    #=======================================
    # @ Get Wrapper HTML
    #=======================================

    protected function get_wrapper()
    {
        $html = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Trellis Desk Install Center</title>
    <link href="../skins/s1/css/jquery-ui.css" rel="stylesheet" type="text/css" media="screen" />
    <link href="../skins/s1/css/tiburon.css" rel="stylesheet" type="text/css" media="screen" />
    <script src="../skins/s1/scripts/livevalidation.js" type="text/javascript"></script>
    <script src="../skins/s1/scripts/jquery.js" type="text/javascript"></script>
    <script src="../skins/s1/scripts/jquery-ui.js" type="text/javascript"></script>
    <script src="../skins/s1/scripts/common_acp.js" type="text/javascript"></script>
</head>

<body>

<div id="navwrap">
    <div id="navbar">
        <a href="index.php" title="Trellis Desk"><img src="<! IMG_DIR !>/navbar_logo.png" width="86" height="36" alt="Trellis Desk" style="float: left;" /></a>
    </div>

    <div id="navexit"><strong>Install Center</strong></div>
    <div class="navclear"></div>
</div>

<div id="tdcontent">

    <!-- LEFT SIDE -->
    <div id="coreleft">

        <% SIDEBAR %>

    </div>

    <!-- RIGHT SIDE -->
    <div id="coreright">

        <!-- Main content -->

        <% MAIN_CONTENT %>

    </div>

    <div class="navclear"></div>

</div>

<div id="copyright">
    <!-- Trellis Desk is a free product. All we ask is that you keep the copyright intact. -->
    <!-- Purchase our copyright removal service at http://www.accord5.com/copyright  -->
    <% COPYRIGHT %>
</div>

<% JAVASCRIPT %>
</body>
</html>
EOF;

        return $html;
    }
}

?>