<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

#=============================
# Safe and Secure
#=============================

ini_set( 'register_globals', 0 );

if ( function_exists('date_default_timezone_get') )
{
     date_default_timezone_set( date_default_timezone_get() );
}

if ( @ini_get( 'register_globals' ) )
{
    while ( list( $key, $value ) = each( $_REQUEST ) )
    {
        unset( $$key );
    }
}

#=============================
# Itsy Bitsy Stuff
#=============================

define( 'IN_HD' , 1 );

ini_set( 'display_errors', 0 );

#=============================
# Define Our Paths
#=============================

define( "TD_PATH", "../" );
define( 'TD_INC', TD_PATH ."includes/" );
define( 'TD_SRC', TD_PATH ."sources/" );
define( 'TD_SKIN', TD_PATH ."skin/" );
define( 'TD_CLASS', TD_PATH ."includes/classes/class_" );
define( 'TD_FUNC', TD_PATH ."includes/functions/func_" );

define( 'TD_DEBUG', false );

#=============================
# Main Class
#=============================

require_once TD_INC . "trellis.php";
$trellis = new trellis(1);

$trellis->load_lang('global');
$trellis->load_lang('tickets');

#=============================
# Pre-Checks
#=============================

if ( ! $trellis->cache->data['settings']['tickets']['new_tickets'] ) exit();

#=============================
# Grab Incoming Email
#=============================

