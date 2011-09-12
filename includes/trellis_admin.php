<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class trellis_admin extends trellis {

    #=======================================
    # @ Initialize
    #=======================================

    public function initialize()
    {
        #=============================
        # Start Execution Timer
        #=============================

        $this->start_timer();

        #=============================
        # Get Incoming Data
        #=============================

        $this->input = $this->get_post();

        #=============================
        # Check Cache
        #=============================

        $this->check_cache();

        #=============================
        # Check Tacks
        #=============================

        $this->check_tasks();

        #=============================
        # Load Skin
        #=============================

        require_once TD_CLASS .'askin.php';

        $this->skin = new td_class_askin( $this );

        #=============================
        # Load Session
        #=============================

        require_once TD_CLASS .'asession.php';

        $this->session = new td_class_asession();
        $this->session->trellis = &$this;

        #=============================
        # Login / Logout
        #=============================

        if ( $this->input['do_login'] )
        {
            $this->user = $this->session->do_login();
        }
        else
        {
            $this->user = $this->session->load_session();
        }

        if ( $this->input['act'] == 'logout' )
        {
            $this->session->do_logout();
        }

        #=============================
        # Other Actions
        #=============================

        if ( $this->input['act'] == 'lookup' )
        {
            if ( $this->input['type'] == 'staff' )
            {
                $this->ajax_staff_lookup();
            }
            elseif ( $this->input['type'] == 'user' )
            {
                $this->ajax_user_lookup();
            }
        }
    }

    #=======================================
    # @ Admin Load Language
    #=======================================

    public function load_lang($name)
    {
        if ( ! $this->user['lang'] )
        {
            if( ! $this->user['lang'] = $this->cache->data['misc']['default_lang'] )
            {
                $this->user['lang'] = $this->config['fallback_lang'];
            }
        }

        require_once TD_PATH .'languages/'. $this->cache->data['langs'][ $this->user['lang'] ]['key'] .'/ad_lang_'. $name .'.php';

        #$this->skin->trellis->lang = array_merge( (array)$lang , (array)$this->skin->trellis->lang );
        $this->lang = array_merge( (array)$lang , (array)$this->lang );
    }

    #=======================================
    # @ Check Permission
    #=======================================

    public function check_perm($section, $page='', $action='', $error=1)
    {
        if ( $this->user['id'] == 1 ) return true;

        $permission = 0;

        $check = $section;
        if ( $page ) $check .= '_'. $page;
        if ( $action ) $check .= '_'. $action;

        if ( $this->user['g_acp_perm'][ $check ] ) $permission = 1;

        if ( ! $permission )
        {
            if ( $error )
            {
                $this->skin->error('no_perm');
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    #=======================================
    # @ AJAX Staff Lookup
    #=======================================

    private function ajax_staff_lookup()
    {
        $staff = array();

        if ( $this->input['assign'] && $this->user['id'] != 1 )
        {
            ( $this->user['g_assign_outside'] ) ? $check_perm = 0 : $check_perm = 1;
        }

        foreach( $this->cache->data['staff'] as $s )
        {
            if ( $check_perm )
            {
                $s['g_acp_depart_perm'] = unserialize( $s['g_acp_depart_perm'] );

                if ( ! $s['g_acp_depart_perm'][ $this->input['assign'] ]['v'] ) continue;
            }

            if ( preg_match( '/^'. $this->input['q'] .'/i', $s['name'] ) ) $staff[] = array( 'caption' => $s['name'], 'value' => $s['id'] );
        }

        $this->skin->ajax_output( json_encode( $staff ) );
    }

    #=======================================
    # @ AJAX User Lookup
    #=======================================

    private function ajax_user_lookup()
    {
        $users = array();

        $this->db->construct( array(
                                            'select'    => array( 'id', 'name' ),
                                            'from'    => 'users',
                                            'where'    => array( 'name', 'like', addcslashes( $this->input['q'], '%_' ) .'%' ),
                                    )      );

        $this->db->execute();

        while( $u = $this->db->fetch_row() )
        {
            $users[] = array( 'caption' => $u['name'], 'value' => $u['id'] );
        }

        $this->skin->ajax_output( json_encode( $users ) );
    }

}

?>