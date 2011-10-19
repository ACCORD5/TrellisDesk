<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class trellis_install extends trellis {

    #=======================================
    # @ Constructor
    #=======================================

    function __construct() # Pass in config
    {
        #=============================
        # Start Execution Timer
        #=============================

        $this->start_timer();

        #=============================
        # Get Incoming Data
        #=============================

        $this->input = $this->get_post();

        #=============================
        # Load Config
        #=============================

        if ( file_exists( TD_PATH .'config.php' ) )
        {
            require_once TD_PATH .'config.php';

            $this->config = $config;
        }

        if ( ! isset( $this->config['debug_level'] ) ) $this->config['debug_level'] = 0;

        define( 'TD_DEBUG', $this->config['debug_level'] );

        if ( ! isset( $this->config['data_path'] ) ) $this->config['data_path'] = TD_PATH .'data/';
        if ( ! isset( $this->config['cache_path'] ) ) $this->config['cache_path'] = $this->config['data_path'] .'cache/';
        if ( ! isset( $this->config['logs_path'] ) ) $this->config['logs_path'] = $this->config['data_path'] .'logs/';
        if ( ! isset( $this->config['skin_compile_path'] ) ) $this->config['skin_compile_path'] = $this->config['data_path'] .'skin_compile/';
        if ( ! isset( $this->config['temp_path'] ) ) $this->config['temp_path'] = $this->config['data_path'] .'temp/';

        #=============================
        # Error Handler
        #=============================

        require_once TD_CLASS .'error_handler.php';

        if ( TD_DEBUG )
        {
            ini_set( 'display_errors', 1 );
            error_reporting( E_ALL | E_NOTICE );
        }

        $this->error = new td_class_error_handler( TD_DEBUG );

        if ( $this->config['logs_path'] ) $this->error->set_log_path( $this->config['logs_path'] .'error.log' );

        #=============================
        # Load Skin
        #=============================

        require_once TD_CLASS .'askin.php';
        require_once TD_INSTALL .'skin_install.php';

        $this->skin = new td_class_iskin( $this );
    }

    #=======================================
    # @ Initialize
    #=======================================

    public function initialize()
    {
        #=============================
        # Load Cache
        #=============================

        require_once TD_CLASS .'cache.php';

        $this->cache = new td_class_cache( $this->config['cache_path'] .'trellis/', $this->config['flatfile_key'] );

        #=============================
        # Load Language
        #=============================

        $this->lang = array(
                            'gmt_n_1200' 	=> 'GMT -12.0 (Eniwetok, Kwajalein)',
							'gmt_n_1100' 	=> 'GMT -11.0 (Midway Island, Samoa)',
							'gmt_n_1000' 	=> 'GMT -10.0 (Hawaii)',
							'gmt_n_900' 	=> 'GMT -9.0 (Alaska)',
							'gmt_n_800' 	=> 'GMT -8.0 (Pacific Time (US &amp; Canada))',
							'gmt_n_700' 	=> 'GMT -7.0 (Mountain Time (US &amp; Canada))',
							'gmt_n_600' 	=> 'GMT -6.0 (Central Time (US &amp; Canada), Mexico City)',
							'gmt_n_500' 	=> 'GMT -5.0 (Eastern Time (US &amp; Canada), Bogota, Lima)',
							'gmt_n_400' 	=> 'GMT -4.0 (Atlantic Time (Canada), Caracas, La Paz)',
							'gmt_n_350' 	=> 'GMT -3.5 (Newfoundland)',
							'gmt_n_300' 	=> 'GMT -3.0 (Brazil, Buenos Aires, Georgetown)',
							'gmt_n_200' 	=> 'GMT -2.0 (Mid-Atlantic)',
							'gmt_n_100' 	=> 'GMT -1.0 (Azores, Cape Verde Islands)',
							'gmt' 			=> 'GMT 0.0 (Western Europe Time, London, Lisbon, Casablanca)',
							'gmt_p_100' 	=> 'GMT +1.0 (Brussels, Copenhagen, Madrid, Paris)',
							'gmt_p_200' 	=> 'GMT +2.0 (Kaliningrad, South Africa)',
							'gmt_p_300' 	=> 'GMT +3.0 (Baghdad, Riyadh, Moscow, St. Petersburg)',
							'gmt_p_350' 	=> 'GMT +3.5 (Tehran)',
							'gmt_p_400' 	=> 'GMT +4.0 (Abu Dhabi, Muscat, Baku, Tbilisi)',
							'gmt_p_450' 	=> 'GMT +4.5 (Kabul)',
							'gmt_p_500' 	=> 'GMT +5.0 (Ekaterinburg, Islamabad, Karachi, Tashkent)',
							'gmt_p_550' 	=> 'GMT +5.5 (Bombay, Calcutta, Madras, New Delhi)',
							'gmt_p_600' 	=> 'GMT +6.0 (Almaty, Dhaka, Colombo)',
							'gmt_p_700' 	=> 'GMT +7.0 (Bangkok, Hanoi, Jakarta)',
							'gmt_p_800' 	=> 'GMT +8.0 (Beijing, Perth, Singapore, Hong Kong)',
							'gmt_p_900' 	=> 'GMT +9.0 (Tokyo, Seoul, Osaka, Sapporo, Yakutsk)',
							'gmt_p_950' 	=> 'GMT +9.5 (Adelaide, Darwin)',
							'gmt_p_1000' 	=> 'GMT +10.0 (Eastern Australia, Guam, Vladivostok)',
							'gmt_p_1100' 	=> 'GMT +11.0 (Magadan, Solomon Islands, New Caledonia)',
							'gmt_p_1200' 	=> 'GMT +12.0 (Auckland, Wellington, Fiji, Kamchatka)',
                            'disabled' => 'Disabled',
                            'enabled' => 'Enabled',
                            'no' => 'No',
                            'yes' => 'Yes',
                            'error_antispam_akismet_check_failed' => 'Akismet check failed. Your Akismet API key is invalid or Akismet could not reach its server.',
                            'error_antispam_phpcaptcha_check_gd' => 'GD 2 Library not available.',
                            'error_antispam_phpcaptcha_check_freetype' => 'FreeType support not available.',
                            'error_antispam_phpcaptcha_check_fonts' => 'Fonts not available.',
                            );
    }

    #=======================================
    # @ Load Database
    #=======================================

    public function load_database()
    {
        require_once TD_CLASS .'mysql.php';

        $this->db = new td_class_db_mysql( array( 'host' => $this->config['db_host'], 'port' => $this->config['db_port'], 'user' => $this->config['db_user'], 'pass' => $this->config['db_pass'], 'name' => $this->config['db_name'], 'prefix' => $this->config['db_prefix'], 'shutdown_queries' => $this->config['db_shutdown_queries'] ) );
    }

    #=======================================
    # @ Load Email
    #=======================================

    function load_email_from_array($config)
    {
        if ( ! $this->email )
        {
            require_once TD_CLASS .'email.php';

            $config = array(
                            'transport'            => $config['transport'],
                            'smtp_host'            => $config['smtp_host'],
                            'smtp_port'            => $config['smtp_port'],
                            'smtp_encryption'    => $config['smtp_encryption'],
                            'smtp_user'            => $config['smtp_user'],
                            'smtp_pass'            => $config['smtp_pass'],
                            'smtp_timeout'        => $config['smtp_timeout'],
                            'sendmail_command'    => $config['sendmail_command'],
                            'test'                => true,
                            );

            foreach ( $config as $id => $c )
            {
                if ( $id != 'transport' || $id != 'smtp_encryption' ) $config[ $id ] = $this->prepare_output( $c, array( 'html' => 1, 'entity' => 1 ) );
            }

            $this->email = new td_class_email( $this, $config );
        }
    }

    #=======================================
    # @ Load Anti-Spam
    #=======================================

    function load_antispam_from_array($config)
    {
        if ( ! file_exists( TD_CLASS . $config['method'] .'.php' ) ) trigger_error( "Anti-Spam - Class file not found: ". $config['method'], E_USER_WARNING );

        require_once TD_CLASS . $config['method'] .'.php';

        $class = 'td_class_'. $config['method'];

        $this->antispam = new $class( $this, $config );
    }

    #=======================================
    # @ Shut Down
    #=======================================

    public function shut_down()
    {
        #=============================
        # Write Cache
        #=============================

        if ( $this->cache ) $this->cache->shut_down();
    }

}

?>