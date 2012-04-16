<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_groups {

    private $output = "";
    private $protected_groups = array( 1, 2, 3, 4, 5 );

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'groups' );

        $this->trellis->load_functions('groups');
        $this->trellis->load_lang('groups');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_groups();
            break;
            case 'add':
                $this->add_group();
            break;
            case 'edit':
                $this->edit_group();
            break;
            case 'delete':
                $this->delete_group();
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
                $this->list_groups();
            break;
        }
    }

    #=======================================
    # @ List Groups
    #=======================================

    private function list_groups()
    {
        #=============================
        # Grab Groups
        #=============================

        $group_table_rows = '';

        $groups = $this->trellis->func->groups->get( array( 'select' => array( 'g_id', 'g_name', 'g_users' ), 'order' => array( 'g_id' => 'asc' ) ) );

        foreach( $groups as $gid => $g )
        {
            ( in_array( $gid, $this->protected_groups ) ) ? $delete_button = "" : $delete_button = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=groups&amp;act=delete&amp;id={$g['g_id']}'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a>";

            $group_table_rows .= "<tr>
                                <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=user&amp;act=list&amp;group={$g['g_id']}'><strong>{$g['g_id']}</strong></a></td>
                                <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=user&amp;act=list&amp;group={$g['g_id']}'>{$g['g_name']}</a></td>
                                <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=user&amp;act=list&amp;group={$g['g_id']}'><strong>{$g['g_users']}</strong></a></td>
                                <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=groups&amp;act=edit&amp;id={$g['g_id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                <td class='bluecellthin-light' align='center'>{$delete_button}</td>
                            </tr>";
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.group_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='72%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='20%' align='center'>{lang.users}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $group_table_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=groups&amp;act=add' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=groups' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_group_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_groups_title}', '{lang.help_about_groups_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Group Form
    #=======================================

    private function add_group($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'groups', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;

            #=============================
            # Permissions
            #=============================

            if ( $this->trellis->user['id'] == 1 )
            {
                $acp_perms_manage = array();
                $acp_perms_look = array();
                $acp_perms_tools = array();

                if ( is_array( $this->trellis->input['acp_perms_manage'] ) )
                {
                    foreach( $this->trellis->input['acp_perms_manage'] as $perm )
                    {
                        $acp_perms_manage[ 'manage_'. $perm ] = 1;
                    }
                }
                if ( is_array( $this->trellis->input['acp_perms_look'] ) )
                {
                    foreach( $this->trellis->input['acp_perms_look'] as $perm )
                    {
                        $acp_perms_look[ 'look_'. $perm ] = 1;
                    }
                }
                if ( is_array( $this->trellis->input['acp_perms_tools'] ) )
                {
                    foreach( $this->trellis->input['acp_perms_tools'] as $perm )
                    {
                        $acp_perms_tools[ 'tools_'. $perm ] = 1;
                    }
                }

                $acp_perms = array_merge( $acp_perms_manage, $acp_perms_look, $acp_perms_tools );
            }
        }

        $depart_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $depart_count = 0;
        $depart_total = count( $this->trellis->cache->data['departs'] );

        foreach( $this->trellis->cache->data['departs'] as $did => $d )
        {
            $depart_count ++;

            if ( $depart_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $depart_count & 1 )
            {
                if ( $depart_count == $depart_total )
                {
                    $depart_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'] ) ."</td></tr>";
                }
                else
                {
                    $depart_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'] ) ."</td>";
                }
            }
            else
            {
                $depart_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'] ) ."</td></tr>";
            }
        }

        $depart_perms_html .= "</table>";

        $this->trellis->load_functions('drop_downs');

        $this->output .= "<script type='text/javascript'>

                        function ckCol(type)
                        {
                            $('input:checkbox').each( function() {
                                if ( this.name.search('^adp_' + type + '_') != -1 ) this.checked = 1
                            } );
                        }

                        function unckCol(type)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^adp_' + type + '_') != -1 ) this.checked = 0
                            } );
                        }

                        function ckRow(did)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^adp_[a-z]+_' + did + '$') != -1 ) this.checked = 1
                            } );
                        }

                        function unckRow(did)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^adp_[a-z]+_' + did + '$') != -1 ) this.checked = 0
                            } );
                        }

                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=groups&amp;act=doadd", 'add_group', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_group}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}',             $this->trellis->skin->textfield( 'g_name' ),                            'a', '30%', '70%' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.ticket_center}' ) ."
                        ". $this->trellis->skin->group_table_full_row( '{lang.staff_ticket_perms_msg}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.ticket_center_access} *',     $this->trellis->skin->yes_no_radio( 'g_ticket_access' ),            'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.create_new_tickets}',     $this->trellis->skin->yes_no_radio( array( 'name' => 'g_ticket_create', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['new_tickets'], 'for' => 1, 'msg' => '{lang.warn_ticket_create}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.can_escalate_tickets}', $this->trellis->skin->yes_no_radio( array( 'name' => 'g_ticket_escalate', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['escalate'], 'for' => 1, 'msg' => '{lang.warn_ticket_escalate}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_own_tickets}',         $this->trellis->skin->yes_no_radio( 'g_ticket_edit' ),                'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.close_own_tickets}',     $this->trellis->skin->yes_no_radio( 'g_ticket_close' ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.reopen_own_tickets}',     $this->trellis->skin->yes_no_radio( 'g_ticket_reopen' ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_own_replies}',         $this->trellis->skin->yes_no_radio( 'g_reply_edit' ),                'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_own_replies}',     $this->trellis->skin->yes_no_radio( 'g_reply_delete' ),            'a' );

        $this->output .= $this->trellis->skin->group_table_row( '{lang.rate_staff_replies}', $this->trellis->skin->yes_no_radio( array( 'name' => 'g_reply_rate', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['rating'], 'for' => 1, 'msg' => '{lang.warn_ticket_rating}' ) ) ), 'a' );

        $this->output .= $this->trellis->skin->group_table_row( '{lang.allow_attachments}',         $this->trellis->skin->yes_no_radio( array( 'name' => 'g_ticket_attach', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['attachments'], 'for' => 1, 'msg' => '{lang.warn_attachments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.max_upload_size} '. $this->trellis->skin->help_tip('{lang.tip_max_upload_size}'), $this->trellis->skin->textfield( 'g_upload_size_max', '', '', 0, 6 ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.upload_exts} '. $this->trellis->skin->help_tip('{lang.tip_upload_exts}'), $this->trellis->skin->textfield( 'g_upload_exts' ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.department_permissions} '. $this->trellis->skin->help_tip('{lang.tip_depart_perms}'), $depart_perms_html,         'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.knowledge_base}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.knowledge_base_access}', $this->trellis->skin->yes_no_radio( array( 'name' => 'g_kb_access', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['enable'], 'for' => 1, 'msg' => '{lang.warn_kb_access}' ) ) ), 'a' );

        $this->output .= $this->trellis->skin->group_table_row( '{lang.comment_on_articles}',            $this->trellis->skin->yes_no_radio( array( 'name' => 'g_kb_comment', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['comments'], 'for' => 1, 'msg' => '{lang.warn_kb_comments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_own_article_comments}',     $this->trellis->skin->yes_no_radio( 'g_kb_com_edit' ),                    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_own_article_comments}',     $this->trellis->skin->yes_no_radio( 'g_kb_com_delete' ),                'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.rate_articles}',                 $this->trellis->skin->yes_no_radio( array( 'name' => 'g_kb_rate', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['rating'], 'for' => 1, 'msg' => '{lang.warn_kb_rating}' ) ) ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.kb_permissions} '. $this->trellis->skin->help_tip('{lang.tip_kb_perms}'), "<select name='g_kb_perm[]' id='g_kb_perm' size='8' multiple='multiple'>". $this->trellis->func->drop_downs->cat_drop( array( 'childs' => 1, 'select' => $this->trellis->input['g_kb_perm'], 'no_perm' => 1 ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.news}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.comment_on_news}',                 $this->trellis->skin->yes_no_radio( array( 'name' => 'g_news_comment', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['news']['comments'], 'for' => 1, 'msg' => '{lang.warn_news_comments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_own_news_comments}',         $this->trellis->skin->yes_no_radio( 'g_news_com_edit' ),                'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_own_news_comments}',         $this->trellis->skin->yes_no_radio( 'g_news_com_delete' ),            'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.profile}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.change_skin}',                     $this->trellis->skin->yes_no_radio( array( 'name' => 'g_change_skin', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['look']['change_skin'], 'for' => 1, 'msg' => '{lang.warn_skin_change}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.change_language}',                 $this->trellis->skin->yes_no_radio( array( 'name' => 'g_change_lang', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['look']['change_lang'], 'for' => 1, 'msg' => '{lang.warn_lang_change}' ) ) ),                    'a' );

        if ( $this->trellis->user['id'] == 1 )
        {
            $acp_perms_html = "<table width='100%' cellpadding='2' cellspacing='0'>
                                <tr>
                                    <td width='33%'>{lang.acp_perms_manage}</td>
                                    <td width='33%'>{lang.acp_perms_look}</td>
                                    <td width='34%'>{lang.acp_perms_tools}</td>
                                </tr>
                                <tr>
                                    <td><select name='acp_perms_manage[]' id='acp_perm_manage' size='10' multiple='multiple'>". $this->acp_perm_drop( 'manage', $acp_perms ) ."</select></td>
                                    <td><select name='acp_perms_look[]' id='acp_perm_look' size='10' multiple='multiple'>". $this->acp_perm_drop( 'look', $acp_perms ) ."</select></td>
                                    <td><select name='acp_perms_tools[]' id='acp_perm_tools' size='10' multiple='multiple'>". $this->acp_perm_drop( 'tools', $acp_perms ) ."</select></td>
                                </tr>
                                </table>";

            $this->output .= $this->trellis->skin->group_table_sub( '{lang.staff_perms_settings}' ) ."
                        ". $this->trellis->skin->group_table_full_row( '{lang.staff_perms_settings_msg}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.grant_acp_access} '. $this->trellis->skin->help_tip('{lang.tip_grant_acp_access}'), $this->trellis->skin->yes_no_radio( 'g_acp_access' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.staff_hide_names} '. $this->trellis->skin->help_tip('{lang.tip_staff_hide_names}'), $this->trellis->skin->yes_no_radio( 'g_hide_names' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.assign_outside} '. $this->trellis->skin->help_tip('{lang.tip_assign_outside}'),     $this->trellis->skin->yes_no_radio( 'g_assign_outside' ),            'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_all_article_comments}',     $this->trellis->skin->yes_no_radio( 'g_kb_com_edit_all' ),            'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_all_article_comments}',     $this->trellis->skin->yes_no_radio( 'g_kb_com_delete_all' ),        'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_all_news_comments}',         $this->trellis->skin->yes_no_radio( 'g_news_com_edit_all' ),        'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_all_news_comments}',         $this->trellis->skin->yes_no_radio( 'g_news_com_delete_all' ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.acp_permissions} '. $this->trellis->skin->help_tip('{lang.tip_acp_permissions}'),         $acp_perms_html, 'a' );

            foreach( $this->trellis->cache->data['departs'] as $did => $d )
            {
                $depart_rows .= "<tr>
                                    <td class='bluecellthin-light'><div style='float:right'><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='cursor:pointer' onclick=\"ckRow('". $d['id'] ."')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='cursor:pointer' onclick=\"unckRow('". $d['id'] ."')\" /></div>{$d['name']}</td>
                                    <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_v_'. $d['id'], 'V' ), '{lang.tip_ticket_view}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_r_'. $d['id'], 'R' ), '{lang.tip_ticket_reply}' ) ."</td>
                                    <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_et_'. $d['id'], 'T' ), '{lang.tip_ticket_edit}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_er_'. $d['id'], 'R' ), '{lang.tip_reply_edit}' ) ."</td>
                                    <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_mv_'. $d['id'], 'M' ), '{lang.tip_ticket_move}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_es_'. $d['id'], 'E' ), '{lang.tip_ticket_escalate}' ) ."</td>
                                    <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_as_'. $d['id'], 'S' ), '{lang.tip_assign_self}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_aa_'. $d['id'], 'A' ), '{lang.tip_assign_all}' ) ."</td>
                                    <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_c_'. $d['id'], 'C' ), '{lang.tip_ticket_close}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_ro_'. $d['id'], 'RO' ), '{lang.tip_ticket_reopen}' ) ."</td>
                                    <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_dt_'. $d['id'], 'T' ), '{lang.tip_ticket_delete}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_dr_'. $d['id'], 'R' ), '{lang.tip_reply_delete}' ) ."</td>
                                </tr>";
            }

            $this->output .= $this->trellis->skin->end_group_table( 'a' ) ."
                        <div id='acp_depart_perm' style='margin-top:12px'>
                        ". $this->trellis->skin->start_group_table( '{lang.staff_depart_perms}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='28%' align='left'>{lang.department}</th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.view_reply}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('v')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('v')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('r')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('r')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.edit}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('et')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('et')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('er')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('er')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.move}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('mv')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('mv')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('es')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('es')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.assign}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('as')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('as')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('aa')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('aa')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.close}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('c')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('c')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('ro')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('ro')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.delete}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('dt')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('dt')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('dr')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('dr')\" /></th>
                        </tr>
                        ". $depart_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_group}' ) );
        }
        else
        {
            $this->output .= $this->trellis->skin->end_group_table( 'a' );
        }

        $this->output .= "</div>";

        $validate_fields = array(
                                 'g_name'                => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_group_name}' ) ) ),
                                 'g_upload_size_max'    => array( array( 'type' => 'number', 'params' => array( 'int_only' => 1, 'not_num_msg' => '{lang.lv_not_num}', 'not_int_msg' => '{lang.lv_not_int}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('g_name');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=groups' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_group_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Group Form
    #=======================================

    private function edit_group($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'groups', 'edit' );

        if ( ! $g = $this->trellis->func->groups->get_single_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_group');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;

            #=============================
            # Permissions
            #=============================

            if ( $this->trellis->user['id'] == 1 )
            {
                $g_kb_perm = $this->trellis->input['g_kb_perm'];

                $acp_perms_manage = array();
                $acp_perms_look = array();
                $acp_perms_tools = array();

                if ( is_array( $this->trellis->input['acp_perms_manage'] ) )
                {
                    foreach( $this->trellis->input['acp_perms_manage'] as $perm )
                    {
                        $acp_perms_manage[ 'manage_'. $perm ] = 1;
                    }
                }
                if ( is_array( $this->trellis->input['acp_perms_look'] ) )
                {
                    foreach( $this->trellis->input['acp_perms_look'] as $perm )
                    {
                        $acp_perms_look[ 'look_'. $perm ] = 1;
                    }
                }
                if ( is_array( $this->trellis->input['acp_perms_tools'] ) )
                {
                    foreach( $this->trellis->input['acp_perms_tools'] as $perm )
                    {
                        $acp_perms_tools[ 'tools_'. $perm ] = 1;
                    }
                }

                $acp_perms = array_merge( $acp_perms_manage, $acp_perms_look, $acp_perms_tools );
            }
        }
        else
        {
            $g_kb_perm = array();

            $g['g_kb_perm'] = unserialize( $g['g_kb_perm'] );

            foreach ( $g['g_kb_perm'] as $gid => $p )
            {
                if ( $p ) $g_kb_perm[] = $gid;
            }

            $g_m_depart_perm = unserialize( $g['g_depart_perm'] );

            if ( $this->trellis->user['id'] == 1 )
            {
                $acp_perms = unserialize( $g['g_acp_perm'] );
            }

            $g_acp_depart_perm = unserialize( $g['g_acp_depart_perm'] );
        }

        $kb_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $kb_count = 0;
        $kb_total = count( $this->trellis->cache->data['categories'] );

        $parents = array();
        $childs = array();

        foreach( $this->trellis->cache->data['categories'] as $id => $c )
        {
            ( $c['parent_id'] ) ? $childs[ $c['parent_id'] ][ $id ] = $c : $parents[ $id ] = $c;
        }

        foreach( $parents as $cid => $c )
        {
            $kb_count ++;

            if ( $kb_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $kb_count & 1 )
            {
                if ( $kb_count == $kb_total )
                {
                    $kb_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'kbp_'. $c['id'], $c['name'], $g['g_kb_perm'][ $c['id'] ] ) ."</td></tr>";
                }
                else
                {
                    $kb_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'kbp_'. $c['id'], $c['name'], $g['g_kb_perm'][ $c['id'] ] ) ."</td>";
                }
            }
            else
            {
                $kb_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'kbp_'. $c['id'], $c['name'], $g['g_kb_perm'][ $c['id'] ] ) ."</td></tr>";
            }

            if ( $childs[ $cid ] )
            {
                foreach( $childs[ $cid ] as $cid => $c )
                {
                    $kb_count ++;

                    if ( $kb_count > 2 ) $padding = " style='padding-top:5px'";

                    if ( $kb_count & 1 )
                    {
                        if ( $kb_count == $kb_total )
                        {
                            $kb_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'kbp_'. $c['id'], '-- '. $c['name'], $g['g_kb_perm'][ $c['id'] ] ) ."</td></tr>";
                        }
                        else
                        {
                            $kb_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'kbp_'. $c['id'], '-- '. $c['name'], $g['g_kb_perm'][ $c['id'] ] ) ."</td>";
                        }
                    }
                    else
                    {
                        $kb_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'kbp_'. $c['id'], '-- '. $c['name'], $g['g_kb_perm'][ $c['id'] ] ) ."</td></tr>";
                    }
                }
            }
        }

        $kb_perms_html .= "</table>";

        $depart_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $depart_count = 0;
        $depart_total = count( $this->trellis->cache->data['departs'] );

        foreach( $this->trellis->cache->data['departs'] as $did => $d )
        {
            $depart_count ++;

            if ( $depart_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $depart_count & 1 )
            {
                if ( $depart_count == $depart_total )
                {
                    $depart_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'], $g_m_depart_perm[ $d['id'] ] ) ."</td></tr>";
                }
                else
                {
                    $depart_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'], $g_m_depart_perm[ $d['id'] ] ) ."</td>";
                }
            }
            else
            {
                $depart_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'dp_'. $d['id'], $d['name'], $g_m_depart_perm[ $d['id'] ] ) ."</td></tr>";
            }
        }

        $depart_perms_html .= "</table>";

        $this->trellis->load_functions('drop_downs');

        $this->output = "<script type='text/javascript'>

                        function ckCol(type)
                        {
                            $('input:checkbox').each( function() {
                                if ( this.name.search('^adp_' + type + '_') != -1 ) this.checked = 1
                            } );
                        }

                        function unckCol(type)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^adp_' + type + '_') != -1 ) this.checked = 0
                            } );
                        }

                        function ckRow(did)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^adp_[a-z]+_' + did + '$') != -1 ) this.checked = 1
                            } );
                        }

                        function unckRow(did)
                        {
                            $('input:checkbox').each( function(e) {
                                if ( this.name.search('^adp_[a-z]+_' + did + '$') != -1 ) this.checked = 0
                            } );
                        }

                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=groups&amp;act=doedit&amp;id={$g['g_id']}", 'edit_group', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_group} '. $g['g_name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}',             $this->trellis->skin->textfield( 'g_name', $g['g_name'] ),                                    'a', '30%', '70%' );

        if ( $this->trellis->cache->data['settings']['security']['force_login'] && $g['g_id'] == 2 )
        {
            $this->output .= "<tr><td colspan='2' class='barstaffonly'>{lang.warning}</td></tr><tr><td colspan='2' class='rollstaffonly'><p>{lang.warning_force_login}</p></td></tr>";
        }

        $this->output .= $this->trellis->skin->group_table_sub( '{lang.ticket_center}' ) ."
                        ". $this->trellis->skin->group_table_full_row( '{lang.staff_ticket_perms_msg}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.ticket_center_access} *',     $this->trellis->skin->yes_no_radio( 'g_ticket_access', $g['g_ticket_access'] ),            'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.create_new_tickets}',     $this->trellis->skin->yes_no_radio( array( 'name' => 'g_ticket_create', 'value' => $g['g_ticket_create'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['new_tickets'], 'for' => 1, 'msg' => '{lang.warn_ticket_create}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.can_escalate_tickets}', $this->trellis->skin->yes_no_radio( array( 'name' => 'g_ticket_escalate', 'value' => $g['g_ticket_escalate'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['escalate'], 'for' => 1, 'msg' => '{lang.warn_ticket_escalate}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_own_tickets}',         $this->trellis->skin->yes_no_radio( 'g_ticket_edit', $g['g_ticket_edit'] ),                'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.close_own_tickets}',     $this->trellis->skin->yes_no_radio( 'g_ticket_close', $g['g_ticket_close'] ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.reopen_own_tickets}',     $this->trellis->skin->yes_no_radio( 'g_ticket_reopen', $g['g_ticket_reopen'] ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_own_replies}',         $this->trellis->skin->yes_no_radio( 'g_reply_edit', $g['g_reply_edit'] ),                'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_own_replies}',     $this->trellis->skin->yes_no_radio( 'g_reply_delete', $g['g_reply_delete'] ),            'a' );

        if ( $g['g_id'] != 2 ) $this->output .= $this->trellis->skin->group_table_row( '{lang.rate_staff_replies}', $this->trellis->skin->yes_no_radio( array( 'name' => 'g_reply_rate', 'value' => $g['g_reply_rate'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['rating'], 'for' => 1, 'msg' => '{lang.warn_ticket_rating}' ) ) ), 'a' );

        $this->output .= $this->trellis->skin->group_table_row( '{lang.allow_attachments}',         $this->trellis->skin->yes_no_radio( array( 'name' => 'g_ticket_attach', 'value' => $g['g_ticket_attach'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['ticket']['attachments'], 'for' => 1, 'msg' => '{lang.warn_attachments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.max_upload_size} '. $this->trellis->skin->help_tip('{lang.tip_max_upload_size}'), $this->trellis->skin->textfield( 'g_upload_size_max', $g['g_upload_size_max'], '', 0, 6 ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.upload_exts} '. $this->trellis->skin->help_tip('{lang.tip_upload_exts}'), $this->trellis->skin->textfield( 'g_upload_exts', $g['g_upload_exts'] ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.department_permissions} '. $this->trellis->skin->help_tip('{lang.tip_depart_perms}'), $depart_perms_html,         'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.knowledge_base}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.knowledge_base_access}', $this->trellis->skin->yes_no_radio( array( 'name' => 'g_kb_access', 'value' => $g['g_kb_access'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['enable'], 'for' => 1, 'msg' => '{lang.warn_kb_access}' ) ) ), 'a' );

        if ( $g['g_id'] != 2 )
        {
            $this->output .= $this->trellis->skin->group_table_row( '{lang.comment_on_articles}',            $this->trellis->skin->yes_no_radio( array( 'name' => 'g_kb_comment', 'value' => $g['g_kb_comment'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['comments'], 'for' => 1, 'msg' => '{lang.warn_kb_comments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_own_article_comments}',     $this->trellis->skin->yes_no_radio( 'g_kb_com_edit', $g['g_kb_com_edit'] ),                    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_own_article_comments}',     $this->trellis->skin->yes_no_radio( 'g_kb_com_delete', $g['g_kb_com_delete'] ),                'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.rate_articles}',                 $this->trellis->skin->yes_no_radio( array( 'name' => 'g_kb_rate', 'value' => $g['g_kb_rate'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['rating'], 'for' => 1, 'msg' => '{lang.warn_kb_rating}' ) ) ), 'a' );
        }

        $this->output .= $this->trellis->skin->group_table_row( '{lang.kb_permissions} '. $this->trellis->skin->help_tip('{lang.tip_kb_perms}'), "<select name='g_kb_perm[]' id='g_kb_perm' size='8' multiple='multiple'>". $this->trellis->func->drop_downs->cat_drop( array( 'childs' => 1, 'select' => $g_kb_perm, 'no_perm' => 1 ) ) ."</select>", 'a' );

        if ( $g['g_id'] != 2 )
        {
            $this->output .= $this->trellis->skin->group_table_sub( '{lang.news}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.comment_on_news}',                 $this->trellis->skin->yes_no_radio( array( 'name' => 'g_news_comment', 'value' => $g['g_news_comment'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['news']['comments'], 'for' => 1, 'msg' => '{lang.warn_news_comments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_own_news_comments}',         $this->trellis->skin->yes_no_radio( 'g_news_com_edit', $g['g_news_com_edit'] ),                'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_own_news_comments}',         $this->trellis->skin->yes_no_radio( 'g_news_com_delete', $g['g_news_com_delete'] ),            'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.profile}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.change_skin}',                     $this->trellis->skin->yes_no_radio( array( 'name' => 'g_change_skin', 'value' => $g['g_change_skin'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['look']['change_skin'], 'for' => 1, 'msg' => '{lang.warn_skin_change}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.change_language}',                 $this->trellis->skin->yes_no_radio( array( 'name' => 'g_change_lang', 'value' => $g['g_change_lang'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['look']['change_lang'], 'for' => 1, 'msg' => '{lang.warn_lang_change}' ) ) ), 'a' );
        }

        if ( $this->trellis->user['id'] == 1 && $g['g_id'] != 2 )
        {
            $acp_perms_html = "<table width='100%' cellpadding='2' cellspacing='0'>
                                <tr>
                                    <td width='33%'>{lang.acp_perms_manage}</td>
                                    <td width='33%'>{lang.acp_perms_look}</td>
                                    <td width='34%'>{lang.acp_perms_tools}</td>
                                </tr>
                                <tr>
                                    <td><select name='acp_perms_manage[]' id='acp_perm_manage' size='10' multiple='multiple'>". $this->acp_perm_drop( 'manage', $acp_perms ) ."</select></td>
                                    <td><select name='acp_perms_look[]' id='acp_perm_look' size='10' multiple='multiple'>". $this->acp_perm_drop( 'look', $acp_perms ) ."</select></td>
                                    <td><select name='acp_perms_tools[]' id='acp_perm_tools' size='10' multiple='multiple'>". $this->acp_perm_drop( 'tools', $acp_perms ) ."</select></td>
                                </tr>
                                </table>";

            $this->output .= $this->trellis->skin->group_table_sub( '{lang.staff_perms_settings}' ) ."
                        ". $this->trellis->skin->group_table_full_row( '{lang.staff_perms_settings_msg}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.grant_acp_access} '. $this->trellis->skin->help_tip('{lang.tip_grant_acp_access}'), $this->trellis->skin->yes_no_radio( 'g_acp_access', $g['g_acp_access'] ),                    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.staff_hide_names} '. $this->trellis->skin->help_tip('{lang.tip_staff_hide_names}'), $this->trellis->skin->yes_no_radio( 'g_hide_names', $g['g_hide_names'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.assign_outside} '. $this->trellis->skin->help_tip('{lang.tip_assign_outside}'),     $this->trellis->skin->yes_no_radio( 'g_assign_outside', $g['g_assign_outside'] ),            'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_all_article_comments}',     $this->trellis->skin->yes_no_radio( 'g_kb_com_edit_all', $g['g_kb_com_edit_all'] ),            'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_all_article_comments}',     $this->trellis->skin->yes_no_radio( 'g_kb_com_delete_all', $g['g_kb_com_delete_all'] ),        'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.edit_all_news_comments}',         $this->trellis->skin->yes_no_radio( 'g_news_com_edit_all', $g['g_news_com_edit_all'] ),        'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.delete_all_news_comments}',         $this->trellis->skin->yes_no_radio( 'g_news_com_delete_all', $g['g_news_com_delete_all'] ),    'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.acp_permissions} '. $this->trellis->skin->help_tip('{lang.tip_acp_permissions}'),         $acp_perms_html, 'a' );

            $depart_rows = '';

            foreach( $this->trellis->cache->data['departs'] as $did => $d )
            {
                $depart_rows .= "<tr>
                                    <td class='bluecellthin-light'><div style='float:right'><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='cursor:pointer' onclick=\"ckRow('". $d['id'] ."')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='cursor:pointer' onclick=\"unckRow('". $d['id'] ."')\" /></div>{$d['name']}</td>
                                    <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_v_'. $d['id'], 'V', $g_acp_depart_perm[ $d['id'] ]['v'] ), '{lang.tip_ticket_view}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_r_'. $d['id'], 'R', $g_acp_depart_perm[ $d['id'] ]['r'] ), '{lang.tip_ticket_reply}' ) ."</td>
                                    <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_et_'. $d['id'], 'T', $g_acp_depart_perm[ $d['id'] ]['et'] ), '{lang.tip_ticket_edit}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_er_'. $d['id'], 'R', $g_acp_depart_perm[ $d['id'] ]['er'] ), '{lang.tip_reply_edit}' ) ."</td>
                                    <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_mv_'. $d['id'], 'M', $g_acp_depart_perm[ $d['id'] ]['mv'] ), '{lang.tip_ticket_move}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_es_'. $d['id'], 'E', $g_acp_depart_perm[ $d['id'] ]['es'] ), '{lang.tip_ticket_escalate}' ) ."</td>
                                    <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_as_'. $d['id'], 'S', $g_acp_depart_perm[ $d['id'] ]['as'] ), '{lang.tip_assign_self}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_aa_'. $d['id'], 'A', $g_acp_depart_perm[ $d['id'] ]['aa'] ), '{lang.tip_assign_all}' ) ."</td>
                                    <td class='bluecellthin-dark' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_c_'. $d['id'], 'C', $g_acp_depart_perm[ $d['id'] ]['c'] ), '{lang.tip_ticket_close}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_ro_'. $d['id'], 'RO', $g_acp_depart_perm[ $d['id'] ]['ro'] ), '{lang.tip_ticket_reopen}' ) ."</td>
                                    <td class='bluecellthin-light' align='center'>". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_dt_'. $d['id'], 'T', $g_acp_depart_perm[ $d['id'] ]['dt'] ), '{lang.tip_ticket_delete}' ) ." ". $this->trellis->skin->text_tip( $this->trellis->skin->checkbox( 'adp_dr_'. $d['id'], 'R', $g_acp_depart_perm[ $d['id'] ]['dr'] ), '{lang.tip_reply_delete}' ) ."</td>
                                </tr>";
            }

            $this->output .= $this->trellis->skin->end_group_table( 'a' ) ."
                        <div id='acp_depart_perm' style='margin-top:12px'>
                        ". $this->trellis->skin->start_group_table( '{lang.staff_depart_perms}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='28%' align='left'>{lang.department}</th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.view_reply}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('v')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('v')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('r')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('r')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.edit}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('et')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('et')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('er')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('er')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.move}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('mv')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('mv')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('es')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('es')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.assign}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('as')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('as')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('aa')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('aa')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.close}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('c')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('c')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('ro')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('ro')\" /></th>
                            <th class='bluecellthin-th' width='12%' align='center'>{lang.delete}<br /><img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('dt')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('dt')\" />&nbsp;&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/plus_sm.png' alt='+' style='vertical-align:bottom;cursor:pointer' onclick=\"ckCol('dr')\" />&nbsp;&nbsp;<img src='<! IMG_DIR !>/icons/minus_sm.png' alt='-' style='vertical-align:bottom;cursor:pointer' onclick=\"unckCol('dr')\" /></th>
                        </tr>
                        ". $depart_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_group}' ) );
        }
        else
        {
            $this->output .= $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_group}' ) );
        }

        $this->output .= "</div>";

        $validate_fields = array(
                                 'g_name'                => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_group_name}' ) ) ),
                                 'g_upload_size_max'    => array( array( 'type' => 'number', 'params' => array( 'int_only' => 1, 'not_num_msg' => '{lang.lv_not_num}', 'not_int_msg' => '{lang.lv_not_int}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=groups' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=groups&amp;act=add' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_group_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Delete Group Form
    #=======================================

    function delete_group()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'groups', 'delete' );

        if ( ! $g = $this->trellis->func->groups->get_single_by_id( array( 'g_id', 'g_name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_group');

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=groups&amp;act=dodel&amp;id={$g['g_id']}", 'delete_group', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.deleting_group} '. $g['g_name'] ) ."
                        ". $this->trellis->skin->group_sub( '{lang.delete_group_users_qs}' ) ."
                        <div class='option1'><input type='radio' name='action' id='action1' value='1' checked='checked' /> <label for='action1'>{lang.move_users_to_group}</label> <select name='moveto'>". $this->trellis->func->drop_downs->group_drop( 0, $g['g_id'] ) ."</select></div>
                        <div class='option2'><input type='radio' name='action' id='action2' value='2' /> <label for='action2'>{lang.delete_users}</label></div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_delete_group}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=groups' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=groups&amp;act=add' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_group_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add Group
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'groups', 'add' );

        if ( ! $this->trellis->input['g_name'] ) $this->add_group('no_group_name');

        #=============================
        # Generate Permissions
        #=============================

        $kb_perm = array();

        foreach ( $this->trellis->input['g_kb_perm'] as $cid )
        {
            $kb_perm[ $cid ] = 1;
        }

        $depart_perm = array();

        foreach ( $this->trellis->cache->data['departs'] as $did => $d )
        {
            $depart_perm[ $did ] = intval( $this->trellis->input[ 'dp_'. $did ] );
        }

        if ( $this->trellis->user['id'] == 1 )
        {
            $acp_perms_manage = array();
            $acp_perms_look = array();
            $acp_perms_tools = array();

            if ( is_array( $this->trellis->input['acp_perms_manage'] ) )
            {
                foreach( $this->trellis->input['acp_perms_manage'] as $perm )
                {
                    $acp_perms_manage[ 'manage_'. $perm ] = 1;
                }
            }
            if ( is_array( $this->trellis->input['acp_perms_look'] ) )
            {
                foreach( $this->trellis->input['acp_perms_look'] as $perm )
                {
                    $acp_perms_look[ 'look_'. $perm ] = 1;
                }
            }
            if ( is_array( $this->trellis->input['acp_perms_tools'] ) )
            {
                foreach( $this->trellis->input['acp_perms_tools'] as $perm )
                {
                    $acp_perms_tools[ 'tools_'. $perm ] = 1;
                }
            }

            $acp_perms = array_merge( $acp_perms_manage, $acp_perms_look, $acp_perms_tools );

            $acp_depart_perm = array();

            foreach ( $this->trellis->cache->data['departs'] as $did => $d )
            {
                $acp_depart_perm[ $did ]['v'] = intval( $this->trellis->input[ 'adp_v_'. $did ] );
                $acp_depart_perm[ $did ]['r'] = intval( $this->trellis->input[ 'adp_r_'. $did ] );
                $acp_depart_perm[ $did ]['et'] = intval( $this->trellis->input[ 'adp_et_'. $did ] );
                $acp_depart_perm[ $did ]['er'] = intval( $this->trellis->input[ 'adp_er_'. $did ] );
                $acp_depart_perm[ $did ]['mv'] = intval( $this->trellis->input[ 'adp_mv_'. $did ] );
                $acp_depart_perm[ $did ]['es'] = intval( $this->trellis->input[ 'adp_es_'. $did ] );
                $acp_depart_perm[ $did ]['as'] = intval( $this->trellis->input[ 'adp_as_'. $did ] );
                $acp_depart_perm[ $did ]['aa'] = intval( $this->trellis->input[ 'adp_aa_'. $did ] );
                $acp_depart_perm[ $did ]['c'] = intval( $this->trellis->input[ 'adp_c_'. $did ] );
                $acp_depart_perm[ $did ]['ro'] = intval( $this->trellis->input[ 'adp_ro_'. $did ] );
                $acp_depart_perm[ $did ]['dt'] = intval( $this->trellis->input[ 'adp_dt_'. $did ] );
                $acp_depart_perm[ $did ]['dr'] = intval( $this->trellis->input[ 'adp_dr_'. $did ] );
            }
        }

        #=============================
        # Add Group
        #=============================

        $db_array = array(
                          'g_name'                    => $this->trellis->input['g_name'],
                          'g_ticket_access'            => $this->trellis->input['g_ticket_access'],
                          'g_ticket_create'            => $this->trellis->input['g_ticket_create'],
                          'g_ticket_edit'            => $this->trellis->input['g_ticket_edit'],
                          'g_ticket_escalate'        => $this->trellis->input['g_ticket_escalate'],
                          'g_ticket_close'            => $this->trellis->input['g_ticket_close'],
                          'g_ticket_reopen'            => $this->trellis->input['g_ticket_reopen'],
                          'g_reply_edit'            => $this->trellis->input['g_reply_edit'],
                          'g_reply_delete'            => $this->trellis->input['g_reply_delete'],
                          'g_reply_rate'            => $this->trellis->input['g_reply_rate'],
                          'g_ticket_attach'            => $this->trellis->input['g_ticket_attach'],
                          'g_upload_size_max'        => $this->trellis->input['g_upload_size_max'],
                          'g_upload_exts'            => $this->trellis->input['g_upload_exts'],
                          'g_kb_access'                => $this->trellis->input['g_kb_access'],
                          'g_kb_comment'            => $this->trellis->input['g_kb_comment'],
                          'g_kb_com_edit'            => $this->trellis->input['g_kb_com_edit'],
                          'g_kb_com_delete'            => $this->trellis->input['g_kb_com_delete'],
                          'g_kb_rate'                => $this->trellis->input['g_kb_rate'],
                          'g_kb_perm'                => $kb_perm,
                          'g_news_comment'            => $this->trellis->input['g_news_comment'],
                          'g_news_com_edit'            => $this->trellis->input['g_news_com_edit'],
                          'g_news_com_delete'        => $this->trellis->input['g_news_com_delete'],
                          'g_change_skin'            => $this->trellis->input['g_change_skin'],
                          'g_change_lang'            => $this->trellis->input['g_change_lang'],
                          'g_depart_perm'            => $depart_perm,
                         );

        if ( $this->trellis->user['id'] == 1 )
        {
            $db_array['g_kb_com_edit_all'] = $this->trellis->input['g_kb_com_edit_all'];
            $db_array['g_kb_com_delete_all'] = $this->trellis->input['g_kb_com_delete_all'];
            $db_array['g_news_com_edit_all'] = $this->trellis->input['g_news_com_edit_all'];
            $db_array['g_news_com_delete_all'] = $this->trellis->input['g_news_com_delete_all'];
            $db_array['g_hide_names'] = $this->trellis->input['g_hide_names'];
            $db_array['g_assign_outside'] = $this->trellis->input['g_assign_outside'];
            $db_array['g_acp_access'] = $this->trellis->input['g_acp_access'];
            $db_array['g_acp_perm'] = $acp_perms;
            $db_array['g_acp_depart_perm'] = $acp_depart_perm;
        }

        $group_id = $this->trellis->func->groups->add( $db_array );

        $this->trellis->log( 'other', "Group Added &#039;". $this->trellis->input['g_name'] ."&#039;", 1, $group_id );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->groups_cache();
        $this->trellis->func->rebuild->staff_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_group_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Group
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'groups', 'edit' );

        if ( ! $this->trellis->input['g_name'] ) $this->edit_group('no_group_name');

        if ( ! $g = $this->trellis->func->groups->get_single_by_id( array( 'g_kb_perm' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_group');

        #=============================
        # Generate Permissions
        #=============================

        $g['g_kb_perm'] = unserialize( $g['g_kb_perm'] );

        $kb_perm = array();

        foreach ( $this->trellis->input['g_kb_perm'] as $cid )
        {
            $kb_perm[ $cid ] = 1;
        }

        $depart_perm = array();

        foreach ( $this->trellis->cache->data['departs'] as $did => $d )
        {
            $depart_perm[ $did ] = intval( $this->trellis->input[ 'dp_'. $did ] );
        }

        if ( $this->trellis->user['id'] == 1 )
        {
            $acp_perms_manage = array();
            $acp_perms_look = array();
            $acp_perms_tools = array();

            if ( is_array( $this->trellis->input['acp_perms_manage'] ) )
            {
                foreach( $this->trellis->input['acp_perms_manage'] as $perm )
                {
                    $acp_perms_manage[ 'manage_'. $perm ] = 1;
                }
            }
            if ( is_array( $this->trellis->input['acp_perms_look'] ) )
            {
                foreach( $this->trellis->input['acp_perms_look'] as $perm )
                {
                    $acp_perms_look[ 'look_'. $perm ] = 1;
                }
            }
            if ( is_array( $this->trellis->input['acp_perms_tools'] ) )
            {
                foreach( $this->trellis->input['acp_perms_tools'] as $perm )
                {
                    $acp_perms_tools[ 'tools_'. $perm ] = 1;
                }
            }

            $acp_perms = array_merge( $acp_perms_manage, $acp_perms_look, $acp_perms_tools );

            $acp_depart_perm = array();

            foreach ( $this->trellis->cache->data['departs'] as $did => $d )
            {
                $acp_depart_perm[ $did ]['v'] = intval( $this->trellis->input[ 'adp_v_'. $did ] );
                $acp_depart_perm[ $did ]['r'] = intval( $this->trellis->input[ 'adp_r_'. $did ] );
                $acp_depart_perm[ $did ]['et'] = intval( $this->trellis->input[ 'adp_et_'. $did ] );
                $acp_depart_perm[ $did ]['er'] = intval( $this->trellis->input[ 'adp_er_'. $did ] );
                $acp_depart_perm[ $did ]['mv'] = intval( $this->trellis->input[ 'adp_mv_'. $did ] );
                $acp_depart_perm[ $did ]['es'] = intval( $this->trellis->input[ 'adp_es_'. $did ] );
                $acp_depart_perm[ $did ]['as'] = intval( $this->trellis->input[ 'adp_as_'. $did ] );
                $acp_depart_perm[ $did ]['aa'] = intval( $this->trellis->input[ 'adp_aa_'. $did ] );
                $acp_depart_perm[ $did ]['c'] = intval( $this->trellis->input[ 'adp_c_'. $did ] );
                $acp_depart_perm[ $did ]['ro'] = intval( $this->trellis->input[ 'adp_ro_'. $did ] );
                $acp_depart_perm[ $did ]['dt'] = intval( $this->trellis->input[ 'adp_dt_'. $did ] );
                $acp_depart_perm[ $did ]['dr'] = intval( $this->trellis->input[ 'adp_dr_'. $did ] );
            }
        }

        #=============================
        # Update Group
        #=============================

        $db_array = array(
                          'g_name'                    => $this->trellis->input['g_name'],
                          'g_ticket_access'            => $this->trellis->input['g_ticket_access'],
                          'g_ticket_create'            => $this->trellis->input['g_ticket_create'],
                          'g_ticket_edit'            => $this->trellis->input['g_ticket_edit'],
                          'g_ticket_escalate'        => $this->trellis->input['g_ticket_escalate'],
                          'g_ticket_close'            => $this->trellis->input['g_ticket_close'],
                          'g_ticket_reopen'            => $this->trellis->input['g_ticket_reopen'],
                          'g_reply_edit'            => $this->trellis->input['g_reply_edit'],
                          'g_reply_delete'            => $this->trellis->input['g_reply_delete'],
                          'g_reply_rate'            => $this->trellis->input['g_reply_rate'],
                          'g_ticket_attach'            => $this->trellis->input['g_ticket_attach'],
                          'g_upload_size_max'        => $this->trellis->input['g_upload_size_max'],
                          'g_upload_exts'            => $this->trellis->input['g_upload_exts'],
                          'g_kb_access'                => $this->trellis->input['g_kb_access'],
                          'g_kb_comment'            => $this->trellis->input['g_kb_comment'],
                          'g_kb_com_edit'            => $this->trellis->input['g_kb_com_edit'],
                          'g_kb_com_delete'            => $this->trellis->input['g_kb_com_delete'],
                          'g_kb_rate'                => $this->trellis->input['g_kb_rate'],
                          'g_kb_perm'                => $kb_perm,
                          'g_news_comment'            => $this->trellis->input['g_news_comment'],
                          'g_news_com_edit'            => $this->trellis->input['g_news_com_edit'],
                          'g_news_com_delete'        => $this->trellis->input['g_news_com_delete'],
                          'g_change_skin'            => $this->trellis->input['g_change_skin'],
                          'g_change_lang'            => $this->trellis->input['g_change_lang'],
                          'g_depart_perm'            => $depart_perm,
                         );

        if ( $this->trellis->user['id'] == 1 )
        {
            $db_array['g_kb_com_edit_all'] = $this->trellis->input['g_kb_com_edit_all'];
            $db_array['g_kb_com_delete_all'] = $this->trellis->input['g_kb_com_delete_all'];
            $db_array['g_news_com_edit_all'] = $this->trellis->input['g_news_com_edit_all'];
            $db_array['g_news_com_delete_all'] = $this->trellis->input['g_news_com_delete_all'];
            $db_array['g_hide_names'] = $this->trellis->input['g_hide_names'];
            $db_array['g_assign_outside'] = $this->trellis->input['g_assign_outside'];
            $db_array['g_acp_access'] = $this->trellis->input['g_acp_access'];
            $db_array['g_acp_perm'] = $acp_perms;
            $db_array['g_acp_depart_perm'] = $acp_depart_perm;
        }

        $this->trellis->func->groups->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( 'other', "Group Edited &#039;". $this->trellis->input['g_name'] ."&#039;", 1, $this->trellis->input['id'] );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->groups_cache();
        $this->trellis->func->rebuild->staff_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_group_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Group
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'groups', 'delete' );

        #=============================
        # DELETE Group *MwhaAaAaAaAa*
        #=============================

        $this->trellis->func->groups->delete( $this->trellis->input['id'], $this->trellis->input['action'], $this->trellis->input['moveto'] );

        $this->trellis->log( 'other', "Group Deleted &#039;". $this->trellis->cache->data['groups'][ $this->trellis->input['id'] ]['g_name'] ."&#039;", 2, intval( $this->trellis->input['id'] ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->groups_cache();
        $this->trellis->func->rebuild->staff_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_group_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ ACP Permissions Drop-Down
    #=======================================

    private function acp_perm_drop($section, $selected=array())
    {
        $acp_pages = array();
        $acp_actions = array();

        $acp_pages['manage']['priorities'] = '{lang.acp_perms_priorities}';
        $acp_pages['manage']['statuses'] = '{lang.acp_perms_statuses}';
        $acp_pages['manage']['flags'] = '{lang.acp_perms_flags}';
        $acp_pages['manage']['rtemplates'] = '{lang.acp_perms_rtemplates}';
        $acp_pages['manage']['departs'] = '{lang.acp_perms_departs}';
        $acp_pages['manage']['cdfields'] = '{lang.acp_perms_cdfields}';
        $acp_pages['manage']['users'] = '{lang.acp_perms_users}';
        $acp_pages['manage']['groups'] = '{lang.acp_perms_groups}';
        $acp_pages['manage']['cpfields'] = '{lang.acp_perms_cpfields}';
        $acp_pages['manage']['news'] = '{lang.acp_perms_news}';
        $acp_pages['manage']['articles'] = '{lang.acp_perms_articles}';
        $acp_pages['manage']['categories'] = '{lang.acp_perms_categories}';
        $acp_pages['manage']['pages'] = '{lang.acp_perms_pages}';

        $acp_pages['look']['skins'] = '{lang.acp_perms_skins}';
        $acp_pages['look']['langs'] = '{lang.acp_perms_langs}';
        $acp_pages['look']['emails'] = '{lang.acp_perms_emails}';

        $acp_pages['tools']['settings'] = '{lang.acp_perms_settings}';
        $acp_pages['tools']['maint'] = '{lang.acp_perms_maint}';
        $acp_pages['tools']['backups'] = '{lang.acp_perms_backups}';
        $acp_pages['tools']['logs'] = '{lang.acp_perms_logs}';

        $acp_actions['priorities']['add'] = '{lang.acp_perms_add}';
        $acp_actions['priorities']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['priorities']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['priorities']['reorder'] = '{lang.acp_perms_reorder}';
        $acp_actions['statuses']['add'] = '{lang.acp_perms_add}';
        $acp_actions['statuses']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['statuses']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['statuses']['reorder'] = '{lang.acp_perms_reorder}';
        $acp_actions['flags']['add'] = '{lang.acp_perms_add}';
        $acp_actions['flags']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['flags']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['flags']['reorder'] = '{lang.acp_perms_reorder}';
        $acp_actions['rtemplates']['add'] = '{lang.acp_perms_add}';
        $acp_actions['rtemplates']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['rtemplates']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['rtemplates']['reorder'] = '{lang.acp_perms_reorder}';
        $acp_actions['departs']['add'] = '{lang.acp_perms_add}';
        $acp_actions['departs']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['departs']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['departs']['reorder'] = '{lang.acp_perms_reorder}';
        $acp_actions['cdfields']['add'] = '{lang.acp_perms_add}';
        $acp_actions['cdfields']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['cdfields']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['cdfields']['reorder'] = '{lang.acp_perms_reorder}';
        $acp_actions['users']['add'] = '{lang.acp_perms_add}';
        $acp_actions['users']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['users']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['users']['approve'] = '{lang.acp_perms_approve}';
        $acp_actions['users']['staff'] = '{lang.acp_perms_staff}';
        $acp_actions['groups']['add'] = '{lang.acp_perms_add}';
        $acp_actions['groups']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['groups']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['cpfields']['add'] = '{lang.acp_perms_add}';
        $acp_actions['cpfields']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['cpfields']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['cpfields']['reorder'] = '{lang.acp_perms_reorder}';
        $acp_actions['news']['add'] = '{lang.acp_perms_add}';
        $acp_actions['news']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['news']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['articles']['add'] = '{lang.acp_perms_add}';
        $acp_actions['articles']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['articles']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['categories']['add'] = '{lang.acp_perms_add}';
        $acp_actions['categories']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['categories']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['categories']['reorder'] = '{lang.acp_perms_reorder}';
        $acp_actions['pages']['add'] = '{lang.acp_perms_add}';
        $acp_actions['pages']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['pages']['delete'] = '{lang.acp_perms_delete}';

        $acp_actions['skins']['add'] = '{lang.acp_perms_add}';
        $acp_actions['skins']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['skins']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['skins']['tools'] = '{lang.acp_perms_tools}';
        $acp_actions['langs']['add'] = '{lang.acp_perms_add}';
        $acp_actions['langs']['edit'] = '{lang.acp_perms_edit}';
        $acp_actions['langs']['delete'] = '{lang.acp_perms_delete}';
        $acp_actions['langs']['tools'] = '{lang.acp_perms_tools}';

        $acp_actions['maint']['recount'] = '{lang.acp_perms_recount}';
        $acp_actions['maint']['rebuild'] = '{lang.acp_perms_rebuild}';
        $acp_actions['maint']['clean'] = '{lang.acp_perms_clean}';
        $acp_actions['backups']['backup'] = '{lang.acp_perms_backup}';
        $acp_actions['backups']['restore'] = '{lang.acp_perms_restore}';
        $acp_actions['logs']['delete'] = '{lang.acp_perms_delete}';

        $html = "";

        foreach( $acp_pages[ $section ] as $p => $pname )
        {
            $html .= "<option value='{$p}'";

            if ( $selected[ $section .'_'. $p ] ) $html .= " selected='yes'";

            $html .= ">{$pname}</option>";

            if ( ! is_array( $acp_actions[ $p ] ) ) continue;

            foreach( $acp_actions[ $p ] as $a => $aname )
            {
                $html .= "<option value='{$p}_{$a}'";

                if ( $selected[ $section .'_'. $p .'_'. $a ] ) $html .= " selected='yes'";

                $html .= ">-- {$aname}</option>";
            }
        }

        return $html;
    }

}

?>