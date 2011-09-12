<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_pages {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'pages' );

        $this->trellis->load_functions('pages');
        $this->trellis->load_lang('pages');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_pages();
            break;
            case 'add':
                $this->add_page();
            break;
            case 'edit':
                $this->edit_page();
            break;

            case 'aliascheck':
                $this->ajax_alias_use_check();
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

            default:
                $this->list_pages();
            break;
        }
    }

    #=======================================
    # @ List Pages
    #=======================================

    private function list_pages()
    {
        #=============================
        # Sorting Options
        #=============================

        if ( $this->trellis->input['sort'] )
        {
            $sort = $this->trellis->input['sort'];
        }
        else
        {
            $sort = 'id';
        }

        $order_var = "order_". $sort;
        $img_var = "img_". $sort;

        if ( $this->trellis->input['order'] )
        {
            $order = strtoupper( $this->trellis->input['order'] );
        }
        elseif ( $sort == 'id' )
        {
            $order = 'asc';
        }

        if ( $order == 'desc' )
        {
            $$order_var = "&amp;order=asc";
            $$img_var = "&nbsp;<img src='<! IMG_DIR !>/arrow_down.gif' alt='{lang.down}' />";
        }
        else
        {
            $$order_var = "&amp;order=desc";
            $$img_var = "&nbsp;<img src='<! IMG_DIR !>/arrow_up.gif' alt='{lang.up}' />";
        }

        $link_id = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=list&amp;sort=id". $order_id ."'>{lang.id}". $img_id ."</a>";
        $link_title = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=list&amp;sort=title". $order_title ."'>{lang.title}". $img_title ."</a>";

        #=============================
        # Grab Pages
        #=============================

        $page_rows = '';

        if ( ! $pages = $this->trellis->func->pages->get( array( 'select' => array( 'id', 'alias', 'title' ), 'order' => array( $sort => $order ) ) ) )
        {
            $page_rows .= "<tr><td class='bluecell-light' colspan='5'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=add'>{lang.no_pages}</a></strong></td></tr>";
        }
        else
        {
            foreach( $pages as $pid => $p )
            {
                $page_rows .= "<tr>
                                    <td class='bluecellthin-light'><strong>{$p['id']}</strong></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/index?page=pages&amp;id={$p['alias']}'>{$p['title']}</a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=edit&amp;id={$p['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=dodel&amp;id={$p['id']}' onclick='return confirmDelete({$p['id']})'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<script type='text/javascript'>
                        function confirmDelete(nid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_title}',
                                message: '{lang.dialog_delete_msg}',
                                yesButton: '{lang.dialog_delete_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=pages&act=dodel&id='+nid) },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.pages_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='6%' align='left'>{$link_id}</th>
                            <th class='bluecellthin-th' width='88%' align='left'>{$link_title}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $page_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=pages' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_pages_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_pages_title}', '{lang.help_about_pages_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Page
    #=======================================

    private function add_page($error="")
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'pages', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        if ( $this->trellis->user['rte_enable'] )
        {
            $this->output .= $this->trellis->skin->tinymce_js( 'content' );
        }

        $this->output .= "<script type='text/javascript'>
                        function ckAliasUse(k) {
                            if ( ! k ) { return true }

                            $.get('<! TD_URL !>/admin.php?section=manage&page=pages&act=aliascheck',
                            { alias: k },
                            function(data) {
                                response = data
                            } );

                            if ( response == 1 ) { return true } else { return false }
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=doadd", 'add_page', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_page}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title}', $this->trellis->skin->textfield( 'title' ), 'a', '18%', '82%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.alias} '. $this->trellis->skin->help_tip('{lang.tip_alias}'), $this->trellis->skin->textfield( 'alias' ), 'a' ) ."
                        <tr>
                            <td colspan='2' class='option2'>". $this->trellis->skin->textarea( array( 'name' => 'content', 'cols' => 80, 'rows' => 10, 'width' => '98%', 'height' => '230px' ) ) ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_page}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'title'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_title}' ) ) ),
                                 'alias'    => array( array( 'type' => 'format', 'params' => array( 'pattern' => '/^[A-Za-z0-9_\/]*$/', 'fail_msg' => '{lang.lv_format_alias}' ) ), array( 'type' => 'custom', 'params' => array( 'against' => 'return ckAliasUse(value)', 'fail_msg' => '{lang.lv_used_alias}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('title');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=pages' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=pages' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_pages_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Page
    #=======================================

    private function edit_page($error="")
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'pages', 'edit' );

        if ( ! $p = $this->trellis->func->pages->get_single_by_id( array( 'id', 'title', 'alias', 'content' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_page');

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
            $content = $p['content'];
        }

        if ( $this->trellis->user['rte_enable'] )
        {
            $this->output .= $this->trellis->skin->tinymce_js( 'content' );
        }

        $this->output .= "<script type='text/javascript'>
                        function ckAliasUse(k) {
                            if ( ! k ) { return true }

                            $.get('<! TD_URL !>/admin.php?section=manage&page=pages&act=aliascheck&edit=". $p['id'] ."',
                            { alias: k },
                            function(data) {
                                response = data
                            } );

                            if ( response == 1 ) { return true } else { return false }
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=doedit&amp;id={$p['id']}", 'edit_page', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_page} '. $p['title'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.title}', $this->trellis->skin->textfield( 'title', $p['title'] ), 'a', '18%', '82%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.alias} '. $this->trellis->skin->help_tip('{lang.tip_alias}'), $this->trellis->skin->textfield( 'alias', $p['alias'] ), 'a' ) ."
                        <tr>
                            <td colspan='2' class='option2'>". $this->trellis->skin->textarea( array( 'name' => 'content', 'value' => $p['content'], 'cols' => 80, 'rows' => 10, 'width' => '98%', 'height' => '230px' ) ) ."</td>
                        </tr>
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_page}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'title'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_title}' ) ) ),
                                 'alias'    => array( array( 'type' => 'format', 'params' => array( 'pattern' => '/^[A-Za-z0-9_\/]*$/', 'fail_msg' => '{lang.lv_format_alias}' ) ), array( 'type' => 'custom', 'params' => array( 'against' => 'return ckAliasUse(value)', 'fail_msg' => '{lang.lv_used_alias}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('title');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=pages' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=pages&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=pages' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_pages_options}', $menu_items );
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

        $this->trellis->check_perm( 'manage', 'pages', 'add' );

        if ( ! $this->trellis->input['title'] ) $this->add_page('no_title');
        if ( ! $this->trellis->input['content'] ) $this->add_page('no_content');

        if ( $this->trellis->input['alias'] )
        {
            $this->trellis->input['alias'] = $this->alias_clean( $this->trellis->input['alias'] );

            if ( ! $this->alias_use_check( $this->trellis->input['alias'] ) ) $this->add_page('used_alias');
        }

        #=============================
        # Add Page
        #=============================

        $db_array = array(
                          'title'        => $this->trellis->input['title'],
                          'alias'        => $this->trellis->input['alias'],
                          'content'        => $this->trellis->input['content'],
                          'date'        => time(),
                         );

        $page_id = $this->trellis->func->pages->add( $db_array );

        if ( ! $this->trellis->input['alias'] ) $this->trellis->func->pages->edit( array( 'alias' => $page_id ), $page_id );

        $this->trellis->log( array( 'msg' => array( 'page_added', $this->trellis->input['title'] ), 'type' => 'other' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_page_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'pages', 'edit' );

        if ( ! $this->trellis->input['title'] ) $this->edit_page('no_title');
        if ( ! $this->trellis->input['content'] ) $this->edit_page('no_content');

        if ( ! $this->trellis->input['alias'] ) $this->trellis->input['alias'] = $this->trellis->input['id'];

        $this->trellis->input['alias'] = $this->alias_clean( $this->trellis->input['alias'] );

        if ( ! $this->alias_use_check( $this->trellis->input['alias'], $this->trellis->input['id'] ) ) $this->edit_page('used_alias');

        #=============================
        # Edit Page
        #=============================

        $db_array = array(
                          'title'        => $this->trellis->input['title'],
                          'alias'        => $this->trellis->input['alias'],
                          'content'        => $this->trellis->input['content'],
                          'modified'    => time(),
                         );

        $this->trellis->func->pages->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'page_edited', $this->trellis->input['title'] ), 'type' => 'other' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_page_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'pages', 'delete' );

        if ( ! $p = $this->trellis->func->pages->get_single_by_id( array( 'id', 'title' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_page');

        #=============================
        # DELETE Page
        #=============================

        $this->trellis->func->pages->delete( $p['id'] );

        $this->trellis->log( array( 'msg' => array( 'page_deleted', $p['title'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_page_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ AJAX Alias Use Check
    #=======================================

    private function ajax_alias_use_check()
    {
        if ( $this->alias_use_check( urldecode( $this->trellis->input['alias'] ), $this->trellis->input['edit'] ) )
        {
            print '1';
        }
        else
        {
            print '0';
        }

        exit();
    }

    #=======================================
    # @ Alias Format Check
    #=======================================

    private function alias_format_check($alias)
    {
        if ( preg_match( '/^[A-Za-z0-9_\/]*$/', $alias ) )
        {
            return true;
        }

        return false;
    }

    #=======================================
    # @ Alias Use Check
    #=======================================

    private function alias_use_check($alias, $pid=0)
    {
        if ( $pid )
        {
            if ( $this->trellis->func->pages->get_single( array( 'id' ), array( array( 'alias', '=', strtolower( $alias ) ), array( 'id', '!=', intval( $pid ), 'and' ) ) ) ) return false;
        }
        else
        {
            if ( $this->trellis->func->pages->get_single( array( 'id' ), array( 'alias', '=', strtolower( $alias ) ) ) ) return false;
        }

        return true;
    }

    #=======================================
    # @ Alias Clean
    #=======================================

    private function alias_clean($alias)
    {
        $alias = preg_replace( '/[\/]+/', '/', $this->trellis->input['alias'] );

        return preg_replace( '/\/$/', '', $alias );
    }

}

?>