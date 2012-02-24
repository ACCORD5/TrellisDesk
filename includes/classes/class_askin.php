<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_askin {

    protected $outhtml                = '';
    protected $javascript            = '';
    protected $img_dir                = 'tiburon';
    protected $main_nav                = array();
    protected $sub_nav                = array();
    protected $popup_nav            = array();
    protected $sidebar_blocks        = array();
    protected $sblocks_toogle        = array();
    protected $groups                = array();
    protected $active_link            = 1;
    public $preserve_input            = 0;
    protected $tickets_awaiting        = 0;
    protected $tickets_my            = 0;
    protected $tickets_unassigned    = 0;
    protected $output                = '';

    #=======================================
    # @ Constructor
    #=======================================

    function __construct(&$trellis)
    {
        $this->trellis = $trellis;

        $this->trellis->load_lang('global');

        $this->wrapper = $this->get_wrapper();

        $this->add_main_nav( 1,    1,    '{lang.menu_acp_home}',            'admin.php?section=admin'    );
        $this->add_main_nav( 2,    2,    '{lang.menu_my_helpdesk}',        'admin.php?section=manage'    );
        $this->add_main_nav( 3,    3,    '{lang.menu_look_feel}',        'admin.php?section=look'    );
        $this->add_main_nav( 4,    4,    '{lang.menu_tools_settings}',    'admin.php?section=tools'    );
        #$this->add_main_nav( 5,    5,    '{lang.menu_plugins_modules}',    'admin.php?section=extras'    );
        $this->add_main_nav( 6,    6,    '{lang.menu_help_support}'                                    );

        $this->add_sub_nav( 1,    1,    '{lang.menu_overview}',             'admin.php?section=admin'                                            );
        $this->add_sub_nav( 1,    2,    '{lang.menu_my_acp_settings}',        'admin.php?section=admin&amp;page=settings'                            );
        $this->add_sub_nav( 1,    3,    '{lang.menu_about_trellis_desk}',    'admin.php?section=admin&amp;page=about'                            );
        $this->add_sub_nav( 1,    4,    'ACCORD5.com',                        'http://www.accord5.com/'                                            );

        $this->add_sub_nav( 2,    1,    '{lang.menu_ticket_control}',         'admin.php?section=manage'                        , 'ticket_control'    );
        $this->add_sub_nav( 2,    2,    '{lang.menu_depart_control}',         'admin.php?section=manage&amp;page=departs'        , 'depart_control'    );
        $this->add_sub_nav( 2,    3,    '{lang.menu_user_control}',         'admin.php?section=manage&amp;page=users'        , 'user_control'    );
        $this->add_sub_nav( 2,    4,    '{lang.menu_news_control}',         'admin.php?section=manage&amp;page=news'                            );
        $this->add_sub_nav( 2,    5,    '{lang.menu_kb_control}',             'admin.php?section=manage&amp;page=articles'    , 'kb_control'        );
        $this->add_sub_nav( 2,    6,    '{lang.menu_pages_control}',         'admin.php?section=manage&amp;page=pages'                            );

        $this->add_sub_nav( 3,    1,    '{lang.menu_skins_manager}',         'admin.php?section=look'                                            );
        $this->add_sub_nav( 3,    2,    '{lang.menu_langs_manager}',        'admin.php?section=look&amp;page=langs'                                );
        $this->add_sub_nav( 3,    3,    '{lang.menu_email_templates}',        'admin.php?section=look&amp;page=emails'                            );
        $this->add_sub_nav( 3,    4,    '{lang.menu_find_more_resources}',    'http://www.accord5.com/'                                            );

        $this->add_sub_nav( 4,    1,    '{lang.menu_helpdesk_settings}',     'admin.php?section=tools'                                            );
        $this->add_sub_nav( 4,    2,    '{lang.menu_maintenance}',             'admin.php?section=tools&amp;page=maint'        , 'maintenance'        );
        $this->add_sub_nav( 4,    3,    '{lang.menu_backups}',                 'admin.php?section=tools&amp;page=backup'                            );
        $this->add_sub_nav( 4,    4,    '{lang.menu_log_center}',             'admin.php?section=tools&amp;page=logs'                                );

        $this->add_sub_nav( 5,    1,    '{lang.menu_plugin_manager}',         'admin.php?section=extras'                                            );
        $this->add_sub_nav( 5,    2,    '{lang.menu_module_manager}',        'admin.php?section=extras&amp;page=modules'                            );
        $this->add_sub_nav( 5,    3,    '{lang.menu_find_more_resources}',    'http://www.accord5.com/'                                            );

        $this->add_sub_nav( 6,    1,    '{lang.menu_documentation}',         'http://docs.accord5.com/'                                            );
        $this->add_sub_nav( 6,    2,    '{lang.menu_p2p_support}',            'http://forums.accord5.com/index.php?showforum=22'                    );
        $this->add_sub_nav( 6,    3,    '{lang.menu_official_support}',        'http://customer.accord5.com/'                                        );
        $this->add_sub_nav( 6,    4,    '{lang.menu_bug_tracker}',             'https://github.com/ACCORD5/TrellisDesk/issues'                                );

        $this->add_popup_nav( 'ticket_control', 1, '{lang.menu_manage_tickets}',        'admin.php?section=manage&amp;page=tickets'        );
        $this->add_popup_nav( 'ticket_control', 2, '{lang.menu_manage_priorities}',        'admin.php?section=manage&amp;page=priorities'    );
        $this->add_popup_nav( 'ticket_control', 3, '{lang.menu_manage_statuses}',        'admin.php?section=manage&amp;page=statuses'    );
        $this->add_popup_nav( 'ticket_control', 4, '{lang.menu_manage_flags}',            'admin.php?section=manage&amp;page=flags'        );
        $this->add_popup_nav( 'ticket_control', 5, '{lang.menu_manage_reply_templates}','admin.php?section=manage&amp;page=rtemplates'    );

        $this->add_popup_nav( 'depart_control', 1, '{lang.menu_manage_departs}',        'admin.php?section=manage&amp;page=departs'        );
        $this->add_popup_nav( 'depart_control', 2, '{lang.menu_custom_depart_fields}',    'admin.php?section=manage&amp;page=cdfields'    );

        $this->add_popup_nav( 'user_control',    1, '{lang.menu_manage_users}',            'admin.php?section=manage&amp;page=users'        );
        $this->add_popup_nav( 'user_control',    2, '{lang.menu_manage_groups}',            'admin.php?section=manage&amp;page=groups'        );
        $this->add_popup_nav( 'user_control',    3, '{lang.menu_custom_profile_fields}',    'admin.php?section=manage&amp;page=cpfields'    );

        $this->add_popup_nav( 'kb_control',     1,     '{lang.menu_manage_articles}',        'admin.php?section=manage&amp;page=articles'    );
        $this->add_popup_nav( 'kb_control',     2,    '{lang.menu_manage_categories}',    'admin.php?section=manage&amp;page=categories'    );

        $this->add_popup_nav( 'maintenance',     1,     '{lang.menu_maintenance_cleanup}',    'admin.php?section=tools&amp;page=cleanup'    );
        $this->add_popup_nav( 'maintenance',     2,    '{lang.menu_maintenance_cache}',    'admin.php?section=tools&amp;page=cache'    );
        $this->add_popup_nav( 'maintenance',     3,    '{lang.menu_maintenance_recount}',    'admin.php?section=tools&amp;page=recount'    );
    }

    #=======================================
    # @ Do Output
    #=======================================

    public function do_output($extra='')
    {
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

        $this->output = $this->get_wrapper();

        if ( $extra['error'] )
        {
            $this->trellis->load_lang('error');

            $this->outhtml = $this->get_error_html( $extra );

            $this->output = str_replace("<% MAIN_NAV %>"    , '', $this->output);
            $this->output = str_replace("<% SUB_NAV %>"        , '', $this->output);
            $this->output = str_replace("<% POPUP_NAV %>"    , '', $this->output);
            $this->output = str_replace("<% HOVER_BAR %>"    , '', $this->output);
        }
        else
        {
            $this->output = str_replace("<% MAIN_NAV %>"    , $this->get_main_nav()        , $this->output);
            $this->output = str_replace("<% SUB_NAV %>"        , $this->get_sub_nav()         , $this->output);
            $this->output = str_replace("<% POPUP_NAV %>"    , $this->get_popup_nav()    , $this->output);
            $this->output = str_replace("<% HOVER_BAR %>"    , $this->get_hover_bar()    , $this->output);
        }

        if ( $extra['login'] )
        {
            $red_link = '<a href="admin.php">{lang.login}</a>';
        }
        else
        {
            $red_link = '<a href="admin.php?act=logout">{lang.logout}</a>';
        }

        $messages = $this->trellis->get_messages();
        $msgs_alert = array();
        $msgs_error = array();
        $msgs_alert_html = '';
        $msgs_error_html = '';
        $msgs_output = '';

        if ( ! empty( $messages ) )
        {
            foreach( $messages as &$m )
            {
                if ( $m['type'] == 'alert' )
                {
                    $msgs_alert[] = $m['msg'];
                }
                elseif ( $m['type'] == 'error' )
                {
                    $msgs_error[] = $m['msg'];
                }
            }
        }

        if ( ! empty( $msgs_alert ) )
        {
            foreach ( $msgs_alert as &$m )
            {
                $msgs_alert_html .= '<li>'. $m .'</li>';
            }

            $msgs_output .= $this->trellis->skin->alert_wrap( '<ul>'. $msgs_alert_html .'</ul>' );
        }
        if ( ! empty( $msgs_error ) )
        {
            foreach ( $msgs_error as &$m )
            {
                $msgs_error_html .= '<li>'. $m .'</li>';
            }

            $msgs_output .= $this->trellis->skin->error_wrap( '<ul>'. $msgs_error_html .'</ul>' );
        }

        $this->output = str_replace("<% SIDEBAR %>"            , $this->get_sidebar()                     , $this->output);
        $this->output = str_replace("<% MAIN_CONTENT %>"    , $msgs_output . $this->outhtml         , $this->output);
        $this->output = str_replace("<% QUERY_COUNT %>"        , $this->trellis->db->get_query_count()    , $this->output);
        $this->output = str_replace("<% COPYRIGHT %>"        , base64_decode( $copyright )            , $this->output);
        $this->output = str_replace("<% ACTIVE_LINK %>"        , $this->active_link                    , $this->output);
        $this->output = str_replace("<% RED_LINK %>"        , $red_link                                , $this->output);
        $this->output = str_replace("<% JAVASCRIPT %>"        , $this->javascript                        , $this->output);
        $this->outhtml = '';

        #=============================
        # Language
        #=============================

        foreach( $this->trellis->lang as $lang_key => $lang_value )
        {
            $this->output = str_replace("{lang.". $lang_key ."}", $lang_value, $this->output);
        }

        #=============================
        # Final Bits! :D
        #=============================

        $this->output = str_replace("<! IMG_DIR !>"        , 'skins/s1/images'                                                , $this->output);
        $this->output = str_replace("<! USER_NAME !>"    , $this->trellis->user['name']                                    , $this->output);
        $this->output = str_replace("<! USER_ID !>"        , $this->trellis->user['id']                                    , $this->output);
        $this->output = str_replace("<! TD_NAME !>"        , $this->trellis->cache->data['settings']['general']['hd_name']    , $this->output);
        $this->output = str_replace("<! TD_URL !>"        , $this->trellis->config['hd_url']                                , $this->output);
        #$this->output = str_replace("<! IMG_URL !>"    , $this->trellis->config['hd_url'] ."/images/". $this->img_dir    , $this->output);

        header ('Content-type: text/html; charset=utf-8');

        $this->trellis->clear_messages();
        $this->trellis->shut_down();

        if ( $this->trellis->cache->data['settings']['general']['shutdown_enable'] )
        {
            $this->send_output();
        }
        else
        {
            register_shutdown_function( array( $this, 'send_output' ) );
        }

        exit();
    }

    #=======================================
    # @ AJAX Output
    #=======================================

    public function ajax_output($output='')
    {
        $this->output = $output;

        $this->trellis->shut_down();

        if ( $this->trellis->cache->data['settings']['general']['shutdown_enable'] )
        {
            $this->send_output('ajax');
        }
        else
        {
            register_shutdown_function( array( $this, 'send_output' ), 'ajax' );
        }

        exit();
    }

    #=======================================
    # @ Redirect
    #=======================================

    public function redirect($url, $append='')
    {
        if ( is_array( $url ) )
        {
            $pre_url = array(
                'section'    => $this->trellis->input['section'],
                'page'        => $this->trellis->input['page'],
                'act'        => $this->trellis->input['act'],
            );

            $url = array_merge( $pre_url, $url );

            $url = 'admin.php?'. http_build_query( $url );
        }

        $url .= $append;

        header( 'Location: '. $url );

        $this->trellis->shut_down();

        if ( $this->trellis->cache->data['settings']['general']['shutdown_enable'] )
        {
            $this->send_output();
        }
        else
        {
            register_shutdown_function( array( $this, 'send_output' ), 'redirect' );
        }
    }

    #=======================================
    # @ Send Output
    #=======================================

    public function send_output($type='')
    {
        if ( $type == 'ajax' )
        {
            print $this->output;
        }
        elseif ( $type != 'redirect' )
        {
            $this->output = str_replace("<% EXE_TIME %>", $this->trellis->end_timer(), $this->output);

            print $this->output;

            if ( TD_DEBUG )
            {
                echo "<br /><br />------------------<br /><br />". $this->trellis->db->queries_ran;
            }
        }

        if ( $this->trellis->cache->data['settings']['general']['shutdown_enable'] )
        {
            if ( ! ( $time_limit = intval( $this->trellis->cache->data['settings']['general']['shutdown_time'] ) ) ) $time_limit = 30;

            ignore_user_abort(true);
            set_time_limit( $time_limit );
            header( 'Connection: close' );
            header( 'Content-length: '. ob_get_length() );
        }

        ob_end_flush();
        flush();
        ob_end_clean();

        exit();
    }

    #=======================================
    # @ Add Javascript
    #=======================================

    public function add_javascript($js_file)
    {
        $this->javascript .= '<script src="includes/scripts/'. $js_file .'" type="text/javascript"></script>'."\n";
     }

    #=======================================
    # @ Add Skin Javascript
    #=======================================

    public function add_skin_javascript($js_file)
    {
        $this->javascript .= '<script src="skins/s1/scripts/'. $js_file .'" type="text/javascript"></script>'."\n";
     }

    #=======================================
    # @ Set Active Link
    #=======================================

    public function set_active_link($id)
    {
        $this->active_link = $id;
     }

    #=======================================
    # @ Add To Output
    #=======================================

    public function add_output($html)
    {
        $this->outhtml .= $html;
     }

    #=======================================
    # @ Add Sidebar Block
    #=======================================

    public function add_sidebar_block($title, $html, $help=0, $collapse_id='')
    {
        if ( $collapse_id ) $this->sblocks_toggle[] = $collapse_id;

        $this->sidebar_blocks[] = array( $title, $html, $help, $collapse_id );
     }

    #=======================================
    # @ Add Main Navigation Link
    #=======================================

    public function add_main_nav($id, $order, $name, $dbl_click_link='')
    {
        $this->main_nav[ $order ] = array( $id, $name, $dbl_click_link );
    }

    #=======================================
    # @ Add Sub Navigation Link
    #=======================================

    public function add_sub_nav($id, $order, $name, $link, $popup='')
    {
        $this->sub_nav[ $id ][ $order ] = array( $name, $link, $popup );
    }

    #=======================================
    # @ Add Popup Navigation Link
    #=======================================

    public function add_popup_nav($id, $order, $name, $link)
    {
        $this->popup_nav[ $id ][ $order ] = array( $name, $link );
    }

    #=======================================
    # @ Add Sidebar Menu
    #=======================================

    public function add_sidebar_menu($title, $items)
    {
        $html = '<ul class="blockmenu">';

        foreach ( $items as $data )
        {
            $html .= '<li><a href="'. $data[2] .'"';

            if ( $data[3] ) $html .= ' onclick="'. $data[3] .'"';

            $html .= '><img src="<! IMG_DIR !>/icons/'. $data[0] .'.png" alt="*" style="margin-bottom:3px" />'. $data[1] .'</a></li>';
        }

        $html .= '</ul>';

        $this->add_sidebar_block( $title, $html );
     }

    #=======================================
    # @ Add Sidebar Help
    #=======================================

    public function add_sidebar_help($title, $text)
    {
        $html = '<ul class="blockdata"><li>'. $text .'</li></ul>';

        $this->add_sidebar_block( $title, $html, 1 );
     }

    #=======================================
    # @ Add Sidebar General
    #=======================================

    public function add_sidebar_general($title, $text)
    {
        $html = '<ul class="blockdata"><li>'. $text .'</li></ul>';

        $this->add_sidebar_block( $title, $html );
     }

    #=======================================
    # @ Add Sidebar List
    #=======================================

    public function add_sidebar_list($title, $items, $collapse_id='')
    {
        $html = '<ul class="blockmenu"';

        if ( $collapse_id )
        {
            $html .= ' id="c'. $collapse_id .'"';

            if ( $this->trellis->get_cookie( 'sbc_'. $collapse_id ) ) $html .= ' style="display:none"';
        }

        $html .= '>';

        while( list( $id, $data ) = each( $items ) )
        {
            if ( is_numeric( $id ) )
            {
                $html .= '<li>'. $data .'</li>';
            }
            else
            {
                $html .= '<li id=\''. $id .'\'>'. $data .'</li>';
            }
        }

        $html .= '</ul>';

        $this->add_sidebar_block( $title, $html, 0, $collapse_id );
     }

    #=======================================
    # @ Add Sidebar List Custom
    #=======================================

    public function add_sidebar_list_custom($title, $items, $id='')
    {
        $html = '<ul class="blockmenu"';

        if ( $id ) $html .= ' id="'. $id .'"';

        $html .= '>'. $items .'</ul>';

        $this->add_sidebar_block( $title, $html );
     }

    #=======================================
    # @ Generate Main Navigation HTML
    #=======================================

    protected function get_main_nav()
    {
        $html = '<ul id="navlinks">';

        ksort( $this->main_nav );

        while( list( , $data ) = each( $this->main_nav ) )
        {
            ( $data[2] ) ? $dbl_click = ' ondblclick="document.location=\''. $data[2] .'\'"' : $dbl_click = '';

            $html .= '<li><a href="#cat'. $data[0] .'"'. $dbl_click .'>'. $data[1] .'</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }

    #=======================================
    # @ Generate Sub Navigation HTML
    #=======================================

    protected function get_sub_nav()
    {
        $html = '<div id="catbar">';

        reset( $this->main_nav );

        while( list( $id, $mdata ) = each( $this->main_nav ) )
        {
            if ( $this->sub_nav[ $id ] )
            {
                ksort( $this->sub_nav[ $id ] );

                $html .= '<div class="tab" id="cat'. $mdata[0] .'"><ul class="catlinks">';

                while( list( , $sdata ) = each( $this->sub_nav[ $id ] ) )
                {
                    if ( $sdata[2] )
                    {
                        $html .= '<li><a href="javascript:ShowContent(\'cat_'. $sdata[2] .'\')" onclick="ShowContent(\'cat_'. $sdata[2] .'\'); return true;" ondblclick="document.location=\''. $sdata[1] .'\'">'. $sdata[0] .'</a></li>';
                    }
                    else
                    {
                        $html .= '<li><a href="'. $sdata[1] .'">'. $sdata[0] .'</a></li>';
                    }
                }

                $html .= "</ul></div>\n";
            }
        }

        $html .= '</div>';

        return $html;
    }

    #=======================================
    # @ Generate Popup Navigation HTML
    #=======================================

    protected function get_popup_nav()
    {
        $html = '';

        while( list( $id, ) = each( $this->popup_nav ) )
        {
            ksort( $this->popup_nav[ $id ] );

            $html .= '<div id="cat_'. $id .'" class="cat_menu"><ul class="popup">';

            while( list( , $data ) = each( $this->popup_nav[ $id ] ) )
            {
                $html .= '<li><a href="'. $data[1] .'">'. $data[0] .'</a></li>';
            }

            $html .= "</ul></div>\n";
        }

        return $html;
    }

    #=======================================
    # @ Generate Sidebar HTML
    #=======================================

    protected function get_sidebar()
    {
        $html = '';

        while( list( , $data ) = each( $this->sidebar_blocks ) )
        {
            if ( $data[2] )
            {
                $html .= '<b class="i1"></b><b class="i2"></b><b class="i3"></b><b class="i4"></b><div class="inline">';
                $html .= '<h3>'. $data[0] .'</h3>';
                $html .= $data[1];
                $html .= '</div><b class="i4"></b><b class="i3"></b><b class="i2"></b><b class="i1e"></b>';
            }
            else
            {
                if ( $data[3] )
                {
                    $html .= '<b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b><div class="block">';

                    if ( $this->trellis->get_cookie( 'sbc_'. $data[3] ) )
                    {
                        $html .= '<h4>'. $data[0] .'<div style="float:right"><img src="<! IMG_DIR !>/icons/toggle_expand.png" id="t'. $data[3] .'" alt="*" style="margin-top:3px" /></div></h4>';
                    }
                    else
                    {
                        $html .= '<h4>'. $data[0] .'<div style="float:right"><img src="<! IMG_DIR !>/icons/toggle_collapse.png" id="t'. $data[3] .'" alt="*" style="margin-top:3px" /></div></h4>';
                    }

                    $html .= $data[1];
                    $html .= '</div><b class="b4"></b><b class="b3"></b><b class="b2"></b><b class="b1e"></b>';
                }
                else
                {
                    $html .= '<b class="b1"></b><b class="b2"></b><b class="b3"></b><b class="b4"></b><div class="block">';
                    $html .= '<h4>'. $data[0] .'</h4>';
                    $html .= $data[1];
                    $html .= '</div><b class="b4"></b><b class="b3"></b><b class="b2"></b><b class="b1e"></b>';
                }
            }
        }

        return $html;
    }

    #=======================================
    # @ Get Awaiting Tickets
    #=======================================

    protected function get_tickets_awaiting()
    {
        $sql_where = array();
        $filters = array();

        $filters[] = array( array( array( 't' => 'aua' ), '!=', 1 ), array( array( 't' => 'onhold' ), '=', 1, 'or' ) );
        $filters[] = array( array( 't' => 'closed' ), '!=', 1 );

        #=============================
        # Permissions
        #=============================

        if ( ! is_array( $this->trellis->user['g_acp_depart_perm'] ) ) $this->trellis->user['g_acp_depart_perm'] = unserialize( $this->trellis->user['g_acp_depart_perm'] );

        if ( $this->trellis->user['id'] != 1 )
        {
            $perms = array();

            if( is_array( $this->trellis->user['g_acp_depart_perm'] ) )
            {
                foreach( $this->trellis->user['g_acp_depart_perm'] as $did => $dperm )
                {
                    if ( $dperm['v'] ) $perms[] = $did;
                }
            }

            if ( empty( $perms ) ) $perms[] = 0;

            $filters[] = array( array( array( 't' => 'did' ), 'in', $perms ), array( array( 'a' => 'uid' ), '=', $this->trellis->user['id'], 'or' ) );
        }

        #=============================
        # Grab Tickets
        #=============================

        foreach( $filters as $fdata )
        {
            if ( ! empty( $sql_where ) ) $fdata[] = 'and';

            $sql_where[] = $fdata;
        }

        #$sql_where[] = array( array( 't' => 'onhold', '=', 1, 'and' ) );

        $tickets = $this->trellis->db->get( array(
                                                  'select'    => array(
                                                                     't' => array( 'id' ),
                                                                     'a' => array( array( 'uid' => 'auid' ) ),
                                                                     'ua' => array( array( 'uid' => 'uauid' ) ), // Check for assigned to others
                                                                     ),
                                                  'from'    => array( 't' => 'tickets' ),
                                                  'join'    => array(
                                                                     array( 'from' => array( 'a' => 'assign_map' ), 'where' => array( array( 't' => 'id', '=', 'a' => 'tid' ), array( $this->trellis->user['id'], '=', 'a' => 'uid', 'and' ) ) ),
                                                                     array( 'from' => array( 'ua' => 'assign_map' ), 'where' => array( array( 't' => 'id', '=', 'ua' => 'tid' ), array( $this->trellis->user['id'], '!=', 'ua' => 'uid', 'and' ) ) ), // Check for assigned to others
                                                                     ),
                                                  'where'    => $sql_where,
                                           ), 'id' );

        if ( $tickets )
        {
            foreach( $tickets as $t )
            {
                $this->tickets_awaiting ++;

                if ( $t['auid'] )
                {
                    $this->tickets_my ++;
                }
                elseif ( ! $t['uauid'] )
                {
                    $this->tickets_unassigned ++;
                }
            }
        }
    }

    #=======================================
    # @ Generate Hover Bar HTML
    #=======================================

    protected function get_hover_bar()
    {
        $html = '';

        $this->get_tickets_awaiting();

        #* links to ticket list, but ticket list still respects user's default filters, so ticket numbers may not match

        $html = '<div id="statusbar">
                <p>'. $this->trellis->lang['bar_welcome'] .' '. $this->trellis->td_timestamp( array( 'time' => time(), 'format' => 'g:i A' ) ) . $this->trellis->lang['bar_tickets_awaiting_a'] .'<a href="<! TD_URL !>/admin.php?section=manage&amp;act=tickets">'. $this->tickets_awaiting .'</a>'. $this->trellis->lang['bar_tickets_awaiting_b'] .'<a href="<! TD_URL !>/admin.php?section=manage&amp;act=tickets&amp;assigned='. $this->trellis->user['id'] .'">'. $this->tickets_my .'</a>'. $this->trellis->lang['bar_tickets_my'] .'<a href="<! TD_URL !>/admin.php?section=manage&amp;act=tickets&amp;unassigned=1">'. $this->tickets_unassigned .'</a>'. $this->trellis->lang['bar_tickets_unassigned'] .'</p>
                </div>';

        return $html;
    }

    #================================================
    # Error
    #================================================

    function error($msg, $login=0)
    {
        echo $msg;
        $this->do_output( array( 'error' => 1, 'msg' => $msg, 'login' => $login ) );
    }

    #=======================================
    # @ Revert HTML
    #=======================================

    protected function revert_html($id)
    {
        return "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=dorevert&amp;id={$id}'><img src='<! IMG_DIR !>/icons/revert.gif' alt='{lang.revert}' /></a>";
    }

    #=======================================
    # @ Default HTML
    #=======================================

    protected function default_html($id)
    {
        return "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=dodefault&amp;id={$id}'><img src='<! IMG_DIR !>/icons/default.gif' alt='{lang.default}' /></a>";
    }

    #=======================================
    # @ Setting Alert
    #=======================================

    public function setting_alert($params)
    {
        if ( $params['check'] != $params['for'] )
        {
            return "&nbsp;&nbsp;". $this->alert_tip( $params['msg'] );
        }
        else
        {
            return false;
        }
    }

    #=======================================
    # @ Yes / No Radio
    #=======================================

    public function yes_no_radio($params, $selected=0, $odefault='', $id=0)
    {
        if ( ! is_array( $params ) )
        {
            $params = array(
                            'name'        => $params,
                            'value'        => $selected,
                            'default'    => $odefault,
                            'id'        => $id,
                            );
        }

        ( isset( $params['revert'] ) && ( $params['value'] != $params['revert'] ) && $params['id'] ) ? $revert = 1 : $revert = 0;
        ( isset( $params['default'] ) && ( $params['value'] != $params['default'] ) && $params['id'] ) ? $default = 1 : $default = 0;

        if ( $this->preserve_input && isset( $this->trellis->input[ $params['name'] ] ) ) $params['value'] = $this->trellis->input[ $params['name'] ];

        $html = "";

        if ( $revert || $default )
        {
            $html .= "<div style='float:right;margin:-2px 0'>";

            if ( $revert ) $html .= $this->revert_html( $params['id'] );
            if ( $default ) $html .= $this->default_html( $params['id'] );

            $html .= "</div>";
        }

        $html .= "<label for='{$params['name']}_1'><input type='radio' name='{$params['name']}' id='{$params['name']}_1' value='1' class='radio'";

        if ( $params['value'] ) $html .= " checked='checked'";
        if ( $params['disabled'] ) $html .= " disabled='disabled'";

        $html .= " /> {lang.yes}</label>&nbsp;&nbsp;<label for='{$params['name']}_0'><input type='radio' name='{$params['name']}' id='{$params['name']}_0' value='0' class='radio'";

        if ( ! $params['value'] ) $html .= " checked='checked'";
        if ( $params['disabled'] ) $html .= " disabled='disabled'";

        $html .= " /> {lang.no}</label>";

        if ( $params['alert'] ) $html .= $this->setting_alert( $params['alert'] );

        return $html;
    }

    #=======================================
    # @ Enabled / Disabled Radio
    #=======================================

    public function enabled_disabled_radio($params, $selected=0, $odefault='', $id=0)
    {
        if ( ! is_array( $params ) )
        {
            $params = array(
                            'name'        => $params,
                            'value'        => $selected,
                            'default'    => $odefault,
                            'id'        => $id,
                            );
        }

        ( isset( $params['revert'] ) && ( $params['value'] != $params['revert'] ) && $params['id'] ) ? $revert = 1 : $revert = 0;
        ( isset( $params['default'] ) && ( $params['value'] != $params['default'] ) && $params['id'] ) ? $default = 1 : $default = 0;

        if ( $this->preserve_input && isset( $this->trellis->input[ $params['name'] ] ) ) $params['value'] = $this->trellis->input[ $params['name'] ];

        $html = "";

        if ( $revert || $default )
        {
            $html .= "<div style='float:right;margin:-2px 0'>";

            if ( $revert ) $html .= $this->revert_html( $params['id'] );
            if ( $default ) $html .= $this->default_html( $params['id'] );

            $html .= "</div>";
        }

        $html .= "<label for='{$params['name']}_1'><input type='radio' name='{$params['name']}' id='{$params['name']}_1' value='1' class='radio'";

        if ( $params['value'] ) $html .= " checked='checked'";
        if ( $params['disabled'] ) $html .= " disabled='disabled'";

        $html .= " /> {lang.enabled}</label>&nbsp;&nbsp;<label for='{$params['name']}_0'><input type='radio' name='{$params['name']}' id='{$params['name']}_0' value='0' class='radio'";

        if ( ! $params['value'] ) $html .= " checked='checked'";
        if ( $params['disabled'] ) $html .= " disabled='disabled'";

        $html .= " /> {lang.disabled}</label>";

        if ( $params['alert'] ) $html .= $this->setting_alert( $params['alert'] );

        return $html;
    }

    #=======================================
    # @ Custom Radio
    #=======================================

    public function custom_radio($params, $options='', $selected='')
    {
        if ( ! is_array( $params ) )
        {
            $params = array(
                            'name'        => $params,
                            'options'    => $options,
                            'value'        => $selected,
                            );
        }

        ( isset( $params['revert'] ) && ( $params['value'] != $params['revert'] ) && $params['id'] ) ? $revert = 1 : $revert = 0;
        ( isset( $params['default'] ) && ( $params['value'] != $params['default'] ) && $params['id'] ) ? $default = 1 : $default = 0;

        if ( $this->preserve_input && isset( $this->trellis->input[ $params['name'] ] ) ) $params['value'] = $this->trellis->input[ $params['name'] ];

        $html = "";

        if ( $revert || $default )
        {
            $html .= "<div style='float:right;margin:-2px 0'>";

            if ( $revert ) $html .= $this->revert_html( $params['id'] );
            if ( $default ) $html .= $this->default_html( $params['id'] );

            $html .= "</div>";
        }

        foreach ( $params['options'] as $key => $name )
        {
            $html .= "<label for='{$params['name']}_{$key}'><input type='radio' name='{$params['name']}' id='{$params['name']}_{$key}' value='{$key}' class='radio'";

            if ( $params['value'] == $key ) $html .= " checked='checked'";
            if ( $params['disabled'] ) $html .= " disabled='disabled'";

            $html .= " /> {$name}</label>&nbsp;&nbsp;";
        }

        if ( $params['alert'] ) $html .= $this->setting_alert( $params['alert'] );

        return $html;
    }

    #=======================================
    # @ Checkbox
    #=======================================

    public function checkbox($params, $option_name='', $selected=0, $odefault='', $id=0, $disabled=0)
    {
        if ( ! is_array( $params ) )
        {
            $params = array(
                            'name'        => $params,
                            'title'        => $option_name,
                            'value'        => $selected,
                            'default'    => $default,
                            'id'        => $id,
                            'disabled'    => $disabled,
                            );
        }

        ( isset( $params['revert'] ) && ( $params['value'] != $params['revert'] ) && $params['id'] ) ? $revert = 1 : $revert = 0;
        ( isset( $params['default'] ) && ( $params['value'] != $params['default'] ) && $params['id'] ) ? $default = 1 : $default = 0;

        if ( $this->preserve_input && isset( $this->trellis->input[ $params['name'] ] ) ) $params['value'] = $this->trellis->input[ $params['name'] ];

        $html = "";

        if ( $revert || $default )
        {
            $html .= "<div style='float:right;margin:-2px 0'>";

            if ( $revert ) $html .= $this->revert_html( $params['id'] );
            if ( $default ) $html .= $this->default_html( $params['id'] );

            $html .= "</div>";
        }

        $html .= "<label for='{$params['name']}'><input type='checkbox' name='{$params['name']}' id='{$params['name']}' value='1' class='ckbox'";

        if ( $params['value'] ) $html .= " checked='checked'";
        if ( $params['disabled'] ) $html .= " disabled='disabled'";

        $html .= " /> {$params['title']}</label>";

        if ( $params['alert'] ) $html .= $this->setting_alert( $params['alert'] );

        return $html;
    }

    #=======================================
    # @ Textfield
    #=======================================

    public function textfield($params, $value='', $odefault='', $id=0, $length=0)
    {
        if ( ! is_array( $params ) )
        {
            $params = array(
                            'name'        => $params,
                            'value'        => $value,
                            'default'    => $odefault,
                            'id'        => $id,
                            'length'    => $length,
                            );
        }

        ( isset( $params['revert'] ) && ( $params['value'] != $params['revert'] ) && $params['id'] ) ? $revert = 1 : $revert = 0;
        ( isset( $params['default'] ) && ( $params['value'] != $params['default'] ) && $params['id'] ) ? $default = 1 : $default = 0;

        if ( $this->preserve_input && isset( $this->trellis->input[ $params['name'] ] ) ) $params['value'] = $this->trellis->input[ $params['name'] ];

        $html = "";

        if ( $revert || $default )
        {
            $html .= "<div style='float:right;margin:-2px 0'>";

            if ( $revert ) $html .= $this->revert_html( $params['id'] );
            if ( $default ) $html .= $this->default_html( $params['id'] );

            $html .= "</div>";
        }

        if ( ! $params['length'] ) $params['length'] = 35;

        $html .= "<input type='text' name='{$params['name']}' id='{$params['name']}' value='{$params['value']}' size='{$params['length']}'";

        if ( $params['disabled'] ) $html .= " disabled='disabled'";

        $html .= " />";

        return $html;
    }

    #=======================================
    # @ Password
    #=======================================

    public function password($params)
    {
        if ( ! is_array( $params ) )
        {
            $params = array(
                            'name'        => $params,
                            'value'        => $value,
                            'default'    => $odefault,
                            'id'        => $id,
                            'length'    => $length,
                            );
        }

        ( isset( $params['revert'] ) && ( $params['value'] != $params['revert'] ) && $params['id'] ) ? $revert = 1 : $revert = 0;
        ( isset( $params['default'] ) && ( $params['value'] != $params['default'] ) && $params['id'] ) ? $default = 1 : $default = 0;

        if ( $this->preserve_input && isset( $this->trellis->input[ $params['name'] ] ) ) $params['value'] = $this->trellis->input[ $params['name'] ];

        $html = "";

        if ( $revert || $default )
        {
            $html .= "<div style='float:right;margin:-2px 0'>";

            if ( $revert ) $html .= $this->revert_html( $params['id'] );
            if ( $default ) $html .= $this->default_html( $params['id'] );

            $html .= "</div>";
        }

        if ( ! $params['length'] ) $params['length'] = 35;

        $html .= "<input type='password' name='{$params['name']}' id='{$params['name']}' value='{$params['value']}' size='{$params['length']}'";

        if ( $params['disabled'] ) $html .= " disabled='disabled'";

        $html .= " />";

        return $html;
    }

    #=======================================
    # @ Textarea
    #=======================================

    public function textarea($params, $value='', $odefault='', $id=0, $cols=0, $rows=0)
    {
        if ( ! is_array( $params ) )
        {
            $params = array(
                            'name'        => $params,
                            'value'        => $value,
                            'cols'        => $cols,
                            'rows'        => $rows,
                            'width'        => $width,
                            'height'    => $height,
                            );
        }

        if ( ! $params['cols'] ) $params['cols'] = 45;
        if ( ! $params['rows'] ) $params['rows'] = 8;

        if ( $this->preserve_input && isset( $this->trellis->input[ $params['name'] ] ) ) $params['value'] = $this->trellis->input[ $params['name'] ];

        $html = "<textarea name='{$params['name']}' id='{$params['name']}' cols='{$params['cols']}' rows='{$params['rows']}'";

        if ( $params['disabled'] ) $html .= " disabled='disabled'";

        if ( $params['width'] || $params['height'] )
        {
            $html .= " style='";

            if ( $params['width'] ) $html .= "width:{$params['width']};";
            if ( $params['height'] ) $html .= "height:{$params['height']};";

            $html .= "'";
        }

        $html .= " />{$params['value']}</textarea>";

        return $html;
    }

    #=======================================
    # @ Drop Down
    #=======================================

    public function drop_down($params, $options=array(), $selected='', $odefault='', $id=0, $onchange='')
    {
        if ( ! is_array( $params ) )
        {
            $params = array(
                            'name'        => $params,
                            'options'    => $options,
                            'value'        => $selected,
                            'default'    => $odefault,
                            'id'        => $id,
                            'onchange'    => $onchange,
                            );
        }

        ( isset( $params['revert'] ) && ( $params['value'] != $params['revert'] ) && $params['id'] ) ? $revert = 1 : $revert = 0;
        ( isset( $params['default'] ) && ( $params['value'] != $params['default'] ) && $params['id'] ) ? $default = 1 : $default = 0;

        if ( $this->preserve_input && isset( $this->trellis->input[ $params['name'] ] ) ) $params['value'] = $this->trellis->input[ $params['name'] ];

        $html = "";

        if ( $revert || $default )
        {
            $html .= "<div style='float:right;margin:-2px 0'>";

            if ( $revert ) $html .= $this->revert_html( $params['id'] );
            if ( $default ) $html .= $this->default_html( $params['id'] );

            $html .= "</div>";
        }

        $html .= "<select name='{$params['name']}' id='{$params['name']}'";

        if ( $params['disabled'] ) $html .= " disabled='disabled'";
        if ( $params['onchange'] ) $html .= " onchange='{$params['onchange']}'";

        $html .= ">";

        if ( ! empty( $params['options'] ) )
        {
            foreach ( $params['options'] as $key => $name )
            {
                $html .= "<option value='{$key}'";

                if ( $params['value'] == $key ) $html .= " selected='selected'";

                $html .= ">{$name}</option>";
            }
        }

        $html .= "</select>";

        return $html;
    }

    #=======================================
    # @ Button
    #=======================================

    public function button($id, $name, $value='')
    {
        return "<button id='{$id}' name='{$id}' type='button' class='button' value='". ( ( $value ) ? $value : $name ) ."'>{$name}</button>";
    }

    #=======================================
    # @ Submit Button
    #=======================================

    public function submit_button($id, $name, $value='')
    {
        return "<button id='{$id}' name='{$id}' type='submit' class='button' value='". ( ( $value ) ? $value : $name ) ."'>{$name}</button>";
    }

    #=======================================
    # @ Start Ticket Details
    #=======================================

    public function start_ticket_details($title)
    {
        return "<b class='t1'></b><b class='t2'></b><b class='t3'></b><b class='t4'></b>
                <div class='card'>
                    <div class='ticketbox'>{$title}</div>";
    }

    #=======================================
    # @ End Ticket Details
    #=======================================

    public function end_ticket_details()
    {
        return "</div>
                <b class='t4'></b><b class='t3'></b><b class='t2'></b><b class='t1e'></b>";
    }

    #=======================================
    # @ Group Title
    #=======================================

    public function group_title($title)
    {
        return "<div class='groupbox'>{$title}</div>";
    }

    #=======================================
    # @ Start Group
    #=======================================

    public function start_group($id)
    {
        if ( $id ) $this->groups[ $id ] = 0;
    }

    #=======================================
    # @ End Group
    #=======================================

    public function end_group($id)
    {
        if ( $id ) unset( $this->groups[ $id ] );
    }

    #=======================================
    # @ Start Group Table
    #=======================================

    public function start_group_table($title, $id='')
    {
        if ( $id ) $this->start_group( $id );

        return $this->group_title( $title ) ."
        <div class='rollhistory'>
        <table width='100%' cellpadding='0' cellspacing='0'>";
    }

    #=======================================
    # @ End Group Table
    #=======================================

    public function end_group_table($id='')
    {
        if ( $id ) $this->end_group( $id );

        return "</table>
        </div>";
    }

    #=======================================
    # @ Group Row
    #=======================================

    public function group_row($content, $group='')
    {
        if ( $group && isset( $this->groups[ $group ] ) )
        {
            $this->groups[ $group ] ++;

            ( $this->groups[ $group ] & 1 ) ? $class = 'option1' : $class = 'option2';
        }
        else
        {
            $class = 'option1';
        }

        return "<div class='{$class}' style='font-weight: normal'>{$content}</div>";
    }

    #=======================================
    # @ Group Row With Paragraphs
    #=======================================

    public function group_row_with_p($content)
    {
        return "<div class='rollcustomer'>{$content}</div>";
    }

    #=======================================
    # @ Group Table Row
    #=======================================

    public function group_table_row($left, $right, $group='', $left_width=0, $right_width=0, $hide=0, $id='')
    {
        if ( $group && isset( $this->groups[ $group ] ) )
        {
            $this->groups[ $group ] ++;

            ( $this->groups[ $group ] & 1 ) ? $class = 'option1' : $class = 'option2';
        }
        else
        {
            $class = 'option1';
        }

        if ( $left_width ) $lwidth = " width='{$left_width}'";
        if ( $right_width ) $rwidth = " width='{$right_width}'";

        if ( $hide ) $hide = " style='display:none'";

        if ( $id ) $id = " id='{$id}'";

        return "<tr{$hide}{$id}>
                    <td class='{$class}'{$lwidth} valign='top'>{$left}</td>
                    <td class='{$class}'{$rwidth} valign='top' style='font-weight: normal'>{$right}</td>
                </tr>";
    }

    #=======================================
    # @ Group Table Full Row
    #=======================================

    public function group_table_full_row($html, $group='', $style='')
    {
        if ( $group && isset( $this->groups[ $group ] ) )
        {
            $this->groups[ $group ] ++;

            ( $this->groups[ $group ] & 1 ) ? $class = 'option1' : $class = 'option2';
        }
        else
        {
            $class = 'option1';
        }

        return "<tr>
                    <td class='{$class}' colspan='2' valign='top'{$style}>{$html}</td>
                </tr>";
    }

    #=======================================
    # @ Group Table Full Row With Paragraphs
    #=======================================

    public function group_table_full_row_with_p($html, $group='')
    {
        if ( $group && isset( $this->groups[ $group ] ) )
        {
            $this->groups[ $group ] ++;
        }

        return "<tr>
                    <td class='rollcustomer' colspan='2' valign='top'>{$html}</td>
                </tr>";
    }

    #=======================================
    # @ Group Subtitle
    #=======================================

    public function group_sub($title)
    {
        return "<div class='subbox''>{$title}</div>";
    }

    #=======================================
    # @ Group Table Subtitle
    #=======================================

    public function group_table_sub($title)
    {
        return "<tr>
                    <td class='subbox' colspan='2'>{$title}</td>
                </tr>";
    }

    #=======================================
    # @ Start Form
    #=======================================

    public function start_form($action, $id, $method, $onsubmit='')
    {
        if ( $onsubmit ) $onsubmit = " onsubmit='{$onsubmit}'";

        return "<form action='{$action}' method='{$method}' id='{$id}'{$onsubmit}>";
    }

    #=======================================
    # @ End Form
    #=======================================

    public function end_form($html='')
    {
        if ( $html )
        {
            return "<div class='formtail'>{$html}</div>
            </form>";
        }
        else
        {
            return "</form>";
        }
    }

    #=======================================
    # @ Formtail
    #=======================================

    public function formtail($html='')
    {
        return "<div class='formtail'>{$html}</div>";
    }

    #=======================================
    # @ Alert Tip
    #=======================================

    public function alert_tip($tip, $size=250)
    {
        return "<span class='hint'><img src='<! IMG_DIR !>/icons/exclamation.png' alt='!' /><span class='tooltip' style='width:{$size}px'><span class='tooltip-pointer'>&nbsp;</span>{$tip}</span></span>";
    }

    #=======================================
    # @ Help Tip
    #=======================================

    public function help_tip($tip, $size=250)
    {
        return "<span class='hint'><img src='<! IMG_DIR !>/icons/question.png' alt='?' /><span class='tooltip' style='width:{$size}px'><span class='tooltip-pointer'>&nbsp;</span>{$tip}</span></span>";
    }

    #=======================================
    # @ Text Tip
    #=======================================

    public function text_tip($text, $tip, $size=250)
    {
        return "<span class='hint'>{$text}<span class='tooltip' style='width:{$size}px'><span class='tooltip-pointer'>&nbsp;</span>{$tip}</span></span>";
    }

    #=======================================
    # @ Error Wrap
    #=======================================

    public function error_wrap($text)
    {
        return "<div class='critical'>{$text}</div>";
    }

    #=======================================
    # @ Alert Wrap
    #=======================================

    public function alert_wrap($text)
    {
        return "<div class='alert'>{$text}</div>";
    }

    public function rating_visual($rating)
    {
        $html = "";

        $full = "<img src='<! IMG_DIR !>/star_full.gif' width='12' height='12' alt='Star' style='vertical-align:middle' title='{$rating}' />";
        $half = "<img src='<! IMG_DIR !>/star_half.gif' width='12' height='12' alt='Star' style='vertical-align:middle' title='{$rating}' />";
        $empty = "<img src='<! IMG_DIR !>/star_empty.gif' width='12' height='12' alt='Star' style='vertical-align:middle' title='{$rating}' />";

        for( $i=0; $i<5; $i++ )
        {
            if ( $rating > 0.5 )
            {
                $html .= $full;
            }
            elseif( $rating > 0 )
            {
                $html .= $half;
            }
            else
            {
                $html .= $empty;
            }

            --$rating;
        }

        return $html;
    }

    #=======================================
    # @ Focus Javascript
    #=======================================

    public function focus_js($field)
    {
        if ( $this->preserve_input )
        {
            return false;
        }
        else
        {
            return "\n" .'<script type="text/javascript">$(function(){$(\'#'. $field .'\').focus();});</script>';
        }
    }

    #=======================================
    # @ Scroll To Javascript
    #=======================================

    public function scroll_to_js($element)
    {
        return "\n" .'<script type="text/javascript">$(function(){$.scrollTo($(\'#'. $element .'\'), {duration:1000});});</script>';
    }

    #=======================================
    # @ Toggle Javascript
    #=======================================

    public function toggle_js()
    {
        $html = "\n<script type='text/javascript'>\n";

        foreach( $this->sblocks_toggle as $elmid )
        {
            $html .= "$('#t{$elmid}').bind('click', function() { toggleSBlock('{$elmid}') });\n";
        }

        $html .= "</script>";

        return $html;
    }

    #=======================================
    # @ Live Validation Javascript
    #=======================================

    public function live_validation_js($validate)
    {
        $html = "\n<script type='text/javascript'>\n";

        foreach( $validate as $field => $data )
        {
            $html .= $this->lv_add( $field, $data );
        }

        $html .= "</script>";

        return $html;
    }

    #=======================================
    # @ Live Validation Add Field
    #=======================================

    protected function lv_add($field, $data)
    {
        $html = "var F{$field} = new LiveValidation( '{$field}', { validMessage: ' ' } );\n";

        foreach( $data as $id => $validate )
        {
            if ( $validate['type'] == 'presence' )
            {
                $html .= $this->lv_presence( $field, $validate['params'] );
            }
            elseif ( $validate['type'] == 'format' )
            {
                $html .= $this->lv_format( $field, $validate['params'] );
            }
            elseif ( $validate['type'] == 'number' )
            {
                $html .= $this->lv_number( $field, $validate['params'] );
            }
            elseif ( $validate['type'] == 'email' )
            {
                $html .= $this->lv_email( $field, $validate['params'] );
            }
            elseif ( $validate['type'] == 'match' )
            {
                $html .= $this->lv_match( $field, $validate['params'] );
            }
            elseif ( $validate['type'] == 'custom' )
            {
                $html .= $this->lv_custom( $field, $validate['params'] );
            }
        }

        return $html;
    }

    #=======================================
    # @ Live Validation Presence
    #=======================================

    protected function lv_presence($field, $raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )
        {
            $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        }
        else
        {
            $params .= "failureMessage: ' ', ";
        }

        return "F{$field}.add( Validate.Presence, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Format
    #=======================================

    protected function lv_format($field, $raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )     $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        if ( $raw_params['pattern'] )     $params .= "pattern: {$raw_params['pattern']}, ";
        if ( $raw_params['negate'] )     $params .= "negate: true, ";

        if ( ! $raw_params['fail_msg'] ) $params .= "failureMessage: ' ', ";

        return "F{$field}.add( Validate.Format, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Number
    #=======================================

    protected function lv_number($field, $raw_params)
    {
        $params = "";

        if ( $raw_params['not_num_msg'] )     $params .= "notANumberMessage: '{$raw_params['not_num_msg']}', ";
        if ( $raw_params['not_int_msg'] )     $params .= "notAnIntegerMessage: '{$raw_params['not_int_msg']}', ";
        if ( $raw_params['wrong_num_msg'] ) $params .= "wrongNumberMessage: '{$raw_params['wrong_num_msg']}', ";
        if ( $raw_params['too_low_msg'] )     $params .= "tooLowMessage: '{$raw_params['too_low_msg']}', ";
        if ( $raw_params['too_high_msg'] )     $params .= "tooHighMessage: '{$raw_params['too_high_msg']}', ";
        if ( $raw_params['is'] )             $params .= "is: '{$raw_params['is']}', ";
        if ( $raw_params['minimum'] )         $params .= "minimum: '{$raw_params['not_num_msg']}', ";
        if ( $raw_params['maximum'] )         $params .= "maximum: '{$raw_params['maximum']}', ";
        if ( $raw_params['int_only'] )         $params .= "onlyInteger: true, ";

        if ( ! $raw_params['not_num_msg'] ) $params .= "notANumberMessage: ' ', ";

        return "F{$field}.add( Validate.Numericality, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Email
    #=======================================

    protected function lv_email($field, $raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )
        {
            $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        }
        else
        {
            $params .= "failureMessage: ' ', ";
        }

        return "F{$field}.add( Validate.Email, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Match
    #=======================================

    protected static function lv_match($field, $raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )     $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        if ( $raw_params['against'] )     $params .= 'against: function(value,args) { return value == $("#'. $raw_params['against'] .'").val(); }, ';

        if ( ! $raw_params['fail_msg'] ) $params .= "failureMessage: ' ', ";

        return "F{$field}.add( Validate.Custom, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Custom
    #=======================================

    protected function lv_custom($field, $raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )     $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        if ( $raw_params['against'] )     $params .= "against: function(value,args) { {$raw_params['against']} }, ";
        if ( $raw_params['args'] )         $params .= "args: {$raw_params['wrong_num_msg']}, ";

        if ( ! $raw_params['fail_msg'] ) $params .= "failureMessage: ' ', ";

        return "F{$field}.add( Validate.Custom, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ TinyMCE Javascript
    #=======================================

    public function tinymce_js($input, $type='')
    {
        return "<script language='javascript' type='text/javascript'>
                tinyMCE.init({
                    mode : 'exact',
                    theme : 'advanced',
                    elements : '{$input}',
                    content_css : '<! TD_URL !>/includes/css/tinymce.css',
                    plugins : 'inlinepopups,safari,spellchecker',
                    dialog_type : 'modal',
                    theme_advanced_toolbar_location : 'top',
                    theme_advanced_toolbar_align : 'left',
                    theme_advanced_path_location : 'bottom',
                    theme_advanced_disable : 'styleselect,formatselect',
                    theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,separator,forecolor,backcolor,separator,bullist,numlist,separator,outdent,indent,separator,link,unlink,image,separator,undo,redo,separator,spellchecker,separator,removeformat,cleanup,code',
                    theme_advanced_buttons2 : '',
                    theme_advanced_buttons3 : '',
                    theme_advanced_resize_horizontal : false,
                    theme_advanced_resizing : true
                });
                </script>";
    }

    #=======================================
    # @ Uploadify Javascript
    #=======================================

    public function uploadify_js($input, $data=array(), $params=array())
    {
        $html = "<script language='javascript' type='text/javascript'>
                // <![CDATA[
                $(function() {
                $('#". $input ."').uploadify({
                'uploader'  : 'includes/uploadify/uploadify.swf',
                'script'    : 'admin.php',
                'cancelImg' : '<! IMG_DIR !>/icons/cross.png',
                'scriptData': {
                    'session_id': '". $this->trellis->user['s_id'] ."'";

        if ( ! empty( $data ) && is_array( $data ) )
        {
            $query_data = '';

            foreach( $data as $field => $value )
            {
                $query_data .= ",\n\t\t\t\t\t'". $field ."': '". $value ."'";
            }

            $html .= $query_data;
        }

        if ( $params['type'] == 'images' )
        {
            $fileDesc = 'Image Files (gif, jpg, jpeg, png, svg, tiff)';
            $fileExt = '*.gif;*.jpg;*.jpeg;*.png;*.svg;*.tiff';
        }
        else
        {
            $fileDesc = 'Files ('. implode( ', ', ( $extensions = array_map( 'trim', explode( ',', $this->trellis->cache->data['settings']['general']['upload_exts'] ) ) ) ) .')';
            $fileExt = implode( ';', array_map( create_function( '$a', 'return \'*.\'. $a;' ), $extensions ) );
        }

        $multi = ( $params['multi'] ) ? 'true' : 'false';

        $html .= "
                },
                'auto': true,
                'fileDesc': '{$fileDesc}',
                'fileExt': '{$fileExt}',
                'simUploadLimit': 1,
                ";

        if ( $params['list'] && $this->trellis->user['g_upload_size_max'] )
        {
            $html .= "'sizeLimit': {$this->trellis->user['g_upload_size_max']},";
        }

        $html .= "
                'multi': {$multi},
                'hideButton': true,
                'width': '70px',
                'height': '28px',
                'wmode': 'transparent',
                'onCancel': function(event,queueID,fileObj,data) {
                    uploadUpdate('remove');
                },
                'onComplete': function(event,queueID,fileObj,response,data) {
                    uploadComplete(event,queueID,fileObj,response,data);
                },
                'onError': function(event,queueID,fileObj,errorObj) {
                    uploadError(event,queueID,fileObj,errorObj);
                },
                'onSelect': function(event,queueID,fileObj) {
                    uploadUpdate('add');
                }
                });

                var simpleUpload = new AjaxUpload('#simple_upload_file', {
                    action: 'admin.php',
                    name: 'Filedata',
                    data: {";

        if ( $query_data )
        {
            $html .= substr( $query_data, 1 );
        }

        $html .= "
                    },
                    autoSubmit: false,
                    onChange: function(file, ext) {
                        $('#simple_upload_file .ui-button-text').text(file);
                    },
                    onSubmit: function(file, ext) {
                        $('#simple_upload').val('{lang.button_uploading}');
                        $('#simple_upload').attr('disabled', true);
                    },
                    onComplete: function(file, response) {
                        uploadComplete(null, null, null, response, null);
                        $('#simple_upload_file .ui-button-text').text('{lang.browse}');
                        $('#simple_upload').val('{lang.button_upload}');
                        $('#simple_upload').removeAttr('disabled');
                    }
                });

                $('#simple_upload').click(function() {
                    simpleUpload.submit();
                });

                $('#upload_switch_simple').click(function() {
                    $('#upload_flash').hide();
                    $('#upload_simple').show();
                });

                $('#upload_switch_flash').click(function() {
                    $('#upload_simple').hide();
                    $('#upload_flash').show();
                });
                });
                ";

        if ( $params['list'] )
        {
            $html .= "
                function uploadComplete(event, queueID, fileObj, response, data) {
                    jsonResponse = convertFromJson(response);
                    if (jsonResponse.success) {
                        $('#upload_msg').text(jsonResponse.successmsg);
                        $('#upload_list').append(\"<li id='uf_\"+jsonResponse.id+\"'><input type='hidden' name='fuploads[]' value='\"+jsonResponse.id+\"' />\"+jsonResponse.name+\"<span class='uploaddel' onclick='uploadDelete(\"+jsonResponse.id+\")'></span></li>\");
                        $('#upload_list').show();
                    }
                    else {
                        if (jsonResponse.error) {
                            $('#upload_msg').text(jsonResponse.errormsg);
                        }
                        else {
                            $('#upload_msg').text('unknown error');
                        }
                    }
                    $('#upload_msg').stop().show('blind');
                    $('#upload_msg').animate({opacity: 1.0}, 5000);
                    $('#upload_msg').hide('blind');
                    uploadUpdate();
                    return false;
                }

                function uploadError(event, queueID, fileObj, errorObj) {
                    uploadUpdate();
                }

                function uploadUpdate(adjust) {
                    if ( $('.uploadifyQueueItem').size() > 1 ) {
                        var newheight = $('#upload_fileQueue').outerHeight();
                        if (adjust == 'add') {
                            newheight += $('.uploadifyQueueItem').outerHeight();
                        } else if (adjust == 'remove') {
                            newheight -= $('.uploadifyQueueItem').outerHeight();
                        }
                        $('#upload_fileQueue').parent().parent().height( newheight );
                    }
                    return true;
                }

                function uploadDelete(fid) {
                    $.getJSON('<! TD_URL !>/admin.php?section={$this->trellis->input['section']}&page={$this->trellis->input['page']}&act=dodelupload&id='+fid, function(jsonResponse) {
                        if (jsonResponse.success) {
                            $('#uf_'+fid).remove();
                        }
                        else {
                            if (jsonResponse.error) {
                                $('#upload_msg').text('{$this->trellis->lang['error_upload_delete']}');
                            }
                            else {
                                $('#upload_msg').text('unknown error');
                            }
                            $('#upload_msg').stop().show('blind');
                            $('#upload_msg').animate({opacity: 1.0}, 5000);
                            $('#upload_msg').hide('blind');
                        }
                    });
                }";
        }

        $html .= "
                // ]]>
                </script>
                <div id='upload_flash' style='position: relative;'>
                    <span class='button'>{lang.browse}</span>
                    <div style='position:absolute; top: 0;'>
                        <input id='upload_file' name='upload_file' type='file' />
                    </div>
                    <div style='float: right;'><span id='upload_switch_simple' class='button'>{lang.upload_switch_simple}</span></div>
                </div>
                <div id='upload_simple' style='display: none;'>
                    <span id='simple_upload_file' class='button'>{lang.browse}</span> <input type='button' id='simple_upload' name='simple_upload' value='{lang.button_upload}' class='button' />
                    <div style='float: right;'><span id='upload_switch_flash' class='button'>{lang.upload_switch_flash}</span></div>
                </div>
                <div id='upload_msg' style='display:none;margin-top:12px'>&nbsp;</div>";

        if ( $params['list'] )
        {
            $html .= "<ul id='upload_list'></ul>";
        }

        return $html;
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
    <title>{lang.acp_title}</title>
    <link href="skins/s1/css/jquery-ui.css" rel="stylesheet" type="text/css" media="screen" />
    <link href="skins/s1/css/tiburon.css" rel="stylesheet" type="text/css" media="screen" />
    <script src="skins/s1/scripts/navlinks.js" type="text/javascript"></script>
    <script src="skins/s1/scripts/catlinks.js" type="text/javascript"></script>
    <script src="skins/s1/scripts/livevalidation.js" type="text/javascript"></script>
    <script src="skins/s1/scripts/jquery.js" type="text/javascript"></script>
    <script src="skins/s1/scripts/jquery-ui.js" type="text/javascript"></script>
    <script src="skins/s1/scripts/jqueryextras.js" type="text/javascript"></script>
    <script src="includes/tinymce/tiny_mce.js" type="text/javascript"></script>
    <script src="skins/s1/scripts/common_acp.js" type="text/javascript"></script>
    <script src="includes/uploadify/swfobject.js" type="text/javascript"></script>
    <script src="includes/uploadify/jquery.uploadify.js" type="text/javascript"></script>
    <script src="skins/s1/scripts/jquery.json.js" type="text/javascript"></script>
    <script src="skins/s1/scripts/jquery.menu.js" type="text/javascript"></script>
    <script src="includes/scripts/ajaxupload.js" type="text/javascript"></script>
    <% JAVASCRIPT %>
</head>

<body>

<div id="ajax_loading"><p>{lang.ajax_loading}</p></div>

<div id="navwrap">
    <div id="navbar">
        <a href="admin.php" title="Trellis Desk"><img src="<! IMG_DIR !>/navbar_logo.png" width="86" height="36" alt="Trellis Desk" style="float: left;" /></a>

        <!-- Navigation triggers -->
        <% MAIN_NAV %>
    </div>

    <div id="navexit"><% RED_LINK %></div>
    <div class="navclear"></div>

</div>

<% SUB_NAV %>

<% POPUP_NAV %>

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
    <p class="queries">[&nbsp; <% QUERY_COUNT %> {lang.queries} &nbsp;|&nbsp; <% EXE_TIME %> {lang.seconds} &nbsp;]</p><br />
    <!-- Trellis Desk is a free product. All we ask is that you keep the copyright intact. -->
    <!-- Purchase our copyright removal service at http://www.accord5.com/copyright  -->
    <% COPYRIGHT %>
</div>

<!-- Hover bar -->
<% HOVER_BAR %>

<script type="text/javascript">
var tabber1 = new Yetii({
id: 'catbar',
active: <% ACTIVE_LINK %>
});
</script>

</body>
</html>
EOF;

        return $html;
    }

    #=======================================
    # @ Get Error HTML
    #=======================================

    protected function get_error_html($extra)
    {
        if ( $extra['login'] )
        {
            /*$cookie_sid = $this->trellis->get_cookie('hdsid');
            $cookie_uid = $this->trellis->get_cookie('hduid');

            if ( $cookie_sid )
            {
                $this->trellis->db->construct( array(
                                                           'select'    => array( 's_uid', 's_uname' ),
                                                           'from'        => 'sessions',
                                                            'where'    => array( array( 's_id', '=', $cookie_sid ), array( 's_uid', '!=', 0, 'and' ) ),
                                                     )     );
            }
            elseif ( $cookie_uid )
            {
                $this->trellis->db->construct( array(
                                                           'select'    => array( 'name' ),
                                                           'from'        => 'users',
                                                            'where'    => array( 'id', '=', intval( $cookie_uid ) ),
                                                     )     );
            }

            if ( $cookie_sid || $cookie_uid )
            {
                $this->trellis->db->execute();

                if ( $this->trellis->db->get_num_rows() )
                {
                    $pre_mem = $this->trellis->db->fetch_row();

                    if ( $cookie_sid )
                    {
                        $pre_mem['name'] = $pre_mem['s_uname'];
                    }
                }
                else
                {
                    $pre_mem['name'] = $this->trellis->lang['username'];
                }
            }
            else
            {
                $pre_mem['name'] = $this->trellis->lang['username'];
            }

            $this->skin['wrapper'] = $this->data['wrapper_e_l'];*/

            if ( $extra['msg'] ) $error = '<div class="critical">{lang.'. $extra['msg'] .'}</div>';

            $pre_username = '{lang.username}';

            $self = '<! TD_URL !>/admin.php';

            if ( $this->trellis->input['act'] != 'logout' && $_SERVER['QUERY_STRING'] ) $self .= '?'. $this->trellis->sanitize_data( $_SERVER['QUERY_STRING'] );

            $this->add_sidebar_help( '{lang.login_title}', '{lang.random_text}' );

            $html = <<<EOF
{$error}
<div id='ticketroll'>
<form action="{$self}" method="post">
<input type="hidden" name="do_login" value="1" />
<div class="groupbox">{lang.login}</div>
<div class="rollhistory">
    <div class="bluecell-dark"><input type="text" name="username" id="username" value="{$pre_username}" size="32" /></div>
    <div class="bluecell-dark"><input type="password" name="password" id="password" value="{lang.password}" size="32" /></div>
    <div class="formtail"><input type="submit" name="submit" id="login" value="{lang.login}" class="button" /></div>
</div>
</form>
</div>
EOF;
        }

        return $html;
    }
}

?>