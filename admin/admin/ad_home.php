<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_home {

    #=======================================
    # @ Auto Run
    # Function that is run automatically
    # when the file is required.
    #=======================================

    public function auto_run()
    {
        $this->trellis->load_functions('admin');
        $this->trellis->load_functions('tickets');
        $this->trellis->load_lang('home');

        switch( $this->trellis->input['act'] )
        {
            case 'donotes':
                $this->ajax_save_notes();
            break;

            default:
                $this->show_home();
            break;
        }
    }

    #=======================================
    # @ Show Home
    #=======================================

    public function show_home()
    {
        #=============================
        # Security Check
        #=============================

        if ( file_exists( TD_PATH .'install/install.lock' ) )
        {
            $this->trellis->send_message( 'alert', $this->trellis->lang['alert_install_locked'] );
        }
        elseif ( is_dir( TD_PATH .'install' ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['alert_install_dir_exists'] );
        }

        #=============================
        # Table Columns
        #=============================

        $columns = unserialize( $this->trellis->user['columns_tm'] );

        if ( empty( $columns ) )
        {
            $columns = array( 'id' => '3%', 'mask' => '6%', 'subject' => '30%', 'priority' => '13%', 'department' => '18%', 'reply' => '17%', 'status' => '13%' );
        }

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
        # Sort
        #=============================

        foreach( $columns as $name => $width )
        {
            if ( $name == $this->trellis->input['sort'] )
            {
                if ( $this->trellis->input['order'] == 'desc' )
                {
                    $link_order = 'asc';
                    $img_order = '&nbsp;<img src="<! IMG_DIR !>/arrow_up.gif" alt="{lang.up}" />';
                    $sql_order = 'desc';
                }
                else
                {
                    $link_order = 'desc';
                    $img_order = '&nbsp;<img src="<! IMG_DIR !>/arrow_down.gif" alt="{lang.down}" />';
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

        $filters = array();
        $sql_where = array();

        $sql_select['a'] = array( array( 'uid' => 'auid' ) ); // Get Assigned

        if ( $this->trellis->input['assigned'] )
        {
            $sql_join[] = array( 'from' => array( 'a' => 'assign_map' ), 'where' => array( array( 't' => 'id', '=', 'a' => 'tid' ), array( $this->trellis->input['assigned'], '=', 'a' => 'uid', 'and' ) ) );

            $filters[] = array( array( 'a' => 'uid' ), '=', $this->trellis->input['assigned'] );
        }
        else
        {
            $sql_join[] = array( 'from' => array( 'a' => 'assign_map' ), 'where' => array( array( 't' => 'id', '=', 'a' => 'tid' ), array( $this->trellis->user['id'], '=', 'a' => 'uid', 'and' ) ) );
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

        $tickets = $this->trellis->func->tickets->get( array(
                                                       'select'    => $sql_select,
                                                       'from'    => array( 't' => 'tickets' ),
                                                       'join'    => $sql_join,
                                                       'where'    => $sql_where,
                                                       'order'    => array( $sql_sort_field => array( $sql_sort_table => $sql_order ) ),
                                                       'limit'    => array( $this->trellis->input['st'], 8 ),
                                                )       );

        if ( count( $tickets ) && $this->trellis->input['field'] == 'id' )
        {
            $this->trellis->input['id'] = $tickets[0]['id'];

            $this->show_ticket();
        }

        if ( count( $tickets ) == 1 && $this->trellis->input['field'] == 'mask' )
        {
            $this->trellis->input['id'] = $tickets[0]['id'];

            $this->show_ticket();
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
                if ( $t['gname'] ) $t['uname'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;search=". urlencode( $t['email'] ) ."&amp;field=email'>{$t['gname']} ({lang.guest})</a>";
                if ( $t['last_uname'] ) $t['last_uname'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$t['last_uid']}'>{$t['last_uname']}</a>";
                if ( isset( $t['last_uid'] ) && ! $t['last_uid'] ) $t['last_uname'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;search=". urlencode( $t['email'] ) ."&amp;field=email'>{$t['gname']} ({lang.guest})</a>";

                if ( $t['email'] ) $t['email'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;search=". urlencode( $t['email'] ) ."&amp;field=email'>{$t['email']}</a>";
                if ( $t['uemail'] ) $t['uemail'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;search=". urlencode( $t['uemail'] ) ."&amp;field=uemail'>{$t['uemail']}</a>";

                if ( $t['dname'] ) $t['dname'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fdepart[]={$t['did']}'>{$t['dname']}</a>";

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

                        $ticket_rows .= "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fpriority[]={$t['priority']}'>";
                    }

                    if ( $name == 'abbr_staff' || $name == 'name_staff' ) $ticket_rows .= "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fstatus[]={$t['status']}'>";

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

                    if ( $name == 'id' || $name == 'subject' ) $ticket_rows .= "</a>";

                    $ticket_rows .= "</td>";
                }

                $ticket_rows .= "</tr>";
            }
        }

        #=============================
        # Grab Logs
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array(
                                                                        'l' => 'all',
                                                                        'u' => array( array( 'name' => 'uname' ) ),
                                                                        ),
                                                   'from'    => array( 'l' => 'logs' ),
                                                   'join'    => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'l' => 'uid', '=', 'u' => 'id' ) ) ),
                                                   'where'    => array( array( 'l' => 'admin' ), '=', 1 ),
                                                   'order'    => array( 'date' => array( 'l' => 'desc' ), 'id' => array( 'l' => 'desc' ) ),
                                                   'limit'    => array( 0, 5 ),
                                            )       );

        $this->trellis->db->execute();

        $log_rows = "";

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

            $known_types = array(
                'kb'        => '{lang.log_type_kb}',
                'news'        => '{lang.log_type_news}',
                'other'        => '{lang.log_type_other}',
                'security'    => '{lang.log_type_security}',
                'settings'    => '{lang.log_type_settings}',
                'ticket'    => '{lang.log_type_ticket}',
                'user'        => '{lang.log_type_user}',
            );

            $type = ( $known_types[ $l['type'] ] ) ? $known_types[ $l['type'] ] : $l['type'];

            $log_rows .= "<tr>
                                <td class='bluecell-light' width='44%'>{$fontcolor_start}{$l['action']}{$fontcolor_end}</td>
                                <td class='bluecell-light' width='11%'>{$fontcolor_start}{$type}{$fontcolor_end}</td>
                                <td class='bluecell-dark' width='15%' style='font-weight:normal'>{$fontcolor_start}{$l['date']}{$fontcolor_end}</td>
                                <td class='bluecell-light' width='15%'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$l['uid']}'>{$fontcolor_start}{$l['uname']}{$fontcolor_end}</a></td>
                                <td class='bluecell-light' width='15%' style='font-weight:normal'>{$fontcolor_start}{$l['ipadd']}{$fontcolor_end}</td>
                            </tr>";
        }

        #=============================
        # Version Check
        #=============================

        $version_check = $this->trellis->func->admin->version_check();

        if ( $version_check == 1 )
        {
            $version_img_url = '<! IMG_DIR !>/vc_update_available.jpg';
        }
        elseif ( $version_check == 2 )
        {
            $version_img_url = '<! IMG_DIR !>/vc_up_to_date.jpg';
        }
        else
        {
            $version_img_url = '<! IMG_DIR !>/vc_unable_to_check.jpg';
        }

        #=============================
        # Sidebar Menu
        #=============================

        $mysql_version = mysql_get_server_info();

        if ( strpos( $mysql_version, '-' ) )
        {
            $mysql_version = substr( $mysql_version, 0, strpos( $mysql_version, '-' ) );
        }

        if ( $this->trellis->cache->data['misc']['vcheck_time'] )
        {
            $vcheck_date = $this->trellis->td_timestamp( array( 'time' => $this->trellis->cache->data['misc']['vcheck_time'], 'format' => 'date' ) );
        }
        else
        {
            $vcheck_date = '{lang.check_now}';
        }

        $system_status_html = "<table width='100%' border='0' cellspacing='0' cellpadding='0' class='blockstatus'>
                    <tr>
                        <td colspan='2' class='statusbadge'><a href='http://www.accord5.com/trellis/latest' target='_blank'><img src='{$version_img_url}' alt='{lang.version_check}' /></a></td>
                    </tr>
                    <tr>
                        <td class='statusleft'><strong>{lang.version_check}</strong></td>
                        <td class='statusright'><a href='<! TD_URL !>/admin.php?section=admin&amp;vcheck=1' title='{lang.check_now}'>". $vcheck_date ."</a></td>
                    </tr>
                    <tr>
                        <td class='statusleft'><strong>{lang.product_version}</strong></td>
                        <td class='statusright'>{$this->trellis->version_short} ({$this->trellis->version_number})</td>
                    </tr>
                        <td class='statusleft'><strong>{lang.php_version}</strong></td>
                        <td class='statusright'>". phpversion() ."</td>

                    </tr>
                    <tr>
                        <td class='statusleft'><strong>{lang.mysql_version}</strong></td>
                        <td class='statusright'>". $mysql_version ."</td>
                    </tr>
                </table>";

        $this->trellis->skin->add_sidebar_block( '{lang.system_status}', $system_status_html );

        $notes_items = array(
                            "<textarea id='notes' name='notes' cols='4' rows='8' style='width:99%' class='notesbox'>{$this->trellis->cache->data['misc']['acp_notes']}</textarea>",
                            "<input name='save_notes' id='save_notes' type='submit' value='{lang.button_save_notes}' class='buttonmini' /> <span id='save_notes_status' class='ajax_update_button'>{lang.saved}</span>",
                            );

        $this->trellis->skin->add_sidebar_list( '{lang.admin_notepad}', $notes_items );

        #=============================
        # Do Output
        #=============================

        $acp_content = "<div id='ticketroll'>
                <div class='orangebox'>{lang.common_acp_sections}</div>
                <div class='rolldefault'>
                    <table width='100%' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td class='orangecell-light' width='25%'><a href='<! TD_URL !>/admin.php?section=manage'><img src='<! IMG_DIR !>/icons/buoy.png' width='16' height='16' alt='{lang.tickets}' />{lang.tickets}</a></td>
                        <td class='orangecell-dark' width='25%'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=news'><img src='<! IMG_DIR !>/icons/balloon.png' width='16' height='16' alt='{lang.news}' />{lang.news}</a></td>
                        <td class='orangecell-light' width='25%'><a href='<! TD_URL !>/admin.php?section=look'><img src='<! IMG_DIR !>/icons/color.png' width='16' height='16' alt='{lang.skins}' />{lang.skins}</a></td>
                        <td class='orangecell-dark' width='25%'><a href='<! TD_URL !>/admin.php?section=tools'><img src='<! IMG_DIR !>/icons/settings.png' width='16' height='16' alt='{lang.settings}' />{lang.settings}</a></td>
                    </tr>
                    <tr>
                        <td class='orangecell-light' width='25%'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users'><img src='<! IMG_DIR !>/icons/addressbook.png' width='16' height='16' alt='{lang.users}' />{lang.users}</a></td>
                        <td class='orangecell-dark' width='25%'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles'><img src='<! IMG_DIR !>/icons/book.png' width='16' height='16' alt='{lang.kb_articles}' />{lang.kb_articles}</a></td>
                        <td class='orangecell-light' width='25%'><a href='<! TD_URL !>/admin.php?section=look&amp;page=langs'><img src='<! IMG_DIR !>/icons/alphabet.png' width='16' height='16' alt='{lang.languages}' />{lang.languages}</a></td>
                        <td class='orangecell-dark' width='25%'><a href='http://docs.accord5.com/' target='_blank'><img src='<! IMG_DIR !>/icons/question.png' width='16' height='16' alt='{lang.documentation}' />{lang.documentation}</a></td>
                    </tr>
                    </table>
                </div>
                <div class='groupbox'><a href='<! TD_URL !>/admin.php?section=manage'>{lang.recent_tickets}</a></div>
                <div class='rolldefault'>
                    <table width='100%' cellpadding='0' cellspacing='0'>
                    ". $ticket_rows ."
                    </table>
                </div>

                <div class='groupbox'><a href='<! TD_URL !>/admin.php?section=tools&amp;page=logs'>{lang.recent_acp_actions}</a></div>
                <div class='rolldefault'>
                    <table width='100%' cellpadding='0' cellspacing='0'>
                    ". $log_rows ."
                    </table>
                </div>
                </div>
                <script type='text/javascript'>
                //<![CDATA[
                $('#save_notes').bind('click', function () {
                    $.post('admin.php?section=admin&act=donotes',
                        { notes: $('#notes').val() },
                        function(data) {
                            if (data != 0) $('#save_notes_status').stop(true).fadeIn().animate({opacity: 1.0}, {duration: 2000}).fadeOut('slow');
                        });
                });
                //]]>
                </script>";

        $this->trellis->skin->add_sidebar_help( '{lang.td_latest_news}', '{lang.td_latest_news_msg}' );

        $this->trellis->skin->add_output( $acp_content );

        $this->trellis->skin->do_output( array( 'title' => 'System Overview' ) );
    }

    #=======================================
    # @ AJAX Save Notes
    #=======================================

    function ajax_save_notes()
    {
        $this->trellis->cache->add( 'misc', array( 'acp_notes' => $this->trellis->input['notes'] ) );

        $this->trellis->skin->ajax_output( '1' );
    }

}

?>