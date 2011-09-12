<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_rtemplates {

    public $error = '';

    #=======================================
    # @ Get Priorities
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'reply_templates',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $rt = $this->trellis->db->fetch_row() )
        {
            $return[ $rt['id'] ] = $rt;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Reply Template
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'reply_templates',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Reply Template By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Reply Template
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'name'                => 'string',
                        'description'        => 'string',
                        'content_html'        => 'string',
                        'content_plaintext'    => 'string',
                        'position'            => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'reply_templates',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Reply Template
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;
        
        $fields = array(
                        'name'                => 'string',
                        'description'        => 'string',
                        'content_html'        => 'string',
                        'content_plaintext'    => 'string',
                        'position'            => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'reply_templates',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Reply Template
    #=======================================

    public function delete($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'reply_templates',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>