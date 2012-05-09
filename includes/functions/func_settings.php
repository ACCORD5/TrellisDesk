<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_settings {

    public $error = '';

    #=======================================
    # @ Get Groups
    #=======================================

    public function get_groups($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'settings_groups',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $cg = $this->trellis->db->fetch_row() )
        {
            $return[ $cg['cg_id'] ] = $cg;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Group
    #=======================================

    public function get_single_group($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'settings_groups',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Group By ID
    #=======================================

    public function get_single_group_by_id($select, $id)
    {
        return $this->get_single_group( $select, array( 'cg_id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Get Settings
    #=======================================

    public function get_settings($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'settings',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $cf = $this->trellis->db->fetch_row() )
        {
            $return[ $cf['cf_id'] ] = $cf;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Setting
    #=======================================

    public function get_single_setting($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'settings',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Setting By ID
    #=======================================

    public function get_single_setting_by_id($select, $id)
    {
        return $this->get_single_setting( $select, array( 'cf_id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Edit Setting
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;
        
        $fields = array(
                        'cf_value'        => 'string',
                        'cf_value_old'    => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'settings',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'cf_id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>