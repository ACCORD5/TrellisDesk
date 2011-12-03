<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_source_register {

    #=======================================
    # @ Auto Run
    #=======================================

    function auto_run()
    {
        #=============================
        # Initialize
        #=============================

        $this->trellis->load_functions('users');
        $this->trellis->load_lang('register');

        #=============================
        # Security Checks
        #=============================

        if ( $this->trellis->user['id'] ) $this->trellis->skin->error( 'must_be_guest' );

        switch( $this->trellis->input['act'] )
        {
            case 'add':
                $this->add_user();
            break;
            case 'resendval':
                $this->resend_val();
            break;
            case 'forgotpass':
                $this->forgot_pass();
            break;
            case 'resetpass':
                $this->reset_pass();
            break;

            case 'doadd':
                $this->do_add();
            break;
            case 'dosendval':
                $this->do_resend_val();
            break;
            case 'doforgotpass':
                $this->do_forgot_pass();
            break;
            case 'doresetpass':
                $this->do_reset_pass();
            break;

            case 'dovalidate':
                $this->do_validate();
            break;

            default:
                $this->add_user();
            break;
        }
    }

    #=======================================
    # @ Add User
    #=======================================

    function add_user($params=array())
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->cache->data['settings']['security']['registration'] ) $this->trellis->skin->error('registration_disabled');

        #=============================
        # Custom Profile Fields
        #=============================

        $this->trellis->load_functions('cpfields');

        if ( $cfields = $this->trellis->func->cpfields->grab( 2 ) ) // Guest group id = 2
        {
            foreach( $cfields as $f )
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
        }

        #=============================
        # Anitspam
        #=============================

        if ( $this->trellis->cache->data['settings']['antispam']['enable'] )
        {
            $this->trellis->load_antispam();

            if ( $antispam_html = $this->trellis->antispam->create() )
            {
                $this->trellis->skin->set_var( 'antispam', $antispam_html );
            }
        }

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] )
        {
            $params['error'] = $this->trellis->lang[ 'err_'. $params['error'] ];

            if ( $params['extra'] ) $params['error'] .= ' '. $params['extra'];

            $this->trellis->skin->set_var( 'error', $params['error'] );
        }

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=register">'. $this->trellis->lang['register'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'register.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['register'] ) );
    }

    #=======================================
    # @ Do Add User
    #=======================================

    function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->cache->data['settings']['security']['registration'] ) $this->trellis->skin->error('registration_disabled');

        if ( ! $this->trellis->input['name'] ) $this->add_user( array( 'error' => 'no_name' ) );

        if ( ! $this->trellis->validate_email( $this->trellis->input['email'] ) ) $this->add_user( array( 'error' => 'no_email_valid' ) );

        if ( ! $this->trellis->input['pass'] ) $this->add_user( array( 'error' => 'no_pass' ) );

        if ( $this->trellis->input['pass'] != $this->trellis->input['passb'] ) $this->add_user( array( 'error' => 'no_pass_match' ) );

        #=============================
        # Anitspam
        #=============================

        if ( $this->trellis->cache->data['settings']['antispam']['enable'] )
        {
            $this->trellis->load_antispam();

            $antispam_fields = array(
                                     'author'    => $this->trellis->input['name'],
                                     'email'    => $this->trellis->input['email'],
                                     'type'        => 'registration',
                                     );

            if ( ! $this->trellis->antispam->verify( $antispam_fields ) ) $this->add_user( array( 'error' => $this->trellis->antispam->get_error() ) );
        }

        #=============================
        # Check Name & Email
        #=============================

        if ( $this->trellis->func->users->check_name( $this->trellis->input['name'] ) ) $this->add_user( array( 'error' => 'name_in_use' ) );

        if ( $this->trellis->func->users->check_email( $this->trellis->input['email'] ) ) $this->add_user( array( 'error' => 'email_in_use' ) );

        #=============================
        # Add User
        #=============================

        $db_array = array(
                          'name'                    => $this->trellis->input['name'],
                          'email'                    => $this->trellis->input['email'],
                          'password'                => $this->trellis->input['pass'],
                          'ugroup'                    => 1,
                          'joined'                    => time(),
                          'sig_auto'                => 1,
                          'time_dst'                => 2,
                          'rte_enable'                => 1,
                          'email_enable'            => 1,
                          'email_ticket'            => 1,
                          'email_action'            => 1,
                          'email_news'                => 1,
                          'email_type'                => 1,
                          'val_email'                => 1,
                          'val_admin'                => 1,
                          'ipadd'                    => $this->trellis->input['ip_address'],
                          );

        $this->trellis->load_functions('cpfields');

		if (!empty($this->trellis->cache->data['pfields'])){
        if( ! $fdata = $this->trellis->func->cpfields->process_input() )
        {
            if ( $this->trellis->func->cpfields->required_field ) $this->add_user( array( 'error' => 'no_field', 'extra' => $this->trellis->func->cpfields->required_field ) );
        }
        else
        {
            $user_id = $this->trellis->func->users->add( $db_array );

            $this->trellis->func->cpfields->set_data( $fdata, $user_id, 1 );
        }
		}
		else {
		 $user_id = $this->trellis->func->users->add( $db_array );
		}

        $this->trellis->log( 'user', "User Added &#039;". $this->trellis->input['name'] ."&#039;", 1, $user_id );

        #=============================
        # Redirect
        #=============================

        if ( $this->trellis->cache->data['settings']['security']['validation_email'] && $this->trellis->cache->data['settings']['security']['validation_admin'] )
        {
            $this->trellis->skin->redirect( '?page=dasboard', 'new_user_val_both', 12 );
        }
        elseif ( $this->trellis->cache->data['settings']['security']['validation_email'] )
        {
            $this->trellis->skin->redirect( '?page=dasboard', 'new_user_val_email', 10 );
        }
        elseif ( $this->trellis->cache->data['settings']['security']['validation_admin'] )
        {
            $this->trellis->skin->redirect( '?page=dasboard', 'new_user_val_admin', 10 );
        }
        else
        {
            $this->trellis->skin->redirect( '?page=dashboard', 'new_user_no_val', 5 );
        }
    }

    #=======================================
    # @ Do Validate Email
    #=======================================

    function do_validate()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['key'] ) $this->trellis->skin->error('no_email_val_key');

        #=============================
        # Validate Email
        #=============================

        if ( ! $response = $this->trellis->func->users->validate_email( $this->trellis->input['key'] ) ) $this->trellis->skin->error('no_email_val_key');

        #=============================
        # Redirect
        #=============================

        if ( $response == 2 )
        {
            $this->trellis->skin->redirect( '?act=dashboard', 'success_acc_activate', 5 );
        }
        else
        {
            $this->trellis->skin->redirect( '?act=dashboard', 'almost_acc_activate', 10 );
        }
    }

    #=======================================
    # @ Resend Validation Form
    #=======================================

    function resend_val($error='')
    {
        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );
        }

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=register&amp;act=resendval">'. $this->trellis->lang['resend_val'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'reg_resend_val.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['resend_val'] ) );
    }

    #=======================================
    # @ Do Resend Validation
    #=======================================

    function do_resend_val()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['name'] && ! $this->trellis->input['email'] ) $this->resend_val('no_name_or_email');

        if ( ! $this->trellis->input['name'] && ! $this->trellis->validate_email( $this->trellis->input['email'] ) ) $this->resend_val('no_email_valid');

        #=============================
        # Resend Validation
        #=============================

        if ( $this->trellis->input['name'] )
        {
            if ( ! $this->trellis->func->users->resend_validation( $this->trellis->input['name'], 'name' ) ) $this->resend_val('resend_val_fail');
        }
        elseif ( $this->trellis->input['email'] )
        {
            if ( ! $this->trellis->func->users->resend_validation( $this->trellis->input['email'], 'email' ) ) $this->resend_val('resend_val_fail');
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( '?act=dashboard', 'new_user_val_resend', 8 );
    }

    #=======================================
    # @ Forgot Password Form
    #=======================================

    function forgot_pass($error='')
    {
        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );
        }

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=register&amp;act=forgotpass">'. $this->trellis->lang['forgot_password'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'reg_forgot_pass.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['forgot_password'] ) );
    }

    #=======================================
    # @ Do Forgot Password
    #=======================================

    function do_forgot_pass()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['name'] && ! $this->trellis->input['email'] ) $this->forgot_pass('no_name_or_email');

        if ( ! $this->trellis->input['name'] && ! $this->trellis->validate_email( $this->trellis->input['email'] ) ) $this->forgot_pass('no_email_valid');

        #=============================
        # Forgot Password Validation
        #=============================

        if ( $this->trellis->input['name'] )
        {
            if ( ! $this->trellis->func->users->forgot_password( $this->trellis->input['name'], 'name' ) ) $this->forgot_pass('user_not_found');
        }
        elseif ( $this->trellis->input['email'] )
        {
            if ( ! $this->trellis->func->users->forgot_password( $this->trellis->input['email'], 'email' ) ) $this->forgot_pass('user_not_found');
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( '?act=dashboard', 'reset_pass_email_sent', 8 );
    }

    #=======================================
    # @ Reset Password Form
    #=======================================

    function reset_pass($error='')
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['key'] ) $this->trellis->skin->error('no_email_val_key');

        if ( ! $this->trellis->func->users->check_reset_pswd_key( $this->trellis->input['key'] ) ) $this->trellis->skin->error('no_email_val_key');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );
        }

        $this->nav = array(
                           $this->trellis->lang['reset_password'],
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'reg_reset_pass.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['reset_password'] ) );
    }

    #=======================================
    # @ Do Reset Password
    #=======================================

    function do_reset_pass()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['key'] ) $this->trellis->skin->error('no_email_val_key');

        if ( ! $this->trellis->input['new_pass'] ) $this->reset_pass('no_pass');

        if ( $this->trellis->input['new_pass'] != $this->trellis->input['new_pass_b'] ) $this->reset_pass('no_pass_match');

        if ( ! $uid = $this->trellis->func->users->check_reset_pswd_key( $this->trellis->input['key'], 1 ) ) $this->trellis->skin->error('no_email_val_key');

        #=============================
        # Reset Password
        #=============================

        $this->trellis->func->users->change_password( $this->trellis->input['new_pass'], $uid );

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( '?act=dashboard', 'reset_pass_success' );
    }

}

?>