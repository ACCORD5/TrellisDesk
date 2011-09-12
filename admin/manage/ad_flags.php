<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_flags {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'flags' );

        $this->trellis->load_functions('flags');
        $this->trellis->load_lang('flags');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_flags();
            break;
            case 'add':
                $this->add_flag();
            break;
            case 'edit':
                $this->edit_flag();
            break;
            case 'reorder':
                $this->reorder_flags();
            break;

            case 'doupload':
                $this->do_upload();
            break;
            case 'imglist':
                $this->get_img_list();
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
                $this->list_flags();
            break;
        }
    }

    #=======================================
    # @ List Flags
    #=======================================

    private function list_flags()
    {
        #=============================
        # Grab Flags
        #=============================

        $flag_rows = "";

        if ( ! $flags = $this->trellis->func->flags->get( array( 'select' => array( 'id', 'name', 'icon' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $flag_rows .= "<tr><td class='bluecell-light' colspan='5'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=add'>{lang.no_flags}</a></strong></td></tr>";
        }
        else
        {
            foreach( $flags as $fid => $f )
            {
                $flag_rows .= "<tr>
                                    <td class='bluecellthin-light'><strong>{$f['id']}</strong></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fflag". urlencode('[]') ."={$f['id']}'><img src='<! TD_URL !>/images/flags/{$f['icon']}' alt='{$f['name']}' style='vertical-align:bottom' />&nbsp;&nbsp;{$f['name']}</a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=edit&amp;id={$f['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=dodel&amp;id={$f['id']}' onclick='return confirmDelete({$f['id']})'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a></td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<script type='text/javascript'>
                        function confirmDelete(fid) {
                            dialogConfirm({
                                title: '{lang.dialog_delete_title}',
                                message: '{lang.dialog_delete_msg}',
                                yesButton: '{lang.dialog_delete_button}',
                                yesAction: function() { goToUrl('<! TD_URL !>/admin.php?section=manage&page=flags&act=dodel&id='+fid) },
                                noButton: '{lang.cancel}'
                            }); return false;
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.flags_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='92%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $flag_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=add' ),
                            array( 'arrow_switch', '{lang.menu_reorder}', '<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=reorder' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=flags' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_flags_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_flags_title}', '{lang.help_about_flags_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Flag
    #=======================================

    private function add_flag($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'flags', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $img_html = "<select name='icon' id='icon'>". $this->icon_drop_down( $icon ) ."</select>
                    <div id='icon_preview' style='margin-top:12px'>
                        <span id='icon_preview_img'></span>&nbsp;<span id='icon_preview_text'></span>
                    </div>";

        $this->output .= "<script type='text/javascript'>
                        $(function() {
                            updateIconPreview();

                            $('#name').keyup(function() {
                                updateIconPreview();
                            });

                            $('#icon').change(function() {
                                updateIconPreview();
                            });

                            var simpleUpload = new AjaxUpload('#simple_upload_file', {
                                action: 'admin.php',
                                name: 'Filedata',
                                data: {
                                    section: 'manage',
                                    page: 'flags',
                                    act: 'doupload'
                                },
                                autoSubmit: false,
                                onChange: function(file, ext) {
                                    $('#simple_upload_file .ui-button-text').text(file);
                                },
                                onSubmit: function(file, ext) {
                                    $('#simple_upload').val('{lang.button_uploading}');
                                    $('#simple_upload').attr('disabled', true);
                                },
                                onComplete: function(file, response) {
                                    uploadComplete(null, null, null, response, null);
                                    $('#simple_upload_file .ui-button-text').text('{lang.button_select_image}');
                                    $('#simple_upload').val('{lang.button_upload}');
                                    $('#simple_upload').removeAttr('disabled');
                                }
                            });

                            $('#simple_upload').click(function() {
                                simpleUpload.submit();
                            });
                        });

                        function updateIconPreview() {
                            $('#icon_preview_img').html(\"<img src='<! TD_URL !>/images/flags/\"+$('#icon').val()+\"' alt='*' style='vertical-align:bottom' />\");
                            if ( $('#name').val() ) $('#icon_preview_text').text( $('#name').val() );
                        }

                        function uploadComplete(event, queueID, fileObj, response, data) {
                            jsonResponse = convertFromJson(response);
                            if (jsonResponse.success) {
                                $('#upload_msg').text(jsonResponse.successmsg);
                                $('#icon').append(\"<option value='\"+jsonResponse.file+\"'>\"+jsonResponse.file+\"</option>\");
                            }
                            else {
                                if (jsonResponse.error) {
                                    $('#upload_msg').text(jsonResponse.errormsg);
                                }
                                else {
                                    $('#upload_msg').text('unknown error');
                                }
                            }
                            $('#upload_msg').show('blind');
                            $('#upload_msg').animate({opacity: 1.0}, 5000);
                            $('#upload_msg').hide('blind');
                            return false;
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=doadd", 'add_flag', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_flag}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name' ), 'a', '16%', '84%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.icon}', $img_html, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.upload} '. $this->trellis->skin->help_tip('{lang.tip_upload}'), $this->trellis->skin->uploadify_js( 'upload_file', array( 'section' => 'manage', 'page' => 'flags', 'act' => 'doupload' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_flag}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=flags' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_flags_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Flag
    #=======================================

    private function edit_flag($error="")
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'flags', 'edit' );

        #=============================
        # Grab Flag
        #=============================

        if ( ! $f = $this->trellis->func->flags->get_single_by_id( array( 'id', 'name', 'icon' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_flag');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;

            $icon = $this->trellis->input['icon'];
        }
        else
        {
            $icon = $f['icon'];
        }

        $img_html = "<select name='icon' id='icon'>". $this->icon_drop_down( $icon ) ."</select>
                    <div id='icon_preview' style='margin-top:12px'>
                        <span id='icon_preview_img'></span>&nbsp;<span id='icon_preview_text'></span>
                    </div>";

        $this->output .= "<script type='text/javascript'>
                        $(function() {
                            updateIconPreview();

                            $('#name').keyup(function() {
                                updateIconPreview();
                            });

                            $('#icon').change(function() {
                                updateIconPreview();
                            });

                            var simpleUpload = new AjaxUpload('#simple_upload_file', {
                                action: 'admin.php',
                                name: 'Filedata',
                                data: {
                                    section: 'manage',
                                    page: 'flags',
                                    act: 'doupload'
                                },
                                autoSubmit: false,
                                onChange: function(file, ext) {
                                    $('#simple_upload_file .ui-button-text').text(file);
                                },
                                onSubmit: function(file, ext) {
                                    $('#simple_upload').val('{lang.button_uploading}');
                                    $('#simple_upload').attr('disabled', true);
                                },
                                onComplete: function(file, response) {
                                    uploadComplete(null, null, null, response, null);
                                    $('#simple_upload_file .ui-button-text').text('{lang.button_select_image}');
                                    $('#simple_upload').val('{lang.button_upload}');
                                    $('#simple_upload').removeAttr('disabled');
                                }
                            });

                            $('#simple_upload').click(function() {
                                simpleUpload.submit();
                            });
                        });

                        function updateIconPreview() {
                            $('#icon_preview_img').html(\"<img src='<! TD_URL !>/images/flags/\"+$('#icon').val()+\"' alt='*' style='vertical-align:bottom' />\");
                            if ( $('#name').val() ) $('#icon_preview_text').text( $('#name').val() );
                        }

                        function uploadComplete(event, queueID, fileObj, response, data) {
                            jsonResponse = convertFromJson(response);
                            if (jsonResponse.success) {
                                $('#upload_msg').text(jsonResponse.successmsg);
                                $('#icon').append(\"<option value='\"+jsonResponse.file+\"'>\"+jsonResponse.file+\"</option>\");
                            }
                            else {
                                if (jsonResponse.error) {
                                    $('#upload_msg').text(jsonResponse.errormsg);
                                }
                                else {
                                    $('#upload_msg').text('unknown error');
                                }
                            }
                            $('#upload_msg').show('blind');
                            $('#upload_msg').animate({opacity: 1.0}, 5000);
                            $('#upload_msg').hide('blind');
                            return false;
                        }
                        </script>
                        <div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=doedit&amp;id={$f['id']}", 'edit_flag', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_flag} '. $f['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name', $f['name'] ), 'a', '16%', '84%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.icon}', $img_html, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.upload} '. $this->trellis->skin->help_tip('{lang.tip_upload}'), $this->trellis->skin->uploadify_js( 'upload_file', array( 'section' => 'manage', 'page' => 'flags', 'act' => 'doupload' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_flag}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=flags' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_flags_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Reorder Flags
    #=======================================

    private function reorder_flags()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'flags', 'reorder' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=doreorder", 'reorder_flags', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.reordering_flags}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.reorder_flags_msg}' ) ."
                        <input type='hidden' name='order' id='order' value='' />";

        if ( ! $flags = $this->trellis->func->flags->get( array( 'select' => array( 'id', 'name', 'icon' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $this->output .= "<div class='bluecell-light'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=add'>{lang.no_flags}</a></strong></div>";
        }
        else
        {
            $this->output .= "<ul class='draggable' id='sortable'>";

            foreach( $flags as $fid => $f )
            {
                $this->output .= "<li class='bluecell-light' id='f_{$f['id']}'><img src='<! TD_URL !>/images/flags/{$f['icon']}' alt='{$f['name']}' style='vertical-align:bottom' />&nbsp;&nbsp;{$f['name']}</li>";
            }

            $this->output .= "</ul>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'reorder', '{lang.button_reorder_flags}' ) ) ."
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
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=flags' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=flags&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_flags_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add Flag
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'flags', 'add' );

        if ( ! $this->trellis->input['name'] ) $this->add_flag('no_name');

        #=============================
        # Add Flag
        #=============================

        $db_array = array(
                          'name'    => $this->trellis->input['name'],
                          'icon'    => $this->trellis->input['icon'],
                          );

        $flag_id = $this->trellis->func->flags->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'flag_added', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->flags_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_flag_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Flag
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'flags', 'edit' );

        if ( ! $this->trellis->input['name'] ) $this->edit_flag('no_name');

        #=============================
        # Update Flag
        #=============================

        $db_array = array(
                          'name'    => $this->trellis->input['name'],
                          'icon'    => $this->trellis->input['icon'],
                          );

        $this->trellis->func->flags->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'flag_edited', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->flags_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_flag_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Flag
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'flags', 'delete' );

        #=============================
        # DELETE Flag
        #=============================

        $this->trellis->func->flags->delete( $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'flag_deleted', $this->trellis->cache->data['flags'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->flags_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_flag_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Reorder Flags
    #=======================================

    private function do_reorder()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'flags', 'reorder' );

        #=============================
        # Reorder Flags
        #=============================

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['order'] ), $order );

        if ( empty( $order['f'] ) ) $this->list_flags( 'no_reorder' );

        if ( $flags = $this->trellis->func->flags->get( array( 'select' => array( 'id', 'position' ) ) ) )
        {
            foreach ( $order['f'] as $position => $fid )
            {
                $position ++;

                if ( $position != $flags[ $fid ]['position'] )
                {
                    $this->trellis->func->flags->edit( array( 'position' => $position ), $fid );
                }
            }
        }

        $this->trellis->log( array( 'msg' => 'flags_reordered', 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->flags_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_flags_reordered'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Upload Flag
    #=======================================

    private function do_upload()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'flags' );

        if ( ! $_FILES['Filedata'] ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => 'no data received' ) ) );

        $allowed_exts = array( '.gif', '.jpg', '.jpeg', '.png', '.svg', '.tiff' );

        $file_ext = strrchr( $_FILES['Filedata']['name'], "." );

        if ( ! in_array( $file_ext, $allowed_exts ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_filetype'] ) ) );

        #=============================
        # Upload Image
        #=============================

        $file_name = $this->sanitize_name( $_FILES['Filedata']['name'] );
        $upload_location = TD_PATH .'images/flags/'. $file_name;

        if ( ! @move_uploaded_file( $_FILES['Filedata']['tmp_name'], $upload_location ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_move'] ) ) );

        # TODO: only run chmod if web user is 'nobody' (just have a setting)
        @chmod( $upload_location, 0777 );

        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->ajax_output( json_encode( array( 'success' => true, 'successmsg' => $this->trellis->lang['upload_success'], 'file' => $file_name ) ) );
    }

    #=======================================
    # @ Sanitize Name
    #=======================================

    private function sanitize_name($name)
    {
        $name = str_replace( " ", "_", $name );

        return ereg_replace( "[^A-Za-z0-9_\.]", "", $name );
    }

    #=======================================
    # @ Icon Drop Down
    #=======================================

    private function icon_drop_down($select)
    {
        $html = "";

        if ( @ $files = scandir( TD_PATH .'images/flags' ) )
        {
            foreach( $files as $fid => $fname )
            {
                if ( strpos( $fname, '.' ) != 0 && $fname != 'index.html' )
                {
                    if ( $fname == $select )
                    {
                        $html .= "<option value='". $fname ."' selected='yes'>". $fname ."</option>";
                    }
                    else
                    {
                        $html .= "<option value='". $fname ."'>". $fname ."</option>";
                    }
                }
            }
        }

        return $html;
    }

}

?>