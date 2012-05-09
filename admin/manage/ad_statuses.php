<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_statuses {

    private $output = "";

    private $types = array(
                            2 => '{lang.open}',
                            4 => '{lang.hold}',
                            5 => '{lang.aua}',
                            6 => '{lang.closed}',
                            );

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'statuses' );

        $this->trellis->load_functions('statuses');
        $this->trellis->load_lang('statuses');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_statuses();
            break;
            case 'add':
                $this->add_status();
            break;
            case 'edit':
                $this->edit_status();
            break;
            case 'delete':
                $this->delete_status();
            break;
            case 'reorder':
                $this->reorder_statuses();
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
            case 'default':
                $this->do_default();
            break;
            case 'doreorder':
                $this->do_reorder();
            break;

            default:
                $this->list_statuses();
            break;
        }
    }

    #=======================================
    # @ List Statuses
    #=======================================

    private function list_statuses()
    {
        $this->types[1] = '{lang.new}';
        $this->types[3] = '{lang.progress}';
        $this->types[5] = '{lang.aua_short}';

        #=============================
        # Grab Statuses
        #=============================

        $status_rows = "";

        if ( ! $statuses = $this->trellis->func->statuses->get( array( 'select' => array( 'id', 'name_staff', 'name_user', 'type', 'default' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $status_rows .= "<tr><td class='bluecell-light' colspan='6'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=add'>{lang.no_statuses}</a></strong></td></tr>";
        }
        else
        {
            foreach( $statuses as $sid => $s )
            {
                if ( $s['id'] == 1 || $s['id'] == 3 )
                {
                    $s['default_button'] = '{lang.na}';
                }
                elseif ( ! $s['default'] )
                {
                    $s['default_button'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=default&amp;id={$s['id']}&amp;type={$s['type']}'>{$s['default']}</a>";
                    $s['delete_button'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=delete&amp;id={$s['id']}'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a>";
                }

                $s['type'] = $this->types[ $s['type'] ];

                $status_rows .= "<tr>
                                    <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fstatus". urlencode('[]') ."={$s['id']}'><strong>{$s['id']}</strong></a></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fstatus". urlencode('[]') ."={$s['id']}'>{$s['name_staff']}</a></td>
                                    <td class='bluecellthin-light' align='center'>{$s['type']}</td>
                                    <td class='bluecellthin-light' align='center'>{$s['default_button']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=edit&amp;id={$s['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'>{$s['delete_button']}</td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.statuses_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='88%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='1%' align='center'>{lang.type}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.default}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $status_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=add' ),
                            array( 'arrow_switch', '{lang.menu_reorder}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=reorder' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=statuses' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_statuses_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_statuses_title}', '{lang.help_about_statuses_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Status
    #=======================================

    private function add_status($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=doadd", 'add_status', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_status}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name_staff} '. $this->trellis->skin->help_tip('{lang.tip_name_staff}'), $this->trellis->skin->textfield( 'name_staff' ), 'a', '22%', '78%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.abbr_staff} '. $this->trellis->skin->help_tip('{lang.tip_abbr_staff}'), $this->trellis->skin->textfield( 'abbr_staff', '', '', 0, 12 ) .' {lang.can_be_blank}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name_user} '. $this->trellis->skin->help_tip('{lang.tip_name_user}'), $this->trellis->skin->textfield( 'name_user' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.abbr_user} '. $this->trellis->skin->help_tip('{lang.tip_abbr_user}'), $this->trellis->skin->textfield( 'abbr_user', '', '', 0, 12 ) .' {lang.can_be_blank}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.type} '. $this->trellis->skin->help_tip('{lang.tip_type}'), $this->trellis->skin->custom_radio( 'type', $this->types ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_status}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name_staff'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 'name_user'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_statuses_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Status
    #=======================================

    private function edit_status($error="")
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'edit' );

        #=============================
        # Grab Status
        #=============================

        if ( ! $s = $this->trellis->func->statuses->get_single_by_id( array( 'id', 'name_staff', 'name_user', 'abbr_staff', 'abbr_user', 'type' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_status');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        if ( $s['type'] == 1 )
        {
            $type_html = '{lang.new}';
        }
        elseif ( $s['type'] == 3 )
        {
            $type_html = '{lang.in_progress}';
        }
        else
        {
            $type_html = $this->trellis->skin->custom_radio( 'type', $this->types, $s['type'] );
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=doedit&amp;id={$s['id']}", 'edit_status', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_status}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name_staff} '. $this->trellis->skin->help_tip('{lang.tip_name_staff}'), $this->trellis->skin->textfield( 'name_staff', $s['name_staff'] ), 'a', '22%', '78%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.abbr_staff} '. $this->trellis->skin->help_tip('{lang.tip_abbr_staff}'), $this->trellis->skin->textfield( 'abbr_staff', $s['abbr_staff'],  '', 0, 12 ) .' {lang.can_be_blank}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name_user} '. $this->trellis->skin->help_tip('{lang.tip_name_user}'), $this->trellis->skin->textfield( 'name_user', $s['name_user'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.abbr_user} '. $this->trellis->skin->help_tip('{lang.tip_abbr_user}'), $this->trellis->skin->textfield( 'abbr_user', $s['abbr_user'], '', 0, 12 ) .' {lang.can_be_blank}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.type} '. $this->trellis->skin->help_tip('{lang.tip_type}'), $type_html, 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_status}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name_staff'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 'name_user'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_statuses_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Delete Status Form
    #=======================================

    private function delete_status()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'delete' );

        if ( ! $s = $this->trellis->func->statuses->get_single_by_id( array( 'id', 'name_staff', 'default' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_status');

        if ( $s['default'] ) $this->list_statuses( 'cannot_delete_default' );

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=dodel&amp;id={$s['id']}", 'delete_status', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.deleting_status} '. $s['name'] ) ."
                        ". $this->trellis->skin->group_sub( '{lang.delete_status_tickets_qs}' ) ."
                        <div class='option1'><input type='radio' name='action' id='action1' value='1' checked='checked' /> <label for='action1'>{lang.change_tickets_to_status}</label> <select name='changeto'>". $this->trellis->func->drop_downs->status_drop( array( 'exclude' => $s['id'] ) ) ."</select></div>
                        <div class='option2'><input type='radio' name='action' id='action2' value='2' /> <label for='action2'>{lang.delete_tickets}</label></div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_delete_status}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_statuses_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Reorder Statuses
    #=======================================

    private function reorder_statuses()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'reorder' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=doreorder", 'reorder_statuses', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.reordering_statuses}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.reorder_statuses_msg}' ) ."
                        <input type='hidden' name='order' id='order' value='' />";

        if ( ! $statuses = $this->trellis->func->statuses->get( array( 'select' => array( 'id', 'name_staff' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $this->output .= "<div class='bluecell-light'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=add'>{lang.no_statuses}</a></strong></div>";
        }
        else
        {
            $this->output .= "<ul class='draggable' id='sortable'>";

            foreach( $statuses as $sid => $s )
            {
                $this->output .= "<li class='bluecell-light' id='s_{$s['id']}'>{$s['name_staff']}</li>";
            }

            $this->output .= "</ul>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'reorder', '{lang.button_reorder_statuses}' ) ) ."
                        </div>
                        <script type='text/javascript' language='javascript'>
                            $(function() {
                                $('#sortable').sortable({
                                    stop: function() {
                                        $('#order').val( $('#sortable').sortable('serialize') );
                                    }
                                });
                            });
                        </script>";
        }

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=statuses&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_statuses_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add Status
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'add' );

        if ( ! $this->trellis->input['name_staff'] ) $this->add_status('no_name_staff');
        if ( ! $this->trellis->input['name_user'] ) $this->add_status('no_name_user');

        #=============================
        # Add Status
        #=============================

        $db_array = array(
                          'name_staff'    => $this->trellis->input['name_staff'],
                          'name_user'    => $this->trellis->input['name_user'],
                          'abbr_staff'    => $this->trellis->input['abbr_staff'],
                          'abbr_user'    => $this->trellis->input['abbr_user'],
                          'type'        => $this->trellis->input['type'],
                          );

        $status_id = $this->trellis->func->statuses->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'status_added', $this->trellis->input['name_staff'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->statuses_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_status_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Status
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'edit' );

        if ( ! $this->trellis->input['name_staff'] ) $this->edit_status('no_name_staff');
        if ( ! $this->trellis->input['name_user'] ) $this->edit_status('no_name_user');

        #=============================
        # Update Status
        #=============================

        if ( $this->trellis->input['id'] == 1 ) $this->trellis->input['type'] = 1;
        if ( $this->trellis->input['id'] == 3 ) $this->trellis->input['type'] = 3;

        $db_array = array(
                          'name_staff'    => $this->trellis->input['name_staff'],
                          'name_user'    => $this->trellis->input['name_user'],
                          'abbr_staff'    => $this->trellis->input['abbr_staff'],
                          'abbr_user'    => $this->trellis->input['abbr_user'],
                          'type'        => $this->trellis->input['type'],
                          );

        $this->trellis->func->statuses->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'status_edited', $this->trellis->input['name_staff'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->statuses_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_status_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Status
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'delete' );

        if ( $this->trellis->cache->data['statuses'][ $this->trellis->input['id'] ]['default'] ) $this->list_statuses( 'cannot_delete_default' );

        #=============================
        # DELETE Status
        #=============================

        $this->trellis->func->statuses->delete( $this->trellis->input['id'], $this->trellis->input['action'], $this->trellis->input['changeto'] );

        $this->trellis->log( array( 'msg' => array( 'status_deleted', $this->trellis->cache->data['statuses'][ $this->trellis->input['id'] ]['name_staff'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->statuses_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_status_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Default Status
    #=======================================

    private function do_default()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'edit' );

        #=============================
        # Default Status
        #=============================

        $this->trellis->func->statuses->set_default( $this->trellis->input['id'], $this->trellis->input['type'] );

        $this->trellis->log( array( 'msg' => array( 'status_default', $this->trellis->cache->data['statuses'][ $this->trellis->input['id'] ]['name_staff'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->statuses_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_status_default'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Reorder Statuses
    #=======================================

    private function do_reorder()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'statuses', 'reorder' );

        #=============================
        # Reorder Statuses
        #=============================

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['order'] ), $order );

        if ( empty( $order['s'] ) ) $this->list_statuses( 'no_reorder' );

        if ( $statuses = $this->trellis->func->statuses->get( array( 'select' => array( 'id', 'position' ) ) ) )
        {
            foreach ( $order['s'] as $position => $sid )
            {
                $position ++;

                if ( $position != $statuses[ $sid ]['position'] )
                {
                    $this->trellis->func->statuses->edit( array( 'position' => $position ), $sid );
                }
            }
        }

        $this->trellis->log( array( 'msg' => 'statuses_reordered', 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->statuses_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_statuses_reordered'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

}

?>