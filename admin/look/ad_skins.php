<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_skins {

    private $output = "";
    private $known_files = array(
        'ad_lang_articles.php'        => 'Admin - Articles', #TODO name from XML!
        'ad_lang_categories.php'    => 'Admin - Categories',
        'ad_lang_cdfields.php'        => 'Admin - Custom Department Fields',
        'ad_lang_cpfields.php'        => 'Admin - Custom Profile Fields',
        'ad_lang_departs.php'        => 'Admin - Departments',
        'ad_lang_emails.php'        => 'Admin - Emails',
        'ad_lang_error.php'            => 'Admin - Error',
        'ad_lang_flags.php'            => 'Admin - Flags',
        'ad_lang_global.php'        => 'Admin - Global',
        'ad_lang_groups.php'        => 'Admin - Groups',
        'ad_lang_home.php'            => 'Admin - Home',
        'ad_lang_skins.php'            => 'Admin - Languages',
        'ad_lang_news.php'            => 'Admin - News',
        'ad_lang_pages.php'            => 'Admin - Pages',
        'ad_lang_priorities.php'    => 'Admin - Priorities',
        'ad_lang_redirect.php'        => 'Admin - Redirect',
        'ad_lang_rtemplates.php'    => 'Admin - Reply Templates',
        'ad_lang_settings.php'        => 'Admin - Settings',
        'ad_lang_statuses.php'        => 'Admin - Satuses',
        'ad_lang_tickets.php'        => 'Admin - Tickets',
        'ad_lang_users.php'            => 'Admin - Users',
        'lang_account.php'            => 'Account',
        'lang_error.php'            => 'Error',
        'lang_global.php'            => 'Global',
        'lang_knowledgebase.php'    => 'Knowledge Base',
        'lang_news.php'                => 'News',
        'lang_redirect.php'            => 'Redirect',
        'lang_register.php'            => 'Register',
        'lang_tickets.php'            => 'Tickets',
    );

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'look', 'skins' );

        $this->trellis->load_functions('skins');
        $this->trellis->load_lang('skins');

        $this->trellis->skin->set_active_link( 3 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_skins();
            break;
            case 'prop':
                $this->prop_skin();
            break;
            case 'edit':
                $this->edit_skin();
            break;
            case 'css':
                $this->edit_css();
            break;
            case 'wrapper':
                $this->edit_wrapper();
            break;
            case 'delete':
                $this->delete_skin();
            break;
            case 'tools':
                $this->tools_skin();
            break;
            case 'import':
                $this->import_skin();
            break;
            case 'export':
                $this->export_skin();
            break;

            case 'doprop':
                $this->do_prop();
            break;
            case 'doedit':
                $this->do_edit();
            break;
            case 'docss':
                $this->do_css();
            break;
            case 'dowrapper':
                $this->do_wrapper();
            break;
            case 'dodel':
                $this->do_delete();
            break;
            case 'dodefault':
                $this->do_default();
            break;
            case 'dotools':
                $this->do_tools();
            break;
            case 'doimport':
                $this->do_import();
            break;
            case 'doexport':
                $this->do_export();
            break;

            default:
                $this->list_skins();
            break;
        }
    }

    #=======================================
    # @ List Skins
    #=======================================

    private function list_skins()
    {
        #=============================
        # Grab Skins
        #=============================

        $skin_rows = "";

        $skins = $this->trellis->func->skins->get( array( 'select' => 'all', 'order' => array( 'name' => 'asc' ) ) );

        foreach( $skins as $s )
        {
            if ( $s['default'] )
            {
                $s['default_button'] = "<img src='<! IMG_DIR !>/icons/circle_tick.png' alt='tick' />&nbsp; Is Default";
            }
            else
            {
                $s['default_button'] = "<a href='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=dodefault&amp;id={$s['id']}'><img src='<! IMG_DIR !>/icons/circle_delete.png' alt='cross' />&nbsp; Make Default</a>";
                $s['delete_button'] = "<br /><a href='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=delete&amp;id={$s['id']}'><img src='<! IMG_DIR !>/button_delete.gif' alt='Delete' style='margin-top:5px;' /></a>";
            }

            $si = @simplexml_load_file( TD_SKIN .'s'. $s['id'] .'/info.xml' );

            $sdesc = "<span style='font-size:12px;'>{$s['name']}</span><br /><span style='font-weight: normal'>";
            if ( $si->description ) $sdesc .= "{$si->description}<br /><br />";
            if ( $si->version_human ) $sdesc .= "<em>Version:</em> {$si->version_human}<br />";
            if ( $si->author ) $sdesc .= "<em>Created by:</em> {$si->author}<br />";
            if ( $si->copyright ) $sdesc .= "<em>Copyright:</em> {$si->copyright}<br />";
            if ( $si->version_min_human ) $sdesc .= "<em>Designed for:</em> {$si->version_min_human}";
            $sdesc .= "</span>";

            $skin_rows .= "<tr>
                               <td class='bluecellthin-light'><img src='<! TD_URL !>/skins/s{$s['id']}/images/preview.jpg' alt='{$s['name']}' /></td>
                            <td class='bluecellthin-dark skin_desc'>{$sdesc}</td>
                            <td class='bluecellthin-light skin_options'>{$s['default_button']}<br /><a href='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=css&amp;id={$s['id']}'><img src='<! IMG_DIR !>/icons/script_edit.png' alt='Edit code' />&nbsp; Edit CSS</a><br /><a href='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=wrapper&amp;id={$s['id']}'><img src='<! IMG_DIR !>/icons/script_edit.png' alt='Edit code' />&nbsp; Edit Wrapper</a><br /><a href='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=edit&amp;id={$s['id']}'><img src='<! IMG_DIR !>/icons/script_edit.png' alt='Edit code' />&nbsp; Edit Templates</a><br /><a href='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=prop&amp;id={$s['id']}'><img src='<! IMG_DIR !>/icons/script_edit.png' alt='Edit properties' />&nbsp; Edit Properties</a>{$s['delete_button']}</td>
                        </tr>";
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.skins_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='1%' align='left'>{lang.preview}</th>
                            <th class='bluecellthin-th' width='80%' align='left'>{lang.information}</th>
                            <th class='bluecellthin-th' width='19%' align='center'>{lang.options}</th>
                        </tr>
                        ". $skin_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'drill', '{lang.menu_tools}', '<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=tools' ),
                            array( 'folder_import', '{lang.menu_import}', '<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=import' ),
                            array( 'folder_export', '{lang.menu_export}', '<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=export' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=skins' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_skins_title}', '{lang.help_about_skins_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Properties
    #=======================================

    private function prop_skin($error='')
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Grab Skin
        #=============================

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        #=============================
        # Do Output
        #=============================

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=doprop&amp;id={$s['id']}", 'edit_prop', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_properties} '. $s['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name', $s['name'] ), 'a', '8%', '92%' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_properties}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Skin
    #=======================================

    private function edit_skin()
    {
        if ( $this->trellis->input['file'] ) $this->edit_file();

        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Grab Files
        #=============================

        $file_rows = "";

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        if ( ! $files = $this->trellis->func->skins->files( $s['id'] ) ) $this->trellis->skin->error('no_skin');

        foreach( $files as $f )
        {
            $name = ( $this->known_files[ $f ] ) ? $this->known_files[ $f ] : $f;

            $file_rows .= "<tr>
                                <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=edit&amp;id={$s['id']}&file=". urlencode( base64_encode( $f ) ) ."'>{$name}</a></td>
                            </tr>";
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.files_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.select_file_instructions}</th>
                        </tr>
                        ". $file_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_skins_title}', '{lang.help_about_skins_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Skin File
    #=======================================

    private function edit_file()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Load File
        #=============================

        $bit_rows = "";

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        if ( ! $content = $this->trellis->func->skins->get_template( $s['id'], ( $file = base64_decode( $this->trellis->input['file'] ) ) ) ) $this->trellis->skin->error('no_skin');

        #=============================
        # Do Output
        #=============================

        if ( ! $this->trellis->func->skins->writeable( $s['id'], $file ) ) $this->trellis->send_message( 'error', $this->trellis->lang['error_writeable'].' '. TD_SKIN .'s'. $s['id'] .'/templates/'. $file );

        $name = ( $this->known_files[ $file ] ) ? $this->known_files[ $file ] : $file;

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=doedit&amp;id={$s['id']}&amp;file=". urlencode( base64_encode( $file ) ), 'edit_file', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.editing_file} '. $name ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->textarea( array( 'name' => 'content', 'value' => $this->trellis->func->skins->prepare_html( $content ), 'cols' => 60, 'rows' => 15, 'width' => '98%', 'height' => '200px' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_file}' ) ) ."
                        </div>
                        <script type='text/javascript'>
                        $(function() {
                            if ($('#content').height() < $('#content').attr('scrollHeight')) {
                                if ($('#content').attr('scrollHeight') < 450) {
                                    $('#content').height($('#content').attr('scrollHeight'));
                                } else {
                                $('#content').height(450);
                                }
                            }
                        });
                        </script>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_skins_title}', '{lang.help_about_skins_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit CSS
    #=======================================

    private function edit_css()
    {
        if ( $this->trellis->input['file'] ) $this->edit_css_file();

        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Grab Files
        #=============================

        $file_rows = "";

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        if ( ! $files = $this->trellis->func->skins->files_css( $s['id'] ) ) $this->trellis->skin->error('no_skin');

        foreach( $files as $f )
        {
            $name = ( $this->known_css[ $f ] ) ? $this->known_css[ $f ] : $f;

            $file_rows .= "<tr>
                                <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=css&amp;id={$s['id']}&file=". urlencode( base64_encode( $f ) ) ."'>{$name}</a></td>
                            </tr>";
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.files_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.select_css_instructions}</th>
                        </tr>
                        ". $file_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_skins_title}', '{lang.help_about_skins_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit CSS File
    #=======================================

    private function edit_css_file()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Load File
        #=============================

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        if ( ! $content = $this->trellis->func->skins->get_css( $s['id'], ( $file = base64_decode( $this->trellis->input['file'] ) ) ) ) $this->trellis->skin->error('no_skin');

        #=============================
        # Do Output
        #=============================

        if ( ! $this->trellis->func->skins->writeable_css( $s['id'], $file ) ) $this->trellis->send_message( 'error', $this->trellis->lang['error_writeable'].' '. TD_SKIN .'s'. $s['id'] .'/css/'. $file );

        $name = ( $this->known_css[ $file ] ) ? $this->known_css[ $file ] : $file;

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=docss&amp;id={$s['id']}&amp;file=". urlencode( base64_encode( $file ) ), 'edit_file', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.editing_css} '. $name ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->textarea( array( 'name' => 'content', 'value' => $this->trellis->func->skins->prepare_html( $content ), 'cols' => 60, 'rows' => 33, 'width' => '98%', 'height' => '450px' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_css}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_skins_title}', '{lang.help_about_skins_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Wrapper
    #=======================================

    private function edit_wrapper()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Load File
        #=============================

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        if ( ! $content = $this->trellis->func->skins->get_wrapper( $s['id'] ) ) $this->trellis->skin->error('no_skin');

        #=============================
        # Do Output
        #=============================

        if ( ! $this->trellis->func->skins->writeable_wrapper( $s['id'] ) ) $this->trellis->send_message( 'error', $this->trellis->lang['error_writeable'].' '. TD_SKIN .'s'. $s['id'] .'/templates/wrapper.tpl' );

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=dowrapper&amp;id={$s['id']}", 'edit_file', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.editing_wrapper}' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->textarea( array( 'name' => 'content', 'value' => $this->trellis->func->skins->prepare_html( $content ), 'cols' => 60, 'rows' => 33, 'width' => '98%', 'height' => '450px' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_wrapper}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_skins_title}', '{lang.help_about_skins_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Delete Skin Form
    #=======================================

    private function delete_skin()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'delete' );

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=dodel&amp;id={$s['id']}", 'delete_skin', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.deleting_skin} '. $s['name'] ) ."
                        ". $this->trellis->skin->group_sub( '{lang.delete_warning}' ) ."
                        ". $this->trellis->skin->group_row( "<label for='switchto'>{lang.switch_current}</label> <select name='switchto'>". $this->trellis->func->drop_downs->skin_drop( 0, $s['id'] ) ."</select>" ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_delete_skin}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'folder_import', '{lang.menu_import}', '<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=import' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Skin Tools
    #=======================================

    private function tools_skin()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'tools' );

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=dotools", 'skin_tools', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.skin_tools}' ) ."
                        <div class='option1'><input type='radio' name='action' id='action1' value='1' checked='checked' /> <label for='action1'>{lang.switch_all}</label> <select name='switchall'>". $this->trellis->func->drop_downs->skin_drop() ."</select></div>
                        <div class='option2'><input type='radio' name='action' id='action2' value='2' /> <label for='action2'>{lang.switch_from}</label> <select name='switchfrom'>". $this->trellis->func->drop_downs->skin_drop() ."</select> <label for='action2'>{lang.switch_to}</label> <select name='switchto'>". $this->trellis->func->drop_downs->skin_drop() ."</select></div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_execute}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Import Skin
    #=======================================

    private function import_skin()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'import' );

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        <form action='<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=doimport' method='post' enctype='multipart/form-data'>
                        ". $this->trellis->skin->start_group( 'a' ) ."
                        ". $this->trellis->skin->group_title( '{lang.import_skin}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.import_instructions}' ) ."
                        ". $this->trellis->skin->group_row( "<input type='file' name='skin_file' id='skin_file' size='40' />", 'a' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->checkbox( array( 'name' => 'css', 'title' => '{lang.import_css}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->checkbox( array( 'name' => 'images', 'title' => '{lang.import_images}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->checkbox( array( 'name' => 'scripts', 'title' => '{lang.import_scripts}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_row( $this->trellis->skin->checkbox( array( 'name' => 'other', 'title' => '{lang.import_other}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'import', '{lang.button_import}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'folder_export', '{lang.menu_export}', '<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=export' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_skins_title}', '{lang.help_about_skins_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Export Skin
    #=======================================

    private function export_skin()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'export' );

        #=============================
        # Grab Skins
        #=============================

        $skin_rows = "";

        $skins = $this->trellis->func->skins->get( array( 'select' => array( 'id', 'name' ), 'order' => array( 'name' => 'asc' ) ) );

        foreach( $skins as $s )
        {
            $skin_rows .= "<tr>
                                <td class='bluecellthin-light' width='2%'><input type='radio' name='id' id='skin_{$s['id']}' value='{$s['id']}' /></td>
                                <td class='bluecellthin-dark' width='98%'><label for='skin_{$s['id']}'>{$s['name']}</label></td>
                            </tr>";
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=doexport", 'export_skin', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.skins_list}', 'a' ) ."
                        <tr>
                            <th class='bluecellthin-th' align='left' colspan='2'>{lang.export_instructions}</th>
                        </tr>
                        ". $skin_rows ."
                        </table>
                        <table width='100%' cellpadding='0' cellspacing='0'>
                        <tr>
                            <th class='bluecellthin-th' align='left' colspan='2'>{lang.export_options}</th>
                        </tr>
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name' ), 'a', '30%', '70%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.description}', $this->trellis->skin->textarea( array( 'name' => 'description', 'width' => '80%', 'height' => '50px' ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.author}', $this->trellis->skin->textfield( 'author' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.copyright}', $this->trellis->skin->textfield( 'copy' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.version_num} '. $this->trellis->skin->help_tip('{lang.tip_version_num}'), $this->trellis->skin->textfield( 'version_num' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.version_human} '. $this->trellis->skin->help_tip('{lang.tip_version_human}'), $this->trellis->skin->textfield( 'version_human' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.td_version_num} '. $this->trellis->skin->help_tip('{lang.tip_version_num}'), $this->trellis->skin->textfield( 'td_version_num' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.td_version_human} '. $this->trellis->skin->help_tip('{lang.tip_td_version_human}'), $this->trellis->skin->textfield( 'td_version_human' ), 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row( $this->trellis->skin->checkbox( array( 'name' => 'css', 'title' => '{lang.export_css}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row( $this->trellis->skin->checkbox( array( 'name' => 'images', 'title' => '{lang.export_images}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row( $this->trellis->skin->checkbox( array( 'name' => 'scripts', 'title' => '{lang.export_scripts}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row( $this->trellis->skin->checkbox( array( 'name' => 'other', 'title' => '{lang.export_other}', 'value' => 1 ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_export}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=skins' ),
                            array( 'folder_import', '{lang.menu_import}', '<! TD_URL !>/admin.php?section=look&amp;page=skins&amp;act=import' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_skins_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_skins_title}', '{lang.help_about_skins_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Edir Properties
    #=======================================

    private function do_prop()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        if ( ! $this->trellis->input['name'] ) $this->prop_skin('no_name');

        #=============================
        # Update Properties
        #=============================

        $this->trellis->func->skins->edit_prop( array( 'name' => $this->trellis->input['name'] ), $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'skin_prop', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->skins_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_skin_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Edit File
    #=======================================

    private function do_edit()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Load File
        #=============================

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        if ( ! $this->trellis->func->skins->writeable( $s['id'], ( $file = base64_decode( $this->trellis->input['file'] ) ) ) ) $this->edit_file();

        #=============================
        # Update File
        #=============================

        $this->trellis->func->skins->edit( $this->trellis->convert_html( $this->trellis->input['content'] ), $s['id'], $file );

        $this->trellis->log( array( 'msg' => array( 'skin_file', $s['name'], ( ( $this->known_files[ $file ] ) ? $this->known_files[ $file ] : $file ) ), 'type' => 'other' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_skin_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'edit', 'id' => $s['id'] ) );
    }

    #=======================================
    # @ Do CSS
    #=======================================

    private function do_css()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Load File
        #=============================

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        if ( ! $this->trellis->func->skins->writeable_css( $s['id'], ( $file = base64_decode( $this->trellis->input['file'] ) ) ) ) $this->edit_css();

        #=============================
        # Update File
        #=============================

        $this->trellis->func->skins->edit_css( $this->trellis->convert_html( $this->trellis->input['content'] ), $s['id'], $file );

        $this->trellis->log( array( 'msg' => array( 'skin_css', $s['name'], ( ( $this->known_css[ $file ] ) ? $this->known_css[ $file ] : $file ) ), 'type' => 'other' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_skin_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'css', 'id' => $s['id'] ) );
    }

    #=======================================
    # @ Do Wrapper
    #=======================================

    private function do_wrapper()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Load File
        #=============================

        if ( ! $s = $this->trellis->func->skins->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_skin');

        if ( ! $this->trellis->func->skins->writeable_wrapper( $s['id'] ) ) $this->edit_wrapper();

        #=============================
        # Update File
        #=============================

        $this->trellis->func->skins->edit_wrapper( $this->trellis->convert_html( $this->trellis->input['content'] ), $s['id'] );

        $this->trellis->log( array( 'msg' => array( 'skin_wrapper', $s['name'] ), 'type' => 'other' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_skin_updated'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Delete Skin
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'delete' );

        if ( $this->trellis->cache->data['misc']['default_skin'] == $this->trellis->input['id'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_delete_default'] );

            $this->trellis->skin->redirect( array( 'act' => null ) );
        }

        if ( ! $this->trellis->cache->data['skins'][ $this->trellis->input['switchto'] ] ) $this->trellis->skin->error('no_skin');

        #=============================
        # DELETE Skin
        #=============================

        if ( ! $this->trellis->func->skins->delete( $this->trellis->input['id'], $this->trellis->input['switchto'] ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_delete'] );

            $this->trellis->skin->redirect( array( 'act' => null ) );
        }

        $this->trellis->log( array( 'msg' => array( 'skin_deleted', $this->trellis->cache->data['skins'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->skins_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_skin_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Default Skin
    #=======================================

    private function do_default()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'edit' );

        #=============================
        # Default Skin
        #=============================

        $this->trellis->func->skins->set_default( $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'skin_default', $this->trellis->cache->data['skins'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->skins_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_skin_default'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Skin Tools
    #=======================================

    private function do_tools()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'tools' );

        #=============================
        # Perform Action
        #=============================

        if ( $this->trellis->input['action'] == 1 )
        {
            if ( ! $this->trellis->cache->data['skins'][ $this->trellis->input['switchall'] ] ) $this->trellis->skin->error('no_skin');

            $this->trellis->db->construct( array(
                'update'    => 'users',
                'set'        => array( 'skin' => $this->trellis->input['switchall'] ),
            ) );

            $this->trellis->db->execute();
        }
        elseif ( $this->trellis->input['action'] == 1 )
        {
            if ( ! $this->trellis->cache->data['skins'][ $this->trellis->input['switchfrom'] ] ) $this->trellis->skin->error('no_skin');
            if ( ! $this->trellis->cache->data['skins'][ $this->trellis->input['switchto'] ] ) $this->trellis->skin->error('no_skin');

            $this->trellis->db->construct( array(
                'update'    => 'users',
                'set'        => array( 'skin' => $this->trellis->input['switchto'] ),
                'where'        => array( 'skin', '=', $this->trellis->input['switchfrom'] ),
            ) );

            $this->trellis->db->execute();
        }

        $this->trellis->log( array( 'msg' => array( 'skin_tools' ), 'type' => 'other' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_tool_action'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Import Skin
    #=======================================

    private function do_import()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'import' );

        if ( ! $_FILES['skin_file']['size'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_file'] );
            $this->import_skin();
        }

        #=============================
        # Import Skin
        #=============================

        $options = array(
            'css'        => $this->trellis->input['css'],
            'images'    => $this->trellis->input['images'],
            'scripts'    => $this->trellis->input['scripts'],
            'other'        => $this->trellis->input['other'],
        );

        if ( ! $skin = $this->trellis->func->skins->import( $_FILES['skin_file']['tmp_name'], $options ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_import'] );
            $this->import_skin();
        }

        $this->trellis->log( array( 'msg' => array( 'skin_imported', $skin['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->skins_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_skin_imported'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Export Skin
    #=======================================

    private function do_export()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'skins', 'export' );

        #=============================
        # Export Skin
        #=============================

        $options = array(
            'name'                    => $this->trellis->input['name'],
            'description'            => $this->trellis->input['description'],
            'author'                => $this->trellis->input['author'],
            'copyright'                => $this->trellis->input['copy'],
            'version'                => $this->trellis->input['version_num'],
            'version_human'            => $this->trellis->input['version_human'],
            'td_version_min'        => $this->trellis->input['td_version_num'],
            'td_version_min_human'    => $this->trellis->input['td_version_human'],
            'css'                    => $this->trellis->input['css'],
            'images'                => $this->trellis->input['images'],
            'scripts'                => $this->trellis->input['scripts'],
            'other'                    => $this->trellis->input['other'],
        );

        if ( ! $this->trellis->func->skins->export( $this->trellis->input['id'], $options ) ) $this->trellis->skin->error('no_skin');

        $this->trellis->shut_down();

        exit();
    }
}

?>