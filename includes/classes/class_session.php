<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_session {

    var $user = array();

    #=======================================
    # @ Load Session
    #=======================================

    public function load_session()
    {
        $authorized = 0;

        #=============================
        # Get Information
        #=============================

        $cookie_sid = $this->trellis->get_cookie('tdsid');
        $cookie_uid = intval( $this->trellis->get_cookie('tduid') );
        $cookie_hash = $this->trellis->get_cookie('tdrmhash');

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
                                                                             'm' => array( 'id', 'name', 'email', 'ugroup', 'ugroup_sub', 'title', 'joined', 'signature', 'sig_html', 'sig_auto', 'lang', 'skin', 'time_zone', 'time_dst', 'rte_enable', 'email_enable', 'email_ticket', 'email_action', 'email_news', 'email_type', 'tickets_total', 'tickets_open' ),
                                                                             'g' => 'all', # TODO: all group data is cached, so do we need to pull it from the db? update similar code if necessary.
                                                                            ),
                                                       'from'        => array( 's' => 'sessions' ),
                                                       'join'        => array( array( 'from' => array( 'm' => 'users' ), 'where' => array( 's' => 's_uid', '=', 'm' => 'id' ) ), array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'm' => 'ugroup' ) ) ),
                                                        'where'    => array( array( 's' => 's_id' ), '=', sha1( $cookie_sid . $this->trellis->config['session_key'] ) ),
                                                        'limit'    => array( 0, 1 ),
                                                 )     );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() == 1 )
            {
                $this->user = $this->trellis->db->fetch_row();

                if ( $this->user['s_time'] <= ( time() - ( $this->trellis->cache->data['settings']['security']['session_timeout'] * 60 ) ) )
                {
                    $this->kill_old_sessions(); # TODO: rarely run because session cookie expires at timeout time. use cron to run periodically.
                }
                elseif ( ! ( $this->trellis->cache->data['settings']['security']['session_ip_check'] && $this->user['s_ipadd'] != $this->trellis->input['ip_address'] ) )
                {
                    #=============================
                    # Update Session
                    #=============================

                    $this->trellis->set_cookie( 'tdsid', $cookie_sid, time() + ( $this->trellis->cache->data['settings']['security']['session_timeout'] * 60 ) );

                    if ( $this->user['s_guest'] )
                    {
                        $this->user['id']    = 0;
                        $this->user['name'] = $this->trellis->lang['guest'];
                        $this->user['ugroup'] = 2;

                        $this->user = array_merge( $this->user, $this->trellis->cache->data['groups'][2] );
                    }

                    // Permissions
                    $this->user['g_depart_perm'] = unserialize( $this->user['g_depart_perm'] );
                    $this->user['g_kb_perm'] = unserialize( $this->user['g_kb_perm'] );

                    // Sub-Groups
                    $this->user['ugroup_sub'] = unserialize( $this->user['ugroup_sub'] );

                    if ( is_array( $this->user['ugroup_sub'] ) && ! empty( $this->user['ugroup_sub'] ) )
                    {
                        $this->merge_groups( $this->user, $this->user['ugroup_sub'] );
                    }

                    $authorized = 1;
                }
            }
        }

        #=============================
        # If We Have A Remember Cookie
        #=============================

        if ( $cookie_uid && $cookie_hash && ! $authorized )
        {
            #=============================
            # Load User
            #=============================

            $this->trellis->db->construct( array(
                                                       'select'    => array( 'm' => array( 'id', 'name', 'email', 'login_key', 'ugroup', 'ugroup_sub', 'title', 'joined', 'signature', 'sig_html', 'sig_auto', 'lang', 'skin', 'time_zone', 'time_dst', 'rte_enable', 'email_enable', 'email_ticket', 'email_action', 'email_news', 'email_type', 'tickets_total', 'tickets_open' ),
                                                                             'g' => 'all',
                                                                            ),
                                                       'from'        => array( 'm' => 'users' ),
                                                       'join'        => array( array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'm' => 'ugroup' ) ) ),
                                                        'where'    => array( array( 'm' => 'id' ), '=', $cookie_uid ),
                                                        'limit'    => array( 0, 1 ),
                                                 )     );

            $this->trellis->db->execute();

            $this->user = $this->trellis->db->fetch_row();

            #=============================
            # Checkie Checkie
            #=============================

            if ( $this->user['login_key'] == hash( 'whirlpool', $cookie_hash . $this->trellis->config['cookie_key'] ) )
            {
                #=============================
                # Create Session
                #=============================

                $session_hash = 's'. $this->user['id'] . uniqid( rand(), true );

                $new_session = sha1( $session_hash . $this->trellis->config['session_key'] );

                $db_array = array(
                                  's_id'            => $new_session,
                                  's_uid'            => $this->user['id'],
                                  's_uname'            => $this->user['name'],
                                  's_email'            => $this->user['email'],
                                  's_ipadd'            => $this->trellis->input['ip_address'],
                                  's_location'        => $this->trellis->input['act'],
                                  's_time'            => time(),
                                  );

                $this->trellis->db->construct( array(
                                                           'insert'    => 'sessions',
                                                           'set'        => $db_array,
                                                     )     );

                $this->trellis->db->execute();

                #=============================
                # New Login Key
                #=============================

                $rmsalt = '';

                while( strlen( $rmsalt ) < 8 ) $rmsalt .= chr(rand( 32,126 ) );

                $rmsalt .= uniqid( rand(), true );

                $rmhash = str_replace( "=", "", base64_encode( sha1( $rmsalt . $this->user['id'] ) ) );

                $lk_hash = hash( 'whirlpool', $rmhash . $this->trellis->config['cookie_key'] );

                $this->trellis->db->construct( array(
                                                           'update'    => 'users',
                                                           'set'        => array( 'login_key' => $lk_hash ),
                                                            'where'    => array( 'id', '=', $this->user['id'] ),
                                                            'limit'    => array( 1 ),
                                                     )     );

                $this->trellis->db->execute();

                $this->trellis->set_cookie( 'tdrmhash', $rmhash );
                $this->trellis->set_cookie( 'tdsid', $session_hash, time() + ( $this->trellis->cache->data['settings']['security']['session_timeout'] * 60 ) );

                // Permissions
                $this->user['g_depart_perm'] = unserialize( $this->user['g_depart_perm'] );
                $this->user['g_kb_perm'] = unserialize( $this->user['g_kb_perm'] );

                // Sub-Groups
                $this->user['ugroup_sub'] = unserialize( $this->user['ugroup_sub'] );

                if ( is_array( $this->user['ugroup_sub'] ) && ! empty( $this->user['ugroup_sub'] ) )
                {
                    $this->users = array_merge( $this->user, $this->merge_groups( $this->user, $this->user['ugroup_sub'] ) );
                }

                $authorized = 1;
            }
            else
            {
                $this->trellis->delete_cookie('tduid');
                $this->trellis->delete_cookie('tdrmhash');
            }
        }

        #=============================
        # If We Are Not Authorized
        #=============================

        if ( ! $authorized )
        {
            $this->user['id']    = 0;
            $this->user['name'] = $this->trellis->lang['guest'];
            $this->user['ugroup'] = 2;

            #=============================
            # Create Session
            #=============================

            $session_hash = 's'. $this->user['id'] . uniqid( rand(), true );

            $new_session = sha1( $session_hash . $this->trellis->config['session_key'] );

            $db_array = array(
                              's_id'            => $new_session,
                              's_uid'            => $this->user['id'],
                              's_uname'            => $this->user['name'],
                              's_ipadd'            => $this->trellis->input['ip_address'],
                              's_location'        => $this->trellis->input['act'],
                              's_time'            => time(),
                              's_guest'            => 1,
                              );

            $this->trellis->db->construct( array(
                                                       'insert'    => 'sessions',
                                                       'set'        => $db_array,
                                                 )     );

            $this->trellis->db->execute();

            $this->trellis->set_cookie( 'tdsid', $session_hash, time() + ( $this->trellis->cache->data['settings']['security']['session_timeout'] * 60 ) );

            $this->user['s_id'] = $new_session;

            $this->user = array_merge( $this->user, $this->trellis->cache->data['groups'][2] );

            // Permissions
            $this->user['g_depart_perm'] = unserialize( $this->user['g_depart_perm'] );
            $this->user['g_kb_perm'] = unserialize( $this->user['g_kb_perm'] );
        }

        // Force Login?
        if ( $this->trellis->cache->data['settings']['security']['force_login'] && ! $this->user['id'] )
        {
            $this->error( 'force_login', 1 );
        }

        return $this->user;
    }

    #=======================================
    # @ Do Login
    #=======================================

    public function do_login()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['username'] || ! $this->trellis->input['password'] ) $this->error( 'fill_form_completely', 1 );

        #=============================
        # Select User
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array(
                                                                     'm' => array( 'id', 'name', 'email', 'pass_hash', 'pass_salt', 'ugroup', 'ugroup_sub', 'title', 'joined', 'signature', 'sig_html', 'sig_auto', 'lang', 'skin', 'time_zone', 'time_dst', 'rte_enable', 'email_enable', 'email_ticket', 'email_action', 'email_news', 'email_type', 'tickets_total', 'tickets_open', 'val_email', 'val_admin' ),
                                                                     'g' => 'all',
                                                                        ),
                                                   'from'    => array( 'm' => 'users' ),
                                                   'join'    => array( array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'm' => 'ugroup' ) ) ),
                                                   'where'    => array( array( 'm' => 'name|lower' ), '=', strtolower( $this->trellis->input['username'] ) ),
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() )
        {
            $this->error('login_no_user', 1);

            # TD LOG: name not found
        }

        $mem = $this->trellis->db->fetch_row();

        #=============================
        # Compare Password
        #=============================

        if ( hash( 'whirlpool', $mem['pass_salt'] . $this->trellis->input['password'] . $this->trellis->config['pass_key'] ) == $mem['pass_hash'] )
        {
            #=============================
            # Validation Check
            #=============================

            if ( ! $mem['val_email'] ) $this->error('login_must_val'); # TD LOG: no email val

            if ( ! $mem['val_admin'] ) $this->error('login_must_val_admin'); # TD LOG: no admin val

            #=============================
            # Delete Old Sessoin
            #=============================

            if ( $this->user['s_id'] )
            {
                $this->trellis->db->construct( array(
                                                           'delete'    => 'sessions',
                                                            'where'    => array( 's_id', '=', $this->user['s_id'] ),
                                                            'limit'    => array( 1 ),
                                                     )     );

                $this->trellis->db->execute();
            }

            #=============================
            # Create Session
            #=============================

            $session_hash = 's'. $this->user['id'] . uniqid( rand(), true );

            $new_session = sha1( $session_hash . $this->trellis->config['session_key'] );

            $db_array = array(
                              's_id'            => $new_session,
                              's_uid'            => $mem['id'],
                              's_uname'            => $mem['name'],
                              's_email'            => $mem['email'],
                              's_ipadd'            => $this->trellis->input['ip_address'],
                              's_location'        => $this->trellis->input['act'],
                              's_time'            => time(),
                              );

            $this->trellis->db->construct( array(
                                                       'insert'    => 'sessions',
                                                       'set'        => $db_array,
                                                 )     );

            $this->trellis->db->execute();

            $this->trellis->set_cookie( 'tdsid', $session_hash, time() + ( $this->trellis->cache->data['settings']['security']['session_timeout'] * 60 ) );

            #=============================
            # Remember Me?
            #=============================

            if ( $this->trellis->input['remember'] )
            {
                #=============================
                # New Login Key
                #=============================

                $rmsalt = '';

                while( strlen( $rmsalt ) < 8 ) $rmsalt .= chr(rand( 32,126 ) );

                $rmsalt .= uniqid( rand(), true );

                $rmhash = str_replace( "=", "", base64_encode( sha1( $rmsalt . $mem['id'] ) ) );

                $lk_hash = hash( 'whirlpool', $rmhash . $this->trellis->config['cookie_key'] );

                $this->trellis->db->construct( array(
                                                           'update'    => 'users',
                                                           'set'        => array( 'login_key' => $lk_hash ),
                                                            'where'    => array( 'id', '=', $mem['id'] ),
                                                            'limit'    => array( 1 ),
                                                     )     );

                $this->trellis->db->execute();

                $this->trellis->set_cookie( 'tduid', $mem['id'] );
                $this->trellis->set_cookie( 'tdrmhash', $rmhash );
            }

            #=============================
            # Return
            #=============================

            // Play It Safe
            $mem['pass_hash'] = $mem['pass_salt'] = "";

            $mem = array_merge( $mem, $db_array );

            $this->user = $mem;

            // Permissions
            $this->user['g_depart_perm'] = unserialize( $this->user['g_depart_perm'] );
            $this->user['g_kb_perm'] = unserialize( $this->user['g_kb_perm'] );

            // Sub-Groups
            $this->user['ugroup_sub'] = unserialize( $this->user['ugroup_sub'] );

            if ( is_array( $this->user['ugroup_sub'] ) && ! empty( $this->user['ugroup_sub'] ) )
            {
                $this->users = array_merge( $this->user, $this->merge_groups( $this->user, $this->user['ugroup_sub'] ) );
            }

            return $this->user;
        }
        else
        {
            $this->error('login_no_pass', 1); # TD Log: incorrect pass
        }
    }

    #=======================================
    # @ Do Guest Login
    #=======================================

    public function do_guest_login()
    {
        #=============================
        # Security Checks
        #=============================

        if ( $onthefly ) # TODO: guest tickets
        {
            $this->trellis->input['email_address'] = $this->trellis->input['email'];
            $this->trellis->input['ticket_key'] = $this->trellis->input['key'];
        }

        if ( ! $this->trellis->validate_email( $this->trellis->input['email_address'] ) )
        {
            $this->error('no_valid_email');
        }

        #=============================
        # Select Ticket
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id', 'uname', 'email' ),
                                                   'from'        => 'tickets',
                                                    'where'    => array( array( 'tkey', '=', $this->trellis->input['ticket_key'] ), array( 'email', '=', $this->trellis->input['email_address'], 'and' ), array( 'guest', '=', 1, 'and' ) ),
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() != 1 )
        {
            $this->error('no_ticket_guest'); # TD LOG: guest ticket not found
        }

        $ticket = $this->trellis->db->fetch_row();

        #=============================
        # Update Session
        #=============================

        $new_session = sha1( 's'. time() . $mem['id'] . uniqid( rand(), true ) . $this->trellis->config['session_key'] );

        $db_array = array( 's_uname' => $ticket['uname'], 's_email' => $ticket['email'], 's_tkey' => $this->trellis->input['ticket_key'] );

        $this->trellis->db->construct( array(
                                                   'update'    => 'sessions',
                                                   'set'        => $db_array,
                                                   'where'    => array( 's_id', '=', $this->user['s_id'] ),
                                                   'limit'    => array( 1 ),
                                             )     );

        $this->trellis->db->execute();

        $mem = array_merge( $mem, $db_array );

        $this->user = $mem;

        return $this->user;
    }

    #=======================================
    # @ Do Logout
    #=======================================

    public function do_logout()
    {
        if ( ! $this->trellis->user['id'] ) $this->error('logout_already_guest');

        #=============================
        # Delete Cookies
        #=============================

        $this->trellis->delete_cookie('tdsid');
        $this->trellis->delete_cookie('tduid');
        $this->trellis->delete_cookie('tdrmhash');

        #=============================
        # Delete Session
        #=============================

        $this->trellis->db->construct( array(
                                                   'delete'    => 'sessions',
                                                    'where'    => array( 's_id', '=', $this->user['s_id'] ),
                                                    'limit'    => array( 1 ),
                                             )     );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'update'    => 'users',
                                                   'set'        => array( 'login_key' => '' ),
                                                    'where'    => array( 'id', '=', $this->user['s_uid'] ),
                                                    'limit'    => array( 1 ),
                                                )     );

        $this->trellis->db->execute();

        #=============================
        # Back to Guest
        #=============================

        $this->user = array();

        $this->user['id']    = 0;
        $this->user['name'] = $this->trellis->lang['guest'];
        $this->user['ugroup'] = 2;

        #=============================
        # Create Session
        #=============================

        $session_hash = 's'. $this->user['id'] . uniqid( rand(), true );

        $new_session = sha1( $session_hash . $this->trellis->config['session_key'] );

        $db_array = array(
                          's_id'            => $new_session,
                          's_uid'            => $this->user['id'],
                          's_uname'            => $this->user['name'],
                          's_ipadd'            => $this->trellis->input['ip_address'],
                          's_location'        => $this->trellis->input['act'],
                          's_time'            => time(),
                          's_guest'            => 1,
                          );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'sessions',
                                                   'set'        => $db_array,
                                             )     );

        $this->trellis->db->execute();

        $this->trellis->set_cookie( 'tdsid', $session_hash, time() + ( $this->trellis->cache->data['settings']['security']['session_timeout'] * 60 ) );

        $this->user['s_id'] = $new_session;

        $this->user = array_merge( $this->user, $this->trellis->cache->data['groups'][2] );

        // Permissions
        $this->user['g_depart_perm'] = unserialize( $this->user['g_depart_perm'] );

        // Force Login?
        if ( $this->trellis->cache->data['settings']['security']['force_login'] && ! $this->user['id'] )
        {
            $this->error( 'force_login', 1 );
        }

        return $this->user;
    }

    #=======================================
    # @ Update Session
    #=======================================

    public function update_session()
    {
        parse_str( $_SERVER['QUERY_STRING'], $location );
        
        $this->trellis->db->construct( array(
                                                   'update'    => 'sessions',
                                                   'set'        => array( 's_location' => serialize( $location ), 's_time' => time() ),
                                                    'where'    => array( 's_id', '=', $this->user['s_id'] ),
                                                    'limit'    => array( 1 ),
                                             )     );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();
    }

    #=======================================
    # @ Kill Old Sessions
    #=======================================

    private function kill_old_sessions()
    {
        $timeout = time() - ( $this->trellis->cache->data['settings']['security']['session_timeout'] * 60 );

        $this->trellis->db->construct( array(
                                                   'delete'    => 'sessions',
                                                    'where'    => array( 's_time', '<=', $timeout ),
                                             )     );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        $num_killed = $this->trellis->db->get_affected_rows();

        return $num_killed;
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
        }

        return $fg;
    }

    #=======================================
    # @ Error
    #=======================================

    private function error($msg, $login=0)
    {
        $this->trellis->user = $this->user; # TODO: I don't like this =/

        $this->trellis->load_skin();

        $this->trellis->skin->error($msg, $login);
    }
}

?>