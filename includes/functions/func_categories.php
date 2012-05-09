<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_categories {

    public $error = '';

    #=======================================
    # @ Get Categories
    #=======================================

    public function get($input)
    {
        $return = array();

        $this->trellis->db->construct( array(
                                                   'select'    => $input['select'],
                                                   'from'    => 'categories',
                                                   'where'    => $input['where'],
                                                   'order'    => $input['order'],
                                                   'limit'    => $input['limit'],
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        while ( $c = $this->trellis->db->fetch_row() )
        {
            $return[ $c['id'] ] = $c;
        }

        return $return;
    }

    #=======================================
    # @ Get Single Category
    #=======================================

    public function get_single($select, $where='')
    {
        $this->trellis->db->construct( array(
                                                   'select'    => $select,
                                                   'from'    => 'categories',
                                                   'where'    => $where,
                                                   'limit'    => array( 0, 1 ),
                                            )       );

        $this->trellis->db->execute();

        if ( ! $this->trellis->db->get_num_rows() ) return false;

        return $this->trellis->db->fetch_row();
    }

    #=======================================
    # @ Get Single Category By ID
    #=======================================

    public function get_single_by_id($select, $id)
    {
        return $this->get_single( $select, array( 'id', '=', intval( $id ) ) );
    }

    #=======================================
    # @ Add Category
    #=======================================

    public function add($data)
    {
        $fields = array(
                        'parent_id'            => 'int',
                        'name'                => 'string',
                        'description'        => 'string',
                        'allow_rating'        => 'int',
                        'allow_comments'    => 'int',
                        'position'            => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'insert'    => 'categories',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_insert_id();
    }

    #=======================================
    # @ Edit Category
    #=======================================

    public function edit($data, $id)
    {
        if ( ! $id = intval( $id ) ) return false;

        $fields = array(
                        'parent_id'            => 'int',
                        'name'                => 'string',
                        'description'        => 'string',
                        'allow_rating'        => 'int',
                        'allow_comments'    => 'int',
                        'position'            => 'int',
                        );

        $this->trellis->db->construct( array(
                                                   'update'    => 'categories',
                                                   'set'    => $this->trellis->process_data( $fields, $data ),
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

    #=======================================
    # @ Delete Category
    #=======================================

    public function delete($id, $action=0, $mvid=0)
    {
        if ( ! $id = intval( $id ) ) return false;

        if ( $action == 1 )
        {
            if ( ! $mvid = intval( $mvid ) ) return false;

            $this->trellis->db->construct( array(
                                                       'update'    => 'articles',
                                                       'set'    => array( 'cid' => $mvid ),
                                                       'where'    => array( 'cid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();

            $num_articles = $this->trellis->db->get_affected_rows();

            if ( $num_articles )
            {
                $this->trellis->db->next_no_quotes('set'); # TODO: THIS MAKES ME NERVOUS

                $this->trellis->db->construct( array(
                                                           'update'    => 'categories',
                                                           'set'    => array( 'articles' => 'articles+'. $num_articles ),
                                                           'where'    => array( 'id', '=', $mvid ),
                                                    )       );

                $this->trellis->db->execute();
            }
        }
        elseif ( $action == 2 )
        {
            $this->trellis->db->construct( array(
                                                       'select'    => array( 'id' ),
                                                       'from'    => 'articles',
                                                       'where'    => array( 'cid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() )
            {
                $articles = array();

                while( $a = $this->trellis->db->fetch_row() )
                {
                    $articles[] = $a['id'];
                }

                $this->trellis->db->construct( array(
                                                           'delete'    => 'article_comments',
                                                           'where'    => array( 'aid', 'in', $articles ),
                                                    )       );

                $this->trellis->db->execute();

                $this->trellis->db->construct( array(
                                                           'delete'    => 'article_rate',
                                                           'where'    => array( 'aid', 'in', $articles ),
                                                    )       );

                $this->trellis->db->execute();
            }

            $this->trellis->db->construct( array(
                                                       'delete'    => 'articles',
                                                       'where'    => array( 'cid', '=', $id ),
                                                )       );

            $this->trellis->db->execute();
        }

        $this->trellis->db->construct( array(
                                                   'delete'    => 'categories',
                                                   'where'    => array( 'id', '=', $id ),
                                                   'limit'    => array( 1 ),
                                            )       );

        $this->trellis->db->execute();

        return $this->trellis->db->get_affected_rows();
    }

}

?>