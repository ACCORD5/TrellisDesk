<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_tickets {

    public $error = '';
    private $auto_assigned = array();
    private $auto_moved = 0;
    private $key = '';

    #=======================================
    # @ Get Tickets
    #=======================================

    public function get($input)
    {
        $return = array();

        # This is almost just like the construct() function. Necessary?

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => $input['from'],
                                                   'join'    => $input['join'],
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $t = $this->trellis->db->fetch_row() )
        {
            $return[ $t['id'] ] = $t;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Ticket
    #=======================================

    public function get_single($input)
    {
        # This is almost just like the construct() function. Necessary?

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => $input['from'],
                                                   'join'    => $input['join'],
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Ticket By ID
    #=======================================

    public function get_single_by_id($input, $id)
    {
        if ( $input['join'] )
        {
            $input['where'] = array( array( 't' => 'id' ), '=', intval( $id ) );
        }
        else
        {
            $input['from'] = 'tickets';
            $input['where'] = array( 'id', '=', intval( $id ) );
        }

        return $this->get_single( $input );
    }

    #=======================================
    # @ Get Id From Mask
    #=======================================

    public function get_id_from_mask($mask)
    {
        $t = $this->get_single( array( 'select' => array( 'id' ), 'from' => 'tickets', 'where' => array( 'mask', '=', $mask ) ) );

        return $t['id'];
    }

    #=======================================
    # @ Get Ticket Assignments
    #=======================================

    public function get_assignments($tid, $key='id')
    {
        $return = array();

        if ( ! $tid = intval( $tid ) ) return false;

        $this->trellis->db->construct( array(
                                                   'select'    => array(
                                                                        'a' => array( 'id', 'uid' ),
                                                                        'u' => array( array( 'name' => 'uname') ),
                                                                        ),
                                                   'from'    => array( 'a' => 'assign_map' ),
                                                   'where'    => array( array( 'a' => 'tid' ), '=', $tid ),
                                                   'join'    => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'a' => 'uid', '=', 'u' => 'id' ) ) ),
                                                   'group'    => array( 'a' => 'uid' ),
                                                   'order'    => array( 'name' => array( 'u' => 'asc' ) ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $a = $this->trellis->db->fetch_row() )
        {
            $return[ $a[ $key ] ] = $a;
        }

        return $return;
    }

    #=======================================
    # @ Get Ticket Assignments By User ID
    #=======================================

    public function get_assignments_by_uid($tid)
    {
        return $this->get_assignments( $tid, 'uid' );
    }

    #=======================================
    # @ Get Ticket Flags
    #=======================================

    public function get_flags($tid)
    {
        $return = array();

        if ( ! $tid = intval( $tid ) ) return false;

        $this->trellis->db->construct( array(
                                                   'select'    => array(
                                                                        'm' => array( 'id', 'fid' ),
                                                                        'f' => array( 'name', 'icon' ),
                                                                        ),
                                                   'from'    => array( 'm' => 'flags_map' ),
                                                   'join'    => array( array( 'from' => array( 'f' => 'flags' ), 'where' => array( 'm' => 'fid', '=', 'f' => 'id' ) ) ),
                                                   'where'    => array( array( 'm' => 'tid' ), '=', $tid ),
                                                   'group'    => array( 'm' => 'fid' ),
                                                   'order'    => array( 'position' => array( 'f' => 'asc' ) ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $f = $this->trellis->db->fetch_row() )
        {
            $return[ $f['id'] ] = $f;
        }

        return $return;
    }

    #=======================================
    # @ Check For Ticket Assignment
    #=======================================

    public function check_assignment($uid, $tid)
    {
        if ( ! $uid = intval( $uid ) ) return false;
        if ( ! $tid = intval( $tid ) ) return false;

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'    => 'assign_map',
                                                   'where'    => array( array( 'tid', '=', $tid ), array( 'uid', '=', $uid, 'and' ) ),
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_num_rows();
    }

    #=======================================
    # @ Add Ticket
    #=======================================

    public function add($data, $params=array())
    {
        $fields = array(
                        'mask'            => 'string',
                        'did'            => 'int',
                        'uid'            => 'int',
                        'email'            => 'string',
                        'subject'        => 'string',
                        'priority'        => 'int',
                        'message'        => 'string',
                        'date'            => 'int',
                        'last_reply'    => 'int',
                        'last_uid'        => 'int',
                        'notes'            => 'string',
                        'status'        => 'int',
                        'accepted'        => 'int',
                        'ipadd'            => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'tickets',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        $id = $this->trellis->db->get_insert_id();

        $mask = $this->generate_mask( $id );

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => array( 'mask' => $mask ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( $data['uid'] )
        {
            $this->key = '';
        }
        else
        {
            $fields = array(
                'id'        => 'int',
                'gname'        => 'string',
                'key'        => 'string',
                'lang'        => 'int',
                'notify'    => 'int',
            );

            $gdata = array(
                'id'        => $id,
                'gname'     => $data['uname'],
                'key'        => substr( md5( uniqid() . $data['email'] ), 0, 10 ),
                'lang'         => $data['lang'],
                'notify'     => $data['notify'],
            );

            $this->trellis->db->construct( array(
                'insert'    => 'tickets_guests',
                'set'        => $this->trellis->process_data( $fields, $gdata ),
            ) );

            $this->trellis->db->execute();

            $this->key = $gdata['key'];
        }

        $assign_auto = unserialize( $this->trellis->cache->data['departs'][ $data['did'] ]['assign_auto'] );

        $assigned = array();
        $assigned_log = array();

        if ( ! empty( $assign_auto ) )
        {
            foreach( $assign_auto as $uid )
            {
                $assigned_log[ $uid ] = $this->add_assignment( $uid, $id, 1, 1, 1 );

                $assigned[ $uid ] = 1;
            }

            $this->set_auto_assigned( $assigned_log );
        }
        else
        {
            $this->clear_auto_assigned();
        }

        // increment department tickets count
        $this->trellis->db->construct( array(
            'update'    => 'departments',
            'set'        => array( 'tickets_total' => array( '+', 1 ) ),
            'where'        => array( 'id', '=', $data['did'] ),
            'limit'        => array( 1 ),
        ) );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        if ( $data['uid'] )
        {
            // increment user tickets count
            $this->trellis->db->construct( array(
                'update'    => 'users',
                'set'        => array( 'tickets_total' => array( '+', 1 ), 'tickets_open' => array( '+', 1 ) ),
                'where'        => array( 'id', '=', $data['uid'] ),
                'limit'        => array( 1 ),
            ) );
        }

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        // Email Notifications
        $this->trellis->load_email();

        $email_tags = array(
                            '{TICKET_ID}'        => $mask,
                            '{KEY}'                => $this->key,
                            '{UNAME}'            => $data['uname'],
                            '{SUBJECT}'            => $data['subject'],
                            '{DEPARTMENT}'        => $this->trellis->cache->data['departs'][ $data['did'] ]['name'],
                            '{PRIORITY}'        => $this->trellis->cache->data['priorities'][ $data['priority'] ]['name'],
                            '{MESSAGE}'            => $this->trellis->prepare_email( $data['message'], 0, 'plain' ),
                            '{MESSAGE_HTML}'    => $this->trellis->prepare_email( $data['message'], 0, 'html' ),
                            '{LINK}'            => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $mask,
                            '%7BLINK%7D'        => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $mask, # CHECK: we have to do this cause HTMLPurifier urlencodes our brackets {} maybe use _ instead of {
                            '{ACTION_USER}'        => $this->trellis->user['name'],
                            );

        // Hide Staff Name
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['g_name'];

        if ( $data['uid'] )
        {
            if ( $data['uid'] == $this->trellis->user['id'] )
            {
                $this->trellis->email->send_email( array( 'to' => $data['uid'], 'msg' => 'ticket_new', 'replace' => $email_tags, 'type' => 'ticket', 'type_user' => 'ticket' ) );
            }
            else
            {
                $this->trellis->email->send_email( array( 'to' => $data['uid'], 'msg' => 'ticket_new_behalf', 'replace' => $email_tags, 'type' => 'ticket', 'type_user' => 'ticket' ) );
            }
        }
        else
        {
            if ( $data['notify'] ) $this->trellis->email->send_email( array( 'to' => $data['email'], 'name' => $data['uname'], 'msg' => 'ticket_new_guest_behalf', 'replace' => $email_tags, 'type' => 'ticket', 'type_user' => 'ticket', 'lang' => $data['lang'] ) );
        }

        $email_tags['{TICKET_ID}'] = $id;
        $email_tags['{LINK}'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $id;
        $email_tags['%7BLINK%7D'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $id;

        // Restore Staff Name for Staff Notifications
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['name'];

        $this->trellis->email->notify_staff( array( 'msg' => 'ticket_new'. ( ( ! $data['uid'] ) ? '_guest' : '' ), 'replace' => $email_tags, 'type' => 'ticket', 'tid' => $id, 'did' => $data['did'], 'assigned' => $assigned, 'exclude' => $this->trellis->user['id'] ) );

        #TODO: update depart, user, stats, send emails, feed, etc

        if ( $params['return'] == 'mask' )
        {
            return array( 'id' => $id, 'mask' => $mask );
        }
        else
        {
            return $id;
        }
    }

    #=======================================
    # @ Add Reply
    #=======================================

    public function add_reply($data, $tid, $params=array())
    {
        $fields = array(
                        'tid'        => 'int',
                        'uid'        => 'int',
                        'message'    => 'string',
                        'signature'    => 'int',
                        'staff'        => 'int',
                        'html'        => 'string',
                        'secret'    => 'int',
                        'date'        => 'int',
                        'ipadd'        => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'replies',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        $id = $this->trellis->db->get_insert_id();

        $db_array = array();

        if ( ! $data['secret'] )
        {
            if ( $data['staff'] ) // Only care about accepted status if we are staff
            {
                $sql_select = array();

                if ( ! isset( $params['accepted'] ) ) $sql_select[] = 'accepted';
                if ( ! isset( $params['onhold'] ) ) $sql_select[] = 'onhold';

                if ( ! empty( $sql_select ) )
                {
                    if ( ! $t = $this->get_single_by_id( array( 'select' => $sql_select, 'from' => 'tickets' ), $tid ) ) return false;
                }

                if ( ! isset( $params['accepted'] ) ) $params['accepted'] = $t['accepted'];
                if ( ! isset( $params['onhold'] ) ) $params['onhold'] = $t['onhold'];
            }

            $db_array = array(
                              'last_reply'            => time(),
                              'last_uid'            => $this->trellis->user['id'],
                              'replies'             => array( '+', 1 ),
                              );

            if ( $data['staff'] ) // " " ^^^
            {
                $db_array['last_reply_staff'] = time();

                if ( ! $params['accepted'] )
                {
                    $db_array['accepted'] = 1;
                }

                if ( $params['onhold'] )
                {
                    if ( ! $params['keep_hold'] )
                    {
                        $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][5];

                        $db_array['aua'] = 1;
                        $db_array['onhold'] = 0;
                    }
                }
                else
                {
                    $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][5];

                    $db_array['aua'] = 1;
                }
            }
            else
            {
                $sql_select = array();

                if ( ! isset( $params['aua'] ) ) $sql_select[] = 'aua';
                if ( ! isset( $params['onhold'] ) ) $sql_select[] = 'onhold';

                if ( ! empty( $sql_select ) )
                {
                    if ( ! $t = $this->get_single_by_id( array( 'select' => $sql_select, 'from' => 'tickets' ), $tid ) ) return false;
                }

                if ( ! isset( $params['aua'] ) ) $params['aua'] = $t['aua'];
                if ( ! isset( $params['onhold'] ) ) $params['onhold'] = $t['onhold'];

                if ( $params['aua'] )
                {
                    if ( ! $params['onhold'] ) $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][2];

                    $db_array['aua'] = 0;
                }
            }
        }

        if ( $this->trellis->cache->data['settings']['ticket']['track'] )
        {
            $db_array['last_reply_all'] = time();
        }

        if ( ! empty( $db_array ) )
        {
            $this->trellis->db->construct( array(
                                                       'update'    => 'tickets',
                                                       'set'    => $db_array,
                                                       'where'    => array( 'id', '=', $tid ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->execute();
        }

        // Email Notifications
        $this->trellis->load_email();

        $email_tags = array(
                            '{TICKET_ID}'        => $data['mask'],
                            '{UNAME}'            => $data['tuname'],
                            '{SUBJECT}'            => $data['subject'],
                            '{DEPARTMENT}'        => $this->trellis->cache->data['departs'][ $data['did'] ]['name'],
                            '{PRIORITY}'        => $this->trellis->cache->data['priorities'][ $data['priority'] ]['name'],
                            '{MESSAGE}'            => $this->trellis->prepare_email( $data['message_original'], 0, 'plain' ), # TODO: we are expecting this data to be passed to us, not a good thing. look it up if not passed. same applies to other functions
                            '{MESSAGE_HTML}'    => $this->trellis->prepare_email( $data['message_original'], 0, 'html' ),
                            '{LINK}'            => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $data['mask'],
                            '%7BLINK%7D'        => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $data['mask'],
                            '{ACTION_USER}'        => $this->trellis->user['name'],
                            );

        $email_tags['{REPLY_HTML}'] = $this->trellis->prepare_email( $data['message'], $data['html'], 'html' );
        $email_tags['{REPLY}'] = $this->trellis->prepare_email( $data['message'], $data['html'], 'plain' );

        // Hide Staff Name
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['g_name'];

        if ( $data['staff'] && ! $data['secret'] )
        {
            if ( $data['tuid'] )
            {
                $this->trellis->email->send_email( array( 'to' => $data['tuid'], 'msg' => 'ticket_reply', 'replace' => $email_tags, 'type' => 'ticket', 'type_user' => 'reply' ) );
            }
            else
            {
                if ( $data['notify'] ) $this->trellis->email->send_email( array( 'to' => $data['email'], 'name' => $data['tuname'], 'msg' => 'ticket_reply', 'replace' => $email_tags, 'type' => 'ticket', 'type_user' => 'reply', 'lang' => $data['lang'] ) );
            }
        }

        if ( $data['secret'] )
        {
            $email_tags['{REPLY_HTML}'] = '<p>'. $this->trellis->lang['staff_only_email_line_html'] .'</p>'. $email_tags['{REPLY_HTML}'];
            $email_tags['{REPLY}'] = $this->trellis->lang['staff_only_email_line'] ."\n\n". $email_tags['{REPLY}'];
        }

        $email_tags['{TICKET_ID}'] = $tid;
        $email_tags['{LINK}'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;
        $email_tags['%7BLINK%7D'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;

        // Restore Staff Name for Staff Notifications
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['name'];

        $this->trellis->email->notify_staff( array( 'msg' => 'ticket_reply', 'replace' => $email_tags, 'type' => 'reply', 'tid' => $tid, 'did' => $data['did'], 'exclude' => $this->trellis->user['id'] ) );

        return $id;
    }

    #=======================================
    # @ Add Ticket Assignment
    #=======================================

    public function add_assignment($uid, $tid, $skip_check=0, $return_name=0, $no_email=0, $data=array())
    {
        # Not safe to use as shut down query

        if ( ! $uid = intval( $uid ) ) return false;
        if ( ! $tid = intval( $tid ) ) return false;

        if ( ! $skip_check )
        {
            if ( $this->check_assignment( $uid, $tid ) ) return false;
        }

        if ( $return_name )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'name' ),
                                                       'from'    => 'users',
                                                       'where'    => array( 'id', '=', $uid ),
                                                       'limit'    => array( 0, 1 ),
                                                )       );

            $this->trellis->db->execute();

            if ( ! $this->trellis->db->get_num_rows() ) return false;

            $result = $this->trellis->db->fetch_row();
        }

        $this->trellis->db->construct( array(
                                                   'insert'    => 'assign_map',
                                                   'set'    => array( 'tid' => $tid, 'uid' => $uid ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $no_email )
        {
            $this->trellis->load_email();

            $email_tags = array(
                                '{TICKET_ID}'        => $tid,
                                '{UNAME}'            => $data['uname'],
                                '{SUBJECT}'            => $data['subject'],
                                '{DEPARTMENT}'        => $this->trellis->cache->data['departs'][ $data['did'] ]['name'],
                                '{PRIORITY}'        => $this->trellis->cache->data['priorities'][ $data['priority'] ]['name'],
                                '{MESSAGE}'            => $this->trellis->prepare_email( $data['message'], 0, 'plain' ),
                                '{MESSAGE_HTML}'    => $this->trellis->prepare_email( $data['message'], 0, 'html' ),
                                '{LINK}'            => $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid,
                                '%7BLINK%7D'        => $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid,
                                '{ACTION_USER}'        => $this->trellis->user['name'],
                                );

            if ( $uid != $this->trellis->user['id'] ) $this->trellis->email->send_email( array( 'to' => $uid, 'msg' => 'ticket_assign_staff', 'replace' => $email_tags, 'type' => 'staff_assign', 'type_staff' => 'assign' ) );
        }

        if ( $return_name )
        {
            return $result['name'];
        }
        else
        {
            return true;
        }
    }

    #=======================================
    # @ Add Ticket Flag
    #=======================================

    public function add_flag($fid, $tid)
    {
        if ( ! $fid = intval( $fid ) ) return false;
        if ( ! $tid = intval( $tid ) ) return false;

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'    => 'flags_map',
                                                   'where'    => array( array( 'tid', '=', $tid ), array( 'fid', '=', $fid, 'and' ) ),
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() ) return false;

        $this->trellis->db->construct( array(
                                                   'insert'    => 'flags_map',
                                                   'set'    => array( 'tid' => $tid, 'fid' => $fid ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Ticket
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'did'            => 'int',
                        'subject'        => 'string',
                        'priority'        => 'int',
                        'message'        => 'string',
                        'last_reply'    => 'int',
                        'last_uid'        => 'int',
                        'replies'        => 'int',
                        'notes'            => 'string',
                        'status'        => 'int',
                        'close_uid'        => 'int',
                        'close_date'    => 'int',
                        'accepted'        => 'int',
                        'aua'            => 'int',
                        'escalated'        => 'int',
                        'onhold'        => 'int',
                        'closed'        => 'int',
                        'allow_reopen'    => 'int',
                        'ipadd'            => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Edit Reply
    #=======================================

    public function edit_reply($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'message'    => 'string',
                        'signature'    => 'int',
                        'staff'        => 'int',
                        'html'        => 'string',
                        'secret'    => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'replies',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Ticket
    #=======================================

    public function delete($id, $params=array())
    {
        if ( ! $id = intval( $id ) ) return false;

        $sql_select = array();

        if ( ! isset( $params['did'] ) ) $sql_select[] = 'did';
        if ( ! isset( $params['uid'] ) ) $sql_select[] = 'uid';
        if ( ! isset( $params['closed'] ) ) $sql_select[] = 'closed';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $t = $this->get_single_by_id( array( 'select' => $sql_select, 'from' => 'tickets' ), $id ) ) return false;
        }

        if ( ! isset( $params['did'] ) ) $params['did'] = $t['did'];
        if ( ! isset( $params['uid'] ) ) $params['uid'] = $t['uid'];
        if ( ! isset( $params['closed'] ) ) $params['closed'] = $t['closed'];

        // decrement department tickets count
        $this->trellis->db->construct( array(
                                                   'update'    => 'departments',
                                                   'set'    => array( 'tickets_total' => array( '-', 1 ) ),
                                                   'where'    => array( 'id', '=', $params['did'] ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        // decrement user tickets count
        $db_array = array( 'tickets_total' => array( '-', 1 ) );

        if ( ! $params['closed'] )
        {
            $db_array['tickets_open'] = array( '-', 1 );
        }

        if ( $params['uid'] )
        {
            $this->trellis->db->construct( array(
                                                       'update'    => 'users',
                                                       'set'    => $db_array,
                                                       'where'    => array( 'id', '=', $params['uid'] ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->next_shutdown();
            $this->trellis->db->execute();
        }

        $this->clear_assignments( $id );

        $this->trellis->db->construct( array(
                                                   'delete'    => 'depart_fields_data',
                                                   'where'    => array( 'tid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->clear_flags( $id );

        // TODO: efficient? too slow? (deleting attachments)
        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'    => 'replies',
                                                   'where'    => array( 'tid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $replies = array();
        if ( $this->trellis->db->get_num_rows() )
        {
            while ( $r = $this->trellis->db->fetch_row() )
            {
                $replies[] = $r['id'];
            }
        }

        if ( ! empty( $replies ) )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'id' ),
                                                       'from'    => 'attachments',
                                                       'where'    => array( array( 'content_type', '=', 'reply' ), array( 'content_id', 'in', $replies, 'and' ) ),
                                                )       );

            $this->trellis->db->execute();

            $attachments = array();
            if ( $this->trellis->db->get_num_rows() )
            {
                while ( $a = $this->trellis->db->fetch_row() )
                {
                    $attachments[] = $a['id'];
                }
            }
        }

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'    => 'attachments',
                                                   'where'    => array( array( 'content_type', '=', 'ticket' ), array( 'content_id', '=', $id, 'and' ) ),
                                            )       );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while ( $a = $this->trellis->db->fetch_row() )
            {
                $attachments[] = $a['id'];
            }
        }

        if ( ! empty( $attachments ) )
        {
            $this->trellis->load_functions('attachments');

            $this->trellis->func->attachments->delete( $attachments );
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'assign_map',
                                                   'where'    => array( 'tid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'flags_map',
                                                   'where'    => array( 'tid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'replies',
                                                   'where'    => array( 'tid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        // TODO: delete reply rate

        $this->trellis->db->construct( array(
                                                   'delete'    => 'tickets_guests',
                                                   'where'    => array( 'id', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'tickets_track',
                                                   'where'    => array( 'tid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'tickets',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Reply
    #=======================================

    public function delete_reply($id, $params=array())
    {
        if ( ! $id = intval( $id ) ) return false;

        $sql_select = array();

        if ( ! isset( $params['tid'] ) ) $sql_select['r'][] = 'tid';
        if ( ! isset( $params['date'] ) ) $sql_select['r'][] = 'date';
        if ( ! isset( $params['last_reply'] ) ) $sql_select['t'][] = 'last_reply';

        if ( ! isset( $params['staff'] ) )  // Only care about secret and last staff reply if we are staff
        {
            if ( ! isset( $params['staff'] ) ) $sql_select['r'][] = 'staff';
            if ( ! isset( $params['secret'] ) ) $sql_select['r'][] = 'secret';
            if ( ! isset( $params['last_reply_staff'] ) ) $sql_select['t'][] = 'last_reply_staff';
        }
        elseif ( $params['staff'] )
        {
            if ( ! isset( $params['secret'] ) ) $sql_select['r'][] = 'secret';
            if ( ! isset( $params['last_reply_staff'] ) ) $sql_select['t'][] = 'last_reply_staff';
        }

        if ( ! empty( $sql_select ) )
        {
            if ( ! $r = $this->get_single( array( 'select' => $sql_select, 'from' => array( 'r' => 'replies' ), 'join' => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ), 'where' => array( array( 'r' => 'id', '=', $id ) ), 'limit' => array( 0, 1 ) ) ) ) return false;
        }

        if ( ! isset( $params['tid'] ) ) $params['tid'] = $r['tid'];
        if ( ! isset( $params['date'] ) ) $params['date'] = $r['date'];
        if ( ! isset( $params['last_reply'] ) ) $params['last_reply'] = $r['last_reply'];
        if ( ! isset( $params['staff'] ) ) $params['staff'] = $r['staff'];

        if ( $params['staff'] )  // " " ^^^
        {
            if ( ! isset( $params['secret'] ) ) $params['secret'] = $r['secret'];
            if ( ! isset( $params['last_reply_staff'] ) ) $params['last_reply_staff'] = $r['last_reply_staff'];
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'replies',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $dr = $this->trellis->db->get_affected_rows();

        if ( ! $params['secret'] )
        {
            $db_array = array( 'replies' => array( '-', 1 ) );

            if ( $params['last_reply_staff'] == $params['date'] )
            {
                if ( $lrs = $this->get_single( array( 'select' => array( 'uid', 'date' ), 'from' => 'replies', 'where' => array( array( 'tid', '=', $params['tid'] ), array( 'staff', '=', 1, 'and' ), array( 'secret', '!=', 1, 'and' ) ), 'order' => array( 'date' => 'desc' ) ) ) )
                {
                    $db_array['last_reply_staff'] = $lrs['date'];
                }
                else
                {
                    $db_array['last_reply_staff'] = 0;
                }
            }

            if ( $params['last_reply'] == $params['date'] )
            {
                if ( $lr = $this->get_single( array( 'select' => array( 'uid', 'date' ), 'from' => 'replies', 'where' => array( array( 'tid', '=', $params['tid'] ), array( 'secret', '!=', 1, 'and' ) ), 'order' => array( 'date' => 'desc' ) ) ) )
                {
                    $db_array['last_reply'] = $lr['date'];
                    $db_array['last_uid'] = $lr['uid'];
                }
                else
                {
                    $t = $this->get_single( array( 'select' => array( 'uid', 'date' ), 'from' => 'tickets', 'where' => array( 'id', '=', $params['tid'] ) ) );

                    $db_array['last_reply'] = $t['date'];
                    $db_array['last_uid'] = $t['uid'];
                }
            }

            $this->trellis->db->construct( array(
                                                       'update'    => 'tickets',
                                                       'set'    => $db_array,
                                                       'where'    => array( 'id', '=', $params['tid'] ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->next_shutdown();
            $this->trellis->db->execute();
        }

        return $dr;
    }

    #=======================================
    # @ Delete Ticket Assignment
    #=======================================

    public function delete_assignment($uid, $tid)
    {
        if ( ! $uid = intval( $uid ) ) return false;
        if ( ! $tid = intval( $tid ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'assign_map',
                                                   'where'    => array( array( 'tid', '=', $tid ), array( 'uid', '=', $uid, 'and' ) ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Ticket Flag
    #=======================================

    public function delete_flag($fid, $tid)
    {
        if ( ! $fid = intval( $fid ) ) return false;
        if ( ! $tid = intval( $tid ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'flags_map',
                                                   'where'    => array( array( 'tid', '=', $tid ), array( 'fid', '=', $fid, 'and' ) ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Clear Ticket Assignments
    #=======================================

    public function clear_assignments($tid)
    {
        if ( ! $tid = intval( $tid ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'assign_map',
                                                   'where'    => array( 'tid', '=', $tid ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Clear Ticket Flags
    #=======================================

    public function clear_flags($tid)
    {
        if ( ! $tid = intval( $tid ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'flags_map',
                                                   'where'    => array( 'tid', '=', $tid ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Move Ticket
    #=======================================

    public function move($ndid, $tid, $odid=0, $data=array(), $clear_assigned=0)
    {
        #* $data includes $tid so why pass the $tid variable when $data has it covered. applies to many other functions in this file.

        if ( ! $tid = intval( $tid ) ) return false;
        if ( ! $ndid = intval( $ndid ) ) return false;

        if ( ! $odid )
        {
            if ( ! $t = $this->get_single_by_id( array( 'select' => array( 'did' ), 'from' => 'tickets' ), $tid ) ) return false;

            $odid = $t['did'];
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'departments',
                                                   'set'    => array( 'tickets_total' => array( '+', 1 ) ),
                                                   'where'    => array( 'id', '=', $ndid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'update'    => 'departments',
                                                   'set'    => array( 'tickets_total' => array( '-', 1 ) ),
                                                   'where'    => array( 'id', '=', $odid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => array( 'did' => $ndid ),
                                                   'where'    => array( 'id', '=', $tid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $result = $this->trellis->db->get_affected_rows();

        if ( $clear_assigned ) $this->clear_assignments( $tid );

        if ( $this->trellis->cache->data['departs'][ $ndid ]['assign_move'] )
        {
            $assign_auto = unserialize( $this->trellis->cache->data['departs'][ $ndid ]['assign_auto'] );

            if ( ! empty( $assign_auto ) )
            {
                foreach( $assign_auto as $auid )
                {
                    $assigned_log[ $auid ] = $this->add_assignment( $auid, $tid, $clear_assigned, 1, 0, $data );
                }

                $this->set_auto_assigned( $assigned_log );
            }
            else
            {
                $this->clear_auto_assigned();
            }
        }

        // Email Notifications
        $this->trellis->load_email();

        $email_tags = array(
                            '{TICKET_ID}'        => $data['mask'],
                            '{UNAME}'            => $data['uname'],
                            '{SUBJECT}'            => $data['subject'],
                            '{OLD_DEPART}'        => $this->trellis->cache->data['departs'][ $odid ]['name'],
                            '{NEW_DEPART}'        => $this->trellis->cache->data['departs'][ $ndid ]['name'],
                            '{PRIORITY}'        => $this->trellis->cache->data['priorities'][ $data['priority'] ]['name'],
                            '{MESSAGE}'            => $this->trellis->prepare_email( $data['message'], 0, 'plain' ),
                            '{MESSAGE_HTML}'    => $this->trellis->prepare_email( $data['message'], 0, 'html' ),
                            '{LINK}'            => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $data['mask'],
                            '%7BLINK%7D'        => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $data['mask'],
                            '{ACTION_USER}'        => $this->trellis->user['name'],
                            );

        // Hide Staff Name
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['g_name'];

        if ( $data['uid'] )
        {
               $this->trellis->email->send_email( array( 'to' => $data['uid'], 'msg' => 'ticket_move', 'replace' => $email_tags, 'type' => 'action', 'type_user' => 'move' ) );
           }
           else
           {
            if ( $data['notify'] ) $this->trellis->email->send_email( array( 'to' => $data['email'], 'name' => $data['uname'], 'msg' => 'ticket_move', 'replace' => $email_tags, 'type' => 'action', 'type_user' => 'move', 'lang' => $data['lang'] ) );
           }

        $email_tags['{TICKET_ID}'] = $tid;
        $email_tags['{LINK}'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;
        $email_tags['%7BLINK%7D'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;

        // Restore Staff Name for Staff Notifications
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['name'];

        $sent = $this->trellis->email->notify_staff( array( 'msg' => 'ticket_move_to', 'replace' => $email_tags, 'type' => 'move_to', 'tid' => $tid, 'did' => $ndid, 'exclude' => $this->trellis->user['id'] ) );

        $sent[ $this->trellis->user['id'] ] = 1;

        $this->trellis->email->notify_staff( array( 'msg' => 'ticket_move_away', 'replace' => $email_tags, 'type' => 'move_away', 'tid' => $tid, 'did' => $odid, 'exclude' => $sent ) );

        return $result;
    }

    #=======================================
    # @ Escalate Ticket
    #=======================================

    public function escalate($tid, $params=array())
    {
        if ( ! $tid = intval( $tid ) ) return false;

        $sql_select = array();

        if ( ! isset( $params['did'] ) ) $sql_select[] = 'did';

        if ( $params['staff'] && ! isset( $params['accepted'] ) ) $sql_select[] = 'accepted'; // Only care about auto-accepting if staff

        if ( ! empty( $sql_select ) )
        {
            if ( ! $t = $this->get_single_by_id( array( 'select' => $sql_select, 'from' => 'tickets' ), $tid ) ) return false;
        }

        if ( ! isset( $params['did'] ) ) $params['did'] = $t['did'];
        if ( $params['staff'] && ! isset( $params['accepted'] ) ) $params['accepted'] = $t['accepted']; // " " ^^^

        if ( $this->trellis->cache->data['departs'][ $params['did'] ]['escalate_depart'] )
        {
            $this->move( $this->trellis->cache->data['departs'][ $params['did'] ]['escalate_depart'], $tid, $params['did'], $params['data'] );

            $this->set_auto_moved( $this->trellis->cache->data['departs'][ $params['did'] ]['escalate_depart'] );
        }
        else
        {
            $this->clear_auto_moved();
        }

        if ( $params['clear_assigned'] ) $this->clear_assignments( $tid );

        $assign_auto = unserialize( $this->trellis->cache->data['departs'][ $params['did'] ]['escalate_assign'] );

        $assigned_log = array();

        if ( ! empty( $assign_auto ) )
        {
            foreach( $assign_auto as $uid )
            {
                $assigned_log[ $uid ] = $this->add_assignment( $uid, $tid, $params['clear_assigned'] , 1, 0, $params['data'] );
            }

            $this->set_auto_assigned( $assigned_log );
        }
        else
        {
            $this->clear_auto_assigned();
        }

        $db_array = array( 'escalated' => 1 );

        if ( $params['staff'] && ! $params['accepted'] ) // " " ^^^
        {
            $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][2];
            $db_array['accepted'] = 1;
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => $db_array,
                                                   'where'    => array( 'id', '=', $tid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $result = $this->trellis->db->get_affected_rows();

        // Email Notifications
        $this->trellis->load_email();

        $email_tags = array(
                            '{TICKET_ID}'        => $params['data']['mask'],
                            '{UNAME}'            => $params['data']['uname'],
                            '{SUBJECT}'            => $params['data']['subject'],
                            '{DEPARTMENT}'        => $this->trellis->cache->data['departs'][ $params['data']['did'] ]['name'],
                            '{PRIORITY}'        => $this->trellis->cache->data['priorities'][ $params['data']['priority'] ]['name'],
                            '{MESSAGE}'            => $this->trellis->prepare_email( $params['data']['message'], 0, 'plain' ),
                            '{MESSAGE_HTML}'    => $this->trellis->prepare_email( $params['data']['message'], 0, 'html' ),
                            '{LINK}'            => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $params['data']['mask'],
                            '%7BLINK%7D'        => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $params['data']['mask'],
                            '{ACTION_USER}'        => $this->trellis->user['name'],
                            );

        // Hide Staff Name
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['g_name'];

        if ( $params['data']['uid'] )
        {
               $this->trellis->email->send_email( array( 'to' => $params['data']['uid'], 'msg' => 'ticket_escalate', 'replace' => $email_tags, 'type' => 'action', 'type_user' => 'escalate' ) );
           }
           else
           {
            if ( $params['data']['notify'] ) $this->trellis->email->send_email( array( 'to' => $params['data']['email'], 'name' => $params['data']['uname'], 'msg' => 'ticket_escalate', 'replace' => $email_tags, 'type' => 'action', 'type_user' => 'escalate', 'lang' => $params['data']['lang'] ) );
           }

        $email_tags['{TICKET_ID}'] = $tid;
        $email_tags['{LINK}'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;
        $email_tags['%7BLINK%7D'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;

        // Restore Staff Name for Staff Notifications
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['name'];

        $sent = $this->trellis->email->notify_staff( array( 'msg' => 'ticket_escalate', 'replace' => $email_tags, 'type' => 'escalate', 'tid' => $tid, 'did' => $params['data']['did'], 'exclude' => $this->trellis->user['id'] ) );

        return $result;
    }

    #=======================================
    # @ Hold Ticket
    #=======================================

    public function hold($tid, $params=array())
    {
        if ( ! $tid = intval( $tid ) ) return false;

        $sql_select = array();

        if ( ! isset( $params['accepted'] ) ) $sql_select[] = 'accepted';
        if ( ! isset( $params['aua'] ) ) $sql_select[] = 'aua';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $t = $this->get_single_by_id( array( 'select' => $sql_select, 'from' => 'tickets' ), $tid ) ) return false;
        }

        $db_array = array( 'status' => $this->trellis->cache->data['misc']['default_statuses'][4], 'onhold' => 1 );

        if ( ! isset( $params['accepted'] ) ) $params['accepted'] = $t['accepted'];
        if ( ! isset( $params['aua'] ) ) $params['aua'] = $t['aua'];

        if ( ! $params['accepted'] )
        {
            $db_array['accepted'] = 1;
        }

        if ( $params['aua'] )
        {
            #$db_array['aua'] = 0; # CHECK: save aua status so we can go back later
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => $db_array,
                                                   'where'    => array( 'id', '=', $tid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $result = $this->trellis->db->get_affected_rows();

        // Email Notifications
        $this->trellis->load_email();

        $email_tags = array(
                            '{TICKET_ID}'        => $params['data']['mask'],
                            '{UNAME}'            => $params['data']['uname'],
                            '{SUBJECT}'            => $params['data']['subject'],
                            '{DEPARTMENT}'        => $this->trellis->cache->data['departs'][ $params['data']['did'] ]['name'],
                            '{PRIORITY}'        => $this->trellis->cache->data['priorities'][ $params['data']['priority'] ]['name'],
                            '{MESSAGE}'            => $this->trellis->prepare_email( $params['data']['message'], 0, 'plain' ),
                            '{MESSAGE_HTML}'    => $this->trellis->prepare_email( $params['data']['message'], 0, 'html' ),
                            '{LINK}'            => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $params['data']['mask'],
                            '%7BLINK%7D'        => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $params['data']['mask'],
                            '{ACTION_USER}'        => $this->trellis->user['name'],
                            );

        // Hide Staff Name
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['g_name'];

        if ( $params['data']['uid'] )
        {
               $this->trellis->email->send_email( array( 'to' => $params['data']['uid'], 'msg' => 'ticket_hold', 'replace' => $email_tags, 'type' => 'action', 'type_user' => 'hold' ) );
           }
           else
           {
            if ( $params['data']['notify'] ) $this->trellis->email->send_email( array( 'to' => $params['data']['email'], 'name' => $params['data']['uname'], 'msg' => 'ticket_hold', 'replace' => $email_tags, 'type' => 'action', 'type_user' => 'hold', 'lang' => $params['data']['lang'] ) );
           }

        $email_tags['{TICKET_ID}'] = $tid;
        $email_tags['{LINK}'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;
        $email_tags['%7BLINK%7D'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;

        // Restore Staff Name for Staff Notifications
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['name'];

        $sent = $this->trellis->email->notify_staff( array( 'msg' => 'ticket_hold', 'replace' => $email_tags, 'type' => 'hold', 'tid' => $tid, 'did' => $params['data']['did'], 'exclude' => $this->trellis->user['id'] ) );

        return $result;
    }

    #=======================================
    # @ Remove Hold Ticket
    #=======================================

    public function rmvhold($tid, $params=array())
    {
        if ( ! $tid = intval( $tid ) ) return false;

        if ( ! isset( $params['aua'] ) )
        {
            echo 'test';
            if ( ! $t = $this->get_single_by_id( array( 'select' => array( 'aua' ), 'from' => 'tickets' ), $tid ) ) return false;

            $params['aua'] = $t['aua'];
        }

        $db_array = array( 'onhold' => 0 );

        if ( $params['aua'] )
        {
            $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][5];
        }
        else
        {
            $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][2];
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => $db_array,
                                                   'where'    => array( 'id', '=', $tid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Close Ticket
    #=======================================

    public function close($tid, $params=array())
    {
        if ( ! $tid = intval( $tid ) ) return false;

        if ( ! isset( $params['uid'] ) )
        {
            if ( ! $t = $this->get_single_by_id( array( 'select' => array( 'uid' ), 'from' => 'tickets' ), $tid ) ) return false;

            $params['uid'] = $t['uid'];
        }

        if ( $params['uid'] )
        {
            $this->trellis->db->construct( array(
                                                       'update'    => 'users',
                                                       'set'    => array( 'tickets_open' => array( '-', 1 ) ),
                                                       'where'    => array( 'id', '=', $params['uid'] ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->next_shutdown();
            $this->trellis->db->execute();
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => array( 'status' => $this->trellis->cache->data['misc']['default_statuses'][6], 'close_uid' => $this->trellis->user['id'], 'close_date' => time(), 'closed' => 1, 'allow_reopen' => $params['allow_reopen'] ),
                                                   'where'    => array( 'id', '=', $tid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $result = $this->trellis->db->get_affected_rows();

        $assigned = $this->get_assignments_by_uid( $tid );

        $this->clear_assignments( $tid );

        if ( $params['uid'] )
        {
            // decrement user tickets count
            $this->trellis->db->construct( array(
                'update'    => 'users',
                'set'        => array( 'tickets_open' => array( '-', 1 ) ),
                'where'        => array( 'id', '=', $params['uid'] ),
                'limit'        => array( 1 ),
            ) );
        }

        // Email Notifications
        $this->trellis->load_email();

        $email_tags = array(
                            '{TICKET_ID}'        => $params['data']['mask'],
                            '{UNAME}'            => $params['data']['uname'],
                            '{SUBJECT}'            => $params['data']['subject'],
                            '{DEPARTMENT}'        => $this->trellis->cache->data['departs'][ $params['data']['did'] ]['name'],
                            '{PRIORITY}'        => $this->trellis->cache->data['priorities'][ $params['data']['priority'] ]['name'],
                            '{MESSAGE}'            => $this->trellis->prepare_email( $params['data']['message'], 0, 'plain' ),
                            '{MESSAGE_HTML}'    => $this->trellis->prepare_email( $params['data']['message'], 0, 'html' ),
                            '{LINK}'            => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $params['data']['mask'],
                            '%7BLINK%7D'        => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $params['data']['mask'],
                            '{ACTION_USER}'        => $this->trellis->user['name'],
                            );

        // Hide Staff Name
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['g_name'];

        if ( $params['data']['uid'] )
        {
               $this->trellis->email->send_email( array( 'to' => $params['data']['uid'], 'msg' => 'ticket_close', 'replace' => $email_tags, 'type' => 'action', 'type_user' => 'close' ) );
           }
           else
           {
            if ( $params['data']['notify'] ) $this->trellis->email->send_email( array( 'to' => $params['data']['email'], 'name' => $params['data']['uname'], 'msg' => 'ticket_close', 'replace' => $email_tags, 'type' => 'action', 'type_user' => 'close', 'lang' => $params['data']['lang'] ) );
           }

        $email_tags['{TICKET_ID}'] = $tid;
        $email_tags['{LINK}'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;
        $email_tags['%7BLINK%7D'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;

        // Restore Staff Name for Staff Notifications
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['name'];

        $sent = $this->trellis->email->notify_staff( array( 'msg' => 'ticket_close', 'replace' => $email_tags, 'type' => 'close', 'tid' => $tid, 'did' => $params['data']['did'], 'assigned' => $assigned, 'exclude' => $this->trellis->user['id'] ) );

        return $result;
    }

    #=======================================
    # @ Reopen Ticket
    #=======================================

    public function reopen($tid, $params=array())
    {
        if ( ! $tid = intval( $tid ) ) return false;

        $sql_select = array();

        if ( ! isset( $params['uid'] ) ) $sql_select[] = 'uid';
        if ( ! isset( $params['did'] ) ) $sql_select[] = 'did';

        if ( $params['staff'] ) // Only care about previous status if staff
        {
            if ( ! isset( $params['aua'] ) ) $sql_select[] = 'aua';
            if ( ! isset( $params['accepted'] ) ) $sql_select[] = 'accepted';
        }

        if ( ! empty( $sql_select ) )
        {
            if ( ! $t = $this->get_single_by_id( array( 'select' => $sql_select, 'from' => 'tickets' ), $tid ) ) return false;
        }

        $db_array = array( 'close_uid' => 0, 'close_date' => 0, 'closed' => 0 );

        if ( ! isset( $params['uid'] ) ) $params['uid'] = $t['uid'];
        if ( ! isset( $params['did'] ) ) $params['did'] = $t['did'];

        if ( $params['staff'] ) // " " ^^^
        {
            if ( ! isset( $params['aua'] ) ) $params['uid'] = $t['aua'];
            if ( ! isset( $params['accepted'] ) ) $params['accepted'] = $t['accepted'];

            if ( $params['aua'] )
            {
                $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][5];
            }
            else
            {
                $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][2];
            }

            if ( ! $params['accepted'] )
            {
                $db_array['accepted'] = 1;
            }
        }
        else
        {
            $db_array['accepted'] = 0;
            $db_array['aua'] = 0;
            $db_array['escalated'] = 0;
            $db_array['onhold'] = 0;
            $db_array['status'] = $this->trellis->cache->data['misc']['default_statuses'][1];
        }

        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => $db_array,
                                                   'where'    => array( 'id', '=', $tid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $result = $this->trellis->db->get_affected_rows();

        $assign_auto = unserialize( $this->trellis->cache->data['departs'][ $params['did'] ]['assign_auto'] );

        $assigned = array();

        if ( ! empty( $assign_auto ) )
        {
            foreach( $assign_auto as $uid )
            {
                $this->add_assignment( $uid, $tid, 1, 0, 1 );

                $assigned[ $uid ] = 1;
            }
        }

        if ( $params['uid'] )
        {
            // increment user tickets count
            $this->trellis->db->construct( array(
                'update'    => 'users',
                'set'        => array( 'tickets_open' => array( '+', 1 ) ),
                'where'        => array( 'id', '=', $params['uid'] ),
                'limit'        => array( 1 ),
            ) );
        }

        // Email Notifications
        $this->trellis->load_email();

        $email_tags = array(
                            '{TICKET_ID}'        => $params['data']['mask'],
                            '{UNAME}'            => $params['data']['uname'],
                            '{SUBJECT}'            => $params['data']['subject'],
                            '{DEPARTMENT}'        => $this->trellis->cache->data['departs'][ $params['data']['did'] ]['name'],
                            '{PRIORITY}'        => $this->trellis->cache->data['priorities'][ $params['data']['priority'] ]['name'],
                            '{MESSAGE}'            => $this->trellis->prepare_email( $params['data']['message'], 0, 'plain' ),
                            '{MESSAGE_HTML}'    => $this->trellis->prepare_email( $params['data']['message'], 0, 'html' ),
                            '{LINK}'            => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $params['data']['mask'],
                            '%7BLINK%7D'        => $this->trellis->config['hd_url'] .'/index.php?page=tickets&act=view&id='. $params['data']['mask'],
                            '{ACTION_USER}'        => $this->trellis->user['name'],
                            );

        // Hide Staff Name
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['g_name'];

        if ( $params['data']['uid'] )
        {
               $this->trellis->email->send_email( array( 'to' => $params['data']['uid'], 'msg' => 'ticket_reopen', 'replace' => $email_tags, 'type' => 'ticket', 'type_user' => 'reopen' ) );
           }
           else
           {
            if ( $params['data']['notify'] ) $this->trellis->email->send_email( array( 'to' => $params['data']['email'], 'name' => $params['data']['uname'], 'msg' => 'ticket_reopen', 'replace' => $email_tags, 'type' => 'ticket', 'type_user' => 'reopen', 'lang' => $params['data']['lang'] ) );
           }

        $email_tags['{TICKET_ID}'] = $tid;
        $email_tags['{LINK}'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;
        $email_tags['%7BLINK%7D'] = $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=tickets&act=view&id='. $tid;

        // Restore Staff Name for Staff Notifications
        if ( $this->trellis->user['g_acp_access'] && $this->trellis->user['g_hide_names'] ) $email_tags['{ACTION_USER}'] = $this->trellis->user['name'];

        $sent = $this->trellis->email->notify_staff( array( 'msg' => 'ticket_reopen', 'replace' => $email_tags, 'type' => 'reopen', 'tid' => $tid, 'did' => $params['data']['did'], 'exclude' => $this->trellis->user['id'] ) );

        return $result;
    }

    #=======================================
    # @ Remove Escalated Status
    #=======================================

    public function rmvescalate($tid)
    {
        $this->trellis->db->construct( array(
                                                   'update'    => 'tickets',
                                                   'set'    => array( 'escalated' => 0 ),
                                                   'where'    => array( 'id', '=', $tid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Track Ticket
    #=======================================

    public function track($id, $last_date=null)
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( ! isset( $last_date ) )
        {
            $this->trellis->db->construct( array(
                'select'    => array( 'date' ),
                'from'        => 'tickets_track',
                'where'        => array( array( 'tid', '=', $id ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ),
                'limit'        => array( 0, 1 ),
            ) );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() )
            {
                $t = $this->trellis->db->fetch_row();

                $last_date = $t['date'];
            }
        }

        if ( isset( $last_date ) )
        {
            $this->trellis->db->construct( array(
                'update'    => 'tickets_track',
                'set'        => array( 'date' => time() ),
                'where'        => array( array( 'tid', '=', $id ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ),
                'limit'        => array( 1 ),
            ) );
        }
        else
        {
            $this->trellis->db->construct( array(
                'insert'    => 'tickets_track',
                'set'        => array( 'tid' => $id, 'uid' => $this->trellis->user['id'], 'date' => time() ),
            ) );
        }

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Track All Tickets
    #=======================================

    public function track_all()
    {
        if ( ! is_array( $this->trellis->user['g_acp_depart_perm'] ) ) $this->trellis->user['g_acp_depart_perm'] = unserialize( $this->trellis->user['g_acp_depart_perm'] );

        if ( $this->trellis->user['id'] != 1 )
        {
            $perms = array();

            if( is_array( $this->trellis->user['g_acp_depart_perm'] ) )
            {
                foreach( $this->trellis->user['g_acp_depart_perm'] as $did => $dperm )
                {
                    if ( $dperm['v'] ) $perms[] = $did;
                }
            }

            if ( empty( $perms ) ) $perms[] = 0;

            $sql_where = array( array( array( 't' => 'did' ), 'in', $perms ), array( array( 't' => 'uid' ), '=', $this->trellis->user['id'], 'or' ) );
        }

        $tickets = $this->trellis->db->get( array(
            'select'    => array( 't' => array( 'id' ), 'tt' => array( array( 'date' => 'track_date' ) ) ),
            'from'        => array( 't' => 'tickets' ),
            'join'        => array( array( 'from' => array( 'tt' => 'tickets_track' ), 'where' => array( 'tt' => 'tid', '=', 't' => 'id' ) ) ),
            'where'        => $sql_where,
        ), 'id' );

        if ( ! $tickets ) return true;

        $this->trellis->db->construct( array(
                'update'    => 'tickets_track',
                'set'        => array( 'date' => time() ),
                'where'        => array( 'uid', '=', $this->trellis->user['id'] ),
            ) );

        $this->trellis->db->execute();

        foreach ( $tickets as &$t )
        {
            if ( ! isset( $t['track_date'] ) )
            {
                $this->trellis->db->construct( array(
                    'insert'    => 'tickets_track',
                    'set'        => array( 'tid' => $t['id'], 'uid' => $this->trellis->user['id'], 'date' => time() ),
                ) );

                $this->trellis->db->execute();
            }
        }

        return true;
    }

    #=======================================
    # @ Untrack Ticket
    #=======================================

    public function untrack($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
            'delete'    => 'tickets_track',
            'where'        => array( array( 'tid', '=', $id ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ),
            'limit'        => array( 1 ),
        ) );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Untrack Reply
    #=======================================

    public function untrack_reply($rid, $tid=0, $date=null)
    {
        if ( ! $rid = intval( $rid ) ) return false;

        $sql_select = array();

        if ( ! $tid ) $sql_select[] = 'tid';
        if ( ! $date ) $sql_select[] = 'date';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $r = $this->get_single( array( 'select' => $sql_select, 'from' => 'replies', 'where' => array( 'id', '=', $rid ) ) ) ) return false;
        }

        if ( ! $tid ) $tid = $r['tid'];
        if ( ! $date ) $date = $r['date'];

        $this->trellis->db->construct( array(
            'update'    => 'tickets_track',
            'set'        => array( 'date' => intval( $date - 1 ) ), // 1 second before
            'where'        => array( array( 'tid', '=', $tid ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ),
            'limit'        => array( 1 ),
        ) );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Set Auto Assigned
    #=======================================

    public function set_auto_assigned($assigned)
    {
        if ( ! is_array( $assigned ) ) return false;

        $this->auto_assigned = $assigned;
    }

    #=======================================
    # @ Clear Auto Assigned
    #=======================================

    public function clear_auto_assigned()
    {
        $this->auto_assigned = array();
    }

    #=======================================
    # @ Get Auto Assigned
    #=======================================

    public function get_auto_assigned()
    {
        if ( empty( $this->auto_assigned ) ) return false;

        return $this->auto_assigned;
    }

    #=======================================
    # @ Set Auto Moved
    #=======================================

    public function set_auto_moved($moved)
    {
        if ( ! intval( $moved ) ) return false;

        $this->auto_moved = $moved;
    }

    #=======================================
    # @ Clear Auto Moved
    #=======================================

    public function clear_auto_moved()
    {
        $this->auto_moved = 0;
    }

    #=======================================
    # @ Get Auto Moved
    #=======================================

    public function get_auto_moved()
    {
        if ( ! $this->auto_moved ) return false;

        return $this->auto_moved;
    }

    #=======================================
    # @ Generate Mask
    #=======================================

    public function generate_mask($tid, $sample=null)
    {
        if ( ! $tid = intval( $tid ) ) return false;

        $mask = $this->trellis->cache->data['settings']['ticket']['mask'];

        if ( $sample ) $mask = $sample;

        $mask = preg_replace_callback( '/\%A/', array( &$this, 'rand_upper' ), $mask );
        $mask = preg_replace_callback( '/\%a/', array( &$this, 'rand_lower' ), $mask );
        $mask = preg_replace_callback( '/\%n/', array( &$this, 'rand_num' ), $mask );
        $mask = str_replace( '%i', $tid, $mask, $count );

        if ( ! $count && ! $sample )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'id' ),
                                                       'from'    => 'tickets',
                                                       'where'    => array( 'mask', '=', $mask ),
                                                       'limit'    => array( 0, 1 ),
                                                )       );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() ) return $this->generate_mask( $tid );
        }

        return $mask;
    }

    #=======================================
    # @ Random Uppercase Letter
    #=======================================

    private function rand_upper()
    {
        return chr( 65 + mt_rand( 0, 25 ) );
    }

    #=======================================
    # @ Random Lowercase Letter
    #=======================================

    private function rand_lower()
    {
        return chr( 97 + mt_rand( 0, 25 ) );
    }

    #=======================================
    # @ Random Number
    #=======================================

    private function rand_num()
    {
        return mt_rand( 0, 9 );
    }

}

?>