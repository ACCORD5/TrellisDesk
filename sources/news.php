<?php

/*
#======================================================
|    Trellis Desk
|    =====================================
|    By DJ "someotherguy" (sog@accord5.com)
|    © 2007 ACCORD5
|    http://www.accord.com/products/trellis/
|    =====================================
|    Email: sales@accord5.com
#======================================================
|    @ Version: v1.0 RC 1 Build 10031234
|    @ Version Int: 100.3.1.234
|    @ Version Num: 10031234
|    @ Build: 0234
#======================================================
|    | Sources: News
#======================================================
*/

class td_source_news {

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        #=============================
        # Initialize
        #=============================

        if ( ! $this->trellis->cache->data['settings']['news']['enable'] || ! $this->trellis->cache->data['settings']['news']['page'] )
        {
            $this->trellis->skin->error( 'news_disabled');
        }

        $this->trellis->load_functions('news');
        $this->trellis->load_lang('news');

        switch( $this->trellis->input['act'] )
        {
            case 'view':
                $this->view_news();
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

            default:
                $this->news_portal();
            break;
        }
    }

    #=======================================
    # @ News Portal
    #=======================================

    function news_portal()
    {
        #=============================
        # Grab News
        #=============================

        $n_total = $this->trellis->func->news->get( array( 'select' => array( 'id' ) ) );

        if ( $news = $this->trellis->func->news->get( array( 'select' => 'all', 'order' => array( 'date' => 'desc' ), 'limit' => array( $this->trellis->input['st'], $this->trellis->cache->data['settings']['news']['page_amount'] ) ) ) )
        {
            foreach ( $news as &$n )
            {
                #=============================
                # Format News
                #=============================

                $n['date_human'] = $this->trellis->td_timestamp( array( 'time' => $n['date'], 'format' => 'long' ) );

                $noutput_params = array( 'linkify' => 1 );

                if ( $n['html'] )
                {
                    $noutput_params['html'] = 1;
                }
                else
                {
                    $noutput_params['paragraphs'] = 1;
                    $noutput_params['nl2br'] = 1;
                }

                $n['content'] = $this->trellis->prepare_output( $n['content'], $noutput_params );
            }

            $this->trellis->skin->set_var( 'news', $news );
        }

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $n_total ),
                                                        'per_page'    => $this->trellis->cache->data['settings']['news']['page_amount'],
                                                        'url'        => $this->trellis->config['hd_url'] .'/index.php?page=news',
                                                        ) );

        $this->trellis->skin->set_var( 'page_links', $page_links );

        $this->trellis->skin->set_var( 'sub_tpl', 'news.tpl' );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=news">'. $this->trellis->lang['news'] .'</a>',
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['news'] ) );
    }

    #=======================================
    # @ Show News
    #=======================================

    function view_news($params=array())
    {
        #=============================
        # Grab News
        #=============================

        if ( ! $n = $this->trellis->func->news->get_single_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_news');

        #=============================
        # Format News
        #=============================

        $n['date_human'] = $this->trellis->td_timestamp( array( 'time' => $n['date'], 'format' => 'long' ) );

        $noutput_params = array( 'linkify' => 1 );

        if ( $n['html'] )
        {
            $noutput_params['html'] = 1;
        }
        else
        {
            $noutput_params['paragraphs'] = 1;
            $noutput_params['nl2br'] = 1;
        }

        $n['content'] = $this->trellis->prepare_output( $n['content'], $noutput_params );

        #=============================
        # Grab Comments
        #=============================

        $comments = $this->trellis->db->get( array(
                                                   'select'    => array(
                                                                     'c'    => 'all',
                                                                     'u'    => array( array( 'name' => 'uname' ) ),
                                                                     ),
                                                   'from'    => array( 'c' => 'news_comments' ),
                                                   'join'    => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'c' => 'uid', '=', 'u' => 'id' ) ) ),
                                                   'where'    => array( array( 'c' => 'nid' ), '=', $n['id'] ),
                                                   'order'    => array( 'date' => array( 'c' => 'asc' ) ),
                                            ), 'id' );

        if ( $comments )
        {
            foreach ( $comments as &$c )
            {
                #=============================
                # Format Comments
                #=============================

                if ( $c['staff'] ) $c['class_staff'] = 'staff';

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
                if ( $this->trellis->cache->data['settings']['news']['comments'] && $n['allow_comments'] && $c['uid'] == $this->trellis->user['id'] )
                {
                    if ( $this->trellis->user['g_news_com_edit'] ) $c['can_edit'] = 1;
                    if ( $this->trellis->user['g_news_com_delete'] ) $c['can_delete'] = 1;
                }
            }

            $this->trellis->skin->set_var( 'comments', $comments );
        }

        #=============================
        # Do Output
        #=============================

        if ( $params['error'] ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $params['error'] ] );
        if ( $params['alert'] ) $this->trellis->skin->set_var( 'alert', $this->trellis->lang[ 'alert_'. $params['alert'] ] );
        if ( $params['error_comment'] ) $this->trellis->skin->set_var( 'error_comment', $this->trellis->lang[ 'err_'. $params['error_comment'] ] );

        if ( $params['scroll'] ) $this->trellis->skin->set_var( 'scroll', $params['scroll'] );

        // Permissions for Templates
        if ( $this->trellis->cache->data['settings']['news']['comments'] && $this->trellis->user['g_news_comment'] && $n['allow_comments'] && $this->trellis->user['id'] ) $n['can_comment'] = 1;

        $this->trellis->skin->set_var( 'n', $n );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=news">'. $this->trellis->lang['news'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=news&amp;act=view&amp;id='. $n['id'] .'">'. $n['title'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'news_item.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['news'] .' :: '. $n['title'] ) );
    }

    #=======================================
    # @ Edit Comment
    #=======================================

    private function edit_comment($error="")
    {
        #=============================
        # Security Checks
        #=============================

        # TODO: could combine into one query =/ maybe edit get_single_comment_by_id to always grab basic news info

        if ( ! $c = $this->trellis->func->news->get_single_comment_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_comment');

        if ( ! $n = $this->trellis->func->news->get_single_by_id( array( 'id', 'title', 'allow_comments' ), $c['nid'] ) ) $this->trellis->skin->error('no_news');

        if ( ! $this->trellis->cache->data['settings']['news']['comments'] || ! $this->trellis->user['g_news_com_edit'] || ! $n['allow_comments'] || $c['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Do Output
        #=============================

        if ( $error ) $this->trellis->skin->set_var( 'error', $this->trellis->lang[ 'err_'. $error ] );

        $this->trellis->skin->set_var( 'c', $c );

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=news">'. $this->trellis->lang['news'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=news&amp;act=view&amp;id='. $n['id'] .'">'. $n['title'] .'</a>',
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=news&amp;act=editcomment&amp;id='. $c['id'] .'">'. $this->trellis->lang['edit_comment'] .'</a>',
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'news_edit_comment.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $this->trellis->lang['news'] .' :: '. $this->trellis->lang['edit_comment'] ) );
    }

    #=======================================
    # @ Do Add Comment
    #=======================================

    private function do_add_comment()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->load_functions('articles');

        if ( ! $this->trellis->input['message'] ) $this->view_news( array( 'error_comment' => 'no_comment' ) );

        if ( ! $n = $this->trellis->func->news->get_single_by_id( array( 'id', 'title', 'allow_comments' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_news');

        if ( ! $this->trellis->cache->data['settings']['news']['comments'] || ! $this->trellis->user['g_news_comment'] || ! $n['allow_comments'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Add Comment
        #=============================

        $db_array = array(
                          'nid'            => $n['id'],
                          'uid'            => $this->trellis->user['id'],
                          'message'        => $this->trellis->input['message'],
                          'date'        => time(),
                          'ipadd'        => $this->trellis->input['ip_address'],
                         );

        $comment_id = $this->trellis->func->news->add_comment( $db_array, $n['id'] );

        #=============================
        # Do Output
        #=============================

        $this->view_news( array( 'alert' => 'comment_added', 'scroll' => 'c'. $comment_id ) );
    }

    #=======================================
    # @ Do Edit Comment
    #=======================================

    private function do_edit_comment()
    {
        #=============================
        # Security Checks
        #=============================

        # TODO: could combine into one query =/ maybe edit get_single_comment_by_id to always grab basic news info

        if ( ! $c = $this->trellis->func->news->get_single_comment_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_comment');

        if ( ! $n = $this->trellis->func->news->get_single_by_id( array( 'id', 'title', 'allow_comments' ), $c['nid'] ) ) $this->trellis->skin->error('no_news');

        if ( ! $this->trellis->cache->data['settings']['news']['comments'] || ! $this->trellis->user['g_news_com_edit'] || ! $n['allow_comments'] || $c['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Update Comment
        #=============================

        $this->trellis->func->news->edit_comment( array( 'message' => $this->trellis->input['message'] ), $c['id'] );

        $this->trellis->log( 'user', "News Comment Edited ID #". $c['id'], 1, $c['id'] );

        #=============================
        # Do Output
        #=============================

        $this->trellis->input['id'] = $c['nid'];

        $this->view_news( array( 'alert' => 'comment_updated', 'scroll' => 'c'. $c['id'] ) );
    }

    #=======================================
    # @ Do Delete Comment
    #=======================================

    private function do_delete_comment()
    {
        #=============================
        # Security Checks
        #=============================

        # TODO: could combine into one query =/ maybe edit get_single_comment_by_id to always grab basic news info

        if ( ! $c = $this->trellis->func->news->get_single_comment_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_comment');

        if ( ! $n = $this->trellis->func->news->get_single_by_id( array( 'id', 'title', 'allow_comments' ), $c['nid'] ) ) $this->trellis->skin->error('no_news');

        if ( ! $this->trellis->cache->data['settings']['news']['comments'] || ! $this->trellis->user['g_news_com_delete'] || ! $n['allow_comments'] || $c['uid'] != $this->trellis->user['id'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Delete Comment
        #=============================

        $this->trellis->func->news->delete_comment( $c['id'], array( 'nid' => $c['nid'] ) );

        $this->trellis->log( 'user', "News Comment Deleted ID #". $c['id'], 2, $c['id'] );

        #=============================
        # Do Output
        #=============================

        $this->trellis->input['id'] = $c['nid'];

        $this->view_news( array( 'error' => 'comment_deleted' ) );
    }

}

?>