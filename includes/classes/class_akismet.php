<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_akismet {

    private $error;
    private $response;

    #=======================================
    # @ Constructor
    #=======================================

    public function __construct(&$trellis, $config=array())
    {
        $this->trellis = $trellis;

        #=============================
        # Get Library
        #=============================

        require_once TD_INC .'akismet/Akismet.class.php';

        $this->akismet = new Akismet( $this->trellis->config['hd_url'], $config['akismet_key'] );

        #=============================
        # Configuration
        #=============================

        if ( $config['port'] ) $this->akismet->setAPIPort( $config['port'] );
    }

    #=======================================
    # @ Check System
    #=======================================

    public function check_system()
    {
        $response = $this->akismet->isKeyValid();

        if ( $response )
        {
            $this->response = '{lang.alert_antispam_akismet_check_success}';
        }
        else
        {
            $this->error = '{lang.error_antispam_akismet_check_failed}';
        }

        return $response;
    }

    #=======================================
    # @ Create
    #=======================================

    public function create()
    {
        return false;
    }

    #=======================================
    # @ Verify
    #=======================================

    public function verify($params)
    {
        if ( $params['author'] ) $this->akismet->setCommentAuthor( $params['author'] );

        if ( $params['email'] ) $this->akismet->setCommentAuthorEmail( $params['email'] );

        if ( $params['content'] ) $this->akismet->setCommentContent( $params['content'] );

        if ( $params['type'] ) $this->akismet->setCommentType( $params['type'] );

        $response = $this->akismet->isCommentSpam();

        if ( $response ) $this->error = 'antispam_akismet_spam';

        return ! $response;
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