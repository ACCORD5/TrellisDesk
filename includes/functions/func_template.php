<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_template {

    # TODO: expand, let's take advantage of template

    #=======================================
    # @ Update Stats
    #=======================================

    protected function update_stats($id, $amount, $table, $field, $type, $now=0)
    {
        if ( $type == 'increase' )
        {
            $type = '+';
        }
        elseif ( $type == 'decrease' )
        {
            $type = '-';
        }
        else
        {
            return false;
        }

        if ( ! $id = intval( $id ) ) return false;
        if ( ! $amount = intval( $amount ) ) return false;

        $this->trellis->db->construct( array(
                                             'update'    => $table,
                                             'set'        => array( $field => array( $type, $amount ) ),
                                             'where'    => array( 'id', '=', $id ),
                                             'limit'    => array( 1 ),
                                      )         );

        if ( ! $now ) $this->trellis->db->next_shutdown();

        $this->trellis->db->execute();
    }

}

?>