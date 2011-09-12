<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_phpcaptcha {

    private $config = array( 'width' => 100, 'height' => 22, 'fontsize_max' => 16 );
    private $error;
    private $response;
    private $captcha;
    private $fonts;

    #=======================================
    # @ Constructor
    #=======================================

    public function __construct($trellis, $config=array())
    {
        $this->trellis = &$trellis;

        $this->update_config( $config );

        #=============================
        # Get Library
        #=============================

        require_once TD_INC .'phpcaptcha/php-captcha.inc.php';

        #=============================
        # Set Fonts
        #=============================

        $this->fonts = array( TD_INC .'phpcaptcha/VeraBd.ttf',  TD_INC .'phpcaptcha/VeraIt.ttf',  TD_INC .'phpcaptcha/Vera.ttf' );
    }

    #=======================================
    # @ Update Config
    #=======================================

    private function update_config($config)
    {
        if ( ! is_array( $config ) ) trigger_error( "Email - Variable passed to update_config() is not an array", E_USER_WARNING );

        if ( empty( $config ) ) return false;

        foreach ( $config as $key => $value )
        {
            $this->config[ $key ] = $value;
        }

         return true;
    }

    #=======================================
    # @ Check System
    #=======================================

    public function check_system()
    {
        $working = 1;

        if ( ! function_exists( 'gd_info' ) )
        {
            $this->error = '{lang.error_antispam_phpcaptcha_check_gd}';

            trigger_error( "PhpCaptcha - GD Library not found", E_USER_WARNING );

            return false;
        }

        $gd_info = gd_info();

        if ( ! $gd_info['FreeType Support'] )
        {
            $this->error = '{lang.error_antispam_phpcaptcha_check_freetype}';

            trigger_error( "PhpCaptcha - GD FreeType support not available", E_USER_WARNING );

            return false;
        }

        if ( ! function_exists( 'imagecreate' ) )
        {
            $this->error = '{lang.error_antispam_phpcaptcha_check_gd}';

            trigger_error( "PhpCaptcha - imagecreate() function not available", E_USER_WARNING );

            return false;
        }

        if ( ! function_exists( 'imagejpeg' ) )
        {
            $this->error = '{lang.error_antispam_phpcaptcha_check_gd}';

            trigger_error( "PhpCaptcha - GD JPEG not supported", E_USER_WARNING );

            return false;
        }

        if ( ! function_exists( 'imagecreatetruecolor' ) )
        {
            $this->error = '{lang.error_antispam_phpcaptcha_check_gd}';

            trigger_error( "PhpCaptcha - imagecreatetruecolor() function not available (likely GD version < 2)", E_USER_WARNING );

            return false;
        }

        foreach( $this->fonts as $f )
        {
            if ( ! file_exists( $f ) )
            {
                $this->error = '{lang.error_antispam_phpcaptcha_check_fonts}';

                trigger_error( "PhpCaptcha - Font file not found:". $f, E_USER_WARNING );

                return false;
            }
        }

        $this->response = '{lang.alert_antispam_phpcaptcha_check_success}';

        return true;
    }

    #=======================================
    # @ Image
    #=======================================

    public function image()
    {
        $this->captcha = new PhpCaptcha( $this->fonts );

        if ( $this->config['chars'] ) $this->captcha->SetNumChars( $this->config['chars'] );

        if ( $this->config['lines'] ) $this->captcha->SetNumLines( $this->config['lines'] );

        if ( $this->config['shadow'] ) $this->captcha->DisplayShadow( $this->config['shadow'] );

        if ( $this->config['owner'] ) $this->captcha->SetOwnerText( $this->config['owner'] );

        if ( $this->config['charset'] ) $this->captcha->SetCharSet( $this->config['charset'] );

        if ( $this->config['bg_images'] ) $this->captcha->SetBackgroundImages( $this->config['bg_images'] );

        if ( $this->config['case_insensitive'] ) $this->captcha->CaseInsensitive( $this->config['case_insensitive'] );

        if ( $this->config['fontsize_min'] ) $this->captcha->SetMinFontSize( $this->config['fontsize_min'] );

        if ( $this->config['fontsize_max'] ) $this->captcha->SetMaxFontSize( $this->config['fontsize_max'] );

        if ( $this->config['color'] ) $this->captcha->UseColour( $this->config['color'] );

        if ( $this->config['type'] ) $this->captcha->SetFileType( $this->config['type'] );

        if ( $this->config['width'] ) $this->captcha->SetWidth( $this->config['width'] );

        if ( $this->config['height'] ) $this->captcha->SetHeight( $this->config['height'] );

        $this->captcha->Create();
    }

    #=======================================
    # @ Create
    #=======================================

    public function create()
    {
        $this->trellis->add_lang( array( 'antispam' => $this->trellis->lang['antispam_captcha'], 'antispam_field' => $this->trellis->lang['antispam_captcha'] ) ); #* use this for plugin system. move other language bits into language file and out of main language files.

        return '<img src="'. $this->trellis->config['hd_url'] .'/index.php?page=antispam&amp;act=image" alt="'. $this->trellis->lang['antispam'] .'" style="vertical-align:middle;margin:-4px 0 -2px 0" />&nbsp;&nbsp;&nbsp;<input type="text" name="antispam" id="antispam" size="12" />';
    }

    #=======================================
    # @ Verify
    #=======================================

    public function verify()
    {
        $response = PhpCaptcha::Validate( $this->trellis->input['antispam'] );

        if ( ! $response ) $this->error = 'antispam_phpcaptcha_fail';

        return $response;
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