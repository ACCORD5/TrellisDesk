<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class feed {

    #=======================================
    # @ Auto Run
    # Function that is run automatically
    # when the file is required.
    #=======================================

    function auto_run()
    {
        #=============================
        # Security Checks
        #=============================

        switch( $this->trellis->input['code'] )
        {
            case 'stickets':
                $this->show_feed('stickets');
            break;

            default:
                $this->show_feed('announcements');
            break;
        }
    }

    #=======================================
    # @ Show Feed
    # Prepares for feed output.
    #=======================================

    function show_feed($type, $uid=0, $create=0)
    {
        if ( $type == 'announcements' )
        {
            $this->feed_file = 'feed_announcements.td';

            $this->output_feed($type);
        }
        elseif ( $type == 'stickets' )
        {
            if ( ! $uid )
            {
                $uid = intval( $this->trellis->input['id'] );
            }

            $this->trellis->db->construct( array(
                                                       'select'    => array( 'm' => array( 'id', 'name', 'ugroup', 'rss_key' ), 'g' => array( 'g_depart_perm', 'g_acp_access' ) ),
                                                       'from'        => array( 'm' => 'users' ),
                                                       'join'        => array( array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'g' => 'g_id', '=', 'm' => 'ugroup' ) ) ),
                                                        'where'    => array( array( array( 'm' => 'id' ), '=', $uid ), array( array( 'g' => 'g_acp_access' ), '=', 1, 'and' ) ),
                                                        'limit'    => array( 0, 1 ),
                                                 )     );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() )
            {
                $this->sm = $this->trellis->db->fetch_row();

                $this->feed_file = 'feed_stickets_m'. $this->sm['id'] .'.td';

                if ( $create )
                {
                    $this->create_feed($type);
                }
                else
                {
                    if ( $this->trellis->input['key'] == $this->sm['rss_key'] )
                    {
                        $this->output_feed($type);
                    }
                }
            }
        }
    }

    #=======================================
    # @ Output Feed
    # Outputs the feed.
    #=======================================

    function output_feed($type)
    {
        header('Content-type: application/xml');

        if ( file_exists( TD_PATH .'core/tmp/'. $this->feed_file ) )
        {
            readfile( TD_PATH .'core/tmp/'. $this->feed_file );
        }
        else
        {
            print $this->create_feed($type);
        }
    }

    #=======================================
    # @ Create Feed
    # Creates a feed.  What else?
    #=======================================

    function create_feed($type)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                <rss version=\"2.0\">
                <channel>";

        if ( $type == 'announcements' )
        {
            $xml .= "<title>". $this->trellis->cache->data['config']['hd_name'] ." Announcements</title>
                    <link>". $this->trellis->config['hd_url'] ."</link>
                    <description>Latest announcements from ". $this->trellis->cache->data['config']['hd_name'] ."</description>
                    <lastBuildDate>". gmdate( 'D, d M Y H:i:s' ) ." GMT</lastBuildDate>";

            $this->trellis->db->construct( array(
                                                        'select'        => 'all',
                                                      'from'        => 'announcements',
                                                      'order'        => array( 'date' => 'desc' ),
                                                      'limit'        => array( 0, $this->trellis->cache->data['config']['announce_amount'] ),
                                                )     );

            $this->trellis->db->execute();

            while ( $a = $this->trellis->db->fetch_row() )
            {
                $xml .= "<item>
                            <title>". $a['title'] ."</title>
                            <link>". $this->trellis->config['hd_url'] ."</link>
                            <guid>". $this->trellis->config['hd_url'] ."/announcements/". $a['id'] ."</guid>
                            <pubDate>". gmdate( 'D, d M Y H:i:s', $a['date'] ) ." GMT</pubDate>
                            <author>". $this->trellis->cache->data['settings']['email']['out_address'] ." (". $a['uname'] .")</author>
                            <description><![CDATA[ ". $this->trellis->prepare_output( $a['content'], 0, 1 ) ." ]]></description>
                        </item>";
            }

            $feed_file = 'feed_announcements.td';
        }
        elseif ( $type == 'stickets' )
        {
            $xml .= "<title>My Department Tickets</title>
                    <link>". $this->trellis->config['hd_url'] ."/admin.php</link>
                    <description>Latest tickets in your department.</description>
                    <lastBuildDate>". gmdate( 'D, d M Y H:i:s' ) ." GMT</lastBuildDate>";

            $this->sm['g_depart_perm'] = unserialize( $this->sm['g_depart_perm'] );

            if ( is_array( $this->sm['g_depart_perm'] ) )
            {
                $rev_perms = array(); // Initialize for Security

                foreach( $this->sm['g_depart_perm'] as $did => $access )
                {
                    if ( $access == 1 ) $rev_perms[] = $did;
                }
            }

            // Tickets
            if ( is_array( $rev_perms ) )
            {
                $this->trellis->db->construct( array(
                                                       'select'    => array( 'id', 'did', 'dname', 'uid', 'uname', 'email', 'subject', 'priority', 'message', 'date' ),
                                                       'from'        => 'tickets',
                                                        'where'    => array( 'did', 'in', $rev_perms ),
                                                        'order'    => array( 'date' => 'DESC' ),
                                                        'limit'    => array( 0, 15 ),
                                                 )     );

                $this->trellis->db->execute();
            }
            else
            {
                $this->trellis->db->construct( array(
                                                       'select'    => array( 'id', 'did', 'dname', 'uid', 'uname', 'email', 'subject', 'priority', 'message', 'date' ),
                                                       'from'        => 'tickets',
                                                        'order'    => array( 'date' => 'DESC' ),
                                                        'limit'    => array( 0, 15 ),
                                                 )     );

                $this->trellis->db->execute();
            }

            $tickets = array(); // Initialize for Security

            while ( $t = $this->trellis->db->fetch_row() )
            {
                $t['type'] = 'ticket';

                $rss_items[ $t['date'] ] = $t;
            }

            // Replies
            if ( is_array( $rev_perms ) )
            {
                $this->trellis->db->construct( array(
                                                       'select'    => array( 'r' => array( 'id', 'tid', 'uid', 'uname', 'message', 'date' ), 't' => array( 'did', 'dname', 'email', 'subject' ) ),
                                                       'from'        => array( 'r' => 'replies' ),
                                                       'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'order'    => array( 'date' => array( 'r' => 'DESC' ) ),
                                                        'where'    => array( array( array( 't' => 'did' ), 'in', $rev_perms ), array( array( 'r' => 'staff' ), '!=', 1, 'and' ) ),
                                                        'limit'    => array( 0, 15 ),
                                                 )     );

                $this->trellis->db->execute();
            }
            else
            {
                $this->trellis->db->construct( array(
                                                       'select'    => array( 'r' => array( 'id', 'tid', 'uid', 'uname', 'message', 'date' ), 't' => array( 'did', 'dname', 'email', 'subject' ) ),
                                                       'from'        => array( 'r' => 'replies' ),
                                                       'join'        => array( array( 'from' => array( 't' => 'tickets' ), 'where' => array( 'r' => 'tid', '=', 't' => 'id' ) ) ),
                                                        'order'    => array( 'date' => array( 'r' => 'DESC' ) ),
                                                        'where'    => array( array( 'r' => 'staff' ), '!=', 1 ),
                                                        'limit'    => array( 0, 15 ),
                                                 )     );

                $this->trellis->db->execute();
            }

            $replies = array(); // Initialize for Security

            while ( $r = $this->trellis->db->fetch_row() )
            {
                $r['type'] = 'reply';

                $rss_items[ $r['date'] ] = $r;
            }

            // Sort
            krsort( $rss_items );

            $count = 0;

            while ( list( $date, $data ) = each( $rss_items ) )
            {
                if ( $count > 14 ) break;

                if ( $data['type'] == 'ticket' )
                {
                    $xml .= "<item>
                                <title>". $data['subject'] ."</title>
                                <link><![CDATA[ ". $this->trellis->config['hd_url'] ."/admin.php?section=manage&act=tickets&code=view&id=". $data['id'] ." ]]></link>
                                <guid><![CDATA[ ". $this->trellis->config['hd_url'] ."/admin.php?section=manage&act=tickets&code=view&id=". $data['id'] ." ]]></guid>
                                <category>". $data['dname'] ."</category>
                                <pubDate>". gmdate( 'D, d M Y H:i:s', $data['date'] ) ." GMT</pubDate>
                                <author>". $data['email'] ." (". $data['uname'] .")</author>
                                <description><![CDATA[ ". $this->trellis->prepare_output( $data['message'] ) ." ]]></description>
                            </item>";
                }
                elseif ( $data['type'] == 'reply' )
                {
                    $xml .= "<item>
                                <title>". $data['subject'] ." (Reply)</title>
                                <link><![CDATA[ ". $this->trellis->config['hd_url'] ."/admin.php?section=manage&act=tickets&code=view&id=". $data['tid'] ."#reply". $data['id'] ." ]]></link>
                                <guid><![CDATA[ ". $this->trellis->config['hd_url'] ."/admin.php?section=manage&act=tickets&code=view&id=". $data['tid'] ."#reply". $data['id'] ." ]]></guid>
                                <category>". $data['dname'] ."</category>
                                <pubDate>". gmdate( 'D, d M Y H:i:s', $data['date'] ) ." GMT</pubDate>
                                <author>". $data['email'] ." (". $data['uname'] .")</author>
                                <description><![CDATA[ ". $this->trellis->prepare_output( $data['message'] ) ." ]]></description>
                            </item>";
                }

                $count ++;
            }
        }

        $xml .= "</channel>
                </rss>";

        if ( $handle = @fopen( TD_PATH .'core/tmp/'. $this->feed_file, 'w' ) )
        {
            fwrite( $handle, $xml );

            @fclose($handle);

            return $xml;
        }

        return FALSE;
    }

}
?>