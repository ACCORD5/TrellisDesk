<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_articles {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        if ( ! $this->trellis->cache->data['settings']['kb']['enable'] ) $this->trellis->skin->error('kb_disabled');

        $this->trellis->check_perm( 'manage', 'articles' );

        $this->trellis->load_functions('articles');
        $this->trellis->load_lang('articles');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_articles();
            break;
            case 'view':
                $this->view_article();
            break;
            case 'add':
                $this->add_article();
            break;
            case 'edit':
                $this->edit_article();
            break;

            case 'doadd':
                $this->do_add();
            break;
            case 'doedit':
                $this->do_edit();
            break;
            case 'dodel':
                $this->do_delete();
            break;

            case 'togglecomments':
                $this->do_toggle_comments();
            break;
            case 'togglerating':
                $this->do_toggle_rating();
            break;

            case 'doaddcomment':
                $this->do_add_comment();
            break;
            case 'doeditcomment':
                $this->do_edit_comment();
            break;
            case 'dodelcomment':
                $this->do_delete_comment();
            break;

            case 'getcomment':
                $this->ajax_get_comment();
            break;

            default:
                $this->list_articles();
            break;
        }
    }

    #=======================================
    # @ List Articles
    #=======================================

    private function list_articles()
    {
        #=============================
        # Sorting Options
        #=============================

        $sort = $this->trellis->generate_sql_sort( array(
                                                         'default_sort' => 'title',
                                                         'default_order' => 'asc',
                                                         'base_url' => $this->generate_url( array( 'sort' => '', 'order' => '' ) ),
                                                         'options' => array(
                                                                             'id' => '{lang.id}',
                                                                             'title' => '{lang.title}',
                                                                             ),
                                                  )         );

        #=============================
        # Filter
        #=============================

        $filters = array();
        $sql_where = array();

        if ( $this->trellis->input['cat'] )
        {
            $filters[] = array( 'cid', '=', $this->trellis->input['cat'] );
        }

        #=============================
        # Grab Articles
        #=============================

        $perms = array();

        foreach ( $this->trellis->user['g_kb_perm'] as $cid => $p )
        {
            if ( $p ) $perms[] = $cid;
        }

        if ( $this->trellis->user['id'] != 1 )
        {
            $filters[] = array( 'cid', 'in', $perms );
        }

        foreach( $filters as $fdata )
        {
            if ( ! empty( $sql_where ) ) $fdata[] = 'and';

            $sql_where[] = $fdata;
        }

        if ( $this->trellis->user['id'] == 1 || ! empty( $perms ) )
        {
            $a_total = $this->trellis->func->articles->get( array( 'select' => array( 'id' ), 'where' => $sql_where ) );

            $articles = $this->trellis->func->articles->get( array( 'select' => array( 'id', 'title', 'description', 'views', 'comments' ), 'where' => $sql_where, 'order' => array( $sort['sort'] => $sort['order'] ), 'limit' => array( $this->trellis->input['st'], 15 ) ) );
        }

        $a_total = $this->trellis->func->articles->get( array( 'select' => array( 'id' ), 'where' => $sql_where ) );

        $article_rows = '';

        if ( ! $articles )
        {
            $article_rows .= "<tr><td class='bluecell-light' colspan='7'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=add'>{lang.no_articles}</a></strong></td></tr>";
        }
        else
        {
            foreach( $articles as $aid => $a )
            {
                if ( ! $a['description'] ) $a['description'] = '<i>{lang.no_description}</i>';

                $article_rows .= "<tr>
                                    <td class='bluecellthin-light'><strong>{$a['id']}</strong></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=view&amp;id={$a['id']}'>{$a['title']}</a></td>
                                    <td class='bluecellthin-light' style='font-weight: normal'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=view&amp;id={$a['id']}'>{$a['description']}</a></td>
                                    <td class='bluecellthin-light' align='center'>{$a['views']}</td>
                                    <td class='bluecellthin-light' align='center'>{$a['comments']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=edit&amp;id={$a['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=dodel&amp;id={$a['id']}' onclick='return confirmDelete({$a['id']})'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $a_total ),
                                                        'per_page'    => 15,
                                                        'url'        => $this->generate_url( array( 'st' => 0 ) ),
                                                        ) );

        $this->output .= "<script type='text/javascript'>
                        function confirmDelete(nid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_title}',
                                message: '{lang.dialog_delete_msg}',
                                yesButton: '{lang.dialog_delete_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=articles&act=dodel&id='+nid) },
                                noButton: '{lang.cancel}',
                                width: 350
                            }); return false;
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.articles_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='6%' align='left'>{$sort['link_id']}</th>
                            <th class='bluecellthin-th' width='23%' align='left'>{$sort['link_title']}</th>
                            <th class='bluecellthin-th' width='55%' align='left'>{lang.description}</th>
                            <th class='bluecellthin-th' width='5%' align='center'>{lang.views}</th>
                            <th class='bluecellthin-th' width='5%' align='center'>{lang.comments}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $article_rows ."
                        <tr>
                            <td class='bluecellthin-th-pages' colspan='7'>". $page_links ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $cat_items = array();
        $cats = array();

        foreach( $this->trellis->cache->data['categories'] as $id => $c )
        {
            if ( $this->trellis->input['cat'] )
            {
                if ( $c['parent_id'] == $this->trellis->input['cat'] ) $cats[ $id ] = $c;
            }
            else
            {
                if ( ! $c['parent_id'] ) $cats[ $id ] = $c;
            }
        }

        foreach( $cats as $id => $c )
        {
            if ( ! $this->trellis->user['g_kb_perm'][ $id ] && $this->trellis->user['id'] != 1 ) continue;

            $cat_items[ $id ] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;cat=". $id ."'>". $c['name'] ."</a>";
        }

        if ( ! empty( $cat_items ) ) $this->trellis->skin->add_sidebar_list( '{lang.categories}', $cat_items );

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_articles_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_articles_title}', '{lang.help_about_articles_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ View Article
    #=======================================

    private function view_article($params=array())
    {
        #=============================
        # Grab Article
        #=============================

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'description', 'content', 'rating_average', 'votes', 'views', 'comments', 'html', 'date', 'modified', 'allow_comments', 'allow_rating' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        #=============================
        # Grab Comments
        #=============================

        # New get() db function

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

        #=============================
        # Format
        #=============================

        if ( ! $check_comments = $this->trellis->skin->setting_alert( array( 'check' => $this->trellis->cache->data['settings']['kb']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments_global}' ) ) )
        {
            $check_comments = $this->trellis->skin->setting_alert( array( 'check' => $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_comments'], 'for' => 1, 'msg' => '{lang.warn_comments_cat}' ) );
        }

        if ( ! $check_rating = $this->trellis->skin->setting_alert( array( 'check' => $this->trellis->cache->data['settings']['kb']['rating'], 'for' => 1, 'msg' => '{lang.warn_rating_global}' ) ) )
        {
            $check_rating = $this->trellis->skin->setting_alert( array( 'check' => $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_rating'], 'for' => 1, 'msg' => '{lang.warn_rating_cat}' ) );
        }

        $a['category_human'] = '';

        if ( $this->trellis->cache->data['categories'][ $a['cid'] ]['parent_id'] ) $a['category_human'] .= $this->trellis->cache->data['categories'][ $this->trellis->cache->data['categories'][ $a['cid'] ]['parent_id'] ]['name'] .' > ';

        $a['category_human'] .= $this->trellis->cache->data['categories'][ $a['cid'] ]['name'];

        $a['date_human'] = $this->trellis->td_timestamp( array( 'time' => $a['date'], 'format' => 'long' ) );

        ( $a['modified'] ) ? $a['modified_human'] = $this->trellis->td_timestamp( array( 'time' => $a['modified'], 'format' => 'long' ) ) : $a['modified_human'] = '--';

        $a['rating_visual'] = $this->trellis->skin->rating_visual( $a['rating_average'] );

        if ( ! $a['description'] ) $a['description'] = '<i>{lang.no_description}</i>';

        ( $a['allow_comments'] ) ? $a['comments_status'] = '({lang.enabled})' : $a['comments_status'] = '({lang.disabled})';
        ( $a['allow_rating'] ) ? $a['rating_status'] = '({lang.enabled})' : $a['rating_status'] = '({lang.disabled})';

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
        # Do Output
        #=============================

        if ( $params['error'] )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $params['error'] .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->output .= "<script type='text/javascript'>
                        //<![CDATA[
                        function confirmDeleteArticle(aid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_article_title}',
                                message: '{lang.dialog_delete_article_msg}',
                                yesButton: '{lang.dialog_delete_article_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=articles&act=dodel&id='+aid) },
                                noButton: '{lang.cancel}',
                                width: 350
                            }); return false;
                        }
                        function confirmDeleteComment(cid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_comment_title}',
                                message: '{lang.dialog_delete_comment_msg}',
                                yesButton: '{lang.dialog_delete_comment_button}',
                                yesAction: function() { inlineCommentDelete(cid); },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        //]]>
                        </script>
                        ". $this->trellis->skin->start_ticket_details( '{lang.article_num}'. $a['id'] .': '. $a['title'] ) ."
                        <input type='hidden' id='aid' name='aid' value='{$a['id']}' />
                        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                            <tr>
                                <td class='cardcell-light' width='20%'>{lang.article_id}</td>
                                <td class='cardcell-dark' width='30%'>{$a['id']}</td>
                                <td class='cardcell-light' width='20%'>{lang.views}</td>
                                <td class='cardcell-dark' width='30%'>{$a['views']}</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.category}</td>
                                <td class='cardcell-dark'>{$a['category_human']}</td>
                                <td class='cardcell-light'>{lang.comments}</td>
                                <td class='cardcell-dark'>{$a['comments']}&nbsp;&nbsp;{$a['comments_status']}". $check_comments ."</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.date}</td>
                                <td class='cardcell-dark'>{$a['date_human']}</td>
                                <td class='cardcell-light'>{lang.rating}</td>
                                <td class='cardcell-dark'>{$a['rating_visual']}&nbsp;&nbsp;({$a['votes']})&nbsp;&nbsp;{$a['rating_status']}". $check_rating ."</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.modified}</td>
                                <td class='cardcell-dark'>{$a['modified_human']}</td>
                                <td class='cardcell-light'>&nbsp;</td>
                                <td class='cardcell-dark'>&nbsp;</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.description}</td>
                                <td class='cardcell-dark' colspan='3' style='line-height:1.4;padding: 6px 10px;'>{$a['description']}</td>
                            </tr>
                        </table>
                        ". $this->trellis->skin->end_ticket_details() ."
                        <div id='ticketroll'>
                            ". $this->trellis->skin->group_title('{lang.article_content}') ."
                            <div class='rollstart'>
                                {$a['content']}
                            </div>";

        if ( ! empty( $comments ) )
        {
            foreach( $comments as $c )
            {
                ( $c['staff'] ) ? $cclass = 'staff': $cclass = 'customer';

                $c['date'] = $this->trellis->td_timestamp( array( 'time' => $c['date'], 'format' => 'long' ) );

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

                if ( $c['signature'] ) $c['message'] .= '<br /><br />'. $this->trellis->prepare_output( $c['usignature'], array( 'html' => $c['sig_html'] ) ); # TODO: sigs on comments?

                $this->output .= "<div id='c{$c['id']}'>
                                <div class='bar{$cclass}'>";

                $comment_edit = 0;
                $comment_delete = 0;
                $comment_javascript_html = '';

                if ( $c['html'] ) $comment_javascript_html = 'Html';

                if ( $this->trellis->user['g_kb_com_edit_all'] || ( $this->trellis->user['g_kb_com_edit'] && $c['uid'] == $this->trellis->user['id'] ) ) $comment_edit = 1;
                if ( $this->trellis->user['g_kb_com_delete_all'] || ( $this->trellis->user['g_kb_com_delete'] && $c['uid'] == $this->trellis->user['id'] ) ) $comment_delete = 1;

                if ( $comment_edit || $comment_delete ) $this->output .= "<div class='barright'>";

                if ( $comment_edit ) $this->output .= "<span id='cedit_". $c['id'] ."' style='cursor:pointer' onclick='inlineCommentEdit{$comment_javascript_html}(". $c['id'] .")'><img src='<! IMG_DIR !>/icon_page_edit.png' alt='{lang.edit}' />{lang.edit}</span><span id='csave_". $c['id'] ."' style='display:none;cursor:pointer' onclick='inlineCommentSave{$comment_javascript_html}(". $c['id'] .")'><img src='<! IMG_DIR !>/icon_page_edit.png' alt='{lang.save_edit}' />{lang.save_edit}</span>";

                if ( $comment_delete )
                {
                    if ( $comment_edit ) $this->output .= " &nbsp; ";

                    $this->output .= "<span id='cdelete_". $c['id'] ."' style='cursor:pointer' onclick='return confirmDeleteComment(". $c['id'] .")'><img src='<! IMG_DIR !>/icon_page_delete.png' alt='{lang.delete}' />{lang.delete}</span>";
                }

                if ( $comment_edit || $comment_delete ) $this->output .= "</div>";

                $this->output .= "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=users&amp;act=view&amp;id={$c['uid']}' title='{$c['ipadd']}'><strong>{$c['uname']}</strong></a> -- {$c['date']}</div>
                                <div class='roll{$cclass}' id='cm{$c['id']}'>
                                    {$c['message']}
                                </div>
                            </div>";
            }
        }

        if ( $this->trellis->cache->data['settings']['kb']['comments'] && $this->trellis->user['g_kb_comment'] && $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_comments'] && $a['allow_comments'] )
        {
            if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['kb']['rte'] )
            {
                $this->output .= $this->trellis->skin->tinymce_js( 'message' );

                $html = 1;
            }
            else
            {
                $html = 0;
            }

            if ( $params['comment_error'] )
            {
                $this->output .= '<br />'. $this->trellis->skin->error_wrap( '{lang.error_'. $params['comment_error'] .'}' );
            }

            #( $this->trellis->user['sig_auto'] ) ? $sig_checked = " checked='checked'" : $sig_checked = '';

            $this->output .= "<form action='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=doaddcomment&amp;id={$a['id']}' method='post'>
                                <input type='hidden' id='html' name='html' value='{$html}' />
                                <div class='slatebox'>{lang.post_a_comment}</div>
                                <div class='rollpost'>
                                ". $this->trellis->skin->textarea( array( 'name' => 'message', 'cols' => 80, 'rows' => 8, 'width' => '98%', 'height' => '200px' ) ) ."
                                </div>
                                <div class='formtail'>". $this->trellis->skin->submit_button( 'add_comment', '{lang.button_add_comment}' ) ."</div>
                                </form>";
        }

        $this->output .= "</div>
            <script language='javascript' type='text/javascript'>
            //<![CDATA[
            function inlineCommentEditHtml(cid) {
                $.get('admin.php?section=manage&page=articles&act=getcomment',
                    { id: cid },
                    function(data) {
                        if (data != 0) {
                            tinyMCE.init({
                                mode : 'exact',
                                theme : 'advanced',
                                elements : 'c'+cid,
                                content_css : '<! TD_URL !>/includes/css/tinymce.css',
                                plugins : 'inlinepopups,safari,spellchecker',
                                dialog_type : 'modal',
                                theme_advanced_toolbar_location : 'top',
                                theme_advanced_toolbar_align : 'left',
                                theme_advanced_path_location : 'bottom',
                                theme_advanced_disable : 'styleselect,formatselect',
                                theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,separator,forecolor,backcolor,separator,bullist,numlist,separator,outdent,indent,separator,link,unlink,image,separator,undo,redo,separator,spellchecker,separator,removeformat,cleanup,code',
                                theme_advanced_buttons2 : '',
                                theme_advanced_buttons3 : '',
                                theme_advanced_resize_horizontal : false,
                                theme_advanced_resizing : true,
                                setup: function(ed) {
                                    ed.onInit.add( function(ed) {
                                        ed.setContent(data);
                                    });
                                }
                            });

                            $('#cedit_'+cid).hide();
                            $('#cdelete_'+cid).hide();
                            $('#csave_'+cid).show();
                        }
                    });
            }
            function inlineCommentSaveHtml(cid) {
                $.post('admin.php?section=manage&page=articles&act=doeditcomment',
                    { id: cid, message: tinyMCE.get('c'+cid).getContent(), html: 1 },
                    function(data) {
                        if (data != 0) {
                            tinyMCE.get('c'+cid).setContent(data);
                            tinyMCE.get('c'+cid).remove();

                            $('#csave_'+cid).hide();
                            $('#cedit_'+cid).show();
                            $('#cdelete_'+cid).show();
                        }
                    });
            }
            function inlineCommentEdit(cid) {
                $.get('admin.php?section=manage&page=articles&act=getcomment',
                    { id: cid },
                    function(data) {
                        if (data != 0) {
                            $('#c'+cid).html(\"<textarea id='ce\"+cid+\"' name='ce\"+cid+\"' cols='80' rows='7' style='width:98%'>\"+data+\"</textarea>\");

                            $('#cedit_'+cid).hide();
                            $('#cdelete_'+cid).hide();
                            $('#csave_'+cid).show();
                        }
                    });
            }
            function inlineCommentSave(cid) {
                $.post('admin.php?section=manage&page=articles&act=doeditcomment',
                    { id: cid, message: $('#ce'+cid).val() },
                    function(data) {
                        if (data != 0) {
                            $('#c'+cid).html(data);

                            $('#csave_'+cid).hide();
                            $('#cedit_'+cid).show();
                            $('#cdelete_'+cid).show();
                        }
                    });
            }
            function inlineCommentDelete(cid) {
                $.post('admin.php?section=manage&page=articles&act=dodelcomment',
                    { id: cid },
                    function(data) {
                        if (data != 0) {
                            $('#cc'+cid).hide('blind');
                        }
                    });
            }
            //]]>
            </script>";

        $menu_items = array( array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=articles' ) );

        if ( $this->trellis->check_perm( 'manage', 'articles', 'add', 0 ) ) $menu_items[] = array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=add' );
        if ( $this->trellis->check_perm( 'manage', 'articles', 'edit', 0 ) ) $menu_items[] = array( 'edit', '{lang.menu_edit}', '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=edit&amp;id='. $a['id'] );

        # TODO: repeated code below... clean up

        if ( $this->trellis->check_perm( 'manage', 'articles', 'edit', 0 ) )
        {
            if ( $a['allow_comments'] )
            {
                $menu_items[] = array( 'balloon', '{lang.menu_disable_comments}'. $check_comments, '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=togglecomments&amp;id='. $a['id'] );
            }
            else
            {
                $menu_items[] = array( 'balloon', '{lang.menu_enable_comments}'. $check_comments, '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=togglecomments&amp;id='. $a['id'] );
            }

            if ( $a['allow_rating'] )
            {
                $menu_items[] = array( 'star', '{lang.menu_disable_rating}'. $check_rating, '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=togglerating&amp;id='. $a['id'] );
            }
            else
            {
                $menu_items[] = array( 'star', '{lang.menu_enable_rating}'. $check_rating, '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=togglerating&amp;id='. $a['id'] );
            }
        }

        if ( $this->trellis->check_perm( 'manage', 'articles', 'delete', 0 ) ) $menu_items[] = array( 'circle_delete', '{lang.menu_delete}', '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=dodel&amp;id='. $a['id'], 'return confirmDeleteArticle('. $a['id'] .')' );
        if ( $this->trellis->check_perm( 'manage', 'settings', null, 0 ) ) $menu_items[] = array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_articles_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Article
    #=======================================

    private function add_article($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'articles', 'add' );

        if ( empty( $this->trellis->cache->data['categories'] ) ) $this->skin->error('no_categories');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->trellis->load_functions('drop_downs');

        if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['kb']['rte'] )
        {
            $this->output .= $this->trellis->skin->tinymce_js( 'content' );

            $html = 1;
        }
        else
        {
            $html = 0;
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=doadd", 'add_article', 'post' ) ."
                        <input type='hidden' id='html' name='html' value='{$html}' />
                        ". $this->trellis->skin->start_group_table( '{lang.adding_article}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title}', $this->trellis->skin->textfield( 'title' ), 'a', '18%', '82%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.category}', "<select name='cid' id='cid'>". $this->trellis->func->drop_downs->cat_drop( array( 'childs' => 1, 'select' => $this->trellis->input['cid'] ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description} '. $this->trellis->skin->help_tip('{lang.tip_description}'), $this->trellis->skin->textarea( 'description', '', '', 0, 60, 2 ), 'a' ) ."
                        <tr>
                            <td class='option1'>{lang.options}</td>
                            <td class='option1' style='font-weight: normal'>
                                ". $this->trellis->skin->checkbox( array( 'name' => 'allow_comments', 'title' => '{lang.allow_comments}', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments_global}' ) ) ) ."&nbsp;&nbsp;
                                ". $this->trellis->skin->checkbox( array( 'name' => 'allow_rating', 'title' => '{lang.allow_rating}', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['rating'], 'for' => 1, 'msg' => '{lang.warn_rating_global}' ) ) ) ."
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2' class='option2'>". $this->trellis->skin->textarea( array( 'name' => 'content', 'cols' => 80, 'rows' => 10, 'width' => '98%', 'height' => '230px' ) ) ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_article}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'title'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_title}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('title');

        $menu_items = array( array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=articles' ) );

        if ( $this->trellis->check_perm( 'manage', 'settings', null, 0 ) ) $menu_items[] = array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_articles_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Article
    #=======================================

    private function edit_article($error="")
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'articles', 'edit' );

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'description', 'content', 'allow_comments', 'allow_rating' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;

            $cid = $this->trellis->input['cid'];
        }
        else
        {
            $cid = $a['cid'];
        }

        $this->trellis->load_functions('drop_downs');

        if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['kb']['rte'] )
        {
            $this->output .= $this->trellis->skin->tinymce_js( 'content' );

            $html = 1;
        }
        else
        {
            $html = 0;
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=doedit&amp;id={$a['id']}", 'edit_article', 'post' ) ."
                        <input type='hidden' id='html' name='html' value='{$html}' />
                        ". $this->trellis->skin->start_group_table( '{lang.editing_article} '. $a['title'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title}', $this->trellis->skin->textfield( 'title', $a['title'] ), 'a', '18%', '82%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.category}', "<select name='cid' id='cid'>". $this->trellis->func->drop_downs->cat_drop( array( 'childs' => 1, 'select' => $cid ) ) ."</select>", 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description} '. $this->trellis->skin->help_tip('{lang.tip_description}'), $this->trellis->skin->textarea( 'description', $a['description'], '', 0, 60, 2 ), 'a' ) ."
                        <tr>
                            <td class='option1'>{lang.options}</td>
                            <td class='option1' style='font-weight: normal'>
                                ". $this->trellis->skin->checkbox( array( 'name' => 'allow_comments', 'title' => '{lang.allow_comments}', 'value' => $a['allow_comments'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments_global}' ) ) ) ."&nbsp;&nbsp;
                                ". $this->trellis->skin->checkbox( array( 'name' => 'allow_rating', 'title' => '{lang.allow_rating}', 'value' => $a['allow_rating'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['rating'], 'for' => 1, 'msg' => '{lang.warn_rating_global}' ) ) ) ."
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2' class='option2'>". $this->trellis->skin->textarea( array( 'name' => 'content', 'value' => $a['content'], 'cols' => 80, 'rows' => 10, 'width' => '98%', 'height' => '230px' ) ) ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_article}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'title'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_title}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('title');

        $menu_items = array( array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=articles' ) );

        if ( $this->trellis->check_perm( 'manage', 'articles', 'add', 0 ) ) $menu_items[] = array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;act=add' );
        if ( $this->trellis->check_perm( 'manage', 'settings', null, 0 ) ) $menu_items[] = array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_articles_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'articles', 'add' );

        if ( ! $this->trellis->input['title'] ) $this->add_article('no_title');
        if ( ! $this->trellis->input['content'] ) $this->add_article('no_content');

        #=============================
        # Add Article
        #=============================

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $this->trellis->input['cid'] ] ) $this->trellis->skin->error('no_category');

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['kb']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'cid'                => $this->trellis->input['cid'],
                          'title'            => $this->trellis->input['title'],
                          'description'        => $this->trellis->input['description'],
                          'content'            => $this->trellis->input['content'],
                          'allow_comments'    => $this->trellis->input['allow_comments'],
                          'allow_rating'    => $this->trellis->input['allow_rating'],
                          'html'            => $html,
                          'date'            => time(),
                         );

        $article_id = $this->trellis->func->articles->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'article_added', $this->trellis->input['title'] ), 'type' => 'kb', 'content_type' => 'article', 'content_id' => $article_id ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_article_added'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $article_id ) );
    }

    #=======================================
    # @ Do Edit
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'articles', 'edit' );

        if ( ! $this->trellis->input['title'] ) $this->edit_article('no_title');
        if ( ! $this->trellis->input['content'] ) $this->edit_article('no_content');

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        #=============================
        # Edit Article
        #=============================

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $this->trellis->input['cid'] ] ) $this->trellis->skin->error('no_category');

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['kb']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'cid'                => $this->trellis->input['cid'],
                          'title'            => $this->trellis->input['title'],
                          'description'        => $this->trellis->input['description'],
                          'content'            => $this->trellis->input['content'],
                          'allow_comments'    => $this->trellis->input['allow_comments'],
                          'allow_rating'    => $this->trellis->input['allow_rating'],
                          'html'            => $html,
                          'modified'        => time(),
                         );

        $this->trellis->func->articles->edit( $db_array, $a['id'] );

        $this->trellis->log( array( 'msg' => array( 'article_edited', $this->trellis->input['title'] ), 'type' => 'kb', 'content_type' => 'article', 'content_id' => $a['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_article_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $a['id'] ) );
    }

    #=======================================
    # @ Do Delete
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'articles', 'delete' );

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        #=============================
        # DELETE Article
        #=============================

        $this->trellis->func->articles->delete( $a['id'] );

        $this->trellis->log( array( 'msg' => array( 'article_deleted', $a['title'] ), 'type' => 'kb', 'level' => 2 ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_article_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null, 'cat' => $a['cid'] ) );
    }

    #=======================================
    # @ Do Toggle Comments
    #=======================================

    private function do_toggle_comments()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'articles', 'edit' );

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'allow_comments' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        #=============================
        # Edit Article
        #=============================

        ( $a['allow_comments'] ) ? $allow_comments = 0 : $allow_comments = 1;

        $this->trellis->func->articles->edit( array( 'allow_comments' => $allow_comments ), $a['id'] );

        if ( $allow_comments )
        {
            $this->trellis->log( array( 'msg' => array( 'articlecom_enabled', $a['title'] ), 'type' => 'kb', 'content_type' => 'article', 'content_id' => $a['id'] ) );

            $this->trellis->send_message( 'alert', $this->trellis->lang['alert_article_com_enabled'] );
        }
        else
        {
            $this->trellis->log( array( 'msg' => array( 'articlecom_disabled', $a['title'] ), 'type' => 'kb', 'level' => 2, 'content_type' => 'article', 'content_id' => $a['id'] ) );

            $this->trellis->send_message( 'alert', $this->trellis->lang['alert_article_com_disabled'] );
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $a['id'] ) );
    }

    #=======================================
    # @ Do Toggle Rating
    #=======================================

    private function do_toggle_rating()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'articles', 'edit' );

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'allow_rating' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        #=============================
        # Edit Article
        #=============================

        ( $a['allow_rating'] ) ? $allow_rating = 0 : $allow_rating = 1;

        $this->trellis->func->articles->edit( array( 'allow_rating' => $allow_rating ), $a['id'] );

        if ( $allow_rating )
        {
            $this->trellis->log( array( 'msg' => array( 'articlerate_enabled', $a['title'] ), 'type' => 'kb', 'content_type' => 'article', 'content_id' => $a['id'] ) );

            $this->trellis->send_message( 'alert', $this->trellis->lang['alert_article_rate_enabled'] );
        }
        else
        {
            $this->trellis->log( array( 'msg' => array( 'articlerate_disabled', $a['title'] ), 'type' => 'kb', 'level' => 2, 'content_type' => 'article', 'content_id' => $a['id'] ) );

            $this->trellis->send_message( 'alert', $this->trellis->lang['alert_article_rate_disabled'] );
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $a['id'] ) );
    }

    #=======================================
    # @ Do Add Comment
    #=======================================

    private function do_add_comment()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['message'] ) $this->view_article( array( 'comment_error' => 'no_message' ) );

        if ( ! $a = $this->trellis->func->articles->get_single_by_id( array( 'id', 'cid', 'title', 'allow_comments' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_article');

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $a['cid'] ] ) $this->trellis->skin->error('no_article');

        if ( ! $this->trellis->cache->data['settings']['kb']['comments'] || ! $this->trellis->user['g_kb_comment'] || ! $this->trellis->cache->data['categories'][ $a['cid'] ]['allow_comments'] || ! $a['allow_comments'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Add Comment
        #=============================

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['kb']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'aid'            => $a['id'],
                          'uid'            => $this->trellis->user['id'],
                          'message'        => $this->trellis->input['message'],
                          'staff'        => 1,
                          'html'        => $html,
                          'date'        => time(),
                          'ipadd'        => $this->trellis->input['ip_address'],
                         );

        $comment_id = $this->trellis->func->articles->add_comment( $db_array, $a['id'] );

        $this->trellis->log( array( 'msg' => array( 'articlecom_added', $a['title'] ), 'type' => 'kb', 'content_type' => 'articlecom', 'content_id' => $comment_id ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $a['id'] ), '#c'. $comment_id );
    }

    #=======================================
    # @ Do Edit Comment
    #=======================================

    private function do_edit_comment()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['message'] ) $this->trellis->skin->ajax_output( '0' );

        $c = $this->trellis->db->get_single( array(
                                                         'select'    => array(
                                                                                'c' => array( 'id', 'uid' ),
                                                                                'a' => array( 'cid', 'title' ),
                                                                                ),
                                                         'from'        => array( 'c' => 'article_comments' ),
                                                         'join'        => array( array( 'from' => array( 'a' => 'articles' ), 'where' => array( 'c' => 'aid', '=', 'a' => 'id' ) ) ),
                                                         'where'    => array( array( 'c' => 'id' ), '=', $this->trellis->input['id'] ),
                                                         'limit'    => array( 0, 1 ),
                                                  )      );

        if ( ! $c ) $this->trellis->skin->ajax_output( '0' );

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $c['cid'] ] ) $this->trellis->skin->ajax_output( '0' );

        # TODO Double Check Permission Logic Below...

        if ( ! $this->trellis->user['g_kb_com_edit_all'] && ! ( $this->trellis->user['g_kb_com_edit'] && $c['uid'] == $this->trellis->user['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        #=============================
        # Edit Reply
        #=============================

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['kb']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'message'        => $this->trellis->input['message'],
                          'html'        => $html,
                         );

        $this->trellis->func->articles->edit_comment( $db_array, $c['id'] );

        $this->trellis->log( array( 'msg' => array( 'articlecom_edited', $c['title'] ), 'type' => 'kb', 'content_type' => 'articlecom', 'content_id' => $c['id'] ) );

        #=============================
        # Do Output
        #=============================

        $coutput_params = array( 'linkify' => 1 );

        if ( $this->trellis->input['html'] )
        {
            $coutput_params['html'] = 1;
        }
        else
        {
            $coutput_params['paragraphs'] = 1;
            $coutput_params['nl2br'] = 1;
        }

        $rmessage = $this->trellis->prepare_output( $this->trellis->input['message'], $coutput_params );

        /*if ( $r['signature'] )
        {
            $rmessage .= '<br /><br />'. $this->trellis->prepare_output( $r['usignature'], array( 'html' => $r['sig_html'] ) );
        }*/

        $this->trellis->skin->ajax_output( $rmessage );
    }

    #=======================================
    # @ Do Delete Comment
    #=======================================

    private function do_delete_comment()
    {
        #=============================
        # Security Checks
        #=============================

        $c = $this->trellis->db->get_single( array(
                                                         'select'    => array(
                                                                                'c' => array( 'id', 'aid', 'uid' ),
                                                                                'a' => array( 'cid', 'title' ),
                                                                                ),
                                                         'from'        => array( 'c' => 'article_comments' ),
                                                         'join'        => array( array( 'from' => array( 'a' => 'articles' ), 'where' => array( 'c' => 'aid', '=', 'a' => 'id' ) ) ),
                                                         'where'    => array( array( 'c' => 'id' ), '=', $this->trellis->input['id'] ),
                                                         'limit'    => array( 0, 1 ),
                                                  )      );

        if ( ! $c ) $this->trellis->skin->ajax_output( '0' );

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $c['cid'] ] ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->trellis->user['g_kb_com_delete_all'] && ! ( $this->trellis->user['g_kb_com_delete'] && $c['uid'] == $this->trellis->user['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        #=============================
        # DELETE Comment
        #=============================

        $this->trellis->func->articles->delete_comment( $c['id'], array( 'aid' => $c['aid'] ) );

        $this->trellis->log( array( 'msg' => array( 'articlecom_deleted', $c['title'] ), 'type' => 'kb', 'level' => 2, 'content_type' => 'article', 'content_id' => $c['aid'] ) );

        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->ajax_output( '1' );
    }

    #=======================================
    # @ AJAX Get Comment
    #=======================================

    private function ajax_get_comment()
    {
        $c = $this->trellis->db->get_single( array(
                                                         'select'    => array(
                                                                                'c' => array( 'id', 'aid', 'uid', 'message', 'html' ),
                                                                                'a' => array( 'cid' ),
                                                                                ),
                                                         'from'        => array( 'c' => 'article_comments' ),
                                                         'join'        => array( array( 'from' => array( 'a' => 'articles' ), 'where' => array( 'c' => 'aid', '=', 'a' => 'id' ) ) ),
                                                         'where'    => array( array( 'c' => 'id' ), '=', $this->trellis->input['id'] ),
                                                         'limit'    => array( 0, 1 ),
                                                  )      );

        if ( ! $c ) $this->trellis->skin->ajax_output( '0' );

        if ( $this->trellis->user['id'] != 1 && ! $this->trellis->user['g_kb_perm'][ $c['cid'] ] ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->trellis->user['g_kb_com_edit_all'] && ! ( $this->trellis->user['g_kb_com_edit'] && $c['uid'] == $this->trellis->user['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        $this->trellis->skin->ajax_output( $this->trellis->prepare_output( $c['message'], array( 'html' => $c['html'] ) ) );
    }

    #=======================================
    # @ Generate URL
    #=======================================

    private function generate_url($params=array())
    {
        $url = '<! TD_URL !>/admin.php?section=manage&amp;page=articles';

        if ( ! isset( $params['cat'] ) ) $params['cat'] = $this->trellis->input['cat'];
        if ( ! isset( $params['sort'] ) ) $params['sort'] = $this->trellis->input['sort'];
        if ( ! isset( $params['order'] ) ) $params['order'] = $this->trellis->input['order'];
        if ( ! isset( $params['st'] ) ) $params['st'] = $this->trellis->input['st'];

        if ( $params['cat'] ) $url .= '&amp;cat='. $params['cat'];

        if ( $params['sort'] ) $url .= '&amp;sort='. $params['sort'];

        if ( $params['order'] ) $url .= '&amp;order='. $params['order'];

        if ( $params['st'] ) $url .= '&amp;st='. $params['st'];

        return $url;
    }

}

?>