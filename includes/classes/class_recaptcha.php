<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_recaptcha {

    private $config;
    private $error;
    private $response;

    #=======================================
    # @ Constructor
    #=======================================

    public function __construct($trellis, $config=array())
    {
        $this->trellis = &$trellis;

        $this->config = $config;

        #=============================
        # Get Library
        #=============================

        require_once TD_INC .'recaptcha/recaptchalib.php';
    }

    #=======================================
    # @ Check System
    #=======================================

    public function check_system()
    {
        $this->response = '{lang.alert_antispam_recaptcha_cannot_check}';

        return true;
    }

    #=======================================
    # @ Create
    #=======================================

    public function create()
    {
        $this->trellis->add_lang( array( 'antispam_field' => $this->trellis->lang['antispam_captcha'] ) );

        return recaptcha_get_html( $this->config['recaptcha_key_public'], $this->error, $this->config['ssl'] );
    }

    #=======================================
    # @ Verify
    #=======================================

    public function verify()
    {
        $response = recaptcha_check_answer( $this->config['recaptcha_key_private'], $this->trellis->input['ip_address'], $this->trellis->input['recaptcha_challenge_field'], $this->trellis->input['recaptcha_response_field'] );

        if ( ! $response->is_valid ) $this->error = 'antispam_recaptcha_fail';

        return $response->is_valid;
    }

    #=======================================
    # @ Get Error
    #=======================================

    public function get_error()
    {
        return $this->error;
    }

    #=======================================
    # @ Get Response
    #=======================================

    public function get_response()
    {
        return $this->response;
    }

}

?>