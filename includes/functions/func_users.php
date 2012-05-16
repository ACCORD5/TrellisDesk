<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_users {

    private $default_columns_tm = array( 'id' => '6%', 'subject' => '31%', 'priority' => '14%', 'department' => '19%', 'reply' => '17%', 'status' => '13%' );

    #=======================================
    # @ Get User
    #=======================================

    public function get($input)
    {
        if ( ! is_array( $input['select'] ) ) return false;

        // Security
        if ( $input['bypass_security'] )
        {
            if ( in_array( 'password', $input['select'] ) ) return false;
            if ( in_array( 'pass_salt', $input['select'] ) ) return false;
            if ( in_array( 'login_key', $input['select'] ) ) return false;
        }

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'users',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $u = $this->trellis->db->fetch_row() )
        {
            $return[ $u['id'] ] = $u;
        }

        return $return;
    }

    #=======================================
    # @ Get Single User
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'users',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single User By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Get Single User By Name
    #=======================================

    public function get_single_by_name($select, $name)
    {
        return $this->get_single( $select, array( 'name|lower', '=', strtolower( $name ) ) );
    }

    #=======================================
    # @ Get Single User By Email
    #=======================================

    public function get_single_by_email($select, $email)
    {
        return $this->get_single( $select, array( 'email|lower', '=', strtolower( $email ) ) );
    }

    #=======================================
    # @ Get User
    #=======================================

    public function get_staff($input)
    {
        if ( ! is_array( $input['select'] ) ) return false;

        // Security
        if ( $input['bypass_security'] )
        {
            if ( in_array( 'password', $input['select'] ) ) return false;
            if ( in_array( 'pass_salt', $input['select'] ) ) return false;
            if ( in_array( 'login_key', $input['select'] ) ) return false;
        }

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'users_staff',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $u = $this->trellis->db->fetch_row() )
        {
            $return[ $u['uid'] ] = $u;
        }

        return $return;
    }

    #=======================================
    # @ Get Single User Staff
    #=======================================

    public function get_single_staff($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'users_staff',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single User By ID Staff
    #=======================================

    public function get_single_by_id_staff($select, $id)
    {
        return $this->get_single_staff( $select, array( 'uid', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add User
    #=======================================

    public function add($data, $params=array())
    {
        $pwsalt = '';
        $rksalt = '';

        while( strlen( $pwsalt ) < 16 ) $pwsalt .= chr( rand( 32, 126 ) );
        while( strlen( $rksalt ) < 8 ) $rksalt .= chr( rand( 32, 126 ) );

        $rksalt .= uniqid( rand(), true );

        $pwhash = hash( 'whirlpool', $pwsalt . $data['password'] . $this->trellis->config['pass_key'] );
        $rkhash = md5( $rksalt . $this->trellis->config['rss_key'] );

        $db_array = array(
                          'pass_hash'        => $pwhash,
                          'pass_salt'        => $pwsalt,
                          'rss_key'            => $rkhash,
                          );

        unset( $data['password'] );

        $result = array_merge( $data, $db_array );

        if ( ! isset( $params['staff'] ) && $this->trellis->cache->data['groups'][ $data['ugroup'] ]['g_acp_acces'] ) $params['staff'] = 1;

        if ( ! isset( $params['staff'] ) )
        {
            if ( $data['ugroup_sub'] )
            {
                $data['ugroup_sub'] = unserialize( $data['ugroup_sub'] );

                if ( ! empty( $data['ugroup_sub'] ) )
                {
                    foreach ( $data['ugroup_sub'] as $gid )
                    {
                        if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $params['staff'] = 1;
                    }
                }
            }
        }

        $fields = array(
                        'name'                    => 'string',
                        'email'                    => 'string',
                        'pass_hash'                => 'string',
                        'pass_salt'                => 'string',
                        'ugroup'                => 'int',
                        'ugroup_sub'            => 'string',
                        'ugroup_sub_acp'        => 'int',
                        'title'                    => 'string',
                        'joined'                => 'string',
                        'signature'                => 'string',
                        'sig_auto'                => 'string',
                        'rss_key'                => 'string',
                        'lang'                    => 'int',
                        'skin'                    => 'int',
                        'time_zone'                => 'string',
                        'time_dst'                => 'int',
                        'rte_enable'            => 'int',
                        'email_enable'            => 'int',
                        'email_ticket'            => 'int',
                        'email_action'            => 'int',
                        'email_news'            => 'int',
                        'email_type'            => 'int',
                        'val_email'                => 'int',
                        'val_admin'                => 'int',
                        'ipadd'                    => 'string',
                        );

        if ( ! $params['bypass_val'] )
        {
            $result['ugroup'] = 1;

            if ( $this->trellis->cache->data['settings']['security']['validation_email'] )
            {
                $result['ugroup'] = 3;
                $result['val_email'] = 0;
            }

            if( $this->trellis->cache->data['settings']['security']['validation_admin'] )
            {
                $result['ugroup'] = 3;
                $result['val_admin'] = 0;
            }
        }

        $this->trellis->db->construct( array(
                                                   'insert'    => 'users',
                                                   'set'    => $this->trellis->process_data( $fields, $result ),
                                            )       );

        $this->trellis->db->execute();

        $uid = $this->trellis->db->get_insert_id();

        if ( $params['staff'] )
        {
            $fields = array(
                            'uid'                        => 'int',
                            'email_staff_enable'        => 'int',
                            'email_staff_user_approve'    => 'int',
                            'email_staff_ticket'        => 'int',
                            'email_staff_reply'            => 'int',
                            'email_staff_assign'        => 'int',
                            'email_staff_escalate'        => 'int',
                            'email_staff_hold'            => 'int',
                            'email_staff_move_to'        => 'int',
                            'email_staff_move_away'        => 'int',
                            'email_staff_close'            => 'int',
                            'email_staff_reopen'        => 'int',
                            'esn_unassigned'            => 'int',
                            'esn_assigned'                => 'int',
                            'esn_assigned_to_me'        => 'int',
                            'columns_tm'                => 'string',
                            'sort_tm'                    => 'string',
                            'order_tm'                    => 'int',
                            'auto_assign'                => 'int',
                            );

            $data['uid'] = $uid;
            $data['columns_tm'] = serialize( $this->default_columns_tm );
            $data['sort_tm'] = 'reply';

            $this->trellis->db->construct( array(
                                                       'insert'    => 'users_staff',
                                                       'set'    => $this->trellis->process_data( $fields, $data ),
                                                )       );

            $this->trellis->db->execute();
        }

        if ( ! $params['bypass_val'] )
        {
            // Email Validation
            if ( $this->trellis->cache->data['settings']['security']['validation_email'] )
            {
                $val_code = md5( $data['email'] . uniqid( rand(), true ) );

                $db_array = array(
                                  'id'        => $val_code,
                                  'uid'        => $uid,
                                  'email'    => $data['email'],
                                  'date'    => time(),
                                  'type'    => 1,
                                  );

                $this->trellis->db->construct( array(
                                                     'insert'    => 'validation',
                                                     'set'        => $db_array,
                                              )      );

                $this->trellis->db->execute();

                $this->trellis->load_email();

                $email_tags = array(
                                    '{LINK}' => $this->trellis->config['hd_url'] .'/index.php?page=register&act=dovalidate&key='. $val_code,
                                    '%7BLINK%7D' => $this->trellis->config['hd_url'] .'/index.php?page=register&act=dovalidate&key='. $val_code,
                                    );

                // Do we require admin validation too?
                if ( $this->trellis->cache->data['settings']['security']['validation_admin'] )
                {
                    $this->trellis->email->send_email( array( 'to' => $uid, 'msg' => 'user_new_val_both', 'replace' => $email_tags, 'override' => 1 ) );
                }
                else
                {
                    $this->trellis->email->send_email( array( 'to' => $uid, 'msg' => 'user_new_val_email', 'replace' => $email_tags, 'override' => 1 ) );
                }
            }
            elseif ( $this->trellis->cache->data['settings']['security']['validation_admin'] )
            {
                $this->trellis->email->send_email( array( 'to' => $uid, 'msg' => 'user_new_val_admin', 'override' => 1 ) );
            }

            // Notify Staff of Required Approval
            if ( $this->trellis->cache->data['settings']['security']['validation_admin'] && $this->trellis->cache->data['settings']['esnotify']['enable'] && $this->trellis->cache->data['settings']['esnotify']['user_approve'] )
            {
                $staff = $this->trellis->db->get( array(
                                                        'select'    => array(
                                                                             'u'    => array( 'id', 'name', 'email', 'lang', 'email_type' ),
                                                                             'us'    => array( 'email_staff_enable', 'email_staff_user_approve' ),
                                                                             'g'    => array( 'g_acp_perm' ),
                                                                             ),
                                                        'from'    => array( 'u' => 'users' ),
                                                        'join'    => array( array( 'from' => array( 'us' => 'users_staff' ), 'where' => array( 'u' => 'id', '=', 'us' => 'uid' ) ), array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'u' => 'ugroup', '=', 'g' => 'g_id' ) ) ),
                                                        'where'    => array( array( array( 'g' => 'g_acp_access' ), '=', 1 ), array( array( 'us' => 'email_staff_enable' ), '=', 1, 'and' ), array( array( 'us' => 'email_staff_user_approve' ), '=', 1, 'and' ) ),
                                                 ), 'id' );

                #* Check ACP permissions and make sure staff can approve users. FYI: Email preference check done in SQL query.

                if ( $staff )
                {
                    $email_tags = array(
                                        '{UNAME}'    => $data['name'],
                                        '{UEMAIL}'    => $data['email'],
                                        '{LINK}'    => $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=users&act=approve',
                                        '%7BLINK%7D' => $this->trellis->config['hd_url'] .'/admin.php?section=manage&page=users&act=approve',
                                        );

                    foreach ( $staff as $s )
                    {
                        $this->trellis->email->send_email( array( 'to' => $s['id'], 'name' => $s['name'], 'email' => $s['email'], 'msg' => 'user_new_val_admin_staff', 'replace' => $email_tags, 'lang' => $s['lang'],  'override' => 1, 'format' => $s['email_type'] ) );
                    }
                }
            }
        }

        #* update new group count

        return $uid;
    }

    #=======================================
    # @ Edit User
    #=======================================

    public function edit($data, $id, $params=array())
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( ! isset( $params['staff'] ) && $this->trellis->cache->data['groups'][ $data['ugroup'] ]['g_acp_acces'] ) $params['staff'] = 1;

        if ( ! isset( $params['staff'] ) )
        {
            if ( $data['ugroup_sub'] )
            {
                $data['ugroup_sub'] = unserialize( $data['ugroup_sub'] );

                if ( ! empty( $data['ugroup_sub'] ) )
                {
                    foreach ( $data['ugroup_sub'] as $gid )
                    {
                        if ( $this->trellis->cache->data['groups'][ $gid ]['g_acp_access'] ) $params['staff'] = 1;
                    }
                }
            }
        }

        if ( $params['staff'] && ! isset( $params['oldstaff'] ) )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'uid' ),
                                                       'from'    => 'users_staff',
                                                       'where'    => array( 'uid', '=', $id ),
                                                       'limit'    => array( 0, 1 ),
                                                )       );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() ) $params['oldstaff'] = 1;
        }

        $fields = array(
                        'name'                    => 'string',
                        'email'                    => 'string',
                        'ugroup'                => 'int',
                        'ugroup_sub'            => 'string',
                        'ugroup_sub_acp'        => 'int',
                        'title'                    => 'string',
                        'joined'                => 'string',
                        'signature'                => 'string',
                        'sig_auto'                => 'string',
                        'rss_key'                => 'string',
                        'lang'                    => 'int',
                        'skin'                    => 'int',
                        'time_zone'                => 'string',
                        'time_dst'                => 'int',
                        'rte_enable'            => 'int',
                        'email_enable'            => 'int',
                        'email_ticket'            => 'int',
                        'email_action'            => 'int',
                        'email_news'            => 'int',
                        'email_type'            => 'int',
                        'val_email'                => 'int',
                        'val_admin'                => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'users',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $ar = $this->trellis->db->get_affected_rows();

        if ( $params['staff'] )
        {
            $fields = array(
                            'uid'                        => 'int',
                            'email_staff_enable'        => 'int',
                            'email_staff_user_approve'    => 'int',
                            'email_staff_ticket'        => 'int',
                            'email_staff_reply'            => 'int',
                            'email_staff_assign'        => 'int',
                            'email_staff_escalate'        => 'int',
                            'email_staff_hold'            => 'int',
                            'email_staff_move_to'        => 'int',
                            'email_staff_move_away'        => 'int',
                            'email_staff_close'            => 'int',
                            'email_staff_reopen'        => 'int',
                            'esn_unassigned'            => 'int',
                            'esn_assigned'                => 'int',
                            'esn_assigned_to_me'        => 'int',
                            'columns_tm'                => 'string',
                            'sort_tm'                    => 'string',
                            'order_tm'                    => 'int',
                            'auto_assign'                => 'int',
                            );

            if ( $params['oldstaff'] )
            {
                $this->trellis->db->construct( array(
                                                           'update'    => 'users_staff',
                                                           'set'    => $this->trellis->process_data( $fields, $data ),
                                                           'where'    => array( 'uid', '=', $id ),
                                                           'limit'    => array( 1 ),
                                                    )       );
            }
            else
            {
                $data['uid'] = $id;
                $data['columns_tm'] = serialize( $this->default_columns_tm );
                $data['sort_tm'] = 'reply';

                $this->trellis->db->construct( array(
                                                           'insert'    => 'users_staff',
                                                           'set'    => $this->trellis->process_data( $fields, $data ),
                                                    )       );
            }

            $this->trellis->db->execute();
        }

        return $ar;
    }

    #=======================================
    # @ Delete User
    #=======================================

    public function delete($id, $delete=array())
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( $delete['tickets'] )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'id' ),
                                                       'from'    => 'tickets',
                                                       'where'    => array( array( 'uid', '=', $id ), array( 'replies', '>', 0, 'and' ) ),
                                                )       );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() )
            {
                $tickets = array();

                while( $t = $this->trellis->db->fetch_row() )
                {
                    $tickets[] = $t['id'];
                }

                $this->trellis->db->construct( array(
                                                           'delete'    => 'replies',
                                                           'where'    => array( 'tid', 'in', $tickets ),
                                                    )       );

                $this->trellis->db->execute();
            }

            $this->trellis->db->construct( array(
                                                       'delete'    => 'tickets',
                                                       'where'    => array( 'uid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();

            $this->trellis->db->construct( array(
                                                       'delete'    => 'depart_fields_data',
                                                       'where'    => array( 'uid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        if ( $delete['replies'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'replies',
                                                       'where'    => array( 'uid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        if ( $delete['comments'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'article_comments',
                                                       'where'    => array( 'uid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();

            $this->trellis->db->construct( array(
                                                       'delete'    => 'news_comments',
                                                       'where'    => array( 'uid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        if ( $delete['ratings'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'article_rate',
                                                       'where'    => array( 'uid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();

            $this->trellis->db->construct( array(
                                                       'delete'    => 'reply_rate',
                                                       'where'    => array( 'uid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'profile_fields_data',
                                                   'where'    => array( 'uid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'users',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'users_staff',
                                                   'where'    => array( 'uid', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Check Name
    #=======================================

    public function check_name($name)
    {
        return $this->get_single_by_name( array( 'id' ), $name );
    }

    #=======================================
    # @ Check Email
    #=======================================

    public function check_email($email)
    {
        return $this->get_single_by_email( array( 'id' ), $email );
    }

    #=======================================
    # @ Check Password
    #=======================================

    public function check_password($passwd, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $u = $this->get_single_by_id( array( 'pass_hash', 'pass_salt' ), $id );

        if ( hash( 'whirlpool', $u['pass_salt'] . $passwd . $this->trellis->config['pass_key'] ) == $u['pass_hash'] )
        {
            return true;
        }

        return false;
    }

    #=======================================
    # @ Change Password
    #=======================================

    public function change_password($new_passwd, $id)
    {
        $pwsalt = '';
        $rksalt = '';

        while( strlen( $pwsalt ) < 16 ) $pwsalt .= chr( rand( 32, 126 ) );
        while( strlen( $rksalt ) < 8 ) $rksalt .= chr( rand( 32, 126 ) );

        $rksalt .= uniqid( rand(), true ) . $id;

        $pwhash = hash( 'whirlpool', $pwsalt . $new_passwd . $this->trellis->config['pass_key'] );
        $rkhash = md5( $rksalt . $this->trellis->config['rss_key'] );

        $db_array = array(
                          'pass_hash'        => $pwhash,
                          'pass_salt'        => $pwsalt,
                          'login_key'        => "",
                          'rss_key'            => $rkhash,
                          );

        $this->trellis->db->construct( array(
                                                   'update'    => 'users',
                                                     'set'    => $db_array,
                                                     'where'    => array( 'id', '=', $id ),
                                                     'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return true;
    }

    #=======================================
    # @ Change Email
    #=======================================

    function change_email($new_email, $id, $skip_val=0)
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( ! $this->trellis->validate_email( $new_email ) ) return false;

        if ( $skip_val || ! $this->trellis->cache->data['settings']['security']['validation_email'] )
        {
            #=============================
            # Update Database
            #=============================

            $this->trellis->db->construct( array(
                                                       'update'    => 'users',
                                                       'set'    => array( 'email' => $new_email ),
                                                       'where'    => array( 'id', '=', $id ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->execute();

            return $new_email;
        }
        else
        {
            #=============================
            # Insert Validation
            #=============================

            $val_code = md5 ( $new_email . uniqid( rand(), true ) );

            $db_array = array(
                              'id'        => $val_code,
                              'uid'        => $this->trellis->user['id'],
                              'email'    => $new_email,
                              'date'    => time(),
                              'type'    => 2,
                             );

            $this->trellis->db->construct( array(
                                                       'insert'    => 'validation',
                                                       'set'    => $db_array,
                                                )       );

            $this->trellis->db->execute();

            #=============================
            # Send Email
            #=============================

            $this->trellis->load_email();

            $email_tags = array(
                                '{LINK}' => $this->trellis->config['hd_url'] .'/index.php?page=account&act=dovalidate&key='. $val_code,
                                '%7BLINK%7D' => $this->trellis->config['hd_url'] .'/index.php?page=account&act=dovalidate&key='. $val_code,
                                );

            $this->trellis->email->send_email( array( 'to' => $id, 'name' => $this->trellis->user['name'], 'email' => $new_email, 'msg' => 'change_email_val', 'replace' => $email_tags, 'lang' => $this->trellis->user['lang'], 'format' => $this->trellis->user['email_type'], 'override' => 1 ) );

            return 1;
        }
    }

    #=======================================
    # @ Approve User
    #=======================================

    public function approve($id, $params=array())
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( ! $params['type'] ) $params['type'] = 'admin';

        $sql_select = array();

        if ( ! isset( $params['val_email'] ) && ( $params['type'] == 'admin' || $params['type'] == 'both' ) ) $sql_select[] = 'val_email';
        if ( ! isset( $params['val_admin'] ) && ( $params['type'] == 'email' || $params['type'] == 'both' ) ) $sql_select[] = 'val_admin';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $u = $this->get_single_by_id( $sql_select, $id ) ) return false;
        }

        if ( ! isset( $params['val_email'] ) && ( $params['type'] == 'admin' || $params['type'] == 'both' ) ) $params['val_email'] = $u['val_email'];
        if ( ! isset( $params['val_admin'] ) && ( $params['type'] == 'email' || $params['type'] == 'both' ) ) $params['val_admin'] = $u['val_admin'];

        if ( $params['type'] == 'both' )
        {
            $db_array = array( 'val_admin' => 1, 'val_email' => 1 );
        }
        elseif ( $params['type'] == 'admin' )
        {
            $db_array = array( 'val_admin' => 1 );
        }
        elseif ( $params['type'] == 'email' )
        {
            $db_array = array( 'val_email' => 1 );
        }

        $result = array_merge( $params, $db_array );

        if ( $result['val_admin'] && $result['val_email'] ) $db_array['ugroup'] = 1; // Members' group id = 1

        #* update old group (validating) user count and update new group (members)

        $this->trellis->db->construct( array(
                                             'update'    => 'users',
                                             'set'        => $db_array,
                                             'where'    => array( 'id', '=', $id ),
                                             'limit'    => array( 1 ),
                                      )      );

        $this->trellis->db->execute();

        $this->trellis->load_email();

        if ( $result['val_admin'] && $result['val_email'] )
        {
            if ( $params['type'] == 'email' )
            {
                $this->trellis->email->send_email( array( 'to' => $id, 'msg' => 'user_activated', 'override' => 1 ) );
            }
            else
            {
                $this->trellis->email->send_email( array( 'to' => $id, 'msg' => 'user_approved', 'override' => 1 ) );
            }

            return 2;
        }
        elseif ( $result['val_admin'] )
        {
            $this->trellis->email->send_email( array( 'to' => $id, 'msg' => 'user_almost_activated', 'override' => 1 ) );

            return 1;
        }
        elseif ( $result['val_email'] )
        {
            $this->trellis->email->send_email( array( 'to' => $id, 'msg' => 'user_almost_approved', 'override' => 1 ) );

            return 1;
        }

        return true;
    }

    #=======================================
    # @ Resend Validation
    #=======================================

    function resend_validation($to, $field='name')
    {
        if ( $field == 'name' )
        {
            if ( ! $u = $this->get_single_by_name( array( 'id', 'email', 'val_email', 'val_admin' ), $to ) ) return false;
        }
        elseif ( $field == 'email' )
        {
            if ( ! $u = $this->get_single_by_email( array( 'id', 'email', 'val_email', 'val_admin' ), $to ) ) return false;
        }
        elseif ( $field == 'id' )
        {
            if ( ! $u = $this->get_single_by_id( array( 'id', 'email', 'val_email', 'val_admin' ), $to ) ) return false;
        }

        if ( $u['val_email'] ) return false;

        $val_code = md5( $u['email'] . uniqid( rand(), true ) );

        $db_array = array(
                          'id'        => $val_code,
                          'uid'        => $u['id'],
                          'email'    => $u['email'],
                          'date'    => time(),
                          'type'    => 1,
                          );

        $this->trellis->db->construct( array(
                                             'insert'    => 'validation',
                                             'set'        => $db_array,
                                      )      );

        $this->trellis->db->execute();

        #* TD log

        $this->trellis->load_email();

        $email_tags = array(
                            '{LINK}' => $this->trellis->config['hd_url'] .'/index.php?page=register&act=dovalidate&key='. $val_code,
                            '%7BLINK%7D' => $this->trellis->config['hd_url'] .'/index.php?page=register&act=dovalidate&key='. $val_code,
                            );

        // Do we require admin validation too?
        if ( $u['val_admin'] )
        {
            $this->trellis->email->send_email( array( 'to' => $u['id'], 'msg' => 'user_new_val_email', 'replace' => $email_tags, 'override' => 1 ) );
        }
        else
        {
            $this->trellis->email->send_email( array( 'to' => $u['id'], 'msg' => 'user_new_val_both', 'replace' => $email_tags, 'override' => 1 ) );
        }

        return true;
    }

    #=======================================
    # @ Validate Email
    #=======================================

    function validate_email($key)
    {
        $this->trellis->db->construct( array(
                                                     'select'    => 'all',
                                                     'from'    => 'validation',
                                                     'where'    => array( 'id', '=', $key ),
                                                     'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        $v = $this->trellis->db->fetch_row();

        if ( $v['type'] != 1 && $v['type'] != 2 ) return false;

        if ( $v['date'] < time() - ( 60 * 60 * $this->trellis->cache->data['settings']['security']['email_expire'] ) )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'validation',
                                                       'where'    => array( 'id', '=', $v['id'] ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->execute();

            return false;
        }

        #=============================
        # Update User
        #=============================

        if ( $v['type'] == 1 )
        {
            if ( ! $response = $this->approve( $v['uid'], array( 'type' => 'email' ) ) ) return false;
        }
        elseif ( $v['type'] == 2 )
        {
            $this->trellis->db->construct( array(
                                                 'update'    => 'users',
                                                 'set'        => array( 'email' => $v['email'] ),
                                                 'where'    => array( 'id', '=', $v['uid'] ),
                                                 'limit'    => array( 1 ),
                                          )      );

            $this->trellis->db->execute();

            $response = $v['email'];
        }

        #=============================
        # Delete Validation
        #=============================

        $this->trellis->db->construct( array(
                                                   'delete'    => 'validation',
                                                   'where'    => array( 'id', '=', $v['id'] ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $response;
    }

    #=======================================
    # @ Forgot Password
    #=======================================

    function forgot_password($to, $field='name')
    {
        if ( $field == 'name' )
        {
            if ( ! $u = $this->get_single_by_name( array( 'id', 'email' ), $to ) ) return false;
        }
        elseif ( $field == 'email' )
        {
            if ( ! $u = $this->get_single_by_email( array( 'id', 'email' ), $to ) ) return false;
        }
        elseif ( $field == 'id' )
        {
            if ( ! $u = $this->get_single_by_id( array( 'id', 'email' ), $to ) ) return false;
        }

        $val_code = md5( $u['email'] . uniqid( rand(), true ) );

        $db_array = array(
                          'id'        => $val_code,
                          'uid'        => $u['id'],
                          'email'    => $u['email'],
                          'date'    => time(),
                          'type'    => 3,
                          );

        $this->trellis->db->construct( array(
                                             'insert'    => 'validation',
                                             'set'        => $db_array,
                                      )      );

        $this->trellis->db->execute();

        #* TD log

        $this->trellis->load_email();

        $email_tags = array(
                            '{LINK}' => $this->trellis->config['hd_url'] .'/index.php?page=register&act=resetpass&key='. $val_code,
                            '%7BLINK%7D' => $this->trellis->config['hd_url'] .'/index.php?page=register&act=resetpass&key='. $val_code,
                            );

        $this->trellis->email->send_email( array( 'to' => $u['id'], 'msg' => 'reset_pass_val', 'replace' => $email_tags, 'override' => 1 ) );

        return true;
    }

    #=======================================
    # @ Check Reset Password Key
    #=======================================

    function check_reset_pswd_key($key, $delete=0)
    {
        $this->trellis->db->construct( array(
                                                   'select'    => 'all',
                                                   'from'    => 'validation',
                                                   'where'    => array( array( 'id', '=', $key ), array( 'type', '=', 3, 'and' ) ),
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        $v = $this->trellis->db->fetch_row();

        if ( $v['date'] < time() - ( 60 * 60 * $this->trellis->cache->data['settings']['security']['password_expire'] ) )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'validation',
                                                       'where'    => array( 'id', '=', $v['id'] ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->execute();

            return false;
        }

        if ( $delete )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'validation',
                                                       'where'    => array( 'id', '=', $v['id'] ),
                                                       'limit'    => array( 1 ),
                                                )       );

            $this->trellis->db->execute();
        }

        return $v['uid'];
    }

}

?>