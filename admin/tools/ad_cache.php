<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_cache {

    private $output = "";
    private $error = "";
    private $alert = "";
    private $known_cache = array(
        'categories',
        'departs',
        'dfields',
        'flags',
        'groups',
        'langs',
        'misc',
        'news',
        'pfields',
        'priorities',
        'rtemplates',
        'settings',
        'skins',
        'staff', # TODO: is this too big?
        'statuses',
    );

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'tools', 'cache' );

        $this->trellis->load_functions('rebuild');
        $this->trellis->load_lang('cache');

        $this->trellis->skin->set_active_link( 4 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_chache();
            break;

            case 'dorebuild':
                $this->do_rebuild();
            case 'doclear':
                $this->do_clear();

            default:
                $this->list_cache();
            break;
        }
    }

    #=======================================
    # @ List Cache
    #=======================================

    private function list_cache()
    {
        #=============================
        # Grab Cache
        #=============================

        $size = $this->trellis->cache->get_size();

        $cache_rows = "";

        foreach( $this->known_cache as $cid )
        {
            $cache_rows .= "<tr>
                                <td class='bluecellthin-dark'>{lang.cache_{$cid}}</a></td>
                                <td class='bluecellthin-light'>{$cid}</a></td>
                                <td class='bluecellthin-light' style='font-weight: normal'>". $this->trellis->td_timestamp( array( 'time' => $this->trellis->cache->data['cdate'][ $cid ] ) ) ."</td>
                                <td class='bluecellthin-dark' align='center' style='font-weight: normal'>". $this->trellis->format_size( $size[ $cid ] ) ."</td>
                                <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id={$cid}' class='altbutton'>{lang.rebuild}</a></td>
                                <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=doclear&amp;id={$cid}' class='altbutton'>{lang.clear}</a></td>
                            </tr>";
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.cache_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='18%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='44%' align='left'>{lang.key}</th>
                            <th class='bluecellthin-th' width='17%' align='left'>{lang.modified}</th>
                            <th class='bluecellthin-th' width='11%' align='l'>{lang.size}</th>
                            <th class='bluecellthin-th' width='5%' align='center'>{lang.rebuild}</th>
                            <th class='bluecellthin-th' width='5%' align='center'>{lang.clear}</th>
                        </tr>
                        ". $cache_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'broom', '{lang.menu_clear}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=doclear&amp;id=all' ),
                            array( 'compile', '{lang.menu_rebuild}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=all' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_cache_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_cache_title}', '{lang.help_about_cache_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Clear Cache
    #=======================================

    private function do_clear()
    {
        #=============================
        # Check Cache
        #=============================

        if ( $this->trellis->input['id'] != 'all' && ! in_array( $this->trellis->input['id'], $this->known_cache ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_cache_unknown'] );
            $this->list_cache();
        }

        #=============================
        # Rebuild Cache
        #=============================

        if ( $this->trellis->input['id'] == 'all' )
        {
            foreach ( $this->known_cache as $name )
            {
                $this->trellis->cache->clear( $name );
            }
        }
        else
        {
            $this->trellis->cache->clear( $this->trellis->input['id'] );
        }

        $this->trellis->log( array( 'msg' => array( 'cache_cleared', $this->trellis->lang[ 'cache_'. $this->trellis->input['id'] ] ), 'type' => 'other', 'content_type' => 'cache' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_cache_cleared'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Rebuild Cache
    #=======================================

    private function do_rebuild()
    {
        #=============================
        # Check Cache
        #=============================

        if ( $this->trellis->input['id'] != 'all' && ! in_array( $this->trellis->input['id'], $this->known_cache ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_cache_unknown'] );
            $this->list_cache();
        }

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        if ( $this->trellis->input['id'] == 'all' )
        {
            foreach ( $this->known_cache as $name )
            {
                $cache_function = $name .'_cache';

                $this->trellis->func->rebuild->$cache_function();
            }
        }
        else
        {
            $cache_function = $this->trellis->input['id'] .'_cache';

            $this->trellis->func->rebuild->$cache_function();
        }

        $this->trellis->log( array( 'msg' => array( 'cache_rebuilt', $this->trellis->lang[ 'cache_'. $this->trellis->input['id'] ] ), 'type' => 'other', 'content_type' => 'cache' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_cache_rebuilt'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

}

?>