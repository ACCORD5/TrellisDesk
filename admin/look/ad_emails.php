<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_emails {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->check_perm( 'manage', 'emails' );

        $this->trellis->load_lang('emails');

        $this->trellis->skin->set_active_link( 3 );

        switch( $this->trellis->input['act'] )
        {
            case 'list':
                $this->list_emails();
            break;
            case 'edit':
                $this->edit_email();
            break;

            case 'doedit':
                $this->do_edit();
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
        $this->trellis->load_functions('languages');

        #=============================
        # Grab Languages
        #=============================

        $language_rows = "";

        $languages = $this->trellis->func->languages->get( array( 'select' => array( 'id', 'key', 'name' ), 'order' => array( 'key' => 'asc' ) ) );

        foreach( $languages as $l )
        {
            $language_rows .= "<tr>
                                <td class='bluecellthin-light' width='2%'><a href='<! TD_URL !>/admin.php?section=look&amp;page=emails&amp;act=list&amp;id={$l['id']}'><strong>{$l['key']}</strong></a></td>
                                <td class='bluecellthin-dark' width='95%'><a href='<! TD_URL !>/admin.php?section=look&amp;page=emails&amp;act=list&amp;id={$l['id']}'>{$l['name']}</a></td>
                                <td class='bluecellthin-light' width='3%' align='center'><a href='<! TD_URL !>/admin.php?section=look&amp;page=emails&amp;act=list&amp;lang={$l['id']}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                            </tr>";
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.languages_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' align='left' colspan='3'>{lang.select_lang_instructions}</th>
                        </tr>
                        ". $language_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'mails_arrow', '{lang.menu_mass_email}', '<! TD_URL !>/admin.php?section=tools&amp;page=emails&amp;act=mass' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=email' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_email_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_emails_title}', '{lang.help_about_emails_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ List Email Templates
    #=======================================

    private function list_emails()
    {
        $this->trellis->load_functions('emails');

        #=============================
        # Grab Email Templates
        #=============================

        $email_rows = "";

        if ( ! $emails =& $this->trellis->func->emails->get( $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_email');

        foreach( $emails as $key => $content )
        {
            if ( substr( $key, -5, 5 ) != '_html' && substr( $key, -4, 4 ) != '_sub' )
            {
                $email_rows .= "<tr>
                                <td class='bluecellthin-light'><a href='<! TD_URL !>/admin.php?section=look&amp;page=emails&amp;act=edit&amp;id={$this->trellis->input['id']}&amp;key={$key}'><strong>{lang.email_{$key}}</strong></a></td>
                                <td class='bluecellthin-dark'><a href='<! TD_URL !>/admin.php?section=look&amp;page=emails&amp;act=edit&amp;id={$this->trellis->input['id']}&amp;key={$key}'>{lang.email_{$key}_desc}</a></td>
                                <td class='bluecellthin-light' align='center'><a href='<! TD_URL !>/admin.php?section=look&amp;page=emails&amp;act=edit&amp;id={$this->trellis->input['id']}&amp;key={$key}'><img src='<! IMG_DIR !>/button_edit.gif' alt='{lang.edit}' /></a></td>
                            </tr>";
            }
        }

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_group_table( '{lang.emails_list}' ) ."
                        <tr>
                            <th class='bluecellthin-th' width='28%' align='left'>{lang.name}</th>
                            <th class='bluecellthin-th' width='57%' align='left'>{lang.description}</th>
                            <th class='bluecellthin-th' width='5%' align='center'>{lang.edit}</th>
                        </tr>
                        ". $email_rows ."
                        ". $this->trellis->skin->end_group_table() ."
                        </div>";

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=emails' ),
                            array( 'mails_arrow', '{lang.menu_mass_email}', '<! TD_URL !>/admin.php?section=tools&amp;page=emails&amp;act=mass' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=email' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_email_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.help_about_emails_title}', '{lang.help_about_emails_msg}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Edit Email Template
    #=======================================

    private function edit_email($error='')
    {
        $this->trellis->load_functions('emails');

        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'emails', 'edit' );

        #=============================
        # Grab Email Template
        #=============================

        if ( ! $e = $this->trellis->func->emails->get_single( $this->trellis->input['id'], $this->trellis->input['key'] ) ) $this->trellis->skin->error('no_email');

        #=============================
        # Do Output
        #=============================

        if ( ! is_writable( TD_PATH .'languages/'. $this->trellis->cache->data['langs'][ $this->trellis->input['id'] ]['key'] .'/lang_email_content.php' ) ) $error = 'not_writable';

        if ( $error )
        {
            $this->output .= $this->trellis->skin->error_wrap( '{lang.error_'. $error .'}' );
            $this->trellis->skin->preserve_input = 1;
        }

        $this->output .= $this->trellis->skin->tinymce_js( 'content_html' );

        $this->output .= "<div id='ticketroll'>
                        ". $this->trellis->skin->start_form( "<! TD_URL !>/admin.php?section=look&amp;page=emails&amp;act=doedit&amp;id={$this->trellis->input['id']}&amp;key={$this->trellis->input['key']}", 'edit_email', 'post' ) ."
                        ". $this->trellis->skin->start_group_table( '{lang.editing_email} {lang.email_'. $this->trellis->input['key'] .'}', 'a' );

        $this->output .= $this->trellis->skin->group_table_full_row( '{lang.email_'. $this->trellis->input['key'] .'_desc}', 'a' );



        if ( $this->trellis->input['key'] != 'header' && $this->trellis->input['key'] != 'footer' ) $this->output .= $this->trellis->skin->group_table_row( '{lang.subject}', $this->trellis->skin->textfield( 'subject', $e['subject'] ), 'a', '16%', '84%' );

        $this->output .= $this->trellis->skin->group_table_sub( '{lang.html_content}' ) ."
                        ". $this->trellis->skin->group_table_full_row( $this->trellis->skin->textarea( array( 'name' => 'content_html', 'value' => $e['html'], 'cols' => 80, 'rows' => 10, 'width' => '98%', 'height' => '280px' ) ), 'a' ) ."
                        ". $this->trellis->skin->group_table_sub( '{lang.text_content}' ) ."
                        ". $this->trellis->skin->group_table_full_row( $this->trellis->skin->textarea( array( 'name' => 'content_text', 'value' => $e['plaintext'], 'cols' => 80, 'rows' => 10, 'width' => '98%', 'height' => '250px' ) ), 'a' ) ."
                        ". $this->trellis->skin->end_group_table( 'a' ) ."
                        ". $this->trellis->skin->end_form( $this->trellis->skin->submit_button( 'edit', '{lang.button_edit_email}' ) ) ."
                        </div>";

        $validate_fields = array(
                                 'subject'    => array( array( 'type' => 'presence', 'params' => array( 'fail_msg' => '{lang.lv_no_subject}' ) ) ),
                                 );

        $this->output .= $this->trellis->skin->live_validation_js( $validate_fields );

        $menu_items = array(
                            array( 'arrow_back', '{lang.menu_back}', '<! TD_URL !>/admin.php?section=look&amp;page=emails' ),
                            array( 'tag', '{lang.menu_template_tags}', '#' ), # TODO: update template tags link
                            array( 'mails_arrow', '{lang.menu_mass_email}', '<! TD_URL !>/admin.php?section=tools&amp;page=emails&amp;act=mass' ),
                            array( 'settings', '{lang.menu_settings}', '<! TD_URL !>/admin.php?section=tools&amp;page=settings&amp;act=edit&amp;group=email' ),
                            );

        $this->trellis->skin->add_sidebar_menu( '{lang.menu_email_options}', $menu_items );
        $this->trellis->skin->add_sidebar_help( '{lang.random_title}', '{lang.random_text}' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

    #=======================================
    # @ Do Edit Email Template
    #=======================================

    private function do_edit()
    {
        $this->trellis->load_functions('emails');

        #=============================
        # Security Checks
        #=============================

        $this->trellis->check_perm( 'manage', 'emails', 'edit' );

        if ( ! $this->trellis->input['subject'] && $this->trellis->input['key'] != 'header' && $this->trellis->input['key'] != 'footer' ) $this->edit_email('no_subject');
        if ( ! $this->trellis->input['content_html'] ) $this->edit_email('no_content_html');
        if ( ! $this->trellis->input['content_text'] ) $this->edit_email('no_content_text');

        #=============================
        # Update Reply Template
        #=============================

        if ( ! $this->trellis->func->emails->edit( $this->trellis->input['id'], $this->trellis->input['key'], $this->trellis->input['subject'], $this->trellis->input['content_html'], $this->trellis->input['content_text'] ) ) $this->edit_email('not_writable');

        $this->trellis->log( 'other', array( 'email_updated', $this->trellis->cache->data['langs'][ $this->trellis->input['id'] ]['name'] ), 1, array( 'link' => 'email', 'id' => $this->trellis->input['id'] ) );

        #=============================
        # Redirect
        #=============================

        $this->trellis->send_message( 'alert', $this->trellis->lang['alert_email_updated'] );

        $this->trellis->skin->redirect( array( 'act' => 'list', 'id' => $this->trellis->input['id'] ) );
    }

}

?>