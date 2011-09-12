<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_cdfields {

    public $error = '';
    public $required_field = '';

    #=======================================
    # @ Get Custom Department Fields
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'depart_fields',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $n = $this->trellis->db->fetch_row() )
        {
            $return[ $n['id'] ] = $n;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Custom Department Field
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'depart_fields',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Custom Department Field By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Custom Department Field
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'name'        => 'string',
                        'type'        => 'string',
                        'extra'        => 'serialize',
                        'required'    => 'int',
                        'departs'    => 'serialize',
                        'position'    => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'depart_fields',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Custom Department Field
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'name'        => 'string',
                        'type'        => 'string',
                        'extra'        => 'serialize',
                        'required'    => 'int',
                        'departs'    => 'serialize',
                        'position'    => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'depart_fields',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Custom Department Field
    #=======================================

    public function delete($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'depart_fields_data',
                                                   'where'    => array( 'fid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'depart_fields',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Grab Custom Department Fields
    #=======================================

    public function grab( $did=0 )
    {
        if ( empty( $this->trellis->cache->data['dfields'] ) ) return false;

        $cfields = $this->trellis->cache->data['dfields'];

        foreach( $cfields as $id => $f )
        {
            if ( $did )
            {
                $perms = unserialize( $f['departs'] );

                if ( ! $perms[ $did ] ) unset( $cfields[ $id ] );
            }
        }

        if ( empty( $cfields ) ) return false;

        return $cfields;
    }

    #=======================================
    # @ Get Data
    #=======================================

    public function get_data( $tid )
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'fid', 'data', 'extra' ),
                                                   'from'    => 'depart_fields_data',
                                                   'where'    => array( 'tid', '=', $tid ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $d = $this->trellis->db->fetch_row() )
        {
            if ( $d['extra'] )
            {
                $return[ $d['fid'] ][ $d['extra'] ] = $d['data'];
            }
            else
            {
                $return[ $d['fid'] ] = $d['data'];
            }
        }

        return $return;
    }

    #=======================================
    # @ Set Data
    #=======================================

    public function set_data( $new_data, $tid, $new=0, $uid=0 )
    {
        if ( ! $new ) $old_data = $this->get_data( $tid );

        if ( ! $uid ) $uid = $this->trellis->user['id'];

        foreach( $new_data as $id => $data )
        {
            if ( is_array( $data ) )
            {
                foreach( $data as $key => $value )
                {
                    if ( $old_data[ $id ][ $key ] != $value )
                    {
                        if ( isset( $old_data[ $id ][ $key ] ) )
                        {
                            $this->trellis->db->construct( array(
                                                                       'update'    => 'depart_fields_data',
                                                                       'set'    => array( 'data' => $value ),
                                                                       'where'    => array( array( 'fid', '=', $id ), array( 'tid', '=', $tid, 'and' ), array( 'extra', '=', $key, 'and' ) ),
                                                                       'limit'    => array( 1 ),
                                                                )       );

                            $this->trellis->db->execute();
                        }
                        else
                        {
                            $this->trellis->db->construct( array(
                                                                       'insert'    => 'depart_fields_data',
                                                                       'set'    => array( 'fid' => $id, 'tid' => $tid, 'uid' => $uid, 'data' => $value, 'extra' => $key ),
                                                                )       );

                            $this->trellis->db->execute();
                        }
                    }
                }
            }
            else
            {
                if ( $old_data[ $id ] != $data )
                {
                    if ( isset( $old_data[ $id ] ) )
                    {
                        $this->trellis->db->construct( array(
                                                                   'update'    => 'depart_fields_data',
                                                                   'set'    => array( 'data' => $data ),
                                                                   'where'    => array( array( 'fid', '=', $id ), array( 'tid', '=', $tid, 'and' ) ),
                                                                   'limit'    => array( 1 ),
                                                            )       );

                        $this->trellis->db->execute();
                    }
                    else
                    {
                        $this->trellis->db->construct( array(
                                                                   'insert'    => 'depart_fields_data',
                                                                   'set'    => array( 'fid' => $id, 'tid' => $tid, 'uid' => $uid, 'data' => $data ),
                                                            )       );

                        $this->trellis->db->execute();
                    }
                }
            }
        }
    }

    #=======================================
    # @ Process Input
    #=======================================

    public function process_input( $did=0 )
    {
        if ( $cfields = $this->grab( $did ) )
        {
            $fdata = array();

            foreach( $cfields as $fid => $f )
            {
                if ( $f['type'] == 'checkbox' )
                {
                    $f['extra'] = unserialize( $f['extra'] );

                    $checkbox_data = array();

                    foreach( $f['extra'] as $key => $name )
                    {
                        $checkbox_data[ $key ] = intval( $this->trellis->input[ 'cdf_'. $f['id'] .'_'. $key ] );
                    }

                    $fdata[ $f['id'] ] = $checkbox_data;
                }
                else
                {
                    if ( $f['required'] )
                    {
                        if ( ! $this->trellis->input[ 'cdf_'. $f['id'] ] )
                        {
                            $this->required_field = $f['name'];

                            return false;
                        }
                    }

                    $fdata[ $f['id'] ] = $this->trellis->input[ 'cdf_'. $f['id'] ];
                }
            }

            return $fdata;
        }
        else
        {
            return false;
        }
    }

}

?>