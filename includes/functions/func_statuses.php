<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_statuses {

    public $error = '';

    #=======================================
    # @ Get Statuses
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'statuses',
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
    # @ Get Single Status
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'statuses',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Status By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Status
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'name_staff'    => 'string',
                        'name_user'        => 'string',
                        'abbr_staff'    => 'string',
                        'abbr_user'        => 'string',
                        'type'            => 'int',
                        'default'        => 'int',
                        'position'        => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'statuses',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Status
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;
        
        $fields = array(
                        'name_staff'    => 'string',
                        'name_user'        => 'string',
                        'abbr_staff'    => 'string',
                        'abbr_user'        => 'string',
                        'type'            => 'int',
                        'default'        => 'int',
                        'position'        => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'statuses',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Status
    #=======================================

    public function delete($id, $action=0, $nsid=0)
    {
        if ( ! $id = intval( $id ) ) return false;
        
        if ( $action == 1 )
        {
            if ( ! $npid = intval( $nsid ) ) return false;
            
            $this->trellis->db->construct( array(
                                                       'update'    => 'tickets',
                                                       'set'    => array( 'status' => $nsid ),
                                                       'where'    => array( 'status', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }
        elseif ( $action == 2 )
        {    
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'id' ),
                                                       'from'    => 'tickets',
                                                       'where'    => array( array( 'status', '=', $id ), array( 'replies', '>', 0, 'and' ) ),
                                                )       );

            $this->trellis->db->execute();
            
            if ( $this->trellis->db->get_num_rows() )
            {
                $tickets = array();
            
                while( $t = $this->trellis->db->fetch_row() )
                {
                    $tickets[] = $t['id'];
                }
            
                $this->trellis->db->construct( array(
                                                           'delete'    => 'replies',
                                                           'where'    => array( 'tid', 'in', $tickets ),
                                                    )       );

                $this->trellis->db->execute();
            }
            
            $this->trellis->db->construct( array(
                                                       'delete'    => 'tickets',
                                                       'where'    => array( 'status', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'statuses',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Default Status
    #=======================================

    public function set_default($id, $type)
    {
        if ( ! $id = intval( $id ) ) return false;
        if ( ! $type = intval( $type ) ) return false;

        $this->trellis->db->construct( array(
                                                   'update'    => 'statuses',
                                                   'set'    => array( 'default' => 0 ),
                                                   'where'    => array( array( 'default', '=', 1 ), array( 'type', '=', $type, 'and' ) ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'update'    => 'statuses',
                                                   'set'    => array( 'default' => 1 ),
                                                   'where'    => array( array( 'id', '=', $id ), array( 'type', '=', $type, 'and' ) ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>