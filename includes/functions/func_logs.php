<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_logs {

    public $error = '';

    #=======================================
    # @ Get Logs
    #=======================================

    public function get($input)
    {
        $return = array();

        if ( in_array( 'uid', $input['select'] ) )
        {
            if ( isset( $input['order'] ) )
            {
                if ( $input['order']['uname'] )
                {
                    $input['order'] = array( 'name' => array( 'u' => current( $input['order'] ) ), 'id' => array( 'l' => 'desc' ) );
                }
                else
                {
                    $input['order'] = array( key( $input['order'] ) => array( 'l' => current( $input['order'] ) ), 'id' => array( 'l' => 'desc' ) );
                }
            }

            $this->trellis->db->construct( array(
                                                       'select'    => array( 'l' => $input['select'], 'u' => array( array( 'name' => 'uname' ) ) ),
                                                       'from'    => array( 'l' => 'logs' ),
                                                       'join'    => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'u' => 'id', '=', 'l' => 'uid' ) ) ),
                                                       'where'    => $input['where'],
                                                       'order'    => $input['order'],
                                                       'limit'    => $input['limit'],
                                                )       );
        }
        else
        {
            $this->trellis->db->construct( array(
                                                       'select'    => $input['select'],
                                                       'from'    => 'logs',
                                                       'where'    => $input['where'],
                                                       'order'    => $input['order'],
                                                       'limit'    => $input['limit'],
                                                )       );
        }

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $f = $this->trellis->db->fetch_row() )
        {
            $return[ $f['id'] ] = $f;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Log
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'logs',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Log By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Prune Logs
    #=======================================

    public function prune($type, $days)
    {
        if ( ! $type ) return false;
        if ( ! is_numeric( $days ) ) return false;

        $sql_where = array();


        if ( $type == 'acp' )
        {
            $sql_where[] = array( 'admin', '=', 1 );
        }
        elseif ( $type == 'nonacp' )
        {
            $sql_where[] = array( 'admin', '!=', 1 );
        }
        elseif ( $type != 'all' )
        {
            $sql_where[] = array( 'type', '=', $type );
        }

        if ( $days )
        {
            if ( $type != 'all' )
            {
                $sql_where[] = array( 'date', '<', ( time() - ( 60 * 60 * 24 * $days ) ), 'and' );
            }
            else
            {
                $sql_where[] = array( 'date', '<', ( time() - ( 60 * 60 * 24 * $days ) ) );
            }
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'logs',
                                                   'where'    => $sql_where,
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>