<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_admin {

    #=======================================
    # @ Version Check
    #=======================================

    function version_check()
    {
        $do_check = 0;

        if ( $this->trellis->input['vcheck'] )
        {
            $do_check = 1;
        }
        elseif ( $this->trellis->cache->data['misc']['vcheck_time'] < ( time() - ( 60 * 60 * 24 * 7 ) ) )
        {
            $do_check = 1;
        }

        if ( $do_check )
        {
            $response = 0;

            $version_check_url = 'http://dev.sogonphp.com/stuff/versioncheck.php?v='. $this->trellis->version_number;

            $postdata = array( 'system_id' => sha1( $this->trellis->config['hd_url'] ) );

            if ( $this->trellis->cache->data['settings']['security']['vcheck_share'] )
            {
                $postdata['install_date'] = $this->trellis->config['start'];
            }

            if ( $this->trellis->cache->data['settings']['security']['vcheck_share'] == 2 )
            {
                $postdata['url'] = $this->trellis->config['hd_url'];
            }

            $postquery = http_build_query( $postdata, null, '&' );

            if ( ini_get('allow_url_fopen') )
            {
                $opts = array( 'http' => array(
                                               'method'        => 'POST',
                                               'timeout'    => 4,
                                               ) );

                if ( $postquery ) $opts['http']['content'] = $postquery;

                $context = stream_context_create( $opts );

                $response = @ file_get_contents( $version_check_url, null, $context );
            }
            elseif ( function_exists('curl_version') )
            {
                $ch = curl_init();

                curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 4 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 4 );
                curl_setopt( $ch, CURLOPT_URL, $version_check_url );
                curl_setopt( $ch, CURLOPT_POST, true );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $postquery );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

                $response = curl_exec($ch);

                curl_close($ch);
            }

            $this->trellis->cache->add( 'misc', array( 'vcheck_time' => time(), 'vcheck_response' => $response ) );
        }
        else
        {
            $response = $this->trellis->cache->data['misc']['vcheck_response'];
        }

        return $response;
    }

}

?>