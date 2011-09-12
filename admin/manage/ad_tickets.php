<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_tickets {

    private $output = "";
    private $assigned_override = array();
    private $parsed_sigs;

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->load_functions('tickets');
        $this->trellis->load_lang('tickets');

        $this->trellis->skin->set_active_link( 2 );

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

            case 'doaddassign':
                $this->ajax_add_assign();
            break;
            case 'doaddflag':
                $this->ajax_add_flag();
            break;
            case 'dodelassign':
                $this->ajax_delete_assign();
            break;
            case 'dodelflag':
                $this->ajax_delete_flag();
            break;
            case 'donotes':
                $this->ajax_save_notes();
            break;
            case 'dodefaults':
                $this->ajax_save_defaults();
            break;

            case 'getstatus':
                $this->ajax_get_status();
            break;
            case 'getrt':
                $this->ajax_get_reply_template();
            break;

            case 'doaccept':
                $this->do_accept();
            break;
            case 'doescalate':
                $this->do_escalate();
            break;
            case 'dormvescalate':
                $this->do_rmvescalate();
            break;
            case 'dohold':
                $this->do_hold();
            break;
            case 'dormvhold':
                $this->do_rmvhold();
            break;
            case 'domove':
                $this->do_move();
            break;
            case 'doclose':
                $this->do_close();
            break;
            case 'doreopen':
                $this->do_reopen();
            break;

            case 'dopriority':
                $this->do_priority();
            break;
            case 'dostatus':
                $this->do_status();
            break;

            case 'dountrack':
                $this->do_untrack();
            break;
            case 'dotrackall':
                $this->do_track_all();
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

            case 'doaddreply':
                $this->do_add_reply();
            break;
            case 'doeditreply':
                $this->do_edit_reply();
            break;
            case 'dodelreply':
                $this->do_delete_reply();
            break;

            case 'getreply':
                $this->ajax_get_reply();
            break;
            case 'doupload':
                $this->do_upload();
            break;
            case 'dodelupload':
                $this->do_delete_upload();
            break;
            case 'attachment':
                $this->do_attachment();
            break;

            default:
                $this->list_tickets();
            break;
        }
    }

    #=======================================
    # @ List Tickets
    #=======================================

    private function list_tickets()
    {
        $this->output = "";

        #=============================
        # Table Columns
        #=============================

        $columns = unserialize( $this->trellis->user['columns_tm'] );

        if ( empty( $columns ) )
        {
            $columns = array( 'id' => '3%', 'mask' => '6%', 'subject' => '30%', 'priority' => '13%', 'department' => '18%', 'reply' => '17%', 'status' => '13%' );
        }

        $lang_columns = array(
    		'id'            => '{lang.id}',
    		'mask'	        => '{lang.mask}',
    		'subject'	    => '{lang.subject}',
    		'priority'	    => '{lang.priority}',
    		'department'	=> '{lang.department}',
        	'date'	        => '{lang.submitted}',
    		'reply'	        => '{lang.last_reply}',
    		'replystaff'	=> '{lang.last_staff_reply}',
    		'lastuname'	    => '{lang.last_replier}',
    		'submitter'	    => '{lang.submitter}',
    		'email'	        => '{lang.ticket_email}',
    		'uemail'	    => '{lang.user_email}',
    		'replies'	    => '{lang.replies}',
    		'status'        => '{lang.status}',
    		);

        $dark_columns = array( 'subject', 'date', 'last_reply' );
        $normal_columns = array( 'dname', 'date', 'last_reply' );

        $sql_select = array();
        $sql_columns = array();
        $sql_join = array();

        #=============================
        # Default Sort
        #=============================

        if ( ! $this->trellis->input['sort'] )
        {
            if ( isset( $columns[ $this->trellis->user['sort_tm'] ] ) ) $this->trellis->input['sort'] = $this->trellis->user['sort_tm'];
        }

        $column_sort = array( 'reply', 'date', 'mask', 'replystaff', 'subject', 'priority', 'department', 'mname', 'lastuname', 'email', 'status', 'replies' );

        for ( $i = 0; ! $this->trellis->input['sort'] && $i < count( $column_sort ); $i++ )
        {
            if ( $columns[ $column_sort[ $i ] ] ) $this->trellis->input['sort'] = $column_sort[ $i ];
        }

        if ( ! $this->trellis->input['sort'] ) $this->trellis->input['sort'] = 'id';

        if ( ! $this->trellis->input['order'] )
        {
            ( $this->trellis->user['order_tm'] ) ? $this->trellis->input['order'] = 'desc' : $this->trellis->input['order'] = 'asc';
        }

        #=============================
        # Prepare Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.tickets_list}' ) ."
                        <tr>";

        #=============================
        # Sort
        #=============================

        $user_table_join = 0;

        foreach( $columns as $name => $width )
        {
            if ( $name == $this->trellis->input['sort'] )
            {
                if ( $this->trellis->input['order'] == 'desc' )
                {
                    $link_order = 'asc';
                    $img_order = '&nbsp;<img src="<! IMG_DIR !>/arrow_down.gif" alt="{lang.down}" />';
                    $sql_order = 'desc';
                }
                else
                {
                    $link_order = 'desc';
                    $img_order = '&nbsp;<img src="<! IMG_DIR !>/arrow_up.gif" alt="{lang.up}" />';
                    $sql_order = 'asc';
                }

                if ( $name == 'department' )
                {
                    $sql_sort_table = 'd';
                    $sql_sort_field = 'name';
                }
                elseif ( $name == 'priority' )
                {
                    $sql_sort_table = 'p';
                    $sql_sort_field = 'position';
                }
                elseif ( $name == 'reply' )
                {
                    $sql_sort_table = 't';
                    $sql_sort_field = 'last_reply';
                }
                elseif ( $name == 'replystaff' )
                {
                    $sql_sort_table = 't';
                    $sql_sort_field = 'last_reply_staff';
                }
                elseif ( $name == 'lastuname' )
                {
                    $sql_sort_table = 'ulr';
                    $sql_sort_field = 'name';
                }
                elseif ( $name == 'status' )
                {
                    $sql_sort_table = 's';
                    $sql_sort_field = 'name_staff';
                }
                elseif ( $name == 'submitter' )
                {
                    $sql_sort_table = 'u';
                    $sql_sort_field = 'name';
                }
                elseif ( strpos( $name, 'cfd' ) === 0 || strpos( $name, 'cfp' ) === 0 )
                {
                    $sql_sort_table = $name;
                    $sql_sort_field = 'data';
                }
                else
                {
                    $sql_sort_table = 't';
                    $sql_sort_field = $name;
                }
            }
            else
            {
                $link_order = 'asc';
                $img_order = '';
            }

            if ( strpos( $name, 'cfd' ) === 0 )
            {
                $lang_columns[ $name ] = $this->trellis->cache->data['dfields'][ substr( $name, 3) ]['name'];
            }
            elseif ( strpos( $name, 'cfp' ) === 0 )
            {
                $lang_columns[ $name ] = $this->trellis->cache->data['pfields'][ substr( $name, 3) ]['name'];
            }

            $this->output .= "<th class='bluecellthin-th' width='{$width}%' align='left'><a href='". $this->generate_url( array( 'sort' => $name, 'order' => $link_order ) ) ."'>{$lang_columns[ $name ]}{$img_order}</a></th>";

            if ( $name == 'department' )
            {
                $sql_columns[] = 'did';
                $sql_select['d'] = array( array( 'name' => 'dname' ) );

                $sql_join[] = array( 'from' => array( 'd' => 'departments' ), 'where' => array( 't' => 'did', '=', 'd' => 'id' ) );
            }
            elseif ( $name == 'priority' )
            {
                $sql_columns[] = 'priority';
                $sql_select['p'] = array( array( 'name' => 'pname' ), 'icon_regular', 'icon_assigned' );

                $sql_join[] = array( 'from' => array( 'p' => 'priorities' ), 'where' => array( 't' => 'priority', '=', 'p' => 'id' ) );
            }
            elseif ( $name == 'status' )
            {
                $sql_columns[] = 'status';
                $sql_select['s'] = array( 'name_staff', 'abbr_staff' );

                $sql_join[] = array( 'from' => array( 's' => 'statuses' ), 'where' => array( 't' => 'status', '=', 's' => 'id' ) );
            }
            elseif ( $name == 'submitter' )
            {
                $sql_select['u'][] = array( 'name' => 'uname' );
                $sql_select['g'][] = 'gname';
                $sql_columns[] = 'email';

                $sql_join[] = array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 't' => 'id', '=', 'g' => 'id' ) );

                if ( ! $user_table_join )
                {
                    $sql_join[] = array( 'from' => array( 'u' => 'users' ), 'where' => array( 't' => 'uid', '=', 'u' => 'id' ) );

                    $sql_columns[] = 'uid';
                }

                $user_table_join = 1;
            }
            elseif ( $name == 'lastuname' )
            {
                $sql_select['ulr'] = array( array( 'name' => 'last_uname' ) );
                $sql_select['g'][] = 'gname';
                $sql_columns[] = 'email';

                $sql_join[] = array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 't' => 'id', '=', 'g' => 'id' ) );
                $sql_join[] = array( 'from' => array( 'ulr' => 'users' ), 'where' => array( 't' => 'last_uid', '=', 'ulr' => 'id' ) );

                $sql_columns[] = 'last_uid';
            }
            elseif ( $name == 'uemail' )
            {
                $sql_select['u'][] = array( 'email' => 'uemail' );

                if ( ! $user_table_join )
                {
                    $sql_join[] = array( 'from' => array( 'u' => 'users' ), 'where' => array( 't' => 'uid', '=', 'u' => 'id' ) );

                    $sql_columns[] = 'uid';
                }

                $user_table_join = 1;
            }
            elseif ( strpos( $name, 'cfd' ) === 0 )
            {
                $sql_select[ $name ] = array( array( 'data' => $name ) );

                $sql_join[] = array( 'from' => array( $name => 'depart_fields_data' ), 'where' => array( array( 't' => 'id', '=', $name => 'tid' ), array( $name => 'fid', '=', substr( $name, 3 ) ) ) );
            }
            elseif ( strpos( $name, 'cfp' ) === 0 )
            {
                $sql_select[ $name ] = array( array( 'data' => $name ) );

                $sql_join[] = array( 'from' => array( $name => 'profile_fields_data' ), 'where' => array( array( 't' => 'uid', '=', $name => 'uid' ), array( $name => 'fid', '=', substr( $name, 3 ) ) ) );
            }
            else
            {
                if ( $name == 'reply' )
                {
                    $name = 'last_reply';
                }
                elseif ( $name == 'replystaff' )
                {
                    $name = 'last_reply_staff';
                }

                $sql_columns[] = $name;
            }
        }

        $this->output .= "<th class='bluecellthin-th' width='1%' align='center'><input name='checkall' id='checkall' type='checkbox' value='1' /></th>
                        </tr>";

        #=============================
        # Filter
        #=============================

        if ( ! $this->trellis->input['cf'] )
        {
            if ( ! is_array( $this->trellis->input['fstatus'] ) )
            {
                $this->trellis->input['fstatus'] = unserialize( $this->trellis->user['dfilters_status'] );
            }

            if ( ! is_array( $this->trellis->input['fdepart'] ) )
            {
                $this->trellis->input['fdepart'] = unserialize( $this->trellis->user['dfilters_depart'] );
            }

            if ( ! is_array( $this->trellis->input['fpriority'] ) )
            {
                $this->trellis->input['fpriority'] = unserialize( $this->trellis->user['dfilters_priority'] );
            }

            if ( ! is_array( $this->trellis->input['fflag'] ) )
            {
                $this->trellis->input['fflag'] = unserialize( $this->trellis->user['dfilters_flag'] );
            }

            if ( ! $this->trellis->input['assigned'] && $this->trellis->user['dfilters_assigned'] ) $this->trellis->input['assigned'] = $this->trellis->user['id'];
        }

        if ( $this->trellis->input['go_all'] )
        {
            unset( $this->trellis->input['fstatus'] );
            unset( $this->trellis->input['fdepart'] );
            unset( $this->trellis->input['fpriority'] );
            unset( $this->trellis->input['fflag'] );
        }

        $filters = array();
        $sql_where = array();

        $sql_select['a'] = array( array( 'uid' => 'auid' ) ); // Get Assigned

        if ( $this->trellis->input['noguest'] )
        {
            $filters[] = array( array( 't' => 'uid' ), '!=', 0 );
        }

        if ( $this->trellis->input['assigned'] )
        {
            $sql_join[] = array( 'from' => array( 'a' => 'assign_map' ), 'where' => array( array( 't' => 'id', '=', 'a' => 'tid' ), array( $this->trellis->input['assigned'], '=', 'a' => 'uid', 'and' ) ) );

            $filters[] = array( array( 'a' => 'uid' ), '=', $this->trellis->input['assigned'] );
        }
        elseif ( $this->trellis->input['unassigned'] )
        {
            $sql_join[] = array( 'from' => array( 'a' => 'assign_map' ), 'where' => array( 't' => 'id', '=', 'a' => 'tid' ) );

            $filters[] = array( array( 'a' => 'uid' ), 'is', 'null' );
        }
        else
        {
            $sql_join[] = array( 'from' => array( 'a' => 'assign_map' ), 'where' => array( array( 't' => 'id', '=', 'a' => 'tid' ), array( $this->trellis->user['id'], '=', 'a' => 'uid', 'and' ) ) );
        }

        if ( $this->trellis->input['escalated'] )
        {
            $filters[] = array( array( 't' => 'escalated' ), '=', 1 );
        }

        if ( $this->trellis->input['field'] )
        {
            $strict_fields = array( 'id', 'mask', 'uid' );

            $user_fields = array ( 'uname' => 'name', 'uemail' => 'email' );

            if ( in_array( $this->trellis->input['field'], $strict_fields ) && ! $this->trellis->input['loose'] )
            {
                $filters[] = array( array( 't' => $this->trellis->input['field'] ), '=', $this->trellis->input['search'] );
            }
            elseif ( $user_fields[ $this->trellis->input['field'] ] )
            {
                $filters[] = array( array( 'u' => $user_fields[ $this->trellis->input['field'] ] ), 'like', '%'. addcslashes( $this->trellis->input['search'], '%_' ) .'%' );

                if ( ! $user_table_join )
                {
                    $sql_join[] = array( 'from' => array( 'u' => 'users' ), 'where' => array( 't' => 'uid', '=', 'u' => 'id' ) );

                    $sql_columns[] = 'uid';
                }
            }
            else
            {
                $filters[] = array( array( 't' => $this->trellis->input['field'] ), 'like', '%'. addcslashes( $this->trellis->input['search'], '%_' ) .'%' );
            }
        }

        if ( is_array( $this->trellis->input['fstatus'] ) )
        {
            $filters[] = array( array( 't' => 'status' ), 'in', $this->trellis->input['fstatus'] );
        }

        if ( is_array( $this->trellis->input['fdepart'] ) )
        {
            $filters[] = array( array( 't' => 'did' ), 'in', $this->trellis->input['fdepart'] );
        }

        if ( is_array( $this->trellis->input['fpriority'] ) )
        {
            $filters[] = array( array( 't' => 'priority' ), 'in', $this->trellis->input['fpriority'] );
        }

        if ( is_array( $this->trellis->input['fflag'] ) )
        {
            foreach ( $this->trellis->input['fflag'] as $fid => $ff )
            {
                $sql_join[] = array( 'from' => array( 'f'. $fid => 'flags_map' ), 'where' => array( array( 't' => 'id', '=', 'f'. $fid => 'tid' ), array( $ff, '=', 'f'. $fid => 'fid', 'and' ) ) );

                $filters[] = array( array( 'f'. $fid => 'fid' ), '=', $ff );
            }
        }

        #=============================
        # Permissions
        #=============================

        if ( $this->trellis->user['id'] != 1 )
        {
            $perms = array();

            if ( is_array( $this->trellis->user['g_acp_depart_perm'] ) )
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

        $sql_columns[] = 'escalated';

        if ( ! in_array( 'id', $sql_columns ) ) $sql_columns[] = 'id';

        // Tracking
        if ( $this->trellis->cache->data['settings']['ticket']['track'] )
        {
            $sql_columns[] = 'last_reply_all';
            $sql_select['tt'] = array( array( 'date' => 'track_date' ) );
            $sql_join[] = array( 'from' => array( 'tt' => 'tickets_track' ), 'where' => array( array( 'tt' => 'uid', '=', $this->trellis->user['id'] ), array( 'tt' => 'tid', '=', 't' => 'id', 'and' ) ) );
        }

        $sql_select['t'] = $sql_columns;

        $ticket_rows = '';

        $t_total = $this->trellis->func->tickets->get( array(
                                                       'select'    => array( 't' => array( 'id' ) ),
                                                       'from'    => array( 't' => 'tickets' ),
                                                       'join'    => $sql_join,
                                                       'where'    => $sql_where,
                                                )       );

        if ( ! $t_total && ! $this->trellis->input['loose'] && $this->trellis->input['field'] && in_array( $this->trellis->input['field'], $strict_fields ) )
        {
            $this->trellis->input['loose'] = 1;

            $this->list_tickets();
        }

        $tickets = $this->trellis->func->tickets->get( array(
                                                       'select'    => $sql_select,
                                                       'from'    => array( 't' => 'tickets' ),
                                                       'join'    => $sql_join,
                                                       'where'    => $sql_where,
                                                       'order'    => array( $sql_sort_field => array( $sql_sort_table => $sql_order ) ),
                                                       'limit'    => array( $this->trellis->input['st'], 15 ),
                                                )       );

        if ( $tickets && count( $tickets ) == 1 && ( $this->trellis->input['field'] == 'id' || $this->trellis->input['field'] == 'mask' ) && ! $this->trellis->input['loose'] )
        {
            $this->trellis->input['id'] = $tickets[ key($tickets) ]['id'];

            $this->view_ticket();
        }

        if ( ! $tickets )
        {
            $ticket_rows .= "<tr><td class='bluecell-light' colspan='". ( count( $columns ) + 1 ) ."'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=add'>{lang.no_tickets}</a></strong></td></tr>";
        }
        else
        {
            foreach( $tickets as $t )
            {
                if ( $t['date'] ) $t['date'] = $this->trellis->td_timestamp( array( 'time' => $t['date'], 'format' => 'short' ) );
                if ( $t['last_reply'] ) $t['last_reply'] = $this->trellis->td_timestamp( array( 'time' => $t['last_reply'], 'format' => 'short' ) );
                ( $t['last_reply_staff'] ) ? $t['last_reply_staff'] = $this->trellis->td_timestamp( array( 'time' => $t['last_reply_staff'], 'format' => 'short' ) ) : $t['last_reply_staff'] = '';

                if ( $t['uname'] ) $t['uname'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$t['uid']}'>{$t['uname']}</a>";
                if ( $t['gname'] ) $t['uname'] = "<a href='". $this->generate_url( array( 'search' => $t['email'], 'field' => 'email', 'fstatus' => '', 'fdepart' => '', 'fpriority' => '', 'fflag' => '', 'cf' => 0 ) ) ."'>{$t['gname']} ({lang.guest})</a>";
                if ( $t['last_uname'] ) $t['last_uname'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$t['last_uid']}'>{$t['last_uname']}</a>";
                if ( isset( $t['last_uid'] ) && ! $t['last_uid'] ) $t['last_uname'] = "<a href='". $this->generate_url( array( 'search' => $t['email'], 'field' => 'email', 'fstatus' => '', 'fdepart' => '', 'fpriority' => '', 'fflag' => '', 'cf' => 0 ) ) ."'>{$t['gname']} ({lang.guest})</a>";

                if ( $t['email'] ) $t['email'] = "<a href='". $this->generate_url( array( 'search' => $t['email'], 'field' => 'email', 'fstatus' => '', 'fdepart' => '', 'fpriority' => '', 'fflag' => '', 'cf' => 0 ) ) ."'>{$t['email']}</a>";
                if ( $t['uemail'] ) $t['uemail'] = "<a href='". $this->generate_url( array( 'search' => $t['uemail'], 'field' => 'uemail', 'fstatus' => '', 'fdepart' => '', 'fpriority' => '', 'fflag' => '', 'cf' => 0 ) ) ."'>{$t['uemail']}</a>";

                if ( $t['dname'] ) $t['dname'] = "<a href='". $this->generate_url( array( 'fdepart' => array( $t['did'] ), 'fstatus' => '', 'fpriority' => '', 'fflag' => '', 'cf' => 0 ) ) ."'>{$t['dname']}</a>";

                $ticket_rows .= "<tr>";

                foreach( $columns as $name => $width )
                {
                    if ( $name == 'department' )
                    {
                        $name = 'dname';
                    }
                    elseif ( $name == 'priority' )
                    {
                        $name = 'pname';
                    }
                    elseif ( $name == 'reply' )
                    {
                        $name = 'last_reply';
                    }
                    elseif ( $name == 'replystaff' )
                    {
                        $name = 'last_reply_staff';
                    }
                    elseif ( $name == 'submitter' )
                    {
                        $name = 'uname';
                    }
                    elseif ( $name == 'lastuname' )
                    {
                        $name = 'last_uname';
                    }
                    elseif ( $name == 'status' )
                    {
                        ( $t['abbr_staff'] ) ? $name = 'abbr_staff' : $name = 'name_staff';
                    }

                    ( in_array( $name, $dark_columns ) ) ? $dark = 1 : $dark = 0;
                    ( in_array( $name, $normal_columns ) ) ? $normal = 1 : $normal = 0;

                    $ticket_rows .= "<td class='bluecellthin-";

                    if ( $dark )
                    {
                        $ticket_rows .= "dark";
                    }
                    else
                    {
                        $ticket_rows .= "light";
                    }

                    $ticket_rows .= "'";

                    if ( $normal ) $ticket_rows .= " style='font-weight: normal'";

                    $ticket_rows .= ">";

                    if ( $name == 'id' || $name == 'mask' ) $ticket_rows .= "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=view&amp;id={$t['id']}'>";

                    if ( $name == 'id' ) $ticket_rows .= "<strong>";

                    if ( $name == 'subject' )
                    {
                        if ( $this->trellis->cache->data['settings']['ticket']['track'] && ( $t['track_date'] < $t['last_reply_all'] ) ) $ticket_rows .= "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=view&amp;id={$t['id']}#unread'><img src='<! IMG_DIR !>/icons/balloon_small.png' alt='*' title='{lang.unread}' style='vertical-align:middle;margin-bottom:2px' /></a>&nbsp;"; // NULL < 0
                        $ticket_rows .= "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=view&amp;id={$t['id']}'>";
                        if ( $t['escalated'] ) $ticket_rows .= "<img src='<! IMG_DIR !>/icons/escalate.png' alt='E' style='vertical-align:middle;margin-bottom:2px' />&nbsp;";
                    }

                    if ( $name == 'pname' )
                    {
                        if ( $t['auid'] == $this->trellis->user['id'] )
                        {
                            $ticket_rows .= "<img src='<! TD_URL !>/images/priorities/{$t['icon_assigned']}' alt='{$t['pname']}' class='prioritybox' />&nbsp;&nbsp;";
                        }
                        else
                        {
                            $ticket_rows .= "<img src='<! TD_URL !>/images/priorities/{$t['icon_regular']}' alt='{$t['pname']}' class='prioritybox' />&nbsp;&nbsp;";
                        }

                        $ticket_rows .= "<a href='". $this->generate_url( array( 'fpriority' => array( $t['priority'] ), 'fstatus' => '', 'fdepart' => '', 'fflag' => '', 'cf' => 0 ) ) ."'>";
                    }

                    if ( $name == 'abbr_staff' || $name == 'name_staff' ) $ticket_rows .= "<a href='". $this->generate_url( array( 'fstatus' => array( $t['status'] ), 'fdepart' => '', 'fpriority' => '', 'fflag' => '', 'cf' => 0 ) ) ."'>";

                    if ( strpos( $name, 'cfd' ) === 0 || strpos( $name, 'cfp' ) === 0 )
                    {
                        if ( strpos( $name, 'cfd' ) === 0 )
                        {
                            $f = $this->trellis->cache->data['dfields'][ substr( $name, 3 ) ];
                        }
                        elseif ( strpos( $name, 'cfp' ) === 0 )
                        {
                            $f = $this->trellis->cache->data['pfields'][ substr( $name, 3 ) ];
                        }

                        if ( $f['type'] == 'dropdown' || $f['type'] == 'radio' )
                        {
                            $f['extra'] = unserialize( $f['extra'] );

                            $t[ $name ] = $f['extra'][ $t[ $name ] ];
                        }
                    }

                    $ticket_rows .= $t[ $name ];

                    if ( $name == 'id' ) $ticket_rows .= "</strong>";

                    if ( $name == 'id' || $name == 'subject' || $name == 'pname' || $name == 'abbr_staff' || $name == 'name_staff' ) $ticket_rows .= "</a>";

                    $ticket_rows .= "</td>";
                }

                $ticket_rows .= "<td class='bluecellthin-light'><input name='mat[]' id='mat_{$t['id']}' type='checkbox' value='{$t['id']}' class='matcb' /></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $t_total ),
                                                        'per_page'    => 15,
                                                        'url'        => $this->generate_url( array( 'st' => 0 ) ),
                                                        ) );

        $this->output .= $ticket_rows ."
                        <tr>
                            <td class='bluecellthin-th-pages' colspan='3' align='left'>". $page_links ."</td>
                            <td class='bluecellthin-th' colspan='". ( count( $columns ) - 2 ) ."' align='right'>Mass-Action</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=add' ),
                            array( 'mail_pencil', '{lang.menu_etemplates}', '<! TD_URL !>/admin.php?section=look&amp;page=emails' ),
                            array( 'balloon', '{lang.menu_mark_all_read}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=dotrackall' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $status_items = array();

        foreach( $this->trellis->cache->data['statuses'] as $s )
        {
            $status_items[ $s['id'] ] = "<input name='fstatus[]' id='fs_". $s['id'] ."' type='checkbox' class='fstatus' value='". $s['id'] ."'";

            if ( is_array( $this->trellis->input['fstatus'] ) )
            {
                if ( in_array( $s['id'], $this->trellis->input['fstatus'] ) ) $status_items[ $s['id'] ] .= " checked='checked'";
            }

            $status_items[ $s['id'] ] .= " />&nbsp;&nbsp;<label for='fs_". $s['id'] ."'>". $s['name_staff'] ."</label>";
        }

        $status_items[] = "<input name='save_status' id='save_status' type='submit' value='{lang.button_save_default}' class='buttontiny' /> <span id='save_status_status' class='ajax_update_button'>{lang.saved}</span>";

        $depart_items = array();

        foreach( $this->trellis->cache->data['departs'] as $d )
        {
            if ( is_array( $perms ) && ( ! in_array( $d['id'], $perms ) ) ) continue;

            $depart_items[ $d['id'] ] = "<input name='fdepart[]' id='fd_". $d['id'] ."' type='checkbox' class='fdepart' value='". $d['id'] ."'";

            if ( is_array( $this->trellis->input['fdepart'] ) )
            {
                if ( in_array( $d['id'], $this->trellis->input['fdepart'] ) ) $depart_items[ $d['id'] ] .= " checked='checked'";
            }

            $depart_items[ $d['id'] ] .= " />&nbsp;&nbsp;<label for='fd_". $d['id'] ."'>". $d['name'] ."</label>";
        }

        $depart_items[] = "<input name='save_depart' id='save_depart' type='submit' value='{lang.button_save_default}' class='buttontiny' /> <span id='save_depart_status' class='ajax_update_button'>{lang.saved}</span>";

        $priority_items = array();

        foreach( $this->trellis->cache->data['priorities'] as $p )
        {
            $priority_items[ $p['id'] ] = "<input name='fpriority[]' id='fp_". $p['id'] ."' type='checkbox' class='fpriority' value='". $p['id'] ."'";

            if ( is_array( $this->trellis->input['fpriority'] ) )
            {
                if ( in_array( $p['id'], $this->trellis->input['fpriority'] ) ) $priority_items[ $p['id'] ] .= " checked='checked'";
            }

            $priority_items[ $p['id'] ] .= " />&nbsp;&nbsp;<label for='fp_". $p['id'] ."'><img src='<! TD_URL !>/images/priorities/{$p['icon_regular']}' alt='{$p['name']}' class='prioritybox' style='margin-right:8px' />". $p['name'] ."</label>";
        }

        $priority_items[] = "<input name='save_priority' id='save_priority' type='submit' value='{lang.button_save_default}' class='buttontiny' /> <span id='save_priority_status' class='ajax_update_button'>{lang.saved}</span>";

        $flag_items = array();

        if ( ! empty( $this->trellis->cache->data['flags'] ) )
        {
            foreach( $this->trellis->cache->data['flags'] as $f )
            {
                $flag_items[ $f['id'] ] = "<input name='fflag[]' id='ff_". $f['id'] ."' type='checkbox' class='fflag' value='". $f['id'] ."'";

                if ( is_array( $this->trellis->input['fflag'] ) )
                {
                    if ( in_array( $f['id'], $this->trellis->input['fflag'] ) ) $flag_items[ $f['id'] ] .= " checked='checked'";
                }

                $flag_items[ $f['id'] ] .= " />&nbsp;&nbsp;<label for='ff_". $f['id'] ."'><img src='<! TD_URL !>/images/flags/{$f['icon']}' alt='{$f['name']}' class='flagicon' />". $f['name'] ."</label>";
            }

            $flag_items[] = "<input name='save_flag' id='save_flag' type='submit' value='{lang.button_save_default}' class='buttontiny' /> <span id='save_flag_status' class='ajax_update_button'>{lang.saved}</span>";
        }

        $this->trellis->skin->preserve_input = 1;

        $other_items = array();

        $other_items[0] = "<form action='". $this->generate_url( array( 'search' => '', 'field' => '' ) ) ."' method='post'><input name='search' id='search' type='text' value='". $this->trellis->input['search'] ."' style='width:95%;margin-bottom:5px' /><br />";

        $search_fields = array( 'id' => '{lang.id}', 'mask' => '{lang.mask}', 'subject' => '{lang.subject}', 'uid' => '{lang.user_id}', 'uname' => '{lang.username}', 'email' => '{lang.ticket_email}', 'uemail' => '{lang.user_email}' );

        $other_items[0] .= $this->trellis->skin->drop_down( 'field', $search_fields );

        $other_items[0] .= "<br /><input name='go' id='go' type='submit' value='{lang.search}' class='buttontiny' style='margin-top:5px' />&nbsp;&nbsp;<input name='go_all' id='go_all' type='submit' value='{lang.all}' class='buttontiny' style='margin-top:5px' /></form>";

        $other_items[1] = "<input name='noguest' id='noguest' type='checkbox' value='1'";

        if ( $this->trellis->input['noguest'] ) $other_items[1] .= " checked='checked'";

        $other_items[1] .= " />&nbsp;&nbsp;<label for='noguest'>{lang.noguest_tickets}</label>";

        $other_items[2] = "<input name='assigned' id='assigned' type='checkbox' value='". $this->trellis->user['id'] ."'";

        if ( $this->trellis->input['assigned'] == $this->trellis->user['id'] ) $other_items[2] .= " checked='checked'";

        $other_items[2] .= " />&nbsp;&nbsp;<label for='assigned'>{lang.my_assigned_tickets}</label>";

        $other_items[3] = "<input name='unassigned' id='unassigned' type='checkbox' value='1'";

        if ( $this->trellis->input['unassigned'] ) $other_items[3] .= " checked='checked'";

        $other_items[3] .= " />&nbsp;&nbsp;<label for='unassigned'>{lang.unassigned_tickets}</label>";

        $other_items[4] = "<input name='escalated' id='escalated' type='checkbox' value='1'";

        if ( $this->trellis->input['escalated'] ) $other_items[4] .= " checked='checked'";

        $other_items[4] .= " />&nbsp;&nbsp;<label for='escalated'>{lang.escalated_tickets}</label>";

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_tickets_options}', $menu_items );
        $this->trellis->skin->add_sidebar_list( '{lang.filter_by_status}', $status_items, 'filter_status' );
        $this->trellis->skin->add_sidebar_list( '{lang.filter_by_department}', $depart_items, 'filter_depart' );
        $this->trellis->skin->add_sidebar_list( '{lang.filter_by_priority}', $priority_items, 'filter_priority' );

        if ( ! empty( $flag_items ) ) $this->trellis->skin->add_sidebar_list( '{lang.filter_by_flag}', $flag_items, 'filter_flag' );

        $this->trellis->skin->add_sidebar_list( '{lang.other_filters}', $other_items, 'filter_other' );

        $this->output .= $this->trellis->skin->toggle_js();

        $this->output .= "<script type='text/javascript'>
                        //<![CDATA[
                        $('.fstatus').bind('click', function() {
                            goToUrl('". str_replace( '&amp;', '&', $this->generate_url( array( 'fstatus' => '', 'cf' => 1, 'go_all' => 0 ) ) ) ."&'+ $('.fstatus').serialize() );
                        });
                        $('.fdepart').bind('click', function() {
                            goToUrl('". str_replace( '&amp;', '&', $this->generate_url( array( 'fdepart' => '', 'cf' => 1, 'go_all' => 0 ) ) ) ."&'+ $('.fdepart').serialize() );
                        });
                        $('.fpriority').bind('click', function() {
                            goToUrl('". str_replace( '&amp;', '&', $this->generate_url( array( 'fpriority' => '', 'cf' => 1, 'go_all' => 0 ) ) ) ."&'+ $('.fpriority').serialize() );
                        });
                        $('.fflag').bind('click', function() {
                            goToUrl('". str_replace( '&amp;', '&', $this->generate_url( array( 'fflag' => '', 'cf' => 1, 'go_all' => 0 ) ) ) ."&'+ $('.fflag').serialize() );
                        });
                        $('#noguest').bind('click', function() {
                            goToUrl('". str_replace( '&amp;', '&', $this->generate_url( array( 'noguest' => '', 'cf' => 1 ) ) ) ."&'+ $('#noguest').serialize() );
                        });
                        $('#assigned').bind('click', function() {
                            goToUrl('". str_replace( '&amp;', '&', $this->generate_url( array( 'assigned' => '', 'unassigned' => '', 'cf' => 1 ) ) ) ."&'+ $('#assigned').serialize() );
                        });
                        $('#unassigned').bind('click', function() {
                            goToUrl('". str_replace( '&amp;', '&', $this->generate_url( array( 'assigned' => '', 'unassigned' => '', 'cf' => 1 ) ) ) ."&'+ $('#unassigned').serialize() );
                        });
                        $('#escalated').bind('click', function() {
                            goToUrl('". str_replace( '&amp;', '&', $this->generate_url( array( 'escalated' => '', 'cf' => 1 ) ) ) ."&'+ $('#escalated').serialize() );
                        });
                        $('#checkall').bind('click', function() {
                            $('.matcb').attr('checked', this.checked);
                        });
                        $('#save_status').bind('click', function () {
                            $.post('admin.php?section=manage&page=tickets&act=dodefaults&type=status',
                                { defaults: $('.fstatus').serialize() },
                                function(data) {
                                    if (data == 1) $('#save_status_status').stop(true).fadeIn().animate({opacity: 1.0}, {duration: 2000}).fadeOut('slow');
                                });
                        });
                        $('#save_depart').bind('click', function () {
                            $.post('admin.php?section=manage&page=tickets&act=dodefaults&type=depart',
                                { defaults: $('.fdepart').serialize() },
                                function(data) {
                                    if (data == 1) $('#save_depart_status').stop(true).fadeIn().animate({opacity: 1.0}, {duration: 2000}).fadeOut('slow');
                                });
                        });
                        $('#save_priority').bind('click', function () {
                            $.post('admin.php?section=manage&page=tickets&act=dodefaults&type=priority',
                                { defaults: $('.fpriority').serialize() },
                                function(data) {
                                    if (data == 1) $('#save_priority_status').stop(true).fadeIn().animate({opacity: 1.0}, {duration: 2000}).fadeOut('slow');
                                });
                        });
                        $('#save_flag').bind('click', function () {
                            $.post('admin.php?section=manage&page=tickets&act=dodefaults&type=flag',
                                { defaults: $('.fflag').serialize() },
                                function(data) {
                                    if (data == 1) $('#save_flag_status').stop(true).fadeIn().animate({opacity: 1.0}, {duration: 2000}).fadeOut('slow');
                                });
                        });
                        //]]>
                        </script>";

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ View Ticket
    #=======================================

    private function view_ticket($params=array())
    {
        $this->output = "";

        #=============================
        # Grab Ticket
        #=============================

        $sql_select = array(
            't'        => 'all',
            'g'        => array( 'gname', 'key' ),
             'u'        => array( array( 'name' => 'uname' ), array( 'email' => 'uemail' ), 'ugroup' ),
            'a'        => array( array( 'id' => 'aid' ) ),
            'lr'    => array( array( 'name' => 'last_uname' ) ),
            'at'    => array( array( 'id' => 'attachments' ) ),
        );

        $sql_join = array(
            array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 't' => 'id', '=', 'g' => 'id' ) ),
            array( 'from' => array( 'u' => 'users' ), 'where' => array( 't' => 'uid', '=', 'u' => 'id' ) ),
            array( 'from' => array( 'a' => 'assign_map' ), 'where' => array( array( 't' => 'id', '=', 'a' => 'tid' ), array( $this->trellis->user['id'], '=', 'a' => 'uid', 'and' ) ) ),
            array( 'from' => array( 'lr' => 'users' ), 'where' => array( 't' => 'last_uid', '=', 'lr' => 'id' ) ),
            array( 'from' => array( 'at' => 'attachments' ), 'where' => array( array( 'at' => 'content_type', '=', 'ticket' ), array( 'at' => 'content_id', '=', 't' => 'id', 'and' ) ) ),
        );

        if ( $this->trellis->cache->data['settings']['ticket']['track'] )
        {
            $sql_select['tt'] = array( array( 'date' => 'track_date' ) );
            $sql_join[] = array( 'from' => array( 'tt' => 'tickets_track' ), 'where' => array( array( 'tt' => 'uid', '=', $this->trellis->user['id'] ), array( 'tt' => 'tid', '=', 't' => 'id', 'and' ) ) );
        }

        $t = $this->trellis->func->tickets->get_single_by_id( array(
            'select'    => $sql_select,
            'from'        => array( 't' => 'tickets' ),
            'join'        => $sql_join,
        ), $this->trellis->input['id'] );

        if ( ! $t ) $this->trellis->skin->error('no_ticket');

        #=============================
        # Permissions
        #=============================

        if ( ! $this->check_perm( $t['id'], $t['did'], 'v' ) )
        {
            if ( $params['new'] )
            {
                $this->trellis->skin->error('no_perm');
            }
            else
            {
                $this->trellis->skin->error('no_ticket');
            }
        }

        #=============================
        # Grab Replies
        #=============================

        $replies = $this->trellis->func->tickets->get( array(
                                                       'select'    => array(
                                                                            'r' => 'all',
                                                                            'u' => array( array( 'name' => 'uname' ), array( 'signature' => 'usignature' ), 'sig_html' ),
                                                                            'a' => array( array( 'id' => 'attachments' ) ),
                                                                            ),
                                                       'from'    => array( 'r' => 'replies' ),
                                                       'join'    => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'r' => 'uid', '=', 'u' => 'id' ) ), array( 'from' => array( 'a' => 'attachments' ), 'where' => array( array( 'a' => 'content_type', '=', 'reply' ), array( 'a' => 'content_id', '=', 'r' => 'id', 'and' ) ) ) ),
                                                       'where'    => array( array( 'r' => 'tid' ), '=', $t['id'] ),
                                                       'order'    => array( 'date' => array( 'r' => 'asc' ) ),
                                                )       );

        #=============================
        # Prepare Output
        #=============================

        $custom_fields = array();
        $custom_fields_html = '';

        #=============================
        # Menu Items
        #=============================

        $reply_action_list = '';

        $menu_items = array( array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets' ) );

        if ( $t['closed'] )
        {
            if ( $this->check_perm( $t['id'], $t['did'], 'et' ) ) $menu_items[] = array( 'status', '{lang.menu_status}', '#', 'return confirmStatus('. $t['id'] .')' );
            if ( $this->check_perm( $t['id'], $t['did'], 'ro' ) ) $menu_items[] = array( 'arrow_step_over', '{lang.menu_reopen}', '#', 'return confirmReopen('. $t['id'] .')' );
        }
        else
        {
            if ( $this->check_perm( $t['id'], $t['did'], 'r' ) && ! $t['accepted'] ) $menu_items = array( array( 'tick', '{lang.menu_accept}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=doaccept&amp;id='. $t['id'] ) );
            if ( $this->check_perm( $t['id'], $t['did'], 'et' ) )
            {
                $menu_items[] = array( 'edit', '{lang.menu_edit}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=edit&amp;id='. $t['id'] );
                $menu_items[] = array( 'priority', '{lang.menu_priority}', '#', 'return confirmPriority('. $t['id'] .')' );
                if ( $t['accepted'] ) $menu_items[] = array( 'status', '{lang.menu_status}', '#', 'return confirmStatus('. $t['id'] .')' );
            }

            if ( $this->check_perm( $t['id'], $t['did'], 'es' ) )
            {
                if ( $this->trellis->cache->data['settings']['ticket']['escalate'] && $this->trellis->cache->data['departs'][ $t['did'] ]['escalate_enable'] && ! $t['escalated'] ) $menu_items[] = array( 'escalate', '{lang.menu_escalate}', '#', 'return confirmEscalate('. $t['id'] .')' );

                if ( $t['escalated'] ) $menu_items[] = array( 'rmvescalate', '{lang.menu_rmvescalate}', '#', 'return confirmRmvescalate('. $t['id'] .')' );

                $reply_action_list .= "<li><a href='#' id='reply_escalate'>{lang.reply_escalate}</a></li>";
            }

            if ( $this->check_perm( $t['id'], $t['did'], 'r' ) )
            {
                if ( $t['onhold'] )
                {
                    $menu_items[] = array( 'hold_minus', '{lang.menu_rmvhold}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=dormvhold&amp;id='. $t['id'] );
                }
                else
                {
                    $menu_items[] = array( 'hold', '{lang.menu_hold}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=dohold&amp;id='. $t['id'] );

                    $reply_action_list .= "<li><a href='#' id='reply_hold'>{lang.reply_hold}</a></li>";
                }
            }
        }

        if ( $this->check_perm( $t['id'], $t['did'], 'mv' ) )
        {
            $menu_items[] = array( 'move', '{lang.menu_move}', '#', 'return confirmMove('. $t['id'] .')' );

            $reply_action_list .= "<li><a href='#' id='reply_move'>{lang.reply_move}</a></li>";
        }

        if ( ! $t['closed'] && $this->check_perm( $t['id'], $t['did'], 'c' ) )
        {
            $menu_items[] = array( 'frame_tick', '{lang.menu_close}', '#', 'return confirmClose('. $t['id'] .')' );

            $reply_action_list .= "<li><a href='#' id='reply_close'>{lang.reply_close}</a></li>";
        }

        if ( $this->check_perm( $t['id'], $t['did'], 'dt' ) ) $menu_items[] = array( 'circle_delete', '{lang.menu_delete}', '#', 'return confirmDeleteTicket('. $t['id'] .')' );

        if ( $this->trellis->cache->data['settings']['ticket']['track'] ) $menu_items[] = array( 'balloon', '{lang.menu_mark_unread}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=dountrack&amp;tid='. $t['id'] );

        $menu_items[] = array( 'print', '{lang.menu_print}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=print&amp;id='. $t['id'] );
        $menu_items[] = array( 'arrow_circle_refresh', '{lang.menu_refresh}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=view&amp;id='. $t['id'] );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_tickets_options}', $menu_items );

        #=============================
        # Custom Profile Fields
        #=============================

        $this->trellis->load_functions('cpfields');

        if ( $cpfields = $this->trellis->func->cpfields->grab( $t['ugroup'], 1, 1 ) )
        {
            $fdata = $this->trellis->func->cpfields->get_data( $t['uid'] );

            foreach( $cpfields as $fid => $f )
            {
                if ( $f['type'] == 'checkbox' )
                {
                    $f['extra'] = unserialize( $f['extra'] );

                    $checkbox_html = '';

                    foreach( $f['extra'] as $key => $name )
                    {
                        $checkbox_html .= $this->trellis->skin->checkbox( array( 'name' => 'cpf_'. $f['id'] .'_'. $key, 'title' => $name, 'value' => $fdata[ $f['id'] ][ $key ], 'disabled' => 1 ) ) .'&nbsp;&nbsp;&nbsp;';
                    }

                    $custom_fields[] = array( 'name' => $f['name'], 'data' => $checkbox_html );
                }
                elseif ( $f['type'] == 'dropdown' || $f['type'] == 'radio' )
                {
                    $f['extra'] = unserialize( $f['extra'] );

                    $custom_fields[] = array( 'name' => $f['name'], 'data' => $f['extra'][ $fdata[ $f['id'] ] ] );
                }
                else
                {
                    $custom_fields[] = array( 'name' => $f['name'], 'data' => $fdata[ $f['id'] ] );
                }
            }
        }

        #=============================
        # Custom Department Fields
        #=============================

        $this->trellis->load_functions('cdfields');

        if ( $cdfields = $this->trellis->func->cdfields->grab( $t['did'] ) )
        {
            $fdata = $this->trellis->func->cdfields->get_data( $t['id'] );

            foreach( $cdfields as $fid => $f )
            {
                if ( $f['type'] == 'checkbox' )
                {
                    $f['extra'] = unserialize( $f['extra'] );

                    $checkbox_html = '';

                    foreach( $f['extra'] as $key => $name )
                    {
                        $checkbox_html .= $this->trellis->skin->checkbox( array( 'name' => 'cdf_'. $f['id'] .'_'. $key, 'title' => $name, 'value' => $fdata[ $f['id'] ][ $key ], 'disabled' => 1 ) ) .'&nbsp;&nbsp;&nbsp;';
                    }

                    $custom_fields[] = array( 'name' => $f['name'], 'data' => $checkbox_html );
                }
                elseif ( $f['type'] == 'dropdown' || $f['type'] == 'radio' )
                {
                    $f['extra'] = unserialize( $f['extra'] );

                    $custom_fields[] = array( 'name' => $f['name'], 'data' => $f['extra'][ $fdata[ $f['id'] ] ] );
                }
                else
                {
                    $custom_fields[] = array( 'name' => $f['name'], 'data' => $fdata[ $f['id'] ] );
                }
            }
        }

        #=============================
        # Combine Custom Fields
        #=============================

        $fields_current = 0;

        foreach( $custom_fields as $f )
        {
            $fields_current ++;

            if ( $fields_current & 1 ) $custom_fields_html .= "<tr>";

            if ( ! $f['data'] ) $f['data'] = '--';

            $custom_fields_html .= "<td class='cardcell-light'>{$f['name']}</td>
                                <td class='cardcell-dark'>{$f['data']}</td>";

            if ( ! $fields_current & 1 ) $custom_fields_html .= "</tr>";
        }

        if ( count( $custom_fields ) & 1 )
        {
            $custom_fields_html .= "<td class='cardcell-light'>&nbsp;</td>
                                <td class='cardcell-dark'>&nbsp;</td>
                            </tr>";
        }

        #=============================
        # Ticket Attachments
        #=============================

        if ( $t['attachments'] )
        {
            $this->trellis->load_functions('attachments');

            if ( $attachments = $this->trellis->func->attachments->get( array( 'select' => array( 'id', 'original_name', 'size' ), 'where' => array( array( 'content_type', '=', 'ticket' ), array( 'content_id', '=', $t['id'], 'and' ) ) ) ) )
            {
                $attach_links = array();

                foreach ( $attachments as &$a )
                {
                    $attach_links[] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=attachment&amp;id={$a['id']}'>{$a['original_name']} (". $this->trellis->format_size( $a['size'] ) .")</a>";
                }
            }
        }

        #=============================
        # Ticket
        #=============================

        $this->trellis->load_functions('drop_downs');
        $this->trellis->load_functions('users');

        // Convert For Humans
        if ( $this->trellis->func->tickets->check_assignment( $this->trellis->user['id'], $t['id'] ) )
        {
            $t['priority_human'] = "<img src='<! TD_URL !>/images/priorities/{$this->trellis->cache->data['priorities'][ $t['priority'] ]['icon_assigned']}' alt='{$this->trellis->cache->data['priorities'][ $t['priority'] ]['name']}' class='prioritybox' style='vertical-align:middle' />&nbsp;&nbsp;<a href='". $this->generate_url( array( 'fpriority' => array( $t['priority'] ) ) ) ."'>{$this->trellis->cache->data['priorities'][ $t['priority'] ]['name']}</a>";
        }
        else
        {
            $t['priority_human'] = "<img src='<! TD_URL !>/images/priorities/{$this->trellis->cache->data['priorities'][ $t['priority'] ]['icon_regular']}' alt='{$this->trellis->cache->data['priorities'][ $t['priority'] ]['name']}' class='prioritybox' style='vertical-align:middle' />&nbsp;&nbsp;<a href='". $this->generate_url( array( 'fpriority' => array( $t['priority'] ) ) ) ."'>{$this->trellis->cache->data['priorities'][ $t['priority'] ]['name']}</a>";
        }

        $t['status_human'] = "<a href='". $this->generate_url( array( 'fstatus' => array( $t['status'] ) ) ) ."'>". $this->trellis->cache->data['statuses'][ $t['status'] ]['name_staff'] ."</a>";

        $t['dname'] = "<a href='". $this->generate_url( array( 'fdepart' => array( $t['did'] ) ) ) ."'>". $this->trellis->cache->data['departs'][ $t['did'] ]['name'] ."</a>";

        $t['date_human'] = $this->trellis->td_timestamp( array( 'time' => $t['date'], 'format' => 'long' ) );

        $t['last_reply'] = $this->trellis->td_timestamp( array( 'time' => $t['last_reply'], 'format' => 'long' ) );

        if ( $t['escalated'] ) $t['escalated_icon'] = "<img src='<! IMG_DIR !>/icons/escalate.png' alt='E' style='vertical-align:middle;margin-bottom:2px' />&nbsp;";

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

        if ( ! empty( $attach_links ) ) $t['message'] .= "<p class='attachments'>{lang.attachments}: ". implode( ', ', $attach_links ). "<p>";

        $this->output .= "<script type='text/javascript'>
                        //<![CDATA[
                        reply_first = false;
                        function confirmPriority(tid) {
                            dialogConfirm({
                                title: '{lang.dialog_priority_title}',
                                message: \"{lang.dialog_priority_msg} <select name='tpriority' id='tpriority'>". $this->trellis->func->drop_downs->priority_drop( array( 'select' => $t['priority'] ) ) ."</select>\",
                                yesButton: '{lang.dialog_priority_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=dopriority&id='+tid+'&pid='+new_priority) },
                                noButton: '{lang.cancel}',
                                beforeclose: function() { new_priority = $('#tpriority').val(); },
                                width: 350
                            }); return false;
                        }
                        function confirmStatus(tid) {
                            $.get('admin.php?section=manage&page=tickets&act=getstatus',
                                { id: tid },
                                function(data) {
                                    if (data != 0) {
                                        dialogConfirm({
                                            title: '{lang.dialog_status_title}',
                                            message: \"<p>{lang.dialog_status_msg_a}</p><p>{lang.dialog_status_msg_b} <select name='tstatus' id='tstatus'>\"+data+\"</select></p>\",
                                            yesButton: '{lang.dialog_status_button}',
                                            yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=dostatus&id='+tid+'&sid='+new_status) },
                                            noButton: '{lang.cancel}',
                                            beforeclose: function() { new_status = $('#tstatus').val(); },
                                            width: 350
                                        });
                                    }
                                }); return false;
                        }
                        function confirmEscalate(tid) {
                            dialogConfirm({
                                title: '{lang.dialog_escalate_title}',
                                message: \"<p>{lang.dialog_escalate_msg}</p><p><input type='checkbox' id='tesclrassign' name='tesclrassign' value='1' checked='checked' /> <label for='tesclrassign'>{lang.dialog_clear_assignments}</label></p>\",
                                yesButton: '{lang.dialog_escalate_button}',
                                yesAction: function() {
                                    if ( reply_first ) {
                                        if ( ! addReply() ) return false;
                                    }
                                    goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=doescalate&id='+tid+'&clrassign='+es_clear_assign);
                                },
                                noButton: '{lang.cancel}',
                                beforeclose: function() { es_clear_assign = ( $('#tesclrassign').is(':checked') ) ? 1 : 0; },
                                width: 350
                            }); return false;
                        }
                        function confirmRmvescalate(tid) {
                            dialogConfirm({
                                title: '{lang.dialog_rmvescalate_title}',
                                message: '{lang.dialog_rmvescalate_msg}',
                                yesButton: '{lang.dialog_rmvescalate_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=dormvescalate&id='+tid) },
                                noButton: '{lang.cancel}',
                                width: 350
                            }); return false;
                        }
                        function confirmMove(tid) {
                            dialogConfirm({
                                title: '{lang.dialog_move_title}',
                                message: \"<p>{lang.dialog_move_msg_a}</p><p>{lang.dialog_move_msg_b} <select name='tmvdid' id='tmvdid'>". $this->trellis->func->drop_downs->dprt_drop( 0, $t['did'], 1 ) ."</select></p><p><input type='checkbox' id='tmvclrassign' name='tmvclrassign' value='1' checked='checked' /> <label for='tmvclrassign'>{lang.dialog_clear_assignments}</label></p>\",
                                yesButton: '{lang.dialog_move_button}',
                                yesAction: function() {
                                    if ( reply_first ) {
                                        if ( ! addReply() ) return false;
                                    }
                                    goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=domove&id='+tid+'&did='+new_did+'&clrassign='+mv_clear_assign);
                                },
                                noButton: '{lang.cancel}',
                                beforeclose: function() { new_did = $('#tmvdid').val(); mv_clear_assign = ( $('#tmvclrassign').is(':checked') ) ? 1 : 0; },
                                width: 350
                            }); return false;
                        }
                        function confirmClose(tid) {
                            dialogConfirm({
                                title: '{lang.dialog_close_title}',
                                message: \"<p>{lang.dialog_close_msg}</p><p><input type='checkbox' name='reopen' id='reopen' value='1' checked='checked' /> <label for='reopen'>{lang.dialog_allow_reopen_msg_a}</label></p><p>{lang.dialog_allow_reopen_msg_b}</p>\",
                                yesButton: '{lang.dialog_close_button}',
                                yesAction: function() {
                                    if ( reply_first ) {
                                        if ( ! addReply() ) return false;
                                    }
                                    goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=doclose&id='+tid+'&reopen='+allow_reopen);
                                },
                                beforeclose: function() { allow_reopen = ( $('#reopen').is(':checked') ) ? 1 : 0; },
                                noButton: '{lang.cancel}',
                                width: 380
                            }); return false;
                        }
                        function confirmReopen(tid) {
                            dialogConfirm({
                                title: '{lang.dialog_reopen_title}',
                                message: '{lang.dialog_reopen_msg}',
                                yesButton: '{lang.dialog_reopen_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=doreopen&id='+tid) },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        function confirmDeleteTicket(tid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_ticket_title}',
                                message: '{lang.dialog_delete_ticket_msg}',
                                yesButton: '{lang.dialog_delete_ticket_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=dodel&id='+tid) },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        function confirmDeleteReply(rid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_reply_title}',
                                message: '{lang.dialog_delete_reply_msg}',
                                yesButton: '{lang.dialog_delete_reply_button}',
                                yesAction: function() { inlineReplyDelete(rid); },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        function askKeepHold() {
                            dialogConfirm({
                                title: '{lang.dialog_keep_hold_title}',
                                message: '{lang.dialog_keep_hold_msg}',
                                yesButton: '{lang.dialog_keep_hold_button_yes}',
                                yesAction: function() {
                                    $('#keep_hold').val(2);
                                    $('#add_reply').trigger('submit');
                                },
                                noButton: '{lang.dialog_keep_hold_button_no}',
                                noAction: function() {
                                    $('#keep_hold').val(1);
                                    $('#add_reply').trigger('submit');
                                },
                            }); return false;
                        }";

        if ( $t['onhold'] )
        {
            $this->output .= "
                        $(function() {
                            $('#add_reply').submit(function() {
                                if ( $('#keep_hold').val() == 0 ) {
                                    return askKeepHold();
                                }
                                else {
                                    return true;
                                }
                            });
                        });";
        }

        if ( $this->trellis->cache->data['settings']['ticket']['track'] )
        {
            $unread_found = false;

            if ( $t['track_date'] < $t['date'] )
            {
                $unread_found = true;
                $unread = true;
            }
            else
            {
                $unread = false;
            }
        }

        $this->output .= "
                        //]]>
                        </script>
                        <input type='hidden' id='tid' name='tid' value='{$t['id']}' />
                        ". $this->trellis->skin->start_ticket_details( '{lang.ticket_num}'. $t['id'] .': '. $t['subject'] ) ."
                        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                            <tr>
                                <td class='cardcell-light' width='20%'>{lang.ticket_id}</td>
                                <td class='cardcell-dark' width='30%'>{$t['escalated_icon']}{$t['id']}</td>
                                <td class='cardcell-light' width='20%'>{lang.ticket_mask}</td>
                                <td class='cardcell-dark' width='30%'>{$t['mask']}</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.subject}</td>
                                <td class='cardcell-dark'>{$t['subject']}</td>
                                <td class='cardcell-light'>{lang.replies}</td>
                                <td class='cardcell-dark'>{$t['replies']}</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.priority}</td>
                                <td class='cardcell-dark'>{$t['priority_human']}</td>
                                <td class='cardcell-light'>{lang.last_reply}</td>
                                <td class='cardcell-dark'>{$t['last_reply']}</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.department}</td>
                                <td class='cardcell-dark'>{$t['dname']}</td>
                                <td class='cardcell-light'>{lang.last_replier}</td>
                                <td class='cardcell-dark'>". ( ( $t['last_uid'] ) ? "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$t['last_uid']}'>{$t['last_uname']}</a>" : "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;search=". urlencode( $t['email'] ) ."&amp;field=email' title='{$t['ipadd']}'>{$t['gname']} ({lang.guest})</a>" ) ."</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.submitted_on}</td>
                                <td class='cardcell-dark'>{$t['date_human']}</td>
                                <td class='cardcell-light'>{lang.status}</td>
                                <td class='cardcell-dark'>{$t['status_human']}</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.submitted_by}</td>
                                <td class='cardcell-dark'>". ( ( $t['uid'] ) ? "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$t['uid']}' title='{$t['ipadd']}'>{$t['uname']}</a>" : "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;search=". urlencode( $t['email'] ) ."&amp;field=email' title='{$t['ipadd']}'>{$t['gname']} ({lang.guest})</a>" ) ."</td>
                                <td class='cardcell-light'>{lang.satisfaction}</td>
                                <td class='cardcell-dark'><img src='<! IMG_DIR !>/star_full.gif' width='12' height='12' alt='Star' /><img src='<! IMG_DIR !>/star_full.gif' width='12' height='12' alt='Star' /><img src='<! IMG_DIR !>/star_full.gif' width='12' height='12' alt='Star' /><img src='<! IMG_DIR !>/star_half.gif' width='12' height='12' alt='Star' /><img src='<! IMG_DIR !>/star_empty.gif' width='12' height='12' alt='Star' /></td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.ticket_email}</td>
                                <td class='cardcell-dark'><a href='". $this->generate_url( array( 'search' => $t['email'], 'field' => 'email' ) ) ."'>{$t['email']}</a></td>
                                <td class='cardcell-light'>{lang.user_email}</td>
                                <td class='cardcell-dark'>". ( ( $t['uid'] ) ? "<a href='". $this->generate_url( array( 'search' => $t['uemail'], 'field' => 'uemail' ) ) ."'>{$t['uemail']}</a>" : "<a href='". $this->generate_url( array( 'search' => $t['email'], 'field' => 'email' ) ) ."'>{$t['email']}</a>" ) ."</td>
                            </tr>
                            ". $custom_fields_html ."
                        </table>
                        ". $this->trellis->skin->end_ticket_details() ."
                        ". ( ( $unread ) ? "<a id='unread'></a>" : '' ) ."
                        <div id='ticketroll'>
                            ". $this->trellis->skin->group_title( ( ( $t['track_date'] < $t['date'] ) ? "<img src='<! IMG_DIR !>/icons/balloon.png' alt='*' title='{lang.unread}' style='vertical-align:top;' />&nbsp;" : '' ). '{lang.ticket_content}' ) ."
                            <div class='rollstart'>
                                {$t['message']}
                            </div>";

        #=============================
        # Replies
        #=============================

        $reply_untrack = ( $this->trellis->cache->data['settings']['ticket']['track'] ) ? 1 : 0;

        if ( ! empty( $replies ) )
        {
            foreach( $replies as &$r )
            {
                if ( $r['secret'] )
                {
                    $rclass = 'staffonly';
                }
                elseif ( $r['staff'] )
                {
                    $rclass = 'staff';
                }
                else
                {
                    $rclass = 'customer';
                }

                $r['date_human'] = $this->trellis->td_timestamp( array( 'time' => $r['date'], 'format' => 'long' ) );

                $routput_params = array( 'linkify' => 1 );

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

                // Tracking
                if ( $this->trellis->cache->data['settings']['ticket']['track'] )
                {
                    if ( ( ! $unread_found ) && ( $t['track_date'] < $r['date'] ) )
                    {
                        $unread_found = true;
                        $unread = true;
                    }
                    else
                    {
                        $unread = false;
                    }
                }

                if ( $unread ) $this->output .= "<a id='unread'></a>";

                $this->output .= "<div id='r{$r['id']}' class='reply'>
                                <div class='bar{$rclass}'>";

                $reply_edit = 0;
                $reply_delete = 0;
                $reply_javascript_html = '';

                if ( $r['html'] ) $reply_javascript_html = 'Html';

                if ( $this->check_perm( $t['id'], $t['did'], 'er' ) || ( $this->trellis->user['g_reply_edit'] && $r['uid'] == $this->trellis->user['id'] ) ) $reply_edit = 1;
                if ( $this->check_perm( $t['id'], $t['did'], 'dr' ) || ( $this->trellis->user['g_reply_delete'] && $r['uid'] == $this->trellis->user['id'] ) ) $reply_delete = 1;

                if ( $reply_untrack || $reply_edit || $reply_delete ) $this->output .= "<div class='barright'>";

                if ( $reply_untrack ) $this->output .= "<span id='rmark_". $r['id'] ."'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=dountrack&amp;rid={$r['id']}'><img src='<! IMG_DIR !>/icons/balloon.png' alt='{lang.mark_unread}' />{lang.mark_unread}</a></span>";

                if ( $reply_edit ) $this->output .= "<span id='redit_". $r['id'] ."' style='cursor:pointer' onclick='inlineReplyEdit{$reply_javascript_html}(". $r['id'] .")'><img src='<! IMG_DIR !>/icons/page_edit.png' alt='{lang.edit}' />{lang.edit}</span><span id='rsave_". $r['id'] ."' style='display:none;cursor:pointer' onclick='inlineReplySave{$reply_javascript_html}(". $r['id'] .")'><img src='<! IMG_DIR !>/icons/page_edit.png' alt='{lang.save_edit}' />{lang.save_edit}</span>";

                if ( $reply_delete )
                {
                    $this->output .= "<span id='rdelete_". $r['id'] ."' style='cursor:pointer' onclick='return confirmDeleteReply(". $r['id'] .")'><img src='<! IMG_DIR !>/icons/page_delete.png' alt='{lang.delete}' />{lang.delete}</span>";
                }

                if ( $reply_edit || $reply_delete ) $this->output .= "</div>";

                #=============================
                # Reply Attachments
                #=============================

                if ( $r['attachments'] )
                {
                    $this->trellis->load_functions('attachments');

                    if ( $attachments = $this->trellis->func->attachments->get( array( 'select' => array( 'id', 'original_name', 'size' ), 'where' => array( array( 'content_type', '=', 'reply' ), array( 'content_id', '=', $r['id'], 'and' ) ) ) ) )
                    {
                        $r['message'] .= "<p class='attachments'>{lang.attachments}: ";

                        $attach_links = array();

                        foreach ( $attachments as &$a )
                        {
                            $attach_links[] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=attachment&amp;id={$a['id']}'>{$a['original_name']} (". $this->trellis->format_size( $a['size'] ) .")</a>";
                        }

                        $r['message'] .= implode( ', ', $attach_links ). "<p>";
                    }
                }

                if ( $this->trellis->cache->data['settings']['ticket']['track'] && ( $t['track_date'] < $r['date'] ) ) $this->output .= "<img src='<! IMG_DIR !>/icons/balloon.png' alt='*' title='{lang.unread}' style='vertical-align:top;' />&nbsp;";

                $this->output .= "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$r['uid']}' title='{$r['ipadd']}'><strong>{$r['uname']}</strong></a> -- {$r['date_human']}</div>
                                <div class='roll{$rclass}' id='rm{$r['id']}'>
                                    {$r['message']}
                                </div>
                        </div>";
            }
        }

        #=============================
        # Form
        #=============================

        if ( ! $t['closed'] && $this->check_perm( $t['id'], $t['did'], 'r') )
        {
            if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['ticket']['rte'] )
            {
                $this->output .= $this->trellis->skin->tinymce_js( 'message' );

                $html = 1;
            }
            else
            {
                $html = 0;
            }

            if ( $params['reply_error'] )
            {
                $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $params['reply_error'] .'}' );
            }

            ( $this->trellis->user['sig_auto'] ) ? $sig_checked = " checked='checked'" : $sig_checked = '';

            $reply_action_button = '';

            if ( $reply_action_list ) $reply_action_list = "<div id='reply_action_list' class='fdrop' style='display: none;'><ul>". $reply_action_list ."</ul></div>";

            $rt_list = '';

            foreach( $this->trellis->cache->data['rtemplates'] as $rt )
            {
                $rt_list .= "<li id='rt{$rt['id']}' onclick='addRT({$rt['id']})'>{$rt['name']}</li>";
            }

            if ( ! $t['aid'] && ( $this->check_perm( $t['id'], $t['did'], 'aa' ) || $this->check_perm( $t['id'], $t['did'], 'as' ) ) )
            {
                $assign_checkbox = "<input type='checkbox' name='assign_to_me' id='assign_to_me' value='1' style='margin-bottom:2px;'";

                if ( $this->trellis->user['auto_assign'] ) $assign_checkbox .= " checked='checked'";

                $assign_checkbox .= " />&nbsp;<label for='assign_to_me'>{lang.assign_to_me}</label>&nbsp;&nbsp;";
            }

            $this->output .= "<form action='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=doaddreply&amp;id={$t['id']}' id='add_reply' method='post'>
                                <input type='hidden' id='html' name='html' value='{$html}' />
                                <input type='hidden' id='keep_hold' name='keep_hold' value='0' />
                                <div class='slatebox'>{lang.post_a_reply}</div>
                                <div class='rollpost'>
                                ". $this->trellis->skin->textarea( array( 'name' => 'message', 'cols' => 80, 'rows' => 8, 'width' => '98%', 'height' => '200px' ) ) ."
                                </div>
                                ";

            if ( $this->trellis->cache->data['settings']['ticket']['attachments'] && $this->trellis->user['g_ticket_attach'] && $this->trellis->cache->data['departs'][ $t['did'] ]['allow_attach'] ) {
                $this->output .= "<div class='option1'>
                                    ". $this->trellis->skin->uploadify_js( 'upload_file', array( 'section' => 'manage', 'page' => 'tickets', 'act' => 'doupload', 'type' => 'reply', 'id' => $t['id'] ), array( 'multi' => true, 'list' => true ) ) ."
                                </div>";
            }


            $this->output .= "<div class='formtail' style='text-align:left'><span>". $this->trellis->skin->submit_button( 'reply', '{lang.button_add_reply}' ) . $this->trellis->skin->button( 'reply_action', '{lang.select_action}' ) ."</span>" . $reply_action_list ."&nbsp;{$assign_checkbox}<input type='checkbox' name='signature' id='signature' value='1' style='margin-bottom:2px;'{$sig_checked} />&nbsp;<label for='signature'>{lang.append_signature}</label>&nbsp;&nbsp;<input type='checkbox' name='secret' id='secret' value='1' style='margin-bottom:2px;' />&nbsp;<label for='secret'>{lang.staff_only_reply}</label><div style='float:right;'><button id='add_rt' name='add_rt' type='button' class='buttontinydrop'>{lang.reply_templates}&nbsp;</button><div id='add_rt_block' class='fakedrop ui-corner-all'><ul>{$rt_list}</ul></div></div></div>
                                </form>";
        }

        #=============================
        # History
        #=============================

        $this->output .= "<div class='slatebox'><a href='<! TD_URL !>/admin.php?section=tools&amp;page=logs&amp;type=ticket&amp;content_type=ticket&amp;content_id={$t['id']}'>{lang.recent_ticket_history}</a></div>
                            <div class='rollhistory'>
                                <table width='100%' cellpadding='0' cellspacing='0'>";

        if ( empty( $replies ) )
        {
            $sql_where = array( array( array( 'l' => 'type' ), '=', 'ticket' ), array( array( 'l' => 'content_type' ), '=', 'ticket', 'and' ), array( array( 'l' => 'content_id' ), '=', $t['id'], 'and' ) );
        }
        else
        {
            $sql_where = array( array( array( array( 'l' => 'type' ), '=', 'ticket' ), array( array( 'l' => 'content_type' ), '=', 'ticket', 'and' ), array( array( 'l' => 'content_id' ), '=', $t['id'], 'and' ) ), array( array( array( 'l' => 'type' ), '=', 'ticket' ), array( array( 'l' => 'content_type' ), '=', 'reply', 'and' ), array( array( 'l' => 'content_id' ), 'in', array_keys( $replies ), 'and' ), 'or' ) );
        }

        $this->trellis->db->construct( array(
                                                   'select'    => array(
                                                                        'l' => 'all',
                                                                        'u' => array( array( 'name' => 'uname' ) ),
                                                                        ),
                                                   'from'    => array( 'l' => 'logs' ),
                                                   'join'    => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'l' => 'uid', '=', 'u' => 'id' ) ) ),
                                                   'where'    => $sql_where,
                                                   'order'    => array( 'date' => array( 'l' => 'desc' ), 'id' => array( 'l' => 'desc' ) ),
                                                   'limit'    => array( 0, 15 ),
                                            )       );

        $this->trellis->db->execute();

        while( $l = $this->trellis->db->fetch_row() )
        {
            $l['date'] = $this->trellis->td_timestamp( array( 'time' => $l['date'], 'format' => 'short' ) );

            if ( $l['level'] == 2 )
            {
                $fontcolor_start = "<font color='#790000'>";
                $fontcolor_end = "<font color='#790000'>";
            }
            else
            {
                $fontcolor_start = "";
                $fontcolor_end = "";
            }

            $this->output .= "<tr>
                                <td class='slatecell-light' width='38%'>{$fontcolor_start}{$l['action']}{$fontcolor_end}</td>
                                <td class='slatecell-dark' width='16%' style='font-weight:normal'>{$fontcolor_start}{$l['date']}{$fontcolor_end}</td>
                                <td class='slatecell-light' width='19%'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$l['uid']}'>{$fontcolor_start}{$l['uname']}{$fontcolor_end}</a></td>
                                <td class='slatecell-light' width='17%' style='font-weight:normal'>{$fontcolor_start}{$l['ipadd']}{$fontcolor_end}</td>
                            </tr>";
        }

        $this->output .= "</table>
                        </div>
                        </div>
                        <script type='text/javascript'>
                        //<![CDATA[
                        $(function() {
                            $('#add_flag').bind('click', function () {
                                $('#add_flag_block').toggle();
                            });
                            $('#add_assign').bind('click', function () {
                                $('#add_assign_block').toggle();
                            });
                            if ( $('#add_reply').length > 0 ) {
                                $('#add_rt').bind('click', function () {
                                    $('#add_rt_block').toggle();
                                });
                                //$('#reply_action').bind('click', function () {
                                //    $('#reply_action_list').toggle();
                                //});
                                $('#reply_action').menu({
                                    content: $('#reply_action_list').html(),
                                    positionOpts: {
                                        posX: 'left',
                                        posY: 'bottom',
                                        offsetX: -($('#reply_action').offset().left - $('#reply').offset().left),
                                        offsetY: 0,
                                        directionH: 'right',
                                        directionV: 'down',
                                        detectH: true,
                                        detectV: true
                                    }
                                });
                            }
                            $('#add_assign_input').autocomplete('<! TD_URL !>/admin.php?act=lookup&type=staff&assign=". $t['did'] ."', {
                                dataType: 'json',
                                parse: function(data) {
                                    return $.map(data, function(row) {
                                        return {
                                            data: row,
                                            result: row.caption
                                        }
                                    });
                                },
                                formatItem: function(row, i, max) {
                                    return row.caption;
                                },
                                matchSubset: false
                            });
                            $('#add_assign_input').result(function(event, data, formatted) {
                                $('#add_assign_id').val(data['value']);
                            });
                            $('#save_notes').bind('click', function () {
                                $.post('admin.php?section=manage&page=tickets&act=donotes&id='+$('#tid').val(),
                                    { notes: $('#notes').val() },
                                    function(data) {
                                        if (data != 0) $('#save_notes_status').stop(true).fadeIn().animate({opacity: 1.0}, {duration: 2000}).fadeOut('slow');
                                    });
                            });
                            $('#reply_escalate').bind('click', function () {
                                reply_first = true; confirmEscalate($('#tid').val());
                            });
                            $('#reply_hold').bind('click', function () {
                                if( addReply() ) goToUrl('<! TD_URL !>/admin.php?section=manage&page=tickets&act=dohold&id='+$('#tid').val());
                            });
                            $('#reply_move').bind('click', function () {
                                reply_first = true; confirmMove($('#tid').val());
                            });
                            $('#reply_close').bind('click', function () {
                                reply_first = true; confirmClose($('#tid').val());
                            });

                            $('#reply').button().click(function() {
                            })
                            .next()
                            .button({
                                text: false,
                                icons: {
                                    primary: 'ui-icon-triangle-1-s'
                                }
                            })
                            .parent()
                            .buttonset();
                            $('#add_assign').button({ icons: { primary: 'ui-icon-triangle-1-s' } });
                            $('#add_flag').button({ icons: { primary: 'ui-icon-triangle-1-s' } });
                            $('#add_rt').button({ icons: { primary: 'ui-icon-triangle-1-s' } });
                        });
                        //]]>
                        </script>";

        #=============================
        # Assignments
        #=============================

        $assign_items = '';
        $assign_used = 0;

        if ( $assignments = $this->trellis->func->tickets->get_assignments( $t['id'] ) )
        {
            foreach( $assignments as $a )
            {
                $assign_items .= "<li id='a{$a['uid']}'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$a['uid']}'>{$a['uname']}</a>";

                if ( $this->check_perm( $t['id'], $t['did'], 'aa' ) || ( $a['uid'] == $this->trellis->user['id'] && $this->check_perm( $t['id'], $t['did'], 'as' ) ) ) $assign_items .= "<img src='<! IMG_DIR !>/icons/cross.png' alt='X' id='ai{$a['uid']}' class='listdel' onclick='delAssign({$a['uid']},{$t['id']})' />";

                $assign_items .= "</li>";
            }

            $assign_used = 1;
        }

        $assign_items .= "<li id='not_assigned'";

        if ( $assign_used ) $assign_items .= " style='display:none'";

        $assign_items .= "><em>{lang.not_assigned}</em></li>";

        if ( $this->check_perm( $t['id'], $t['did'], 'aa' ) )
        {
            $assign_items .= "<li><button id='add_assign' name='add_assign' type='button' class='buttontinydrop' />{lang.button_add_assign}</button>
                            <div id='add_assign_block' class='fakedrop ui-corner-all'><ul><li style='cursor:default'><input name='add_assign_id' id='add_assign_id' type='hidden' value='0' /><input name='add_assign_input' id='add_assign_input' type='text' value='' size='22' />&nbsp;&nbsp;<input name='add_assign_button' id='add_assign_button' type='submit' value='{lang.button_add}' class='buttonmini' onclick='addAssign({$t['id']})' /></li><li style='cursor:default'><em>{lang.add_assign_instructions}</em></li></ul></div></li>";
        }
        elseif ( $this->check_perm( $t['id'], $t['did'], 'as' ) )
        {
            $assign_items .= "<li><input name='add_assign_id' id='add_assign_id' type='hidden' value='". $this->trellis->user['id'] ."' /><input name='add_assign_button' id='add_assign_button' type='submit' value='{lang.button_assign_myself}' class='buttonmini' onclick='addAssign({$t['id']})' /></li>";
        }

        $this->trellis->skin->add_sidebar_list_custom( '{lang.ticket_assignments}', $assign_items, 'assign_list' );

        #=============================
        # Flags
        #=============================

        if ( ! empty( $this->trellis->cache->data['flags'] ) )
        {
            $flags_used = array();
            $flags_items = '';
            $flags_list = '';
            $flags_add_list = 0;

            if ( $flags = $this->trellis->func->tickets->get_flags( $t['id'] ) )
            {
                foreach( $flags as $f )
                {
                    $flags_used[ $f['fid'] ] = 1;

                    $flags_items .= "<li id='f{$f['fid']}'><a href='". $this->generate_url( array( 'fflag' => array( $f['fid'] ) ) ) ."'><img src='<! TD_URL !>/images/flags/{$f['icon']}' alt='{$f['name']}' class='flagicon' />{$f['name']}</a><img src='<! IMG_DIR !>/icons/cross.png' alt='X' id='fi{$f['fid']}' class='listdel' onclick='delFlag({$f['fid']},{$t['id']})' /></li>";
                }
            }

            $flags_items .= "<li id='no_flags'";

            if ( ! empty( $flags_used ) ) $flags_items .= " style='display:none'";

            $flags_items .= "><em>{lang.no_flags}</em></li>";

            foreach( $this->trellis->cache->data['flags'] as $f )
            {
                if ( ! $flags_used[ $f['id'] ] )
                {
                    if ( ! $flags_add_list ) $flags_add_list = 1;

                    $flags_list .= "<li id='af{$f['id']}' onclick='addFlag({$f['id']},{$t['id']})'><img src='<! TD_URL !>/images/flags/{$f['icon']}' alt='{$f['name']}' class='flagicon' />{$f['name']}</li>";
                }
            }

            $flags_list .= "<li id='noaddflags'";

            if ( $flags_add_list ) $flags_list .= " style='display:none'";

            $flags_list .= "><em>{lang.no_flags_to_add}</em></li>";

            $flags_items .= "<li><button id='add_flag' name='add_flag' type='button' class='buttontinydrop' />{lang.button_add_flag}</button><div id='add_flag_block' class='fakedrop ui-corner-all'><ul>{$flags_list}</ul></div></li>";

            $this->trellis->skin->add_sidebar_list_custom( '{lang.ticket_flags}', $flags_items, 'flags_list' );
        }

        #=============================
        # Tracking
        #=============================

        if ( $this->trellis->cache->data['settings']['ticket']['track'] ) $this->trellis->func->tickets->track( $t['id'], $t['track_date'] );

        #=============================
        # Do Output
        #=============================

        $notes_items = array(
                            "<textarea id='notes' name='notes' cols='4' rows='6' style='width:99%' class='notesbox'>{$t['notes']}</textarea>",
                            "<input name='save_notes' id='save_notes' type='submit' value='{lang.button_save_notes}' class='buttonmini' /> <span id='save_notes_status' class='ajax_update_button'>{lang.saved}</span>",
                            );

        $this->trellis->skin->add_sidebar_list( '{lang.ticket_notes}', $notes_items );

        $this->trellis->skin->add_skin_javascript( 'autocomplete.js' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Ticket
    #=======================================

    private function add_ticket()
    {
        if ( ! $this->trellis->user['g_ticket_create'] && $this->trellis->user['id'] != 1 ) $this->trellis->skin->error('no_perm');

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

    private function add_ticket_step_1()
    {
        #=============================
        # Do Output
        #=============================

        if ( $this->trellis->input['uid'] )
        {
            $this->trellis->load_functions('users');

            if ( ! $u = $this->trellis->func->users->get_single( array( 'name' ), array( 'id', '=', $this->trellis->input['uid'] ) ) ) $this->trellis->skin->error('no_user');
        }

        $perms = &$this->trellis->user['g_depart_perm'];
        if ( $this->trellis->user['id'] != 1 )
        {
            if ( is_array( $this->trellis->user['g_acp_depart_perm'] ) )
            {
                foreach( $this->trellis->user['g_acp_depart_perm'] as $did => $dperm )
                {
                    if ( $dperm['v'] ) $perms[ $did ] = 1;
                }
            }
        }

        $depart_list = "<table width='100%' cellpadding='0' cellspacing='0' class='departlist'>";

        foreach( $this->trellis->cache->data['departs'] as $id => &$d )
        {
            if ( $this->trellis->user['id'] == 1 || $perms[ $d['id'] ] ) $depart_list .= "<tr><td width='1%' valign='top'><input type='radio' name='did' id='d_{$id}' value='{$id}'". ( ( $this->trellis->input['did'] == $id ) ? " checked='checked'" : "" ) ." /></td><td width='99%'><label for='d_{$id}'>{$d['name']}<br /><em>{$d['description']}</em></label></td></tr>";
        }

        $depart_list .= '</table>';

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=add&amp;step=2", 'add_ticket', 'post' ) ."
                        <input name='uid' id='uid' type='hidden' value='{$this->trellis->input['uid']}' />
                        ". $this->trellis->skin->start_group_table( '{lang.submit_a_ticket}', 'a' ) ."
                        <tr>
                            <th class='bluecellthin-th' align='left' colspan='2'>{lang.submit_ticket_user_msg}</th>
                        </tr>
                        ". $this->trellis->skin->group_table_row( '{lang.behalf_of_user}', $this->trellis->skin->textfield( 'uname', $u['name'] ), 'a', '20%', '80%' ) ."
                        ". $this->trellis->skin->end_group_table() ."
                        ". $this->trellis->skin->group_sub( '{lang.select_department}' ) ."
                        ". $this->trellis->skin->group_row( $depart_list, 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_step_2}' ) ) ."
                        </div>";

        $this->trellis->skin->end_group( 'a' );

        $this->output .= "<script type='text/javascript'>
                        //<![CDATA[
                        $('#uname').autocomplete('<! TD_URL !>/admin.php?act=lookup&type=user', {
                            dataType: 'json',
                            parse: function(data) {
                                return $.map(data, function(row) {
                                    return {
                                        data: row,
                                        result: row.caption
                                    }
                                });
                            },
                            formatItem: function(row, i, max) {
                                return row.caption;
                            },
                            matchSubset: false
                        });
                        $('#uname').result(function(event, data, formatted) {
                            $('#uid').val(data['value']);
                        });
                        //]]>
                        </script>";

        $this->output .= $this->trellis->skin->focus_js('uname');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_tickets_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_skin_javascript( 'autocomplete.js' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Ticket Step 2
    #=======================================

    private function add_ticket_step_2()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->load_functions('users');

        $validate = true;
        if ( $this->trellis->input['uid'] )
        {
            if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['uid'] ) )
            {
                $this->trellis->send_message( 'error', $this->trellis->lang['error_no_user'] );
                $validate = false;
            }
        }
        else
        {
            if ( $this->trellis->validate_email( $this->trellis->input['uname'] ) )
            {
                if ( ! $u = $this->trellis->func->users->get_single_by_email( array( 'id', 'name' ), $this->trellis->input['uname'] ) )
                {
                    $u = array( 'id' => 0 );
                }
            }
            else
            {
                $this->trellis->send_message( 'error', $this->trellis->lang['error_no_user'] );
                $validate = false;
            }
        }
        if ( ! $this->trellis->input['did'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_depart'] );
            $validate = false;
        }
        if ( ! $validate )
        {
            $this->trellis->skin->preserve_input = 1;
            $this->add_ticket_step_1();
        }

        $perms = &$this->trellis->user['g_depart_perm'];

        if ( $this->trellis->user['id'] != 1 && ! $perms[ $this->trellis->input['did'] ])
        {
            $this->trellis->skin->error('no_perm');
        }

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');
        $this->trellis->load_functions('cdfields');

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=doadd", 'add_ticket', 'post' ) ."
                        <input name='uid' id='uid' type='hidden' value='{$this->trellis->input['uid']}' />
                        ". ( ( ! $u['id'] ) ? "<input name='email' id='email' type='hidden' value='{$this->trellis->input['uname']}' />" : "" ) ."
                        <input name='did' id='did' type='hidden' value='{$this->trellis->input['did']}' />
                        ". $this->trellis->skin->start_group_table( '{lang.submit_a_ticket}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( ( ( $u['id'] ) ? '{lang.user}' : '{lang.guest_email}' ), ( ( $u['id'] ) ? $u['name'] : $this->trellis->input['uname'] ), 'a', '20%', '80%' ) ."
                        ". ( ( ! $u['id'] ) ? $this->trellis->skin->group_table_row( '{lang.guest_name}', $this->trellis->skin->textfield( 'name' ), 'a' ) : "" ) ."
                        ". ( ( ! $u['id'] ) ? $this->trellis->skin->group_table_row( '{lang.guest_preferences}', "<select name='lang'>". $this->trellis->func->drop_downs->lang_drop( $this->trellis->input['lang'] ) ."</select>&nbsp;&nbsp;&nbsp;". $this->trellis->skin->checkbox( array( 'name' => 'notify', 'title' => '{lang.email_notifications}', 'value' => ( ( isset( $this->trellis->input['notify'] ) ) ? $this->trellis->input['notify'] : 1 ) ) ), 'a' ) : "" ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.subject}', $this->trellis->skin->textfield( 'subject' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.priority}', "<select name='priority'>". $this->trellis->func->drop_downs->priority_drop( array( 'select' => $this->trellis->input['priority'] ) ) ."</select>", 'a' );

        if ( $cfields = $this->trellis->func->cdfields->grab( $this->trellis->input['did'] ) )
        {
            foreach( $cfields as $fid => $f )
            {
                $f['extra'] = unserialize( $f['extra'] );

                if ( $f['type'] == 'textfield' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->textfield( array( 'name' => 'cdf_'. $f['id'], 'length' => $f['extra']['size'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'textarea' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->textarea( array( 'name' => 'cdf_'. $f['id'], 'cols' => $f['extra']['cols'], 'rows' => $f['extra']['rows'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'dropdown' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->drop_down( array( 'name' => 'cdf_'. $f['id'], 'options' => $f['extra'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'checkbox' )
                {
                    $checkbox_html = '';

                    foreach( $f['extra'] as $key => $name )
                    {
                        $checkbox_html .= $this->trellis->skin->checkbox( array( 'name' => 'cdf_'. $f['id'] .'_'. $key, 'title' => $name ) ) .'&nbsp;&nbsp;&nbsp;';
                    }

                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $checkbox_html, 'a' );
                }
                elseif ( $f['type'] == 'radio' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->custom_radio( array( 'name' => 'cdf_'. $f['id'], 'options' => $f['extra'] ) ), 'a' );
                }
            }
        }

        $this->output .= $this->trellis->skin->end_group_table() ."
                        ". $this->trellis->skin->group_sub( '{lang.message}' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->textarea( array( 'name' => 'message', 'cols' => '80', 'rows' => '10', 'width' => '98%', 'height' => '180px' ) ), 'a' ) ."
                        ";

        if ( $this->trellis->cache->data['settings']['ticket']['attachments'] && $this->trellis->user['g_ticket_attach'] && $this->trellis->cache->data['departs'][ $this->trellis->input['did'] ]['allow_attach'] )
        {
            $this->output .= $this->trellis->skin->group_row( $this->trellis->skin->uploadify_js( 'upload_file', array( 'section' => 'manage', 'page' => 'tickets', 'act' => 'doupload', 'type' => 'ticket', 'id' => $this->trellis->input['did'] ), array( 'multi' => true, 'list' => true ) ), 'a' ) ."
                        ";
        }

        $this->output .= $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_ticket}' ) ) ."
                        </div>";

        $this->trellis->skin->end_group( 'a' );

        $validate_fields = array(
                                 'subject'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_subject}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= ( ( $u['id'] ) ? $this->trellis->skin->focus_js('subject') : $this->trellis->skin->focus_js('name') );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_tickets_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Ticket
    #=======================================

    private function edit_ticket()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject', 'priority', 'message', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'et' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        #=============================
        # Do Output
        #=============================

        $priority = ( $this->trellis->input['priority'] ) ? $this->trellis->input['priority'] : $t['priority'];

        $this->trellis->load_functions('drop_downs');
        $this->trellis->load_functions('cdfields');

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=doedit&amp;id={$t['id']}", 'edit_ticket', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_ticket} '. $t['subject'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.subject}', $this->trellis->skin->textfield( array( 'name' => 'subject', 'value' => $t['subject'] ) ), 'a', '20%', '80%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.priority}', "<select name='priority'>". $this->trellis->func->drop_downs->priority_drop( array( 'select' => $priority ) ) ."</select>", 'a' );

        if ( $cfields = $this->trellis->func->cdfields->grab( $t['did'] ) )
        {
            $fdata = $this->trellis->func->cdfields->get_data( $t['id'] );

            foreach( $cfields as $fid => $f )
            {
                $f['extra'] = unserialize( $f['extra'] );

                if ( $f['type'] == 'textfield' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->textfield( array( 'name' => 'cdf_'. $f['id'], 'value' => $fdata[ $f['id'] ], 'length' => $f['extra']['size'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'textarea' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->textarea( array( 'name' => 'cdf_'. $f['id'], 'value' => $fdata[ $f['id'] ], 'cols' => $f['extra']['cols'], 'rows' => $f['extra']['rows'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'dropdown' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->drop_down( array( 'name' => 'cdf_'. $f['id'], 'value' => $fdata[ $f['id'] ], 'options' => $f['extra'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'checkbox' )
                {
                    $checkbox_html = '';

                    foreach( $f['extra'] as $key => $name )
                    {
                        $checkbox_html .= $this->trellis->skin->checkbox( array( 'name' => 'cpf_'. $f['id'] .'_'. $key, 'title' => $name, 'value' => $fdata[ $f['id'] ][ $key ] ) ) .'&nbsp;&nbsp;&nbsp;';
                    }

                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $checkbox_html, 'a' );
                }
                elseif ( $f['type'] == 'radio' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->custom_radio( array( 'name' => 'cpf_'. $f['id'], 'value' => $fdata[ $f['id'] ], 'options' => $f['extra'] ) ), 'a' );
                }
            }
        }

        $this->output .= $this->trellis->skin->end_group_table() ."
                        ". $this->trellis->skin->group_sub( '{lang.message}' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->textarea( array( 'name' => 'message', 'value' => $t['message'], 'cols' => '80', 'rows' => '10', 'width' => '98%', 'height' => '180px' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_ticket}' ) ) ."
                        </div>";

        $this->trellis->skin->end_group( 'a' );

        $validate_fields = array(
                                 'subject'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_subject}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_tickets_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Accept
    #=======================================

    private function do_accept()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject', 'accepted', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'r' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        if ( $t['accepted'] ) $this->trellis->skin->error('ticket_already_accepted');

        #=============================
        # Accept Ticket
        #=============================

        $db_array = array(
                          'status'        => $this->trellis->cache->data['misc']['default_statuses'][2],
                          'accepted'    => 1,
                         );

        $this->trellis->func->tickets->edit( $db_array, $t['id'] );

        $this->trellis->log( array( 'msg' => array( 'ticket_accepted', $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_accepted'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Escalate
    #=======================================

    private function do_escalate()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->cache->data['settings']['ticket']['escalate'] ) $this->trellis->skin->error('no_perm');

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'email', 'subject', 'priority', 'message', 'accepted', 'escalated', 'closed' ), 'g' => array( 'gname', 'lang', 'notify' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ), array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 'g' => 'id', '=', 't' => 'id' ) ) ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'es' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        if ( $t['escalated'] ) $this->trellis->skin->error('ticket_already_escalated');

        if ( ! $this->trellis->cache->data['departs'][ $t['did'] ]['escalate_enable'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Escalate Ticket
        #=============================

        if ( ! $t['uid'] ) $t['uname'] = $t['gname'];

        $this->trellis->func->tickets->escalate( $t['id'], array( 'did' => $t['did'], 'accepted' => $t['accepted'], 'staff' => 1, 'data' => $t, 'clear_assigned' => $this->trellis->input['clrassign'] ) );

        if ( $this->trellis->input['clrassign'] )
        {
            $this->trellis->log( array( 'msg' => array( 'ticket_assigncleared', $t['subject'] ), 'type' => 'ticket', 'level' => 2, 'content_type' => 'ticket', 'content_id' => $t['id'] ) );
        }

        $this->trellis->log( array( 'msg' => array( 'ticket_escalateadd', $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        if ( $assigned = $this->trellis->func->tickets->get_auto_assigned() )
        {
            foreach ( $assigned as $aid => &$aname )
            {
                $this->trellis->log( array( 'msg' => array( 'ticket_assignaddatuo', $aname, $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );
            }
        }

        if ( $moved = $this->trellis->func->tickets->get_auto_moved() )
        {
            $this->trellis->log( array( 'msg' => array( 'ticket_movedauto',  $this->trellis->cache->data['departs'][ $t['did'] ]['name'], $this->trellis->cache->data['departs'][ $moved ]['name'], $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_escalated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Remove Escalate
    #=======================================

    private function do_rmvescalate()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject', 'escalated', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'es' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        if ( ! $t['escalated'] ) $this->trellis->skin->error('ticket_not_escalated');

        #=============================
        # Removae Escalated Status
        #=============================

        $this->trellis->func->tickets->rmvescalate( $t['id'] );

        $this->trellis->log( array( 'msg' => array( 'ticket_escalatermv', $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_rmvescalated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Move
    #=======================================

    private function do_move()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->cache->data['departs'][ $this->trellis->input['did'] ] ) $this->trellis->skin->error('no_depart');

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'email', 'subject', 'priority', 'message' ), 'g' => array( 'gname', 'lang', 'notify' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ), array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 'g' => 'id', '=', 't' => 'id' ) ) ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( $t['did'] == $this->trellis->input['did'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_depart_same'] );

            $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
        }

        if ( ! $this->check_perm( $t['id'], $t['did'], 'mv' ) ) $this->trellis->skin->error('no_perm');

        #=============================
        # Move Ticket
        #=============================

        if ( ! $t['uid'] ) $t['uname'] = $t['gname'];

        $this->trellis->func->tickets->move( $this->trellis->input['did'], $t['id'], $t['did'], $t, $this->trellis->input['clrassign'] );

        if ( $this->trellis->input['clrassign'] )
        {
            $this->trellis->log( array( 'msg' => array( 'ticket_assigncleared', $t['subject'] ), 'type' => 'ticket', 'level' => 2, 'content_type' => 'ticket', 'content_id' => $t['id'] ) );
        }

        $this->trellis->log( array( 'msg' => array( 'ticket_moved', $this->trellis->cache->data['departs'][ $t['did'] ]['name'], $this->trellis->cache->data['departs'][ $this->trellis->input['did'] ]['name'], $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        if ( $assigned = $this->trellis->func->tickets->get_auto_assigned() )
        {
            foreach ( $assigned as $aid => &$aname )
            {
                $this->trellis->log( array( 'msg' => array( 'ticket_assignaddatuo', $aname, $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );
            }
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_moved'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Hold
    #=======================================

    private function do_hold()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'email', 'subject', 'priority', 'message', 'accepted', 'aua', 'onhold', 'closed' ), 'g' => array( 'gname', 'lang', 'notify' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ), array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 'g' => 'id', '=', 't' => 'id' ) ) ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'r' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        if ( $t['onhold'] ) $this->trellis->skin->error('ticket_already_onhold');

        #=============================
        # Hold Ticket
        #=============================

        if ( ! $t['uid'] ) $t['uname'] = $t['gname'];

        $this->trellis->func->tickets->hold( $t['id'], array( 'accepted' => $t['accepted'], 'aua' => $t['aua'], 'data' => $t ) );

        $this->trellis->log( array( 'msg' => array( 'ticket_holdadd', $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_hold'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Remove Hold
    #=======================================

    private function do_rmvhold()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject', 'aua', 'onhold', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'r' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        if ( ! $t['onhold'] ) $this->trellis->skin->error('ticket_not_onhold'); // TODO: Remove obstructive errors like this? Alert redirect instead?

        #=============================
        # Hold Ticket
        #=============================

        $this->trellis->func->tickets->rmvhold( $t['id'], array( 'aua' => $t['aua'] ) );

        $this->trellis->log( array( 'msg' => array( 'ticket_holdrmv', $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_rmvhold'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Close
    #=======================================

    private function do_close()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'email', 'subject', 'priority', 'message', 'closed' ), 'g' => array( 'gname', 'lang', 'notify' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ), array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 'g' => 'id', '=', 't' => 'id' ) ) ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'c' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        #=============================
        # Close Ticket
        #=============================

        if ( ! $t['uid'] ) $t['uname'] = $t['gname'];

        $this->trellis->func->tickets->close( $t['id'], array( 'uid' => $t['uid'], 'allow_reopen' => $this->trellis->input['reopen'], 'data' => $t ) );

        $this->trellis->log( array( 'msg' => array( 'ticket_closed', $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_closed'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Reopen
    #=======================================

    private function do_reopen()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'email', 'subject', 'priority', 'message', 'accepted', 'aua', 'closed' ), 'g' => array( 'gname', 'lang', 'notify' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ), array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 'g' => 'id', '=', 't' => 'id' ) ) ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'ro' ) ) $this->trellis->skin->error('no_perm');

        if ( ! $t['closed'] ) $this->trellis->skin->error('ticket_not_closed');

        #=============================
        # Reopen Ticket
        #=============================

        if ( ! $t['uid'] ) $t['uname'] = $t['gname'];

        $this->trellis->func->tickets->reopen( $t['id'], array( 'uid' => $t['uid'], 'did' => $t['did'], 'aua' => $t['aua'], 'accepted' => $t['accepted'], 'staff' => 1, 'data' => $t ) );

        $this->trellis->log( array( 'msg' => array( 'ticket_reopened', $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_reopened'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Priority
    #=======================================

    private function do_priority()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['pid'] ) $this->edit_ticket('no_priority');

        if ( ! $this->trellis->cache->data['priorities'][ $this->trellis->input['pid'] ] ) $this->trellis->skin->error('no_priority');

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject', 'priority', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( $t['priority'] == $this->trellis->input['pid'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_priority_same'] );

            $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
        }

        if ( ! $this->check_perm( $t['id'], $t['did'], 'et' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        #=============================
        # Change Priority
        #=============================

        $this->trellis->func->tickets->edit( array( 'priority' => $this->trellis->input['pid'] ), $t['id'] );

        $this->trellis->log( array( 'msg' => array( 'ticket_priority', $this->trellis->cache->data['priorities'][ $t['priority'] ]['name'], $this->trellis->cache->data['priorities'][ $this->trellis->input['pid'] ]['name'], $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_priority_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Status
    #=======================================

    private function do_status()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['sid'] ) $this->edit_ticket('no_status');

        if ( ! $this->trellis->cache->data['statuses'][ $this->trellis->input['sid'] ] ) $this->trellis->skin->error('no_status');

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject', 'status', 'accepted', 'aua', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'et' ) || ! $t['accepted'] ) $this->trellis->skin->error('no_perm');

        if ( $t['status'] == $this->trellis->input['sid'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_status_same'] );

            $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
        }

        $switch_to_aua = 0;

        if ( ! $t['aua'] && ! $t['closed'] )
        {
            if ( $this->trellis->cache->data['statuses'][ $t['status'] ]['type'] != $this->trellis->cache->data['statuses'][ $this->trellis->input['sid'] ]['type'] )
            {
                if ( $this->trellis->cache->data['statuses'][ $this->trellis->input['sid'] ]['type'] != 5 ) $this->trellis->skin->error('no_perm');

                $switch_to_aua = 1;
            }
        }
        else
        {
            if ( $this->trellis->cache->data['statuses'][ $t['status'] ]['type'] != $this->trellis->cache->data['statuses'][ $this->trellis->input['sid'] ]['type'] ) $this->trellis->skin->error('no_perm');
        }

        #=============================
        # Change Status
        #=============================

        if ( $switch_to_aua )
        {
            $db_array = array( 'status' => $this->trellis->input['sid'], 'aua' => 1, 'onhold' => 0 );
        }
        else
        {
            $db_array = array( 'status' => $this->trellis->input['sid'] );
        }

        $this->trellis->func->tickets->edit( $db_array, $t['id'] );

        $this->trellis->log( array( 'msg' => array( 'ticket_status', $this->trellis->cache->data['statuses'][ $t['status'] ]['name_staff'], $this->trellis->cache->data['statuses'][ $this->trellis->input['sid'] ]['name_staff'], $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_status_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Untrack
    #=======================================

    private function do_untrack()
    {
        if ( $this->trellis->input['tid'] )
        {
            $this->do_untrack_ticket();
        }
        else
        {
            $this->do_untrack_reply();
        }
    }

    #=======================================
    # @ Do Untrack Ticket
    #=======================================

    private function do_untrack_ticket()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did' ) ), $this->trellis->input['tid'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'v' ) ) $this->trellis->skin->error('no_ticket');

        #=============================
        # Untrack Ticket
        #=============================

        $this->trellis->func->tickets->untrack( $t['id'] );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_untracked'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Untrack Reply
    #=======================================

    private function do_untrack_reply()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $r = $this->trellis->func->tickets->get_single( array( 'select' => array( 'r' => array( 'id', 'tid', 'date' ), 't' => array( 'did' ) ), 'from' => array( 'r' => 'replies' ), 'join' => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 't' => 'id', '=', 'r' => 'tid' ) ) ), 'where' => array( array( 'r' => 'id' ), '=', $this->trellis->input['rid'] ) ) ) ) $this->trellis->skin->error('no_reply');

        if ( ! $this->check_perm( $r['tid'], $r['did'], 'v' ) ) $this->trellis->skin->error('no_reply');

        #=============================
        # Untrack Reply
        #=============================

        $this->trellis->func->tickets->untrack_reply( $r['id'], $t['tid'], $r['date'] );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_reply_untracked'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Track All
    #=======================================

    private function do_track_all()
    {
        #=============================
        # Untrack Ticket
        #=============================

        $this->trellis->func->tickets->track_all();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_tickets_tracked'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Add
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->load_functions('users');

        $validate = true;
        if ( $this->trellis->input['uid'] )
        {
            if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name', 'email' ), $this->trellis->input['uid'] ) )
            {
                $this->trellis->send_message( 'error', $this->trellis->lang['error_no_user'] );
                $validate = false;
            }
        }
        else
        {
            if ( $this->trellis->validate_email( $this->trellis->input['email'] ) )
            {
                if ( ! $u = $this->trellis->func->users->get_single_by_email( array( 'id', 'name', 'email' ), $this->trellis->input['email'] ) )
                {
                    $u = array( 'id' => 0, 'name' => ( ( $this->trellis->input['name'] ) ? $this->trellis->input['name'] : $this->trellis->input['email'] ), 'email' => $this->trellis->input['email'] );
                }
            }
            else
            {
                $this->trellis->send_message( 'error', $this->trellis->lang['error_no_user'] );
                $validate = false;
            }
        }
        if ( ! $this->trellis->input['did'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_depart'] );
            $validate = false;
        }
        if ( ! $validate )
        {
            $this->trellis->skin->preserve_input = 1;
            $this->add_ticket_step_1();
        }

        $perms = &$this->trellis->user['g_depart_perm'];
        if ( $this->trellis->user['id'] != 1 )
        {
            if ( is_array( $this->trellis->user['g_acp_depart_perm'] ) )
            {
                foreach( $this->trellis->user['g_acp_depart_perm'] as $did => $dperm )
                {
                    if ( $dperm['v'] ) $perms[ $did ] = 1;
                }
            }
        }

        if ( ! $perms[ $this->trellis->input['did'] ] && $this->trellis->user['id'] != 1 )
        {
            $this->trellis->skin->error('no_perm');
        }

        if ( ! $this->trellis->input['subject'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_subject'] );
            $validate = false;
        }
        if ( ! $this->trellis->input['priority'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_priority'] );
            $validate = false;
        }
        if ( ! $this->trellis->input['message'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_message'] );
            $validate = false;
        }
        if ( ! $validate )
        {
            $this->trellis->skin->preserve_input = 1;
            $this->add_ticket_step_2();
        }

        if ( ! $this->trellis->cache->data['departs'][ $this->trellis->input['did'] ] ) $this->trellis->skin->error('no_depart');

        #=============================
        # Add Ticket
        #=============================

        $db_array = array(
                          'did'            => $this->trellis->input['did'],
                          'uid'            => $u['id'],
                          'email'        => $u['email'],
                          'subject'        => $this->trellis->input['subject'],
                          'priority'    => $this->trellis->input['priority'],
                          'message'        => $this->trellis->input['message'],
                          'date'        => time(),
                          'last_reply'    => time(),
                          'last_uid'    => $u['id'],
                          'ipadd'        => $this->trellis->input['ip_address'],
                          'status'        => $this->trellis->cache->data['misc']['default_statuses'][2],
                          'accepted'    => 1,
                          'uname'        => $u['name'],
                         );

        if ( ! $u['id'] )
        {
            $db_array['lang'] = $this->trellis->input['lang'];
            $db_array['notify'] = $this->trellis->input['notify'];
        }

        $this->trellis->load_functions('cdfields');

        if( ! $fdata = $this->trellis->func->cdfields->process_input( $this->trellis->input['did'] ) )
        {
            if ( $this->trellis->func->cdfields->required_field ) $this->add_ticket_step_2( 'no_field', $this->trellis->func->cdfields->required_field );
        }

        $ticket_id = $this->trellis->func->tickets->add( $db_array );

        if ( $fdata ) $this->trellis->func->cdfields->set_data( $fdata, $ticket_id, 1, $u['id'] );

        $this->trellis->log( array( 'msg' => array( 'ticket_added', $this->trellis->input['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $ticket_id ) );

        if ( $assigned = $this->trellis->func->tickets->get_auto_assigned() )
        {
            foreach ( $assigned as $aid => &$aname )
            {
                $this->trellis->log( array( 'msg' => array( 'ticket_assignaddatuo', $aname, $this->trellis->input['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $ticket_id ) );
            }
        }

        #=============================
        # Assign Attachments
        #=============================

        if ( is_array( $this->trellis->input['fuploads'] ) )
        {
            $this->trellis->load_functions('attachments');

            if ( $attachments = $this->trellis->func->attachments->get( array( 'select' => array( 'id', 'original_name' ), 'where' => array( 'id', 'in', $this->trellis->input['fuploads'] ) ) ) )
            {
                $to_attach = array();

                foreach ( $attachments as &$a )
                {
                    $to_attach[] = $a['id'];

                    $this->trellis->log( array( 'msg' => array( 'ticket_attach', $a['original_name'], $this->trellis->input['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $ticket_id ) );
                }

                $this->trellis->func->attachments->assign( $to_attach, $ticket_id );
            }
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_added'] );

        if ( $this->check_perm( $ticket_id, $this->trellis->input['did'], 'v' ) )
        {
            $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $ticket_id  ) );
        }
        else
        {
            $this->trellis->skin->redirect( array( 'act' => null ) );
        }
    }

    #=======================================
    # @ Do Edit
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $validate = true;
        if ( ! $this->trellis->input['subject'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_subject'] );
            $validate = false;
        }
        if ( ! $this->trellis->input['priority'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_priority'] );
            $validate = false;
        }
        if ( ! $this->trellis->input['message'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_message'] );
            $validate = false;
        }

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'et' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        $this->trellis->load_functions('cdfields');

        if( ! $fdata = $this->trellis->func->cdfields->process_input( $t['did'] ) )
        {
            if ( $this->trellis->func->cdfields->required_field )
            {
                $this->trellis->send_message( 'error', $this->trellis->lang['error_no_field'].' '. $this->trellis->func->cdfields->required_field );
                $validate = false;
            }
        }

        if ( ! $validate )
        {
            $this->trellis->skin->preserve_input = 1;
            $this->edit_ticket();
        }

        #=============================
        # Edit Ticket
        #=============================

        $db_array = array(
                          'subject'        => $this->trellis->input['subject'],
                          'priority'    => intval( $this->trellis->input['priority'] ),
                          'message'        => $this->trellis->input['message'],
                         );

        $this->trellis->func->tickets->edit( $db_array, $t['id'] );

        if ( $fdata ) $this->trellis->func->cdfields->set_data( $fdata, $t['id'] );

        $this->trellis->log( array( 'msg' => array( 'ticket_edited', $this->trellis->input['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_ticket_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ) );
    }

    #=======================================
    # @ Do Delete
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'uid', 'subject', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'dt' ) ) $this->trellis->skin->error('no_perm');

        #=============================
        # DELETE Ticket
        #=============================

        $this->trellis->func->tickets->delete( $t['id'], array( 'did' => $t['did'], 'uid' => $t['uid'], 'closed' => $t['closed'] ) );

        $this->trellis->log( array( 'msg' => array( 'ticket_deleted', $t['subject'] ), 'type' => 'ticket', 'level' => 2, 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_ticket_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Add Reply
    #=======================================

    private function do_add_reply()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['message'] ) $this->view_ticket( array( 'reply_error' => 'no_message' ) );

        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'uid', 'email', 'subject', 'priority', 'message', 'accepted', 'onhold', 'closed' ), 'g' => array( 'gname', 'lang', 'notify' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ), array( 'from' => array( 'g' => 'tickets_guests' ), 'where' => array( 'g' => 'id', '=', 't' => 'id' ) ) ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_ticket');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'r' ) ) $this->trellis->skin->error('no_perm');

        if ( $t['closed'] ) $this->trellis->skin->error('ticket_closed');

        #=============================
        # Add Reply
        #=============================

        ( $this->trellis->input['keep_hold'] == 2 ) ? $keep_hold = 1 : $keep_hold = 0;

        // Auto Assign
        if ( $this->trellis->input['assign_to_me'] && ( $this->check_perm( $t['id'], $t['did'], 'aa' ) || $this->check_perm( $t['id'], $t['did'], 'as' ) ) ) $this->trellis->func->tickets->add_assignment( $this->trellis->user['id'], $t['id'], 0, 0, 1 );

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['ticket']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'tid'                    => $t['id'],
                          'uid'                    => $this->trellis->user['id'],
                          'message'                => $this->trellis->input['message'],
                          'signature'            => $this->trellis->input['signature'],
                          'staff'                => 1,
                          'html'                => $html,
                          'secret'                => $this->trellis->input['secret'],
                          'date'                => time(),
                          'ipadd'                => $this->trellis->input['ip_address'],
                          'mask'                => $t['mask'], # TODO: move extra data to $params['data']
                          'did'                    => $t['did'],
                          'tuid'                => $t['uid'],
                          'tuname'                => $t['uname'],
                          'subject'                => $t['subject'],
                          'priority'            => $t['priority'],
                          'message_original'    => $t['message'],
                         );

        if ( ! $t['uid'] )
        {
            $db_array['tuname'] = $t['gname'];
            $db_array['email'] = $t['email'];
            $db_array['lang'] = $t['lang'];
            $db_array['notify'] = $t['notify'];
        }

        $reply_id = $this->trellis->func->tickets->add_reply( $db_array, $t['id'], array( 'accepted' => $t['accepted'], 'onhold' => $t['onhold'], 'keep_hold' => $keep_hold ) );

        $this->trellis->log( array( 'msg' => array( 'reply_added', $t['subject'] ), 'type' => 'ticket', 'content_type' => 'reply', 'content_id' => $reply_id ) );

        #=============================
        # Assign Attachments
        #=============================

        if ( is_array( $this->trellis->input['fuploads'] ) )
        {
            $this->trellis->load_functions('attachments');

            if ( $attachments = $this->trellis->func->attachments->get( array( 'select' => array( 'id', 'original_name' ), 'where' => array( 'id', 'in', $this->trellis->input['fuploads'] ) ) ) )
            {
                $to_attach = array();

                foreach ( $attachments as &$a )
                {
                    $to_attach[] = $a['id'];

                    $this->trellis->log( array( 'msg' => array( 'reply_attach', $a['original_name'], $t['subject'] ), 'type' => 'ticket', 'content_type' => 'reply', 'content_id' => $reply_id ) );
                }

                $this->trellis->func->attachments->assign( $to_attach, $reply_id );
            }
        }

        #=============================
        # Redirect
        #=============================

        if ( $this->trellis->input['ajax'] )
        {
            $this->trellis->skin->ajax_output( '1' );
        }
        else
        {
            $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $t['id'] ), '#r'. $reply_id );
        }
    }

    #=======================================
    # @ Do Edit Reply
    #=======================================

    private function do_edit_reply()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['message'] ) $this->trellis->skin->ajax_output( '0' );

        $r = $this->trellis->func->tickets->get_single( array(
                                                        'select'    => array(
                                                                                'r' => 'all',
                                                                                't' => array( 'did', 'subject', 'closed' ),
                                                                                'u' => array( array( 'signature' => 'usignature' ), 'sig_html' ),
                                                                                ),
                                                        'from'        => array( 'r' => 'replies' ),
                                                        'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ), array( 'from' => array( 'u' => 'users' ), 'where' => array( 'r' => 'uid', '=', 'u' => 'id' ) ) ),
                                                        'where'        => array( array( 'r' => 'id' ), '=', $this->trellis->input['id'] ),
                                                 )        );

        if ( ! $r ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->check_perm( $r['tid'], $r['did'], 'er' ) && ( $r['uid'] != $this->trellis->user['id'] || ! $this->trellis->user['g_reply_edit'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $r['closed'] ) $this->trellis->skin->ajax_output( '0' );

        #=============================
        # Edit Reply
        #=============================

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['ticket']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'message'        => $this->trellis->input['message'],
                          'html'        => $html,
                         );

        $this->trellis->func->tickets->edit_reply( $db_array, $r['id'] );

        $this->trellis->log( array( 'msg' => array( 'reply_edited', $r['subject'] ), 'type' => 'ticket', 'content_type' => 'reply', 'content_id' => $r['id'] ) );

        #=============================
        # Do Output
        #=============================

        $routput_params = array( 'linkify' => 1 );

        if ( $this->trellis->input['html'] )
        {
            $routput_params['html'] = 1;
        }
        else
        {
            $routput_params['paragraphs'] = 1;
            $routput_params['nl2br'] = 1;
        }

        $rmessage = $this->trellis->prepare_output( $this->trellis->input['message'], $routput_params );

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

        if ( $r['signature'] ) $rmessage .= $this->trellis->prepare_output( $r['usignature'], $soutput_params );

        $this->trellis->skin->ajax_output( $rmessage );
    }

    #=======================================
    # @ Do Delete Reply
    #=======================================

    private function do_delete_reply()
    {
        #=============================
        # Security Checks
        #=============================

        $r = $this->trellis->func->tickets->get_single( array(
                                                        'select'    => array(
                                                                                'r' => array( 'id', 'tid', 'uid', 'staff', 'secret', 'date' ),
                                                                                't' => array( 'did', 'subject', 'last_reply', 'last_reply_staff', 'closed' ),
                                                                                ),
                                                        'from'        => array( 'r' => 'replies' ),
                                                        'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'where'        => array( array( 'r' => 'id' ), '=', $this->trellis->input['id'] ),
                                                 )        );

        if ( ! $r ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->check_perm( $r['tid'], $r['did'], 'dr' ) && ( $r['uid'] != $this->trellis->user['id'] || ! $this->trellis->user['g_reply_delete'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $r['closed'] ) $this->trellis->skin->ajax_output( '0' );

        #=============================
        # DELETE Reply
        #=============================

        $this->trellis->func->tickets->delete_reply( $r['id'], array( 'tid' => $r['tid'], 'secret' => $r['secret'], 'date' => $r['date'], 'last_reply' => $r['last_reply'], 'last_reply_staff' => $r['last_reply_staff'], 'staff' => $r['staff'] ) );

        $this->trellis->log( array( 'msg' => array( 'reply_deleted', $r['subject'] ), 'type' => 'ticket', 'level' => 2, 'content_type' => 'ticket', 'content_id' => $r['tid'] ) );

        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->ajax_output( '1' );
    }

    #=======================================
    # @ Generate URL
    #=======================================

    private function generate_url($params=array())
    {
        $url = '<! TD_URL !>/admin.php?section=manage&amp;page=tickets';

        if ( ! isset( $params['sort'] ) ) $params['sort'] = $this->trellis->input['sort'];
        if ( ! isset( $params['order'] ) ) $params['order'] = $this->trellis->input['order'];
        if ( ! isset( $params['search'] ) ) $params['search'] = $this->trellis->input['search'];
        if ( ! isset( $params['field'] ) ) $params['field'] = $this->trellis->input['field'];
        if ( ! isset( $params['go_all'] ) ) $params['go_all'] = $this->trellis->input['go_all'];
        if ( ! isset( $params['noguest'] ) ) $params['noguest'] = $this->trellis->input['noguest'];
        if ( ! isset( $params['assigned'] ) ) $params['assigned'] = $this->trellis->input['assigned'];
        if ( ! isset( $params['unassigned'] ) ) $params['unassigned'] = $this->trellis->input['unassigned'];
        if ( ! isset( $params['escalated'] ) ) $params['escalated'] = $this->trellis->input['escalated'];
        if ( ! isset( $params['fstatus'] ) ) $params['fstatus'] = $this->trellis->input['fstatus'];
        if ( ! isset( $params['fdepart'] ) ) $params['fdepart'] = $this->trellis->input['fdepart'];
        if ( ! isset( $params['fpriority'] ) ) $params['fpriority'] = $this->trellis->input['fpriority'];
        if ( ! isset( $params['fflag'] ) ) $params['fflag'] = $this->trellis->input['fflag'];
        if ( ! isset( $params['cf'] ) ) $params['cf'] = $this->trellis->input['cf'];
        if ( ! isset( $params['st'] ) ) $params['st'] = $this->trellis->input['st'];

        if ( $params['sort'] ) $url .= '&amp;sort='. $params['sort'];

        if ( $params['order'] ) $url .= '&amp;order='. $params['order'];

        if ( $params['field'] == 'email' || $params['field'] == 'uemail' ) $params['search'] = urlencode( $params['search'] );
        if ( $params['search'] ) $url .= '&amp;search='. $params['search'];

        if ( $params['field'] ) $url .= '&amp;field='. $params['field'];

        if ( $params['go_all'] ) $url .= '&amp;go_all=1';

        if ( $params['noguest'] ) $url .= '&amp;noguest='. $params['noguest'];

        if ( $params['assigned'] ) $url .= '&amp;assigned='. $params['assigned'];

        if ( $params['unassigned'] ) $url .= '&amp;unassigned='. $params['unassigned'];

        if ( $params['escalated'] ) $url .= '&amp;escalated='. $params['escalated'];

        if ( is_array( $params['fstatus'] ) )
        {
            foreach( $params['fstatus'] as $sid )
            {
                $url .= '&amp;fstatus'. urlencode( '[]' ) .'='. $sid;
            }
        }

        if ( is_array( $params['fdepart'] ) )
        {
            foreach( $params['fdepart'] as $did )
            {
                $url .= '&amp;fdepart'. urlencode( '[]' ) .'='. $did;
            }
        }

        if ( is_array( $params['fpriority'] ) )
        {
            foreach( $params['fpriority'] as $pid )
            {
                $url .= '&amp;fpriority'. urlencode( '[]' ) .'='. $pid;
            }
        }

        if ( is_array( $params['fflag'] ) )
        {
            foreach( $params['fflag'] as $fid )
            {
                $url .= '&amp;fflag'. urlencode( '[]' ) .'='. $fid;
            }
        }

        if ( $params['cf'] ) $url .= '&amp;cf='. $params['cf'];

        if ( $params['st'] ) $url .= '&amp;st='. $params['st'];

        return $url;
    }

    #=======================================
    # @ Check Permission
    #=======================================

    private function check_perm($tid, $did, $type)
    {
        if ( $this->trellis->user['id'] == 1 ) return true;

        if ( ! $this->trellis->user['g_acp_depart_perm'][ $did ]['v'] )
        {
            if ( ! $this->assigned_override[ $tid ] )
            {
                if ( ! $a = $this->trellis->db->get_single( array( 'select' => array( 'id' ), 'from' => 'assign_map', 'where' => array( array( 'tid', '=', $tid ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ) ) ) ) return false;

                $this->assigned_override[ $tid ] = $a['id'];
            }
        }

        if ( $type == 'v' ) return true;

        if ( ! $this->trellis->user['g_acp_depart_perm'][ $did ][ $type ] ) return false;

        return true;
    }

    #=======================================
    # @ AJAX Do Add Assignment
    #=======================================

    private function ajax_add_assign()
    {
        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 't' => array( 'id', 'mask', 'did', 'subject', 'priority', 'message' ), 'u' => array( array( 'name' => 'uname' ) ) ), 'from' => array( 't' => 'tickets' ), 'join' => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 't' => 'uid' ) ) ) ), $this->trellis->input['tid'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->check_perm( $t['id'], $t['did'], 'aa' ) && ( $this->trellis->input['uid'] != $this->trellis->user['id'] || ! $this->check_perm( $t['id'], $t['did'], 'as' ) ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_assign_outside'] )
        {
            $perms = unserialize( $this->trellis->cache->data['staff'][ $this->trellis->input['uid'] ]['g_acp_depart_perm'] );

            if ( ! $perms[ $t['did'] ]['v'] ) $this->trellis->skin->ajax_output( '00' ); # TODO: Does this permission check work?
        }

        if ( $uname = $this->trellis->func->tickets->add_assignment( $this->trellis->input['uid'], $t['id'], 0 , 1, 0, $t ) )
        {
            $this->trellis->log( array( 'msg' => array( 'ticket_assignadd', $uname, $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

            $this->trellis->skin->ajax_output( $uname );
        }
        else
        {
            $this->trellis->skin->ajax_output( '0' );
        }
    }

    #=======================================
    # @ AJAX Do Add Flag
    #=======================================

    private function ajax_add_flag()
    {
        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject' ) ), $this->trellis->input['tid'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->check_perm( $t['id'], $t['did'], 'v' ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $fid = $this->trellis->func->tickets->add_flag( $this->trellis->input['fid'], $t['id'] ) )
        {
            $this->trellis->log( array( 'msg' => array( 'ticket_flagadd', $this->trellis->cache->data['flags'][ $this->trellis->input['fid'] ]['name'], $t['subject'] ), 'type' => 'ticket', 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

            $this->trellis->skin->ajax_output( '1' );
        }
        else
        {
            $this->trellis->skin->ajax_output( '0' );
        }
    }

    #=======================================
    # @ AJAX Do Delete Assignment
    #=======================================

    private function ajax_delete_assign()
    {
        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject' ) ), $this->trellis->input['tid'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->check_perm( $t['id'], $t['did'], 'aa' ) && ( $this->trellis->input['uid'] != $this->trellis->user['id'] || ! $this->check_perm( $t['id'], $t['did'], 'as' ) ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $this->trellis->func->tickets->delete_assignment( $this->trellis->input['uid'], $t['id'] ) )
        {
            $this->trellis->db->construct( array( 'select' => array( 'name' ), 'from' => 'users', 'where' => array( 'id', '=', $this->trellis->input['uid'] ), 'limit'    => array( 0, 1 ) ) );
            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() ) $u = $this->trellis->db->fetch_row();

            $this->trellis->log( array( 'msg' => array( 'ticket_assignrmv', $u['name'], $t['subject'] ), 'type' => 'ticket', 'level' => 2, 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

            $this->trellis->skin->ajax_output( '1' );
        }
        else
        {
            $this->trellis->skin->ajax_output( '0' );
        }
    }

    #=======================================
    # @ AJAX Do Delete Flag
    #=======================================

    private function ajax_delete_flag()
    {
        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'subject' ) ), $this->trellis->input['tid'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->check_perm( $t['id'], $t['did'], 'v' ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $this->trellis->func->tickets->delete_flag( $this->trellis->input['fid'], $t['id'] ) )
        {
            $this->trellis->log( array( 'msg' => array( 'ticket_flagrmv', $this->trellis->cache->data['flags'][ $this->trellis->input['fid'] ]['name'], $t['subject'] ), 'type' => 'ticket', 'level' => 2, 'content_type' => 'ticket', 'content_id' => $t['id'] ) );

            $this->trellis->skin->ajax_output( '1' );
        }
        else
        {
            $this->trellis->skin->ajax_output( '0' );
        }
    }

    #=======================================
    # @ AJAX Do Save Notes
    #=======================================

    private function ajax_save_notes()
    {
        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->check_perm( $t['id'], $t['did'], 'v' ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $this->trellis->func->tickets->edit( array( 'notes' => $this->trellis->input['notes'] ), $t['id'] ) )
        {
            $this->trellis->skin->ajax_output( '1' );
        }
        else
        {
            $this->trellis->skin->ajax_output( '0' );
        }
    }

    #=======================================
    # @ AJAX Do Save Defaults
    #=======================================

    private function ajax_save_defaults()
    {
        parse_str( str_replace( '&amp;', '&', $this->trellis->input['defaults'] ), $defaults );

        if ( $this->trellis->input['type'] == 'status' )
        {
            $db_array = array( 'dfilters_status' => serialize( $defaults['fstatus'] ) );
        }
        elseif ( $this->trellis->input['type'] == 'depart' )
        {
            $db_array = array( 'dfilters_depart' => serialize( $defaults['fdepart'] ) );
        }
        elseif ( $this->trellis->input['type'] == 'priority' )
        {
            $db_array = array( 'dfilters_priority' => serialize( $defaults['fpriority'] ) );
        }
        elseif ( $this->trellis->input['type'] == 'flag' )
        {
            $db_array = array( 'dfilters_flag' => serialize( $defaults['fflag'] ) );
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'users_staff',
                                                   'set'    => $db_array,
                                                   'where'    => array( 'uid', '=', $this->trellis->user['id'] ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->skin->ajax_output( $this->trellis->db->get_affected_rows() );
    }

    #=======================================
    # @ AJAX Get Status
    #=======================================

    private function ajax_get_status()
    {
        if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'status', 'aua', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $t['aua'] && ! $t['closed'] )
        {
            $type = array( $this->trellis->cache->data['statuses'][ $t['status'] ]['type'], 5 );
        }
        else
        {
            $type = $this->trellis->cache->data['statuses'][ $t['status'] ]['type'];
        }

        $this->trellis->load_functions('drop_downs');

        $this->trellis->skin->ajax_output( $this->trellis->func->drop_downs->status_drop( array( 'type' => $type ) ) );
    }

    #=======================================
    # @ AJAX Get Reply Template
    #=======================================

    private function ajax_get_reply_template()
    {
        $this->trellis->load_functions('rtemplates');

        if ( ! $rt = $this->trellis->func->rtemplates->get_single_by_id( array( 'content_html', 'content_plaintext' ), $this->trellis->input['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $this->trellis->input['html'] )
        {
            $message = $this->trellis->prepare_output( $rt['content_html'], array( 'html' => 1 ) );
        }
        else
        {
            $message = $rt['content_plaintext'];
        }

        $this->trellis->skin->ajax_output( $message );
    }

    #=======================================
    # @ AJAX Get Reply
    #=======================================

    private function ajax_get_reply()
    {
        $r = $this->trellis->func->tickets->get_single( array(
                                                        'select'    => array(
                                                                                'r' => 'all',
                                                                                't' => array( 'did', 'closed' ),
                                                                                ),
                                                        'from'        => array( 'r' => 'replies' ),
                                                        'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'where'        => array( array( 'r' => 'id' ), '=', $this->trellis->input['id'] ),
                                                 )        );

        if ( ! $r ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->check_perm( $r['tid'], $r['did'], 'er' ) && ( $r['uid'] != $this->trellis->user['id'] || ! $this->trellis->user['g_reply_edit'] ) ) $this->trellis->skin->ajax_output( '0' );

        if ( $r['closed'] ) $this->trellis->skin->ajax_output( '0' );

        $this->trellis->skin->ajax_output( $this->trellis->prepare_output( $r['message'], array( 'html' => $r['html'] ) ) );
    }

    #=======================================
    # @ Do Upload
    #=======================================

    private function do_upload()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->cache->data['settings']['ticket']['attachments'] || ! $this->trellis->user['g_ticket_attach'] ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_perm'] ) ) );

        if ( ! $this->trellis->input['type'] ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_perm'] ) ) );

        if ( $this->trellis->input['type'] == 'ticket' )
        {
            if ( ! $this->trellis->cache->data['departs'][ $this->trellis->input['id'] ]['allow_attach'] ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_perm'] ) ) );
        }
        elseif ( $this->trellis->input['type'] == 'reply' )
        {
            if ( ! $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did', 'closed' ) ), $this->trellis->input['id'] ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_perm'] ) ) );

            if ( ! $this->check_perm( $t['id'], $t['did'], 'r' ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_perm'] ) ) );

            if ( $t['closed'] ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_perm'] ) ) );

            if ( ! $this->trellis->cache->data['departs'][ $t['did'] ]['allow_attach'] ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_perm'] ) ) );
        }

        #=============================
        # Upload File
        #=============================

        $this->trellis->load_functions('attachments');

        $file = $this->trellis->func->attachments->upload( $_FILES['Filedata'], array( 'content_type' => $this->trellis->input['type'] ), 'ajax' );

        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->ajax_output( json_encode( array( 'success' => true, 'successmsg' => $this->trellis->lang['upload_success'], 'id' => $file['id'], 'name' => $file['name'] ) ) );

        exit();
    }

    #=======================================
    # @ Do Delete Upload
    #=======================================

    private function do_delete_upload()
    {
        $this->trellis->load_functions('attachments');

        #=============================
        # Security Checks
        #=============================

        if ( ! $u = $this->trellis->func->attachments->get_single_by_id( array( 'id', 'content_id', 'uid' ), $this->trellis->input['id'] ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_delete'] ) ) );

        if ( $u['content_id'] || ( $u['uid'] != $this->trellis->user['id'] ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_delete'] ) ) );

        #=============================
        # DELETE Upload
        #=============================

        if ( ! $this->trellis->func->attachments->delete( $u['id'] ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true ) ) );

        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->ajax_output( json_encode( array( 'success' => true ) ) );

        exit();
    }

    #=======================================
    # @ Download Attachment
    #=======================================

    private function do_attachment()
    {
        $this->trellis->load_functions('attachments');

        #=============================
        # Security Checks
        #=============================

        if ( ! $a = $this->trellis->func->attachments->get_single_by_id( array( 'id', 'content_type', 'content_id' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_attachment');

        if ( $a['content_type'] == 'ticket' )
        {
            $t = $this->trellis->func->tickets->get_single_by_id( array( 'select' => array( 'id', 'did' ) ), $a['content_id'] );
        }
        else
        {
            $t = $this->trellis->func->tickets->get_single( array(
                                                                    'select'    => array( 't' => array( 'id', 'did' ) ),
                                                                    'from'        => array( 'r' => 'replies' ),
                                                                    'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ),
                                                                    'where'        => array( array( 'r' => 'id' ), '=', $a['content_id'] ),
                                                             )        );
        }

        if ( ! $t ) $this->trellis->skin->error('no_attachment');

        if ( ! $this->check_perm( $t['id'], $t['did'], 'v' ) ) $this->trellis->skin->error('no_perm');

        #=============================
        # Download Attachment
        #=============================

        if ( ! $this->trellis->func->attachments->download( $a['id'] ) ) $this->trellis->skin->error('no_attachment');

        $this->trellis->shut_down();

        exit();
    }

}

?>