<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_langs {

    private $output = "";
    private $known_files = array(
        'ad_lang_articles.php'        => 'Admin - Articles',
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
        'ad_lang_languages.php'        => 'Admin - Languages',
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
        $this->trellis->check_perm( 'look', 'langs' );

        $this->trellis->load_functions('languages');
        $this->trellis->load_lang('languages');

        $this->trellis->skin->set_active_link( 3 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_languages();
            break;
            case 'prop':
                $this->prop_language();
            break;
            case 'edit':
                $this->edit_language();
            break;
            case 'delete':
                $this->delete_language();
            break;
            case 'tools':
                $this->tools_language();
            break;
            case 'import':
                $this->import_language();
            break;
            case 'export':
                $this->export_language();
            break;

            case 'doprop':
                $this->do_prop();
            break;
            case 'doedit':
                $this->do_edit();
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
                $this->list_languages();
            break;
        }
    }

    #=======================================
    # @ List Languages
    #=======================================

    private function list_languages()
    {
        #=============================
        # Grab Languages
        #=============================

        $language_rows = "";

        $languages = $this->trellis->func->languages->get( array( 'select' => array( 'id', 'key', 'name', 'users', 'default' ), 'order' => array( 'key' => 'asc' ) ) );

        foreach( $languages as $l )
        {
            if ( ! $l['default'] )
            {
                $l['default_button'] = "<a href='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=dodefault&amp;id={$l['id']}'>{$l['default']}</a>";
                $l['delete_button'] = "<a href='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=delete&amp;id={$l['id']}'><img src='<! IMG_DIR !>/button_delete.gif' alt='{lang.delete}' /></a>";
            }

            $language_rows .= "<tr>
                                <td class='bluecellthin-light'><strong>{$l['id']}</strong></td>
                                <td class='bluecellthin-light'><strong>{$l['key']}</strong></td>
                                <td class='bluecellthin-dark'>{$l['name']}</td>
                                <td class='bluecellthin-dark' align='center'>{$l['users']}</td>
                                <td class='bluecellthin-light' align='center'>{$l['default_button']}</td>
                                <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=prop&amp;id={$l['id']}'>Edit Properties</a></td>
                                <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=edit&amp;id={$l['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                                <td class='bluecellthin-light' align='center'>{$l['delete_button']}</td>
                            </tr>";
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.languages_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.id}</th>
                            <th class='bluecellthin-th' width='2%' align='left'>{lang.key}</th>
                            <th class='bluecellthin-th' width='82%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='2%' align='center'>{lang.users}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.default}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.properties}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.edit}</th>
                            <th class='bluecellthin-th' width='3%' align='center'>{lang.delete}</th>
                        </tr>
                        ". $language_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'drill', '{lang.menu_tools}', '<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=tools' ),
                            array( 'folder_import', '{lang.menu_import}', '<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=import' ),
                            array( 'folder_export', '{lang.menu_export}', '<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=export' ),
                            array( 'mail_pencil', '{lang.menu_etemplates}', '<! TD_URL !>/admin.php?section=look&amp;page=emails' ),
                            array( 'compile', '{lang.menu_cache}', '<! TD_URL !>/admin.php?section=tools&amp;page=cache&amp;act=dorebuild&amp;id=langs' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_langs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_langs_title}', '{lang.help_about_langs_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Properties
    #=======================================

    private function prop_language()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'edit' );

        #=============================
        # Grab Language
        #=============================

        if ( ! $l = $this->trellis->func->languages->get_single_by_id( array( 'id', 'key', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_language');

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=doprop&amp;id={$l['id']}", 'edit_prop', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_properties} '. $l['name'], 'a' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.key} '. $this->trellis->skin->help_tip('{lang.tip_key}'), $this->trellis->skin->textfield( 'key', $l['key'] ), 'a', '16%', '84%' ) ."
                        ". $this->trellis->skin->group_table_row( '{lang.name}', $this->trellis->skin->textfield( 'name', $l['name'] ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_properties}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'key'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_key}' ) ), array( 'type' => 'format', 'params' => array( 'pattern' => '/^[a-z0-9]*$/', 'fail_msg' => '{lang.lv_valid_key}' ) ) ),
                                 'name'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_name}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=langs' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_langs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Language
    #=======================================

    private function edit_language()
    {
        if ( $this->trellis->input['file'] ) $this->edit_file();

        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'edit' );

        #=============================
        # Grab Files
        #=============================

        $file_rows = "";

        if ( ! $l = $this->trellis->func->languages->get_single_by_id( array( 'id', 'key', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_language');

        if ( ! $files = $this->trellis->func->languages->files( $l['key'] ) ) $this->trellis->skin->error('no_language');

        foreach( $files as $f )
        {
            $name = ( $this->known_files[ $f ] ) ? $this->known_files[ $f ] : $f;

            $file_rows .= "<tr>
                                <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=edit&amp;id={$l['id']}&file=". urlencode( base64_encode( $f ) ) ."'>{$name}</a></td>
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
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=langs' ),
                            array( 'mail_pencil', '{lang.menu_etemplates}', '<! TD_URL !>/admin.php?section=look&amp;page=emails' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_langs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_langs_title}', '{lang.help_about_langs_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Language File
    #=======================================

    private function edit_file()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'edit' );

        #=============================
        # Load File
        #=============================

        $bit_rows = "";

        if ( ! $l = $this->trellis->func->languages->get_single_by_id( array( 'id', 'key', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_language');

        if ( ! $bits = $this->trellis->func->languages->bits( $l['key'], ( $file = base64_decode( $this->trellis->input['file'] ) ) ) ) $this->trellis->skin->error('no_language');

        foreach( $bits as $key => &$value )
        {
            $bit_rows .= $this->trellis->skin->group_table_full_row( $key, 'a' ) ."
                        ". $this->trellis->skin->group_table_full_row( $this->trellis->skin->textarea( array( 'name' => 'lb_'. $key, 'value' => $this->trellis->func->languages->prepare_html( $value ), 'cols' => 60, 'rows' => 1, 'width' => '98%', 'height' => '13px' ) ), 'a' );
        }

        #=============================
        # Do Output
        #=============================

        if ( ! $this->trellis->func->languages->writeable( $l['key'], $file ) ) $this->trellis->send_message( 'error', $this->trellis->lang['error_writeable'].' '. TD_PATH .'languages/'. $l['key'] .'/'. $file );

        $name = ( $this->known_files[ $file ] ) ? $this->known_files[ $file ] : $file;

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=doedit&amp;id={$l['id']}&amp;file=". urlencode( base64_encode( $file ) ), 'edit_file', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_file} '. $name, 'a' ) ."
                        ". $bit_rows ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_file}' ) ) ."
                        </div>
                        <script type='text/javascript'>
                        //<![CDATA[
                        $('textarea').each(function() {
                            if ($(this).height() < $(this).attr('scrollHeight')) {
                                $(this).height($(this).attr('scrollHeight'));
                            }
                        });
                        //]]>
                        </script>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=langs' ),
                            array( 'mail_pencil', '{lang.menu_etemplates}', '<! TD_URL !>/admin.php?section=look&amp;page=emails' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_langs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_langs_title}', '{lang.help_about_langs_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Delete Language Form
    #=======================================

    private function delete_language()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'delete' );

        if ( ! $l = $this->trellis->func->languages->get_single_by_id( array( 'id', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_language');

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=dodel&amp;id={$l['id']}", 'delete_language', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.deleting_language} '. $l['name'] ) ."
                        ". $this->trellis->skin->group_row( "<label for='switchto'>{lang.switch_current}</label> <select name='switchto'>". $this->trellis->func->drop_downs->lang_drop( 0, $l['id'] ) ."</select>" ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_delete_language}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=langs' ),
                            array( 'folder_import', '{lang.menu_import}', '<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=import' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_langs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Language Tools
    #=======================================

    private function tools_language()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'tools' );

        #=============================
        # Do Output
        #=============================

        $this->trellis->load_functions('drop_downs');

        $this->output = "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=dotools", 'language_tools', 'post' ) ."
                        ". $this->trellis->skin->group_title( '{lang.language_tools}' ) ."
                        <div class='option1'><input type='radio' name='action' id='action1' value='1' checked='checked' /> <label for='action1'>{lang.switch_all}</label> <select name='switchall'>". $this->trellis->func->drop_downs->lang_drop() ."</select></div>
                        <div class='option2'><input type='radio' name='action' id='action2' value='2' /> <label for='action2'>{lang.switch_from}</label> <select name='switchfrom'>". $this->trellis->func->drop_downs->lang_drop() ."</select> <label for='action2'>{lang.switch_to}</label> <select name='switchto'>". $this->trellis->func->drop_downs->lang_drop() ."</select></div>
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'delete', '{lang.button_execute}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=langs' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_langs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Import Language
    #=======================================

    private function import_language()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'import' );

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        <form action='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=doimport' method='post' enctype='multipart/form-data'>
                        ". $this->trellis->skin->group_title( '{lang.import_language}' ) ."
                        ". $this->trellis->skin->group_sub( '{lang.import_instructions}' ) ."
                        ". $this->trellis->skin->group_row( "<input type='file' name='lang_file' id='lang_file' size='40' />" ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'import', '{lang.button_import}' ) ) ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=langs' ),
                            array( 'folder_export', '{lang.menu_export}', '<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=export' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_langs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_langs_title}', '{lang.help_about_langs_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Export Language
    #=======================================

    private function export_language()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'export' );

        #=============================
        # Grab Languages
        #=============================

        $language_rows = "";

        $languages = $this->trellis->func->languages->get( array( 'select' => array( 'id', 'key', 'name' ), 'order' => array( 'key' => 'asc' ) ) );

        foreach( $languages as $l )
        {
            $language_rows .= "<tr>
                                <td class='bluecellthin-light' width='2%'><a href='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=doexport&amp;id={$l['id']}'><strong>{$l['id']}</strong></a></td>
                                <td class='bluecellthin-light' width='2%'><a href='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=doexport&amp;id={$l['id']}'><strong>{$l['key']}</strong></a></td>
                                <td class='bluecellthin-dark' width='96%'><a href='<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=doexport&amp;id={$l['id']}'>{$l['name']}</a></td>
                            </tr>";
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.languages_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' align='left' colspan='3'>{lang.export_instructions}</th>
                        </tr>
                        ". $language_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=langs' ),
                            array( 'folder_import', '{lang.menu_import}', '<! TD_URL !>/admin.php?section=look&amp;page=langs&amp;act=import' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=look' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_langs_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_langs_title}', '{lang.help_about_langs_msg}' );

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

        $this->trellis->check_perm( 'look', 'langs', 'edit' );

        $validate = true;
        if ( ! $this->trellis->input['key'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_key'] );
            $validate = false;
        }
        elseif ( ! $this->trellis->func->languages->check_key( $this->trellis->input['key'] ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_valid_key'] );
            $validate = false;
        }
        if ( ! $this->trellis->input['name'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_name'] );
            $validate = false;
        }
        if ( ! $validate )
        {
            $this->trellis->skin->preserve_input = 1;
            $this->prop_language();
        }

        #=============================
        # Update Properties
        #=============================

        $db_array = array(
                          'key'        => $this->trellis->input['key'],
                          'name'    => $this->trellis->input['name'],
                          );

        $this->trellis->func->languages->edit_prop( $db_array, $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'lang_prop', $this->trellis->input['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->langs_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_language_updated'] );

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

        $this->trellis->check_perm( 'look', 'langs', 'edit' );

        #=============================
        # Load File
        #=============================

        $bit_rows = "";

        if ( ! $l = $this->trellis->func->languages->get_single_by_id( array( 'id', 'key', 'name' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_language');

        if ( ! $bits = $this->trellis->func->languages->bits( $l['key'], ( $file = base64_decode( $this->trellis->input['file'] ) ) ) ) $this->trellis->skin->error('no_language');

        if ( ! $this->trellis->func->languages->writeable( $l['key'], $file ) ) $this->edit_file();

        #=============================
        # Update File
        #=============================

        $data = array();

        foreach( $bits as $key => &$value )
        {
            $data[ $key ] = $this->trellis->func->languages->convert_html( $this->trellis->input[ 'lb_'. $key ] );
        }

        $this->trellis->func->languages->edit( $data, $l['key'], $file );

        $this->trellis->log( array( 'msg' => array( 'lang_file', $l['name'], ( ( $this->known_files[ $file ] ) ? $this->known_files[ $file ] : $file ) ), 'type' => 'other' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_language_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'edit', 'id' => $l['id'] ) );
    }

    #=======================================
    # @ Do Delete Language
    #=======================================

    private function do_delete()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'delete' );

        if ( $this->trellis->cache->data['misc']['default_lang'] == $this->trellis->input['id'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_delete_default'] );

            $this->trellis->skin->redirect( array( 'act' => null ) );
        }

        if ( ! $this->trellis->cache->data['langs'][ $this->trellis->input['switchto'] ] ) $this->trellis->skin->error('no_language');

        #=============================
        # DELETE Language
        #=============================

        if ( ! $this->trellis->func->languages->delete( $this->trellis->input['id'], $this->trellis->input['switchto'] ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_delete'] );

            $this->trellis->skin->redirect( array( 'act' => null ) );
        }

        $this->trellis->log( array( 'msg' => array( 'lang_deleted', $this->trellis->cache->data['langs'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other', 'level' => 2 ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->langs_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'error', $this->trellis->lang['error_language_deleted'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Default Language
    #=======================================

    private function do_default()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'edit' );

        #=============================
        # Default Language
        #=============================

        $this->trellis->func->languages->set_default( $this->trellis->input['id'] );

        $this->trellis->log( array( 'msg' => array( 'lang_default', $this->trellis->cache->data['langs'][ $this->trellis->input['id'] ]['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->langs_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_language_default'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Language Tools
    #=======================================

    private function do_tools()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'tools' );

        #=============================
        # Perform Action
        #=============================

        if ( $this->trellis->input['action'] == 1 )
        {
            if ( ! $this->trellis->cache->data['langs'][ $this->trellis->input['switchall'] ] ) $this->trellis->skin->error('no_language');

            $this->trellis->db->construct( array(
                'update'    => 'users',
                'set'        => array( 'lang' => $this->trellis->input['switchall'] ),
            ) );

            $this->trellis->db->execute();
        }
        elseif ( $this->trellis->input['action'] == 1 )
        {
            if ( ! $this->trellis->cache->data['langs'][ $this->trellis->input['switchfrom'] ] ) $this->trellis->skin->error('no_language');
            if ( ! $this->trellis->cache->data['langs'][ $this->trellis->input['switchto'] ] ) $this->trellis->skin->error('no_language');

            $this->trellis->db->construct( array(
                'update'    => 'users',
                'set'        => array( 'lang' => $this->trellis->input['switchto'] ),
                'where'        => array( 'lang', '=', $this->trellis->input['switchfrom'] ),
            ) );

            $this->trellis->db->execute();
        }

        $this->trellis->log( array( 'msg' => array( 'lang_tools' ), 'type' => 'other' ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_tool_action'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Import Language
    #=======================================

    private function do_import()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'import' );

        if ( ! $_FILES['lang_file']['size'] )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_no_file'] );
            $this->import_language();
        }

        #=============================
        # Import Language
        #=============================

        if ( ! $lang = $this->trellis->func->languages->import( $_FILES['lang_file']['tmp_name'] ) )
        {
            $this->trellis->send_message( 'error', $this->trellis->lang['error_import'] );
            $this->import_language();
        }

        $this->trellis->log( array( 'msg' => array( 'lang_imported', $lang['name'] ), 'type' => 'other' ) );

        #=============================
        # Rebuild Cache
        #=============================

        $this->trellis->load_functions('rebuild');

        $this->trellis->func->rebuild->langs_cache();

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_language_imported'] );

        $this->trellis->skin->redirect( array( 'act' => null ) );
    }

    #=======================================
    # @ Do Export Language
    #=======================================

    private function do_export()
    {
        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'look', 'langs', 'export' );

        #=============================
        # Export Language
        #=============================

        if ( ! $this->trellis->func->languages->export( $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_language');

        $this->trellis->shut_down();

        exit();
    }
}

?>