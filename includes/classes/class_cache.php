<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_cache {

    public $data        = array();
    private $file_key    = '';
    private $new_cache    = array();

    #=======================================
    # @ Constructor
    #=======================================

    function __construct($cache_path, $file_key='')
    {
        #=============================
        # Set Paths
        #=============================

        if ( ! is_dir( $cache_path ) ) trigger_error( "Cache - Cache directory not found: ". $cache_path, E_USER_ERROR );

        define( 'CACHE_PATH', $cache_path );

        #=============================
        # Set File Key
        #=============================

        if ( $file_key ) $this->set_file_key( $file_key );

        #=============================
        # Load Cache Data
        #=============================

        $this->load();
    }

    #=======================================
    # @ Load Data
    #=======================================

    private function load()
    {
        if ( ! $handle = opendir( CACHE_PATH ) ) trigger_error( "Cache - Could not open the cache directory for reading: ". CACHE_PATH, E_USER_ERROR );

        while ( $cache = readdir( $handle ) )
        {
            if ( preg_match( "#^(.+?)\.A5$#", $cache, $name ) )
            {
                if ( ! is_writable( CACHE_PATH . $cache ) ) trigger_error( "Cache - Cache file not writable: ". $cache, E_USER_ERROR );

                $raw_data = file_get_contents( CACHE_PATH . $cache );

                $data = unserialize( base64_decode( $raw_data ) );

                $decoded_name = base64_decode( $name[1] );

                if ( $this->file_key ) $decoded_name = substr( $decoded_name, 0, strrpos( $decoded_name, $this->file_key ) );

                $this->data[ $decoded_name ] = $data;
            }
        }
    }

    #=======================================
    # @ Get Size
    #=======================================

    public function get_size()
    {
        if ( ! $handle = opendir( CACHE_PATH ) ) trigger_error( "Cache - Could not open the cache directory for reading: ". CACHE_PATH, E_USER_ERROR );

        $return = array();

        while ( $cache = readdir( $handle ) )
        {
            if ( preg_match( "#^(.+?)\.A5$#", $cache, $name ) )
            {
                if ( ! is_readable( CACHE_PATH . $cache ) ) trigger_error( "Cache - Cache file not readable: ". $cache, E_USER_ERROR );

                $decoded_name = base64_decode( $name[1] );

                if ( $this->file_key ) $decoded_name = substr( $decoded_name, 0, strrpos( $decoded_name, $this->file_key ) );

                $return[ $decoded_name ] = filesize( CACHE_PATH . $cache );
            }
        }

        return $return;
    }

    #=======================================
    # @ CHMOD Files
    #=======================================

    private function chmod()
    {
        if ( ! $handle = opendir( CACHE_PATH ) ) tigger_error( "Cache - Could not open the cache directory for reading: ". CACHE_PATH, E_USER_ERROR );

        while ( $cache = readdir( $handle ) )
        {
            if ( preg_match( "#^(.+?)\.A5$#", $cache, $name ) )
            {
                @chmod( CACHE_PATH . $cache, 0777 );
            }
        }
    }

    #=======================================
    # @ Add Data
    #=======================================

    public function add($name, $data, $delete_first=0)
    {
        #=============================
        # Are We An Array?
        #=============================

        if ( is_array( $data ) )
        {
            #=============================
            # Reserved Names
            #=============================

            if ( $name == 'cdate' ) trigger_error( "Cache - 'cdate' cannot be used as a cache container as it is reserved.", E_USER_ERROR );

            #=============================
            # Do We Exist Already?
            #=============================

            if ( isset( $this->data[ $name ] ) )
            {
                if ( $delete_first )
                {
                    $this->new_cache[ $name ] = $data;
                }
                else
                {
                    $this->new_cache[ $name ] = array_merge( $this->data[ $name ], $data );
                }
            }
            else
            {
                $this->new_cache[ $name ] = $data;
            }

            #=============================
            # Cache Date
            #=============================

            $this->new_cache['cdate'][ $name ] = time();

            #=============================
            # Update Cache
            #=============================

            $this->data[ $name ] = $this->new_cache[ $name ];
        }
        elseif ( $delete_first )
        {
            $this->new_cache[ $name ] = array();
            $this->new_cache['cdate'][ $name ] = time();
        }
    }

    #=======================================
    # @ Write Data
    #=======================================

    private function write()
    {
        #=============================
        # Write Each Cache Array
        #=============================

        if ( isset( $this->new_cache['cdate'] ) && is_array( $this->data['cdate'] ) )
        {
            $this->new_cache['cdate'] = array_merge( $this->data['cdate'], $this->new_cache['cdate'] );
        }

        if ( $this->new_cache )
        {
            foreach ( $this->new_cache as $name => &$raw_data )
            {
                #=============================
                # Prepare
                #=============================

                $cache_file = CACHE_PATH . base64_encode( $name . $this->file_key ) .".A5";

                $data = base64_encode( serialize( $raw_data ) );

                #=============================
                # Write :D *mmwhahaha*
                #=============================

                if( ! $handle = @fopen( $cache_file, "w" ) ) trigger_error( "Cache - Could not write to the cache file: ". $cache_file, E_USER_ERROR );

                fwrite( $handle, $data );
                fclose( $handle );

                @chmod( $cache_file, 0777 );
            }
        }
    }

    #=======================================
    # @ Clear Data
    #=======================================

    public function clear($name)
    {
        $cache_file = CACHE_PATH . base64_encode( $name . $this->file_key ) .".A5";

        if ( file_exists( $cache_file ) )
        {
            @unlink( $cache_file );

            $this->new_cache['cdate'][ $name ] = 0;
        }
    }

    #=======================================
    # @ Clear All Data
    #=======================================

    public function clear_all()
    {
        foreach ( $this->data as $name => $data )
        {
            $cache_file = CACHE_PATH . base64_encode( $name . $this->file_key ) .".A5";

            if ( file_exists( $cache_file ) )
            {
                @unlink( $cache_file );
            }
        }
    }

    #=======================================
    # @ Shut Down
    #=======================================

    public function shut_down()
    {
        #=============================
        # Write Cache Files
        #=============================

        $this->write();
    }

    #=======================================
    # @ Set File Key
    #=======================================

    public function set_file_key($key)
    {
        $this->file_key = '.'. $key;
    }

}

?>