foreach( $trellis->cache->data['depart'] as $d )
{
    if ( $d['email_pop3'] )
    {
        if ( $mbox = imap_open( "{". $d['pop3_host'] .":110/pop3}INBOX", $d['pop3_user'], $d['pop3_pass'] ) )
        {
            $MC = imap_check($mbox);

            $result = imap_fetch_overview( $mbox, "1:{$MC->Nmsgs}", 0 );

            foreach ( $result as $msg )
            {
                $email = array(); // Initialize for Security

                $raw_email = imap_fetchbody( $mbox, $msg->msgno, NULL );

                imap_delete( $mbox, $msg->msgno );

                #=============================
                # Now the Fun Begins :D
                #=============================

                $lines = explode( "\n", $raw_email );

                $headers_complete = 0;
                $boundary_active = 0;
                $boundary_count = 0;

                while ( list( , $line ) = each( $lines ) )
                {
                    if ( ! $headers_complete )
                    {
                        // From
                        if ( preg_match( "/^From:\s*(.*)$/", $line, $matches ) )
                        {
                            $email['from'] = $matches[1];

                            if ( strpos( $email['from'], '<' ) !== false )
                            {
                                if ( preg_match( "/(.*?)<(.*)>/", $email['from'], $matches ) )
                                {
                                    $email['nickname'] = $matches[1];
                                    $email['from'] = $matches[2];

                                    if ( preg_match( "/\"([^\"]*)\"/", $email['nickname'], $matches ) )
                                    {
                                        $email['nickname'] = $matches[1];
                                    }
                                }
                            }
                        }

                        // To
                        if ( preg_match( "/^To:\s*(.*)$/", $line, $matches ) )
                        {
                            $email['to'] = $matches[1];

                            if ( strpos( $email['to'], '<' ) !== false )
                            {
                                if ( preg_match( "/<(.*)>/", $line, $matches ) )
                                {
                                    $email['to'] = $matches[1];
                                }
                            }
                        }

                        // Subject
                        if ( preg_match( "/^Subject:\s*(.*)$/", $line, $matches ) )
                        {
                            $email['subject'] = $matches[1];
                        }

                        // Date
                        if ( preg_match( "/^Date:\s*(.*)$/", $line, $matches ) )
                        {
                            $email['date'] = $matches[1];
                        }

                        // Content Type
                        if ( preg_match( "/^Content-Type:\s*(.*)$/", $line, $matches ) )
                        {
                            $email['content_type_raw'] = $matches[1];

                            if ( preg_match( "/^multipart\/alternative;/", $email['content_type_raw'], $matches ) )
                            {
                                $email['mixed_content'] = 1;
                            }
                            elseif ( preg_match( "/^text\/plain;/", $email['content_type_raw'], $matches ) )
                            {
                                $email['content_type'] = 'text/plain';
                            }
                            elseif ( preg_match( "/^text\/html;/", $email['content_type_raw'], $matches ) )
                            {
                                $email['content_type'] = 'text/html';
                            }
                        }

                        // Boundary
                        if ( $email['mixed_content'] )
                        {
                            if ( preg_match( "/(\s*)?boundary=\"(.*)\"/", $line, $matches ) )
                            {
                                $email['boundary'] = $matches[2];
                            }
                        }

                        // Check For Header End
                        if ( ! trim( $line ) )
                        {
                            $headers_complete = 1;
                        }
                    }
                    else
                    {
                        // Multipart
                        if ( $email['mixed_content'] )
                        {
                            $boundary_first = 0;

                            if ( trim( $line ) == '--' .$email['boundary'] )
                            {
                                if ( $boundary_active )
                                {
                                    $boundary_first = 1;
                                    $boundary_count ++;
                                }
                                else
                                {
                                    $boundary_active = 1;
                                    $boundary_first = 1;
                                    $boundary_count ++;
                                }
                            }

                            if ( $boundary_active && ! $boundary_first && $line != '--' .$email['boundary'] .'--' )
                            {
                                $email['parts'][ $boundary_count ]['raw_message'] .= $line ."\n";
                            }
                        }
                        else
                        {
                            $email['message'] .= $line ."\n";
                        }
                    }
                }

                // Clean Up
                if ( $email['mixed_content'] )
                {
                    $plain_text_found = 0;

                    while( list( $pid, ) = each( $email['parts'] ) )
                    {
                        $lines = explode( "\n", $email['parts'][ $pid ]['raw_message'] );

                        $part_headers_complete = 0;

                        while( list( , $line ) = each( $lines ) )
                        {
                            if ( ! $part_headers_complete )
                            {
                                // Content Type
                                if ( preg_match( "/^Content-Type:\s*(.*)$/", $line, $matches ) )
                                {
                                    $email['parts'][ $pid ]['content_type_raw'] = $matches[1];

                                    if ( preg_match( "/^text\/plain;/", $email['parts'][ $pid ]['content_type_raw'], $matches ) )
                                    {
                                        $email['parts'][ $pid ]['content_type'] = 'text/plain';

                                        $plain_text_found = 1;
                                    }
                                    elseif ( preg_match( "/^text\/html;/", $email['parts'][ $pid ]['content_type_raw'], $matches ) )
                                    {
                                        $email['parts'][ $pid ]['content_type'] = 'text/html';
                                    }
                                }

                                // Check For Header End
                                if ( ! trim( $line ) )
                                {
                                    $part_headers_complete = 1;
                                }
                            }
                            else
                            {
                                $email['parts'][ $pid ]['message'] .= $line ."\n";
                            }
                        }

                        $email['parts'][ $pid ]['message'] = trim( $email['parts'][ $pid ]['message'] );

                        if ( $plain_text_found )
                        {
                            if ( $email['parts'][ $pid ]['content_type'] == 'text/plain' )
                            {
                                $email['content_type'] = 'text/plain';
                                $email['message'] = $email['parts'][ $pid ]['message'];
                            }
                        }
                        else
                        {
                            if ( ! $email['message'] )
                            {
                                if ( $email['parts'][ $pid ]['content_type'] == 'text/html' )
                                {
                                    $email['content_type'] = 'text/html';
                                    $email['message'] = str_replace( "\n", "", $email['parts'][ $pid ]['message'] );
                                    $email['message'] = preg_replace( '/<br(.*?)>/i', "\n", $email['message'] );
                                    $email['message'] = trim( strip_tags( $email['message'] ) );
                                }
                            }
                        }
                    }
                }
                else
                {
                    $email['message'] = trim( $email['message'] );

                    if ( $email['content_type'] == 'text/html' )
                    {
                        $email['message'] = str_replace( "\n", "", $email['message'] );
                        $email['message'] = preg_replace( '/<br(.*?)>/i', "\n", $email['message'] );
                        $email['message'] = trim( strip_tags( $email['message'] ) );
                    }
                }

                // Finally, Sanitize
                $email['from'] = $trellis->sanitize_data( imap_utf8( $email['from'] ) );
                $email['nickname'] = $trellis->sanitize_data( imap_utf8( $email['nickname'] ) );
                $email['to'] = $trellis->sanitize_data( imap_utf8( $email['to'] ) );
                $email['subject'] = $trellis->sanitize_data( imap_utf8( $email['subject'] ) );
                $email['message'] = $trellis->sanitize_data( utf8_encode( decode_ISO88591( imap_utf8( $email['message'] ) ) ) );
                $email['date'] = $trellis->sanitize_data( $email['date'] );

                $email['date'] = strtotime( $email['date'] );

                if ( ! $email['nickname'] ) $email['nickname'] = $email['from'];

                if ( ! $email['from'] || ! $email['to'] || ! $email['subject'] || ! $email['message'] ) continue;

                if ( ! $trellis->validate_email( $email['from'] ) ) continue;

                #=============================
                # Find User
                #=============================

                $trellis->core->db->construct( array(
                                                     'select'    => array( 'm' => array( 'id', 'name' ), 'g' => array( 'g_m_depart_perm' ) ),
                                                   'from'    => array( 'm' => 'users' ),
                                                   'join'    => array( array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'm' => 'ugroup' ) ) ),
                                                      'where'    => array( array( 'm' => 'email' ), '=', $email['from'] ),
                                                      'limit'    => array( 0, 1 ),
                                             )       );

                $trellis->core->db->execute();

                if ( $trellis->core->db->get_num_rows() )
                {
                    $m = $trellis->core->db->fetch_row();
                }
                else
                {
                    if ( ! $d['guest_pipe'] )
                    {
                        $replace = array(); // Initialize for Security

                        if ( $m['id'] )
                        {
                            $trellis->send_email( $m['id'], 'ticket_pipe_rejected', $replace, array( 'from_email' => $d['incoming_email'] ) );
                        }
                        else
                        {
                            $replace['MEM_NAME'] = $email['nickname'];

                            $trellis->send_guest_email( $email['from'], 'ticket_pipe_rejected', $replace, array( 'from_email' => $d['incoming_email'] ) );
                        }

                        $trellis->log( 'security', "Guest Piping Not Allowed: ". $d['name'] );

                        continue;
                    }

                    $trellis->core->db->construct( array(
                                                         'select'    => array( 'g_m_depart_perm' ),
                                                       'from'    => 'groups',
                                                          'where'    => array( 'g_id', '=', 2 ),
                                                          'limit'    => array( 0, 1 ),
                                                 )       );

                    $trellis->core->db->execute();

                    $m = $trellis->core->db->fetch_row();
                }

                #=============================
                # Detect Type
                #=============================

                $ticket_found = 0;

                if ( preg_match_all( '/Ticket ID #([0-9]+)/i', $email['subject'], $matches, PREG_PATTERN_ORDER ) )
                {
                    while( list( , $ptid ) = each( $matches[1] ) )
                    {
                        $trellis->core->db->construct( array(
                                                             'select'    => 'all',
                                                           'from'    => 'tickets',
                                                              'where'    => array( array( 'id', '=', intval( $ptid ) ), array( 'email', '=', $email['from'], 'and' ) ),
                                                              'limit'    => array( 0, 1 ),
                                                     )       );

                        $trellis->core->db->execute();

                        if ( $trellis->core->db->get_num_rows() )
                        {
                            $ticket_found = 1;

                            $t = $trellis->core->db->fetch_row();

                            break;
                        }
                    }
                }

                if ( $ticket_found )
                {
                    if ( $t['status'] == 6 )
                    {
                        $trellis->log( 'error', "Reply Rejected Ticket Closed &#039;". $t['subject'] ."&#039;", 1, $t['id'] );

                        continue;
                    }

                    #=============================
                    # Add Reply
                    #=============================

                    $db_array = array(
                                      'tid'            => $t['id'],
                                      'uid'            => $m['id'],
                                      'uname'        => $m['name'],
                                      'message'        => $email['message'],
                                      'date'        => $email['date'],
                                      'ipadd'        => $trellis->input['ip_address'],
                                     );

                    if ( ! $m['id'] )
                    {
                        $db_array['uname'] = $email['nickname'];
                        $db_array['guest'] = 1;
                    }

                    $trellis->core->db->construct( array(
                                                           'insert'    => 'replies',
                                                           'set'        => $db_array,
                                                     )     );

                    $trellis->core->db->execute();

                    $reply_id = $trellis->core->db->get_insert_id();

                    $trellis->log( 'user', "Ticket Reply &#039;". $t['subject'] ."&#039;", 1, $reply_id );
                    $trellis->log( 'ticket', "Ticket Reply &#039;". $t['subject'] ."&#039;", 1, $t['id'] );

                    #=============================
                    # Email Staff
                    #=============================

                    $trellis->core->db->construct( array(
                                                           'select'    => array( 'm' => array( 'id', 'ugroup', 'email_staff_ticket_reply' ),
                                                                                 'g' => array( 'g_depart_perm' ),
                                                                                ),
                                                           'from'        => array( 'm' => 'users' ),
                                                           'join'        => array( array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'm' => 'ugroup' ) ) ),
                                                            'where'    => array( array( 'g' => 'g_acp_access' ), '=', 1 ),
                                                     )     );

                    $trellis->core->db->execute();

                    if ( $trellis->core->db->get_num_rows($staff_sql) )
                    {
                        while( $sm = $trellis->core->db->fetch_row($staff_sql) )
                        {
                            // Check Departments
                            if ( is_array( unserialize( $sm['g_depart_perm'] ) ) )
                            {
                                $my_departs = "";
                                $my_departs = unserialize( $sm['g_depart_perm'] );

                                if ( $my_departs[ $d['id'] ] )
                                {
                                    if ( $sm['email_staff_ticket_reply'] )
                                    {
                                        $s_email_staff = 1;
                                    }

                                    $do_feeds[ $sm['id'] ] = 1;
                                }
                            }
                            else
                            {
                                if ( $sm['email_staff_ticket_reply'] )
                                {
                                    $s_email_staff = 1;
                                }

                                $do_feeds[ $sm['id'] ] = 1;
                            }

                            if ( $s_email_staff )
                            {
                                $replace = array(); // Initialize for Security

                                $replace['TICKET_ID'] = $t['id'];
                                $replace['SUBJECT'] = $t['subject'];
                                $replace['DEPARTMENT'] = $t['dname'];
                                $replace['PRIORITY'] = $trellis->get_priority( $t['priority'] );
                                $replace['SUB_DATE'] = $trellis->a5_date( $t['date'] );
                                $replace['REPLY'] = $email['message'];
                                $replace['TICKET_LINK'] = $trellis->cache->data['config']['hd_url'] ."/admin.php?section=manage&act=tickets&code=view&id=". $t['id'];

                                if ( $m['id'] )
                                {
                                    $replace['MEMBER'] = $m['name'];
                                }
                                else
                                {
                                    $replace['MEMBER'] = $email['nickname'];
                                }

                                $trellis->send_email( $sm['id'], 'staff_reply_ticket', $replace );
                            }

                            $s_email_staff = 0; // Reset
                        }

                        if ( is_array( $do_feeds ) )
                        {
                            require_once TD_SRC .'feed.php';

                            $feed = new feed();
                            $feed->trellis =& $trellis;

                            while( list( $suid, ) = each( $do_feeds ) )
                            {
                                $feed->show_feed( 'stickets', $suid, 1 );
                            }
                        }
                    }

                    #=============================
                    # Update Ticket
                    #=============================

                    if ( $t['status'] == 4 )
                    {
                        if ( $m['id'] )
                        {
                            $db_array = array( 'last_reply' => $email['date'], 'last_uid' => $m['id'], 'last_uname' => $m['name'], 'replies' => ( $t['replies'] + 1 ), 'status' => 1 );
                        }
                        else
                        {
                            $db_array = array( 'last_reply' => $email['date'], 'last_uid' => $m['id'], 'last_uname' => $email['nickname'], 'replies' => ( $t['replies'] + 1 ), 'status' => 1 );
                        }
                    }
                    else
                    {
                        if ( $m['id'] )
                        {
                            $db_array = array( 'last_reply' => $email['date'], 'last_uid' => $m['id'], 'last_uname' => $m['name'], 'replies' => ( $t['replies'] + 1 ) );
                        }
                        else
                        {
                            $db_array = array( 'last_reply' => $email['date'], 'last_uid' => $m['id'], 'last_uname' => $email['nickname'], 'replies' => ( $t['replies'] + 1 ) );
                        }
                    }

                    $trellis->core->db->construct( array(
                                                           'update'    => 'tickets',
                                                           'set'        => $db_array,
                                                            'where'    => array( 'id', '=', $t['id'] ),
                                                            'limit'    => array( 1 ),
                                                     )     );

                    $trellis->core->db->next_shutdown();
                    $trellis->core->db->execute();
                }
                else
                {
                    #=============================
                    # Department Security
                    #=============================

                    $d_allow = unserialize( $m['g_m_depart_perm'] );

                    if ( ! $d_allow[ $d['id'] ] )
                    {
                        $replace = array(); // Initialize for Security

                        if ( $m['id'] )
                        {
                            $trellis->send_email( $m['id'], 'ticket_pipe_rejected', $replace, array( 'from_email' => $d['incoming_email'] ) );
                        }
                        else
                        {
                            $replace['MEM_NAME'] = $email['nickname'];

                            $trellis->send_guest_email( $email['from'], 'ticket_pipe_rejected', $replace, array( 'from_email' => $d['incoming_email'] ) );
                        }

                        $trellis->log( 'security', "New Ticket to &#039;". $d['name'] ."&#039; Denied", 1, $d['id'] );

                        continue;
                    }

                    #=============================
                    # Create Ticket
                    #=============================

                    $db_array = array(
                                      'did'            => $d['id'],
                                      'dname'        => $d['name'],
                                      'uid'            => $m['id'],
                                      'uname'        => $m['name'],
                                      'email'        => $email['from'],
                                      'subject'        => $email['subject'],
                                      'priority'    => 2,
                                      'message'        => $email['message'],
                                      'date'        => $email['date'],
                                      'last_reply'    => $email['date'],
                                      'last_uid'    => $m['id'],
                                      'last_uname'    => $m['name'],
                                      'ipadd'        => $trellis->input['ip_address'],
                                      'status'        => 1,
                                     );

                    if ( ! $m['id'] )
                    {
                        $db_array['tkey'] = substr( md5( 'tk' . uniqid( rand(), true ) . time() ), 0, 11 );
                        $db_array['uname'] = $email['nickname'];
                        $db_array['last_uname'] = $email['nickname'];
                        $db_array['guest'] = 1;
                        $db_array['guest_email'] = 1;
                    }

                    $trellis->core->db->construct( array(
                                                           'insert'    => 'tickets',
                                                           'set'        => $db_array,
                                                     )     );

                    $trellis->core->db->execute();

                    $ticket_id = $trellis->core->db->get_insert_id();

                    $trellis->log( 'user', "Ticket Created &#039;". $email['subject'] ."&#039;", 1, $ticket_id );
                    $trellis->log( 'ticket', "Ticket Created &#039;". $email['subject'] ."&#039;", 1, $ticket_id );

                    #=============================
                    # Update User
                    #=============================

                    if ( $m['id'] )
                    {
                        $trellis->core->db->next_no_quotes('set');

                        $trellis->core->db->construct( array(
                                                               'update'    => 'users',
                                                               'set'        => array( 'open_tickets' => 'open_tickets+1', 'tickets' => 'tickets+1' ),
                                                                'where'    => array( 'id', '=', $m['id'] ),
                                                                'limit'    => array( 1 ),
                                                         )     );

                        $trellis->core->db->next_shutdown();
                        $trellis->core->db->execute();
                    }

                    #=============================
                    # Update Department
                    #=============================

                    $trellis->core->db->next_no_quotes('set');

                    $trellis->core->db->construct( array(
                                                           'update'    => 'departments',
                                                           'set'        => array( 'tickets' => 'tickets+1' ),
                                                            'where'    => array( 'id', '=', $d['id'] ),
                                                            'limit'    => array( 1 ),
                                                     )     );

                    $trellis->core->db->next_shutdown();
                    $trellis->core->db->execute();

                    #=============================
                    # Send Email
                    #=============================

                    if ( $m['id'] )
                    {
                        $replace = array(); // Initialize for Security

                        $replace['TICKET_ID'] = $ticket_id;
                        $replace['SUBJECT'] = $email['subject'];
                        $replace['DEPARTMENT'] = $d['name'];
                        $replace['PRIORITY'] = $trellis->get_priority( 2 );
                        $replace['SUB_DATE'] = $trellis->a5_date( $email['date'] );
                        $replace['TICKET_LINK'] = $trellis->cache->data['config']['hd_url'] ."/index.php?act=tickets&code=view&id=". $ticket_id;

                        $trellis->send_email( $m['id'], 'new_ticket', $replace, array( 'from_email' => $d['incoming_email'] ) );
                    }

                    #=============================
                    # Send Guest Email
                    #=============================

                    if ( ! $m['id'] )
                    {
                        $replace = array(); // Initialize for Security

                        $replace['TICKET_ID'] = $ticket_id;
                        $replace['SUBJECT'] = $email['subject'];
                        $replace['DEPARTMENT'] = $d['name'];
                        $replace['PRIORITY'] = $trellis->get_priority( 2 );
                        $replace['SUB_DATE'] = $trellis->a5_date( $email['date'] );
                        $replace['TICKET_LINK'] = $trellis->cache->data['config']['hd_url'] ."/index.php?act=tickets&code=view&id=". $ticket_id;
                        $replace['MEM_NAME'] = $email['nickname'];
                        $replace['TICKET_KEY'] = $db_array['tkey'];

                        $trellis->send_guest_email( $email['from'], 'new_guest_ticket', $replace, array( 'from_email' => $d['incoming_email'] ) );
                    }

                    #=============================
                    # Email Staff
                    #=============================

                    $trellis->core->db->construct( array(
                                                           'select'    => array( 'm' => array( 'id', 'ugroup', 'email_staff_new_ticket' ),
                                                                                 'g' => array( 'g_depart_perm' ),
                                                                                ),
                                                           'from'        => array( 'm' => 'users' ),
                                                           'join'        => array( array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'm' => 'ugroup' ) ) ),
                                                            'where'    => array( array( 'g' => 'g_acp_access' ), '=', 1 ),
                                                     )     );

                    $trellis->core->db->execute();

                    if ( $trellis->core->db->get_num_rows($staff_sql) )
                    {
                        while( $sm = $trellis->core->db->fetch_row($staff_sql) )
                        {
                            // Check Departments
                            if ( is_array( unserialize( $sm['g_depart_perm'] ) ) )
                            {
                                $my_departs = "";
                                $my_departs = unserialize( $sm['g_depart_perm'] );

                                if ( $my_departs[ $d['id'] ] )
                                {
                                    if ( $sm['email_staff_new_ticket'] )
                                    {
                                        $s_email_staff = 1;
                                    }

                                    $do_feeds[ $sm['id'] ] = 1;
                                }
                            }
                            else
                            {
                                if ( $sm['email_staff_new_ticket'] )
                                {
                                    $s_email_staff = 1;
                                }

                                $do_feeds[ $sm['id'] ] = 1;
                            }

                            if ( $s_email_staff )
                            {
                                $replace = array(); // Initialize for Security

                                $replace['TICKET_ID'] = $ticket_id;
                                $replace['SUBJECT'] = $email['subject'];
                                $replace['DEPARTMENT'] = $d['name'];
                                $replace['PRIORITY'] = $trellis->get_priority( 2 );
                                $replace['SUB_DATE'] = $trellis->a5_date( $email['date'] );
                                $replace['MESSAGE'] = $email['message'];
                                $replace['TICKET_LINK'] = $trellis->cache->data['config']['hd_url'] ."/admin.php?section=manage&act=tickets&code=view&id=". $ticket_id;

                                if ( $m['id'] )
                                {
                                    $replace['MEMBER'] = $m['name'];

                                    $trellis->send_email( $sm['id'], 'staff_new_ticket', $replace );
                                }
                                else
                                {
                                    $replace['MEMBER'] = $email['nickname'];

                                    $trellis->send_email( $sm['id'], 'staff_new_guest_ticket', $replace );
                                }
                            }

                            $s_email_staff = 0; // Reset
                        }
                    }

                    if ( is_array( $do_feeds ) )
                    {
                        require_once TD_SRC .'feed.php';

                        $feed = new feed();
                        $feed->trellis =& $trellis;

                        while( list( $suid, ) = each( $do_feeds ) )
                        {
                            $feed->show_feed( 'stickets', $suid, 1 );
                        }
                    }
                }
            }

            imap_expunge( $mbox );

            imap_close( $mbox );
        }
    }
}

#=============================
# Update Stats
#=============================

$trellis->r_ticket_stats(1);

#=============================
# Bye Bye
#=============================

$trellis->core->shut_down_q();
$trellis->shut_down();
$trellis->core->shut_down();

#=============================
# Decode ISO88591
# Courtesy of PHP.net from
# aperez at informatica dot 24ruedas dot com
#=============================

function decode_ISO88591( $string )
{
    $string = str_replace( "=\r\n", "", $string );

    $string = str_replace("=?iso-8859-1?q?","",$string);
    $string = str_replace("=?iso-8859-1?Q?","",$string);
    $string = str_replace("?=","",$string);

    $charHex = array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F" );

    for( $z=0; $z < sizeof($charHex); $z++ )
    {
        for( $i=0; $i< sizeof($charHex); $i++ )
        {
            $string = str_replace( ("=" .( $charHex[$z].$charHex[$i] ) ), chr( hexdec( $charHex[$z].$charHex[$i]) ), $string );
        }
    }

    return($string);
}

?>