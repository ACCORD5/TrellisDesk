<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_departs {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'departs' );

        $this->trellis->load_functions('departs');
        $this->trellis->load_lang('departs');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_departs();
            break;
            case 'reorder':
                $this->reorder_departs();
            break;
            case 'add':
                $this->add_depart();
            break;
            case 'edit':
                $this->edit_depart();
            break;
            case 'delete':
                $this->delete_depart();
            break;

            case 'doreorder':
                $this->do_reorder();
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

            default:
                $this->list_departs();
            break;
        }
    }

    #=======================================
    # @ List Departmenets
    #=======================================

    private function list_departs()
    {
        #=============================
        # Grab Departments
        #=============================

        $depart_rows = '';

        if ( ! $departs = $this->trellis->func->departs->get( array( 'select' => array( 'id', 'name', 'description', 'tickets_total' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $depart_rows .= "<tr><td class='bluecell-light' colspan='6'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=add'>{lang.no_departs}</a></strong></td></tr>";
        }
        else
        {
            foreach( $departs as $did => $d )
            {
                if ( ! $d['description'] ) $d['description'] = '<i>{lang.no_description}</i>';

                $depart_rows .= "<tr>
                                    <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fdepart". urlencode('[]') ."={$d['id']}'><strong>{$d['id']}</strong></a></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fdepart". urlencode('[]') ."={$d['id']}'>{$d['name']}</a></td>
                                    <td class='bluecellthin-light' style='font-weight: normal'>{$d['description']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;depart={$d['id']}'><strong>{$d['tickets_total']}</strong></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=edit&amp;id={$d['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=delete&amp;id={$d['id']}'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.departs_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='30%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='54%' align='left'>{lang.description}</th>
                            <th class='bluecellthin-th' width='8%' align='center'>{lang.tickets}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $depart_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=add' ),
                            array( 'arrow_switch', '{lang.menu_reorder}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=reorder' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=departs' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_departs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_departs_title}', '{lang.help_about_departs_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Department Form
    #=======================================

    private function add_depart($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'departs', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->trellis->load_functions('drop_downs');

        $group_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $group_count = 0;
        $group_total = count( $this->trellis->cache->data['groups'] );

        foreach( $this->trellis->cache->data['groups'] as $gid => $g )
        {
            $group_count ++;

            if ( $group_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $group_count & 1 )
            {
                if ( $group_count == $group_total )
                {
                    $group_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'] ) ."</td></tr>";
                }
                else
                {
                    $group_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'] ) ."</td>";
                }
            }
            else
            {
                $group_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'] ) ."</td></tr>";
            }
        }

        $group_perms_html .= "</table>";

        $this->output .= "<script type='text/javascript'>

                        function ckCol(type)
                        {
                            $('input:checkbox').each( function() {
                                if ( this.name.search('^agp_' + type + '_') != -1 ) this.checked = 1
                            } );
                        }

                        function unckCol(type)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^agp_' + type + '_') != -1 ) this.checked = 0
                            } );
                        }

                        function ckRow(did)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^agp_[a-z]+_' + did + '$') != -1 ) this.checked = 1
                            } );
                        }

                        function unckRow(did)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^agp_[a-z]+_' + did + '$') != -1 ) this.checked = 0
                            } );
                        }

                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=doadd", 'add_depart', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_depart}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}',$this->trellis->skin->textfield( 'name' ),'a','28%','72%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description} '. $this->trellis->skin->help_tip('{lang.tip_description}'), $this->trellis->skin->textarea( 'description', '', '', 0, 60, 3 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.auto_assign} '. $this->trellis->skin->help_tip('{lang.tip_auto_assign}'), "<select name='assign_auto' id='assign_auto'>". $this->trellis->func->drop_downs->staff_drop( array( 'select' => $this->trellis->input['assign_auto'], 'type' => 'fcbkc' ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.auto_assign_move} '. $this->trellis->skin->help_tip('{lang.tip_auto_assign_move}'), $this->trellis->skin->yes_no_radio( array( 'name' => 'assign_move' ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.escalation_options}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.enable_escalation} '. $this->trellis->skin->help_tip('{lang.tip_escalation}'), $this->trellis->skin->yes_no_radio( array( 'name' => 'escalate_enable', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['escalate'], 'for' => 1, 'msg' => '{lang.warn_escalation}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_user_escalation}', $this->trellis->skin->yes_no_radio( 'escalate_user' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.escalate_wait_time} '. $this->trellis->skin->help_tip('{lang.tip_escalate_wait}'), $this->trellis->skin->textfield( 'escalate_wait', '', '', 0, 5 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.escalate_to} '. $this->trellis->skin->help_tip('{lang.tip_escalate_to}'), "<select name='escalate_depart' id='escalate_depart'><option value='0'>{lang.do_not_move}</option>". $this->trellis->func->drop_downs->dprt_drop( $this->trellis->input['escalate_depart'], 0, 2 ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.escalate_assign} '. $this->trellis->skin->help_tip('{lang.tip_escalate_assign}'), "<select name='escalate_assign' id='escalate_assign'>". $this->trellis->func->drop_downs->staff_drop( array( 'select' => $this->trellis->input['escalate_assign'], 'type' => 'fcbkc' ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.close_options}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.close_own_tickets} '. $this->trellis->skin->help_tip('{lang.tip_close_own_tickets}'), $this->trellis->skin->yes_no_radio( 'close_own' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.close_auto_wait_time} '. $this->trellis->skin->help_tip('{lang.tip_close_auto}'), $this->trellis->skin->textfield( 'close_auto', '', '', 0, 5 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.reopen_own_tickets} '. $this->trellis->skin->help_tip('{lang.tip_reopen_own_tickets}'), $this->trellis->skin->yes_no_radio( 'reopen_own' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.permissions}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_attachments} '. $this->trellis->skin->help_tip('{lang.tip_attachments}'), $this->trellis->skin->yes_no_radio( array( 'name' => 'allow_attach', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['attachments'], 'for' => 1, 'msg' => '{lang.warn_attachments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_rating} '. $this->trellis->skin->help_tip('{lang.tip_rating}'), $this->trellis->skin->yes_no_radio( array( 'name' => 'allow_rating', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['rating'], 'for' => 1, 'msg' => '{lang.warn_rating}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.group_permissions} '. $this->trellis->skin->help_tip('{lang.tip_group_perms}'), $group_perms_html, 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' );

        if ( $this->trellis->user['id'] == 1 )
        {
            $group_table_rows = '';

            foreach( $this->trellis->cache->data['groups'] as $gid => $g )
            {
                if ( $g['g_acp_access'] && $g['g_id'] != 2 )
                {
                    $group_table_rows .= "<tr>
                                        <td class='bluecellthin-light'><div style='float:right'><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='cursor:pointer' onclick=\"ckRow('". $g['g_id'] ."')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='cursor:pointer' onclick=\"unckRow('". $g['g_id'] ."')\" /></div>{$g['g_name']}</td>
                                        <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_v_'. $g['g_id'], 'V' ), '{lang.tip_ticket_view}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_r_'. $g['g_id'], 'R' ), '{lang.tip_ticket_reply}' ) ."</td>
                                        <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_et_'. $g['g_id'], 'T' ), '{lang.tip_ticket_edit}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_er_'. $g['g_id'], 'R' ), '{lang.tip_reply_edit}' ) ."</td>
                                        <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_mv_'. $g['g_id'], 'M' ), '{lang.tip_ticket_move}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_es_'. $g['g_id'], 'E' ), '{lang.tip_ticket_escalate}' ) ."</td>
                                        <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_as_'. $g['g_id'], 'S' ), '{lang.tip_assign_self}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_aa_'. $g['g_id'], 'A' ), '{lang.tip_assign_all}' ) ."</td>
                                        <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_c_'. $g['g_id'], 'C' ), '{lang.tip_ticket_close}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_ro_'. $g['g_id'], 'RO' ), '{lang.tip_ticket_reopen}' ) ."</td>
                                        <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_dt_'. $g['g_id'], 'T' ), '{lang.tip_ticket_delete}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_dr_'. $g['g_id'], 'R' ), '{lang.tip_reply_delete}' ) ."</td>
                                    </tr>";
                }
            }

            $this->output .= "<div id='acp_depart_perm' style='margin-top:12px'>
                        ". $this->trellis->skin->start_group_table( '{lang.staff_depart_perms}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='28%' align='left'>{lang.group}</th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.view_reply}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('v')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('v')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('r')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('r')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.edit}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('et')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('et')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('er')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('er')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.move}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('mv')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('mv')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('es')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('es')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.assign}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('as')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('as')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('aa')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('aa')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.close}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('c')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('c')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('ro')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('ro')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.delete}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('dt')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('dt')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('dr')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('dr')\" /></th>
                        </tr>
                        ". $group_table_rows ."
                        ". $this->trellis->skin->end_group_table() ."

                        </div>";
        }

        $this->output .= $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_depart}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $this->output .= "<script type='text/javascript'>
                        $(function() {
                            $('#assign_auto').fcbkcomplete( {
                                json_url: '<! TD_URL !>/admin.php?act=lookup&type=staff'
                            });
                        });
                        $(function() {
                            $('#escalate_assign').fcbkcomplete( {
                                json_url: '<! TD_URL !>/admin.php?act=lookup&type=staff'
                            });
                        });
                        </script>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_departs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_skin_javascript( 'fcbkcomplete.js' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Department Form
    #=======================================

    private function edit_depart($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'departs', 'edit' );

        if ( ! $d = $this->trellis->func->departs->get_single_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_depart');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;

            $assign_auto = $this->trellis->input['assign_auto'];
            $escalate_depart = $this->trellis->input['escalate_depart'];
            $escalate_assign = $this->trellis->input['escalate_assign'];
        }
        else
        {
            $assign_auto = unserialize( $d['assign_auto'] );
            $escalate_assign = unserialize( $d['escalate_assign'] );
            $escalate_depart = $d['escalate_depart'];
        }

        $this->trellis->load_functions('drop_downs');
        $this->trellis->load_functions('groups');

        $groups = $this->trellis->func->groups->get( array( 'select' => array( 'g_id', 'g_name', 'g_depart_perm', 'g_acp_access', 'g_acp_depart_perm' ) ) );

        $group_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $group_count = 0;
        $group_total = count( $groups );

        foreach( $groups as $gid => $g )
        {
            $g['g_depart_perm'] = unserialize( $g['g_depart_perm'] );

            $group_count ++;

            if ( $group_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $group_count & 1 )
            {
                if ( $group_count == $group_total )
                {
                    $group_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_depart_perm'][ $d['id' ] ] ) ."</td></tr>";
                }
                else
                {
                    $group_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_depart_perm'][ $d['id' ] ] ) ."</td>";
                }
            }
            else
            {
                $group_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_depart_perm'][ $d['id' ] ] ) ."</td></tr>";
            }
        }

        $group_perms_html .= "</table>";

        $this->output .= "<script type='text/javascript'>

                        function ckCol(type)
                        {
                            $('input:checkbox').each( function() {
                                if ( this.name.search('^agp_' + type + '_') != -1 ) this.checked = 1
                            } );
                        }

                        function unckCol(type)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^agp_' + type + '_') != -1 ) this.checked = 0
                            } );
                        }

                        function ckRow(did)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^agp_[a-z]+_' + did + '$') != -1 ) this.checked = 1
                            } );
                        }

                        function unckRow(did)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^agp_[a-z]+_' + did + '$') != -1 ) this.checked = 0
                            } );
                        }

                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=doedit&amp;id={$d['id']}", 'add_depart', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_depart} '. $d['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}',$this->trellis->skin->textfield( 'name', $d['name'] ),'a','28%','72%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description} '. $this->trellis->skin->help_tip('{lang.tip_description}'), $this->trellis->skin->textarea( 'description', $d['description'], '', 0, 60, 3 ),'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.auto_assign} '. $this->trellis->skin->help_tip('{lang.tip_auto_assign}'), "<select name='assign_auto' id='assign_auto'>". $this->trellis->func->drop_downs->staff_drop( array( 'select' => $assign_auto, 'type' => 'fcbkc' ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.auto_assign_move} '. $this->trellis->skin->help_tip('{lang.tip_auto_assign_move}'), $this->trellis->skin->yes_no_radio( array( 'name' => 'assign_move', 'value' => $d['assign_move'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.escalation_options}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.enable_escalation} '. $this->trellis->skin->help_tip('{lang.tip_escalation}'), $this->trellis->skin->yes_no_radio( array( 'name' => 'escalate_enable', 'value' => $d['escalate_enable'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['escalate'], 'for' => 1, 'msg' => '{lang.warn_escalation}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_user_escalation}', $this->trellis->skin->yes_no_radio( 'escalate_user', $d['escalate_user'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.escalate_wait_time} '. $this->trellis->skin->help_tip('{lang.tip_escalate_wait}'), $this->trellis->skin->textfield( 'escalate_wait', $d['escalate_wait'], '', 0, 5 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.escalate_to} '. $this->trellis->skin->help_tip('{lang.tip_escalate_to}'), "<select name='escalate_depart' id='escalate_depart'><option value='0'>{lang.do_not_move}</option>". $this->trellis->func->drop_downs->dprt_drop( $escalate_depart, 0, 2 ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.escalate_assign} '. $this->trellis->skin->help_tip('{lang.tip_escalate_assign}'), "<select name='escalate_assign' id='escalate_assign'>". $this->trellis->func->drop_downs->staff_drop( array( 'select' => $escalate_assign, 'type' => 'fcbkc' ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.close_options}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.close_own_tickets} '. $this->trellis->skin->help_tip('{lang.tip_close_own_tickets}'), $this->trellis->skin->yes_no_radio( 'close_own', $d['close_own'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.close_auto_wait_time} '. $this->trellis->skin->help_tip('{lang.tip_close_auto}'), $this->trellis->skin->textfield( 'close_auto', $d['close_auto'], '', 0, 5 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.reopen_own_tickets} '. $this->trellis->skin->help_tip('{lang.tip_reopen_own_tickets}'), $this->trellis->skin->yes_no_radio( 'reopen_own', $d['reopen_own'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.permissions}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_attachments} '. $this->trellis->skin->help_tip('{lang.tip_attachments}'), $this->trellis->skin->yes_no_radio( array( 'name' => 'allow_attach', 'value' => $d['allow_attach'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['attachments'], 'for' => 1, 'msg' => '{lang.warn_attachments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_rating} '. $this->trellis->skin->help_tip('{lang.tip_rating}'), $this->trellis->skin->yes_no_radio( array( 'name' => 'allow_rating', 'value' => $d['allow_rating'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['rating'], 'for' => 1, 'msg' => '{lang.warn_rating}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.group_permissions} '. $this->trellis->skin->help_tip('{lang.tip_group_perms}'), $group_perms_html, 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' );

        if ( $this->trellis->user['id'] == 1 )
        {
            $group_table_rows = '';

            foreach( $groups as $gid => $g )
            {
                if ( $g['g_acp_access'] && $g['g_id'] != 2 )
                {
                    $g['g_acp_depart_perm'] = unserialize( $g['g_acp_depart_perm'] );

                    $group_table_rows .= "<tr>
                                        <td class='bluecellthin-light'><div style='float:right'><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='cursor:pointer' onclick=\"ckRow('". $g['g_id'] ."')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='cursor:pointer' onclick=\"unckRow('". $g['g_id'] ."')\" /></div>{$g['g_name']}</td>
                                        <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_v_'. $g['g_id'], 'V', $g['g_acp_depart_perm'][ $d['id'] ]['v'] ), '{lang.tip_ticket_view}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_r_'. $g['g_id'], 'R', $g['g_acp_depart_perm'][ $d['id'] ]['r'] ), '{lang.tip_ticket_reply}' ) ."</td>
                                        <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_et_'. $g['g_id'], 'T', $g['g_acp_depart_perm'][ $d['id'] ]['et'] ), '{lang.tip_ticket_edit}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_er_'. $g['g_id'], 'R', $g['g_acp_depart_perm'][ $d['id'] ]['er'] ), '{lang.tip_reply_edit}' ) ."</td>
                                        <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_mv_'. $g['g_id'], 'M', $g['g_acp_depart_perm'][ $d['id'] ]['mv'] ), '{lang.tip_ticket_move}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_es_'. $g['g_id'], 'E', $g['g_acp_depart_perm'][ $d['id'] ]['es'] ), '{lang.tip_ticket_escalate}' ) ."</td>
                                        <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_as_'. $g['g_id'], 'S', $g['g_acp_depart_perm'][ $d['id'] ]['as'] ), '{lang.tip_assign_self}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_aa_'. $g['g_id'], 'A', $g['g_acp_depart_perm'][ $d['id'] ]['aa'] ), '{lang.tip_assign_all}' ) ."</td>
                                        <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_c_'. $g['g_id'], 'C', $g['g_acp_depart_perm'][ $d['id'] ]['c'] ), '{lang.tip_ticket_close}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_ro_'. $g['g_id'], 'RO', $g['g_acp_depart_perm'][ $d['id'] ]['ro'] ), '{lang.tip_ticket_reopen}' ) ."</td>
                                        <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_dt_'. $g['g_id'], 'T', $g['g_acp_depart_perm'][ $d['id'] ]['dt'] ), '{lang.tip_ticket_delete}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'agp_dr_'. $g['g_id'], 'R', $g['g_acp_depart_perm'][ $d['id'] ]['dr'] ), '{lang.tip_reply_delete}' ) ."</td>
                                    </tr>";
                }
            }

            $this->output .= "<div id='acp_depart_perm' style='margin-top:12px'>
                        ". $this->trellis->skin->start_group_table( '{lang.staff_depart_perms}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='28%' align='left'>{lang.group}</th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.view_reply}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('v')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('v')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('r')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('r')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.edit}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('et')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('et')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('er')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('er')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.move}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('mv')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('mv')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('es')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('es')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.assign}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('as')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('as')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('aa')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('aa')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.close}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('c')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('c')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('ro')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('ro')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.delete}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('dt')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('dt')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('dr')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('dr')\" /></th>
                        </tr>
                        ". $group_table_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";
        }

        $this->output .= $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_depart}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $this->output .= "<script type='text/javascript'>
                        $(function() {
                            $('#assign_auto').fcbkcomplete( {
                                json_url: '<! TD_URL !>/admin.php?act=lookup&type=staff'
                            });
                        });
                        $(function() {
                            $('#escalate_assign').fcbkcomplete( {
                                json_url: '<! TD_URL !>/admin.php?act=lookup&type=staff'
                            });
                        });
                        </script>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_departs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_skin_javascript( 'fcbkcomplete.js' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Delete Department Form
    #=======================================

    private function delete_depart()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'departs', 'delete' );

        if ( ! $d = $this->trellis->func->departs->get_single_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_depart');

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=dodel&amp;id={$d['id']}", 'delete_depart', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.deleting_depart} '. $d['name'] ) ."
                        ". $this->trellis->skin->group_sub( '{lang.delete_depart_tickets_qs}' ) ."
                        <div class='option1'><input type='radio' name='action' id='action1' value='1' checked='checked' /> <label for='action1'>{lang.move_tickets_to_depart}</label> <select name='moveto'>". $this->trellis->func->drop_downs->dprt_drop( 0, $d['id'], 2 ) ."</select></div>
                        <div class='option2'><input type='radio' name='action' id='action2' value='2' /> <label for='action2'>{lang.delete_tickets}</label></div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_delete_depart}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_departs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Reorder Departmenets
    #=======================================

    private function reorder_departs()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'departs', 'reorder' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=doreorder", 'reorder_departs', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.reordering_departs}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.reorder_departs_msg}' ) ."
                        <input type='hidden' name='order' id='order' value='' />";

        if ( ! $departs = $this->trellis->func->departs->get( array( 'select' => array( 'id', 'name', 'description' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $this->output .= "<div class='bluecell-light'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=add'>{lang.no_departs}</a></strong></div>";
        }
        else
        {
            $this->output .= "<ul class='draggable' id='sortable'>";

            foreach( $departs as $did => $d )
            {
                if ( ! $d['description'] ) $d['description'] = '<i>{lang.no_description}</i>';

                $this->output .= "<li class='bluecell-light' id='d_{$d['id']}'>{$d['name']} <span style='font-weight: normal'>({$d['description']})</span></li>";
            }

            $this->output .= "</ul>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'reorder', '{lang.button_reorder_departs}' ) ) ."
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
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=departs&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_departs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add Department
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'departs', 'add' );

        if ( ! $this->trellis->input['name'] ) $this->add_depart('no_name');

        #=============================
        # Add Department
        #=============================

        $db_array = array(
                          'name'                => $this->trellis->input['name'],
                          'description'            => $this->trellis->input['description'],
                          'assign_auto'            => serialize( $this->trellis->input['assign_auto'] ),
                          'assign_move'            => $this->trellis->input['assign_move'],
                          'escalate_enable'        => $this->trellis->input['escalate_enable'],
                          'escalate_user'        => $this->trellis->input['escalate_user'],
                          'escalate_wait'        => $this->trellis->input['escalate_wait'],
                          'escalate_depart'        => $this->trellis->input['escalate_depart'],
                          'escalate_assign'        => serialize( $this->trellis->input['escalate_assign'] ),
                          'close_auto'            => $this->trellis->input['close_auto'],
                          'close_own'            => $this->trellis->input['close_own'],
                          'reopen_own'            => $this->trellis->input['reopen_own'],
                          'allow_attach'        => $this->trellis->input['allow_attach'],
                          'allow_rating'        => $this->trellis->input['allow_rating'],
                         );

        $depart_id = $this->trellis->func->departs->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'depart_added', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Generate Permissions
        #=============================

        $this->trellis->load_functions('groups');

        $groups = $this->trellis->func->groups->get( array( 'select' => array( 'g_id', 'g_depart_perm', 'g_acp_access', 'g_acp_depart_perm' ) ) );

        foreach ( $groups as $gid => $g )
        {
            $new_perms = unserialize( $g['g_depart_perm'] );

            $new_perms[ $depart_id ] = intval( $this->trellis->input[ 'gp_'. $g['g_id'] ] );

            if ( $this->trellis->user['id'] == 1 && $g['g_id'] != 2 && $g['g_acp_access'] )
            {
                $new_acp_perms = unserialize( $g['g_acp_depart_perm'] );

                $new_acp_perms[ $depart_id ]['v'] = intval( $this->trellis->input[ 'agp_v_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['r'] = intval( $this->trellis->input[ 'agp_r_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['et'] = intval( $this->trellis->input[ 'agp_et_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['er'] = intval( $this->trellis->input[ 'agp_er_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['mv'] = intval( $this->trellis->input[ 'agp_mv_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['es'] = intval( $this->trellis->input[ 'agp_es_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['as'] = intval( $this->trellis->input[ 'agp_as_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['aa'] = intval( $this->trellis->input[ 'agp_aa_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['c'] = intval( $this->trellis->input[ 'agp_c_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['ro'] = intval( $this->trellis->input[ 'agp_ro_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['dt'] = intval( $this->trellis->input[ 'agp_dt_'. $g['g_id'] ] );
                $new_acp_perms[ $depart_id ]['dr'] = intval( $this->trellis->input[ 'agp_dr_'. $g['g_id'] ] );

                $this->trellis->func->groups->edit( array( 'g_depart_perm' => $new_perms, 'g_acp_depart_perm' => $new_acp_perms ), $g['g_id'] );
            }
            else
            {
                $this->trellis->func->groups->edit( array( 'g_depart_perm' => $new_perms ), $g['g_id'] );
            }
        }

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->departs_cache();
        $this->trellis->func->rebuild->groups_cache(); # TODO: necessary to rebuild when no group selected?

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_depart_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Department
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'departs', 'edit' );

        if ( ! $this->trellis->input['name'] ) $this->edit_depart('no_name');

        #=============================
        # Edit Department
        #=============================

        $db_array = array(
                          'name'                => $this->trellis->input['name'],
                          'description'            => $this->trellis->input['description'],
                          'assign_auto'            => serialize( $this->trellis->input['assign_auto'] ),
                          'assign_move'            => $this->trellis->input['assign_move'],
                          'escalate_enable'        => $this->trellis->input['escalate_enable'],
                          'escalate_user'        => $this->trellis->input['escalate_user'],
                          'escalate_wait'        => $this->trellis->input['escalate_wait'],
                          'escalate_depart'        => $this->trellis->input['escalate_depart'],
                          'escalate_assign'        => serialize( $this->trellis->input['escalate_assign'] ),
                          'close_auto'            => $this->trellis->input['close_auto'],
                          'close_own'            => $this->trellis->input['close_own'],
                          'reopen_own'            => $this->trellis->input['reopen_own'],
                          'allow_attach'        => $this->trellis->input['allow_attach'],
                          'allow_rating'        => $this->trellis->input['allow_rating'],
                         );

        $this->trellis->func->departs->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'depart_edited', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Generate Permissions
        #=============================

        $this->trellis->load_functions('groups');

        $groups = $this->trellis->func->groups->get( array( 'select' => array( 'g_id', 'g_depart_perm', 'g_acp_access', 'g_acp_depart_perm' ) ) );

        foreach ( $groups as $gid => $g )
        {
            $new_perms = unserialize( $g['g_depart_perm'] );

            $new_perms[ $this->trellis->input['id'] ] = intval( $this->trellis->input[ 'gp_'. $g['g_id'] ] );

            if ( $this->trellis->user['id'] == 1 && $g['g_id'] != 2 && $g['g_acp_access'] )
            {
                $new_acp_perms = unserialize( $g['g_acp_depart_perm'] );

                $new_acp_perms[ $this->trellis->input['id'] ]['v'] = intval( $this->trellis->input[ 'agp_v_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['r'] = intval( $this->trellis->input[ 'agp_r_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['et'] = intval( $this->trellis->input[ 'agp_et_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['er'] = intval( $this->trellis->input[ 'agp_er_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['mv'] = intval( $this->trellis->input[ 'agp_mv_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['es'] = intval( $this->trellis->input[ 'agp_es_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['as'] = intval( $this->trellis->input[ 'agp_as_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['aa'] = intval( $this->trellis->input[ 'agp_aa_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['c'] = intval( $this->trellis->input[ 'agp_c_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['ro'] = intval( $this->trellis->input[ 'agp_ro_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['dt'] = intval( $this->trellis->input[ 'agp_dt_'. $g['g_id'] ] );
                $new_acp_perms[ $this->trellis->input['id'] ]['dr'] = intval( $this->trellis->input[ 'agp_dr_'. $g['g_id'] ] );

                $this->trellis->func->groups->edit( array( 'g_depart_perm' => $new_perms, 'g_acp_depart_perm' => $new_acp_perms ), $g['g_id'] );
            }
            else
            {
                $this->trellis->func->groups->edit( array( 'g_depart_perm' => $new_perms ), $g['g_id'] );
            }
        }

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->departs_cache();
        $this->trellis->func->rebuild->groups_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_depart_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Department
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'departs', 'delete' );

        #=============================
        # DELETE Department
        #=============================

        $this->trellis->func->departs->delete( $this->trellis->input['id'], $this->trellis->input['action'], $this->trellis->input['moveto'] );

        $this->trellis->log( array( 'msg' => array( 'depart_deleted', $this->trellis->cache->data['departs'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->departs_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_depart_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Reorder Departments
    #=======================================

    private function do_reorder()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'departs', 'reorder' );

        #=============================
        # Reorder Departments
        #=============================

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['order'] ), $order );

        if ( empty( $order['d'] ) ) $this->list_departs( 'no_reorder' );

        if ( $departs = $this->trellis->func->departs->get( array( 'select' => array( 'id', 'position' ) ) ) )
        {
            foreach ( $order['d'] as $position => $did )
            {
                $position ++;

                if ( $position != $departs[ $did ]['position'] )
                {
                    $this->trellis->func->departs->edit( array( 'position' => $position ), $did );
                }
            }
        }

        $this->trellis->log( array( 'msg' => 'departs_reordered', 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->departs_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_departs_reordered'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

}

?>