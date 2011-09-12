<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_categories {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        if ( ! $this->trellis->cache->data['settings']['kb']['enable'] ) $this->trellis->skin->error('kb_disabled');

        $this->trellis->check_perm( 'manage', 'categories' );

        $this->trellis->load_functions('categories');
        $this->trellis->load_lang('categories');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_cats();
            break;
            case 'reorder':
                $this->reorder_cats();
            break;
            case 'add':
                $this->add_cat();
            break;
            case 'edit':
                $this->edit_cat();
            break;
            case 'delete':
                $this->delete_cat();
            break;

            case 'doreorder':
                $this->do_reorder();
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
                $this->list_cats();
            break;
        }
    }

    #=======================================
    # @ List Categories
    #=======================================

    private function list_cats()
    {
        #=============================
        # Grab Categories
        #=============================

        $cat_rows = '';

        if ( ! $parents = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'name', 'description', 'articles' ), 'where' => array( 'parent_id', '=', 0 ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $cat_rows .= "<tr><td class='bluecell-light' colspan='6'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=add'>{lang.no_categories}</a></strong></td></tr>";
        }
        else
        {
            if ( $childs_raw = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'parent_id', 'name', 'description', 'articles' ), 'where' => array( 'parent_id', '!=', 0 ), 'order' => array( 'position' => 'asc' ) ) ) )
            {
                $childs = array();

                foreach( $childs_raw as $crid => $cr )
                {
                    $childs[ $cr['parent_id'] ][ $crid ] = $cr;
                }
            }

            # Put Into Order
            $cats = array();

            foreach( $parents as $pid => $p )
            {
                $cats[ $pid ] = $p;

                if ( $childs[ $pid ] )
                {
                    foreach( $childs[ $pid ] as $cid => $c )
                    {
                        $cats[ $cid ] = $c;
                    }
                }
            }

            foreach( $cats as $cid => $c )
            {
                if ( $c['parent_id'] ) $c['name'] = "<img src='<! IMG_DIR !>/icon_arrow_sub.png' alt='--' style='vertical-align:bottom' />&nbsp;&nbsp;". $c['name'];

                if ( ! $c['description'] ) $c['description'] = '<i>{lang.no_description}</i>';

                $cat_rows .= "<tr>
                                    <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;cat={$c['id']}'><strong>{$c['id']}</strong></a></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;cat={$c['id']}'>{$c['name']}</a></td>
                                    <td class='bluecellthin-light' style='font-weight: normal'>{$c['description']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=articles&amp;cat={$c['id']}'><strong>{$c['articles']}</strong></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=edit&amp;id={$c['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=delete&amp;id={$c['id']}'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
                                </tr>";

            }
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.cats_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='30%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='54%' align='left'>{lang.description}</th>
                            <th class='bluecellthin-th' width='8%' align='center'>{lang.articles}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $cat_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=add' ),
                            array( 'arrow_switch', '{lang.menu_reorder}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=reorder' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=categories' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_categories_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_categories_title}', '{lang.help_about_categories_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Category Form
    #=======================================

    private function add_cat($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->trellis->load_functions('drop_downs');

        $group_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $group_count = 0;
        $group_total = count( $groups );

        foreach( $this->trellis->cache->data['groups'] as $gid => $g )
        {
            $g['g_kb_perm'] = unserialize( $g['g_kb_perm'] );

            $group_count ++;

            if ( $group_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $group_count & 1 )
            {
                if ( $group_count == $group_total )
                {
                    $group_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_kb_perm'][ $c['id' ] ] ) ."</td></tr>";
                }
                else
                {
                    $group_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_kb_perm'][ $c['id' ] ] ) ."</td>";
                }
            }
            else
            {
                $group_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_kb_perm'][ $c['id' ] ] ) ."</td></tr>";
            }
        }

        $group_perms_html .= "</table>";

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=doadd", 'add_category', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_category}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.parent_cat} '. $this->trellis->skin->help_tip('{lang.tip_parent_cat}' ), "<select name='parent_id' id='parent_id'><option value='0'>{lang.no_parent}</option>". $this->trellis->func->drop_downs->cat_drop( array( 'select' => $this->trellis->input['parent_id'], 'no_perm' => 1 ) ) ."</select>", 'a', '25%', '75%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description} '. $this->trellis->skin->help_tip('{lang.tip_description}' ), $this->trellis->skin->textarea( 'description', '', '', 0, 60, 3 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_rating}', $this->trellis->skin->yes_no_radio( array( 'name' => 'allow_rating', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['rating'], 'for' => 1, 'msg' => '{lang.warn_rating}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_comments}', $this->trellis->skin->yes_no_radio( array( 'name' => 'allow_comments', 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.group_permissions} '. $this->trellis->skin->help_tip('{lang.tip_group_perms}'), $group_perms_html, 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_category}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_categories_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Category Form
    #=======================================

    private function edit_cat($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'edit' );

        if ( ! $c = $this->trellis->func->categories->get_single_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_category');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;

            $parent_id = $this->trellis->input['$parent_id'];
        }
        else
        {
            $parent_id = $c['parent_id'];
        }

        $this->trellis->load_functions('drop_downs');
        $this->trellis->load_functions('groups');

        $groups = $this->trellis->func->groups->get( array( 'select' => array( 'g_id', 'g_name', 'g_kb_perm' ) ) );

        $group_perms_html = "<table width='100%' cellpadding='0' cellspacing='0'>";
        $group_count = 0;
        $group_total = count( $groups );

        foreach( $groups as $gid => $g )
        {
            $g['g_kb_perm'] = unserialize( $g['g_kb_perm'] );

            $group_count ++;

            if ( $group_count > 2 ) $padding = " style='padding-top:5px'";

            if ( $group_count & 1 )
            {
                if ( $group_count == $group_total )
                {
                    $group_perms_html .= "<tr><td colspan='2'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_kb_perm'][ $c['id' ] ] ) ."</td></tr>";
                }
                else
                {
                    $group_perms_html .= "<tr><td width='35%'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_kb_perm'][ $c['id' ] ] ) ."</td>";
                }
            }
            else
            {
                $group_perms_html .= "<td width='65%'{$padding}>". $this->trellis->skin->checkbox( 'gp_'. $g['g_id'], $g['g_name'], $g['g_kb_perm'][ $c['id' ] ] ) ."</td></tr>";
            }
        }

        $group_perms_html .= "</table>";

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=doedit&amp;id={$c['id']}", 'edit_category', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_category} '. $c['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.parent_cat} '. $this->trellis->skin->help_tip('{lang.tip_parent_cat}' ), "<select name='parent_id' id='parent_id'><option value='0'>{lang.no_parent}</option>". $this->trellis->func->drop_downs->cat_drop( array( 'select' => $parent_id, 'no_perm' => 1 ) ) ."</select>", 'a', '25%', '75%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name', $c['name'] ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description} '. $this->trellis->skin->help_tip('{lang.tip_description}' ), $this->trellis->skin->textarea( 'description', $c['description'], '', 0, 60, 3 ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_rating}', $this->trellis->skin->yes_no_radio( array( 'name' => 'allow_rating', 'value' => $c['allow_rating'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['rating'], 'for' => 1, 'msg' => '{lang.warn_rating}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.allow_comments}', $this->trellis->skin->yes_no_radio( array( 'name' => 'allow_comments', 'value' => $c['allow_comments'], 'alert' => array( 'check' => $this->trellis->cache->data['settings']['kb']['comments'], 'for' => 1, 'msg' => '{lang.warn_comments}' ) ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.group_permissions} '. $this->trellis->skin->help_tip('{lang.tip_group_perms}'), $group_perms_html, 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_category}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_categories_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Delete Category Form
    #=======================================

    private function delete_cat()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'delete' );

        if ( ! $c = $this->trellis->func->categories->get_single_by_id( 'all', $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_category');

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=dodel&amp;id={$c['id']}", 'delete_cat', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.deleting_category} '. $c['name'] ) ."
                        ". $this->trellis->skin->group_sub( '{lang.delete_cat_articles_qs}' ) ."
                        <div class='option1'><input type='radio' name='action' id='action1' value='1' checked='checked' /> <label for='action1'>{lang.move_articles_to_cat}</label> <select name='moveto'>". $this->trellis->func->drop_downs->cat_drop( array( 'exclude' => $c['id'], 'childs' => 1, 'no_perm' => 1 ) ) ."</select></div>
                        <div class='option2'><input type='radio' name='action' id='action2' value='2' /> <label for='action2'>{lang.delete_articles}</label></div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_delete_category}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_categories_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Reorder Categories
    #=======================================

    private function reorder_cats()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'reorder' );

        #=============================
        # Pass to Function
        #=============================

        if ( $this->trellis->input['parent'] )
        {
            $this->reorder_child_cats();
        }
        else
        {
            $this->reorder_parent_cats();
        }
    }

    #=======================================
    # @ Reorder Parent Categories
    #=======================================

    private function reorder_parent_cats()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'reorder' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=doreorder", 'reorder_parent_cats', 'post', 'return getOrder()' ) ."
                        ". $this->trellis->skin->group_title( '{lang.reordering_parent_cats}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.reorder_parent_cats_msg}' ) ."
                        <input type='hidden' name='order' id='order' value='' />";

        if ( ! $cats = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'name', 'description' ), 'where' => array( 'parent_id', '=', 0 ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $this->output .= "<div class='bluecell-light'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=add'>{lang.no_categories}</a></strong></div>";
        }
        else
        {
            $parents = array();

            $childs = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'parent_id' ), 'where' => array( 'parent_id', '!=', 0 ) ) );

            foreach( $childs as $cid => $c )
            {
                $parents[ $c['parent_id'] ] ++;
            }

            $this->output .= "<ul class='draggable' id='sortable'>";

            foreach( $cats as $cid => $c )
            {
                if ( ! $c['description'] ) $c['description'] = '<i>{lang.no_description}</i>';

                if ( $parents[ $cid ] )
                {
                    $this->output .= "<li class='bluecell-light' id='c_{$cid}'><span style='float:right'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=reorder&amp;parent={$cid}'><img src='<! IMG_DIR !>/icon_arrow_switch.png' alt='*' style='vertical-align:bottom' /></a></span>{$c['name']} <span style='font-weight: normal'>({$c['description']})</span></li>";
                }
                else
                {
                    $this->output .= "<li class='bluecell-light' id='c_{$cid}'>{$c['name']} <span style='font-weight: normal'>({$c['description']})</span></li>";
                }
            }

            $this->output .= "</ul>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'reorder', '{lang.button_reorder_parent_cats}' ) ) ."
                        </div>
                        <script type='text/javascript' language='javascript'>
                            $(function() {
                                $('#sortable').sortable({
                                    stop: function() {
                                        $('#order').val( $('#sortable').sortable('serialize') );
                                    }
                                });
                            });
                        </script>";
        }

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_categories_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Reorder Child Categories
    #=======================================

    private function reorder_child_cats()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'reorder' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=doreorder&amp;parent={$this->trellis->input['parent']}", 'reorder_child_cats', 'post', 'return getOrder()' ) ."
                        ". $this->trellis->skin->group_title( '{lang.reordering_child_cats} '. $this->trellis->cache->data['categories'][ $this->trellis->input['parent'] ]['name'] ) ."
                        ". $this->trellis->skin->group_sub( '{lang.reorder_child_cats_msg}' ) ."
                        <input type='hidden' name='order' id='order' value='' />";

        if ( ! $cats = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'name', 'description' ), 'where' => array( 'parent_id', '=', $this->trellis->input['parent'] ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $this->output .= "<div class='bluecell-light'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=add'>{lang.no_sub_categories}</a></strong></div>";
        }
        else
        {
            $this->output .= "<ul class='draggable' id='sortable'>";

            foreach( $cats as $cid => $c )
            {
                if ( ! $c['description'] ) $c['description'] = '<i>{lang.no_description}</i>';

                $this->output .= "<li class='bluecell-light' id='c_{$cid}'>{$c['name']} <span style='font-weight: normal'>({$c['description']})</span></li>";
            }

            $this->output .= "</ul>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'reorder', '{lang.button_reorder_child_cats}' ) ) ."
                        </div>
                        <script type='text/javascript' language='javascript'>
                            $(function() {
                                $('#sortable').sortable({
                                    stop: function() {
                                        $('#order').val( $('#sortable').sortable('serialize') );
                                    }
                                });
                            });
                        </script>";
        }

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=categories&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=kb' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_categories_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add Category
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'add' );

        if ( ! $this->trellis->input['name'] ) $this->add_cat('no_name');

        #=============================
        # Add Category
        #=============================

        $db_array = array(
                          'parent_id'        => $this->trellis->input['parent_id'],
                          'name'            => $this->trellis->input['name'],
                          'description'        => $this->trellis->input['description'],
                          'allow_rating'    => $this->trellis->input['allow_rating'],
                          'allow_comments'    => $this->trellis->input['allow_comments'],
                         );

        $cat_id = $this->trellis->func->categories->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'category_added', $this->trellis->input['name'] ), 'type' => 'kb', 'content_type' => 'category', 'content_id' => $cat_id ) );

        #=============================
        # Generate Permissions
        #=============================

        $this->trellis->load_functions('groups');

        $groups = $this->trellis->func->groups->get( array( 'select' => array( 'g_id', 'g_kb_perm' ) ) );

        foreach ( $groups as $gid => $g )
        {
            $new_perms = unserialize( $g['g_kb_perm'] );

            $new_perms[ $cat_id ] = intval( $this->trellis->input[ 'gp_'. $g['g_id'] ] );

            $this->trellis->func->groups->edit( array( 'g_kb_perm' => $new_perms ), $g['g_id'] );
        }

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->categories_cache();
        $this->trellis->func->rebuild->groups_cache(); # TODO: necessary to rebuild when no group selected?

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_category_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Category
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'edit' );

        if ( ! $this->trellis->input['name'] ) $this->edit_cat('no_name');

        #=============================
        # Update Category
        #=============================

        $db_array = array(
                          'parent_id'        => $this->trellis->input['parent_id'],
                          'name'            => $this->trellis->input['name'],
                          'description'        => $this->trellis->input['description'],
                          'allow_rating'    => $this->trellis->input['allow_rating'],
                          'allow_comments'    => $this->trellis->input['allow_comments'],
                         );

        $this->trellis->func->categories->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'category_edited', $this->trellis->input['name'] ), 'type' => 'kb', 'content_type' => 'category', 'content_id' => $this->trellis->input['id'] ) );

        #=============================
        # Generate Permissions
        #=============================

        $this->trellis->load_functions('groups');

        $groups = $this->trellis->func->groups->get( array( 'select' => array( 'g_id', 'g_kb_perm' ) ) );

        foreach ( $groups as $gid => $g )
        {
            $new_perms = unserialize( $g['g_kb_perm'] );

            $new_perms[ $this->trellis->input['id'] ] = intval( $this->trellis->input[ 'gp_'. $g['g_id'] ] );

            $this->trellis->func->groups->edit( array( 'g_kb_perm' => $new_perms ), $g['g_id'] );
        }

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->categories_cache();
        $this->trellis->func->rebuild->groups_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_category_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Category
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'delete' );

        #=============================
        # DELETE Category
        #=============================

        $this->trellis->func->categories->delete( $this->trellis->input['id'], $this->trellis->input['action'], $this->trellis->input['moveto'] );

        $this->trellis->log( array( 'msg' => array( 'category_deleted', $this->trellis->cache->data['categories'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'kb', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->categories_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_category_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Reorder Categories
    #=======================================

    private function do_reorder()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'reorder' );

        #=============================
        # Pass to Function
        #=============================

        if ( $this->trellis->input['parent'] )
        {
            $this->do_reorder_childs();
        }
        else
        {
            $this->do_reorder_parents();
        }
    }

    #=======================================
    # @ Do Reorder Parent Categories
    #=======================================

    private function do_reorder_parents()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'reorder' );

        #=============================
        # Reorder Categories
        #=============================

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['order'] ), $order );

        if ( empty( $order['c'] ) ) $this->list_cats( 'no_reorder' );

        if ( $cats = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'position' ), 'where' => array( 'parent_id', '=', 0 ) ) ) )
        {
            foreach ( $order['c'] as $position => $cid )
            {
                $position ++;

                if ( $position != $cats[ $cid ]['position'] )
                {
                    $this->trellis->func->categories->edit( array( 'position' => $position ), $cid );
                }
            }
        }

        $this->trellis->log( array( 'msg' => 'categories_reordered_p', 'type' => 'kb' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->categories_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_parent_cats_reordered'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Reorder Child Categories
    #=======================================

    private function do_reorder_childs()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'categories', 'reorder' );

        if ( ! $c = $this->trellis->func->categories->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['parent'] ) ) $this->trellis->skin->error('no_category');

        #=============================
        # Reorder Categories
        #=============================

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['order'] ), $order );

        if ( empty( $order['c'] ) ) $this->list_cats( 'no_reorder' );

        if ( $cats = $this->trellis->func->categories->get( array( 'select' => array( 'id', 'position' ), 'where' => array( 'parent_id', '=', $this->trellis->input['parent'] ) ) ) )
        {
            foreach ( $order['c'] as $position => $cid )
            {
                $position ++;

                if ( $position != $cats[ $cid ]['position'] )
                {
                    $this->trellis->func->categories->edit( array( 'position' => $position ), $cid );
                }
            }
        }

        $this->trellis->log( array( 'msg' => array( 'categories_reordered_c', $c['name'] ), 'type' => 'kb' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->categories_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_child_cats_reordered'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

}

?>