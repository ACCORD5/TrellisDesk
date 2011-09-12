<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_articles extends td_func_template {

    #=======================================
    # @ Get Articles
    #=======================================

    public function get($input)
    {
        $return = array();

        if ( ! $input['from'] ) $input['from'] = 'articles';

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => $input['from'],
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $a = $this->trellis->db->fetch_row() )
        {
            $return[ $a['id'] ] = $a;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Article
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'articles',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Article By ID
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
        $input['from'] = 'article_comments';

        return $this->get($input);
    }

    #=======================================
    # @ Get Single Comment
    #=======================================

    public function get_single_comment($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'article_comments',
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
    # @ Get Ratings
    #=======================================

    public function get_ratings($input)
    {
        $input['from'] = 'article_rate';

        return $this->get($input);
    }

    #=======================================
    # @ Get Single Rating
    #=======================================

    public function get_single_rating($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'article_rate',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Rating By ID
    #=======================================

    public function get_single_rating_by_id($select, $id)
    {
        return $this->get_single_rating( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Article
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'id'                => 'int',
                        'cid'                => 'int',
                        'title'                => 'string',
                        'description'        => 'string',
                        'content'            => 'string',
                        'html'                => 'int',
                        'allow_comments'    => 'int',
                        'allow_rating'        => 'int',
                        'date'                => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'articles',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        $id = $this->trellis->db->get_insert_id();

        // increment category article count
        $this->trellis->db->construct( array(
            'update'    => 'categories',
            'set'        => array( 'articles' => array( '-', 1 ) ),
            'where'        => array( 'id', '=', $data['cid'] ),
            'limit'        => array( 1 ),
        ) );

        return $id;
    }

    #=======================================
    # @ Add Comment
    #=======================================

    public function add_comment($data, $aid)
    {
        $fields = array(
                        'aid'        => 'int',
                        'uid'        => 'int',
                        'message'    => 'string',
                        'staff'        => 'int',
                        'html'        => 'int',
                        'date'        => 'int',
                        'ipadd'        => 'string',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'article_comments',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        $id = $this->trellis->db->get_insert_id();

        $this->increase_count( $aid, 1, 'comments' );

        return $id;
    }

    #=======================================
    # @ Add Rating
    #=======================================

    public function add_rating($amount, $aid, $params=array())
    {
        if ( ! $amount = floatval( $amount ) ) return false;
        if ( ! $aid = intval( $aid ) ) return false;

        $sql_select = array();

        if ( ! isset( $params['rating_total'] ) ) $sql_select[] = 'rating_total';
        if ( ! isset( $params['votes'] ) ) $sql_select[] = 'votes';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $a = $this->get_single_by_id( $sql_select, $aid ) ) return false;
        }

        if ( ! isset( $params['rating_total'] ) ) $params['rating_total'] = $a['rating_total'];
        if ( ! isset( $params['votes'] ) ) $params['votes'] = $a['votes'];

        $db_array = array(
                          'aid'            => $aid,
                          'uid'            => $this->trellis->user['id'],
                          'rating'        => $amount,
                          'date'        => time(),
                          'ipadd'        => $this->trellis->input['ip_address'],
                         );

        $this->trellis->db->construct( array(
                                             'insert'    => 'article_rate',
                                             'set'        => $db_array,
                                      )         );

        $this->trellis->db->execute();

        $id = $this->trellis->db->get_insert_id();

        $new_total = $params['rating_total'] + $amount;
        $new_average = round( ( $new_total ) / ( $params['votes'] + 1 ), 2 );

        $this->trellis->db->construct( array(
                                             'update'    => 'articles',
                                             'set'        => array( 'rating_average' => $new_average, 'rating_total' => $new_total, 'votes' => ( $params['votes'] + 1 ) ),
                                             'where'    => array( 'id', '=', $aid ),
                                             'limit'    => array( 1 ),
                                      )         );

        $this->trellis->db->execute();

        return $id;
    }

    #=======================================
    # @ Edit Article
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'id'                => 'int',
                        'cid'                => 'int',
                        'title'                => 'string',
                        'description'        => 'string',
                        'content'            => 'string',
                        'html'                => 'int',
                        'allow_comments'    => 'int',
                        'allow_rating'        => 'int',
                        'modified'            => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'articles',
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
                                                   'update'    => 'article_comments',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Edit Rating
    #=======================================

    public function edit_rating($amount, $id, $params=array())
    {
        if ( ! $amount = floatval( $amount ) ) return false;
        if ( ! $id = intval( $id ) ) return false;

        $sql_select = array();

        if ( ! isset( $params['aid'] ) ) $sql_select[] = 'aid';
        if ( ! isset( $params['amount_old'] ) ) $sql_select[] = 'rating';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $r = $this->get_single_rating_by_id( $sql_select, $id ) ) return false;
        }

        if ( ! isset( $params['aid'] ) ) $params['aid'] = $r['aid'];
        if ( ! isset( $params['amount_old'] ) ) $params['amount_old'] = $r['rating'];

        $sql_select = array();

        if ( ! isset( $params['rating_total'] ) ) $sql_select[] = 'rating_total';
        if ( ! isset( $params['votes'] ) ) $sql_select[] = 'votes';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $a = $this->get_single_by_id( $sql_select, $params['aid'] ) ) return false;
        }

        if ( ! isset( $params['rating_total'] ) ) $params['rating_total'] = $a['rating_total'];
        if ( ! isset( $params['votes'] ) ) $params['votes'] = $a['votes'];

        $db_array = array(
                          'rating'        => $amount,
                          'date'        => time(),
                          'ipadd'        => $this->trellis->input['ip_address'],
                         );

        $this->trellis->db->construct( array(
                                             'update'    => 'article_rate',
                                             'set'        => $db_array,
                                             'where'    => array( 'id', '=', $id ),
                                             'limit'    => array( 1 ),
                                      )         );

        $this->trellis->db->execute();

        $new_total = $params['rating_total'] - $params['amount_old'] + $amount;
        $new_average = round( ( $new_total ) / ( $params['votes'] ), 2 );

        $this->trellis->db->construct( array(
                                             'update'    => 'articles',
                                             'set'        => array( 'rating_average' => $new_average, 'rating_total' => $new_total ),
                                             'where'    => array( 'id', '=', $params['aid'] ),
                                             'limit'    => array( 1 ),
                                      )         );

        $this->trellis->db->execute();

        return true;
    }

    #=======================================
    # @ Delete Article
    #=======================================

    public function delete($id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $this->trellis->db->construct( array(
                                                   'delete'    => 'article_comments',
                                                   'where'    => array( 'aid', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'article_rate',
                                                   'where'    => array( 'aid', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $this->trellis->db->construct( array(
                                                   'delete'    => 'articles',
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

        if ( ! isset( $params['aid'] ) )
        {
            if ( ! $c = $this->get_single_comment_by_id( array( 'aid' ), $id ) ) return false;

             $params['aid'] = $c['aid'];
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'article_comments',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        $dc = $this->trellis->db->get_affected_rows();

        $this->decrease_count( $params['aid'], 1, 'comments' );

        return $dc;
    }

    #=======================================
    # @ Delete Rating
    #=======================================

    public function delete_rating($id, $params=array())
    {
        if ( ! $id = intval( $id ) ) return false;

        $sql_select = array();

        if ( ! isset( $params['aid'] ) ) $sql_select[] = 'aid';
        if ( ! isset( $params['amount_old'] ) ) $sql_select[] = 'rating';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $r = $this->get_single_rating_by_id( $sql_select, $id ) ) return false;
        }

        if ( ! isset( $params['aid'] ) ) $params['aid'] = $r['aid'];
        if ( ! isset( $params['amount_old'] ) ) $params['amount_old'] = $r['rating'];

        $sql_select = array();

        if ( ! isset( $params['rating_total'] ) ) $sql_select[] = 'rating_total';
        if ( ! isset( $params['votes'] ) ) $sql_select[] = 'votes';

        if ( ! empty( $sql_select ) )
        {
            if ( ! $a = $this->get_single_by_id( $sql_select, $params['aid'] ) ) return false;
        }

        if ( ! isset( $params['rating_total'] ) ) $params['rating_total'] = $a['rating_total'];
        if ( ! isset( $params['votes'] ) ) $params['votes'] = $a['votes'];

        $this->trellis->db->construct( array(
                                             'delete'    => 'article_rate',
                                             'where'    => array( 'id', '=', $id ),
                                             'limit'    => array( 1 ),
                                      )         );

        $this->trellis->db->execute();

        $new_total = $params['rating_total'] - $params['amount_old'];
        ( ( $params['votes'] - 1 ) ) ? $new_average = round( ( $new_total ) / ( $params['votes'] - 1 ), 2 ) : $new_average = 0;

        $this->trellis->db->construct( array(
                                             'update'    => 'articles',
                                             'set'        => array( 'rating_average' => $new_average, 'rating_total' => $new_total, 'votes' => ( $params['votes'] - 1 ) ),
                                             'where'    => array( 'id', '=', $params['aid'] ),
                                             'limit'    => array( 1 ),
                                      )         );

        $this->trellis->db->execute();

        return true;
    }

    #=======================================
    # @ Increase Count
    #=======================================

    public function increase_count($id, $amount, $field, $now=0)
    {
        $this->update_stats( $id, $amount, 'articles', $field, 'increase', $now );
    }

    #=======================================
    # @ Decrease Count
    #=======================================

    public function decrease_count($id, $amount, $field, $now=0)
    {
        $this->update_stats( $id, $amount, 'articles', $field, 'decrease', $now );
    }

}

?>