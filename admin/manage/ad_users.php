<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_users {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'users' );

        $this->trellis->load_functions('users');
        $this->trellis->load_lang('users');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_users();
            break;
            case 'pending':
                $this->list_pending();
            break;
            case 'view':
                $this->view_user();
            break;
            case 'add':
                $this->add_user();
            break;
            case 'edit':
                $this->edit_user();
            break;
            case 'delete':
                $this->delete_user();
            break;
            case 'sig':
                $this->edit_signature();
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
            case 'dosig':
                $this->do_signature();
            break;
            case 'doapprove':
                $this->do_approve();
            break;
            case 'domulti':
                $this->do_multi_action();
            break;

            default:
                $this->list_users();
            break;
        }
    }

    #=======================================
    # @ List Users
    #=======================================

    private function list_users()
    {
        #=============================
        # Sorting Options
        #=============================

        $sort = $this->trellis->generate_sql_sort( array(
                                                         'default_sort' => 'id',
                                                         'default_order' => 'asc',
                                                         'base_url' => $this->generate_url( array( 'sort' => '', 'order' => '' ) ),
                                                         'options' => array(
                                                                             'id' => '{lang.id}',
                                                                             'name' => '{lang.name}',
                                                                             'tickets_open' => '{lang.open_tickets}',
                                                                             'tickets_total' => '{lang.total_tickets}',
                                                                             ),
                                                  )         );

        #=============================
        # Grab Users
        #=============================

        $u_total = $this->trellis->func->users->get( array( 'select' => array( 'id' ) ) );

        $user_rows = '';

        if ( ! $users = $this->trellis->func->users->get( array( 'select' => array( 'id', 'name', 'ugroup', 'tickets_total', 'tickets_open' ), 'order' => array( $sort['sort'] => $sort['order'] ), 'limit' => array( $this->trellis->input['st'], 15 ) ) ) )
        {
            $user_rows .= "<tr><td class='bluecell-light' colspan='7'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=add'>{lang.no_users}</a></strong></td></tr>";
        }
        else
        {
            foreach( $users as $uid => $u )
            {
                $user_rows .= "<tr>
                                    <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$u['id']}'><strong>{$u['id']}</strong></a></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$u['id']}'>{$u['name']}</a></td>
                                    <td class='bluecellthin-light' style='font-weight: normal'>". $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_name'] ."</td>
                                    <td class='bluecellthin-light' align='center'>{$u['tickets_open']}</td>
                                    <td class='bluecellthin-light' align='center'>{$u['tickets_total']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=edit&amp;id={$u['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=delete&amp;id={$u['id']}'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $u_total ),
                                                        'per_page'    => 15,
                                                        'url'        => $this->generate_url( array( 'st' => 0 ) ),
                                                        ) );

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.users_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='6%' align='left'>{$sort['link_id']}</th>
                            <th class='bluecellthin-th' width='32%' align='left'>{$sort['link_name']}</th>
                            <th class='bluecellthin-th' width='22%' align='left'>{lang.group}</th>
                            <th class='bluecellthin-th' width='17%' align='center'>{$sort['link_tickets_open']}</th>
                            <th class='bluecellthin-th' width='17%' align='center'>{$sort['link_tickets_total']}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $user_rows ."
                        <tr>
                            <td class='bluecellthin-th-pages' colspan='7'>". $page_links ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=add' ),
                            array( 'tick', '{lang.menu_pending}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=list&amp;act=pending' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=users' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_users_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_users_title}', '{lang.help_about_users_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ List Pending Users
    #=======================================

    private function list_pending()
    {
        #=============================
        # Sorting Options
        #=============================

        $sort = $this->trellis->generate_sql_sort( array(
                                                         'default_sort' => 'id',
                                                         'default_order' => 'asc',
                                                         'base_url' => '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=pending',
                                                         'options' => array(
                                                                             'id' => '{lang.id}',
                                                                             'name' => '{lang.name}',
                                                                             'email' => '{lang.email}',
                                                                             'joined' => '{lang.joined}',
                                                                             ),
                                                  )         );

        #=============================
        # Grab Users
        #=============================

        $u_total = $this->trellis->func->users->get( array( 'select' => array( 'id' ), 'where' => array( array( 'val_email', '!=', 1 ), array( 'val_admin', '!=', 1, 'or' ) ), 'order' => array( $sort['sort'] => $sort['order'] ) ) );

        $user_rows = '';

        $icon_cross = "<img src='<! IMG_URL !>/icon_cross.png' alt='X' />";
        $icon_tick = "<img src='<! IMG_URL !>/icon_tick.png' alt='/' />";

        if ( ! $users = $this->trellis->func->users->get( array( 'select' => array( 'id', 'name', 'email', 'joined', 'val_admin', 'val_email', 'ipadd' ), 'where' => array( array( 'val_email', '!=', 1 ), array( 'val_admin', '!=', 1, 'or' ) ), 'order' => array( $sort['sort'] => $sort['order'] ), 'limit'    => array( $this->trellis->input['st'], 15 ) ) ) )
        {
            $user_rows .= "<tr><td class='bluecell-light' colspan='7'><strong>{lang.no_pending_users}</a></td></tr>";
        }
        else
        {
            foreach( $users as $uid => $u )
            {
                $u['joined_human'] = $this->trellis->td_timestamp( array( 'time' => $u['joined'], 'format' => 'short' ) );

                ( $u['val_admin'] ) ? $u['val_admin_icon'] = $icon_tick :  $u['val_admin_icon'] = $icon_cross;
                ( $u['val_email'] ) ? $u['val_email_icon'] = $icon_tick :  $u['val_email_icon'] = $icon_cross;

                $user_rows .= "<tr>
                                    <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$u['id']}'><strong>{$u['id']}</strong></a></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$u['id']}'>{$u['name']}</a></td>
                                    <td class='bluecellthin-light' style='font-weight: normal'>{$u['email']}</td>
                                    <td class='bluecellthin-light' style='font-weight: normal'>{$u['joined_human']}</td>
                                    <td class='bluecellthin-light' align='center'>{$u['val_admin_icon']}</td>
                                    <td class='bluecellthin-light' align='center'>{$u['val_email_icon']}</td>
                                    <td class='bluecellthin-light'><input name='mau[]' id='mau_{$u['id']}' type='checkbox' value='{$u['id']}' class='maucb' /></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $u_total ),
                                                        'per_page'    => 15,
                                                        'url'        => $this->generate_url(),
                                                        ) );

        $this->output .= "<script type='text/javascript'>
                        //<![CDATA[
                        function confirmMultiAction() {
                            dialogConfirm({
                                title: '{lang.dialog_multi_action_title}',
                                message: '{lang.dialog_multi_action_msg}',
                                yesButton: '{lang.dialog_multi_action_button}',
                                yesAction: function() {
                                    $('#confirm_ma').val(1);
                                    $('#multi_user').trigger('submit');
                                },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        $(function() {
                            $('#multi_user').submit(function() {
                                if ( $('#confirm_ma').val() != 1 ) {
                                    return confirmMultiAction();
                                }
                                else {
                                    return true;
                                }
                            });
                        });
                        //]]>
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=domulti", 'multi_user', 'post' ) ."
                        <input type='hidden' id='confirm_ma' name='confirm_ma' value='0' />
                        ". $this->trellis->skin->start_group_table( '{lang.pending_users_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='6%' align='left'>{$sort['link_id']}</th>
                            <th class='bluecellthin-th' width='26%' align='left'>{$sort['link_name']}</th>
                            <th class='bluecellthin-th' width='41%' align='left'>{$sort['link_email']}</th>
                            <th class='bluecellthin-th' width='20%' align='left'>{$sort['link_joined']}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.admin}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.email}</th>
                            <th class='bluecellthin-th' width='1%' align='center'><input name='checkall' id='checkall' type='checkbox' value='1' /></th>
                        </tr>
                        ". $user_rows ."
                        <tr>
                            <td class='bluecellthin-th-pages' colspan='3' align='left'>". $page_links ."</td>
                            <td class='bluecellthin-th' colspan='4' align='right'>{lang.with_selected} <select name='mu_action' id='mu_action'><option value='aa'>{lang.approve_admin}</option><option value='ae'>{lang.approve_email}</option><option value='ab'>{lang.approve_both}</option></select>&nbsp;<input type='submit' name='mu_go' id='mu_go' value='{lang.go}' /></td>
                        </tr>
                        ". $this->trellis->skin->end_group_table() ."
                        ". $this->trellis->skin->end_form() ."
                        </div>
                        <script type='text/javascript'>
                        //<![CDATA[
                        $('#checkall').bind('click', function() {
                            $('.maucb').attr('checked', this.checked);
                        });
                        //]]>
                        </script>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=add' ),
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=users' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=users' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_users_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ View User
    #=======================================

    private function view_user()
    {
        #=============================
        # Grab User
        #=============================

        if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name', 'email', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp', 'title', 'joined', 'time_zone', 'time_dst', 'tickets_total', 'tickets_open', 'val_admin', 'val_email' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_user');

        $staff = 0;

        if ( $u['ugroup_sub_acp'] ) $staff = 1;

        if ( $u['id'] == 1 ) $this->trellis->check_perm( 'manage', 'users', 'root' );

        if ( ! $staff && $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_acp_access'] ) $staff = 1;

        if ( ! $staff && $u['ugroup_sub_acp'] && $u['ugroup_sub'] )
        {
            $u['ugroup_sub'] = unserialize( $u['ugroup_sub'] );

            if ( ! empty( $u['ugroup_sub'] ) )
            {
                foreach ( $u['ugroup_sub'] as $gid )
                {
                    if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $staff = 1;

                    break;
                }
            }
        }

        if ( $staff ) $this->trellis->check_perm( 'manage', 'users', 'staff' );

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('cpfields');

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.viewing_user} '. $u['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.username}', $u['name'], 'a', '20%', '80%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email}', $u['email'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title}', $u['title'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.group}', $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.time_zone}', $u['time_zone'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.local_time}', $this->trellis->td_timestamp( array( 'time' => time(), 'time_zone' => $u['time_zone'], 'dst' => $u['time_dst'], 'format' => 'long' ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.joined}', $this->trellis->td_timestamp( array( 'time' => $u['joined'], 'format' => 'long' ) ), 'a' );

        if ( $cfields = $this->trellis->func->cpfields->grab( $u['ugroup'], 1 ) )
        {
            $this->output .= $this->trellis->skin->group_table_sub( '{lang.additional_info}' );

            $fdata = $this->trellis->func->cpfields->get_data( $u['id'] );

            foreach( $cfields as $fid => $f )
            {
                if ( $f['type'] == 'checkbox' )
                {
                    $f['extra'] = unserialize( $f['extra'] );

                    $checkbox_html = '';

                    foreach( $f['extra'] as $key => $name )
                    {
                        $checkbox_html .= $this->trellis->skin->checkbox( array( 'name' => 'cpf_'. $f['id'] .'_'. $key, 'title' => $name, 'value' => $fdata[ $f['id'] ][ $key ], 'disabled' => 1 ) ) .'&nbsp;&nbsp;&nbsp;';
                    }

                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $checkbox_html, 'a' );
                }
                elseif ( $f['type'] == 'dropdown' || $f['type'] == 'radio' )
                {
                    $f['extra'] = unserialize( $f['extra'] );

                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $f['extra'][ $fdata[ $f['id'] ] ], 'a' );
                }
                else
                {
                    if ( ! $fdata[ $f['id'] ] ) $fdata[ $f['id'] ] = '--';

                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $fdata[ $f['id'] ], 'a' );
                }
            }
        }

        $this->output .= $this->trellis->skin->group_table_sub( '{lang.statistics}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.open_tickets}', $u['tickets_open'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.total_tickets}', $u['tickets_total'], 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        </div>";

        $menu_items = array(
                            array( 'buoy', '{lang.menu_user_tickets}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;search='. $u['id'] .'&amp;field=uid' ),
                            array( 'buoy_plus', '{lang.menu_user_submit}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;act=add&amp;uid='. $u['id'] ),
                            );

        if ( $staff ) $menu_items[] = array( 'ticket', '{lang.menu_user_assigned}', '<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;assigned='. $u['id'] );

        if ( $this->trellis->check_perm( 'manage', 'users', 'approve', 0 ) )
        {
            if ( ! $u['val_admin'] ) $menu_items[] = array( 'tick', '{lang.menu_approve_admin}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=doapprove&amp;type=admin&amp;id='. $u['id'] );
            if ( ! $u['val_email'] ) $menu_items[] = array( 'tick', '{lang.menu_approve_email}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=doapprove&amp;type=email&amp;id='. $u['id'] );
        }

        $menu_items[] = array( 'signature', '{lang.menu_user_signature}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=sig&amp;id='. $u['id'] );
        $menu_items[] = array( 'user_pencil', '{lang.menu_user_edit}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=edit&amp;id='. $u['id'] );
        $menu_items[] = array( 'user_minus', '{lang.menu_user_delete}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=delete&amp;id='. $u['id'] );

        $this->trellis->skin->add_sidebar_menu( $u['name'], $menu_items );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=users' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=users' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_users_options}', $menu_items );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add User
    #=======================================

    private function add_user($error='', $field='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            if ( $field )
            {
                $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'} '. $field );
            }
            else
            {
                $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            }

            $this->trellis->skin->preserve_input = 1;
        }

        $this->trellis->load_functions('drop_downs');
        $this->trellis->load_functions('cpfields');

        ( $this->trellis->check_perm( 'manage', 'users', 'staff', 0 ) ) ? $ugroup_sub_acp = '&nbsp;&nbsp;'. $this->trellis->skin->checkbox( array( 'name' => 'ugroup_sub_acp', 'title' => '{lang.acp_access} '. $this->trellis->skin->help_tip('{lang.tip_acp_access}') ) ) : $ugroup_sub_acp = "";

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=doadd", 'add_user', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_user}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.username}', $this->trellis->skin->textfield( 'name' ), 'a', '25%', '75%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email}', $this->trellis->skin->textfield( 'email' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.password}', $this->trellis->skin->textfield( 'password' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title} '. $this->trellis->skin->help_tip('{lang.tip_title}'), $this->trellis->skin->textfield( 'title' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.group}', "<select name='ugroup' id='ugroup'>". $this->trellis->func->drop_downs->group_drop( array( 'select' => $this->trellis->input['ugroup'], 'staff_check' => 1 ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.sub_groups} '. $this->trellis->skin->help_tip('{lang.tip_sub_groups}'), "<select name='ugroup_sub[]' id='ugroup_sub' multiple='yes'>". $this->trellis->func->drop_downs->group_drop( array( 'select' => $this->trellis->input['ugroup_sub'], 'staff_check' => 1 ) ) ."</select>". $ugroup_sub_acp, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.time_zone}', "<select name='time_zone' id='time_zone'>". $this->trellis->func->drop_downs->time_zone_drop( $this->trellis->input['time_zone'] ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.dst} '. $this->trellis->skin->help_tip('{lang.tip_dst}'), $this->trellis->skin->custom_radio( 'time_dst', array( 0 => '{lang.inactive}', 1 => '{lang.active}', 2 => '{lang.auto}' ), 2 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.language}', "<select name='lang' id='lang'>". $this->trellis->func->drop_downs->lang_drop( $this->trellis->input['lang'] ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.skin}', "<select name='skin' id='skin'>". $this->trellis->func->drop_downs->skin_drop( $this->trellis->input['skin'] ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.rich_text_editor}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'rte_enable', 'value' => 1 ) ), 'a' );

        if ( $cfields = $this->trellis->func->cpfields->grab( 0, 1 ) )
        {
            $this->output .= $this->trellis->skin->group_table_sub( '{lang.additional_info}' );

            foreach( $cfields as $fid => $f )
            {
                $f['extra'] = unserialize( $f['extra'] );

                if ( $f['type'] == 'textfield' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->textfield( array( 'name' => 'cpf_'. $f['id'], 'length' => $f['extra']['size'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'textarea' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->textarea( array( 'name' => 'cpf_'. $f['id'], 'cols' => $f['extra']['cols'], 'rows' => $f['extra']['rows'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'dropdown' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->drop_down( array( 'name' => 'cpf_'. $f['id'], 'options' => $f['extra'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'checkbox' )
                {
                    $checkbox_html = '';

                    foreach( $f['extra'] as $key => $name )
                    {
                        $checkbox_html .= $this->trellis->skin->checkbox( array( 'name' => 'cpf_'. $f['id'] .'_'. $key, 'title' => $name ) ) .'&nbsp;&nbsp;&nbsp;';
                    }

                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $checkbox_html, 'a' );
                }
                elseif ( $f['type'] == 'radio' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->custom_radio( array( 'name' => 'cpf_'. $f['id'], 'options' => $f['extra'] ) ), 'a' );
                }
            }
        }

        $this->output .= $this->trellis->skin->group_table_sub( '{lang.user_email_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_notifications}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_enable', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['eunotify']['enable'], 'for' => 1, 'msg' => '{lang.warn_email_user_enable}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_user_ticket}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_ticket', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_user_action}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_action', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_news}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_news', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.staff_email_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_notifications}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_enable', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['enable'], 'for' => 1, 'msg' => '{lang.warn_email_enable}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_user_approve}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_user_approve', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['user_approve'], 'for' => 1, 'msg' => '{lang.warn_email_user_approve}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_ticket}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_ticket', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['ticket'], 'for' => 1, 'msg' => '{lang.warn_email_ticket}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_reply}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_reply', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['reply'], 'for' => 1, 'msg' => '{lang.warn_email_reply}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_assign}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_assign', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['assign'], 'for' => 1, 'msg' => '{lang.warn_email_assign}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_escalate}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_escalate', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['escalate'], 'for' => 1, 'msg' => '{lang.warn_email_escalate}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_hold}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_hold', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['hold'], 'for' => 1, 'msg' => '{lang.warn_email_hold}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_move_to}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_move_to', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['move_to'], 'for' => 1, 'msg' => '{lang.warn_email_move_to}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_move_away}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_move_away', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['move_away'], 'for' => 1, 'msg' => '{lang.warn_email_move_away}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_close}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_close', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['close'], 'for' => 1, 'msg' => '{lang.warn_email_close}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_reopen}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_reopen', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['reopen'], 'for' => 1, 'msg' => '{lang.warn_email_reopen}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.receive_for}', $this->trellis->skin->checkbox( array( 'name' => 'esn_unassigned', 'title' => '{lang.unassigned_tickets}', 'value' => 1 ) ) ."<br />". $this->trellis->skin->checkbox( array( 'name' => 'esn_assigned', 'title' => '{lang.assigned_tickets}', 'value' => 1 ) ) ."<br />". $this->trellis->skin->checkbox( array( 'name' => 'esn_assigned_to_me', 'title' => '{lang.tickets_assigned_to_me}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.email_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_type}', $this->trellis->skin->custom_radio( 'email_type', array( 1 => '{lang.html}', 2 => '{lang.plain_text}' ), 1 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.ticket_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.auto_assign} '. $this->trellis->skin->help_tip('{lang.tip_auto_assign}'), $this->trellis->skin->yes_no_radio( 'auto_assign', 1 ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_user}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'        => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 'email'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_email}' ) ), array( 'type' => 'email', 'params' => array( 'fail_msg' => '{lang.lv_invalid_email}' ) ) ),
                                 'password'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_password}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=users' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=users' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_users_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit User
    #=======================================

    private function edit_user($error='', $field='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'edit' );

        #=============================
        # Grab User
        #=============================

        if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name', 'email', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp', 'title', 'lang', 'skin', 'time_zone', 'time_dst', 'rte_enable', 'email_enable', 'email_ticket', 'email_action', 'email_news', 'email_type' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_user');

        #=============================
        # More Security Checks
        #=============================

        $staff = 0;

        if ( $u['ugroup_sub_acp'] ) $staff = 1;

        if ( $u['id'] == 1 ) $this->trellis->check_perm( 'manage', 'users', 'root' );

        if ( ! $staff && $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_acp_access'] ) $staff = 1;

        $u['ugroup_sub'] = unserialize( $u['ugroup_sub'] );

        if ( ! $staff && $u['ugroup_sub'] )
        {
            if ( ! empty( $u['ugroup_sub'] ) )
            {
                foreach ( $u['ugroup_sub'] as $gid )
                {
                    if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $staff = 1;

                    break;
                }
            }
        }

        if ( $staff )
        {
            $this->trellis->check_perm( 'manage', 'users', 'staff' );

            $us = $this->trellis->func->users->get_single_by_id_staff( array( 'email_staff_enable', 'email_staff_user_approve', 'email_staff_ticket', 'email_staff_reply', 'email_staff_assign', 'email_staff_escalate', 'email_staff_hold', 'email_staff_move_to', 'email_staff_move_away', 'email_staff_close', 'email_staff_reopen', 'esn_unassigned', 'esn_assigned', 'esn_assigned_to_me', 'auto_assign' ), $u['id'] );

            if ( is_array( $us ) ) $u = array_merge( $us, $u );
        }

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            if ( $field )
            {
                $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'} '. $field );
            }
            else
            {
                $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            }

            $this->trellis->skin->preserve_input = 1;

            $ugroup = $this->trellis->input['ugroup'];
            $ugroup_sub = $this->trellis->input['ugroup_sub'];
            $time_zone = $this->trellis->input['time_zone'];
            $lang = $this->trellis->input['lang'];
            $skin = $this->trellis->input['skin'];
        }
        else
        {
            $ugroup = $u['ugroup'];
            $ugroup_sub = $u['ugroup_sub'];
            $time_zone = $u['time_zone'];
            $lang = $u['lang'];
            $skin = $u['skin'];
        }

        $this->trellis->load_functions('drop_downs');
        $this->trellis->load_functions('cpfields');

        ( $this->trellis->check_perm( 'manage', 'users', 'staff', 0 ) ) ? $ugroup_sub_acp = '&nbsp;&nbsp;'. $this->trellis->skin->checkbox( array( 'name' => 'ugroup_sub_acp', 'title' => '{lang.acp_access} '. $this->trellis->skin->help_tip('{lang.tip_acp_access}'), 'value' => $u['ugroup_sub_acp'] ) ) : $ugroup_sub_acp = "";

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=doedit&amp;id={$u['id']}", 'edit_user', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_user} '. $u['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.username}', $this->trellis->skin->textfield( 'name', $u['name'] ), 'a', '25%', '75%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email}', $this->trellis->skin->textfield( 'email', $u['email'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.password} '. $this->trellis->skin->help_tip('{lang.tip_change_password}'), $this->trellis->skin->textfield( 'password' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title} '. $this->trellis->skin->help_tip('{lang.tip_title}'), $this->trellis->skin->textfield( 'title', $u['title'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.group}', "<select name='ugroup' id='ugroup'>". $this->trellis->func->drop_downs->group_drop( array( 'select' => $ugroup, 'staff_check' => 1 ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.sub_groups} '. $this->trellis->skin->help_tip('{lang.tip_sub_groups}'), "<select name='ugroup_sub[]' id='ugroup_sub' multiple='yes'>". $this->trellis->func->drop_downs->group_drop( array( 'select' => $ugroup_sub, 'staff_check' => 1 ) ) ."</select>". $ugroup_sub_acp, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.time_zone}', "<select name='time_zone' id='time_zone'>". $this->trellis->func->drop_downs->time_zone_drop( $time_zone ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.dst} '. $this->trellis->skin->help_tip('{lang.dst}'), $this->trellis->skin->custom_radio( 'time_dst', array( 0 => '{lang.inactive}', 1 => '{lang.active}', 2 => '{lang.auto}' ), $u['time_dst'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.language}', "<select name='lang' id='lang'>". $this->trellis->func->drop_downs->lang_drop( $lang ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.skin}', "<select name='skin' id='skin'>". $this->trellis->func->drop_downs->skin_drop( $skin ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.rich_text_editor}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'rte_enable', 'value' => $u['rte_enable'] ) ), 'a' );

        if ( $cfields = $this->trellis->func->cpfields->grab( 0, 1 ) )
        {
            $this->output .= $this->trellis->skin->group_table_sub( '{lang.additional_info}' );

            $fdata = $this->trellis->func->cpfields->get_data( $u['id'] );

            foreach( $cfields as $fid => $f )
            {
                $f['extra'] = unserialize( $f['extra'] );

                if ( $f['type'] == 'textfield' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->textfield( array( 'name' => 'cpf_'. $f['id'], 'value' => $fdata[ $f['id'] ], 'length' => $f['extra']['size'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'textarea' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->textarea( array( 'name' => 'cpf_'. $f['id'], 'value' => $fdata[ $f['id'] ], 'cols' => $f['extra']['cols'], 'rows' => $f['extra']['rows'] ) ), 'a' );
                }
                elseif ( $f['type'] == 'dropdown' )
                {
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->drop_down( array( 'name' => 'cpf_'. $f['id'], 'value' => $fdata[ $f['id'] ], 'options' => $f['extra'] ) ), 'a' );
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
                    $this->output .= $this->trellis->skin->group_table_row( $f['name'], $this->trellis->skin->custom_radio( array( 'name' => 'cpf_'. $f['id'], 'options' => $f['extra'], 'value' => $fdata[ $f['id'] ] ) ), 'a' );
                }
            }
        }

        $this->output .= $this->trellis->skin->group_table_sub( '{lang.user_email_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_notifications}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_enable', 'value' => $u['email_enable'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['enable'], 'for' => 1, 'msg' => '{lang.warn_email_user_enable}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_user_ticket}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_ticket', 'value' => $u['email_ticket'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_user_action}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_action', 'value' => $u['email_action'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_news}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_news', 'value' => $u['email_news'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.staff_email_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_notifications}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_enable', 'value' => $u['email_staff_enable'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['enable'], 'for' => 1, 'msg' => '{lang.warn_email_enable}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_user_approve}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_user_approve', 'value' => $u['email_staff_user_approve'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['user_approve'], 'for' => 1, 'msg' => '{lang.warn_email_user_approve}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_ticket}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_ticket', 'value' => $u['email_staff_ticket'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['ticket'], 'for' => 1, 'msg' => '{lang.warn_email_ticket}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_reply}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_reply', 'value' => $u['email_staff_reply'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['reply'], 'for' => 1, 'msg' => '{lang.warn_email_reply}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_assign}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_assign', 'value' => $u['email_staff_assign'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['assign'], 'for' => 1, 'msg' => '{lang.warn_email_assign}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_escalate}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_escalate', 'value' => $u['email_staff_escalate'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['escalate'], 'for' => 1, 'msg' => '{lang.warn_email_escalate}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_hold}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_hold', 'value' => $u['email_staff_hold'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['hold'], 'for' => 1, 'msg' => '{lang.warn_email_hold}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_move_to}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_move_to', 'value' => $u['email_staff_move_to'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['move_to'], 'for' => 1, 'msg' => '{lang.warn_email_move_to}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_move_away}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_move_away', 'value' => $u['email_staff_move_away'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['move_away'], 'for' => 1, 'msg' => '{lang.warn_email_move_away}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_close}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_close', 'value' => $u['email_staff_close'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['close'], 'for' => 1, 'msg' => '{lang.warn_email_close}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_reopen}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_reopen', 'value' => $u['email_staff_reopen'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['reopen'], 'for' => 1, 'msg' => '{lang.warn_email_reopen}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.receive_for}', $this->trellis->skin->checkbox( array( 'name' => 'esn_unassigned', 'title' => '{lang.unassigned_tickets}', 'value' => $u['esn_unassigned'] ) ) ."<br />". $this->trellis->skin->checkbox( array( 'name' => 'esn_assigned', 'title' => '{lang.assigned_tickets}', 'value' => $u['esn_assigned'] ) ) ."<br />". $this->trellis->skin->checkbox( array( 'name' => 'esn_assigned_to_me', 'title' => '{lang.tickets_assigned_to_me}', 'value' => $u['esn_assigned_to_me'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.email_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_type}', $this->trellis->skin->custom_radio( 'email_type', array( 1 => '{lang.html}', 2 => '{lang.plain_text}' ), $u['email_type'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.ticket_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.auto_assign} '. $this->trellis->skin->help_tip('{lang.tip_auto_assign}'), $this->trellis->skin->yes_no_radio( 'auto_assign', $u['auto_assign'] ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_user}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'        => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 'email'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_email}' ) ), array( 'type' => 'email', 'params' => array( 'fail_msg' => '{lang.lv_invalid_email}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $menu_items = array(
                            array( 'signature', '{lang.menu_user_signature}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=sig&amp;id='. $u['id'] ),
                            array( 'arrow_back', '{lang.menu_back_user}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id='. $u['id'] ),
                            #array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=users' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=users' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_users_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Delete User Form
    #=======================================

    private function delete_user()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'delete' );

        if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_user');

        #=============================
        # More Security Checks
        #=============================

        $staff = 0;

        if ( $u['ugroup_sub_acp'] ) $staff = 1;

        if ( $u['id'] == 1 ) $this->trellis->check_perm( 'manage', 'users', 'root' );

        if ( ! $staff && $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_acp_access'] ) $staff = 1;

        if ( ! $staff && $u['ugroup_sub'] )
        {
            $u['ugroup_sub'] = unserialize( $u['ugroup_sub'] );

            if ( ! empty( $u['ugroup_sub'] ) )
            {
                foreach ( $u['ugroup_sub'] as $gid )
                {
                    if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $staff = 1;

                    break;
                }
            }
        }

        if ( $staff ) $this->trellis->check_perm( 'manage', 'users', 'staff' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=dodel&amp;id={$u['id']}", 'delete_user', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.deleting_user} '. $u['name'] ) ."
                        ". $this->trellis->skin->group_sub( '{lang.delete_user_options}' ) ."
                        <div class='option1'>". $this->trellis->skin->checkbox( 'delete_tickets', '{lang.delete_user_tickets}', 1 ) ."</div>
                        <div class='option2'>". $this->trellis->skin->checkbox( 'delete_replies', '{lang.delete_user_replies}', 1 ) ."</div>
                        <div class='option1'>". $this->trellis->skin->checkbox( 'delete_comments', '{lang.delete_user_comments}', 1 ) ."</div>
                        <div class='option2'>". $this->trellis->skin->checkbox( 'delete_ratings', '{lang.delete_user_ratings}', 1 ) ."</div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_delete_user}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=users' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=users' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_users_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Signature
    #=======================================

    private function edit_signature()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'edit' );

        #=============================
        # Grab User
        #=============================

        if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp', 'signature', 'sig_auto' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_user');

        #=============================
        # More Security Checks
        #=============================

        $staff = 0;

        if ( $u['ugroup_sub_acp'] ) $staff = 1;

        if ( $u['id'] == 1 ) $this->trellis->check_perm( 'manage', 'users', 'root' );

        if ( ! $staff && $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_acp_access'] ) $staff = 1;

        if ( ! $staff && $u['ugroup_sub'] )
        {
            $u['ugroup_sub'] = unserialize( $u['ugroup_sub'] );

            if ( ! empty( $u['ugroup_sub'] ) )
            {
                foreach ( $u['ugroup_sub'] as $gid )
                {
                    if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $staff = 1;

                    break;
                }
            }
        }

        if ( $staff ) $this->trellis->check_perm( 'manage', 'users', 'staff' );

        #=============================
        # Do Output
        #=============================

        if ( $alert )
        {
            $this->output .= $this->trellis->skin->alert_wrap( '{lang.alert_'. $alert .'}' );
        }

        if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['tickets']['rte'] )
        {
            $this->output .= $this->trellis->skin->tinymce_js( 'signature' );
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=dosig&amp;id={$u['id']}", 'edit_signature', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.editing_signature} '. $u['name'] ) ."
                        <div class='option1'>". $this->trellis->skin->textarea( array( 'name' => 'signature', 'value' => $u['signature'], 'cols' => 80, 'rows' => 6, 'width' => '98%', 'height' => '160px' ) ) ."</div>
                        <div class='option2'>". $this->trellis->skin->checkbox( 'sig_auto', '{lang.auto_append_sig}', $u['sig_auto'] ) ."</div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_signature}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'user_pencil', '{lang.menu_user_edit}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=edit&amp;id='. $u['id'] ),
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=users' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=users' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_users_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add User
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'add' );

        if ( ! $this->trellis->input['name'] ) $this->add_user('no_username');
        if ( ! $this->trellis->input['email'] ) $this->add_user('no_email');
        if ( ! $this->trellis->input['password'] ) $this->add_user('no_password');

        if ( ! $this->trellis->validate_email( $this->trellis->input['email'] ) ) $this->add_user('invalid_email');

        #=============================
        # Check Name & Email
        #=============================

        if ( $this->trellis->func->users->check_name( $this->trellis->input['name'] ) ) $this->add_user('name_in_use');

        if ( $this->trellis->func->users->check_email( $this->trellis->input['email'] ) ) $this->add_user('email_in_use');

        #=============================
        # Add User
        #=============================

        ( ! empty( $this->trellis->input['ugroup_sub'] ) ) ? $ugroup_sub = serialize( $this->trellis->input['ugroup_sub'] ) : $ugroup_sub = '';

        $staff = 0;

        if ( $this->trellis->input['ugroup_sub_acp'] ) $staff = 1;

        if ( ! $staff && $this->trellis->cache->data['groups'][ $this->trellis->input['ugroup'] ]['g_acp_access'] )
        {
            $staff = 1;
        }
        elseif ( ! $staff && ! empty( $this->trellis->input['ugroup_sub'] ) )
        {
            foreach( $this->trellis->input['ugroup_sub'] as $gid )
            {
                if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] )
                {
                    $staff = 1;

                    break;
                }
            }
        }

        if ( $staff ) $this->trellis->check_perm( 'manage', 'users', 'staff' );

        $db_array = array(
                          'name'                        => $this->trellis->input['name'],
                          'email'                        => $this->trellis->input['email'],
                          'password'                    => $this->trellis->input['password'],
                          'ugroup'                        => $this->trellis->input['ugroup'],
                          'ugroup_sub'                    => $ugroup_sub,
                          'title'                        => $this->trellis->input['title'],
                          'joined'                        => time(),
                          'sig_auto'                    => 1,
                          'lang'                        => $this->trellis->input['lang'],
                          'skin'                        => $this->trellis->input['skin'],
                          'time_zone'                    => $this->trellis->input['time_zone'],
                          'time_dst'                    => $this->trellis->input['time_dst'],
                          'rte_enable'                    => $this->trellis->input['rte_enable'],
                          'email_enable'                => $this->trellis->input['email_enable'],
                          'email_ticket'                => $this->trellis->input['email_ticket'],
                          'email_action'                => $this->trellis->input['email_action'],
                          'email_news'                    => $this->trellis->input['email_news'],
                          'email_staff_enable'            => $this->trellis->input['email_staff_enable'],
                          'email_staff_user_approve'    => $this->trellis->input['email_staff_user_approve'],
                          'email_staff_ticket'            => $this->trellis->input['email_staff_ticket'],
                          'email_staff_reply'            => $this->trellis->input['email_staff_reply'],
                          'email_staff_assign'            => $this->trellis->input['email_staff_assign'],
                          'email_staff_escalate'        => $this->trellis->input['email_staff_escalate'],
                          'email_staff_hold'            => $this->trellis->input['email_staff_hold'],
                          'email_staff_move_to'            => $this->trellis->input['email_staff_move_to'],
                          'email_staff_move_away'        => $this->trellis->input['email_staff_move_away'],
                          'email_staff_close'            => $this->trellis->input['email_staff_close'],
                          'email_staff_reopen'            => $this->trellis->input['email_staff_reopen'],
                          'esn_unassigned'                => $this->trellis->input['esn_unassigned'],
                          'esn_assigned'                => $this->trellis->input['esn_assigned'],
                          'esn_assigned_to_me'            => $this->trellis->input['esn_assigned_to_me'],
                          'email_type'                    => $this->trellis->input['email_type'],
                          'auto_assign'                    => $this->trellis->input['auto_assign'],
                          'val_email'                    => 1,
                          'val_admin'                    => 1,
                          'ipadd'                        => $this->trellis->input['ip_address'],
                          );

        if ( $this->trellis->check_perm( 'manage', 'users', 'staff', 0 ) ) $db_array['ugroup_sub_acp'] = $this->trellis->input['ugroup_sub_acp'];

        $this->trellis->load_functions('cpfields');

        if (!empty($this->trellis->cache->data['pfields'])){
        if( ! $fdata = $this->trellis->func->cpfields->process_input() )
        {
            if ( $this->trellis->func->cpfields->required_field ) $this->add_user( 'no_field', $this->trellis->func->cpfields->required_field );
        }
        else
        {
            $user_id = $this->trellis->func->users->add( $db_array, array( 'staff' => $staff, 'bypass_val' => 1 ) );

            $this->trellis->func->cpfields->set_data( $fdata, $user_id, 1 );
        }
		}
		else {
		 $user_id = $this->trellis->func->users->add( $db_array, array( 'staff' => $staff, 'bypass_val' => 1 ) );
		}

        $this->trellis->log( array( 'msg' => array( 'user_added', $this->trellis->input['name'] ), 'type' => 'user', 'content_type' => 'user', 'content_id' => $user_id ) );

        #=============================
        # Update Staff Cache
        #=============================

        if ( $staff )
        {
            $this->trellis->load_functions('rebuild');

            $this->trellis->func->rebuild->staff_cache();
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_user_added'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $user_id ) );
    }

    #=======================================
    # @ Do Edit User
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'edit' );

        if ( ! $this->trellis->input['name'] ) $this->edit_user('no_username');
        if ( ! $this->trellis->input['email'] ) $this->edit_user('no_email');

        if ( ! $this->trellis->validate_email( $this->trellis->input['email'] ) ) $this->edit_user('invalid_email');

        #=============================
        # Grab User
        #=============================

        if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name', 'email', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_user');

        #=============================
        # More Security Checks
        #=============================

        $staff = 0;

        if ( $u['ugroup_sub_acp'] ) $staff = 1;

        if ( $u['id'] == 1 ) $this->trellis->check_perm( 'manage', 'users', 'root' );

        if ( ! $staff && $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_acp_access'] ) $staff = 1;

        if ( ! $staff && $u['ugroup_sub'] )
        {
            $u['ugroup_sub'] = unserialize( $u['ugroup_sub'] );

            if ( ! empty( $u['ugroup_sub'] ) )
            {
                foreach ( $u['ugroup_sub'] as $gid )
                {
                    if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $staff = 1;

                    break;
                }
            }
        }

        #=============================
        # Check Name & Email
        #=============================

        if ( strtolower( $u['name'] ) != strtolower( $this->trellis->input['name'] ) )
        {
            if ( $this->trellis->func->users->check_name( $this->trellis->input['name'] ) ) $this->edit_user('name_in_use');
        }

        if ( strtolower( $u['email'] ) != strtolower( $this->trellis->input['email'] ) )
        {
            if ( $this->trellis->func->users->check_email( $this->trellis->input['email'] ) ) $this->edit_user('email_in_use');
        }

        #=============================
        # Update User
        #=============================

        ( ! empty( $this->trellis->input['ugroup_sub'] ) ) ? $ugroup_sub = serialize( $this->trellis->input['ugroup_sub'] ) : $ugroup_sub = '';

        if ( ! $staff && $this->trellis->input['ugroup_sub_acp'] ) $staff = 1;

        if ( ! $staff && $this->trellis->cache->data['groups'][ $this->trellis->input['ugroup'] ]['g_acp_access'] )
        {
            $staff = 1;
        }
        elseif ( ! $staff && ! empty( $this->trellis->input['ugroup_sub'] ) )
        {
            foreach( $this->trellis->input['ugroup_sub'] as $gid )
            {
                if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] )
                {
                    $staff = 1;

                    break;
                }
            }
        }

        if ( $staff ) $this->trellis->check_perm( 'manage', 'users', 'staff' );

        $db_array = array(
                          'name'                        => $this->trellis->input['name'],
                          'email'                        => $this->trellis->input['email'],
                          'ugroup'                        => $this->trellis->input['ugroup'],
                          'ugroup_sub'                    => $ugroup_sub,
                          'title'                        => $this->trellis->input['title'],
                          'lang'                        => $this->trellis->input['lang'],
                          'skin'                        => $this->trellis->input['skin'],
                          'time_zone'                    => $this->trellis->input['time_zone'],
                          'time_dst'                    => $this->trellis->input['time_dst'],
                          'rte_enable'                    => $this->trellis->input['rte_enable'],
                          'email_enable'                => $this->trellis->input['email_enable'],
                          'email_ticket'                => $this->trellis->input['email_ticket'],
                          'email_action'                => $this->trellis->input['email_action'],
                          'email_news'                    => $this->trellis->input['email_news'],
                          'email_staff_enable'            => $this->trellis->input['email_staff_enable'],
                          'email_staff_user_approve'    => $this->trellis->input['email_staff_user_approve'],
                          'email_staff_ticket'            => $this->trellis->input['email_staff_ticket'],
                          'email_staff_reply'            => $this->trellis->input['email_staff_reply'],
                          'email_staff_assign'            => $this->trellis->input['email_staff_assign'],
                          'email_staff_escalate'        => $this->trellis->input['email_staff_escalate'],
                          'email_staff_hold'            => $this->trellis->input['email_staff_hold'],
                          'email_staff_move_to'            => $this->trellis->input['email_staff_move_to'],
                          'email_staff_move_away'        => $this->trellis->input['email_staff_move_away'],
                          'email_staff_close'            => $this->trellis->input['email_staff_close'],
                          'email_staff_reopen'            => $this->trellis->input['email_staff_reopen'],
                          'esn_unassigned'                => $this->trellis->input['esn_unassigned'],
                          'esn_assigned'                => $this->trellis->input['esn_assigned'],
                          'esn_assigned_to_me'            => $this->trellis->input['esn_assigned_to_me'],
                          'email_type'                    => $this->trellis->input['email_type'],
                          'auto_assign'                    => $this->trellis->input['auto_assign'],
                          );

        if ( $this->trellis->check_perm( 'manage', 'users', 'staff', 0 ) ) $db_array['ugroup_sub_acp'] = $this->trellis->input['ugroup_sub_acp'];

        $this->trellis->load_functions('cpfields');
		if (!empty($this->trellis->cache->data['pfields'])){
        if( ! $fdata = $this->trellis->func->cpfields->process_input() )
        {
            if ( $this->trellis->func->cpfields->required_field ) $this->edit_user( 'no_field', $this->trellis->func->cpfields->required_field );
        }
        else
        {
            $this->trellis->func->users->edit( $db_array, $u['id'], array( 'staff' => $staff ) );

            $this->trellis->func->cpfields->set_data( $fdata, $u['id'] );
        }
		}
		else {$this->trellis->func->users->edit( $db_array, $u['id'], array( 'staff' => $staff ) );}

        $this->trellis->log( array( 'msg' => array( 'user_edited', $this->trellis->input['name'] ), 'type' => 'user', 'content_type' => 'user', 'content_id' => $u['id'] ) );

        #=============================
        # Change Password
        #=============================

        if ( $this->trellis->input['password'] )
        {
            $this->trellis->func->users->change_password( $this->trellis->input['password'], $u['id'] );

            $this->trellis->log( array( 'msg' => array( 'user_password', $this->trellis->input['name'] ), 'type' => 'security', 'content_type' => 'user', 'content_id' => $u['id'] ) );
        }

        #=============================
        # Update Staff Cache
        #=============================

        if ( $staff )
        {
            $this->trellis->load_functions('rebuild');

            $this->trellis->func->rebuild->staff_cache();
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_user_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $u['id'] ) );
    }

    #=======================================
    # @ Do Delete User
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'delete' );

        #=============================
        # Grab User
        #=============================

        if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_user');

        #=============================
        # More Security Checks
        #=============================

        $staff = 0;

        if ( $u['ugroup_sub_acp'] ) $staff = 1;

        if ( $u['id'] == 1 ) $this->trellis->check_perm( 'manage', 'users', 'root' );

        if ( ! $staff && $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_acp_access'] ) $staff = 1;

        if ( ! $staff && $u['ugroup_sub'] )
        {
            $u['ugroup_sub'] = unserialize( $u['ugroup_sub'] );

            if ( ! empty( $u['ugroup_sub'] ) )
            {
                foreach ( $u['ugroup_sub'] as $gid )
                {
                    if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $staff = 1;

                    break;
                }
            }
        }

        if ( $staff ) $this->trellis->check_perm( 'manage', 'users', 'staff' );

        #=============================
        # DELETE User
        #=============================

        $this->trellis->func->users->delete( $u['id'], array( 'tickets' => $this->trellis->input['delete_tickets'], 'replies' => $this->trellis->input['delete_replies'], 'comments' => $this->trellis->input['delete_comments'], 'ratings' => $this->trellis->input['delete_ratings'] ) );

        $this->trellis->log( array( 'msg' => array( 'user_deleted', $u['name'] ), 'type' => 'user', 'level' => 2 ) );

        #=============================
        # Update Staff Cache
        #=============================

        if ( $staff )
        {
            $this->trellis->load_functions('rebuild');

            $this->trellis->func->rebuild->staff_cache();
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_user_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Signature
    #=======================================

    private function do_signature()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'edit' );

        #=============================
        # Grab User
        #=============================

        if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_user');

        #=============================
        # More Security Checks
        #=============================

        $staff = 0;

        if ( $u['ugroup_sub_acp'] ) $staff = 1;

        if ( $u['id'] == 1 ) $this->trellis->check_perm( 'manage', 'users', 'root' );

        if ( ! $staff && $this->trellis->cache->data['groups'][ $u['ugroup'] ]['g_acp_access'] ) $staff = 1;

        if ( ! $staff && $u['ugroup_sub'] )
        {
            $u['ugroup_sub'] = unserialize( $u['ugroup_sub'] );

            if ( ! empty( $u['ugroup_sub'] ) )
            {
                foreach ( $u['ugroup_sub'] as $gid )
                {
                    if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $staff = 1;

                    break;
                }
            }
        }

        if ( $staff ) $this->trellis->check_perm( 'manage', 'users', 'staff' );

        #=============================
        # Update User
        #=============================

        $db_array = array(
                          'signature'    => $this->trellis->input['signature'],
                          'sig_auto'    => $this->trellis->input['sig_auto'],
                          );

        $this->trellis->func->users->edit( $db_array, $u['id'] );

        $this->trellis->log( array( 'msg' => array( 'user_signature', $u['name'] ), 'type' => 'user', 'content_type' => 'user', 'content_id' => $u['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_signature_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $u['id'] ) );
    }

    #=======================================
    # @ Do Approve User
    #=======================================

    private function do_approve()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'users', 'approve' );

        if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_user');

        #=============================
        # Approve User
        #=============================

        if ( ! $this->trellis->func->users->approve( $u['id'], array( 'type' => $this->trellis->input['type'] ) ) ) $this->trellis->skin->error('no_user'); #* appropriate error msg? find simliar and check.

        $this->trellis->log( array( 'msg' => array( 'user_approved_'. $this->trellis->input['type'], $u['name'] ), 'type' => 'user', 'content_type' => 'user', 'content_id' => $u['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_user_approved'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $u['id'] ) );
    }

    #=======================================
    # @ Do Multi Action User
    #=======================================

    private function do_multi_action()
    {
        #=============================
        # Security Checks
        #=============================

        $actions = array(
                         'aa'    => array( 'check' => 'approve', 'func' => 'approve', 'success_msg' => 'users_approved' ),
                         'ae'    => array( 'check' => 'approve', 'func' => 'approve', 'success_msg' => 'users_approved' ),
                         'ab'    => array( 'check' => 'approve', 'func' => 'approve', 'success_msg' => 'users_approved' ),
                         );

        if ( ! $actions[ $this->trellis->input['mu_action'] ] ) $this->list_users(); #* output error msg?

        if ( ! is_array( $this->trellis->input['mau'] ) || empty( $this->trellis->input['mau'] ) ) $this->list_users(); #* output error msg? no users selected.

        if ( $actions[ $this->trellis->input['mu_action'] ]['check'] ) $this->trellis->check_perm( 'manage', 'users', $actions[ $this->trellis->input['mu_action'] ]['check'] );

        #=============================
        # Do Multi Action
        #=============================

        #* we can improve this method. this way uses a lot of queries.

        $func = $actions[ $this->trellis->input['mu_action'] ]['func'];

        if ( $func == 'approve' )
        {
            if ( $this->trellis->input['mu_action'] == 'aa' )
            {
                $type = 'admin';
            }
            elseif ( $this->trellis->input['mu_action'] == 'ae' )
            {
                $type = 'email';
            }
            elseif ( $this->trellis->input['mu_action'] == 'ab' )
            {
                $type = 'both';
            }
        }

        foreach( $this->trellis->input['mau'] as $id )
        {
            if ( ! $u = $this->trellis->func->users->get_single_by_id( array( 'id', 'name' ), $id ) ) continue; #* appropriate error msg?

            if ( $func == 'approve' )
            {
                $this->trellis->func->users->approve( $u['id'], array( 'type' => $type ) ); #* capture errors? not sure if its a good idea cause ex: user selects to multi close tickets and some tickets may already be closed.

                $this->trellis->log( array( 'msg' => array( 'user_approved_'. $type, $u['name'] ), 'type' => 'user', 'content_type' => 'user', 'content_id' => $u['id'] ) );
            }
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang[ 'alert_'. $actions[ $this->trellis->input['mu_action'] ]['success_msg'] ] );

        if ( $func == 'approve' )
        {
            $this->trellis->skin->redirect( array( 'act' => 'pending' ) );
        }
        else
        {
            $this->trellis->skin->redirect( array( 'act' => null ) );
        }
    }

    #=======================================
    # @ Generate URL
    #=======================================

    private function generate_url($params=array())
    {
        $url = '<! TD_URL !>/admin.php?section=manage&amp;page=users';

        if ( ! isset( $params['act'] ) ) $params['act'] = $this->trellis->input['act'];
        if ( ! isset( $params['sort'] ) ) $params['sort'] = $this->trellis->input['sort'];
        if ( ! isset( $params['order'] ) ) $params['order'] = $this->trellis->input['order'];
        if ( ! isset( $params['search'] ) ) $params['search'] = $this->trellis->input['search'];
        if ( ! isset( $params['field'] ) ) $params['field'] = $this->trellis->input['field'];
        if ( ! isset( $params['st'] ) ) $params['st'] = $this->trellis->input['st'];

        if ( $params['act'] ) $url .= '&amp;act='. $params['act'];

        if ( $params['sort'] ) $url .= '&amp;sort='. $params['sort'];

        if ( $params['order'] ) $url .= '&amp;order='. $params['order'];

        if ( $params['search'] ) $url .= '&amp;search='. $params['search'];

        if ( $params['field'] ) $url .= '&amp;field='. $params['field'];

        if ( $params['st'] ) $url .= '&amp;st='. $params['st'];

        return $url;
    }

}

?>