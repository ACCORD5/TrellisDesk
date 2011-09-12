<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_cpfields {

    public $error = '';
    public $required_field = '';

    #=======================================
    # @ Get Custom Profile Fields
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'profile_fields',
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
    # @ Get Single Custom Profile Field
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'profile_fields',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Custom Profile Field By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Custom Profile Field
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'name'        => 'string',
                        'type'        => 'string',
                        'extra'        => 'serialize',
                        'required'    => 'int',
                        'ticket'    => 'int',
                        'staff'        => 'int',
                        'perms'        => 'serialize',
                        'position'    => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'profile_fields',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Custom Profile Field
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'name'        => 'string',
                        'type'        => 'string',
                        'extra'        => 'serialize',
                        'required'    => 'int',
                        'ticket'    => 'int',
                        'staff'        => 'int',
                        'perms'        => 'serialize',
                        'position'    => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'profile_fields',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Custom Profile Field
    #=======================================

    public function delete($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'profile_fields_data',
                                                   'where'    => array( 'fid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'profile_fields',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Grab Custom Profile Fields
    #=======================================

    public function grab( $gid=0, $staff=0, $ticket=0 )
    {
        if ( empty( $this->trellis->cache->data['pfields'] ) ) return false;

        $cfields = $this->trellis->cache->data['pfields'];

        foreach( $cfields as $id => $f )
        {
            if ( $gid )
            {
                $perms = unserialize( $f['perms'] );

                if ( ! $perms[ $gid ] ) unset( $cfields[ $id ] );
            }

            if ( $f['staff'] )
            {
                if ( ! $staff ) unset( $cfields[ $id ] );
            }

            if ( $ticket )
            {
                if ( ! $f['ticket'] ) unset( $cfields[ $id ] );
            }
        }

        if ( empty( $cfields ) ) return false;

        return $cfields;
    }

    #=======================================
    # @ Get Data
    #=======================================

    public function get_data( $uid )
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'fid', 'data', 'extra' ),
                                                   'from'    => 'profile_fields_data',
                                                   'where'    => array( 'uid', '=', $uid ),
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

    public function set_data( $new_data, $uid, $new=0 )
    {
        if ( ! $new ) $old_data = $this->get_data( $uid );

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
                                                                       'update'    => 'profile_fields_data',
                                                                       'set'    => array( 'data' => $value ),
                                                                       'where'    => array( array( 'fid', '=', $id ), array( 'uid', '=', $uid, 'and' ), array( 'extra', '=', $key, 'and' ) ),
                                                                       'limit'    => array( 1 ),
                                                                )       );

                            $this->trellis->db->execute();
                        }
                        else
                        {
                            $this->trellis->db->construct( array(
                                                                       'insert'    => 'profile_fields_data',
                                                                       'set'    => array( 'fid' => $id, 'uid' => $uid, 'data' => $value, 'extra' => $key ),
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
                                                                   'update'    => 'profile_fields_data',
                                                                   'set'    => array( 'data' => $data ),
                                                                   'where'    => array( array( 'fid', '=', $id ), array( 'uid', '=', $uid, 'and' ) ),
                                                                   'limit'    => array( 1 ),
                                                            )       );

                        $this->trellis->db->execute();
                    }
                    else
                    {
                        $this->trellis->db->construct( array(
                                                                   'insert'    => 'profile_fields_data',
                                                                   'set'    => array( 'fid' => $id, 'uid' => $uid, 'data' => $data ),
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

    public function process_input( $gid=0 )
    {
        if ( $cfields = $this->grab( 0, 1 ) )
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
                        $checkbox_data[ $key ] = intval( $this->trellis->input[ 'cpf_'. $f['id'] .'_'. $key ] );
                    }

                    $fdata[ $f['id'] ] = $checkbox_data;
                }
                else
                {
                    if ( $f['required'] )
                    {
                        if ( ! $this->trellis->input[ 'cpf_'. $f['id'] ] )
                        {
                            $this->required_field = $f['name'];

                            return false;
                        }
                    }

                    $fdata[ $f['id'] ] = $this->trellis->input[ 'cpf_'. $f['id'] ];
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