<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_attachments {

    #=======================================
    # @ Get Attachments
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'attachments',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $a = $this->trellis->db->fetch_row() )
        {
            if ( $a['id'] )
            {
                $return[ $a['id'] ] = $a;
            }
            else
            {
                $return[] = $a;
            }
        }

        return $return;
    }

    #=======================================
    # @ Get Single Attachment
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'attachments',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Attachment By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Upload Attachment
    #=======================================

    public function upload(&$file, $data=array(), $response='')
    {
        if ( ! $file )
        {
            if ( $response == 'ajax' ) { $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => 'no data received' ) ) ); } else { return false; };
        }

        if ( $this->trellis->user['g_upload_max_size'] && ( $file['size'] > $this->trellis->user['g_upload_max_size'] ) )
        {
            if ( $response == 'ajax' ) { $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_size'] ) ) ); } else { return false; };
        }

        $allowed_exts = array_map( create_function( '$a', 'return \'.\'. trim( $a );' ), explode( ',', $this->trellis->user['g_upload_exts'] ) );

        $file_ext = strrchr( $file['name'], "." );

        if ( ! in_array( $file_ext, $allowed_exts ) )
        {
            if ( $response == 'ajax' ) { $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_filetype'] ) ) ); } else { return false; };
        }

        $file_name = md5( $file['name'] . microtime() );
        $upload_location = $this->trellis->cache->data['settings']['general']['upload_dir'] . $file_name;

        if ( ! is_writeable( $this->trellis->cache->data['settings']['general']['upload_dir'] ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => 'directory' ) ) );

        if ( ! @move_uploaded_file( $file['tmp_name'], $upload_location ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_move'] ) ) );

        # TODO: only run chmod if web user is 'nobody' (just have a setting)
        //@chmod( $upload_location, 0666 );

        $data['uid'] = $this->trellis->user['id'];
        $data['real_name'] = $file_name;
        $data['original_name'] = $this->trellis->sanitize_data( $file['name'] );
        $data['extension'] = $this->trellis->sanitize_data( $file_ext );

        if ( function_exists( 'finfo_file' ) )
        {
            $finfo = finfo_open( FILEINFO_MIME_TYPE );

            $data['mime'] = finfo_file( $finfo, $upload_location );

            finfo_close( $finfo );
        }

        $data['size'] = $file['size'];
        $data['date'] = time();
        $data['ipadd'] = $this->trellis->input['ip_address'];

        $fields = array(
                        'content_type'    => 'string',
                        'content_id'    => 'int',
                        'uid'            => 'int',
                        'real_name'        => 'string',
                        'original_name'    => 'string',
                        'extension'        => 'string',
                        'mime'            => 'string',
                        'size'            => 'int',
                        'date'            => 'int',
                        'ipadd'            => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'attachments',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        if ( $response == 'ajax' ) return array( 'id' => $this->trellis->db->get_insert_id(), 'name' => $data['original_name'] );

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Assign Attachments
    #=======================================

    public function assign($ids, $cid)
    {
        if ( ! $cid = intval( $cid ) ) return false;

        if ( ! is_array( $ids ) && intval( $ids ) )
        {
            $ids = array( $ids );
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'attachments',
                                                   'set'    => array( 'content_id' => $cid ),
                                                   'where'    => array( 'id', 'in', $ids ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Attachments
    #=======================================

    public function delete($ids)
    {
        if ( is_array( $ids ) )
        {
            if ( ! $files = $this->get( array( 'select' => array( 'real_name' ), 'where' => array( 'id', 'in', $ids ) ) ) ) return false;

            foreach ( $files as &$f )
            {
                @unlink( $this->trellis->cache->data['settings']['general']['upload_dir'] . $f['real_name'] );
            }

            $this->trellis->db->construct( array(
                                                       'delete'    => 'attachments',
                                                       'where'    => array( 'id', 'in', $ids ),
                                                )       );

            $this->trellis->db->execute();
        }
        else
        {
            if ( ! $ids = intval( $ids ) ) return false;

            if ( ! $f = $this->get_single_by_id( array( 'real_name' ), $ids ) ) return false;

            if ( ! @unlink( $this->trellis->cache->data['settings']['general']['upload_dir'] . $f['real_name'] ) ) return false;

            $this->trellis->db->construct( array(
                                                       'delete'    => 'attachments',
                                                       'where'    => array( 'id', '=', $ids ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->execute();
        }

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Download Attachment
    #=======================================

    public function download($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( ! $f = $this->get_single_by_id( array( 'real_name', 'original_name', 'mime', 'size' ), $id ) ) return false;

        if ( ! is_readable( ( $file = $this->trellis->cache->data['settings']['general']['upload_dir'] . $f['real_name'] ) ) ) return false;

        if ( $f['mime'] )
        {
            header( "Content-type: ". $f['mime'] );
        }
        else
        {
            #header( "Content-type: application/force-download" );
        }

        $show_types = array( 'image/gif', 'image/jpeg', 'image/jpg', 'image/png', 'image/tiff' );

        if ( ! in_array( $f['mime'], $show_types ) )
        {
            header( "Content-Disposition: attachment; filename=". $f['original_name'] );
            header( "Content-length: ". filesize( $file_path ) );
        }

        header( "Expires: ". gmdate( 'D, d M Y H:i:s', time() ) ." GMT" );

        readfile( $file );

        $this->trellis->shut_down();

        exit();
    }

}

?>