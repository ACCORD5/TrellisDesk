<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_source_tickets {

    private $parsed_sigs;

    #=======================================
    # @ Auto Run
    #=======================================

    function auto_run()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['g_ticket_access'] )
        {
            if ( $this->trellis->user['id'] )
            {
                $this->trellis->log( 'security', "Blocked Access Ticket Center" );

                $this->trellis->skin->error('banned_ticket');
            }
        }

        # TODO: Guest Support
        if ( ! $this->trellis->user['id'] )
        {
            $this->trellis->log( 'security', "Blocked Guest Access Ticket Center" );

            $this->trellis->skin->error( 'must_be_user', 1 );
        }

        #=============================
        # Initialize
        #=============================

        $this->trellis->load_functions('tickets');
        $this->trellis->load_lang('tickets');

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_tickets();
            break;
            case 'view':
                $this->view_ticket();
            break;
            case 'add':
                $this->add_ticket();
            break;
            case 'edit':
                $this->edit_ticket();
            break;

            case 'doescalate':
                $this->do_escalate();
            break;
            case 'doclose':
                $this->do_close();
            break;
            case 'doreopen':
                $this->do_reopen();
            break;

            case 'doadd':
                $this->do_add();
            break;
            case 'doedit':
                $this->do_edit();
            break;
            case 'dodel':
                $this->do_delete();
            break;

            case 'editreply':
                $this->edit_reply();
            break;

            case 'doaddreply':
                $this->do_add_reply();
            break;
            case 'doeditreply':
                $this->do_edit_reply();
            break;
            case 'dodelreply':
                $this->do_delete_reply();
            break;

            default:
                $this->list_tickets();
            break;
        }
    }

    #=======================================
    # @ List Tickets
    #=======================================

    private function list_tickets($params=array())
    {
        # TODO: if displaying last reply user name column, remember to honor hide group names setting

        #=============================
        # Get Tickets
        #=============================

        $t_total = $this->trellis->func->tickets->get( array(
                                                       'select'    => array(
                                                                            't' => array( 'id' ),
                                                                         ),
                                                       'from'    => array( 't' => 'tickets' ),
                                                       'join'    => array(
                                                                         array( 'from' => array( 'd' => 'departments' ), 'where' => array( 't' => 'did', '=', 'd' => 'id' ) ),
                                                                         array( 'from' => array( 'p' => 'priorities' ), 'where' => array( 't' => 'priority', '=', 'p' => 'id' ) ),
                                                                         array( 'from' => array( 's' => 'statuses' ), 'where' => array( 't' => 'status', '=', 's' => 'id' ) ),
                                                                         ),
                                                       'where'    => array( array( 't' => 'uid' ), '=', $this->trellis->user['id'] ),
                                                )       );

        $tickets = $this->trellis->func->tickets->get( array(
                                                       'select'    => array(
                                                                            't' => array( 'id', 'mask', 'subject', 'priority', 'last_reply', 'escalated', 'status' ),
                                                                            'd' => array( array( 'name' => 'dname' ) ),
                                                                            'p' => array( array( 'name' => 'pname' ), 'icon_regular', 'icon_assigned' ),
                                                                         's' => array( array( 'name_user' => 'status_name' ), array( 'abbr_user' => 'status_abbr' ) ),
                                                                         ),
                                                       'from'    => array( 't' => 'tickets' ),
                                                       'join'    => array(
                                                                         array( 'from' => array( 'd' => 'departments' ), 'where' => array( 't' => 'did', '=', 'd' => 'id' ) ),
                                                                         array( 'from' => array( 'p' => 'priorities' ), 'where' => array( 't' => 'priority', '=', 'p' => 'id' ) ),
                                                                         array( 'from' => array( 's' => 'statuses' ), 'where' => array( 't' => 'status', '=', 's' => 'id' ) ),
                                                                         ),
                                                       'where'    => array( array( 't' => 'uid' ), '=', $this->trellis->user['id'] ),
                                                       'order'    => array( 'last_reply' => array( 't' => 'desc' ) ),
                                                       'limit'    => array( $this->trellis->input['st'], 15 ),
                                                )       );

        #=============================
        # Prepare Output
        #=============================

        if ( $params['error'] )
        {
            $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $params['error'] ] );
        }
        elseif ( $params['alert'] )
        {
            $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $params['alert'] ] );
        }

        #=============================
        # Fix Up Information
        #=============================

        $row_count = 0;

        if ( $tickets )
        {
            foreach ( $tickets as &$t )
            {
                if ( $t['date'] ) $t['date_human'] = $this->trellis->td_timestamp( array( 'time' => $t['date'], 'format' => 'short' ) );
                if ( $t['last_reply'] ) $t['last_reply_human'] = $this->trellis->td_timestamp( array( 'time' => $t['last_reply'], 'format' => 'short' ) );

                if ( ! $t['status_abbr'] ) $t['status_abbr'] = $t['status_name'];
            }

            $this->trellis->skin->set_var( 'tickets', $tickets );
        }

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $t_total ),
                                                        'per_page'    => 15,
                                                        'url'        => $this->generate_url( array( 'st' => 0 ) ),
                                                        ) );

        $this->trellis->skin->set_var( 'page_links', $page_links );

        $this->trellis->skin->set_var( 'sub_tpl', 'tickets.tpl' );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets">'. $this->trellis->lang['ticket_history'] .'</a>',
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['ticket_history'] ) );
    }

    #=======================================
    # @ View Ticket
    #=======================================

    function view_ticket($params=array())
    {
        #=============================
        # Grab Ticket
        #=============================

        # CHECK: Do we really need this extra query?

        $ticket_id = $this->trellis->func->tickets->get_id_from_mask( $this->trellis->input['id'] );

        # CHECK: Do we really want to grab ALL ticket fields? Same with reply fields?

        $t = $this->trellis->func->tickets->get_single_by_id( array(
                                                              'select'    => array(
                                                                                    't' => 'all',
                                                                                    'u' => array( array( 'name' => 'uname' ), array( 'email' => 'uemail' ), 'ugroup' ),
                                                                                    'lr' => array( array( 'name' => 'last_uname' ) ),
                                                                                    ),
                                                              'from'    => array( 't' => 'tickets' ),
                                                              'join'    => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 't' => 'uid', '=', 'u' => 'id' ) ), array( 'from' => array( 'lr' => 'users' ), 'where' => array( 't' => 'last_uid', '=', 'lr' => 'id' ) ) ),
                                                       ), $ticket_id );

        if ( ! $t || $t['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_ticket');

        #=============================
        # Grab Replies
        #=============================

        $replies = $this->trellis->func->tickets->get( array(
                                                       'select'    => array(
                                                                            'r' => 'all',
                                                                            'u' => array( array( 'name' => 'uname' ), array( 'signature' => 'usignature' ), 'sig_html' ),
                                                                            ),
                                                       'from'    => array( 'r' => 'replies' ),
                                                       'join'    => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'r' => 'uid', '=', 'u' => 'id' ) ) ),
                                                       'where'    => array( array( array( 'r' => 'tid' ), '=', $t['id'] ), array( array( 'r' => 'secret' ), '!=', 1, 'and' ) ),
                                                       'order'    => array( 'date' => array( 'r' => 'asc' ) ),
                                                )       );

        #=============================
        # Custom Department Fields
        #=============================

        $this->trellis->load_functions('cdfields');

        if ( $cdfields = $this->trellis->func->cdfields->grab( $t['did'] ) )
        {
            $fdata = $this->trellis->func->cdfields->get_data( $t['id'] );

            foreach( $cdfields as &$f )
            {
                if ( $f['type'] == 'checkbox' )
                {
                    $f['extra'] = unserialize( $f['extra'] );
                }
                elseif ( $f['type'] == 'dropdown' || $f['type'] == 'radio' )
                {
                    $f['extra'] = unserialize( $f['extra'] );
                }
            }

            $this->trellis->skin->set_var( 'cdfields', $cdfields );
            $this->trellis->skin->set_var( 'cdfdata', $fdata );
        }

        #=============================
        # Format Ticket
        #=============================

        // Hide Staff Name
        if ( $this->trellis->cache->data['staff'][ $t['last_uid'] ]['g_hide_names'] ) $t['last_uname'] = $this->trellis->cache->data['groups'][ $this->trellis->cache->data['staff'][ $t['last_uid'] ]['ugroup'] ]['g_name'];

        $t['priority_icon'] = $this->trellis->cache->data['priorities'][ $t['priority'] ]['icon_regular'];
        $t['priority_human'] = $this->trellis->cache->data['priorities'][ $t['priority'] ]['name'];

        $t['status_human'] = $this->trellis->cache->data['statuses'][ $t['status'] ]['name_user'];
        $t['dname'] = $this->trellis->cache->data['departs'][ $t['did'] ]['name'];

        $t['date_human'] = $this->trellis->td_timestamp( array( 'time' => $t['date'], 'format' => 'long' ) );
        $t['last_reply_human'] = $this->trellis->td_timestamp( array( 'time' => $t['last_reply'], 'format' => 'long' ) );

        $toutput_params = array( 'linkify' => 1 );

        if ( $t['html'] )
        {
            $toutput_params['html'] = 1;
        }
        else
        {
            $toutput_params['paragraphs'] = 1;
            $toutput_params['nl2br'] = 1;
        }

        $t['message'] = $this->trellis->prepare_output( $t['message'], $toutput_params );

        // Permissions for Template
        ( $t['last_reply_staff'] ) ? $escalate_time = $t['last_reply_staff'] : $escalate_time = $t['date'];

        if ( ! $t['escalated'] && ! $t['closed'] && ( $escalate_time < ( time() - ( $this->trellis->cache->data['departs'][ $t['did'] ]['escalate_wait'] * 60 ) ) ) && $this->trellis->cache->data['departs'][ $t['did'] ]['escalate_enable'] && $this->trellis->user['g_ticket_escalate'] ) $t['can_escalate'] = 1;

        if ( ! $t['closed'] && $this->trellis->user['g_ticket_close'] && $this->trellis->cache->data['departs'][ $t['did'] ]['close_own'] )
        {
            $t['can_close'] = 1;
        }
        elseif ( $t['closed'] && $t['allow_reopen'] && $this->trellis->user['g_ticket_reopen'] && $this->trellis->cache->data['departs'][ $t['did'] ]['reopen_own'] )
        {
            $t['can_reopen'] = 1;
        }

        #=============================
        # Grab Ratings?
        #=============================

        /*if ( $this->trellis->user['id'] && $this->trellis->cache->data['settings']['tickets']['rating'] && $this->trellis->user['g_reply_rate'] )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => 'all',
                                                       'from'        => 'reply_rate',
                                                        'where'    => array( 'tid', '=', $t['id'] ),
                                                 )     );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() )
            {
                while ( $rt = $this->trellis->db->fetch_row() )
                {
                    $ratings[ $rt['rid'] ] = $rt['rating'];
                }
            }
        }*/

        #=============================
        # Format Replies
        #=============================

        if ( $replies )
        {
            $row_count = 0;

            foreach ( $replies as &$r )
            {
                #=============================
                # Fix Up Information
                #=============================

                # CHECK: necessary to hide staff id as well? i don't think so because it shouldn't be displayed. changing it would affect the coding below (sigs, etc). if changed, update all similar coding.
                // Hide Staff Name
                if ( $r['staff'] && $this->trellis->cache->data['staff'][ $r['uid'] ]['g_hide_names'] ) $r['uname'] = $this->trellis->cache->data['groups'][ $this->trellis->cache->data['staff'][ $r['uid'] ]['ugroup'] ]['g_name'];

                $r['time_ago'] = $this->trellis->td_timestamp( array( 'time' => $r['date'], 'format' => 'relative' ) );

                $r['date_human'] = $this->trellis->td_timestamp( array( 'time' => $r['date'], 'format' => 'short' ) );

                $routput_params = array( 'urls' => 1 );

                if ( $r['html'] )
                {
                    $routput_params['html'] = 1;
                }
                else
                {
                    $routput_params['paragraphs'] = 1;
                    $routput_params['nl2br'] = 1;
                }

                $r['message'] = $this->trellis->prepare_output( $r['message'], $routput_params );

                if ( $r['signature'] )
                {
                    $soutput_params = array( 'linkify' => 1 );

                    if ( $r['sig_html'] )
                    {
                        $soutput_params['html'] = 1;
                    }
                    else
                    {
                        $soutput_params['paragraphs'] = 1;
                        $soutput_params['nl2br'] = 1;
                    }

                    if ( ! $this->parsed_sigs[ $r['uid'] ] ) $this->parsed_sigs[ $r['uid'] ] = $this->trellis->prepare_output( $r['usignature'], $soutput_params ); # CHECK: Let's not parse the signature over and over again

                    $r['message'] .= '<p>'. $this->parsed_sigs[ $r['uid'] ] .'</p>';
                }

                // Permissions for Templates
                if ( ! $t['closed'] && $r['uid'] == $this->trellis->user['id'] )
                {
                    if ( $this->trellis->user['g_reply_edit'] ) $r['can_edit'] = 1;
                    if ( $this->trellis->user['g_reply_delete'] ) $r['can_delete'] = 1;
                }

                # TODO: don't forget about ratings!

                /*if ( $this->trellis->user['id'] && $this->trellis->cache->data['settings']['tickets']['rating'] && $this->trellis->user['g_reply_rate'] && $r['staff'] && ! $this->trellis->user['ban_ticket_rate'] )
                {
                    if ( $ratings[ $r['id'] ] )
                    {
                        $r['rate_imgs'] = $this->rate_thumbs_already( $ratings[ $r['id'] ] );
                        $r['rate_imgs_solo'] = $this->rate_thumbs_already( $ratings[ $r['id'] ] );
                    }
                    else
                    {
                        $r['rate_imgs'] = $this->rate_thumbs( $t['id'], $r['id'] );
                        $r['rate_imgs_solo'] = $this->rate_thumbs( $t['id'], $r['id'] );
                    }
                }

                $reply_edit_icon = 0;
                $reply_delete_icon = 0;

                if ( $this->trellis->user['g_reply_edit'] && $r['uid'] == $this->trellis->user['id'] && ! $r['staff'] )
                {
                    $r['rate_imgs'] = "&nbsp;&nbsp;<span class='response_imgs'><a href='". $this->trellis->config['hd_url'] ."/index.php?page=tickets&amp;act=editreply&amp;id={$r['id']}'><img src='images/". $this->trellis->skin->data['img_dir'] ."/edit_icon.gif' /></a>";

                    $reply_edit_icon = 1;
                }

                if ( $this->trellis->user['g_reply_delete'] && $r['uid'] == $this->trellis->user['id'] && ! $r['staff'] )
                {
                    if ( $reply_edit_icon )
                    {
                        $r['rate_imgs'] .= '&nbsp;&nbsp;&nbsp;';
                    }
                    else
                    {
                        $r['rate_imgs'] = "&nbsp;&nbsp;<span class='response_imgs'>";
                    }

                    $r['rate_imgs'] .= "<a href='". $this->trellis->config['hd_url'] ."/index.php?page=tickets&amp;act=dodelreply&amp;id={$r['id']}' onclick='return sure_delete_reply()'><img src='images/". $this->trellis->skin->data['img_dir'] ."/delete_icon.gif' /></a>";

                    $reply_delete_icon = 1;
                }

                if ( $reply_edit_icon || $reply_delete_icon ) $r['rate_imgs'] .= '</span>';*/
            }

            $this->trellis->skin->set_var( 'replies', $replies );
        }

        #=============================
        # Do Output
        #=============================

        // Permissions for Template
        if ( $t['status'] != 6 )
        {
            if ( $this->trellis->user['g_ticket_edit'] ) $t['can_edit'] = 1;

            $t['can_reply'] = 1;
        }

        if ( $params['error'] ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $params['error'] ] );
        if ( $params['alert'] ) $this->trellis->skin->set_var( 'alert', $this->trellis->lang[ 'alert_'. $params['alert'] ] );
        if ( $params['error_reply'] ) $this->trellis->skin->set_var( 'error_reply', $this->trellis->lang[ 'err_'. $params['reply_error'] ] );

        if ( $params['scroll'] ) $this->trellis->skin->set_var( 'scroll', $params['scroll'] );

        if ( $this->trellis->cache->data['settings']['ticket']['attachments'] && $this->trellis->user['g_ticket_attach'] && $this->trellis->cache->data['departs'][ $t['did'] ]['allow_attach'] )
        {
            #$this->trellis->skin->set_var( 'upload_form', $this->trellis->skin->upload_form( 'upload_file', array( 'page' => 'tickets', 'act' => 'doupload', 'type' => 'reply', 'id' => $t['id'] ), array( 'multi' => true ) ) );
        }

        $this->trellis->skin->set_var( 't', $t );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets">'. $this->trellis->lang['ticket_history'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets&amp;act=view&amp;id='. $t['mask'] .'">'. $t['subject'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'tck_view.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['tickets'] .' :: '. $t['subject'] ) );
    }

    #=======================================
    # @ Add Ticket
    #=======================================

    private function add_ticket()
    {
        # TODO: better error messages / perms check

        if ( ! $this->trellis->cache->data['settings']['ticket']['new_tickets'] )
        {
            $this->trellis->skin->error('new_tickets_disabled');
        }

        if ( ! $this->trellis->user['g_ticket_create'] )
        {
            $this->trellis->skin->error('no_perm');
        }

        switch( $this->trellis->input['step'] )
        {
            case 1:
                $this->add_ticket_step_1();
            break;
            case 2:
                $this->add_ticket_step_2();
            break;

            default:
                $this->add_ticket_step_1();
            break;
        }
    }

    #=======================================
    # @ Add Ticket Step 1
    #=======================================

    function add_ticket_step_1($params=array())
    {
        #=============================
        # Do Output
        #=============================

        if ( $params['error'] ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $params['error'] ] );

        $departs = array();

        $perms = &$this->trellis->user['g_depart_perm'];

        foreach( $this->trellis->cache->data['departs'] as $id => $d )
        {
            if ( $perms[ $d['id'] ] ) $departs[] = $d;
        }

        $this->trellis->skin->set_var( 'departs', $departs );

        $this->trellis->skin->set_var( 'sub_tpl', 'tck_submit_1.tpl' );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets">'. $this->trellis->lang['tickets'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets&amp;act=add">'. $this->trellis->lang['open_ticket'] .'</a>',
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['tickets'] .' :: '. $this->trellis->lang['open_ticket'] ) );
    }

    #=======================================
    # @ Add Ticket Step 2
    #=======================================

    function add_ticket_step_2($params=array())
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['did'] ) $this->add_ticket_step_1( array( 'error' => 'no_depart' ) );

        $perms = &$this->trellis->user['g_depart_perm'];

        if ( ! $perms[ $this->trellis->input['did'] ] ) $this->add_ticket_step_1( array( 'error' => 'no_depart' ) ); // use more detailed msg

        #=============================
        # Custom Profile Fields
        #=============================

        $this->trellis->load_functions('cdfields');

        if ( $cdfields = $this->trellis->func->cdfields->grab( $this->trellis->input['did'] ) )
        {
            foreach( $cdfields as &$f )
            {
                if ( $f['type'] == 'checkbox' )
                {
                    $f['extra'] = unserialize( $f['extra'] );
                }
                elseif ( $f['type'] == 'dropdown' || $f['type'] == 'radio' )
                {
                    $f['extra'] = unserialize( $f['extra'] );
                }
            }

            $this->trellis->skin->set_var( 'cdfields', $cdfields );
        }

        #=============================
        # Do Output
        #=============================

        # TODO: article suggestions as you type

        $this->trellis->skin->set_var( 'dname', $this->trellis->cache->data['departs'][ $this->trellis->input['did'] ]['name'] );

        if ( $params['error'] )
        {
            if ( $params['field'] )
            {
                $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $params['error'] ] .' '. $params['field'] );
            }
            else
            {
                $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $params['error'] ] );
            }
        }

        $priority_options = array();

        foreach ( $this->trellis->cache->data['priorities'] as $p )
        {
            $priority_options[ $p['id'] ] = $p['name'];
        }

        $this->trellis->skin->set_var( 'priority_options', $priority_options );

        $this->trellis->skin->set_var( 'sub_tpl', 'tck_submit_2.tpl' );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets">'. $this->trellis->lang['tickets'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets&amp;act=add">'. $this->trellis->lang['open_ticket'] .'</a>',
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['tickets'] .' :: '. $this->trellis->lang['open_ticket'] ) );
    }

    #=======================================
    # @ Do Add
    #=======================================

    function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['did'] ) $this->add_ticket_step_1( array( 'error' => 'no_depart' ) );
        if ( ! $this->trellis->input['subject'] ) $this->add_ticket_step_2( array( 'error' => 'no_subject' ) );
        if ( ! $this->trellis->input['priority'] ) $this->add_ticket_step_2( array( 'error' => 'no_priority' ) );
        if ( ! $this->trellis->input['message'] ) $this->add_ticket_step_2( array( 'error' => 'no_message' ) );

        if ( ! $this->trellis->cache->data['departs'][ $this->trellis->input['did'] ] ) $this->trellis->skin->error('no_depart');

        $perms = &$this->trellis->user['g_depart_perm'];

        if ( ! $perms[ $this->trellis->input['did'] ] ) $this->add_ticket_step_1( array( 'error' => 'no_depart' ) );

        #=============================
        # Add Ticket
        #=============================

        $db_array = array(
                          'did'            => $this->trellis->input['did'],
                          'uid'            => $this->trellis->user['id'],
                          'email'        => $this->trellis->user['email'],
                          'subject'        => $this->trellis->input['subject'],
                          'priority'    => $this->trellis->input['priority'],
                          'message'        => $this->trellis->input['message'],
                          'date'        => time(),
                          'last_reply'    => time(),
                          'last_uid'    => $this->trellis->user['id'],
                          'ipadd'        => $this->trellis->input['ip_address'],
                          'status'        => $this->trellis->cache->data['misc']['default_statuses'][1],
                          'uname'        => $this->trellis->user['name'],
                         );

        $this->trellis->load_functions('cdfields');

        if( ! $fdata = $this->trellis->func->cdfields->process_input( $this->trellis->input['did'] ) )
        {
            if ( $this->trellis->func->cdfields->required_field ) $this->add_ticket_step_2( array( 'error' => 'no_field', 'field' => $this->trellis->func->cdfields->required_field ) );
        }

        $ticket = $this->trellis->func->tickets->add( $db_array, array( 'return' => 'mask' ) );

        if ( $fdata ) $this->trellis->func->cdfields->set_data( $fdata, $ticket['id'], 1 );

        $this->trellis->log( 'user', "Ticket Created &#039;". $this->trellis->input['subject'] ."&#039;", 1, $ticket['id'] );
        $this->trellis->log( 'ticket', "Ticket Created &#039;". $this->trellis->input['subject'] ."&#039;", 1, $ticket['id'] );

        #=============================
        # Do Output
        #=============================

        $this->trellis->input['id'] = $ticket['mask'];

        $this->view_ticket( array( 'alert' => 'ticket_added' ) );
    }

    #=======================================
    # @ Do Add Reply
    #=======================================

    function do_add_reply()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['message'] ) $this->view_ticket( array( 'reply_error' => 'no_message' ) );

        $ticket_id = $this->trellis->func->tickets->get_id_from_mask( $this->trellis->input['id'] );

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'subject', 'priority', 'message', 'aua', 'closed' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ) ) ), $ticket_id ) ) $this->trellis->skin->error('no_ticket');

        if ( $t['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        #=============================
        # Add Reply
        #=============================

        $db_array = array(
                          'tid'                    => $t['id'],
                          'uid'                    => $this->trellis->user['id'],
                          'message'                => $this->trellis->input['message'],
                          'date'                => time(),
                          'ipadd'                => $this->trellis->input['ip_address'],
                          'mask'                => $t['mask'],
                          'did'                    => $t['did'],
                          'tuid'                => $t['uid'],
                          'tuname'                => $t['uname'],
                          'subject'                => $t['subject'],
                          'priority'            => $t['priority'],
                          'message_original'    => $t['message'],
                         );

        $reply_id = $this->trellis->func->tickets->add_reply( $db_array, $t['id'], array( 'aua' => $t['aua'] ) );

        $this->trellis->log( 'user', "Ticket Reply Added &#039;". $t['subject'] ."&#039;", 1, $t['id'] );
        $this->trellis->log( 'ticket', "Ticket Reply Added &#039;". $t['subject'] ."&#039;", 1, $t['id'] );

        #=============================
        # Do Output
        #=============================

        $this->view_ticket( array( 'alert' => 'reply_added', 'scroll' => 'r'. $reply_id ) );
    }

    #=======================================
    # @ Do Escalate
    #=======================================

    function do_escalate()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->cache->data['settings']['ticket']['escalate'] || ! $this->trellis->user['g_ticket_escalate'] ) $this->trellis->skin->error('no_perm');

        $ticket_id = $this->trellis->func->tickets->get_id_from_mask( $this->trellis->input['id'] );

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'subject', 'priority', 'message', 'date', 'last_reply_staff', 'escalated', 'closed' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ) ) ), $ticket_id ) ) $this->trellis->skin->error('no_ticket');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        ( $t['last_reply_staff'] ) ? $escalate_time = $t['last_reply_staff'] : $escalate_time = $t['date'];

        if ( $t['uid'] != $this->trellis->user['id'] || $t['escalated'] || ! $this->trellis->cache->data['departs'][ $t['did'] ]['escalate_enable'] || ( $this->trellis->cache->data['departs'][ $t['did'] ]['escalate_wait'] && ( $escalate_time >= ( time() - ( $this->trellis->cache->data['departs'][ $t['did'] ]['escalate_wait'] * 60 ) ) ) ) ) $this->trellis->skin->error('no_perm');

        #=============================
        # Escalate Ticket
        #=============================

        $this->trellis->func->tickets->escalate( $t['id'], array( 'did' => $t['did'], 'staff' => 0, 'data' => $t ) );

        $this->trellis->log( 'user', "Ticket Escalated &#039;". $t['subject'] ."&#039;", 1, $t['id'] );
        $this->trellis->log( 'ticket', "Ticket Escalated &#039;". $t['subject'] ."&#039;", 1, $t['id'] );

        #=============================
        # Do Output
        #=============================

        $this->view_ticket( array( 'alert' => 'ticket_escalated' ) );
    }

    #=======================================
    # @ Do Close
    #=======================================

    function do_close()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['g_ticket_close'] ) $this->trellis->skin->error('no_perm');

        $ticket_id = $this->trellis->func->tickets->get_id_from_mask( $this->trellis->input['id'] );

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'subject', 'priority', 'message', 'closed' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ) ) ), $ticket_id ) ) $this->trellis->skin->error('no_ticket');

        if ( $t['uid'] != $this->trellis->user['id'] || ! $this->trellis->cache->data['departs'][ $t['did'] ]['close_own'] ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        #=============================
        # Close Ticket
        #=============================

        $this->trellis->func->tickets->close( $t['id'], array( 'uid' => $t['uid'], 'allow_reopen' => 1, 'data' => $t ) );

        $this->trellis->log( 'user', "Ticket Closed &#039;". $t['subject'] ."&#039;", 1, $t['id'] );
        $this->trellis->log( 'ticket', "Ticket Closed &#039;". $t['subject'] ."&#039;", 1, $t['id'] );

        #=============================
        # Do Output
        #=============================

        $this->view_ticket( array( 'error' => 'ticket_closed' ) );
    }

    #=======================================
    # @ Do Reopen
    #=======================================

    function do_reopen()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['g_ticket_reopen'] ) $this->trellis->skin->error('no_perm');

        $ticket_id = $this->trellis->func->tickets->get_id_from_mask( $this->trellis->input['id'] );

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'subject', 'priority', 'message', 'closed', 'allow_reopen' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ) ) ), $ticket_id ) ) $this->trellis->skin->error('no_ticket');

        if ( $t['uid'] != $this->trellis->user['id'] || ! $this->trellis->cache->data['departs'][ $t['did'] ]['reopen_own'] || ! $t['allow_reopen'] ) $this->trellis->skin->error('no_perm');

        if ( ! $t['closed'] ) $this->trellis->skin->error('ticket_open'); //* Write error message

        #=============================
        # Reopen Ticket
        #=============================

        $this->trellis->func->tickets->reopen( $t['id'], array( 'uid' => $t['uid'], 'did' => $t['did'], 'staff' => 0, 'data' => $t ) );

        $this->trellis->log( 'other', "Ticket Reopened &#039;". $t['subject'] ."&#039;", 1, $t['id'] );
        $this->trellis->log( 'ticket', "Ticket Reopened &#039;". $t['subject'] ."&#039;", 1, $t['id'] );

        #=============================
        # Do Output
        #=============================

        $this->view_ticket( array( 'alert' => 'ticket_reopened' ) );
    }

    #=======================================
    # @ Edit Reply
    #=======================================

    function edit_reply($error="")
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['g_reply_edit'] )
        {
            $this->trellis->log( 'security', "Blocked Ticket Reply Edit Attempt" );

            $this->trellis->skin->error('no_perm_reply_edit');
        }

        $r = $this->trellis->func->tickets->get_single( array(
                                                        'select'    => array(
                                                                                'r' => 'all',
                                                                                't' => array( 'mask', 'closed' ),
                                                                                ),
                                                        'from'        => array( 'r' => 'replies' ),
                                                        'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'where'        => array( array( 'r' => 'id' ), '=', $this->trellis->input['id'] ),
                                                 )        );

        if ( $r['closed'] ) $this->trellis->skin->error('no_perm_reply_edit');

        if ( ! $r )
        {
            $this->trellis->log( 'error', "Reply Not Found ID: ". $this->trellis->input['id'] );

            $this->trellis->skin->error('no_reply');
        }

        if ( $r['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_perm_reply_edit');

        #=============================
        # Do Output
        #=============================

        if ( $error ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );

        $this->trellis->skin->set_var( 'r', $r );

        # TODO: show ticket in nav

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets">'. $this->trellis->lang['tickets'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets&amp;act=view&amp;id='. $r['mask'] .'">'. $this->trellis->lang['edit_reply'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'tck_reply_edit.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['tickets'] .' :: '. $this->trellis->lang['edit_reply'] ) );
    }

    #=======================================
    # @ Edit Ticket
    #=======================================

    function edit_ticket($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $ticket_id = $this->trellis->func->tickets->get_id_from_mask( $this->trellis->input['id'] );

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'mask', 'uid', 'subject', 'message', 'closed' ), 'from' => 'tickets' ), $ticket_id ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->trellis->user['g_ticket_edit'] || $t['uid'] != $this->trellis->user['id'] || $t['closed'] ) $this->trellis->skin->error('no_perm_ticket_edit');

        #=============================
        # Do Output
        #=============================

        if ( $error ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );

        $this->trellis->skin->set_var( 't', $t );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets">'. $this->trellis->lang['tickets'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets&amp;act=view&amp;id='. $t['mask'] .'">'. $t['subject'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=tickets&amp;act=view&amp;id='. $t['mask'] .'">'. $this->trellis->lang['edit'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'tck_edit.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['tickets'] .' :: '. $t['subject'] .' :: '. $this->trellis->lang['edit'] ) );
    }

    #=======================================
    # @ Do Edit Ticket
    #=======================================

    function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['message'] ) $this->edit_ticket('no_message');

        $ticket_id = $this->trellis->func->tickets->get_id_from_mask( $this->trellis->input['id'] );

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'mask', 'uid', 'subject', 'message', 'closed' ), 'from' => 'tickets' ), $ticket_id ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->trellis->user['g_ticket_edit'] || $t['uid'] != $this->trellis->user['id'] || $t['closed'] ) $this->trellis->skin->error('no_perm_ticket_edit');

        #=============================
        # Edit Ticket
        #=============================

        $this->trellis->func->tickets->edit( array( 'message' => $this->trellis->input['message'] ), $t['id'] );

        $this->trellis->log( 'user', "Edited Ticket ID #". $t['id'], 1, $t['id'] );
        $this->trellis->log( 'ticket', "Edited Ticket ID #". $t['id'], 1, $t['tid'] );

        #=============================
        # Do Output
        #=============================

        $this->view_ticket( array( 'alert' => 'ticket_updated' ) );
    }

    #=======================================
    # @ Do Edit Reply
    #=======================================

    function do_edit_reply()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['message'] )
        {
            $this->edit_reply('no_reply');
        }

        if ( ! $this->trellis->user['g_reply_edit'] )
        {
            $this->trellis->log( 'security', "Blocked Ticket Reply Edit Attempt" );

            $this->trellis->skin->error('no_perm_reply_edit');
        }

        $r = $this->trellis->func->tickets->get_single( array(
                                                        'select'    => array(
                                                                                'r' => 'all',
                                                                                't' => array( 'mask', 'closed' ),
                                                                                ),
                                                        'from'        => array( 'r' => 'replies' ),
                                                        'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'where'        => array( array( 'r' => 'id' ), '=', $this->trellis->input['id'] ),
                                                 )        );

        if ( $r['closed'] ) $this->trellis->skin->error('no_perm_reply_edit');

        if ( ! $r )
        {
            $this->trellis->log( 'error', "Reply Not Found ID: ". $this->trellis->input['id'] );

            $this->trellis->skin->error('no_reply');
        }

        #=============================
        # Edit Reply
        #=============================

        $this->trellis->func->tickets->edit_reply( array( 'message' => $this->trellis->input['message'] ), $r['id'] );

        $this->trellis->log( 'user', "Edited Ticket Reply ID #". $r['id'], 1, $r['id'] );
        $this->trellis->log( 'ticket', "Edited Ticket Reply ID #". $r['id'], 1, $r['tid'] );

        #=============================
        # Do Output
        #=============================

        $this->trellis->input['id'] = $r['mask'];

        $this->view_ticket( array( 'alert' => 'reply_updated', 'scroll' => 'r'. $r['id'] ) );
    }

    #=======================================
    # @ Do Delete Reply
    #=======================================

    function do_delete_reply()
    {
        #=============================
        # Security Checks
        #=============================

        $r = $this->trellis->func->tickets->get_single( array(
                                                        'select'    => array(
                                                                                'r' => array( 'id', 'tid', 'uid', 'date' ),
                                                                                't' => array( 'mask', 'subject', 'last_reply', 'closed' ),
                                                                                ),
                                                        'from'        => array( 'r' => 'replies' ),
                                                        'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'where'        => array( array( 'r' => 'id' ), '=', $this->trellis->input['id'] ),
                                                 )        );

        if ( ! $r ) $this->trellis->skin->error('no_reply');

        if ( $r['uid'] != $this->trellis->user['id'] || ! $this->trellis->user['g_reply_delete'] || $r['closed'] ) $this->trellis->skin->error('no_perm_reply_delete');

        #=============================
        # DELETE Reply
        #=============================

        $this->trellis->func->tickets->delete_reply( $r['id'], array( 'tid' => $r['tid'], 'date' => $r['date'], 'last_reply' => $r['last_reply'], 'staff' => 0 ) );

        $this->trellis->log( 'other', "Ticket Reply Deleted &#039;". $r['subject'] ."&#039;", 2, $r['tid'] );
        $this->trellis->log( 'ticket', "Ticket Reply Deleted &#039;". $r['subject'] ."&#039;", 2, $r['tid'] );

        #=============================
        # Do Output
        #=============================

        $this->trellis->input['id'] = $r['mask'];

        $this->view_ticket( array( 'error' => 'reply_deleted' ) );
    }

    #=======================================
    # @ Do Rate
    # Adding rating to reply.
    #=======================================

    /*function do_rate()
    {
        $this->trellis->input['id'] = intval( $this->trellis->input['id'] );
        $this->trellis->input['rid'] = intval( $this->trellis->input['rid'] );

        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['id'] )
        {
            $this->trellis->skin->error( 'must_be_user', 1 );
        }

        if ( ! $this->trellis->cache->data['settings']['tickets']['rating'] )
        {
            $this->trellis->skin->error('reply_rating_disabled');
        }

        if ( ! $this->trellis->user['g_reply_rate'] || $this->trellis->user['ban_ticket_rate'] )
        {
            $this->trellis->log( 'security', "Blocked Reply Rating" );

            $this->trellis->skin->error('banned_ticket_rate');
        }

        $this->trellis->db->construct( array(
                                                   'select'    => 'all',
                                                   'from'        => 'tickets',
                                                    'where'    => array( array( 'id', '=', $this->trellis->input['id'] ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ),
                                                    'limit'    => array( 0, 1 ),
                                             )     );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() )
        {
            $this->trellis->log( 'error', "Ticket Not Found ID: ". $this->trellis->input['id'] );

            $this->trellis->skin->error('no_ticket');
        }

        $t = $this->trellis->db->fetch_row();

        $this->trellis->db->construct( array(
                                                   'select'    => 'all',
                                                   'from'        => 'replies',
                                                    'where'    => array( array( 'id', '=', $this->trellis->input['rid'] ), array( 'tid', '=', $this->trellis->input['id'], 'and' ) ),
                                                    'limit'    => array( 0, 1 ),
                                             )     );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() )
        {
            $this->trellis->log( 'error', "Reply Not Found ID: ". $this->trellis->input['rid'] );

            $this->trellis->skin->error('no_reply');
        }

        $r = $this->trellis->db->fetch_row();

        if ( ! $r['staff'] )
        {
            $this->trellis->log( 'security', "Reply Rating Blocked Not Staff &#039;". $t['subject'] ."&#039;", 1, $r['id'] );

            $this->trellis->skin->error('no_staff_rate_reply');
        }

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id', 'votes', 'rating', 'rating_total' ),
                                                   'from'        => 'users',
                                                    'where'    => array( 'id', '=', $r['uid'] ),
                                                    'limit'    => array( 0, 1 ),
                                             )     );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() )
        {
            $this->trellis->log( 'error', "User Not Found ID: ". $r['uid'] );

            $this->trellis->skin->error('no_user');
        }

        $s = $this->trellis->db->fetch_row();

        if ( ! $this->trellis->user['id'] )
        {
            $this->trellis->log( 'security', "Reply Rating Blocked From Guest &#039;". $t['subject'] ."&#039;", 1, $r['id'] );

            $this->trellis->skin->error( 'must_be_user', 1 );
        }

        $allowed_ratings = array( 1, 5 );

        if ( ! in_array( $this->trellis->input['amount'], $allowed_ratings ) )
        {
            $this->trellis->log( 'security', "Invalid Reply Rating Amount &#039;". $t['subject'] ."&#039;", 1, $r['id'] );

            $this->trellis->skin->error('invalid_rate_value_reply');
        }

        $this->trellis->db->construct( array(
                                                   'select'    => 'all',
                                                   'from'        => 'reply_rate',
                                                    'where'    => array( array( 'tid', '=', $this->trellis->input['id'] ), array( 'rid', '=', $this->trellis->input['rid'], 'and' ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ),
                                                    'limit'    => array( 0, 1 ),
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            $this->trellis->log( 'security', "Already Rated Reply By User &#039;". $t['subject'] ."&#039;", 1, $r['id'] );

            $this->trellis->skin->error('already_rated_reply');
        }

        #=============================
        # Add Rating
        #=============================

        $db_array = array(
                          'tid'            => $this->trellis->input['id'],
                          'rid'            => $this->trellis->input['rid'],
                          'uid'            => $this->trellis->user['id'],
                          'rating'        => $this->trellis->input['amount'],
                          'date'        => time(),
                          'ipadd'        => $this->trellis->input['ip_address'],
                         );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'reply_rate',
                                                   'set'        => $db_array,
                                             )     );

        $this->trellis->db->execute();

        $this->trellis->log( 'user', "Reply Rating Value ". $this->trellis->input['amount'] ." Added &#039;". $t['subject'] ."&#039;", 1, $r['id'] );

        #=============================
        # Update Ticket Rating
        #=============================

        $new_ticket_rating = round( ( $t['rating_total'] + $this->trellis->input['amount'] ) / ( $t['votes'] + 1 ), 2 );

        $this->trellis->db->next_no_quotes('set');

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'        => array( 'votes' => 'votes+1', 'rating' => $new_ticket_rating, 'rating_total' => 'rating_total+'. $this->trellis->input['amount'] ),
                                                    'where'    => array( 'id', '=', $t['id'] ),
                                                    'limit'    => array( 1 ),
                                             )     );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        #=============================
        # Update Staff User
        #=============================

        $new_rating = round( ( $s['rating_total'] + $this->trellis->input['amount'] ) / ( $s['votes'] + 1 ), 2 );

        $this->trellis->db->next_no_quotes('set');

        $this->trellis->db->construct( array(
                                                   'update'    => 'users',
                                                   'set'        => array( 'votes' => 'votes+1', 'rating' => $new_rating, 'rating_total' => 'rating_total+'. $this->trellis->input['amount'] ),
                                                    'where'    => array( 'id', '=', $s['id'] ),
                                                    'limit'    => array( 1 ),
                                             )     );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        #=============================
        # Do Output
        #=============================

        $this->view_ticket();
    }

    #=======================================
    # @ Show Guest Login
    # Show the guest ticket login form.
    #=======================================

    function show_guest_login()
    {
        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->set_var( 'token_gt_login', $this->trellis->create_token('glogin') );

        $this->trellis->skin->set_var( 'sub_tpl', 'tck_guest_login.tpl' );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ rate_thumbs_already()
    #=======================================

    function rate_thumbs_already($choice)
    {
        if ( $choice == 1 )
        {
            $a5_html = "&nbsp;&nbsp;<span class='response_imgs'><img src='images/". $this->trellis->skin->data['img_dir'] ."/thumbs_down_hover.gif' alt='". $this->trellis->lang['thumbs_down'] ."' style='vertical-align:middle' /></span>";
        }
        elseif ( $choice == 5 )
        {
            $a5_html = "&nbsp;&nbsp;<span class='response_imgs'><img src='images/". $this->trellis->skin->data['img_dir'] ."/thumbs_up_hover.gif' alt='". $this->trellis->lang['thumbs_up'] ."' style='vertical-align:middle' /></span>";
        }

        return $a5_html;
    }

    #=======================================
    # @ rate_thumbs()
    #=======================================

    function rate_thumbs($tid, $rid)
    {
        $a5_html = "&nbsp;&nbsp;<span class='response_imgs'><a href='". $this->trellis->config['hd_url'] ."/index.php?page=tickets&amp;act=rate&amp;amount=5&amp;id={$tid}&amp;rid={$rid}'><img src='images/". $this->trellis->skin->data['img_dir'] ."/thumbs_up.gif' alt='". $this->trellis->lang['thumbs_up'] ."' id='thumbsup_{$rid}' style='vertical-align:middle' onmouseover='amithumbsup({$rid})' onmouseout='unamithumbsup({$rid})' /></a>&nbsp;&nbsp;<a href='". $this->trellis->config['hd_url'] ."/index.php?page=tickets&amp;act=rate&amp;amount=1&amp;id={$tid}&amp;rid={$rid}'><img src='images/". $this->trellis->skin->data['img_dir'] ."/thumbs_down.gif' alt='". $this->trellis->lang['thumbs_down'] ."' id='thumbsdown_{$rid}' style='vertical-align:middle' onmouseover='amithumbsdown({$rid})' onmouseout='unamithumbsdown({$rid})' /></a></span>";

        return $a5_html;
    }

    #=======================================
    # @ Download Attachment
    # Send the attachment to the browser.
    #=======================================

    function download_attachment()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['id'] && ! $this->trellis->user['s_tkey'] )
        {
            $this->show_guest_login();
        }

        $this->trellis->input['id'] = intval( $this->trellis->input['id'] );

        if ( $this->trellis->user['id'] )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'a' => 'all',
                                                                             't' => array( 'subject' ),
                                                                            ),
                                                       'from'        => array( 'a' => 'attachments' ),
                                                       'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'a' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'where'    => array( array( array( 'a' => 'id' ), '=', $this->trellis->input['id'] ), array( array( 't' => 'uid' ), '=', $this->trellis->user['id'], 'and' ) ),
                                                        'limit'    => array( 0, 1 ),
                                                 )     );
        }
        else
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'a' => 'all',
                                                                             't' => array( 'subject' ),
                                                                            ),
                                                       'from'        => array( 'a' => 'attachments' ),
                                                       'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'a' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'where'    => array( array( array( 'a' => 'id' ), '=', $this->trellis->input['id'] ), array( array( 't' => 'email' ), '=', $this->trellis->user['s_email'], 'and' ), array( array( 't' => 'guest' ), '=', 1, 'and' ) ),
                                                        'limit'    => array( 0, 1 ),
                                                 )     );
        }

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() )
        {
            $this->trellis->skin->error('no_attachment');
        }

        $a = $this->trellis->db->fetch_row();

        $file_path = $this->trellis->cache->data['settings']['general']['upload_dir'] .'/'. $a['real_name'];

        if ( ! file_exists( $file_path ) )
        {
            $this->trellis->skin->error('no_attachment');

            $this->trellis->log( 'error', "Attachment File Not Found ID #". $a['id'], 2, $a['id'] );
        }

        #=============================
        # Send Download
        #=============================

        if ( $a['mime'] )
        {
            header("Content-type: {$a['mime']}");
        }
        else
        {
            header("Content-type: application/force-download");
        }

        $show_types = array( 'image/gif', 'image/jpeg', 'image/jpg', 'image/png', 'text/plain', 'text/html' );

        if ( ! in_array( $a['mime'], $show_types ) )
        {
            header("Content-Disposition: attachment; filename={$a['original_name']}");
        }

        #header("Content-length: ".filesize( $file_path ));

        readfile( $file_path );
    }*/

    #=======================================
    # @ Generate URL
    #=======================================

    private function generate_url($params=array())
    {
        $url = $this->trellis->config['hd_url'] .'/index.php?page=tickets';

        if ( ! isset( $params['sort'] ) ) $params['sort'] = $this->trellis->input['sort'];
        if ( ! isset( $params['order'] ) ) $params['order'] = $this->trellis->input['order'];
        if ( ! isset( $params['st'] ) ) $params['st'] = $this->trellis->input['st'];

        if ( $params['sort'] ) $url .= '&amp;sort='. $params['sort'];

        if ( $params['order'] ) $url .= '&amp;order='. $params['order'];

        if ( $params['st'] ) $url .= '&amp;st='. $params['st'];

        return $url;
    }

}

?>