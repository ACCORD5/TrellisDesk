<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_recount {

    #=======================================
    # @ Recount: Tickets per User
    # Recounts the number of tickets per
    # user.
    #=======================================

    function r_tickets_per_user($shutdown=0)
    {
        if ( $shutdown == 1 )
        {
            $this->trellis->shutdown_funcs[] = 'r_tickets_per_user';
            return TRUE;
        }

        #=============================
        # Grab Tickets
        #=============================

        $this->trellis->db->construct( array(
                                            'select'    => array( 'id', 'uid', 'status' ),
                                            'from'    => 'tickets',
                                      )      );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $t = $this->trellis->db->fetch_row() )
            {
                if ( $t['status'] != 6 )
                {
                    $o_tickets[ $t['uid'] ] ++;
                }

                $tickets[ $t['uid'] ] ++;
            }
        }

        #=============================
        # Grab Users
        #=============================

        $this->trellis->db->construct( array(
                                          'select'    => array( 'id' ),
                                            'from'    => 'users',
                                      )      );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $m = $this->trellis->db->fetch_row() )
            {
                $users[ $m['id'] ] = 1;
            }
        }

        #=============================
        # Update Users
        #=============================

        while ( list( $uid, ) = each( $users ) )
        {
            $this->trellis->db->construct( array(
                                              'update'    => 'users',
                                              'set'        => array( 'open_tickets' => $o_tickets[ $uid ], 'tickets' => $tickets[ $uid ] ),
                                                'where'    => array( 'id', '=', $uid ),
                                                'limit'    => array( 1 ),
                                         )      );

            $this->trellis->db->execute();
        }

        $this->trellis->log( 'other', "Recounted Tickets Per User" );
    }

    #=======================================
    # @ Recount: Tickets per Department
    # Recounts the number of tickets per
    # department.
    #=======================================

    function r_tickets_per_dept($shutdown=0)
    {
        if ( $shutdown == 1 )
        {
            $this->trellis->shutdown_funcs[] = 'r_tickets_per_dept';
            return TRUE;
        }

        #=============================
        # Grab Tickets
        #=============================

        $this->trellis->db->construct( array(
                                            'select'    => array( 'id', 'did' ),
                                            'from'    => 'tickets',
                                      )      );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $t = $this->trellis->db->fetch_row() )
            {
                $tickets[ $t['did'] ] ++;
            }
        }

        #=============================
        # Grab Departments
        #=============================

        $this->trellis->db->construct( array(
                                            'select'    => array( 'id' ),
                                            'from'    => 'departments',
                                     )      );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $d = $this->trellis->db->fetch_row() )
            {
                $departs[ $d['id'] ] = 1;
            }
        }

        #=============================
        # Update Departments
        #=============================

        while ( list( $did, ) = each( $departs ) )
        {
            $this->trellis->db->construct( array(
                                                'update'    => 'departments',
                                                'set'        => array( 'tickets' => $tickets[ $did ] ),
                                                'where'    => array( 'id', '=', $did ),
                                                'limit'    => array( 1 ),
                                          )      );

            $this->trellis->db->execute();
        }

        $this->trellis->log( 'other', "Recounted Tickets Per Department" );
    }
}

?>