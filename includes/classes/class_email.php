<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_email {

    private $transport;
    private $mailer;
    private $message;
    public $failures;
    private $swift_exception;
    private $config = array();
    private $to_send = array();
    private $to_send_id = 0;

    #=======================================
    # @ Constructor
    #=======================================

    function __construct(&$trellis, $config=array())
    {
        $this->trellis = &$trellis;

        #=============================
        # Update Configuration
        #=============================

        if ( ! empty( $config ) ) $this->update_config( $config );
    }

    #=======================================
    # @ Initialize
    #=======================================

    public function initialize()
    {
        require_once TD_INC .'swift/swift_required.php';

        if ( $this->config['transport'] == 'smtp' )
        {
            $this->transport = Swift_SmtpTransport::newInstance( $this->config['smtp_host'] );

            if ( $this->config['smtp_port'] ) $this->transport->setPort( $this->config['smtp_port'] );

            if ( $this->config['smtp_encryption'] ) $this->transport->setEncryption( $this->config['smtp_encryption'] );

            if ( $this->config['smtp_user'] ) $this->transport->setUsername( $this->config['smtp_user'] );
            if ( $this->config['smtp_pass'] ) $this->transport->setPassword( $this->config['smtp_pass'] );

            if ( $this->config['smtp_timeout'] ) $this->transport->setTimeout( $this->config['smtp_timeout'] );
        }
        elseif ( $this->config['transport'] == 'sendmail' )
        {
            $this->transport = Swift_SendmailTransport::newInstance();

            if ( $this->config['sendmail_command'] ) $this->transport->setCommand( $this->config['sendmail_command'] );
        }
        elseif ( $this->config['transport'] == 'mail' )
        {
            $this->transport = Swift_MailTransport::newInstance();
        }

        $this->mailer = Swift_Mailer::newInstance( $this->transport );
    }

    #=======================================
    # @ Update Config
    #=======================================

    private function update_config($config)
    {
        if ( ! is_array( $config ) ) trigger_error( "Email - Variable passed to update_config() is not an array", E_USER_WARNING );

        foreach ( $config as $key => $value )
        {
            $this->config[ $key ] = $value;
        }
    }

    #=======================================
    # @ Test Transport
    #=======================================

    public function test()
    {
        $this->initialize();

        try
        {
            $this->transport->start();
        }
        catch ( Exception $e )
        {
            $this->swift_exception = $e->getMessage();

            return false;
        }

        if ( ! $this->transport->isStarted() ) return false;

        return true;
    }

    #=======================================
    # @ Get Swift Exception
    #=======================================

    public function get_exception()
    {
        return $this->swift_exception;
    }

    #=======================================
    # @ Create Message
    #=======================================

    private function create_message()
    {
        $this->message = Swift_Message::newInstance();
    }

    #=======================================
    # @ Add To
    #=======================================

    private function add_to($addresses, $name=null)
    {
        $this->message->addTo( $addresses, $name );
    }

    #=======================================
    # @ Add CC
    #=======================================

    private function add_cc($addresses, $name=null)
    {
        $this->message->addCc( $addresses, $name );
    }

    #=======================================
    # @ Add BCC
    #=======================================

    private function add_bcc($addresses, $name=null)
    {
        $this->message->addBcc( $addresses, $name );
    }

    #=======================================
    # @ Set From
    #=======================================

    private function set_from($address, $name=null)
    {
        $this->message->setFrom( $address, $name );
    }

    #=======================================
    # @ Set Subject
    #=======================================

    private function set_subject($subject)
    {
        $this->message->setSubject( $subject );
    }

    #=======================================
    # @ Set Body
    #=======================================

    private function set_body($body, $type=null)
    {
        $this->message->setBody( $body, $type );
    }

    #=======================================
    # @ Add Part
    #=======================================

    private function add_part($part, $type=null)
    {
        $this->message->addPart( $part, $type );
    }

    #=======================================
    # @ Attach
    #=======================================

    private function attach($path, $type=null)
    {
        $attachment = Swift_Attachment::fromPath( $path, $type );

        $this->message->attach( $attachment );
    }

    #=======================================
    # @ Set Replacements
    #=======================================

    private function set_replacements($replacements)
    {
        $decorator = new Swift_Plugins_DecoratorPlugin( $replacements );

        $this->mailer->registerPlugin( $decorator );
    }

    #=======================================
    # @ Send
    #=======================================

    private function send()
    {
        return $this->mailer->send( $this->message, $this->failures ); #* handle failures
    }

    #=======================================
    # @ Send Email
    #=======================================

    public function send_email($params=array())
    {
        if ( ! $this->trellis->cache->data['settings']['email']['enable'] ) return true;
        if ( $params['type_user'] && ( ! $this->trellis->cache->data['settings']['eunotify']['enable'] || ! $this->trellis->cache->data['settings']['eunotify'][ $params['type_user'] ] ) ) return true;
        if ( $params['type_staff'] && ( ! $this->trellis->cache->data['settings']['esnotify']['enable'] || ! $this->trellis->cache->data['settings']['esnotify'][ $params['type_staff'] ] ) ) return true;

        #=============================
        # Add Lookup User
        #=============================

        if ( ! $params['to'] )
        {
            trigger_error( "Email - Recipient not specified", E_USER_NOTICE );

            return false;
        }

        if ( ! $params['msg'] )
        {
            trigger_error( "Email - Message not specified", E_USER_NOTICE );

            return false;
        }

        if ( is_numeric( $params['to'] ) )
        {
            $this->to_send[ ++$this->to_send_id ] = array( 'id' => $params['to'], 'email' => $params['email'], 'name' => $params['name'], 'msg' => $params['msg'], 'from' => $params['from'], 'replace' => $params['replace'], 'lang' => $params['lang'], 'override' => $params['override'], 'format' => $params['format'], 'type' => $params['type'] );
        }
        else
        {
            $this->to_send[ ++$this->to_send_id ] = array( 'email' => $params['to'], 'name' => $params['name'], 'msg' => $params['msg'], 'from' => $params['from'], 'replace' => $params['replace'], 'lang' => $params['lang'], 'override' => $params['override'], 'format' => $params['format'], 'type' => $params['type'] );
        }

        return true;
    }

    #=======================================
    # @ Send Email Now
    #=======================================

    public function send_email_now($params=array())
    {
        $this->send_email( $params );

        return $this->send_emails();
    }

    #=======================================
    # @ Get User
    #=======================================

    public function get_user($id)
    {
        #* probably not the fastest because we are always pulling all email type fields and staff fields
        #* we can probably get rid of staff fields? only one i know of using them is email_staff_assign

        return $this->trellis->db->get_single( array(
                                                     'select'    => array(
                                                                         'u'    => array( 'id', 'name', 'email', 'lang', 'email_enable', 'email_ticket', 'email_action', 'email_news', 'email_type' ),
                                                                         'us'    => array( 'email_staff_enable', 'email_staff_ticket', 'email_staff_reply', 'email_staff_assign', 'email_staff_escalate', 'email_staff_hold', 'email_staff_move_to', 'email_staff_move_away', 'email_staff_close', 'email_staff_reopen' ),
                                                                         ),
                                                     'from'        => array( 'u' => 'users' ),
                                                     'join'        => array( array( 'from' => array( 'us' => 'users_staff' ), 'where' => array( 'u' => 'id', '=', 'us' => 'uid' ) ) ),
                                                     'where'    => array( array( 'u' => 'id' ), '=', $id ),
                                              ), 'id' );
    }

    #=======================================
    # @ Get Users
    #=======================================

    public function get_users($ids)
    {
        #* probably not the fastest because we are always pulling all email type fields and staff fields
        #* we can probably get rid of staff fields? only one i know of using them is email_staff_assign

        return $this->trellis->db->get( array(
                                              'select'    => array(
                                                                'u'        => array( 'id', 'name', 'email', 'lang', 'email_enable', 'email_ticket', 'email_action', 'email_news', 'email_type' ),
                                                                'us'    => array( 'email_staff_enable', 'email_staff_ticket', 'email_staff_reply', 'email_staff_assign', 'email_staff_escalate', 'email_staff_hold', 'email_staff_move_to', 'email_staff_move_away', 'email_staff_close', 'email_staff_reopen' ),
                                                                ),
                                              'from'    => array( 'u' => 'users' ),
                                              'join'    => array( array( 'from' => array( 'us' => 'users_staff' ), 'where' => array( 'u' => 'id', '=', 'us' => 'uid' ) ) ),
                                              'where'    => array( array( 'u' => 'id' ), 'in', $ids ),
                                       ), 'id' );
    }

    #=======================================
    # @ Notify Staff
    #=======================================

    public function notify_staff($params=array())
    {
        if ( ! $this->trellis->cache->data['settings']['email']['enable'] ) return true;
        if ( ! $this->trellis->cache->data['settings']['esnotify']['enable'] ) return true;

        if ( $params['type'] && ! $this->trellis->cache->data['settings']['esnotify'][ $params['type'] ] ) return true;

        $staff = $this->trellis->db->get( array(
                                                'select'    => array(
                                                                     'u'    => array( 'id', 'name', 'email', 'ugroup_sub', 'ugroup_sub_acp', 'lang', 'email_type' ),
                                                                     'us'    => array( 'email_staff_enable', 'email_staff_'. $params['type'], 'esn_unassigned', 'esn_assigned', 'esn_assigned_to_me' ),
                                                                     'g'    => array( 'g_acp_access', 'g_acp_depart_perm' ),
                                                                     ),
                                                'from'    => array( 'u' => 'users' ),
                                                'join'    => array( array( 'from' => array( 'us' => 'users_staff' ), 'where' => array( 'u' => 'id', '=', 'us' => 'uid' ) ), array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'u' => 'ugroup', '=', 'g' => 'g_id' ) ) ),
                                                'where'    => array( array( array( 'g' => 'g_acp_access' ), '=', 1 ), array( array( 'u' => 'ugroup_sub_acp' ), '=', 1, 'or' ) ),
                                         ), 'id' );

        if ( ! $staff ) return false;

        if ( ! $params['assigned'] )
        {
            $this->trellis->load_functions('tickets');

            $params['assigned'] = $this->trellis->func->tickets->get_assignments_by_uid( $params['tid'] ); # TODO: i'm pretty sure this can be combined into the above query were we select the staff. left join on the assign_map table. look for a matching tid with uid.
        }

        $sent = array();

        foreach ( $staff as $s )
        {
            if ( $params['exclude'] )
            {
                if ( is_array( $params['exclude'] ) && $params['exclude'][ $s['id'] ] )
                {
                    continue;
                }
                else
                {
                    if ( $params['exclude'] == $s['id'] ) continue;
                }
            }

            # CHECK: sub-group logic

            // Sub-Groups
            $s['ugroup_sub'] = unserialize( $s['ugroup_sub'] );

            if ( ! $s['g_acp_access'] && $s['ugroup_sub_acp'] && is_array( $s['ugroup_sub'] ) && ! empty( $s['ugroup_sub'] ) )
            {
                foreach ( $s['ugroup_sub'] as $g )
                {
                    if ( $this->trellis->cache->data['groups'][ $g ]['g_acp_access'] ) $s['g_acp_access'] = 1;

                    break;
                }
            }

            if ( ! $s['g_acp_access'] ) continue;

            $perms = unserialize( $s['g_acp_depart_perm'] );

            // Sub-Groups Permissions
            if ( is_array( $s['ugroup_sub'] ) && ! empty( $s['ugroup_sub'] ) )
            {
                foreach ( $s['ugroup_sub'] as $gid )
                {
                    $g = $this->trellis->cache->data['groups'][ $gid ];

                    $g['g_acp_depart_perm'] = unserialize( $g['g_acp_depart_perm'] );

                    if ( is_array( $g['g_acp_depart_perm'] ) && ! empty( $g['g_acp_depart_perm'] ) )
                    {
                        foreach ( $g['g_acp_depart_perm'] as $d => $types )
                        {
                            if ( ! $perms[ $d ]['v'] && $g['g_acp_depart_perm'][ $d ]['v'] ) $perms[ $d ]['v'] = 1;
                        }
                    }
                }
            }

            if ( ! $perms[ $params['did'] ]['v'] && ! $params['assigned'][ $s['id'] ] ) continue;

            if ( ! $params['assigned'] )
            {
                if ( ! $s['esn_unassigned'] ) continue;
            }
            else
            {
                if ( $params['assigned'][ $s['id'] ] )
                {
                    if ( ! $s['esn_assigned_to_me'] ) continue;
                }
                else
                {
                    if ( ! $s['esn_assigned'] ) continue;
                }
            }

            if ( ! $params['override'] )
            {
                if ( ! $s['email_staff_enable'] ) continue;

                if ( ! $s[ 'email_staff_'. $params['type'] ] ) continue;
            }

            $sent[ $s['id'] ] = 1;

            $this->send_email( array( 'to' => $s['id'], 'name' => $s['name'], 'email' => $s['email'], 'msg' => $params['msg'] .'_staff', 'replace' => $params['replace'], 'lang' => $s['lang'], 'override' => 1, 'format' => $s['email_type'] ) );
        }

        return $sent;
    }

    #=======================================
    # @ Send Emails
    #=======================================

    public function send_emails()
    {
        if ( ! is_array( $this->to_send ) ) return false;

        $emails = array();
        $languages = array();

        #=============================
        # Lookup Users
        #=============================

        $lookup_ids = array();

        foreach( $this->to_send as $id => $data )
        {
            // If we do send an email address for a registered user, then be sure to send name, lang, format, *time_zone, and *dst over
            if ( ! $data['email'] && ! in_array( $data['id'], $lookup_ids ) ) $lookup_ids[] = $data['id'];
        }

        if ( ! empty( $lookup_ids ) )
        {
            $users = array();

            if ( count( $lookup_ids ) == 1 )
            {
                if ( ! $u = $this->get_user( $lookup_ids[0] ) ) trigger_error( "Email - User not found: ". current( $lookup_ids ), E_USER_NOTICE );

                $users[ $u['id'] ] = $u;
            }
            else
            {
                if ( ! $users = $this->get_users( $lookup_ids ) ) trigger_error( "Email - No users found.", E_USER_NOTICE );
            }
        }

        #=============================
        # Organize Emails
        #=============================

        foreach( $this->to_send as $id => $data )
        {
            if ( ! $data['lang'] && $data['id'] ) $data['lang'] = $users[ $data['id'] ]['lang'];
            if ( ! $data['format'] && $data['id'] ) $this->to_send[ $id ]['format'] = $users[ $data['id'] ]['email_type'];

            if ( ! $data['lang'] || ! $this->trellis->cache->data['langs'][ $data['lang'] ] ) $data['lang'] = ( ( $this->trellis->cache->data['misc']['default_lang'] ) ? $this->trellis->cache->data['misc']['default_lang'] : $this->trellis->config['fallback_lang'] );

            $data['lang'] = $this->trellis->cache->data['langs'][ $data['lang'] ]['key']; // Convert to language key

            if ( $this->to_send[ $id ]['format'] == 1 )
            {
                $this->to_send[ $id ]['format'] = 'html';
            }
            elseif ( $this->to_send[ $id ]['format'] == 2 )
            {
                $this->to_send[ $id ]['format'] = 'text';
            }

            if ( ! $this->to_send[ $id ]['format'] ) $this->to_send[ $id ]['format'] = 'html'; // Default to HTML

            if ( ! $data['name'] && $data['id'] ) $this->to_send[ $id ]['name'] = $users[ $data['id'] ]['name'];
            if ( ! $data['email'] && $data['id'] ) $this->to_send[ $id ]['email'] = $users[ $data['id'] ]['email'];

            if ( ! $this->to_send[ $id ]['name'] ) $this->to_send[ $id ]['name'] = $this->to_send[ $id ]['email'];

            // Check Preferences / Permissions
            if ( ! $data['override'] )
            {
                $type_staff = strpos( $data['type'], 'staff_' );

                if ( $data['id'] && $this->to_send[ $id ]['preference'] )
                {
                    ( $type_staff === 0 ) ? $this->to_send[ $id ]['preference'] = $users[ $data['id'] ]['email_staff_enable'] : $this->to_send[ $id ]['preference'] = $users[ $data['id'] ]['email_enable'];
                }

                if ( $data['type'] && $this->to_send[ $id ]['preference'] ) $this->to_send[ $id ]['preference'] = $users[ $data['id'] ][ 'email_'. $data['type'] ];

                if ( ! $data['id'] && $this->trellis->cache->data['settings']['eunotify']['guest'] ) $this->to_send[ $id ]['preference'] = 1; // Set Guest Preference
            }

            if ( $this->to_send[ $id ]['preference'] || $data['override'] ) $languages[ $data['lang'] ][] = $id;
        }

        #=============================
        # Process Emails
        #=============================

        foreach( $languages as $lkey => &$emails )
        {
            if ( ! ( include TD_PATH .'languages/'. $lkey .'/lang_email_content.php' ) ) trigger_error( "Email - Unable to load email content language file: ". $lkey, E_USER_WARNING );

            foreach( $emails as &$id )
            {
                if ( ! $this->mailer ) $this->initialize();

                $msg = &$this->to_send[ $id ]['msg'];

                if ( ! $subject = $lang[ $msg .'_sub' ] )
                {
                    trigger_error( "Email - Language subject missing: ". $msg, E_USER_NOTICE );

                    continue;
                }

                if ( ! $lang[ $msg ] )
                {
                    trigger_error( "Email - Language message missing: ". $msg, E_USER_NOTICE );

                    continue;
                }

                if ( $this->to_send[ $id ]['format'] == 'html' && ! $lang[ $msg .'_html' ] )
                {
                    trigger_error( "Email - Language message missing: ". $msg .'_html', E_USER_NOTICE );

                    continue;
                }

                $message_text = $this->trellis->prepare_email( $lang['header'] ."\n\n". $lang[ $msg ] ."\n\n". $lang['footer'], 0, 'plain' );

                $message_html = $this->trellis->prepare_email( $lang['header_html'] . $lang[ $msg .'_html' ] . $lang['footer_html'], 1, 'html' );

                $this->create_message( $id );

                $this->set_subject( $subject ); #* use htmlentitydecode

                ( $this->to_send[ $id ]['from'] ) ? $from = $this->to_send[ $id ]['from'] : $from = $this->trellis->cache->data['settings']['email']['out_address'];

                $this->set_from( $from, $this->trellis->cache->data['settings']['general']['hd_name'] );

                $this->add_to( $this->to_send[ $id ]['email'], $this->to_send[ $id ]['name'] );

                if ( $this->trellis->cache->data['settings']['email']['html'] && $this->to_send[ $id ]['format'] == 'html' )
                {
                    $this->set_body( $message_html, 'text/html' );

                    $this->add_part( $message_text, 'text/plain' );
                }
                else
                {
                    $this->set_body( $message_text, 'text/plain' );
                }

                $replace = array(
                                 '{TD_NAME}'    => $this->trellis->cache->data['settings']['general']['hd_name'],
                                 '{TD_URL}'        => $this->trellis->config['hd_url'],
                                 '{USER_NAME}'    => $this->to_send[ $id ]['name'],
                                 '{USER_ID}'    => $this->to_send[ $id ]['id'],
                                 );

                if ( is_array( $this->to_send[ $id ]['replace'] ) ) $replace = array_merge( $replace, $this->to_send[ $id ]['replace'] );

                $replace['<p><p>'] = '<p>';
                $replace['</p></p>'] = '</p>'; # CHECK: I don't like to do this :(

                $this->set_replacements( array( $this->to_send[ $id ]['email'] => $replace ) );

                $this->send();
            }
        }

        $this->to_send = array(); // Clear emails in queue

        return true;
    }

}
?>