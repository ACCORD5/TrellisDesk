<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_settings {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->load_lang('settings');

        $this->trellis->skin->set_active_link( 1 );

        switch( $this->trellis->input['act'] )
        {
            case 'edit':
                $this->edit_settings();
            break;

            case 'doedit':
                $this->do_edit();
            break;

            default:
                $this->edit_settings();
            break;
        }
    }

    #=======================================
    # @ Edit Settings
    #=======================================

    private function edit_settings()
    {
        #=============================
        # Do Output
        #=============================

        if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['ticket']['rte'] )
        {
            $this->output .= $this->trellis->skin->tinymce_js( 'signature' );

            $sig_html = 1;
        }
        else
        {
            $sig_html = 0;
        }

        $this->trellis->load_functions('drop_downs');

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=admin&amp;page=settings&amp;act=doedit", 'edit_settings', 'post' ) ."
                        <input type='hidden' id='sig_html' name='sig_html' value='{$sig_html}' />
                        ". $this->trellis->skin->start_group_table( '{lang.my_acp_settings}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( 'Paint It Black', $this->trellis->skin->yes_no_radio( 'paint_it_black' ),'a','25%','75%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.time_zone}', "<select name='time_zone' id='time_zone'>". $this->trellis->func->drop_downs->time_zone_drop( $this->trellis->user['time_zone'] ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.dst}', $this->trellis->skin->custom_radio( 'time_dst', array( 0 => '{lang.inactive}', 1 => '{lang.active}', 2 => '{lang.auto}' ), $this->trellis->user['time_dst'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.language}', "<select name='lang' id='lang'>". $this->trellis->func->drop_downs->lang_drop( $this->trellis->user['lang'] ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.rich_text_editor}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'rte_enable', 'value' => $this->trellis->user['rte_enable'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.ticket_column_layout}' );

        $columns = unserialize( $this->trellis->user['columns_tm'] );

        $lang_columns = array(
                              'id'            => '{lang.id}',
                              'mask'        => '{lang.mask}',
                              'subject'        => '{lang.subject}',
                              'priority'    => '{lang.priority}',
                              'department'    => '{lang.department}',
                              'date'        => '{lang.submitted}',
                              'reply'        => '{lang.last_reply}',
                              'replystaff'    => '{lang.last_staff_reply}',
                              'lastuname'    => '{lang.last_replier}',
                              'submitter'    => '{lang.submitter}',
                              'email'        => '{lang.ticket_email}',
                              'uemail'        => '{lang.user_email}',
                              'replies'        => '{lang.replies}',
                              'status'        => '{lang.status}',
                              );

        foreach( $this->trellis->cache->data['dfields'] as $cf )
        {
            if ( $cf['type'] != 'checkbox' ) $lang_columns[ 'cfd'. $cf['id'] ] = $cf['name'];
        }

        foreach( $this->trellis->cache->data['pfields'] as $cf )
        {
            if ( $cf['type'] != 'checkbox' ) $lang_columns[ 'cfp'. $cf['id'] ] = $cf['name'];
        }

        $columns_current = '';
        $columns_available = '';
        $cc_empty = '';
        $ca_empty = '';

        if ( empty( $columns ) )
        {
            $cc_empty = " style='height:10px'";
        }
        else
        {
            foreach( $columns as $key => $width )
            {
                ( $this->trellis->user['sort_tm'] == $key ) ? $stm_radio = " checked='checked'" : $stm_radio = "";

                $columns_current .= "<li id='c_". $key ."'>". $lang_columns[ $key ] ."<input type='text' id='cw_". $key ."' name='cw_". $key ."' value='". $width ."' size='3' style='float:right;margin-left:10px;text-align:right' /><input type='radio' id='sort_tm_". $key ."' name='sort_tm' value='". $key ."'". $stm_radio ." style='float:right;margin-top:5px;margin-left:10px;' /></li>";

                unset( $lang_columns[ $key ] );
            }
        }

        if ( empty( $lang_columns ) )
        {
            $ca_empty = " style='height:10px'";
        }
        else
        {
            foreach( $lang_columns as $key => $name )
            {
                $columns_available .= "<li id='c_". $key ."'>". $name ."<input type='text' id='cw_". $key ."' name='cw_". $key ."' value='". $columns[ $key ] ."' size='3' style='float:right;margin-left:10px;text-align:right' /><input type='radio' id='sort_tm_". $key ."' name='sort_tm' value='". $key ."' style='float:right;margin-top:5px;margin-left:10px;' /></li>";
            }
        }

        $column_layout .= "<div style='width:49%;float:left'>
                            <fieldset>
                                <legend>{lang.current_columns}</legend>
                                <input type='hidden' id='cc_serialized' name='cc_serialized' value='' />
                                <ul id='columns_current' class='colblocks'{$cc_empty}>{$columns_current}</ul>
                            </fieldset>
                            </div>
                            <div style='width:49%;float:right'>
                            <fieldset>
                                <legend>{lang.available_columns}</legend>
                                <input type='hidden' id='ac_serialized' name='ac_serialized' value='' />
                                <ul id='columns_available' class='colblocks'{$cc_empty}>{$columns_available}</ul>
                            </fieldset>
                            </div>";

        $this->output .= $this->trellis->skin->group_table_full_row( $column_layout ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.default_sort_order}', $this->trellis->skin->custom_radio( array( 'name' => 'order_tm', 'options' => array( 0 => '{lang.ascending}', 1 => '{lang.descending}' ), 'value' => $this->trellis->user['order_tm'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.staff_email_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_notifications}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_enable', 'value' => $this->trellis->user['email_staff_enable'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['enable'], 'for' => 1, 'msg' => '{lang.warn_email_enable}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_user_approve}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_user_approve', 'value' => $this->trellis->user['email_staff_user_approve'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['user_approve'], 'for' => 1, 'msg' => '{lang.warn_email_user_approve}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_ticket}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_ticket', 'value' => $this->trellis->user['email_staff_ticket'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['ticket'], 'for' => 1, 'msg' => '{lang.warn_email_ticket}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_reply}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_reply', 'value' => $this->trellis->user['email_staff_reply'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['reply'], 'for' => 1, 'msg' => '{lang.warn_email_reply}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_assign}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_assign', 'value' => $this->trellis->user['email_staff_assign'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['assign'], 'for' => 1, 'msg' => '{lang.warn_email_assign}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_escalate}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_escalate', 'value' => $this->trellis->user['email_staff_escalate'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['escalate'], 'for' => 1, 'msg' => '{lang.warn_email_escalate}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_hold}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_hold', 'value' => $this->trellis->user['email_staff_hold'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['hold'], 'for' => 1, 'msg' => '{lang.warn_email_hold}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_move_to}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_move_to', 'value' => $this->trellis->user['email_staff_move_to'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['move_to'], 'for' => 1, 'msg' => '{lang.warn_email_move_to}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_move_away}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_move_away', 'value' => $this->trellis->user['email_staff_move_away'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['move_away'], 'for' => 1, 'msg' => '{lang.warn_email_move_away}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_close}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_close', 'value' => $this->trellis->user['email_staff_close'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['close'], 'for' => 1, 'msg' => '{lang.warn_email_close}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_reopen}', $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'email_staff_reopen', 'value' => $this->trellis->user['email_staff_reopen'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['esnotify']['reopen'], 'for' => 1, 'msg' => '{lang.warn_email_reopen}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.receive_for}', $this->trellis->skin->checkbox( array( 'name' => 'esn_unassigned', 'title' => '{lang.unassigned_tickets}', 'value' => $this->trellis->user['esn_unassigned'] ) ) ."<br />". $this->trellis->skin->checkbox( array( 'name' => 'esn_assigned', 'title' => '{lang.assigned_tickets}', 'value' => $this->trellis->user['esn_assigned'] ) ) ."<br />". $this->trellis->skin->checkbox( array( 'name' => 'esn_assigned_to_me', 'title' => '{lang.tickets_assigned_to_me}', 'value' => $this->trellis->user['esn_assigned_to_me'] ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.email_preferences}' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.email_type}', $this->trellis->skin->custom_radio( 'email_type', array( 1 => '{lang.html}', 2 => '{lang.plain_text}' ), $this->trellis->user['email_type'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.ticket_preferences}' ) ."
                        ". $this->trellis->skin->end_group_table() ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->checkbox( 'auto_assign', '{lang.auto_assign}', $this->trellis->user['auto_assign'] ), 'a' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->checkbox( 'dfilters_assigned', '{lang.dfilters_assigned}', $this->trellis->user['dfilters_assigned'] ), 'a' ) ."
                        ". $this->trellis->skin->group_title( '{lang.my_signature}' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->textarea( array( 'name' => 'signature', 'value' => $this->trellis->user['signature'], 'cols' => 80, 'rows' => 6, 'width' => '98%', 'height' => '160px' ) ), 'a' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->checkbox( 'sig_auto', '{lang.auto_append_sig}', $this->trellis->user['sig_auto'] ), 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_settings}' ) ) ."
                        </div>";

        $this->trellis->skin->end_group( 'a' );

        $this->output .= "<script type='text/javascript'>
                        $('#columns_current, #columns_available').sortable({
                            connectWith: '.colblocks',
                            stop: function() { $('#cc_serialized').val( $('#columns_current').sortable('serialize') ); }
                        });
                        $('#cc_serialized').val( $('#columns_current').sortable('serialize') );
                        </script>";

        $this->trellis->skin->add_sidebar_general( '{lang.hey_you_title}', '{lang.hey_you_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Edit Settings
    #=======================================

    private function do_edit()
    {
        #=============================
        # Update User
        #=============================

        $db_array = array(
                          'signature'    => $this->trellis->input['signature'],
                          'sig_html'    => $this->trellis->input['sig_html'],
                          'sig_auto'    => $this->trellis->input['sig_auto'],
                          'lang'        => $this->trellis->input['lang'],
                          'time_zone'    => $this->trellis->input['time_zone'],
                          'time_dst'    => $this->trellis->input['time_dst'],
                          'rte_enable'    => $this->trellis->input['rte_enable'],
                          'email_type'    => $this->trellis->input['email_type'],
                          );

        $this->trellis->db->construct( array(
                                                   'update'    => 'users',
                                                   'set'    => $db_array,
                                                    'where'    => array( 'id', '=', $this->trellis->user['id'] ),
                                                    'limit'    => array( 1 ),
                                             )       );

        $this->trellis->db->execute();

        $db_array_old = $db_array;

        $db_array = array(
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
                          'sort_tm'                        => $this->trellis->input['sort_tm'],
                          'order_tm'                    => $this->trellis->input['order_tm'],
                          'dfilters_assigned'            => $this->trellis->input['dfilters_assigned'],
                          'auto_assign'                    => $this->trellis->input['auto_assign'],
                          );

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['cc_serialized'] ), $order );

        if ( ! empty( $order['c'] ) )
        {
            $columns = array();

            foreach( $order['c'] as $column )
            {
                $columns[ $column ] = $this->trellis->input[ 'cw_'. $column ];
            }

            $db_array['columns_tm'] = serialize( $columns );
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'users_staff',
                                                   'set'    => $db_array,
                                                    'where'    => array( 'uid', '=', $this->trellis->user['id'] ),
                                                    'limit'    => array( 1 ),
                                             )       );

        $this->trellis->db->execute();

        $this->trellis->log( array( 'msg' => 'acpset_updated', 'type' => 'other' ) );

        $this->trellis->user = array_merge( $this->trellis->user, $db_array );
        $this->trellis->user = array_merge( $this->trellis->user, $db_array_old );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_settings_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

}

?>