<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_logs {

    private $output = "";
    private $known_types = array(
        'kb'        => '{lang.type_kb}',
        'news'        => '{lang.type_news}',
        'other'        => '{lang.type_other}',
        'security'    => '{lang.type_security}',
        'settings'    => '{lang.type_settings}',
        'ticket'    => '{lang.type_ticket}',
        'user'        => '{lang.type_user}',
    );

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'tools', 'logs' );

        $this->trellis->load_functions('logs');
        $this->trellis->load_lang('logs');

        $this->trellis->skin->set_active_link( 4 );

        switch( $this->trellis->input['act'] )
        {
            case 'prune':
                $this->prune_logs();
            break;

            case 'doprune':
                $this->do_prune();
            break;

            default:
                $this->list_logs();
            break;
        }
    }

    #=======================================
    # @ List Logs
    #=======================================

    private function list_logs()
    {
        #=============================
        # Sorting Options
        #=============================

        $sort = $this->trellis->generate_sql_sort( array(
                                                         'default_sort' => 'date',
                                                         'default_order' => 'desc',
                                                         'base_url' => $this->generate_url( array( 'sort' => '', 'order' => '' ) ),
                                                         'options' => array(
                                                                             'action' => '{lang.action}',
                                                                             'type' => '{lang.type}',
                                                                             'date' => '{lang.date}',
                                                                             'uname' => '{lang.user}',
                                                                             'ipadd' => '{lang.ip_address}',
                                                                             ),
                                                  )         );

        #=============================
        # Filters
        #=============================

        $gfilters = null;
        $filters = null;
        $sql_where = null;
        $sql_where_extra = null;

        if ( isset( $this->trellis->input['admin'] ) )
        {
            if ( $this->trellis->input['admin'] )
            {
                $gfilters[] = array( array( 'l' => 'admin' ), '=', 1 );
            }
            else
            {
                $gfilters[] = array( array( 'l' => 'admin' ), '!=', 1 );
            }
        }
        elseif ( $this->trellis->input['type'] )
        {
            $gfilters[] = array( array( 'l' => 'type' ), '=', strtolower( $this->trellis->input['type'] ) );
        }

        if ( $this->trellis->input['content_type'] )
        {
            $filters[] = array( array( 'l' => 'content_type' ), '=', strtolower( $this->trellis->input['content_type'] ) );
        }

        if ( $this->trellis->input['content_id'] )
        {
            $filters[] = array( array( 'l' => 'content_id' ), '=', strtolower( $this->trellis->input['content_id'] ) );
        }

        if ( ! empty( $gfilters ) )
        {
            foreach( $gfilters as $fdata )
            {
                if ( ! empty( $sql_where ) ) $fdata[] = 'and';

                $sql_where[] = $fdata;
            }
        }

        if ( ! empty( $filters ) )
        {
            foreach( $filters as $fdata )
            {
                if ( ! empty( $sql_where ) ) $fdata[] = 'and';

                $sql_where[] = $fdata;
            }
        }

        if ( $this->trellis->input['content_type'] == 'ticket' )
        {
            $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'    => 'replies',
                                                   'where'    => array( 'tid', '=', $this->trellis->input['content_id'] ),
                                            )       );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() )
            {
                $replies = array();

                while ( $r = $this->trellis->db->fetch_row() )
                {
                    $replies[] = $r['id'];
                }

                foreach( $gfilters as $fdata )
                {
                    if ( ! empty( $sql_where_extra ) ) $fdata[] = 'and';

                    $sql_where_extra[] = $fdata;
                }

                $sql_where_extra[] = array( array( 'l' => 'content_type' ), '=', 'reply', 'and' );
                $sql_where_extra[] = array( array( 'l' => 'content_id' ), 'in', $replies, 'and' );

                $sql_where_extra[] = 'or';

                $sql_where = array( $sql_where, $sql_where_extra );
            }
        }

        #=============================
        # Grab Logs
        #=============================

        $l_total = $this->trellis->func->logs->get( array( 'select' => array( 'id', 'uid' ), 'where' => $sql_where ) );

        $log_rows = '';

        if ( ! $logs = $this->trellis->func->logs->get( array( 'select' => array( 'id', 'uid', 'action', 'extra', 'type', 'level', 'admin', 'date', 'ipadd' ), 'where' => $sql_where, 'order' => array( $sort['sort'] => $sort['order'] ), 'limit' => array( $this->trellis->input['st'], 15 ) ) ) )
        {
            $log_rows .= "<tr><td class='bluecell-light' colspan='5'><strong>{lang.no_logs}</strong></td></tr>";
        }
        else
        {
            foreach( $logs as $lid => $l )
            {
                $l['date'] = $this->trellis->td_timestamp( array( 'time' => $l['date'], 'format' => 'short' ) );

                if ( $l['level'] == 2 )
                {
                    $fontcolor_start = "<font color='#790000'>";
                    $fontcolor_end = "<font color='#790000'>";
                }
                else
                {
                    $fontcolor_start = "";
                    $fontcolor_end = "";
                }

                $type = ( $this->known_types[ $l['type'] ] ) ? $this->known_types[ $l['type'] ] : $l['type'];

                $log_rows .= "<tr>
                                    <td class='bluecell-light'>{$fontcolor_start}{$l['action']}{$fontcolor_end}</td>
                                    <td class='bluecell-light'>{$fontcolor_start}{$type}{$fontcolor_end}</td>
                                    <td class='bluecell-dark' style='font-weight:normal'>{$fontcolor_start}{$l['date']}{$fontcolor_end}</td>
                                    <td class='bluecell-light'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$l['uid']}'>{$fontcolor_start}{$l['uname']}{$fontcolor_end}</a></td>
                                    <td class='bluecell-light' style='font-weight:normal'>{$fontcolor_start}{$l['ipadd']}{$fontcolor_end}</td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $l_total ),
                                                        'per_page'    => 15,
                                                        'url'        => $this->generate_url( array( 'st' => 0 ) ),
                                                        ) );

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.logs}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='44%' align='left'>{$sort['link_action']}</th>
                            <th class='bluecellthin-th' width='11%' align='left'>{$sort['link_type']}</th>
                            <th class='bluecellthin-th' width='15%' align='left'>{$sort['link_date']}</th>
                            <th class='bluecellthin-th' width='15%' align='left'>{$sort['link_uname']}</th>
                            <th class='bluecellthin-th' width='15%' align='left'>{$sort['link_ipadd']}</th>
                        </tr>
                        ". $log_rows ."
                        <tr>
                            <td class='bluecellthin-th-pages' colspan='5'>". $page_links ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'broom', '{lang.menu_prune}', '<! TD_URL !>/admin.php?section=tools&amp;page=logs&amp;act=prune' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=log' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_logs_options}', $menu_items );

        $type_items = array();

        $type_items[] = "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=logs'>{lang.type_all}</a>";
        $type_items[] = "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=logs&amp;admin=1'>{lang.type_acp}</a>";
        $type_items[] = "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=logs&amp;admin=0'>{lang.type_non_acp}</a>";

        foreach( $this->known_types as $tid => $tname )
        {
            $type_items[] = "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=logs&amp;type=". $tid ."'>". $tname ."</a>";
        }

        $this->trellis->skin->add_sidebar_list( '{lang.types}', $type_items );

        $this->trellis->skin->add_sidebar_help( '{lang.help_about_logs_title}', '{lang.help_about_logs_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Prune Logs
    #=======================================

    private function prune_logs()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'tools', 'logs', 'prune' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=tools&amp;page=logs&amp;act=doprune", 'prune_logs', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.pruning_logs}' ) ."
                        ". $this->trellis->skin->group_row( "<label for='type'>{lang.prune}</label> ". $this->trellis->skin->drop_down( array( 'name' => 'type', 'options' => array_merge( array( 'all' => '{lang.type_all}', 'acp' => '{lang.type_acp}', 'nonacp' => '{lang.type_non_acp}' ), $this->known_types ) ) ) ." <label for='days'>{lang.logs_older_than}</label> <input type='text' id='days' name='days' value='' size='2' /> <label for='days'>{lang.days}</label>", 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'prune', '{lang.button_prune_logs}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=tools&amp;page=logs' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=log' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_logs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Prune
    #=======================================

    private function do_prune()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'tools', 'logs', 'prune' );

        if ( ! is_numeric( $this->trellis->input['days'] ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_days'] );

            $this->prune_logs();
        }

        #=============================
        # PRUNE Logs
        #=============================

        $this->trellis->func->logs->prune( $this->trellis->input['type'], $this->trellis->input['days'] );

        $this->trellis->log( array( 'msg' => array( 'logs_pruned', $this->trellis->lang[ 'type_'. $this->trellis->input['type'] ], $this->trellis->input['days'] ), 'type' => 'security', 'level' => 2, 'content_type' => 'logs' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_logs_pruned'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Generate URL
    #=======================================

    private function generate_url($params=array())
    {
        $url = '<! TD_URL !>/admin.php?section=tools&amp;page=logs';

        if ( ! isset( $params['admin'] ) ) $params['admin'] = $this->trellis->input['admin'];
        if ( ! isset( $params['type'] ) ) $params['type'] = $this->trellis->input['type'];
        if ( ! isset( $params['content_type'] ) ) $params['content_type'] = $this->trellis->input['content_type'];
        if ( ! isset( $params['content_id'] ) ) $params['content_id'] = $this->trellis->input['content_id'];
        if ( ! isset( $params['sort'] ) ) $params['sort'] = $this->trellis->input['sort'];
        if ( ! isset( $params['order'] ) ) $params['order'] = $this->trellis->input['order'];
        if ( ! isset( $params['st'] ) ) $params['st'] = $this->trellis->input['st'];

        if ( $params['admin'] ) $url .= '&amp;admin='. $params['admin'];

        if ( $params['type'] ) $url .= '&amp;type='. $params['type'];

        if ( $params['content_type'] ) $url .= '&amp;content_type='. $params['content_type'];

        if ( $params['content_id'] ) $url .= '&amp;content_id='. $params['content_id'];

        if ( $params['sort'] ) $url .= '&amp;sort='. $params['sort'];

        if ( $params['order'] ) $url .= '&amp;order='. $params['order'];

        if ( $params['st'] ) $url .= '&amp;st='. $params['st'];

        return $url;
    }

}

?>