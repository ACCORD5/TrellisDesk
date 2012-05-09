<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_pages {

    public $error = '';

    #=======================================
    # @ Get Pages
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'pages',
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
    # @ Get Single Page
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'pages',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Page By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Get Single Page By Alias
    #=======================================

    public function get_single_by_alias($select, $alias)
    {
        return $this->get_single( $select, array( 'alias', '=', $alias ) );
    }

    #=======================================
    # @ Add Page
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'id'            => 'int',
                        'title'            => 'string',
                        'alias'            => 'string',
                        'description'    => 'string',
                        'content'        => 'string',
                        'date'            => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'pages',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Page
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'id'            => 'int',
                        'title'            => 'string',
                        'alias'            => 'string',
                        'description'    => 'string',
                        'content'        => 'string',
                        'modified'        => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'pages',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Page
    #=======================================

    public function delete($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'pages',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>