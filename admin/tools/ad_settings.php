<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_settings {

    private $output = "";
    private $error = "";
    private $alert = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'settings' );

        $this->trellis->load_functions('settings');
        $this->trellis->load_lang('settings');

        $this->trellis->skin->set_active_link( 4 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_settings();
            break;
            case 'edit':
                $this->edit_settings();
            break;

            case 'doedit':
                $this->do_edit();
            break;
            case 'dorevert':
                $this->do_revert();
            break;
            case 'dodefault':
                $this->do_default();
            break;
            case 'dodefaultall':
                $this->do_default_all();
            break;

            default:
                $this->list_settings();
            break;
        }
    }

    #=======================================
    # @ List Settings
    #=======================================

    private function list_settings()
    {
        #=============================
        # Grab Settings
        #=============================

        $setting_rows = "";

        if ( ! $settings = $this->trellis->func->settings->get_groups( array( 'select' => array( 'cg_id', 'cg_key', 'cg_set_count' ), 'where' => array( 'cg_hide', '!=', 1 ), 'order' => array( 'cg_key' => 'asc' ) ) ) )
        {
            $setting_rows .= "<tr><td class='bluecell-light' colspan='3'><strong>{lang.no_settings}</strong></td></tr>";
        }
        else
        {
            foreach( $settings as $cgid => $cg )
            {
                $setting_rows .= "<tr>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group={$cg['cg_key']}'>{lang.grp_{$cg['cg_key']}}</a></td>
                                    <td class='bluecellthin-light' style='font-weight: normal'>{lang.grp_{$cg['cg_key']}_desc}</td>
                                    <td class='bluecellthin-light' align='center'>{$cg['cg_set_count']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group={$cg['cg_key']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                </tr>";
            }
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.settings_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='22%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='72%' align='left'>{lang.description}</th>
                            <th class='bluecellthin-th' width='3%' align='left'>{lang.settings}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                        </tr>
                        ". $setting_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=settings' ),
                            array( 'mail_pencil', '{lang.menu_etemplates}', '<! TD_URL !>/admin.php?section=look&amp;page=emails' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_settings_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_settings_title}', '{lang.help_about_settings_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Settings
    #=======================================

    private function edit_settings()
    {
        #=============================
        # Grab Settings
        #=============================

        $cg_key = &$this->trellis->input['group'];

        if ( ! $settings = $this->trellis->func->settings->get_settings( array( 'select' => array( 'cf_id', 'cf_key', 'cf_type', 'cf_default', 'cf_extra', 'cf_value', 'cf_value_old', 'cf_callback' ), 'where' => array( 'cf_group', '=', $cg_key ), 'order' => array( 'cf_position' => 'asc' ) ) ) ) $this->trellis->skin->error('no_settings');

        #=============================
        # Do Output
        #=============================

        $settings_rows = "";

        foreach( $settings as $cf )
        {
            if ( $cf['cf_callback'] )
            {
                #* Use this for plugin system later

                $function = $cg_key .'_'. $cf['cf_key'];

               $cf = $this->$function( $cf );
            }
            elseif ( $cf['cf_type'] == 'textfield' )
            {
                $cf['cf_html'] = $this->trellis->skin->textfield( array( 'name' => 'cf_'. $cf['cf_id'], 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );
            }
            elseif ( $cf['cf_type'] == 'password' )
            {
                $cf['cf_html'] = $this->trellis->skin->password( array( 'name' => 'cf_'. $cf['cf_id'], 'value' => $cf['cf_value'], 'id' => $cf['cf_id'] ) );
            }
            elseif ( $cf['cf_type'] == 'textarea' )
            {
                $cf['cf_html'] = $this->trellis->skin->textarea( array( 'name' => 'cf_'. $cf['cf_id'], 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );
            }
            elseif ( $cf['cf_type'] == 'yes_no' )
            {
                $cf['cf_html'] = $this->trellis->skin->yes_no_radio( array( 'name' => 'cf_'. $cf['cf_id'], 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );
            }
            elseif ( $cf['cf_type'] == 'enabled_disabled' )
            {
                $cf['cf_html'] = $this->trellis->skin->enabled_disabled_radio( array( 'name' => 'cf_'. $cf['cf_id'], 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );
            }
            elseif ( $cf['cf_type'] == 'dropdown' )
            {
                $cf['cf_html'] = $this->trellis->skin->drop_down( array( 'name' => 'cf_'. $cf['cf_id'], 'options' => unserialize( $cf['cf_extra'] ), 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );
            }
            elseif ( $cf['cf_type'] == 'radio' )
            {
                $cf['cf_html'] = $this->trellis->skin->custom_radio( array( 'name' => 'cf_'. $cf['cf_id'], 'options' => unserialize( $cf['cf_extra'] ), 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );
            }

            if ( $this->trellis->lang[ 'set_'. $cg_key .'_'. $cf['cf_key'] .'_desc' ] )
            {
                $settings_rows .= $this->trellis->skin->group_table_row( '{lang.set_'. $cg_key .'_'. $cf['cf_key'] .'}' .' '. $this->trellis->skin->help_tip( '{lang.set_'. $cg_key .'_'. $cf['cf_key'] .'_desc}' ), $cf['cf_html'], 'a', '35%', '65%', $cf['cf_hide'], 'cfr_'. $cf['cf_id'] );
            }
            else
            {
                $settings_rows .= $this->trellis->skin->group_table_row( '{lang.set_'. $cg_key .'_'. $cf['cf_key'] .'}', $cf['cf_html'], 'a', '35%', '65%', $cf['cf_hide'], 'cfr_'. $cf['cf_id'] );
            }
        }

        if ( $this->get_setting_alert() )
        {
            $this->output .= $this->trellis->skin->alert_wrap( $this->get_setting_alert() );
        }

        if ( $this->get_setting_error() )
        {
            $this->output .= $this->trellis->skin->error_wrap( $this->get_setting_error() );
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=doedit&amp;group={$cg_key}", 'edit_setting', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_settings} {lang.grp_'. $cg_key .'}', 'a' ) ."
                        ". $settings_rows .
                        $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_settings}' ) ) ."
                        </div>";

        if ( ! empty( $this->javascript ) )
        {
            $this->output .= "<script language='javascript' type='text/javascript'>\n";

            foreach ( $this->javascript as $javascript )
            {
                $this->output .= $javascript ."\n";
            }

            $this->output .= "</script>";
        }

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings' ),
                            array( 'arrow_circle_back', '{lang.menu_default}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=dodefaultall&amp;group='. $cg_key ),
                            );

        if ( $cg_key == 'email' || $cg_key == 'eunotify' || $cg_key == 'esnotify' ) $menu_items[] = array( 'mail_pencil', '{lang.menu_etemplates}', '<! TD_URL !>/admin.php?section=look&amp;page=emails' );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_settings_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Edit Settings
    #=======================================

    private function do_edit()
    {
        #=============================
        # Update Settings
        #=============================

        if ( ! $settings = $this->trellis->func->settings->get_settings( array( 'select' => array( 'cf_id', 'cf_type', 'cf_value' ), 'where' => array( 'cf_group', '=', $this->trellis->input['group'] ) ) ) ) $this->trellis->skin->error('no_settings');

        foreach( $settings as $cf )
        {
            if ( isset( $this->trellis->input[ 'cf_'. $cf['cf_id'] ] ) && $cf['cf_value'] != $this->trellis->input[ 'cf_'. $cf['cf_id'] ] )
            {
                if ( $cf['cf_type'] == 'password' ) $cf['cf_value'] = ""; // Let's not store old passwords

                if ( $cf['cf_type'] == 'yes_no' || $cf['cf_type'] == 'enabled_disabled' ) $this->trellis->input[ 'cf_'. $cf['cf_id'] ] = intval( $this->trellis->input[ 'cf_'. $cf['cf_id'] ] );

                $this->trellis->func->settings->edit( array( 'cf_value' => $this->trellis->input[ 'cf_'. $cf['cf_id'] ], 'cf_value_old' => $cf['cf_value'] ), $cf['cf_id'] );
            }
        }

        $this->trellis->log( array( 'msg' => array( 'settings_edited', $this->trellis->lang[ 'grp_'. $this->trellis->input['group'] ] ), 'type' => 'settings', 'content_type' => 'settings', 'content_id' => $this->trellis->input['group'] ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->settings_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_settings_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'edit', 'group' => $this->trellis->input['group'] ) );
    }

    #=======================================
    # @ Do Revert Setting
    #=======================================

    private function do_revert()
    {
        #=============================
        # Revert Setting
        #=============================

        if ( ! $cf = $this->trellis->func->settings->get_single_setting( array( 'cf_id', 'cf_key', 'cf_group', 'cf_value_old' ), array( 'cf_id', '=', $this->trellis->input['id'] ) ) ) $this->trellis->skin->error('no_settings');

        $this->trellis->func->settings->edit( array( 'cf_value' => $cf['cf_value_old'] ), $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'setting_reverted', $this->trellis->lang[ 'set_'. $cf['cf_group'] .'_'. $cf['cf_key'] ], $this->trellis->lang[ 'grp_'. $cf['cf_group'] ] ), 'type' => 'settings', 'level' => 2, 'content_type' => 'settings', 'content_id' => $cf['cf_group'] ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->settings_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_setting_reverted'] );

        $this->trellis->skin->redirect( array( 'act' => 'edit', 'group' => $cf['cf_group'] ) );
    }

    #=======================================
    # @ Do Default Setting
    #=======================================

    private function do_default()
    {
        #=============================
        # Default Setting
        #=============================

        if ( ! $cf = $this->trellis->func->settings->get_single_setting( array( 'cf_id', 'cf_key', 'cf_group', 'cf_default' ), array( 'cf_id', '=', $this->trellis->input['id'] ) ) ) $this->trellis->skin->error('no_settings');

        $this->trellis->func->settings->edit( array( 'cf_value' => $cf['cf_default'] ), $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'setting_default', $this->trellis->lang[ 'set_'. $cf['cf_group'] .'_'. $cf['cf_key'] ], $this->trellis->lang[ 'grp_'. $cf['cf_group'] ] ), 'type' => 'settings', 'level' => 2, 'content_type' => 'settings', 'content_id' => $cf['cf_group'] ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->settings_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_setting_defaulted'] );

        $this->trellis->skin->redirect( array( 'act' => 'edit', 'group' => $cf['cf_group'] ) );
    }

    #=======================================
    # @ Do Default Settings
    #=======================================

    private function do_default_all()
    {
        #=============================
        # Default Setting
        #=============================

        if ( ! $settings = $this->trellis->func->settings->get_settings( array( 'select' => array( 'cf_id', 'cf_value', 'cf_default' ), 'where' => array( 'cf_group', '=', $this->trellis->input['group'] ) ) ) ) $this->trellis->skin->error('no_settings');

        foreach( $settings as $cf )
        {
            if ( $cf['cf_value'] != $cf['cf_default'] )
            {
                $this->trellis->func->settings->edit( array( 'cf_value' => $cf['cf_default'], 'cf_value_old' => $cf['cf_value'] ), $cf['cf_id'] );
            }
        }

        $this->trellis->log( array( 'msg' => array( 'settings_default', $this->trellis->lang[ 'grp_'. $this->trellis->input['group'] ] ), 'type' => 'settings', 'level' => 2, 'content_type' => 'settings', 'content_id' => $this->trellis->input['group'] ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->settings_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_settings_defaulted'] );

        $this->trellis->skin->redirect( array( 'act' => 'edit', 'group' => $this->trellis->input['group'] ) );
    }

    #=======================================
    # @ Get Setting Error
    #=======================================

    private function get_setting_error()
    {
        if ( $this->error )
        {
            return $this->error;
        }
        else
        {
            return false;
        }
    }

    #=======================================
    # @ Set Setting Error
    #=======================================

    private function set_setting_error($error)
    {
        $this->error = $error;
    }

    #=======================================
    # @ Get Setting Alert
    #=======================================

    private function get_setting_alert()
    {
        if ( $this->alert )
        {
            return $this->alert;
        }
        else
        {
            return false;
        }
    }

    #=======================================
    # @ Set Setting Alert
    #=======================================

    private function set_setting_alert($alert)
    {
        $this->alert = $alert;
    }

    #=======================================
    # @ Email Transport Check
    #=======================================

    private function email_transport($cf)
    {
        $cf['cf_html'] = $this->trellis->skin->drop_down( array( 'name' => 'cf_'. $cf['cf_id'], 'options' => unserialize( $cf['cf_extra'] ), 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );

        if ( $this->trellis->cache->data['settings']['email']['enable'] )
        {
            $this->trellis->load_email();

            $test_msg = ' <a href="<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=email&test=1">{lang.click_here}</a> {lang.alert_email_test}';

            if ( $this->trellis->input['test'] )
            {
                if ( ! $this->trellis->email->send_email_now( array( 'to' => $this->trellis->cache->data['settings']['email']['out_address'], 'name' => $this->trellis->user['name'], 'msg' => 'test', 'bypass' => 1 ) ) )
                {
                    $this->set_setting_error( '{lang.alert_email_test_failed}'. $test_msg );
                }
                else
                {
                    $this->set_setting_alert( '{lang.alert_email_test_sent}'. $test_msg );
                }
            }
            else
            {
                if ( $this->trellis->cache->data['settings']['email']['transport'] == 'smtp' || $this->trellis->cache->data['settings']['email']['transport'] == 'sendmail' )
                {
                    if ( ! $this->trellis->email->test() )
                    {
                        if ( $this->trellis->cache->data['settings']['email']['transport'] == 'smtp' )
                        {
                            $transports = stream_get_transports();

                            ( $this->trellis->cache->data['settings']['email']['smtp_encryption'] ) ? $tp = $this->trellis->cache->data['settings']['email']['smtp_encryption'] : $tp = 'tcp';

                            if ( ! in_array( $tp, $transports ) )
                            {
                                if ( $this->trellis->cache->data['settings']['email']['smtp_encryption'] == 'tls' )
                                {
                                    $warn_msg = '{lang.error_email_transport_smtp_tls}';
                                }
                                elseif ( $this->trellis->cache->data['settings']['email']['smtp_encryption'] == 'ssl' )
                                {
                                    $warn_msg = '{lang.error_email_transport_smtp_ssl}';
                                }
                                else
                                {
                                    $warn_msg = '{lang.error_email_transport_smtp_tcp}';
                                }
                            }
                            else
                            {
                                $warn_msg = '{lang.error_email_transport_smtp}';
                            }

                            if( $this->trellis->email->get_exception() ) $warn_msg .= ' {lang.following_error_returned}<br /><br />'. $this->trellis->email->get_exception();
                        }
                        elseif ( $this->trellis->cache->data['settings']['email']['transport'] == 'sendmail' )
                        {
                            $warn_msg = '{lang.error_email_transport_sendmail}';
                        }

                        $this->set_setting_error( $warn_msg );
                    }
                    else
                    {
                        $this->set_setting_alert( '{lang.alert_email_transport_'. $this->trellis->cache->data['settings']['email']['transport'] .'_success}'. $test_msg );
                    }
                }
                elseif ( $this->trellis->cache->data['settings']['email']['transport'] == 'mail' )
                {
                    $this->set_setting_alert( '{lang.alert_email_transport_mail}'. $test_msg );
                }
            }
        }

        return $cf;
    }

    #=======================================
    # @ Anti-Spam Method Check
    #=======================================

    private function antispam_method($cf)
    {
        $cf['cf_html'] = $this->trellis->skin->drop_down( array( 'name' => 'cf_'. $cf['cf_id'], 'options' => unserialize( $cf['cf_extra'] ), 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );

        if ( $this->trellis->cache->data['settings']['antispam']['enable'] )
        {
            $this->trellis->load_antispam();

            if ( $this->trellis->antispam->check_system() )
            {
                $this->set_setting_alert( $this->trellis->antispam->get_response() );
            }
            else
            {
                $this->set_setting_error( $this->trellis->antispam->get_error() );
            }
        }

        return $cf;
    }

    #=======================================
    # @ Security Email Validation Check
    #=======================================

    private function security_validation_email($cf)
    {
        $cf['cf_html'] = $this->trellis->skin->yes_no_radio( array( 'name' => 'cf_'. $cf['cf_id'], 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['email']['enable'], 'for' => 1, 'msg' => '{lang.warn_email_enable}' ) ) );

        return $cf;
    }

    #=======================================
    # @ Ticket Mask
    #=======================================

    private function ticket_mask($cf)
    {
        $cf['cf_html'] = $this->trellis->skin->textfield( array( 'name' => 'cf_'. $cf['cf_id'], 'value' => $cf['cf_value'], 'revert' => $cf['cf_value_old'], 'default' => $cf['cf_default'], 'id' => $cf['cf_id'] ) );

        $this->trellis->load_functions('tickets');

        $this->set_setting_alert( '{lang.alert_ticket_mask_sample} '. $this->trellis->func->tickets->generate_mask( 1, $cf['cf_value'] ) );

        return $cf;
    }

}

?>