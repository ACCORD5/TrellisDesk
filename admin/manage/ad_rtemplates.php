<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_rtemplates {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'rtemplates' );

        $this->trellis->load_functions('rtemplates');
        $this->trellis->load_lang('rtemplates');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_rtemplates();
            break;
            case 'add':
                $this->add_rtemplate();
            break;
            case 'edit':
                $this->edit_rtemplate();
            break;
            case 'reorder':
                $this->reorder_rtemplates();
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
            case 'doreorder':
                $this->do_reorder();
            break;

            default:
                $this->list_rtemplates();
            break;
        }
    }

    #=======================================
    # @ List Reply Templates
    #=======================================

    private function list_rtemplates()
    {
        #=============================
        # Grab Reply Templates
        #=============================

        $rtemplate_rows = "";

        if ( ! $rtemplates = $this->trellis->func->rtemplates->get( array( 'select' => array( 'id', 'name', 'description' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $rtemplate_rows .= "<tr><td class='bluecell-light' colspan='5'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=add'>{lang.no_rtemplates}</a></strong></td></tr>";
        }
        else
        {
            foreach( $rtemplates as $rtid => $rt )
            {
                if ( ! $rt['description'] ) $rt['description'] = '<i>{lang.no_description}</i>';

                $rtemplate_rows .= "<tr>
                                    <td class='bluecellthin-light'><strong>{$rt['id']}</strong></td>
                                    <td class='bluecellthin-dark'>{$rt['name']}</td>
                                    <td class='bluecellthin-light' style='font-weight: normal'>{$rt['description']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=edit&amp;id={$rt['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=dodel&amp;id={$rt['id']}' onclick='return confirmDelete({$rt['id']})'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
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
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=rtemplates&act=dodel&id='+nid) },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.rtemplates_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='28%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='64%' align='left'>{lang.description}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $rtemplate_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=add' ),
                            array( 'arrow_switch', '{lang.menu_reorder}', '<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=reorder' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=rtemplates' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_rtemplates_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_rtemplates_title}', '{lang.help_about_rtemplates_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Reply Template
    #=======================================

    private function add_rtemplate($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'rtemplates', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->output .= $this->trellis->skin->tinymce_js( 'content_html' );

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=doadd", 'add_rtemplate', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_rtemplate}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name' ), 'a', '16%', '84%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description}', $this->trellis->skin->textarea( array( 'name' => 'description', 'cols' => 60, 'rows' => 2 ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.html_content}' ) ."
                        <div class='option1'>". $this->trellis->skin->textarea( array( 'name' => 'content_html', 'cols' => 80, 'rows' => 8, 'width' => '98%', 'height' => '200px' ) ) ."</div>
                        ". $this->trellis->skin->group_sub( '{lang.plaintext_content}' ) ."
                        <div class='option2'>". $this->trellis->skin->textarea( array( 'name' => 'content_plaintext', 'cols' => 80, 'rows' => 7, 'width' => '98%', 'height' => '180px' ) ) ."</div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_rtemplate}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_rtemplates_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Reply Template
    #=======================================

    private function edit_rtemplate($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'rtemplates', 'edit' );

        #=============================
        # Grab Reply Template
        #=============================

        if ( ! $rt = $this->trellis->func->rtemplates->get_single_by_id( array( 'id', 'name', 'description', 'content_html', 'content_plaintext' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_rtemplate');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->output .= $this->trellis->skin->tinymce_js( 'content_html' );

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=doedit&amp;id={$rt['id']}", 'edit_rtemplate', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_rtemplate} '. $rt['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name', $rt['name'] ), 'a', '16%', '84%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description}', $this->trellis->skin->textarea( array( 'name' => 'description', 'value' => $rt['description'], 'cols' => 60, 'rows' => 2 ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.html_content}' ) ."
                        <div class='option1'>". $this->trellis->skin->textarea( array( 'name' => 'content_html', 'value' => $rt['content_html'], 'cols' => 80, 'rows' => 8, 'width' => '98%', 'height' => '200px' ) ) ."</div>
                        ". $this->trellis->skin->group_sub( '{lang.plaintext_content}' ) ."
                        <div class='option2'>". $this->trellis->skin->textarea( array( 'name' => 'content_plaintext', 'value' => $rt['content_plaintext'], 'cols' => 80, 'rows' => 7, 'width' => '98%', 'height' => '180px' ) ) ."</div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_rtemplate}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_rtemplates_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Reorder Reply Templates
    #=======================================

    private function reorder_rtemplates()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'rtemplates', 'reorder' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=doreorder", 'reorder_rtemplates', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.reordering_rtemplates}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.reorder_rtemplates_msg}' ) ."
                        <input type='hidden' name='order' id='order' value='' />";

        if ( ! $rtemplates = $this->trellis->func->rtemplates->get( array( 'select' => array( 'id', 'name', 'description' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $this->output .= "<div class='bluecell-light'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=add'>{lang.no_rtemplates}</a></strong></div>";
        }
        else
        {
            $this->output .= "<ul class='draggable' id='sortable'>";

            foreach( $rtemplates as $rtid => $rt )
            {
                $this->output .= "<li class='bluecell-light' id='rt_{$rt['id']}'>{$rt['name']}";

                if ( $rt['description'] ) $this->output .= " ({$rt['description']})";

                $this->output .= "</li>";
            }

            $this->output .= "</ul>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'reorder', '{lang.button_reorder_rtemplates}' ) ) ."
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
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=rtemplates&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_rtemplates_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add Reply Template
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'rtemplates', 'add' );

        if ( ! $this->trellis->input['name'] ) $this->add_rtemplate('no_name');

        #=============================
        # Add Reply Template
        #=============================

        $db_array = array(
                          'name'                => $this->trellis->input['name'],
                          'description'            => $this->trellis->input['description'],
                          'content_html'        => $this->trellis->input['content_html'],
                          'content_plaintext'    => $this->trellis->input['content_plaintext'],
                          );

        $rtemplate_id = $this->trellis->func->rtemplates->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'rtemplate_added', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->rtemplates_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_rtemplate_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Reply Template
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'rtemplates', 'edit' );

        if ( ! $this->trellis->input['name'] ) $this->edit_rtemplate('no_name');

        #=============================
        # Update Reply Template
        #=============================

        $db_array = array(
                          'name'                => $this->trellis->input['name'],
                          'description'            => $this->trellis->input['description'],
                          'content_html'        => $this->trellis->input['content_html'],
                          'content_plaintext'    => $this->trellis->input['content_plaintext'],
                          );

        $this->trellis->func->rtemplates->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'rtemplate_edited', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->rtemplates_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_rtemplate_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Reply Template
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'rtemplates', 'delete' );

        #=============================
        # DELETE Reply Template
        #=============================

        $this->trellis->func->rtemplates->delete( $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'rtemplate_deleted', $this->trellis->cache->data['rtemplates'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->rtemplates_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_rtemplate_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Reorder Reply Templates
    #=======================================

    private function do_reorder()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'rtemplates', 'reorder' );

        #=============================
        # Reorder Reply Templates
        #=============================

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['order'] ), $order );

        if ( empty( $order['rt'] ) ) $this->list_rtemplates( 'no_reorder' );

        if ( $rtemplates = $this->trellis->func->rtemplates->get( array( 'select' => array( 'id', 'position' ) ) ) )
        {
            foreach ( $order['rt'] as $position => $rtid )
            {
                $position ++;

                if ( $position != $rtemplates[ $rtid ]['position'] )
                {
                    $this->trellis->func->rtemplates->edit( array( 'position' => $position ), $rtid );
                }
            }
        }

        $this->trellis->log( array( 'msg' => 'rtemplates_reordered', 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->rtemplates_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_rtemplates_reordered'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

}

?>