<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_asession {

    public $user = array();
    private $messages = array();

    #=======================================
    # @ Load Session
    # Loads the session.  What else? :D
    #=======================================

    function load_session()
    {
        $authorized = 0;

        #=============================
        # Get Information
        #=============================

        $cookie_sid = $this->trellis->get_cookie('tdasid');

        if ( $this->trellis->input['session_id'] ) $cookie_sid = $this->trellis->input['session_id'];

        #=============================
        # If We Have A Session Cookie
        #=============================

        if ( $cookie_sid )
        {
            #=============================
            # Load User
            #=============================

            $this->trellis->db->construct( array(
                                                       'select'    => array( 's' => 'all',
                                                                             'u' => array( 'id', 'name', 'email', 'login_key', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp', 'title', 'joined', 'signature', 'sig_html', 'sig_auto', 'rss_key', 'lang', 'skin', 'time_zone', 'time_dst', 'rte_enable', 'email_type' ),
                                                                             'us' => array( 'email_staff_enable', 'email_staff_user_approve', 'email_staff_ticket', 'email_staff_reply', 'email_staff_assign', 'email_staff_escalate', 'email_staff_hold', 'email_staff_move_to', 'email_staff_move_away', 'email_staff_close', 'email_staff_reopen', 'esn_unassigned', 'esn_assigned', 'esn_assigned_to_me', 'columns_tm', 'sort_tm', 'order_tm', 'dfilters_status', 'dfilters_depart', 'dfilters_priority', 'dfilters_flag', 'dfilters_assigned', 'auto_assign' ),
                                                                             'g' => 'all',
                                                                            ),
                                                       'from'        => array( 's' => 'asessions' ),
                                                       'join'        => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 's' => 's_uid', '=', 'u' => 'id' ) ), array( 'from' => array( 'us' => 'users_staff' ), 'where' => array( 's' => 's_uid', '=', 'us' => 'uid' ) ), array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'u' => 'ugroup' ) ) ),
                                                        'where'    => array( array( 's' => 's_id' ), '=', $cookie_sid ),
                                                        'limit'    => array( 0, 1 ),
                                                 )     );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() )
            {
                $this->user = $this->trellis->db->fetch_row();

                if ( $this->user['s_time'] <= time() - ( $this->trellis->config['acp_session_timeout'] * 60 ) )
                {
                    $this->kill_old_sessions();
                }
                elseif ( ! ( $this->trellis->cache->data['settings']['security']['session_ip_check'] && $this->user['s_ipadd'] != $this->trellis->input['ip_address'] ) )
                {
                    // Sub-Groups
                    $this->user['ugroup_sub'] = unserialize( $this->user['ugroup_sub'] );

                    if ( ! $this->user['g_acp_access'] && $this->user['ugroup_sub_acp'] && is_array( $this->user['ugroup_sub'] ) && ! empty( $this->user['ugroup_sub'] ) )
                    {
                        foreach ( $this->user['ugroup_sub'] as $g )
                        {
                            if ( $this->trellis->cache->data['groups'][ $g ]['g_acp_access'] ) $this->user['g_acp_access'] = 1;

                            break;
                        }
                    }

                    if ( $this->user['g_acp_access'] )
                    {
                        /*#=============================
                        # Update Ticket
                        #=============================

                        $update_ticket = 0;

                        if ( $this->user['s_inticket'] )
                        {
                            if ( $this->trellis->input['section'] == 'manage' && $this->trellis->input['page'] == 'tickets' && $this->trellis->input['act'] != 'view' )
                            {
                                if ( $this->user['s_inticket'] != $this->trellis->input['id'] ) $update_ticket = 1;
                            }
                            else
                            {
                                $update_ticket = 1;
                            }
                        }

                        if ( $update_ticket )
                        {
                            $this->trellis->db->construct( array(
                                                                       'select'    => array( 'status' ),
                                                                       'from'        => 'tickets',
                                                                        'where'    => array( 'id', '=', $this->user['s_inticket'] ),
                                                                        'limit'    => array( 0, 1 ),
                                                                 )     );

                            $this->trellis->db->execute();

                            if ( $this->trellis->db->get_num_rows() )
                            {
                                $t = $this->trellis->db->fetch_row();

                                if ( $t['status'] == 2 )
                                {
                                    $this->trellis->db->construct( array(
                                                                               'update'    => 'tickets',
                                                                               'set'        => array( 'status' => 1 ),
                                                                                 'where'    => array( 'id', '=', $this->user['s_inticket'] ),
                                                                                 'limit'    => array( 1 ),
                                                                         )     );

                                    $this->trellis->db->execute();
                                }
                            }
                        }*/

                        #=============================
                        # Unserialize Data
                        #=============================

                        if ( is_array( ( $messages = unserialize( $this->user['s_messages'] ) ) ) )
                        {
                            $this->messages = &$messages;
                        }

                        #=============================
                        # Update Session
                        #=============================

                        $this->trellis->set_cookie( 'tdasid', $cookie_sid, time() + ( $this->trellis->config['acp_session_timeout'] * 60 ) );

                        #=============================
                        # Permissions
                        #=============================

                        $this->user['g_kb_perm'] = unserialize( $this->user['g_kb_perm'] );
                        $this->user['g_depart_perm'] = unserialize( $this->user['g_depart_perm'] );
                        $this->user['g_acp_depart_perm'] = unserialize( $this->user['g_acp_depart_perm'] );
                        $this->user['g_acp_perm'] = unserialize( $this->user['g_acp_perm'] );

                        // Sub-Groups
                        if ( is_array( $this->user['ugroup_sub'] ) && ! empty( $this->user['ugroup_sub'] ) )
                        {
                            $this->merge_groups( &$this->user, $this->user['ugroup_sub'] );
                        }

                        $authorized = 1;
                    }
                }
            }
        }

        #=============================
        # If We Are Not Authorized
        #=============================

        if ( ! $authorized )
        {
            $this->user['id']    = 0;

            $this->trellis->delete_cookie( 'tdasid' );

            $this->trellis->skin->error( '', 1 );
        }

        return $this->user;
    }

    #=======================================
    # @ Do Login
    # Attempt to login.
    #=======================================

    function do_login()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! isset( $this->trellis->input['username'] ) || ! isset( $this->trellis->input['password'] ) )
        {
            $this->trellis->skin->error( 'fill_form_completely', 1 );
        }

        #=============================
        # Select User
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'u' => array( 'id', 'name', 'email', 'pass_hash', 'pass_salt', 'login_key', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp', 'title', 'joined', 'signature', 'sig_auto', 'rss_key', 'lang', 'skin', 'time_zone', 'time_dst', 'rte_enable', 'email_type' ),
                                                                         'us' => array( 'email_staff_enable', 'email_staff_user_approve', 'email_staff_ticket', 'email_staff_reply', 'email_staff_assign', 'esn_unassigned', 'esn_assigned', 'esn_assigned_to_me', 'columns_tm', 'sort_tm', 'order_tm', 'dfilters_status', 'dfilters_depart', 'dfilters_priority', 'dfilters_flag', 'dfilters_assigned', 'auto_assign' ),
                                                                         'g' => 'all',
                                                                        ),
                                                   'from'        => array( 'u' => 'users' ),
                                                   'join'        => array( array( 'from' => array( 'us' => 'users_staff' ), 'where' => array( 'u' => 'id', '=', 'us' => 'uid' ) ), array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'u' => 'ugroup' ) ) ),
                                                    'where'    => array( array( 'u' => 'name|lower' ), '=', strtolower( $this->trellis->input['username'] ) ),
                                                    'limit'    => array( 0, 1 ),
                                             )     );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() )
        {
            $this->trellis->log( array( 'msg' => array( 'login_fail_user', $this->trellis->input['username'] ), 'type' => 'security', 'level' => 2 ) );

            $this->trellis->skin->error( 'login_no_user', 1 );
        }

        $mem = $this->trellis->db->fetch_row();

        #=============================
        # Compare Password
        #=============================

        if ( hash( 'whirlpool', $mem['pass_salt'] . $this->trellis->input['password'] . $this->trellis->config['pass_key'] ) == $mem['pass_hash'] )
        {
            // Sub-Groups
            $mem['ugroup_sub'] = unserialize( $mem['ugroup_sub'] );

            if ( ! $mem['g_acp_access'] && $mem['ugroup_sub_acp'] && is_array( $mem['ugroup_sub'] ) && ! empty( $mem['ugroup_sub'] ) )
            {
                foreach ( $mem['ugroup_sub'] as $g )
                {
                    if ( $this->trellis->cache->data['groups'][ $g ]['g_acp_access'] ) $mem['g_acp_access'] = 1;

                    break;
                }
            }

            // Permission
            if ( ! $mem['g_acp_access'] )
            {
                $this->trellis->log( 'security', 'login_fail_perm', 2, array( 'link' => 'user', 'id' => $mem['id'] ), $mem['id'] );

                $this->trellis->skin->error( 'login_no_admin', 1 );
            }

            #=============================
            # Create Session
            #=============================

            $new_session = sha1( 's' . time() . $mem['id'] . uniqid( rand(), true ) . $this->trellis->config['session_key'] );

            $db_array = array(
                              's_id'            => $new_session,
                              's_uid'            => $mem['id'],
                              's_uname'            => $mem['name'],
                              's_ipadd'            => $this->trellis->input['ip_address'],
                              's_location'        => $this->trellis->input['act'],
                              's_time'            => time(),
                              );

            if ( $this->trellis->input['section'] == 'manage' && $this->trellis->input['act'] == 'tickets' && $this->trellis->input['code'] == 'view' )
            {
                $db_array['s_inticket'] = $this->trellis->input['id'];
            }
            else
            {
                $db_array['s_inticket'] = 0;
            }

            $this->trellis->db->construct( array(
                                                       'insert'    => 'asessions',
                                                       'set'        => $db_array,
                                                 )     );

            $this->trellis->db->execute();

            $this->trellis->set_cookie( 'tdasid', $new_session, time() + ( $this->trellis->config['acp_session_timeout'] * 60 ) );

            $this->trellis->log( array( 'msg' => 'login_success', 'type' => 'security', 'level' => 2, 'content_type' => 'user', 'content_id' => $mem['id'], 'uid' => $mem['id'] ) );

            // Play It Safe
            $mem['pass_hash'] = $mem['pass_salt'] = $mem['login_key'] = "";

            #=============================
            # Redirect
            #=============================

            parse_str( $_SERVER['QUERY_STRING'], $url ); // # TODO: security risk? sanitize?

            $this->trellis->skin->redirect( $url );
        }
        else
        {
            $this->trellis->log( array( 'msg' => 'login_fail_pass', 'type' => 'security', 'level' => 2, 'content_type' => 'user', 'content_id' => $mem['id'], 'uid' => $mem['id'] ) );

            $this->trellis->skin->error( 'login_no_pass', 1 );
        }
    }

    #=======================================
    # @ Do Logout
    # Attempt to logout.
    #=======================================

    function do_logout()
    {
        #=============================
        # Delete Cookie
        #=============================

        $this->trellis->delete_cookie('tdasid');

        #=============================
        # Update Ticket
        #=============================

        if ( $this->user['s_inticket'] )
        {
            $this->trellis->db->construct( array(
                                                       'update'    => 'tickets',
                                                       'set'        => array( 'status' => 1 ),
                                                         'where'    => array( array( 'id', '=', $this->user['s_inticket'] ), array( 'status', '=', 2, 'and' ) ),
                                                 )     );

            $this->trellis->db->next_shutdown();
            $this->trellis->db->execute();
        }

        #=============================
        # Delete Session
        #=============================

        $this->trellis->db->construct( array(
                                                   'delete'    => 'asessions',
                                                    'where'    => array( 's_id', '=', $this->user['s_id'] ),
                                                    'limit'    => array( 1 ),
                                             )     );

        $this->trellis->db->execute();

        $this->trellis->log( array( 'msg' => 'logout', 'type' => 'security', 'level' => 2, 'content_type' => 'user', 'content_id' => $mem['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( array( 'section' => 'admin', 'page' => null, 'act' => null ) );
    }

    #=======================================
    # @ Kill Old Sessions
    # Kills sessions older than the session
    # timeout (defined in ACP).
    #=======================================

    function kill_old_sessions()
    {
        #=============================
        # Grab Sessions
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 's_id', 's_inticket' ),
                                                   'from'        => 'asessions',
                                                    'where'    => array( 's_time' ,'<=', ( time() - ( $this->trellis->config['acp_session_timeout'] * 60 ) ) ),
                                             )     );

        $this->trellis->db->execute();

        if ( $num_killed = $this->trellis->db->get_num_rows() )
        {
            # CHECK: in progress control

            $sessions = array(); // Initialize For Security
            $tickets = array(); // Initialize For Security

            while ( $s = $this->trellis->db->fetch_row() )
            {
                $sessions[] = $s['s_id'];
                $tickets[] = $s['s_inticket'];
            }

            #=============================
            # Update Tickets
            #=============================

            $this->trellis->db->construct( array(
                                                       'update'    => 'tickets',
                                                       'set'        => array( 'status' => 1 ),
                                                         'where'    => array( array( 'id', 'in', $tickets ), array( 'status', '=', 2, 'and' ) ),
                                                 )     );

            $this->trellis->db->next_shutdown();
            $this->trellis->db->execute();

            #=============================
            # Delete Sessions
            #=============================

            $this->trellis->db->construct( array(
                                                       'delete'    => 'asessions',
                                                        'where'    => array( 's_id' ,'in', $sessions ),
                                                 )     );

            $this->trellis->db->next_shutdown();
            $this->trellis->db->execute();
        }

        return $num_killed;
    }

    #=======================================
    # @ Add Message
    #=======================================

    public function add_message( $type, $msg )
    {
        $this->messages[] = array( 'type' => $type, 'msg' => $msg );
    }

    #=======================================
    # @ Get Messages
    #=======================================

    public function get_messages()
    {
        return $this->messages;
    }

    #=======================================
    # @ Clear Messages
    #=======================================

    public function clear_messages()
    {
        return $this->messages = array();
    }

    #=======================================
    # @ Update Session
    #=======================================

    public function update_session()
    {
        parse_str( $_SERVER['QUERY_STRING'], $location );

        $this->trellis->db->construct( array(
                                                   'update'    => 'asessions',
                                                   'set'        => array( 's_location' => serialize( $location ), 's_messages' => serialize( $this->messages ), 's_time' => time() ),
                                                    'where'    => array( 's_id', '=', $this->user['s_id'] ),
                                                    'limit'    => array( 1 ),
                                             )     );

        #$this->trellis->db->next_shutdown(); // TODO: if we use shutdown then messages don't get displayed on redirects where a background process is still running (ex: outgoing emails)
        $this->trellis->db->execute();
    }

    #=======================================
    # @ Merge Groups
    #=======================================

    private function merge_groups($fg, $groups)
    {
        # TODO: optimize

        $and = array(
                     'g_ticket_access',
                     'g_ticket_create',
                     'g_kb_access',
                     'g_kb_rate',
                     'g_kb_comment',
                     'g_news_comment',
                     'g_ticket_edit',
                     'g_ticket_escalate',
                     'g_reply_rate',
                     'g_reply_edit',
                     'g_reply_delete',
                     'g_change_skin',
                     'g_change_lang',
                     'g_news_com_edit_all',
                     'g_news_com_delete_all',
                     'g_acp_access',
                     'g_ticket_close',
                     'g_tickte_reopen',
                     'g_ticket_attach',
                     'g_kb_com_edit',
                     'g_kb_com_delete',
                     'g_kb_com_edit_all',
                     'g_kb_com_delete_all',
                     'g_news_com_edit',
                     'g_news_com_delete',
                     'g_assign_outside',
                     'g_hide_names',
                     );

        foreach ( $and as $k => $v )
        {
            if ( $fg[ $v ] ) unset( $and[ $k ] );
        }

        foreach ( $groups as $gid )
        {
            $g = $this->trellis->cache->data['groups'][ $gid ];

            if ( ! empty( $and ) )
            {
                foreach ( $and as $v )
                {
                    if ( ! $fg[ $v ] && $g[ $v ] ) $fg[ $v ] = 1;
                }
            }

            if ( ( ! $g['g_upload_size_max'] && $fg['g_upload_size_max'] ) || ( $g['g_upload_size_max'] && $g['g_upload_size_max'] > $fg['g_upload_size_max'] ) ) $fg['g_upload_size_max'] = $g['g_upload_size_max'];

            $g['g_depart_perm'] = unserialize( $g['g_depart_perm'] );

            if ( is_array( $g['g_depart_perm'] ) && ! empty( $g['g_depart_perm'] ) )
            {
                foreach ( $g['g_depart_perm'] as $d => $p )
                {
                    if ( ! $fg['g_depart_perm'][ $d ] && $g['g_depart_perm'][ $d ] ) $fg['g_depart_perm'][ $d ] = 1;
                }
            }

            $g['g_acp_depart_perm'] = unserialize( $g['g_acp_depart_perm'] );

            if ( is_array( $g['g_acp_depart_perm'] ) && ! empty( $g['g_acp_depart_perm'] ) )
            {
                foreach ( $g['g_acp_depart_perm'] as $d => $types )
                {
                    foreach ( $types as $t => $p )
                    {
                        if ( ! $fg['g_acp_depart_perm'][ $d ][ $t ] && $g['g_acp_depart_perm'][ $d ][ $t ] ) $fg['g_acp_depart_perm'][ $d ][ $t ] = 1;
                    }
                }
            }

            $g['g_acp_perm'] = unserialize( $g['g_acp_perm'] );

            if ( is_array( $g['g_acp_perm'] ) && ! empty( $g['g_acp_perm'] ) )
            {
                foreach ( $g['g_acp_perm'] as $t => $p )
                {
                    if ( ! $fg['g_acp_perm'][ $t ] && $g['g_acp_perm'][ $t ] ) $fg['g_acp_perm'][ $t ] = 1;
                }
            }
        }

        return $fg;
    }
}

?>