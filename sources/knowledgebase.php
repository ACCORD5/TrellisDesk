<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_source_knowledgebase {

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->cache->data['settings']['kb']['enable'] ) $this->trellis->skin->error('kb_disabled');

        if ( ! $this->trellis->user['g_kb_access'] )
        {
            $this->trellis->log( 'security', "Blocked Access Knowledge Base" );

            $this->trellis->skin->error('no_perm');
        }

        #=============================
        # Initialize
        #=============================

        $this->trellis->load_lang('knowledgebase');

        switch( $this->trellis->input['act'] )
        {
            case 'view':
                $this->view_article();
            break;
            case 'cat':
                $this->show_category();
            break;

            case 'editcomment':
                $this->edit_comment();
            break;

            case 'doaddcomment':
                $this->do_add_comment();
            break;
            case 'doeditcomment':
                $this->do_edit_comment();
            break;
            case 'dodeletecomment':
                $this->do_delete_comment();
            break;
            case 'dorate':
                $this->do_rate();
            break;
            case 'search':
                $this->do_search();
            break;

            default:
                $this->show_categories();
            break;
        }
    }

    #=======================================
    # @ Show Categories
    #=======================================

    private function show_categories()
    {
        #=============================
        # Grab Categories
        #=============================

        $this->trellis->load_functions('categories');

        $perms = array();

        foreach ( $this->trellis->user['g_kb_perm'] as $cid => $p )
        {
            if ( $p ) $perms[] = $cid;
        }

        if ( ! empty( $perms ) ) $categories = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'name', 'description', 'articles' ), 'where' => array( array( 'parent_id', '=', 0 ), array( 'id', 'in', $perms, 'and' ) ), 'order' => array( 'position' => 'asc' ) ) );

        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->set_var( 'categories', $categories );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb">'. $this->trellis->lang['knowledge_base'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'knowledge_base.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['knowledge_base'] ) );
    }

    #=======================================
    # @ Show Category
    #=======================================

    private function show_category()
    {
        #=============================
        # Grab Category
        #=============================

        $perms = array();

        foreach ( $this->trellis->user['g_kb_perm'] as $cid => $p )
        {
            if ( $p ) $perms[] = $cid;
        }

        $this->trellis->load_functions('categories');

        if ( ! $c = $this->trellis->func->categories->get_single_by_id( array( 'id', 'name', 'description', 'articles' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_category');

        $this->trellis->skin->set_var( 'c', $c );

        if ( ! $this->trellis->user['g_kb_perm'][ $c['id'] ] ) $this->trellis->skin->error('no_category');

        #=============================
        # Grab Sub-Categories
        #=============================

        if ( ! empty( $perms ) ) $categories = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'name', 'description', 'articles' ), 'where' => array( array( 'parent_id', '=', $this->trellis->input['id'] ), array( 'id', 'in', $perms, 'and' ) ), 'order' => array( 'position' => 'asc' ) ) );

        $this->trellis->skin->set_var( 'categories', $categories );

        #=============================
        # Grab Articles
        #=============================

        $this->trellis->load_functions('articles');

        $a_total = $this->trellis->func->articles->get( array( 'select' => array( 'id' ), 'where' => array( 'cid', '=', $c['id'] ) ) );

        $articles = $this->trellis->func->articles->get( array( 'select' => array( 'id', 'title', 'description', 'rating_average', 'votes', 'views', 'comments', 'allow_rating' ), 'where' => array( 'cid', '=', $c['id'] ), 'order' => array( 'title' => 'asc' ), 'limit' => array( $this->trellis->input['st'], 15 ) ) );

        if ( $articles )
        {
            foreach ( $articles as &$a )
            {
                #=============================
                # Format Articles
                #=============================

                if ( $this->trellis->cache->data['settings']['kb']['rating'] && $this->trellis->cache->data['categories'][ $c['id'] ]['allow_rating'] && $a['allow_rating'] )
                {
                    ( $this->trellis->cache->data['settings']['kb']['rating_threshold'] && $this->trellis->cache->data['settings']['kb']['rating_threshold'] <= $a['votes'] ) ? $rating_threshold = 1 : $rating_threshold = 0;

                    ( $rating_threshold ) ? $rating_to_display = $a['rating_average'] : $rating_to_display = 0;

                    $a['rate_stars'] = $this->rate_stars( $rating_to_display, 0, 'star bumpup', $a['id'] );
                }
            }
        }

        $this->trellis->skin->set_var( 'articles', $articles );

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $a_total ),
                                                        'per_page'    => 15,
                                                        'url'        => $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=cat&amp;id='. $c['id'],
                                                        ) );

        $this->trellis->skin->set_var( 'page_links', $page_links );

        $this->nav = array( '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb">'. $this->trellis->lang['knowledge_base'] .'</a>' );

        if ( $this->trellis->cache->data['categories'][ $c['id'] ]['parent_id'] ) $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=cat&amp;id='. $this->trellis->cache->data['categories'][ $c['id'] ]['parent_id'] .'">'. $this->trellis->cache->data['categories'][ $this->trellis->cache->data['categories'][ $c['id'] ]['parent_id'] ]['name'] .'</a>';

        $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=cat&amp;id='. $c['id'] .'">'. $c['name'] .'</a>';

        $this->trellis->skin->set_var( 'sub_tpl', 'kb_category.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['knowledge_base'] .' :: '. $c['name'] ) );
    }

    #=======================================
    # @ Show Article
    #=======================================

    private function view_article($params=array())
    {
        #=============================
        # Grab Article
        #=============================

        $this->trellis->load_functions('articles');

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'description', 'content', 'rating_average', 'votes', 'views', 'comments', 'html', 'date', 'modified', 'allow_comments', 'allow_rating' ), $this->trellis->input['id'] ) )
        {
            $this->trellis->log( 'error', "Article Not Found ID: ". $this->trellis->input['id'] );

            $this->trellis->skin->error('no_article');
        }

        if ( ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        #=============================
        # Format Article
        #=============================

        $a['date_human'] = $this->trellis->td_timestamp( array( 'time' => $a['date'], 'format' => 'short' ) );

        $aoutput_params = array( 'linkify' => 1 );

        if ( $a['html'] )
        {
            $aoutput_params['html'] = 1;
        }
        else
        {
            $aoutput_params['paragraphs'] = 1;
            $aoutput_params['nl2br'] = 1;
        }

        $a['content'] = $this->trellis->prepare_output( $a['content'], $aoutput_params );

        #=============================
        # Can We Rate?
        #=============================

        if ( $this->trellis->cache->data['settings']['kb']['rating'] && $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_rating'] && $a['allow_rating'] )
        {
            ( $this->trellis->cache->data['settings']['kb']['rating_threshold'] && $this->trellis->cache->data['settings']['kb']['rating_threshold'] <= $a['votes'] ) ? $rating_threshold = 1 : $rating_threshold = 0;

            ( $rating_threshold ) ? $rating_to_display = $a['rating_average'] : $rating_to_display = 0;

            if ( $this->trellis->user['id'] && $this->trellis->user['g_kb_rate'] ) # TODO: allow guests to rate
            {
                if ( $r = $this->trellis->func->articles->get_single_rating( array( 'id', 'rating' ), array( array( 'aid', '=', $a['id'] ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ) ) )
                {
                    if ( ! $rating_threshold ) $rating_to_display = $r['rating'];

                    $a['rate_stars'] = $this->rate_stars( $rating_to_display, 1 );
                }
                else
                {
                    $a['rate_stars'] = $this->rate_stars( $rating_to_display, 1 );
                }
            }
            else
            {
                $a['rate_stars'] = $this->rate_stars( $rating_to_display, 0 );
            }
        }

        #=============================
        # Grab Comments?
        #=============================

        $comments = $this->trellis->db->get( array(
                                                         'select'    => array(
                                                                                'c' => 'all',
                                                                                'u' => array( array( 'name' => 'uname' ) ),
                                                                                ),
                                                         'from'        => array( 'c' => 'article_comments' ),
                                                         'join'        => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'c' => 'uid', '=', 'u' => 'id' ) ) ),
                                                         'where'    => array( array( 'c' => 'aid' ), '=', $a['id'] ),
                                                         'order'    => array( 'date' => array( 'c' => 'asc' ) ),
                                                  ), 'id' );

        if ( $comments )
        {
            foreach ( $comments as &$c )
            {
                #=============================
                # Format Comments
                #=============================

                $c['time_ago'] = $this->trellis->td_timestamp( array( 'time' => $c['date'], 'format' => 'relative' ) );

                $c['date_human'] = $this->trellis->td_timestamp( array( 'time' => $c['date'], 'format' => 'short' ) );

                $coutput_params = array( 'linkify' => 1 );

                if ( $c['html'] )
                {
                    $coutput_params['html'] = 1;
                }
                else
                {
                    $coutput_params['paragraphs'] = 1;
                    $coutput_params['nl2br'] = 1;
                }

                $c['message'] = $this->trellis->prepare_output( $c['message'], $coutput_params );

                // Permissions for Templates
                if ( $this->trellis->cache->data['settings']['kb']['comments'] && $a['allow_comments'] && $c['uid'] == $this->trellis->user['id'] )
                {
                    if ( $this->trellis->user['g_kb_com_edit'] ) $c['can_edit'] = 1;
                    if ( $this->trellis->user['g_kb_com_delete'] ) $c['can_delete'] = 1;
                }
            }

            $this->trellis->skin->set_var( 'comments', $comments );
        }

        #=============================
        # Stats
        #=============================

        $this->trellis->func->articles->increase_count( $a['id'], 1, 'views' );

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $params['error'] ] );
        if ( $params['alert'] ) $this->trellis->skin->set_var( 'alert', $this->trellis->lang[ 'alert_'. $params['alert'] ] );
        if ( $params['error_comment'] ) $this->trellis->skin->set_var( 'error_comment', $this->trellis->lang[ 'err_'. $params['error_comment'] ] );

        if ( $params['scroll'] ) $this->trellis->skin->set_var( 'scroll', $params['scroll'] );

        // Permissions for Templates
        if ( $this->trellis->cache->data['settings']['kb']['comments'] && $this->trellis->user['g_kb_comment'] && $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_comments'] && $a['allow_comments'] ) $a['can_comment'] = 1;

        $this->trellis->skin->set_var( 'a', $a );

        $this->nav = array( '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb">'. $this->trellis->lang['knowledge_base'] .'</a>' );

        if ( $this->trellis->cache->data['categories'][ $a['cid'] ]['parent_id'] ) $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=cat&amp;id='. $this->trellis->cache->data['categories'][ $a['cid'] ]['parent_id'] .'">'. $this->trellis->cache->data['categories'][ $this->trellis->cache->data['categories'][ $a['cid'] ]['parent_id'] ]['name'] .'</a>';

        $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=cat&amp;id='. $a['cid'] .'">'. $this->trellis->cache->data['categories'][ $a['cid'] ]['name'] .'</a>';
        $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=view&amp;id='. $a['id'] .'">'. $a['title'] .'</a>';

        if ( $type == 'print' )
        {
            $this->trellis->skin->set_var( 'sub_tpl', 'kb_print_article.tpl' );

            $this->trellis->skin->do_print( array( 'title' => $this->trellis->lang['knowledge_base'] .' :: '. $a['name'] ) );
        }
        else
        {
            $this->trellis->skin->set_var( 'sub_tpl', 'kb_article.tpl' );

            $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['knowledge_base'] .' :: '. $a['name'] ) );
        }
    }

    #=======================================
    # @ Edit Comment
    #=======================================

    private function edit_comment($error="")
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->load_functions('articles');

        # TODO: could combine into one query =/ maybe edit get_single_comment_by_id to always grab basic article info

        if ( ! $c = $this->trellis->func->articles->get_single_comment_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_comment');

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'allow_comments' ), $c['aid'] ) ) $this->trellis->skin->error('no_article');

        if ( ! $this->trellis->cache->data['settings']['kb']['comments'] || ! $this->trellis->user['g_kb_com_edit'] || ! $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_comments'] || ! $a['allow_comments'] || $c['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );
        }

        $this->trellis->skin->set_var( 'c', $c );

        $this->nav = array( '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb">'. $this->trellis->lang['knowledge_base'] .'</a>' );

        if ( $this->trellis->cache->data['categories'][ $a['cid'] ]['parent_id'] ) $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=cat&amp;id='. $this->trellis->cache->data['categories'][ $a['cid'] ]['parent_id'] .'">'. $this->trellis->cache->data['categories'][ $this->trellis->cache->data['categories'][ $a['cid'] ]['parent_id'] ]['name'] .'</a>';

        $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=cat&amp;id='. $a['cid'] .'">'. $this->trellis->cache->data['categories'][ $a['cid'] ]['name'] .'</a>';
        $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=view&amp;id='. $a['id'] .'">'. $a['title'] .'</a>';
        $this->nav[] = '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=kb&amp;act=editcomment&amp;id='. $c['id'] .'">'. $this->trellis->lang['edit_comment'] .'</a>';

        $this->trellis->skin->set_var( 'sub_tpl', 'kb_edit_comment.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['knowledge_base'] .' :: '. $this->trellis->lang['edit_comment'] ) );
    }

    #=======================================
    # @ Do Add Comment
    #=======================================

    private function do_add_comment()
    {
        #=============================
        # Security Checks
        #=============================

        $perms = array();

        foreach ( $this->trellis->user['g_kb_perm'] as $cid => $p )
        {
            if ( $p ) $perms[] = $cid;
        }

        $this->trellis->load_functions('articles');

        if ( ! $this->trellis->input['message'] ) $this->view_article( array( 'error_comment' => 'no_comment' ) );

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'allow_comments' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        if ( ! $this->trellis->cache->data['settings']['kb']['comments'] || ! $this->trellis->user['g_kb_comment'] || ! $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_comments'] || ! $a['allow_comments'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Add Comment
        #=============================

        $db_array = array(
                          'aid'            => $a['id'],
                          'uid'            => $this->trellis->user['id'],
                          'message'        => $this->trellis->input['message'],
                          'date'        => time(),
                          'ipadd'        => $this->trellis->input['ip_address'],
                         );

        $comment_id = $this->trellis->func->articles->add_comment( $db_array, $a['id'] );

        #=============================
        # Do Output
        #=============================

        $this->view_article( array( 'alert' => 'comment_added', 'scroll' => 'c'. $comment_id ) );
    }

    #=======================================
    # @ Do Edit Comment
    #=======================================

    private function do_edit_comment()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->load_functions('articles');

        # TODO: could combine into one query =/ maybe edit get_single_comment_by_id to always grab basic article info

        if ( ! $c = $this->trellis->func->articles->get_single_comment_by_id( array( 'id', 'aid', 'uid' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_comment');

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'allow_comments' ), $c['aid'] ) ) $this->trellis->skin->error('no_article');

        if ( ! $this->trellis->cache->data['settings']['kb']['comments'] || ! $this->trellis->user['g_kb_com_edit'] || ! $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_comments'] || ! $a['allow_comments'] || $c['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Update Comment
        #=============================

        $this->trellis->func->articles->edit_comment( array( 'message' => $this->trellis->input['message'] ), $c['id'] );

        $this->trellis->log( 'user', "Article Comment Edited ID #". $c['id'], 1, $c['id'] );

        #=============================
        # Do Output
        #=============================

        $this->trellis->input['id'] = $c['aid'];

        $this->view_article( array( 'alert' => 'comment_updated', 'scroll' => 'c'. $c['id'] ) );
    }

    #=======================================
    # @ Do Delete Comment
    #=======================================

    function do_delete_comment()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->load_functions('articles');

        # TODO: could combine into one query =/ maybe edit get_single_comment_by_id to always grab basic article info

        if ( ! $c = $this->trellis->func->articles->get_single_comment_by_id( array( 'id', 'aid', 'uid' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_comment');

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'allow_comments' ), $c['aid'] ) ) $this->trellis->skin->error('no_article');

        if ( ! $this->trellis->cache->data['settings']['kb']['comments'] || ! $this->trellis->user['g_kb_com_delete'] || ! $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_comments'] || ! $a['allow_comments'] || $c['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Delete Comment
        #=============================

        $this->trellis->func->articles->delete_comment( $c['id'], array( 'aid' => $c['aid'] ) );

        $this->trellis->log( 'user', "Article Comment Deleted ID #". $c['id'], 2, $c['id'] );

        #=============================
        # Do Output
        #=============================

        $this->trellis->input['id'] = $c['aid'];

        $this->view_article( array( 'error' => 'comment_deleted' ) );
    }

    #=======================================
    # @ Do Rate
    #=======================================

    private function do_rate()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->load_functions('articles');

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'rating_total', 'votes', 'allow_rating' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        if ( ! $this->trellis->user['id'] )
        {
            $this->trellis->log( 'security', "Article Rating Blocked From Guest &#039;". $a['name'] ."&#039;", 1, $a['id'] );

            $this->trellis->skin->error( 'must_be_user', 1 );
        }

        $allowed_ratings = array( '0.5', '1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5' );

        if ( $this->trellis->input['amount'] && ! in_array( $this->trellis->input['amount'], $allowed_ratings ) )
        {
            $this->trellis->log( 'security', "Invalid Article Rating Amount &#039;". $a['name'] ."&#039;", 1, $a['id'] );

            $this->trellis->skin->error('invalid_rate_value');
        }

        if ( ! $this->trellis->cache->data['settings']['kb']['rating'] || ! $this->trellis->user['g_kb_rate'] || ! $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_rating'] || ! $a['allow_rating'] ) $this->trellis->skin->error('no_perm');  # TD LOG no perm

        #=============================
        # Add / Edit / Delete Rating
        #=============================

        if ( $r = $this->trellis->func->articles->get_single_rating( array( 'id', 'rating' ), array( array( 'aid', '=', $a['id'] ), array( 'uid', '=', $this->trellis->user['id'], 'and' ) ) ) )
        {
            if ( ! $this->trellis->input['amount'] )
            {
                $this->trellis->func->articles->delete_rating( $r['id'], array( 'aid' => $a['id'], 'rating_total' => $a['rating_total'], 'votes' => $a['votes'], 'amount_old' => $r['rating'] ) );

                $this->trellis->log( 'user', "Article Rating Value ". $r['rating'] ." Deleted &#039;". $a['name'] ."&#039;", 1, $a['id'] );

                $msg = array( 'error' => 'rating_deleted' );
            }
            else
            {
                $this->trellis->func->articles->edit_rating( $this->trellis->input['amount'], $r['id'], array( 'aid' => $a['id'], 'rating_total' => $a['rating_total'], 'votes' => $a['votes'], 'amount_old' => $r['rating'] ) );

                $this->trellis->log( 'user', "Article Rating Value ". $r['rating'] ." to ". $this->trellis->input['amount'] ." Updated &#039;". $a['name'] ."&#039;", 1, $a['id'] );

                $msg = array( 'alert' => 'rating_updated' );
            }
        }
        elseif ( $this->trellis->input['amount'] )
        {
            $this->trellis->func->articles->add_rating( $this->trellis->input['amount'], $a['id'], array( 'rating_total' => $a['rating_total'], 'votes' => $a['votes'] ) );

            $this->trellis->log( 'user', "Article Rating Value ". $this->trellis->input['amount'] ." Added &#039;". $a['name'] ."&#039;", 1, $a['id'] );

            $msg = array( 'alert' => 'rating_added' );
        }

        #=============================
        # Do Output
        #=============================

        $this->view_article( $msg );
    }

    #=======================================
    # @ Do Search
    #=======================================

    function do_search()
    {
        #=============================
        # Search!
        #=============================

        # TODO: improve / finish search!

        die('not ready yet');

        /*$this->trellis->check_token('search');

        $searchstring = $this->trellis->input['keywords'];

        #$sql = "SELECT *, MATCH(name, description, article) AGAINST ('$searchstring') AS score FROM ". DB_PRE ."articles WHERE MATCH(name, description, article) AGAINST ('$searchstring') ORDER BY score DESC";

        $sql = "SELECT *, ( 1.6 * ( MATCH(keywords) AGAINST ('$searchstring' IN BOOLEAN MODE) ) + 0.9 * ( MATCH(name) AGAINST ('$searchstring' IN BOOLEAN MODE) ) + ( 0.6 * ( MATCH(article) AGAINST ('$searchstring' IN BOOLEAN MODE) ) ) ) AS score FROM ". DB_PRE ."articles WHERE MATCH(name, description, article) AGAINST ('$searchstring' IN BOOLEAN MODE) ORDER BY score DESC";
        #$sql = "SELECT *, ( 1.6 * ( MATCH(name) AGAINST ('$searchstring' IN BOOLEAN MODE) ) + ( 0.9 * ( MATCH(article) AGAINST ('$searchstring' IN BOOLEAN MODE) ) ) ) AS score FROM ". DB_PRE ."articles WHERE MATCH(name, description, article) AGAINST ('$searchstring' IN BOOLEAN MODE) ORDER BY score DESC";
        #$sql = "SELECT *, ( 0.9 * ( MATCH(name) AGAINST ('$searchstring' IN BOOLEAN MODE) ) + ( 1.6 * ( MATCH(article) AGAINST ('$searchstring' IN BOOLEAN MODE) ) ) ) AS score FROM ". DB_PRE ."articles WHERE MATCH(name, description, article) AGAINST ('$searchstring' IN BOOLEAN MODE) ORDER BY score DESC";

        #$sql = "SELECT *, ( (1.3 * (MATCH(name) AGAINST ('+{$searchtitle}' IN BOOLEAN MODE))) + (0.6 * (MATCH(article) AGAINST ('+$searchstring' IN BOOLEAN MODE))) ) AS score FROM ". DB_PRE ."articles WHERE ( MATCH(name,article) AGAINST ('$searchstring' IN BOOLEAN MODE) ) HAVING score > 0 ORDER BY score DESC";

        $this->trellis->db->query( $sql );

        $art = ""; // Initialize for Security

        while( $m = $this->trellis->db->fetch_row() )
        {
               if ( $m['score'] > $max_score )
               {
                   $max_score = $m['score'];
               }

               $art[] = $m;
        }

        $articles = array(); // Initialize for Security
        $row_count = 0; // Initialize for Security

        if ( is_array( $art ) )
        {
            while ( list( , $a ) = each( $art ) )
            {
                #=============================
                # Fix Up Information
                #=============================

                $row_count ++;

                ( $row_count & 1 ) ? $a['class'] = 1 : $a['class'] = 2;

                $a['date'] = $this->trellis->a5_date( $a['date'] );

                $a['score'] = @ round( ( $a['score'] / $max_score ) * 100 ) ."%";

                $articles[] = $a;
            }

            $this->trellis->skin->set_var( 'results', $articles );
        }

        #=============================
        # Do Output
        #=============================

        $this->nav = array(
                           "<a href='{$this->trellis->config['hd_url']}/index.php?act=kb'>{$this->trellis->lang['knowledge_base']}</a>",
                           "<a href='{$this->trellis->config['hd_url']}/index.php?act=kb&amp;code=search'>{$this->trellis->lang['search_results']}</a>",
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'kb_search_results.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['knowledge_base'] .' :: '. $this->trellis->lang['search_results'] ) );*/
    }

    #=======================================
    # @ Rate Stars
    #=======================================

    private function rate_stars($rating, $rate=0, $class='auto-submit-star', $name='')
    {
        # TODO: move to templates

        $html = "";

        $rating = round( ( $rating * 2 ), 0 );

        for ( $i = 0; $i < 10; $i++ )
        {
            $html .= '<input class="'. $class .' {split:2}" type="radio" name="rate'. $name .'" value="'. ( ( $i + 1 ) / 2 ) .'" ';

            if ( $i == 0 && ! $rate ) $html .= 'disabled="disabled" ';

            if ( ( $i + 1 ) == $rating ) $html .= 'checked="checked" ';

            $html .= '/>';
        }

        return $html;
    }

}

?>