<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_priorities {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'priorities' );

        $this->trellis->load_functions('priorities');
        $this->trellis->load_lang('priorities');

        $this->trellis->skin->set_active_link( 2 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_priorities();
            break;
            case 'add':
                $this->add_priority();
            break;
            case 'edit':
                $this->edit_priority();
            break;
            case 'delete':
                $this->delete_priority();
            break;
            case 'reorder':
                $this->reorder_priorities();
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
            case 'default':
                $this->do_default();
            break;
            case 'doreorder':
                $this->do_reorder();
            break;

            default:
                $this->list_priorities();
            break;
        }
    }

    #=======================================
    # @ List Priorities
    #=======================================

    private function list_priorities()
    {
        #=============================
        # Grab Priorities
        #=============================

        $priority_rows = "";

        if ( ! $priorities = $this->trellis->func->priorities->get( array( 'select' => array( 'id', 'name', 'icon_regular', 'default' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $priority_rows .= "<tr><td class='bluecell-light' colspan='5'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=add'>{lang.no_priorities}</a></strong></td></tr>";
        }
        else
        {
            foreach( $priorities as $pid => $p )
            {
                if ( ! $p['default'] )
                {
                    $p['default_button'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=default&amp;id={$p['id']}'>{$p['default']}</a>";
                    $p['delete_button'] = "<a href='<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=delete&amp;id={$p['id']}'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a>";
                }

                $priority_rows .= "<tr>
                                    <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fpriority". urlencode('[]') ."={$p['id']}'><strong>{$p['id']}</strong></a></td>
                                    <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=tickets&amp;fpriority". urlencode('[]') ."={$p['id']}'><img src='<! TD_URL !>/images/priorities/{$p['icon_regular']}' alt='{$p['name']}' class='prioritybox' />&nbsp;&nbsp;{$p['name']}</a></td>
                                    <td class='bluecellthin-light' align='center'>{$p['default_button']}</td>
                                    <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=edit&amp;id={$p['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                    <td class='bluecellthin-light' align='center'>{$p['delete_button']}</td>
                                </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.priorities_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='89%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.default}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $priority_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=add' ),
                            array( 'arrow_switch', '{lang.menu_reorder}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=reorder' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=priorities' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_priorities_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_priorities_title}', '{lang.help_about_priorities_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Add Priority
    #=======================================

    private function add_priority($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'add' );

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $img_html = "<table width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                        <td width='25%' valign='top'>
                            {lang.icon_regular}<br />
                            <select name='icon_regular' id='icon_regular' style='margin-top:6px'>". $this->icon_drop_down( $icon_regular ) ."</select>
                            <div id='regular_preview' style='margin-top:12px'>
                                <span id='regular_preview_img'></span>&nbsp;<span id='regular_preview_text'></span>
                            </div>
                        </td>
                        <td width='75%' valign='top'>
                            {lang.icon_assigned}<br />
                            <select name='icon_assigned' id='icon_assigned' style='margin-top:6px'>". $this->icon_drop_down( $icon_assigned ) ."</select>
                            <div id='assigned_preview' style='margin-top:12px'>
                                <span id='assigned_preview_img'></span>&nbsp;<span id='assigned_preview_text'></span>
                            </div>
                        </td>
                    </tr>
                    </table>";

        $this->output .= "<script type='text/javascript'>
                        $(function() {
                            updateRegularPreview();
                            updateAssignedPreview();

                            $('#name').keyup(function() {
                                updateRegularPreview();
                                updateAssignedPreview();
                            });

                            $('#icon_regular').change(function() {
                                updateRegularPreview();
                            });

                            $('#icon_assigned').change(function() {
                                updateAssignedPreview();
                            });

                            var simpleUpload = new AjaxUpload('#simple_upload_file', {
                                action: 'admin.php',
                                name: 'Filedata',
                                data: {
                                    section: 'manage',
                                    page: 'priorities',
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

                        function updateRegularPreview() {
                            $('#regular_preview_img').html(\"<img src='<! TD_URL !>/images/priorities/\"+$('#icon_regular').val()+\"' alt='*' class='prioritybox' />\");
                            if ( $('#name').val() ) $('#regular_preview_text').text( $('#name').val() );
                        }

                        function updateAssignedPreview() {
                            $('#assigned_preview_img').html(\"<img src='<! TD_URL !>/images/priorities/\"+$('#icon_assigned').val()+\"' alt='*' class='prioritybox' />\");
                            if ( $('#name').val() ) $('#assigned_preview_text').text( $('#name').val() );
                        }

                        function uploadComplete(event, queueID, fileObj, response, data) {
                            jsonResponse = convertFromJson(response);
                            if (jsonResponse.success) {
                                $('#upload_msg').text(jsonResponse.successmsg);
                                $('#icon_regular').append(\"<option value='\"+jsonResponse.file+\"'>\"+jsonResponse.file+\"</option>\");
                                $('#icon_assigned').append(\"<option value='\"+jsonResponse.file+\"'>\"+jsonResponse.file+\"</option>\");
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
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=doadd", 'add_priority', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.adding_priority}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name' ), 'a', '16%', '84%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.icon} '. $this->trellis->skin->help_tip('{lang.tip_icon}'), $img_html, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.upload} '. $this->trellis->skin->help_tip('{lang.tip_upload}'), $this->trellis->skin->uploadify_js( 'upload_file', array( 'section' => 'manage', 'page' => 'priorities', 'act' => 'doupload' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'add', '{lang.button_add_priority}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );
        $this->output .= $this->trellis->skin->focus_js('name');

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_priorities_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Priority
    #=======================================

    private function edit_priority($error="")
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'edit' );

        #=============================
        # Grab Priority
        #=============================

        if ( ! $p = $this->trellis->func->priorities->get_single_by_id( array( 'id', 'name', 'icon_regular', 'icon_assigned' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_priority');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;

            $icon_regular = $this->trellis->input['icon_regular'];
            $icon_assigned = $this->trellis->input['icon_assigned'];
        }
        else
        {
            $icon_regular = $p['icon_regular'];
            $icon_assigned = $p['icon_assigned'];
        }

        $img_html = "<table width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                        <td width='25%' valign='top'>
                            {lang.icon_regular}<br />
                            <select name='icon_regular' id='icon_regular' style='margin-top:6px'>". $this->icon_drop_down( $icon_regular ) ."</select>
                            <div id='regular_preview' style='margin-top:12px'>
                                <span id='regular_preview_img'></span>&nbsp;<span id='regular_preview_text'></span>
                            </div>
                        </td>
                        <td width='75%' valign='top'>
                            {lang.icon_assigned}<br />
                            <select name='icon_assigned' id='icon_assigned' style='margin-top:6px'>". $this->icon_drop_down( $icon_assigned ) ."</select>
                            <div id='assigned_preview' style='margin-top:12px'>
                                <span id='assigned_preview_img'></span>&nbsp;<span id='assigned_preview_text'></span>
                            </div>
                        </td>
                    </tr>
                    </table>";

        $this->output .= "<script type='text/javascript'>
                        $(function() {
                            updateRegularPreview();
                            updateAssignedPreview();

                            $('#name').keyup(function() {
                                updateRegularPreview();
                                updateAssignedPreview();
                            });

                            $('#icon_regular').change(function() {
                                updateRegularPreview();
                            });

                            $('#icon_assigned').change(function() {
                                updateAssignedPreview();
                            });

                            var simpleUpload = new AjaxUpload('#simple_upload_file', {
                                action: 'admin.php',
                                name: 'Filedata',
                                data: {
                                    section: 'manage',
                                    page: 'priorities',
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

                        function updateRegularPreview() {
                            $('#regular_preview_img').html(\"<img src='<! TD_URL !>/images/priorities/\"+$('#icon_regular').val()+\"' alt='*' class='prioritybox' />\");
                            if ( $('#name').val() ) $('#regular_preview_text').text( $('#name').val() );
                        }

                        function updateAssignedPreview() {
                            $('#assigned_preview_img').html(\"<img src='<! TD_URL !>/images/priorities/\"+$('#icon_assigned').val()+\"' alt='*' class='prioritybox' />\");
                            if ( $('#name').val() ) $('#assigned_preview_text').text( $('#name').val() );
                        }

                        function uploadComplete(event, queueID, fileObj, response, data) {
                            jsonResponse = convertFromJson(response);
                            if (jsonResponse.success) {
                                $('#upload_msg').text(jsonResponse.successmsg);
                                $('#icon_regular').append(\"<option value='\"+jsonResponse.file+\"'>\"+jsonResponse.file+\"</option>\");
                                $('#icon_assigned').append(\"<option value='\"+jsonResponse.file+\"'>\"+jsonResponse.file+\"</option>\");
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
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=doedit&amp;id={$p['id']}", 'edit_priority', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_priority}', 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name', $p['name'] ), 'a', '16%', '84%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.icon} '. $this->trellis->skin->help_tip('{lang.tip_icon}'), $img_html, 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.upload} '. $this->trellis->skin->help_tip('{lang.tip_upload}'), $this->trellis->skin->uploadify_js( 'upload_file', array( 'section' => 'manage', 'page' => 'priorities', 'act' => 'doupload' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_priority}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_priorities_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Delete Priority Form
    #=======================================

    private function delete_priority()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'delete' );

        if ( ! $p = $this->trellis->func->priorities->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_priority');

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=dodel&amp;id={$p['id']}", 'delete_priority', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.deleting_priority} '. $p['name'] ) ."
                        ". $this->trellis->skin->group_sub( '{lang.delete_priority_tickets_qs}' ) ."
                        <div class='option1'><input type='radio' name='action' id='action1' value='1' checked='checked' /> <label for='action1'>{lang.change_tickets_to_priority}</label> <select name='changeto'>". $this->trellis->func->drop_downs->priority_drop( array( 'exclude' => $p['id'] ) ) ."</select></div>
                        <div class='option2'><input type='radio' name='action' id='action2' value='2' /> <label for='action2'>{lang.delete_tickets}</label></div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_delete_priority}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_priorities_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Reorder Priorities
    #=======================================

    private function reorder_priorities()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'reorder' );

        #=============================
        # Do Output
        #=============================

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=doreorder", 'reorder_priorities', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.reordering_priorities}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.reorder_priorities_msg}' ) ."
                        <input type='hidden' name='order' id='order' value='' />";

        if ( ! $priorities = $this->trellis->func->priorities->get( array( 'select' => array( 'id', 'name', 'icon_regular' ), 'order' => array( 'position' => 'asc' ) ) ) )
        {
            $this->output .= "<div class='bluecell-light'><strong><a href='<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=add'>{lang.no_priorities}</a></strong></div>";
        }
        else
        {
            $this->output .= "<ul class='draggable' id='sortable'>";

            foreach( $priorities as $pid => $p )
            {
                $this->output .= "<li class='bluecell-light' id='p_{$p['id']}'><img src='<! TD_URL !>/images/priorities/{$p['icon_regular']}' alt='{$p['name']}' />&nbsp;&nbsp;{$p['name']}</li>";
            }

            $this->output .= "</ul>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'reorder', '{lang.button_reorder_priorities}' ) ) ."
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
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities' ),
                            array( 'circle_plus', '{lang.menu_add}', '<! TD_URL !>/admin.php?section=manage&amp;page=priorities&amp;act=add' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=ticket' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_priorities_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Add Priority
    #=======================================

    private function do_add()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'add' );

        if ( ! $this->trellis->input['name'] ) $this->add_priority('no_name');

        #=============================
        # Add Priority
        #=============================

        $db_array = array(
                          'name'            => $this->trellis->input['name'],
                          'icon_regular'    => $this->trellis->input['icon_regular'],
                          'icon_assigned'    => $this->trellis->input['icon_assigned'],
                          );

        $priority_id = $this->trellis->func->priorities->add( $db_array );

        $this->trellis->log( array( 'msg' => array( 'priority_added', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->priorities_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_priority_added'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit Priority
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'edit' );

        if ( ! $this->trellis->input['name'] ) $this->edit_priority('no_name');

        #=============================
        # Update Priority
        #=============================

        $db_array = array(
                          'name'            => $this->trellis->input['name'],
                          'icon_regular'    => $this->trellis->input['icon_regular'],
                          'icon_assigned'    => $this->trellis->input['icon_assigned'],
                          );

        $this->trellis->func->priorities->edit( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'priority_edited', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->priorities_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_priority_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Priority
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'delete' );

        if ( $this->trellis->cache->data['priorities'][ $this->trellis->input['id'] ]['default'] ) $this->list_priorities( 'cannot_delete_default' );

        #=============================
        # DELETE Priority
        #=============================

        $this->trellis->func->priorities->delete( $this->trellis->input['id'], $this->trellis->input['action'], $this->trellis->input['changeto'] );

        $this->trellis->log( array( 'msg' => array( 'priority_deleted', $this->trellis->cache->data['priorities'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->priorities_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_priority_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Default Priority
    #=======================================

    private function do_default()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'edit' );

        #=============================
        # Default Priority
        #=============================

        $this->trellis->func->priorities->set_default( $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'priority_default', $this->trellis->cache->data['priorities'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->priorities_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_priority_default'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Reorder Priorities
    #=======================================

    private function do_reorder()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities', 'reorder' );

        #=============================
        # Reorder Priorities
        #=============================

        parse_str( str_replace( '&amp;', '&', $this->trellis->input['order'] ), $order );

        if ( empty( $order['p'] ) ) $this->list_priorities( 'no_reorder' );

        if ( $priorities = $this->trellis->func->priorities->get( array( 'select' => array( 'id', 'position' ) ) ) )
        {
            foreach ( $order['p'] as $position => $pid )
            {
                $position ++;

                if ( $position != $priorities[ $pid ]['position'] )
                {
                    $this->trellis->func->priorities->edit( array( 'position' => $position ), $pid );
                }
            }
        }

        $this->trellis->log( array( 'msg' => 'priorities_reordered', 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->priorities_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_priorities_reordered'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Upload Priority
    #=======================================

    private function do_upload()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'priorities' );

        if ( ! $_FILES['Filedata'] ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => 'no data received' ) ) );

        $allowed_exts = array( '.gif', '.jpg', '.jpeg', '.png', '.svg', '.tiff' );

        $file_ext = strrchr( $_FILES['Filedata']['name'], "." );

        if ( ! in_array( $file_ext, $allowed_exts ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_filetype'] ) ) );

        #=============================
        # Upload Image
        #=============================

        $file_name = $this->sanitize_name( $_FILES['Filedata']['name'] );
        $upload_location = TD_PATH .'images/priorities/'. $file_name;

        if ( ! @move_uploaded_file( $_FILES['Filedata']['tmp_name'], $upload_location ) ) $this->trellis->skin->ajax_output( json_encode( array( 'error' => true, 'errormsg' => $this->trellis->lang['error_upload_move'] ) ) );

        # TODO: only run chmod if web user is 'nobody' (just have a setting)
        @chmod( $upload_location, 0777 );

        #=============================
        # Do Output
        #=============================

        $this->trellis->skin->ajax_output( json_encode( array( 'success' => true, 'successmsg' => $this->trellis->lang['upload_success'], 'file' => $file_name ) ) );

        exit();
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

        if ( @ $files = scandir( TD_PATH .'images/priorities' ) )
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