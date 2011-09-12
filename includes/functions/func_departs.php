<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_departs {

    public $error = '';

    #=======================================
    # @ Get Departments
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'departments',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $d = $this->trellis->db->fetch_row() )
        {
            $return[ $d['id'] ] = $d;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Department
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'departments',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Department By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Department
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'name'                => 'string',
                        'description'        => 'string',
                        'assign_auto'        => 'string',
                        'assign_move'        => 'int',
                        'escalate_enable'    => 'int',
                        'escalate_user'        => 'int',
                        'escalate_wait'        => 'int',
                        'escalate_depart'    => 'int',
                        'escalate_assign'    => 'string',
                        'close_auto'        => 'int',
                        'close_own'            => 'int',
                        'reopen_own'        => 'int',
                        'allow_attach'        => 'int',
                        'allow_rating'        => 'int',
                        'position'            => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'departments',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Department
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'name'                => 'string',
                        'description'        => 'string',
                        'assign_auto'        => 'string',
                        'assign_move'        => 'int',
                        'escalate_enable'    => 'int',
                        'escalate_user'        => 'int',
                        'escalate_wait'        => 'int',
                        'escalate_depart'    => 'int',
                        'escalate_assign'    => 'string',
                        'close_auto'        => 'int',
                        'close_own'            => 'int',
                        'reopen_own'        => 'int',
                        'allow_attach'        => 'int',
                        'allow_rating'        => 'int',
                        'position'            => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'departments',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Department
    #=======================================

    public function delete($id, $action=0, $mvid=0)
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( $action == 1 )
        {
            if ( ! $mvid = intval( $mvid ) ) return false;

            $this->trellis->db->construct( array(
                                                       'update'    => 'tickets',
                                                       'set'    => array( 'did' => $mvid ),
                                                       'where'    => array( 'did', '=', $id ),
                                                )       );

            $this->trellis->db->execute();

            $num_tickets = $this->trellis->db->get_affected_rows();

            if ( $num_tickets )
            {
                $this->trellis->db->construct( array(
                                                           'update'    => 'departments',
                                                           'set'    => array( 'tickets_total' => array( '+', $num_tickets ) ),
                                                           'where'    => array( 'id', '=', $mvid ),
                                                    )       );

                $this->trellis->db->execute();
            }
        }
        elseif ( $action == 2 )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'id' ),
                                                       'from'    => 'tickets',
                                                       'where'    => array( array( 'did', '=', $id ), array( 'replies', '>', 0, 'and' ) ),
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
                                                       'where'    => array( 'did', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'departments',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>