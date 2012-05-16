<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_source_dashboard {

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        #=============================
        # Grab News
        #=============================

        if ( $this->trellis->cache->data['settings']['news']['enable'] && $this->trellis->cache->data['settings']['news']['dashboard'] )
        {
            if ( ! empty( $this->trellis->cache->data['news'] ) )
            {
                $news = array();

                foreach( $this->trellis->cache->data['news'] as $n )
                {
                    $news[ $n['id'] ] = $n;

                    #=============================
                    # Format News
                    #=============================

                    $news[ $n['id'] ]['date_human'] = $this->trellis->td_timestamp( array( 'time' => $a['date'], 'format' => 'short' ) );
                }

                $this->trellis->skin->set_var( 'news', $news );
            }
        }

        #=============================
        # Grab Tickets
        #=============================

        # TODO: if displaying last reply user name column, remember to honor hide group names setting

        if ( $this->trellis->user['g_ticket_access'] )
        {
            $this->trellis->load_functions('tickets');

            if ( $this->trellis->user['id'] || $this->trellis->user['s_tkey'] )
            {
                if ( $this->trellis->user['id'] )
                {
                    $tickets = $this->trellis->func->tickets->get( array(
                                                                   'select'    => array(
                                                                                        't' => array( 'id', 'mask', 'subject', 'priority', 'last_reply', 'escalated', 'status' ),
                                                                                        'd' => array( array( 'name' => 'dname' ) ),
                                                                                        'p' => array( array( 'name' => 'pname' ), 'icon_regular', 'icon_assigned' ),
                                                                                     's' => array( array( 'name_user' => 'status_name' ), array( 'abbr_user' => 'status_abbr' ) ),
                                                                                     ),
                                                                   'from'    => array( 't' => 'tickets' ),
                                                                   'join'    => array(
                                                                                     array( 'from' => array( 'd' => 'departments' ), 'where' => array( 't' => 'did', '=', 'd' => 'id' ) ),
                                                                                     array( 'from' => array( 'p' => 'priorities' ), 'where' => array( 't' => 'priority', '=', 'p' => 'id' ) ),
                                                                                     array( 'from' => array( 's' => 'statuses' ), 'where' => array( 't' => 'status', '=', 's' => 'id' ) ),
                                                                                     ),
                                                                   'where'    => array( array( 't' => 'uid' ), '=', $this->trellis->user['id'] ),
                                                                   'order'    => array( 'last_reply' => array( 't' => 'desc' ) ),
                                                                   'limit'    => array( 0, 8 ),
                                                            )       );
                }
                elseif ( $this->trellis->user['s_tkey'] )
                {
                    $this->trellis->db->construct( array(
                                                               'select'    => array( 'id', 'dname', 'subject', 'priority', 'date', 'status' ),
                                                               'from'        => 'tickets',
                                                                'where'    => array( array( 'email', '=', $this->trellis->user['s_email'] ), array( 'guest', '=', 1, 'and' ) ),
                                                                'order'    => array( 'date' => 'DESC' ),
                                                                'limit'    => array( 0, 8 ),
                                                         )     );
                }

                if ( $tickets )
                {
                    foreach ( $tickets as &$t )
                    {
                        #=============================
                        # Format Tickets
                        #=============================

                        if ( $t['date'] ) $t['date_human'] = $this->trellis->td_timestamp( array( 'time' => $t['date'], 'format' => 'short' ) );
                        if ( $t['last_reply'] ) $t['last_reply_human'] = $this->trellis->td_timestamp( array( 'time' => $t['last_reply'], 'format' => 'short' ) );

                        if ( ! $t['status_abbr'] ) $t['status_abbr'] = $t['status_name'];
                    }

                    $this->trellis->skin->set_var( 'tickets', $tickets );
                }
            }
        }

        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->set_var( 'sub_tpl', 'dashboard.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => array( '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=dashboard">'. $this->trellis->lang['dashboard'] .'</a>' ) ) );
    }

}

?>