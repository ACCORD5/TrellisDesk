<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_news {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        if ( ! $this->trellis->cache->data['settings']['news']['enable'] ) $this->trellis->skin->error('news_disabled');

        $this->trellis->check_perm( 'manage', 'news' );

        $this->trellis->load_functions('news');
        $this->trellis->load_lang('news');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_news();
            break;
            case 'view':
                $this->view_news();
            break;
            case 'add':
                $this->add_news();
            break;
            case 'edit':
                $this->edit_news();
            break;

            case 'togglecomments':
                $this->do_toggle_comments();
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
                $this->list_news();
            break;
        }
    }

    #=======================================
    # @ List News
    #=======================================

    private function list_news()
    {
        #=============================
        # Sorting Options
        #=============================

        $sort = $this->trellis->generate_sql_sort( array(
                                                         'default_sort' => 'id',
                                                         'default_order' => 'desc',
                                                         'base_url' => $this->generate_url( array( 'sort' => '', 'order' => '' ) ),
                                                         'options' => array(
                                                                             'id' => '{lang.id}',
                                                                             'title' => '{lang.title}',
                                                                             ),
                                                  )         );

        #=============================
        # Grab News
        #=============================

        $n_total = $this->trellis->func->news->get( array( 'select' => array( 'id' ) ) );

        $news_rows = '';

        if ( ! $news = $this->trellis->func->news->get( array( 'select' => array( 'id', 'title', 'content', 'excerpt' ), 'order' => array( $sort['sort'] => $sort['order'] ), 'limit' => array( $this->trellis->input['st'], 15 ) ) ) )
        {
            $news_rows .= "<tr><td class='bluecell-light' colspan='5'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=add'>{lang.no_news}</a></strong></td></tr>";
        }
        else
        {
            foreach( $news as $nid => $n )
            {
                if ( ! $n['excerpt'] ) $n['excerpt'] = '<i>{lang.no_excerpt}</i>';

                $news_rows .= "<tr>
                                    <td class='bluecellthin-light'><strong>{$n['id']}</strong></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=view&amp;id={$n['id']}'>{$n['title']}</a></td>
                                    <td class='bluecellthin-light' style='font-weight: normal'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=view&amp;id={$n['id']}'>{$n['excerpt']}</a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=edit&amp;id={$n['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=dodel&amp;id={$n['id']}' onclick='return confirmDelete({$n['id']})'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $page_links = $this->trellis->page_links( array(
                                                        'total'        => count( $n_total ),
                                                        'per_page'    => 15,
                                                        'url'        => $this->generate_url( array( 'st' => 0 ) ),
                                                        ) );

        $this->output .= "<script type='text/javascript'>
                        function confirmDelete(nid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_news_title}',
                                message: '{lang.dialog_delete_news_msg}',
                                yesButton: '{lang.dialog_delete_news_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=news&act=dodel&id='+nid) },
                                noButton: '{lang.cancel}',
                                width: 350
                            }); return false;
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.news_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='6%' align='left'>{$sort['link_id']}</th>
                            <th class='bluecellthin-th' width='28%' align='left'>{$sort['link_title']}</th>
                            <th class='bluecellthin-th' width='60%' align='left'>{lang.description}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $news_rows ."
                        <tr>
                            <td class='bluecellthin-th-pages' colspan='5'>". $page_links ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=add' ),
                            array( 'mails_arrow', '{lang.menu_mass_email}', '<! TD_URL !>/admin.php?section=tools&amp;page=email&amp;act=mass' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=news' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=news' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_news_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_news_title}', '{lang.help_about_news_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ View News
    #=======================================

    private function view_news($params=array())
    {
        #=============================
        # Grab News
        #=============================

        $n = $this->trellis->db->get_single( array(
                                                         'select'    => array(
                                                                                'n' => array( 'id', 'uid', 'title', 'excerpt', 'content', 'views', 'comments', 'html', 'date', 'allow_comments' ),
                                                                                'u' => array( array( 'name' => 'uname' ) ),
                                                                                ),
                                                         'from'        => array( 'n' => 'news' ),
                                                         'join'        => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'n' => 'uid', '=', 'u' => 'id' ) ) ),
                                                         'where'    => array( array( 'n' => 'id' ), '=', $this->trellis->input['id'] ),
                                                         'limit'    => array( 0, 1 ),
                                                  )      );

        if ( ! $n ) $this->trellis->skin->error('no_news');

        #=============================
        # Grab Comments
        #=============================

        # New get() db function

        $comments = $this->trellis->db->get( array(
                                                         'select'    => array(
                                                                                'c' => 'all',
                                                                                'u' => array( array( 'name' => 'uname' ) ),
                                                                                ),
                                                         'from'        => array( 'c' => 'news_comments' ),
                                                         'join'        => array( array( 'from' => array( 'u' => 'users' ), 'where' => array( 'c' => 'uid', '=', 'u' => 'id' ) ) ),
                                                         'where'    => array( array( 'c' => 'nid' ), '=', $n['id'] ),
                                                         'order'    => array( 'date' => array( 'c' => 'asc' ) ),
                                                  ), 'id' );

        #=============================
        # Format
        #=============================

        $n['date_human'] = $this->trellis->td_timestamp( array( 'time' => $n['date'], 'format' => 'long' ) );

        if ( ! $n['excerpt'] ) $n['excerpt'] = '<i>{lang.no_excerpt}</i>';

        ( $n['allow_comments'] ) ? $n['comments_status'] = '({lang.enabled})' : $n['comments_status'] = '({lang.disabled})';

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
        # Do Output
        #=============================

        if ( $params['error'] )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $params['error'] .'}' );
            $this->trellis->skin->preserve_input = 1;
        }
        elseif ( $params['alert'] )
        {
            $this->output .= $this->trellis->skin->alert_wrap( '{lang.alert_'. $params['alert'] .'}' );
        }

        $this->output .= "<script type='text/javascript'>
                        //<![CDATA[
                        function confirmDeleteNews(aid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_news_title}',
                                message: '{lang.dialog_delete_news_msg}',
                                yesButton: '{lang.dialog_delete_news_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=news&act=dodel&id='+aid) },
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
                        ". $this->trellis->skin->start_ticket_details( '{lang.news_num}'. $n['id'] .': '. $n['title'] ) ."
                        <input type='hidden' id='nid' name='nid' value='{$n['id']}' />
                        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                            <tr>
                                <td class='cardcell-light' width='20%'>{lang.news_id}</td>
                                <td class='cardcell-dark' width='30%'>{$n['id']}</td>
                                <td class='cardcell-light' width='20%'>{lang.views}</td>
                                <td class='cardcell-dark' width='30%'>{$n['views']}</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.date}</td>
                                <td class='cardcell-dark'>{$n['date_human']}</td>
                                <td class='cardcell-light'>{lang.comments}</td>
                                <td class='cardcell-dark'>{$n['comments']}&nbsp;&nbsp;{$n['comments_status']}". $this->trellis->skin->setting_alert( array( 'check' => $this->trellis->cache->data['settings']['news']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments}' ) ) ."</td>
                            </tr>
                            <tr>
                                <td class='cardcell-light'>{lang.excerpt}</td>
                                <td class='cardcell-dark' colspan='3' style='line-height:1.4;padding: 6px 10px;'>{$n['excerpt']}</td>
                            </tr>
                        </table>
                        ". $this->trellis->skin->end_ticket_details() ."
                        <div id='ticketroll'>
                            ". $this->trellis->skin->group_title('{lang.news_content}') ."
                            <div class='rollstart'>
                                {$n['content']}
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

                /*if ( $c['signature'] ) $c['message'] .= '<br /><br />'. $this->trellis->prepare_output( $c['usignature'], array( 'html' => $c['sig_html'] ) );*/

                $this->output .= "<div id='c{$c['id']}'>
                                <div class='bar{$cclass}'>";

                $comment_edit = 0;
                $comment_delete = 0;
                $comment_javascript_html = '';

                if ( $c['html'] ) $comment_javascript_html = 'Html';

                if ( $this->trellis->user['g_news_com_edit_all'] || ( $this->trellis->user['g_news_com_edit'] && $c['uid'] == $this->trellis->user['id'] ) ) $comment_edit = 1;
                if ( $this->trellis->user['g_news_com_delete_all'] || ( $this->trellis->user['g_news_com_delete'] && $c['uid'] == $this->trellis->user['id'] ) ) $comment_delete = 1;

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

        if ( $this->trellis->cache->data['settings']['news']['comments'] && $n['allow_comments'] && $this->trellis->user['g_news_comment'] )
        {
            if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['news']['rte'] )
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

            $this->output .= "<form action='<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=doaddcomment&amp;id={$n['id']}' method='post'>
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
                $.get('admin.php?section=manage&page=news&act=getcomment',
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
                $.post('admin.php?section=manage&page=news&act=doeditcomment',
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
                $.get('admin.php?section=manage&page=news&act=getcomment',
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
                $.post('admin.php?section=manage&page=news&act=doeditcomment',
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
                $.post('admin.php?section=manage&page=news&act=dodelcomment',
                    { id: cid },
                    function(data) {
                        if (data != 0) {
                            $('#cc'+cid).hide('blind');
                        }
                    });
            }
            //]]>
            </script>";

        $menu_items = array( array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=news' ) );

        if ( $this->trellis->check_perm( 'manage', 'news', 'add', 0 ) ) $menu_items[] = array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=add' );
        if ( $this->trellis->check_perm( 'manage', 'news', 'edit', 0 ) ) $menu_items[] = array( 'edit', '{lang.menu_edit}', '<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=edit&amp;id='. $n['id'] );

        # TODO: Repeated code below... clean up

        if ( $this->trellis->check_perm( 'manage', 'news', 'edit', 0 ) )
        {
            if ( $n['allow_comments'] )
            {
                $menu_items[] = array( 'balloon', '{lang.menu_disable_comments}'. $this->trellis->skin->setting_alert( array( 'check' => $this->trellis->cache->data['settings']['news']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments}' ) ), '<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=togglecomments&amp;id='. $n['id'] );
            }
            else
            {
                $menu_items[] = array( 'balloon', '{lang.menu_enable_comments}'. $this->trellis->skin->setting_alert( array( 'check' => $this->trellis->cache->data['settings']['news']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments}' ) ), '<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=togglecomments&amp;id='. $n['id'] );
            }
        }

        if ( $this->trellis->check_perm( 'manage', 'news', 'delete', 0 ) ) $menu_items[] = array( 'circle_delete', '{lang.menu_delete}', '<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=dodel&amp;id='. $n['id'], 'return confirmDeleteNews('. $n['id'] .')' );
        if ( $this->trellis->check_perm( 'manage', 'settings', null, 0 ) ) $menu_items[] = array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=news' );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_news_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add News Form
    #=======================================

    private function add_news($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'news', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['news']['rte'] )
        {
            $this->output .= $this->trellis->skin->tinymce_js( 'content' );

            $html = 1;
        }
        else
        {
            $html = 0;
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=doadd", 'add_news', 'post' ) ."
                        <input type='hidden' id='html' name='html' value='{$html}' />
                        ". $this->trellis->skin->start_group_table( '{lang.adding_news}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title}', $this->trellis->skin->textfield( 'title' ), 'a', '20%', '80%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.excerpt} '. $this->trellis->skin->help_tip('{lang.tip_excerpt}'), $this->trellis->skin->textarea( 'excerpt', '', '', 0, 60, 2 ), 'a' ) ."
                        <tr>
                            <td class='option1'>{lang.options}</td>
                            <td class='option1' style='font-weight: normal'>
                                ". $this->trellis->skin->checkbox( array( 'name' => 'allow_comments', 'title' => '{lang.allow_comments}', 'value' => 1, 'alert' => array( 'check' => $this->trellis->cache->data['settings']['news']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments}' ) ) ) ."&nbsp;&nbsp;
                                ". $this->trellis->skin->checkbox( 'email_users', '{lang.email_users} '. $this->trellis->skin->help_tip('{lang.tip_email_users}') ) ."
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2' class='option2'>". $this->trellis->skin->textarea( array( 'name' => 'content', 'cols' => 80, 'rows' => 10, 'width' => '98%', 'height' => '230px' ) ) ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_news}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'title'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_title}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('title');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=news' ),
                            array( 'mails_arrow', '{lang.menu_mass_email}', '<! TD_URL !>/admin.php?section=tools&amp;page=email&amp;act=mass' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=news' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_news_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit News Form
    #=======================================

    private function edit_news($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'news', 'edit' );

        if ( ! $n = $this->trellis->func->news->get_single_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_news');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;

            $content = $this->trellis->input['content'];
        }
        else
        {
            $content = $n['content'];
        }

        if ( $this->trellis->user['rte_enable'] && $this->trellis->cache->data['settings']['news']['rte'] )
        {
            $this->output .= $this->trellis->skin->tinymce_js( 'content' );

            $html = 1;
        }
        else
        {
            $html = 0;
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=doedit&amp;id={$n['id']}", 'edit_news', 'post' ) ."
                        <input type='hidden' id='html' name='html' value='{$html}' />
                        ". $this->trellis->skin->start_group_table( '{lang.editing_news} '. $n['title'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title}', $this->trellis->skin->textfield( 'title', $n['title'] ), 'a', '20%', '80%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.excerpt} '. $this->trellis->skin->help_tip('{lang.tip_excerpt}'), $this->trellis->skin->textarea( 'excerpt', $n['excerpt'], '', 0, 60, 2 ), 'a' ) ."
                        <tr>
                            <td class='option1'>{lang.options}</td>
                            <td class='option1' style='font-weight: normal'>
                                ". $this->trellis->skin->checkbox( array( 'name' => 'allow_comments', 'title' => '{lang.allow_comments}', 'value' => $n['allow_comments'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['news']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments}' ) ) ) ."
                            </td>
                        </tr>
                        <tr>
                            <td colspan='2' class='option2'>". $this->trellis->skin->textarea( array( 'name' => 'content', 'value' => $n['content'], 'cols' => 80, 'rows' => 10, 'width' => '98%', 'height' => '230px' ) ) ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_news}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'title'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_title}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=news' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=news&amp;act=add' ),
                            array( 'mails_arrow', '{lang.menu_mass_email}', '<! TD_URL !>/admin.php?section=tools&amp;page=email&amp;act=mass' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=news' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_news_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add News
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'news', 'add' );

        if ( ! $this->trellis->input['title'] ) $this->add_news('no_title');
        if ( ! $this->trellis->input['content'] ) $this->add_news('no_content');

        #=============================
        # Add News
        #=============================

        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['news']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'uid'                => $this->trellis->user['id'],
                          'title'            => $this->trellis->input['title'],
                          'excerpt'            => $this->trellis->input['excerpt'],
                          'content'            => $this->trellis->input['content'],
                          'allow_comments'    => $this->trellis->input['allow_comments'],
                          'html'            => $html,
                          'date'            => time(),
                          'ipadd'            => $this->trellis->input['ip_address'],
                         );

        $news_id = $this->trellis->func->news->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'news_added', $this->trellis->input['title'] ), 'type' => 'news', 'content_type' => 'news', 'content_id' => $news_id ) );

        #=============================
        # Send Email
        #=============================

        # TODO: to be completed

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->news_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_news_added'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $news_id ) );
    }

    #=======================================
    # @ Do Edit News
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'news', 'edit' );

        if ( ! $this->trellis->input['title'] ) $this->edit_news('no_title');
        if ( ! $this->trellis->input['content'] ) $this->edit_news('no_content');

        #=============================
        # Update News
        #=============================

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['news']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'uid'                => $this->trellis->user['id'],
                          'title'            => $this->trellis->input['title'],
                          'excerpt'            => $this->trellis->input['excerpt'],
                          'content'            => $this->trellis->input['content'],
                          'allow_comments'    => $this->trellis->input['allow_comments'],
                          'html'            => $html,
                         );

        $this->trellis->func->news->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'news_edited', $this->trellis->input['title'] ), 'type' => 'news', 'content_type' => 'news', 'content_id' => $this->trellis->input['id'] ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->news_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_news_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $this->trellis->input['id'] ) );
    }

    #=======================================
    # @ Do Delete News
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'news', 'delete' );

        #=============================
        # DELETE News
        #=============================

        $this->trellis->func->news->delete( $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'news_deleted', $this->trellis->cache->data['news'][ $this->trellis->input['id'] ]['title'] ), 'type' => 'news', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->news_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_news_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Toggle Comments
    #=======================================

    private function do_toggle_comments()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'news', 'edit' );

        if ( ! $n = $this->trellis->func->news->get_single_by_id( array( 'id', 'title', 'allow_comments' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_news');

        #=============================
        # Edit New
        #=============================

        ( $n['allow_comments'] ) ? $allow_comments = 0 : $allow_comments = 1;

        $this->trellis->func->news->edit( array( 'allow_comments' => $allow_comments ), $n['id'] );

        if ( $allow_comments )
        {
            $this->trellis->log( array( 'msg' => array( 'newscom_enabled', $n['title'] ), 'type' => 'news', 'content_type' => 'news', 'content_id' => $this->trellis->input['id'] ) );

            $this->trellis->send_message( 'alert', $this->trellis->lang['alert_news_com_enabled'] );
        }
        else
        {
            $this->trellis->log( array( 'msg' => array( 'newscom_disabled', $n['title'] ), 'type' => 'news', 'level' => 2, 'content_type' => 'news', 'content_id' => $this->trellis->input['id'] ) );

            $this->trellis->send_message( 'alert', $this->trellis->lang['alert_news_com_disabled'] );
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $n['id'] ) );
    }

    #=======================================
    # @ Do Add Comment
    #=======================================

    private function do_add_comment()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->input['message'] ) $this->view_news( array( 'comment_error' => 'no_message' ) );

        if ( ! $n = $this->trellis->func->news->get_single_by_id( array( 'id', 'title', 'allow_comments' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_news');

        if ( ! $this->trellis->cache->data['settings']['news']['comments'] || ! $n['allow_comments'] || ! $this->trellis->user['g_news_comment'] ) $this->trellis->skin->error('no_perm');

        #=============================
        # Add Comment
        #=============================

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['news']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'nid'            => $n['id'],
                          'uid'            => $this->trellis->user['id'],
                          'message'        => $this->trellis->input['message'],
                          'staff'        => 1,
                          'html'        => $html,
                          'date'        => time(),
                          'ipadd'        => $this->trellis->input['ip_address'],
                         );

        $comment_id = $this->trellis->func->news->add_comment( $db_array, $n['id'] );

        $this->trellis->log( array( 'msg' => array( 'newscom_added', $n['title'] ), 'type' => 'news', 'content_type' => 'newscom', 'content_id' => $comment_id ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->skin->redirect( array( 'act' => 'view', 'id' => $n['id'] ), '#c'. $comment_id );
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
                                                                                'n' => array( 'title' ),
                                                                                ),
                                                         'from'        => array( 'c' => 'news_comments' ),
                                                         'join'        => array( array( 'from' => array( 'n' => 'news' ), 'where' => array( 'c' => 'nid', '=', 'n' => 'id' ) ) ),
                                                         'where'    => array( array( 'c' => 'id' ), '=', $this->trellis->input['id'] ),
                                                         'limit'    => array( 0, 1 ),
                                                  )      );

        if ( ! $c ) $this->trellis->skin->ajax_output( '0' );

        # Double Check Permission Logic Below...

        if ( ! $this->trellis->user['g_news_com_edit_all'] && ! ( $this->trellis->user['g_news_com_edit'] && $c['uid'] == $this->trellis->user['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        #=============================
        # Edit Reply
        #=============================

        // RTE Permissions
        ( $this->trellis->input['html'] && $this->trellis->cache->data['settings']['news']['rte'] ) ? $html = 1 : $html = 0;

        $db_array = array(
                          'message'        => $this->trellis->input['message'],
                          'html'        => $html,
                         );

        $this->trellis->func->news->edit_comment( $db_array, $c['id'] );

        $this->trellis->log( array( 'msg' => array( 'newscom_edited', $c['title'] ), 'type' => 'news', 'content_type' => 'newscom', 'content_id' => $c['id'] ) );

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
                                                                                'c' => array( 'id','nid', 'uid' ),
                                                                                'n' => array( 'title' ),
                                                                                ),
                                                         'from'        => array( 'c' => 'news_comments' ),
                                                         'join'        => array( array( 'from' => array( 'n' => 'news' ), 'where' => array( 'c' => 'nid', '=', 'n' => 'id' ) ) ),
                                                         'where'    => array( array( 'c' => 'id' ), '=', $this->trellis->input['id'] ),
                                                         'limit'    => array( 0, 1 ),
                                                  )      );

        if ( ! $c ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->trellis->user['g_news_com_delete_all'] && ! ( $this->trellis->user['g_news_com_delete'] && $c['uid'] == $this->trellis->user['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        #=============================
        # DELETE Comment
        #=============================

        $this->trellis->func->news->delete_comment( $c['id'], array( 'nid' => $c['nid'] ) );

        $this->trellis->log( array( 'msg' => array( 'newscom_deleted', $c['title'] ), 'type' => 'news', 'level' => 2, 'content_type' => 'news', 'content_id' => $c['nid'] ) );

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
        $c = $this->trellis->func->news->get_single_comment_by_id( array( 'id', 'uid', 'message', 'html' ), $this->trellis->input['id'] );

        if ( ! $c ) $this->trellis->skin->ajax_output( '0' );

        if ( ! $this->trellis->user['g_news_com_edit_all'] && ! ( $this->trellis->user['g_news_com_edit'] && $c['uid'] == $this->trellis->user['id'] ) ) $this->trellis->skin->ajax_output( '0' );

        $this->trellis->skin->ajax_output( $this->trellis->prepare_output( $c['message'], array( 'html' => $c['html'] ) ) );
    }

    #=======================================
    # @ Generate URL
    #=======================================

    private function generate_url($params=array())
    {
        $url = '<! TD_URL !>/admin.php?section=manage&amp;page=news';

        if ( ! isset( $params['sort'] ) ) $params['sort'] = $this->trellis->input['sort'];
        if ( ! isset( $params['order'] ) ) $params['order'] = $this->trellis->input['order'];
        if ( ! isset( $params['st'] ) ) $params['st'] = $this->trellis->input['st'];

        if ( $params['sort'] ) $url .= '&amp;sort='. $params['sort'];

        if ( $params['order'] ) $url .= '&amp;order='. $params['order'];

        if ( $params['st'] ) $url .= '&amp;st='. $params['st'];

        return $url;
    }

}

?>