<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_groups {

    public $error = '';

    #=======================================
    # @ Get Groups
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'groups',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $g = $this->trellis->db->fetch_row() )
        {
            $return[ $g['g_id'] ] = $g;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Group
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'groups',
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

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'g_id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Group
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'g_name'                => 'string',
                        'g_ticket_access'        => 'int',
                        'g_ticket_create'        => 'int',
                        'g_ticket_edit'            => 'int',
                        'g_ticket_escalate'        => 'int',
                        'g_ticket_close'        => 'int',
                        'g_ticket_reopen'        => 'int',
                        'g_reply_edit'            => 'int',
                        'g_reply_delete'        => 'int',
                        'g_reply_rate'            => 'int',
                        'g_ticket_attach'        => 'int',
                        'g_upload_size_max'        => 'int',
                        'g_upload_exts'            => 'string',
                        'g_kb_access'            => 'int',
                        'g_kb_comment'            => 'int',
                        'g_kb_com_edit'            => 'int',
                        'g_kb_com_delete'        => 'int',
                        'g_kb_rate'                => 'int',
                        'g_kb_perm'                => 'serialize',
                        'g_news_comment'        => 'int',
                        'g_news_com_edit'        => 'int',
                        'g_news_com_delete'        => 'int',
                        'g_change_skin'            => 'int',
                        'g_change_lang'            => 'int',
                        'g_reply_edit_all'        => 'int',
                        'g_kb_com_edit_all'        => 'int',
                        'g_kb_com_delete_all'    => 'int',
                        'g_news_com_edit_all'    => 'int',
                        'g_news_com_delete_all'    => 'int',
                        'g_hide_names'            => 'int',
                        'g_assign_outside'        => 'int',
                        'g_depart_perm'            => 'serialize',
                        'g_acp_access'            => 'int',
                        'g_acp_perm'            => 'serialize',
                        'g_acp_depart_perm'        => 'serialize',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'groups',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Group
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'g_name'                => 'string',
                        'g_ticket_access'        => 'int',
                        'g_ticket_create'        => 'int',
                        'g_ticket_edit'            => 'int',
                        'g_ticket_escalate'        => 'int',
                        'g_ticket_close'        => 'int',
                        'g_ticket_reopen'        => 'int',
                        'g_reply_edit'            => 'int',
                        'g_reply_delete'        => 'int',
                        'g_reply_rate'            => 'int',
                        'g_ticket_attach'        => 'int',
                        'g_upload_size_max'        => 'int',
                        'g_upload_exts'            => 'string',
                        'g_kb_access'            => 'int',
                        'g_kb_comment'            => 'int',
                        'g_kb_com_edit'            => 'int',
                        'g_kb_com_delete'        => 'int',
                        'g_kb_rate'                => 'int',
                        'g_kb_perm'                => 'serialize',
                        'g_news_comment'        => 'int',
                        'g_news_com_edit'        => 'int',
                        'g_news_com_delete'        => 'int',
                        'g_change_skin'            => 'int',
                        'g_change_lang'            => 'int',
                        'g_reply_edit_all'        => 'int',
                        'g_kb_com_edit_all'        => 'int',
                        'g_kb_com_delete_all'    => 'int',
                        'g_news_com_edit_all'    => 'int',
                        'g_news_com_delete_all'    => 'int',
                        'g_hide_names'            => 'int',
                        'g_assign_outside'        => 'int',
                        'g_depart_perm'            => 'serialize',
                        'g_acp_access'            => 'int',
                        'g_acp_perm'            => 'serialize',
                        'g_acp_depart_perm'        => 'serialize',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'groups',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'g_id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Group
    #=======================================

    public function delete($id, $action=0, $mvid=0)
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( $action == 1 )
        {
            if ( ! $mvid = intval( $mvid ) ) return false;

            $this->trellis->db->construct( array(
                                                       'update'    => 'users',
                                                       'set'    => array( 'ugroup' => $mvid ),
                                                       'where'    => array( 'ugroup', '=', $id ),
                                                )       );

            $this->trellis->db->execute();

            $num_users = $this->trellis->db->get_affected_rows();

            if ( $num_users )
            {
                $this->trellis->db->next_no_quotes('set'); # THIS MAKES ME NERVOUS

                $this->trellis->db->construct( array(
                                                           'update'    => 'groups',
                                                           'set'    => array( 'g_users' => 'g_users+'. $num_users ),
                                                           'where'    => array( 'g_id', '=', $mvid ),
                                                    )       );

                $this->trellis->db->execute();
            }
        }
        elseif ( $action == 2 )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'users',
                                                       'where'    => array( 'ugroup', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'groups',
                                                   'where'    => array( 'g_id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>