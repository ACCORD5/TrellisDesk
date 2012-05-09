<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_priorities {

    public $error = '';

    #=======================================
    # @ Get Priorities
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'priorities',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $p = $this->trellis->db->fetch_row() )
        {
            $return[ $p['id'] ] = $p;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Priority
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'priorities',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Priority By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Priority
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'name'            => 'string',
                        'icon_regular'    => 'string',
                        'icon_assigned'    => 'string',
                        'default'        => 'int',
                        'position'        => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'priorities',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Priority
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;
        
        $fields = array(
                        'name'            => 'string',
                        'icon_regular'    => 'string',
                        'icon_assigned'    => 'string',
                        'default'        => 'int',
                        'position'        => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'priorities',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Priority
    #=======================================

    public function delete($id, $action=0, $npid=0)
    {
        if ( ! $id = intval( $id ) ) return false;
        
        if ( $action == 1 )
        {
            if ( ! $npid = intval( $npid ) ) return false;
            
            $this->trellis->db->construct( array(
                                                       'update'    => 'tickets',
                                                       'set'    => array( 'priority' => $npid ),
                                                       'where'    => array( 'priority', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }
        elseif ( $action == 2 )
        {    
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'id' ),
                                                       'from'    => 'tickets',
                                                       'where'    => array( array( 'priority', '=', $id ), array( 'replies', '>', 0, 'and' ) ),
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
                                                       'where'    => array( 'priority', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'priorities',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Default Priority
    #=======================================

    public function set_default($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'update'    => 'priorities',
                                                   'set'    => array( 'default' => 0 ),
                                                   'where'    => array( 'default', '=', 1 ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'update'    => 'priorities',
                                                   'set'    => array( 'default' => 1 ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>