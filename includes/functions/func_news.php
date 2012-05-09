<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_news {

    public $error = '';

    #=======================================
    # @ Get News
    #=======================================

    public function get($input)
    {
        $return = array();

        if ( ! $input['from'] ) $input['from'] = 'news';

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => $input['from'],
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $n = $this->trellis->db->fetch_row() )
        {
            $return[ $n['id'] ] = $n;
        }

        return $return;
    }

    #=======================================
    # @ Get Single News Item
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'news',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single News Item By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Get Comments
    #=======================================

    public function get_comments($input)
    {
        $input['from'] = 'news_comments';

        return $this->get($input);
    }

    #=======================================
    # @ Get Single Comment
    #=======================================

    public function get_single_comment($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'news_comments',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Comment By ID
    #=======================================

    public function get_single_comment_by_id($select, $id)
    {
        return $this->get_single_comment( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add News
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'uid'                => 'int',
                        'title'                => 'string',
                        'excerpt'            => 'string',
                        'content'            => 'string',
                        'allow_comments'    => 'int',
                        'html'                => 'int',
                        'date'                => 'int',
                        'ipadd'                => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'news',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Add Comment
    #=======================================

    public function add_comment($data, $nid)
    {
        # $nid can be pulled from $data!

        $fields = array(
                        'nid'        => 'int',
                        'uid'        => 'int',
                        'message'    => 'string',
                        'staff'        => 'int',
                        'html'        => 'int',
                        'date'        => 'int',
                        'ipadd'        => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'news_comments',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        $id = $this->trellis->db->get_insert_id();

        $this->trellis->db->construct( array(
                                                   'update'    => 'news',
                                                   'set'    => array( 'comments' => array( '+', 1 ) ),
                                                   'where'    => array( 'id', '=', $nid ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $id;
    }

    #=======================================
    # @ Edit News
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'uid'                => 'int',
                        'title'                => 'string',
                        'excerpt'            => 'string',
                        'content'            => 'string',
                        'allow_comments'    => 'int',
                        'html'                => 'int',
                        'date'                => 'int',
                        'ipadd'                => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'news',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Edit Comment
    #=======================================

    public function edit_comment($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'message'    => 'string',
                        'staff'        => 'int',
                        'html'        => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'news_comments',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete News
    #=======================================

    public function delete($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'news_comments',
                                                   'where'    => array( 'nid', '=', $id ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'news',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Comment
    #=======================================

    public function delete_comment($id, $params=array())
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( ! isset( $params['nid'] ) )
        {
            if ( ! $c = $this->get_single_comment_by_id( array( 'nid' ), $id ) ) return false;

             $params['nid'] = $c['nid'];
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'news_comments',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $dc = $this->trellis->db->get_affected_rows();

        $this->trellis->db->construct( array(
                                                   'update'    => 'news',
                                                   'set'    => array( 'comments' => array( '-', 1 ) ),
                                                   'where'    => array( 'id', '=', $params['nid'] ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->next_shutdown();
        $this->trellis->db->execute();

        return $dc;
    }

}

?>