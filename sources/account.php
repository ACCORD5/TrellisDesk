<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_source_account {

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['id'] ) $this->trellis->skin->error( 'must_be_user', 1 );

        #=============================
        # Initialize
        #=============================

        $this->trellis->load_lang('account');

        switch( $this->trellis->input['act'] )
        {
            case 'edit':
                $this->modify_form();
            break;
            case 'pass':
                $this->pass_form();
            break;
            case 'email':
                $this->email_form();
            break;

            case 'doedit':
                $this->do_modify();
            break;
            case 'dopass':
                $this->do_pass();
            break;
            case 'doemail':
                $this->do_email();
            break;

            case 'dovalidate':
                $this->validate_email();
            break;

            default:
                $this->show_overview();
            break;
        }
    }

    #=======================================
    # @ Show Overview
    #=======================================

    private function show_overview($alert='')
    {
        #=============================
        # Format Information
        #=============================

        $this->trellis->user['joined_human'] = $this->trellis->td_timestamp( array( 'time' => $this->trellis->user['joined'], 'format' => 'long' ) );

        #=============================
        # Custom Profile Fields
        #=============================

        $this->trellis->load_functions('cpfields');

        if ( $cfields = $this->trellis->func->cpfields->grab( $this->trellis->user['ugroup'] ) )
        {
            $fdata = $this->trellis->func->cpfields->get_data( $this->trellis->user['id'] );

            foreach( $cfields as &$f )
            {
                if ( $f['type'] == 'checkbox' )
                {
                    $f['extra'] = unserialize( $f['extra'] );
                }
                elseif ( $f['type'] == 'dropdown' || $f['type'] == 'radio' )
                {
                    $f['extra'] = unserialize( $f['extra'] );
                }
                else
                {
                    if ( ! $fdata[ $f['id'] ] ) $fdata[ $f['id'] ] = '--';
                }
            }

            $this->trellis->skin->set_var( 'cpfields', $cfields );
            $this->trellis->skin->set_var( 'cpfdata', $fdata );
        }

        #=============================
        # Do Output
        #=============================

        if ( $alert ) $this->trellis->skin->set_var( 'alert', $this->trellis->lang[ 'alert_'. $alert ] );

        $this->trellis->skin->set_var( 'sub_tpl', 'account.tpl' );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=account">'. $this->trellis->lang['my_account'] .'</a>',
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['my_account'] ) );
    }

    #=======================================
    # @ Modify Form
    #=======================================

    function modify_form($error="", $extra="")
    {
        #=============================
        # Drop Downs
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->trellis->skin->set_var( 'time_zone_options', $this->trellis->func->drop_downs->get_time_zones() );
        $this->trellis->skin->set_var( 'dts_options', array( 0 => $this->trellis->lang['inactive'], 1 => $this->trellis->lang['active'], 2 => $this->trellis->lang['auto'] ) );
        $this->trellis->skin->set_var( 'lang_options', $this->trellis->func->drop_downs->get_languages() );
        $this->trellis->skin->set_var( 'skin_options', $this->trellis->func->drop_downs->get_skins() );
        $this->trellis->skin->set_var( 'email_type_options', array( 1 => $this->trellis->lang['html'], 2 => $this->trellis->lang['plain_text'] ) );

        #=============================
        # Custom Profile Fields
        #=============================

        $this->trellis->load_functions('cpfields');

        if ( $cfields = $this->trellis->func->cpfields->grab( $this->trellis->user['ugroup'] ) )
        {
            $fdata = $this->trellis->func->cpfields->get_data( $this->trellis->user['id'] );

            foreach( $cfields as &$f )
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

            $this->trellis->skin->set_var( 'cpfields', $cfields );
            $this->trellis->skin->set_var( 'cpfdata', $fdata );
        }

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $error = $this->trellis->lang[ 'err_'. $error ];

            if ( $extra ) $error .= ' '. $extra;

            $this->trellis->skin->set_var( 'error', $error );
        }

        $this->trellis->skin->set_var( 'time_now', $this->trellis->td_timestamp( array( 'time' => time(), 'format' => 'long' ) ) );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=account">'. $this->trellis->lang['my_account'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=account&amp;act=edit">'. $this->trellis->lang['modify_account'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'acc_modify.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['modify_account'] ) );
    }

    #=======================================
    # @ Do Modify
    #=======================================

    function do_modify()
    {
        $this->trellis->load_functions('users');

        #=============================
        # Update User
        #=============================

        $db_array = array(
                          'time_zone'            => $this->trellis->input['time_zone'],
                          'time_dst'            => $this->trellis->input['time_dst'],
                          'rte_enable'            => $this->trellis->input['rte_enable'],
                          'email_enable'        => $this->trellis->input['email_enable'],
                          'email_ticket'        => $this->trellis->input['email_ticket'],
                          'email_action'            => $this->trellis->input['email_action'],
                          'email_news'            => $this->trellis->input['email_news'],
                          'email_type'            => $this->trellis->input['email_type'],
                          );

        if ( $this->trellis->user['g_change_lang'] ) $db_array['lang'] = $this->trellis->input['user_lang'];
        if ( $this->trellis->user['g_change_skin'] ) $db_array['skin'] = $this->trellis->input['user_skin'];

        $this->trellis->load_functions('cpfields');

        if( ! $fdata = $this->trellis->func->cpfields->process_input() )
        {
            if ( $this->trellis->func->cpfields->required_field ) $this->modify_form( 'no_field', $this->trellis->func->cpfields->required_field );
        }
        else
        {
            $this->trellis->func->users->edit( $db_array, $this->trellis->user['id'] );

            $this->trellis->func->cpfields->set_data( $fdata, $this->trellis->user['id'] );
        }

        $this->trellis->func->users->edit( $db_array, $this->trellis->user['id'] );

        $this->trellis->log( array( 'msg' => array( 'user_edited', $this->trellis->user['name'] ), 'type' => 'user', 'content_type' => 'user', 'content_id' => $this->trellis->user['id'] ) );

        #=============================
        # Do Output
        #=============================

        $this->trellis->user = array_merge( $this->trellis->user, $db_array );

        $this->show_overview( 'account_updated' );
    }

    #=======================================
    # @ Pass Form
    #=======================================

    function pass_form($error='')
    {
        #=============================
        # Do Output
        #=============================

        if ( $error ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=account">'. $this->trellis->lang['my_account'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=account&amp;act=pass">'. $this->trellis->lang['change_password'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'acc_change_pass.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['change_password'] ) );
    }

    #=======================================
    # @ Do Pass
    #=======================================

    function do_pass()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['current_pass'] ) $this->pass_form('no_pass_short');

        if ( $this->trellis->input['new_pass'] != $this->trellis->input['new_pass_b'] ) $this->pass_form('no_pass_match');

        if ( ! $this->trellis->input['new_pass'] ) $this->pass_form('no_new_pass_short');

        #=============================
        # Update User
        #=============================

        $this->trellis->load_functions('users');

        if ( ! $this->trellis->func->users->check_password( $this->trellis->input['current_pass'], $this->trellis->user['id'] ) ) $this->pass_form('login_no_pass');

        $this->trellis->func->users->change_password( $this->trellis->input['new_pass'], $this->trellis->user['id'] );

        $this->trellis->log( array( 'msg' => array( 'user_password', $this->trellis->user['name'] ), 'type' => 'security', 'content_type' => 'user', 'content_id' => $this->trellis->user['id'] ) );

        #=============================
        # Do Output
        #=============================

        $this->show_overview( 'password_updated' );
    }

    #=======================================
    # @ Email Form
    #=======================================

    function email_form($error='')
    {
        #=============================
        # Do Output
        #=============================

        if ( $error ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=account">'. $this->trellis->lang['my_account'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=account&amp;act=email">'. $this->trellis->lang['change_email'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'acc_change_email.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['change_email'] ) );
    }

    #=======================================
    # @ Do Email
    #=======================================

    function do_email()
    {
        #=============================
        # Security Checks
        #=============================

        if ( $this->trellis->input['new_email'] != $this->trellis->input['new_email_b'] ) $this->email_form('no_email_match');

        if ( $this->trellis->user['email'] == $this->trellis->input['new_email'] ) $this->email_form('no_email_change');

        #=============================
        # Check Email
        #=============================

        $this->trellis->load_functions('users');

        if ( $this->trellis->func->users->check_email( $this->trellis->input['new_email'] ) ) $this->email_form('email_in_use');

        #=============================
        # Change Email
        #=============================

        if ( ! $response = $this->trellis->func->users->change_email( $this->trellis->input['new_email'], $this->trellis->user['id'] ) ) $this->email_form('no_email_valid');

        $this->trellis->log( array( 'msg' => array( 'user_email_init', $this->trellis->user['name'] ), 'type' => 'user', 'content_type' => 'user', 'content_id' => $this->trellis->user['id'] ) );

        #=============================
        # Do Output
        #=============================

        if ( $response == 1 )
        {
            $this->show_overview( 'email_almost_updated' );
        }
        else
        {
            $this->trellis->user['email'] = $response;

            $this->show_overview( 'email_updated' );
        }
    }

    #=======================================
    # @ Validate Email
    #=======================================

    function validate_email()
    {
        $this->trellis->load_functions('users');

        if ( ! $email = $this->trellis->func->users->validate_email( $this->trellis->input['key'] ) ) $this->trellis->skin->error('no_email_val_key');

        $this->trellis->log( array( 'msg' => array( 'user_email_completed', $this->trellis->user['name'] ), 'type' => 'user', 'content_type' => 'user', 'content_id' => $this->trellis->user['id'] ) );

        #=============================
        # Do Output
        #=============================

        $this->trellis->user['email'] = $email;

        $this->show_overview( 'email_verified' );
    }

}

?>