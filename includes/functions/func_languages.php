<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_languages {

    public $error = '';

    #=======================================
    # @ Get Languages
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'languages',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $l = $this->trellis->db->fetch_row() )
        {
            $return[ $l['id'] ] = $l;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Language
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'languages',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Language By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Language
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'key'    => 'string',
                        'name'    => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'languages',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Language
    #=======================================

    public function edit($data, $folder, $file)
    {
        $path = TD_PATH .'languages/'. $folder .'/'. $file;

        if ( ! is_file( $path ) ) return false;

        if ( ! $this->writeable( $folder, $file ) ) return false;

        if ( ! $handle = fopen( $path, 'wb' ) ) return false;

        $file_start = "<?php\n\n/*\n#======================================================\n";
        $file_start .= "|    | Trellis Desk Language File\n";
        $file_start .= "|    | ". $file ."\n";
        $file_start .= "#======================================================\n*/\n\n";
        $file_start .= "\$lang = array(\n\n";

        fwrite( $handle, $file_start );

        ksort( $data );

        foreach( $data as $k => $l )
        {
            fwrite( $handle, "'". $k ."' => '". addslashes( $l ) ."',\n" );
        }

        fwrite( $handle, "\n);\n\n?>" );

        @fclose( $handle );

        return true;
    }

    #=======================================
    # @ Edit Properties
    #=======================================

    public function edit_prop($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'key'    => 'string',
                        'name'    => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'languages',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Language
    #=======================================

    public function delete($id, $switchto)
    {
        if ( ! $id = intval( $id ) ) return false;
        if ( ! $switchto = intval( $switchto ) ) return false;

        if ( ! $l = $this->trellis->func->languages->get_single_by_id( array( 'id', 'key' ), $id ) ) return false;

        $dir = TD_PATH .'languages/'. $l['key'];

        if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) return false;

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( is_file( $dir .'/'. $file ) && $file != '.' && $file != '..' )
            {
                if ( ! is_writable( $dir .'/'. $file ) ) return false;
                if ( ! @unlink( $dir .'/'. $file ) ) return false;
            }
        }

        if ( ! @rmdir( $dir ) ) return false;

        $this->trellis->db->construct( array(
                                                   'update'    => 'users',
                                                   'set'    => array( 'lang' => $switchto ),
                                                   'where'    => array( 'lang', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'languages',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }


    #=======================================
    # @ Default Language
    #=======================================

    public function set_default($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'update'    => 'languages',
                                                   'set'    => array( 'default' => 0 ),
                                                   'where'    => array( 'default', '=', 1 ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'update'    => 'languages',
                                                   'set'    => array( 'default' => 1 ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Import
    #=======================================

    public function import( $file )
    {
        if ( ! $xml = @simplexml_load_file( $file ) ) return false;

        if ( $xml->getName() != 'language' ) return false;
        if ( ! ( $key = $this->trellis->sanitize_data(  $xml['key'] ) ) )  return false;
        if ( ! ( $name = $this->trellis->sanitize_data(  $xml['name'] ) ) )  return false;
        if ( ! ( $version = $this->trellis->sanitize_data(  $xml['version'] ) ) )  return false;
        if ( ! ( $exported = $this->trellis->sanitize_data(  $xml['exported'] ) ) )  return false;

		//if ( ! ( $key = $this->trellis->sanitize_data( base64_decode( $xml['key'] ) ) ) ) return false;
        //if ( ! ( $name = $this->trellis->sanitize_data( base64_decode( $xml['name'] ) ) ) ) return false;
        //if ( ! ( $version = $this->trellis->sanitize_data( base64_decode( $xml['version'] ) ) ) ) return false;
        //if ( ! ( $exported = $this->trellis->sanitize_data( base64_decode( $xml['exported'] ) ) ) ) return false;

		
        if ( ! $this->check_key( $key ) ) return false;
        if ( $version != $this->trellis->version_number ) return false;

        $i = '';
        while ( is_dir( ( $dir = TD_PATH .'languages/'. $key . $i ) ) )
        {
            $i++;
        }
        $key = $key.$i;

        if ( ! @mkdir( $dir, 0755 ) ) return false;
        if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) return false;

        foreach ( $xml as $f )
        {
            if ( $f->getName() != 'file' ) return false;
            if ( ! ( $this->trellis->sanitize_data( $file =  $f['name'] ) ) )  return false;
			//if ( ! ( $this->trellis->sanitize_data( $file = base64_decode( $f['name'] ) ) ) ) return false;

            if ( ! $handle = fopen( $dir .'/'. $file, 'wb' ) ) return false;

            $file_start = "<?php\n\n/*\n#======================================================\n";
            $file_start .= "|    | Trellis Desk Language File\n";
            $file_start .= "|    | ". $file ."\n";
            $file_start .= "#======================================================\n*/\n\n";

            if ( $file != 'lang_email_content.php' )
            {
                $file_start .= "\$lang = array(\n\n";
            }

            fwrite( $handle, $file_start );

            foreach ( $f as $b )
            {
                if ( $b->getName() != 'bit' ) return false;

                if ( ! ( $this->trellis->sanitize_data( $bk =  $b->key ) ) )  return false;
                //if ( ! ( $this->trellis->sanitize_data( $bk = base64_decode( $b->key ) ) ) ) return false;
                
				if ( ! ( $this->trellis->sanitize_data( $bv =  $b->value ) ) )  return false;
				//if ( ! ( $this->trellis->sanitize_data( $bv = base64_decode( $b->value ) ) ) ) return false;

                if ( $file == 'lang_email_content.php' )
                {
                    if ( substr( $bk, -4, 4 ) == '_sub' )
                    {
                        fwrite( $handle, "\$lang['". $bk ."'] = \"". $bv ."\";\n\n" );
                    }
                    else
                    {
                        fwrite( $handle, "\$lang['". $bk ."'] = <<<EOF\n". $bv ."\nEOF;\n\n" );
                    }
                }
                else
                {
                    fwrite( $handle, "'". $bk ."' => '". addslashes( $this->convert_html( $bv ) ) ."',\n" );
                }
            }

            if ( $file == 'lang_email_content.php' )
            {
                fwrite( $handle, "?>" );
            }
            else
            {
                fwrite( $handle, "\n);\n\n?>" );
            }

            @fclose( $handle );
        }

        if ( ! $id = $this->add( array( 'key' => $key, 'name' => $name ) ) ) return false;

        return array( 'id' => $id, 'name' => $name );
    }

    #=======================================
    # @ Export
    #=======================================

    public function export( $id )
    {
        if ( ! $l = $this->trellis->func->languages->get_single_by_id( array( 'id', 'key', 'name' ), $id ) ) return false;

        $dir = TD_PATH .'languages/'. $l['key'];

        if ( ! is_readable( $dir ) || ! is_dir( $dir ) ) return false;

        $files = array();

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( is_file( $dir .'/'. $file ) && is_readable( $dir .'/'. $file ) && ( strrchr( $file, "." ) == '.php' ) )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        $doc = new DOMDocument();
        $doc->formatOutput = true;

        $pack = $doc->createElement( 'language' );
        $doc->appendChild( $pack );

        $key = $doc->createAttribute( 'key' );
        $pack->appendChild( $key );
        //$key->appendChild( $doc->createTextNode( base64_encode( $l['key'] ) ) );
		$key->appendChild( $doc->createTextNode(  $l['key'] ) ) ;

        $name = $doc->createAttribute( 'name' );
        $pack->appendChild( $name );
        $name->appendChild( $doc->createTextNode(  $l['name'] ) ) ;
		//$name->appendChild( $doc->createTextNode( base64_encode( $l['name'] ) ) );

        $version = $doc->createAttribute( 'version' );
        $pack->appendChild( $version );
        $version->appendChild( $doc->createTextNode(  $this->trellis->version_number ) ) ;
		//$version->appendChild( $doc->createTextNode( base64_encode( $this->trellis->version_number ) ) );

        $exported = $doc->createAttribute( 'exported' );
        $pack->appendChild( $exported );
        $exported->appendChild( $doc->createTextNode(  time() ) ) ;
		//$exported->appendChild( $doc->createTextNode( base64_encode( time() ) ) );

        foreach( $files as &$f )
        {
            $file = $doc->createElement( 'file' );
            $pack->appendChild( $file );

            $name = $doc->createAttribute( 'name' );
            $file->appendChild( $name );
            $name->appendChild( $doc->createTextNode(  $f ) );
			//$name->appendChild( $doc->createTextNode( base64_encode( $f ) ) );

			
            require $dir .'/'. $f;

            foreach ( $lang as $k => &$v )
            {
                $bit = $doc->createElement( 'bit' );
                $file->appendChild( $bit );

                $key = $doc->createElement( 'key' );
                $bit->appendChild( $key );
                $key->appendChild( $doc->createTextNode(  $k ) );
				//$key->appendChild( $doc->createTextNode( base64_encode( $k ) ) );

                $value = $doc->createElement( 'value' );
                $bit->appendChild( $value );
                $value->appendChild( $doc->createTextNode(  $v ) );
				//$value->appendChild( $doc->createTextNode( base64_encode( $v ) ) );
            }

            unset( $lang );
        }

        header( 'Content-type: text/xml' );

        header( 'Content-Disposition: attachment; filename="td_lang_'. $l['key'] .'.xml"' );

        print $doc->saveXML();

        $this->trellis->shut_down();

        exit();
    }

    #=======================================
    # @ Files
    #=======================================

    public function files($folder)
    {
        $dir = TD_PATH .'languages/'. $folder;

        if ( ! is_readable( $dir ) || ! is_dir( $dir ) ) return false;

        $files = array();

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( is_file( $dir .'/'. $file ) && ( strrchr( $file, "." ) == '.php' ) && $file != "lang_email_content.php" )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        return $files;
    }

    #=======================================
    # @ Bits
    #=======================================

    public function bits($folder, $file)
    {
        $file = TD_PATH .'languages/'. $folder .'/'. $file;

        if ( ! is_readable( $file ) || ! is_file( $file ) ) return false;

        require $file;

        return $lang;
    }

    #=======================================
    # @ Files
    #=======================================

    public function writeable($folder, $file)
    {
        $file = TD_PATH .'languages/'. $folder .'/'. $file;

        return is_writable( $file );
    }

    #=======================================
    # @ Prepare Html
    #=======================================

    public function prepare_html( $data )
    {
        $data = str_replace( '&', '&amp;', $data );
        $data = str_replace( '\'', '&#039;', $data );
        $data = str_replace( '\'', '&#39;', $data );
        $data = str_replace( '"', '&quot;', $data );
        $data = str_replace( '<', '&lt;', $data );
        $data = str_replace( '>', '&gt;', $data );
        $data = str_replace( '(', '&#40;', $data );
        $data = str_replace( ')', '&#41;', $data );

        return $data;
    }

    #=======================================
    # @ Convert Html
    #=======================================

    public function convert_html( $data )
    {
        $data = str_replace( '&amp;', '&', $data );
        $data = str_replace( '&#039;', '\'', $data );
        $data = str_replace( '&#39;', '\'', $data );
        $data = str_replace( '&quot;', '"', $data );
        $data = str_replace( '&lt;', '<', $data );
        $data = str_replace( '&gt;', '>', $data );
        $data = str_replace( '&#40;', '(', $data );
        $data = str_replace( '&#41;', ')', $data );

        return $data;
    }

    #=======================================
    # @ Check Key
    #=======================================

    public function check_key( $key )
    {
        return preg_match( '/^[a-z0-9]*$/', $key ) ;
    }

}

?>