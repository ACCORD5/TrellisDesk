<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_skins {

    public $error = '';

    #=======================================
    # @ Get Skins
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'skins',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $s = $this->trellis->db->fetch_row() )
        {
            $return[ $s['id'] ] = $s;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Skin
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'skins',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Skin By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Get Template
    #=======================================

    public function get_template($id, $file)
    {
        $file = TD_SKIN .'s'. $id .'/templates/'. $file;

        if ( ! is_readable( $file ) || ! is_file( $file ) ) return false;

        return file_get_contents( $file );
    }

    #=======================================
    # @ Get CSS
    #=======================================

    public function get_css($id, $file)
    {
        $file = TD_SKIN .'s'. $id .'/css/'. $file;

        if ( ! is_readable( $file ) || ! is_file( $file ) ) return false;

        return file_get_contents( $file );
    }

    #=======================================
    # @ Get Wrapper
    #=======================================

    public function get_wrapper($id)
    {
        $file = TD_SKIN .'s'. $id .'/templates/wrapper.tpl';

        if ( ! is_readable( $file ) || ! is_file( $file ) ) return false;

        return file_get_contents( $file );
    }

    #=======================================
    # @ Add Skin
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'name'    => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'skins',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Skin
    #=======================================

    public function edit($data, $id, $file)
    {
        $path = TD_SKIN .'s'. $id .'/templates/'. $file;

        if ( ! is_file( $path ) ) return false;

        if ( ! $this->writeable( $id, $file ) ) return false;

        return file_put_contents( $path, $data );
    }

    #=======================================
    # @ Edit CSS
    #=======================================

    public function edit_css($data, $id, $file)
    {
        $path = TD_SKIN .'s'. $id .'/css/'. $file;

        if ( ! is_file( $path ) ) return false;

        if ( ! $this->writeable_css( $id, $file ) ) return false;

        return file_put_contents( $path, $data );
    }

    #=======================================
    # @ Edit Wrapper
    #=======================================

    public function edit_wrapper($data, $id)
    {
        $path = TD_SKIN .'s'. $id .'/templates/wrapper.tpl';

        if ( ! is_file( $path ) ) return false;

        if ( ! $this->writeable_wrapper( $id ) ) return false;

        return file_put_contents( $path, $data );
    }

    #=======================================
    # @ Edit Properties
    #=======================================

    public function edit_prop($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'name'    => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'skins',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Skin
    #=======================================

    public function delete($id, $switchto)
    {
        if ( ! $id = intval( $id ) ) return false;
        if ( ! $switchto = intval( $switchto ) ) return false;

        $dir = TD_PATH .'skins/s'. $id;

        if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) return false;

        if ( ! $this->_rrmdir( $dir ) ) return false;

        $this->trellis->db->construct( array(
                                                   'update'    => 'users',
                                                   'set'    => array( 'skin' => $switchto ),
                                                   'where'    => array( 'skin', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'skins',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Recursive rmdir
    #=======================================

    private function _rrmdir($dir)
    {
        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( $file != '.' && $file != '..' )
            {
                if ( is_file( $dir .'/'. $file ) )
                {
                    if ( ! is_writable( $dir .'/'. $file ) ) return false;
                    if ( ! @unlink( $dir .'/'. $file ) ) return false;
                }
                elseif ( is_dir( $dir .'/'. $file ) )
                {
                    if ( ! $this->_rrmdir( $dir .'/'. $file ) ) return false;
                }
            }
        }

        return @rmdir( $dir );
    }

    #=======================================
    # @ Default Skin
    #=======================================

    public function set_default($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'update'    => 'skins',
                                                   'set'    => array( 'default' => 0 ),
                                                   'where'    => array( 'default', '=', 1 ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'update'    => 'skins',
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

    public function import( $file, $options )
    {
        if ( ! $xml = @simplexml_load_file( $file ) ) return false;

        if ( $xml->getName() != 'skin' ) return false;

        if ( ! ( $version = $this->trellis->sanitize_data( base64_decode( $xml['version'] ) ) ) ) return false;
        if ( ! ( $exported = $this->trellis->sanitize_data( base64_decode( $xml['exported'] ) ) ) ) return false;

        #if ( $version != $this->trellis->version_number ) return false; // TODO: check min version requirement
        if ( ! $info = base64_decode( next($xml) ) ) return false;

        if ( ! $infoxml = @simplexml_load_string( $info ) ) return false;

        $info = array();
        foreach ( $infoxml as $ik => $iv ) {
            $info[ (string)$ik ] = (string)( $this->trellis->sanitize_data( $iv[0] ) );
        }

        if ( ! $info['name'] ) return false;

        if ( ! ( $id = $this->add( array( 'name' => $info['name'] ) ) ) ) return false;

        if ( ! @mkdir( ( $dir = TD_SKIN .'s'. $id ), 0755, true ) ) return false;
        if ( ! is_dir( $dir ) || ! is_writable( $dir ) ) return false;

        if ( ! @file_put_contents( $dir .'/info.xml', $infoxml->saveXML() ) ) return false;

        foreach ( $xml as $f )
        {
            $func = null;
            $folder = null;

            if ( $f->getName() == 'templates' )
            {
                $func = '_import_template';
            }
            elseif ( $f->getName() == 'css' && $options['css'] )
            {
                $func = '_import_css';
            }
            elseif ( $f->getName() == 'images' && $options['images'] )
            {
                $func = '_import_image';
            }
            elseif ( $f->getName() == 'scripts' && $options['scripts'] )
            {
                $func = '_import_script';
            }
            elseif ( $f->getName() == 'other' && $options['other'] )
            {
                $func = '_import_other';
            }

            if ( $f['folder'] ) {
                $folder = base64_decode( (string)$f['folder'] );
            }

            if ( ! $func ) continue;

            foreach ( $f as $c ) {
                $this->$func($c, $dir, $folder);
            }
        }

        // TODO: create index.html files?

        return array( 'id' => $id, 'name' => $info['name'] );
    }

    #=======================================
    # @ Import Template
    #=======================================

    private function _import_template(&$c, $dir, $folder)
    {
        $dir .= '/templates';
        if ( $folder ) $dir .= '/'. $folder;

        if ( ! is_dir( $dir ) ) {
            if ( ! @mkdir( $dir, 0755 ) ) return false;
        }
        if ( ! is_writable( $dir ) ) return false;

        if ( ! ( $file = base64_decode( (string)$c['name'] ) ) ) return false; // TODO: security - check filename to make sure it is safe
        if ( ! ( $content = base64_decode( (string)$c->content ) ) ) return false;

        return @file_put_contents( $dir .'/'. $file .'.tpl', $content );
    }

    #=======================================
    # @ Import CSS
    #=======================================

    private function _import_css(&$c, $dir, $folder)
    {
        $dir .= '/css';
        if ( $folder ) $dir .= '/'. $folder;

        if ( ! is_dir( $dir ) ) {
            if ( ! @mkdir( $dir, 0755, true ) ) return false;
        }
        if ( ! is_writable( $dir ) ) return false;

        if ( ! ( $file = base64_decode( (string)$c['name'] ) ) ) return false; // TODO: security - check filename to make sure it is safe
        if ( ! ( $content = base64_decode( (string)$c->content ) ) ) return false;

        return @file_put_contents( $dir .'/'. $file .'.css', $content );
    }

    #=======================================
    # @ Import Image
    #=======================================

    private function _import_image(&$c, $dir, $folder)
    {
        $dir .= '/images';
        if ( $folder ) $dir .= '/'. $folder;

        if ( ! is_dir( $dir ) ) {
            if ( ! @mkdir( $dir, 0755, true ) ) return false;
        }
        if ( ! is_writable( $dir ) ) return false;

        if ( ! ( $file = base64_decode( (string)$c['name'] ) ) ) return false; // TODO: security - check filename to make sure it is safe
        if ( ! ( $content = base64_decode( (string)$c->content ) ) ) return false;

        return @file_put_contents( $dir .'/'. $file, $content );
    }

    #=======================================
    # @ Import Script
    #=======================================

    private function _import_script(&$c, $dir, $folder)
    {
        $dir .= '/scripts';
        if ( $folder ) $dir .= '/'. $folder;

        if ( ! is_dir( $dir ) ) {
            if ( ! @mkdir( $dir, 0755, true ) ) return false;
        }
        if ( ! is_writable( $dir ) ) return false;

        if ( ! ( $file = base64_decode( (string)$c['name'] ) ) ) return false; // TODO: security - check filename to make sure it is safe
        if ( ! ( $content = base64_decode( (string)$c->content ) ) ) return false;

        return @file_put_contents( $dir .'/'. $file .'.js', $content );
    }

    #=======================================
    # @ Import Other
    #=======================================

    private function _import_other(&$c, $dir, $folder)
    {
        $dir .= '/assets';
        if ( $folder ) $dir .= '/'. $folder;

        if ( ! is_dir( $dir ) ) {
            if ( ! @mkdir( $dir, 0755, true ) ) return false;
        }
        if ( ! is_writable( $dir ) ) return false;

        if ( ! ( $file = base64_decode( (string)$c['name'] ) ) ) return false; // TODO: security - check filename to make sure it is safe
        if ( ! ( $content = base64_decode( (string)$c->content ) ) ) return false;

        return @file_put_contents( $dir .'/'. $file, $content );
    }

    #=======================================
    # @ Export
    #=======================================

    public function export( $id, $options )
    {
        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $id ) ) return false;

        if ( ! $options['name'] ) $options['name'] = $s['name'];

        $base_dir = TD_SKIN .'s'. $id .'/';

        $infodoc = new DOMDocument();
        $infodoc->formatOutput = true;

        $skin = $infodoc->createElement( 'skin' );
        $infodoc->appendChild( $skin );

        $name = $infodoc->createElement( 'name' );
        $skin->appendChild( $name );
        $name->appendChild( $infodoc->createTextNode( $options['name'] ) );

        $description = $infodoc->createElement( 'description' );
        $skin->appendChild( $description );
        $description->appendChild( $infodoc->createTextNode( $options['description'] ) );

        $author = $infodoc->createElement( 'author' );
        $skin->appendChild( $author );
        $author->appendChild( $infodoc->createTextNode( $options['author'] ) );

        $copyright = $infodoc->createElement( 'copyright' );
        $skin->appendChild( $copyright );
        $copyright->appendChild( $infodoc->createTextNode( $options['copyright'] ) );

        $version = $infodoc->createElement( 'version' );
        $skin->appendChild( $version );
        $version->appendChild( $infodoc->createTextNode( $options['version'] ) );

        $version_human = $infodoc->createElement( 'version_human' );
        $skin->appendChild( $version_human );
        $version_human->appendChild( $infodoc->createTextNode( $options['version_human'] ) );

        $td_version_min = $infodoc->createElement( 'td_version_min' );
        $skin->appendChild( $td_version_min );
        $td_version_min->appendChild( $infodoc->createTextNode( $options['td_version_min'] ) );

        $td_version_min_human = $infodoc->createElement( 'td_version_min_human' );
        $skin->appendChild( $td_version_min_human );
        $td_version_min_human->appendChild( $infodoc->createTextNode( $options['td_version_min_human'] ) );

        $infoxml = $infodoc->saveXML();

        $doc = new DOMDocument();
        $doc->formatOutput = true;

        $pack = $doc->createElement( 'skin' );
        $doc->appendChild( $pack );

        $version = $doc->createAttribute( 'version' );
        $pack->appendChild( $version );
        $version->appendChild( $doc->createTextNode( base64_encode( $this->trellis->version_number ) ) );

        $exported = $doc->createAttribute( 'exported' );
        $pack->appendChild( $exported );
        $exported->appendChild( $doc->createTextNode( base64_encode( time() ) ) );

        $info = $doc->createElement( 'info' );
        $pack->appendChild( $info );
        $info->appendChild( $doc->createTextNode( base64_encode( $infoxml ) ) );

        $dir = $base_dir .'templates';

        if ( ! $this->_export_templates($doc, $pack, $dir, $dir) ) return false;

        if ( $options['css'] )
        {
            $dir = $base_dir .'css';
            $this->_export_css($doc, $pack, $dir, $dir);
        }

        if ( $options['images'] )
        {
            $dir = $base_dir .'images';
            $this->_export_images($doc, $pack, $dir, $dir);
        }

        if ( $options['scripts'] )
        {
            $dir = $base_dir .'scripts';
            $this->_export_scripts($doc, $pack, $dir, $dir);
        }

        if ( $options['other'] )
        {
            $dir = $base_dir .'assets';
            $this->_export_other($doc, $pack, $dir, $dir);
        }

        header( 'Content-type: text/xml' );

        header( 'Content-Disposition: attachment; filename="td_skin.xml"' );

        print $doc->saveXML();

        $this->trellis->shut_down();

        exit();
    }

    #=======================================
    # @ Export Templates
    #=======================================

    private function _export_templates(&$doc, &$pack, $dir, $base)
    {
        $files = array();

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( $file != '.' && $file != '..' && is_dir( $dir .'/'. $file ) )
            {
                #$this->_export_templates($doc, $pack, $dir .'/'. $file, $base);
            }
            elseif ( is_file( $dir .'/'. $file ) && is_readable( $dir .'/'. $file ) && ( strrchr( $file, "." ) == '.tpl' ) )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        $templates = $doc->createElement( 'templates' );
        $pack->appendChild( $templates );
        $folder = preg_replace( '/^'. str_replace( '/', '\\/', $base ) .'/', '', $dir );

        if ( $folder )
        {
            $path = $doc->createAttribute( 'folder' );
            $templates->appendChild( $path );
            $path->appendChild( $doc->createTextNode( base64_encode( trim( $folder, '/' ) ) ) );
        }

        foreach( $files as &$f )
        {
            $file = $doc->createElement( 'file' );
            $templates->appendChild( $file );

            $name = $doc->createAttribute( 'name' );
            $file->appendChild( $name );
            $name->appendChild( $doc->createTextNode( base64_encode( basename( $f, '.tpl' ) ) ) );

            $content = $doc->createElement( 'content' );
            $file->appendChild( $content );
            $content->appendChild( $doc->createTextNode( base64_encode( @file_get_contents( $dir .'/'. $f ) ) ) );
        }

        return true;
    }

    #=======================================
    # @ Export CSS
    #=======================================

    private function _export_css(&$doc, &$pack, $dir, $base)
    {
        if ( ! is_dir( $dir ) ) return false;

        $files = array();

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( $file != '.' && $file != '..' && is_dir( $dir .'/'. $file ) )
            {
                $this->_export_css($doc, $pack, $dir .'/'. $file, $base);
            }
            elseif ( is_file( $dir .'/'. $file ) && is_readable( $dir .'/'. $file ) && ( strrchr( $file, "." ) == '.css' ) )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        $css = $doc->createElement( 'css' );
        $pack->appendChild( $css );
        $folder = preg_replace( '/^'. str_replace( '/', '\\/', $base ) .'/', '', $dir );

        if ( $folder )
        {
            $path = $doc->createAttribute( 'folder' );
            $css->appendChild( $path );
            $path->appendChild( $doc->createTextNode( base64_encode( trim( $folder, '/' ) ) ) );
        }

        foreach( $files as &$f )
        {
            $file = $doc->createElement( 'file' );
            $css->appendChild( $file );

            $name = $doc->createAttribute( 'name' );
            $file->appendChild( $name );
            $name->appendChild( $doc->createTextNode( base64_encode( basename( $f, '.css' ) ) ) );

            $content = $doc->createElement( 'content' );
            $file->appendChild( $content );
            $content->appendChild( $doc->createTextNode( base64_encode( @file_get_contents( $dir .'/'. $f ) ) ) );
        }
    }

    #=======================================
    # @ Export Images
    #=======================================

    private function _export_images(&$doc, &$pack, $dir, $base)
    {
        if ( ! is_dir( $dir ) ) return false;

        $files = array();
        $img_exts = array( '.bmp', '.gif', '.jpg', '.jpeg', '.png', '.psd', '.tiff' );

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( $file != '.' && $file != '..' && is_dir( $dir .'/'. $file ) )
            {
                $this->_export_images($doc, $pack, $dir .'/'. $file, $base);
            }
            elseif ( is_file( $dir .'/'. $file ) && is_readable( $dir .'/'. $file ) && ( in_array( strrchr( $file, "." ), $img_exts ) ) )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        $images = $doc->createElement( 'images' );
        $pack->appendChild( $images );
        $folder = preg_replace( '/^'. str_replace( '/', '\\/', $base ) .'/', '', $dir );

        if ( $folder )
        {
            $path = $doc->createAttribute( 'folder' );
            $images->appendChild( $path );
            $path->appendChild( $doc->createTextNode( base64_encode( trim( $folder, '/' ) ) ) );
        }

        foreach( $files as &$f )
        {
            $file = $doc->createElement( 'file' );
            $images->appendChild( $file );

            $name = $doc->createAttribute( 'name' );
            $file->appendChild( $name );
            $name->appendChild( $doc->createTextNode( base64_encode( basename( $f ) ) ) );

            $content = $doc->createElement( 'content' );
            $file->appendChild( $content );
            $content->appendChild( $doc->createTextNode( base64_encode( @file_get_contents( $dir .'/'. $f ) ) ) );
        }

        return true;
    }

    #=======================================
    # @ Export Scripts
    #=======================================

    private function _export_scripts(&$doc, &$pack, $dir, $base)
    {
        if ( ! is_dir( $dir ) ) return false;

        $files = array();

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( $file != '.' && $file != '..' && is_dir( $dir .'/'. $file ) )
            {
                $this->_export_scripts($doc, $pack, $dir .'/'. $file, $base);
            }
            elseif ( is_file( $dir .'/'. $file ) && is_readable( $dir .'/'. $file ) && ( strrchr( $file, "." ) == '.js' ) )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        $scripts = $doc->createElement( 'scripts' );
        $pack->appendChild( $scripts );
        $folder = preg_replace( '/^'. str_replace( '/', '\\/', $base ) .'/', '', $dir );

        if ( $folder )
        {
            $path = $doc->createAttribute( 'folder' );
            $scripts->appendChild( $path );
            $path->appendChild( $doc->createTextNode( base64_encode( trim( $folder, '/' ) ) ) );
        }

        foreach( $files as &$f )
        {
            $file = $doc->createElement( 'file' );
            $scripts->appendChild( $file );

            $name = $doc->createAttribute( 'name' );
            $file->appendChild( $name );
            $name->appendChild( $doc->createTextNode( base64_encode( basename( $f, '.js' ) ) ) );

            $content = $doc->createElement( 'content' );
            $file->appendChild( $content );
            $content->appendChild( $doc->createTextNode( base64_encode( @file_get_contents( $dir .'/'. $f ) ) ) );
        }

        return true;
    }

    #=======================================
    # @ Export Other
    #=======================================

    # TODO: repating code... we can make the common code into another function

    private function _export_other(&$doc, &$pack, $dir, $base)
    {
        if ( ! is_dir( $dir ) ) return false;

        $files = array();

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( $file != '.' && $file != '..' && is_dir( $dir .'/'. $file ) )
            {
                if ( strpos( $file, $base))
                $this->_export_other($doc, $pack, $dir .'/'. $file, $base);
            }
            elseif ( is_file( $dir .'/'. $file ) && is_readable( $dir .'/'. $file ) )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        $other = $doc->createElement( 'other' );
        $pack->appendChild( $other );
        $folder = preg_replace( '/^'. str_replace( '/', '\\/', $base ) .'/', '', $dir );

        if ( $folder )
        {
            $path = $doc->createAttribute( 'folder' );
            $other->appendChild( $path );
            $path->appendChild( $doc->createTextNode( base64_encode( trim( $folder, '/' ) ) ) );
        }

        foreach( $files as &$f )
        {
            $file = $doc->createElement( 'file' );
            $other->appendChild( $file );

            $name = $doc->createAttribute( 'name' );
            $file->appendChild( $name );
            $name->appendChild( $doc->createTextNode( base64_encode( basename( $f ) ) ) );

            $content = $doc->createElement( 'content' );
            $file->appendChild( $content );
            $content->appendChild( $doc->createTextNode( base64_encode( @file_get_contents( $dir .'/'. $f ) ) ) );
        }

        return true;
    }

    #=======================================
    # @ Files
    #=======================================

    public function files($id)
    {
        $dir = TD_SKIN .'s'. $id .'/templates/';

        if ( ! is_readable( $dir ) || ! is_dir( $dir ) ) return false;

        $files = array();

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( is_file( $dir .'/'. $file ) && ( strrchr( $file, "." ) == '.tpl' ) && $file != "wrapper.tpl" )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        return $files;
    }

    #=======================================
    # @ Files CSS
    #=======================================

    public function files_css($id)
    {
        $dir = TD_SKIN .'s'. $id .'/css/';

        if ( ! is_readable( $dir ) || ! is_dir( $dir ) ) return false;

        $files = array();

        if ( ! $handle = opendir( $dir ) ) return false;

        while ( false !== ( $file = readdir( $handle ) ) )
        {
            if ( is_file( $dir .'/'. $file ) && ( strrchr( $file, "." ) == '.css' ) )
            {
                $files[] = $file;
            }
        }

        if ( empty( $files ) ) return false;

        return $files;
    }

    #=======================================
    # @ Writeable
    #=======================================

    public function writeable($id, $file)
    {
        $file = TD_SKIN .'s'. $id .'/templates/'. $file;

        return is_writable( $file );
    }

    #=======================================
    # @ Writeable CSS
    #=======================================

    public function writeable_css($id, $file)
    {
        $file = TD_SKIN .'s'. $id .'/css/'. $file;

        return is_writable( $file );
    }

    #=======================================
    # @ Writeable Wrapper
    #=======================================

    public function writeable_wrapper($id)
    {
        $file = TD_SKIN .'s'. $id .'/templates/wrapper.tpl';

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