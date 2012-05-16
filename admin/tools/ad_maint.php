<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_maint {

    #=======================================
    # @ Auto Run
    # Function that is run automatically
    # when the file is required.
    #=======================================

    function auto_run()
    {
        if ( ! $this->trellis->user['acp']['tools_maint'] )
        {
            $this->trellis->skin->error('no_perm');
        }
        
        $this->trellis->skin->set_section( 'Tools &amp; Maintenance' );        
        $this->trellis->skin->set_description( 'Run reports &amp; statistics,  maintenance utilities, recount functions, cleaning utilities, and backup functions.' );

        switch( $this->trellis->input['code'] )
        {
            case 'recount':
                $this->show_recount();
            break;
            case 'rebuild':
                $this->show_rebuild();
            break;
            case 'clean':
                $this->show_clean();
            break;
            case 'syscheck':
                $this->syscheck();
            break;

            case 'dorecount':
                $this->do_recount();
            break;
            case 'doclean':
                $this->do_clean();
            break;

            default:
                $this->show_recount();
            break;
        }
    }

    #=======================================
    # @ Show Recount
    # Show the recount page.
    #=======================================

    function show_recount($alert='')
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['acp']['tools_maint_recount'] )
        {
            $this->trellis->skin->error('no_perm');
        }

        #=============================
        # Do Output
        #=============================
        
        if ( $alert )
        {
            $alert = "<div class='alert'>{$alert}</div>";
        }

        $this->output = "{$alert}
                        <div class='groupbox'>Recount Functions</div>
                        <div class='subbox'>Please select a task below.</div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rticket'>Ticket Statistics</a> <span class='desc'>-- Rebuild ticket statistics such as total tickets, etc.</span></div>
                        <div class='option2'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rkb'>Knowledge Base Statistics</a> <span class='desc'>-- Rebuild knowledge base statistics such as article count, etc.</span></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=ruser'>User Statistics</a> <span class='desc'>-- Rebuild user statistics such as total user count, etc.</span></div>
                        <div class='option2'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rreplies'>Replies Per Ticket</a> <span class='desc'>-- The number of replies for each ticket.</span></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rmemtick'>Tickets Per User</a> <span class='desc'>-- The number of tickets for each user.</span></div>
                        <div class='option2'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rdeptick'>Tickets Per Department</a> <span class='desc'>-- The number of tickets for each department.</span></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rtassign'>Assigned Tickets</a> <span class='desc'>-- The number of open tickets assigned to each staff user.</span></div>
                        <div class='option2'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=racomments'>Article Comments</a> <span class='desc'>-- The number of comments for each article.</span></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rncomments'>News Comments</a> <span class='desc'>-- The number of comments for each announcement.</span></div>
                        <div class='option2'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rusers'>Users</a> <span class='desc'>-- The number of users for each group, etc.</span></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rartrate'>Article Ratings</a> <span class='desc'>-- The rating value for each article.</span></div>
                        <div class='option2'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=rsettings'>Settings</a> <span class='desc'>-- The number of settings per settings group.</span></div>";

        $this->trellis->skin->add_output( $this->output );

        $this->nav = array(
                           "<a href='<! TD_URL !>/admin.php?section=tools'>Tools</a>",
                           "<a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint'>Maintenance</a>",
                           "Recount Functions",
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => 'Maintenance' ) );
    }

    #=======================================
    # @ Show Rebuild
    # Show the rebuild page.
    #=======================================

    function show_rebuild($alert='')
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['acp']['tools_maint_recount'] )
        {
            $this->trellis->skin->error('no_perm');
        }

        #=============================
        # Do Output
        #=============================
        
        if ( $alert )
        {
            $alert = "<div class='alert'>{$alert}</div>";
        }

        $this->output = "{$alert}
                        <div class='groupbox'>Rebuild Functions</div>
                        <div class='subbox'>Please select a function below to rebuild.</div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=cannounce'>Announcement Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=ccanned'>Canned Replies Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=ckbcat'>Category Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=ccdfields'>Custom Department Fields Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=ccpfields'>Custom Profile Fields Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=cdepart'>Department Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=cgroup'>Group Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=clang'>Language Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=cconfig'>Settings Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=cskin'>Skin Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=cstaff'>Staff Cache</a></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=cchmod'>CHMOD Cache Files</a> <span class='desc'>-- CHMOD all cache files to 0777.</span></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=cloginkeys'>Login Keys</a> <span class='desc'>-- Regenerate login keys for all users.</span></div>
                        <div class='option1'><a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint&amp;code=dorecount&amp;type=crsskeys'>RSS Keys</a> <span class='desc'>-- Regenerate RSS keys for all users.</span></div>";

        $this->trellis->skin->add_output( $this->output );

        $this->nav = array(
                           "<a href='<! TD_URL !>/admin.php?section=tools'>Tools</a>",
                           "<a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint'>Maintenance</a>",
                           "Rebuild Functions",
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => 'Maintenance' ) );
    }

    #=======================================
    # @ Show Clean
    # Show the Spring cleaning page. :D
    #=======================================

    function show_clean($alert='')
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['acp']['tools_maint_clean'] )
        {
            $this->trellis->skin->error('no_perm');
        }

        #=============================
        # Do Output
        #=============================
        
        if ( $alert )
        {
            $alert = "<div class='alert'>{$alert}</div>";
        }

        $this->output = "{$alert}
                        <div class='groupbox'>Spring Cleaning</div>
                        <div class='subbox'>Please select the checkbox next to each action you wish to perform.  Then click Start Cleaning.</div>
                        <form action='<! TD_URL !>/admin.php?section=tools&amp;act=clean&amp;code=doclean' method='post' onsubmit='return validate_form(this)'>
                        <table width='100%' cellpadding='0' cellspacing='0'>
                        <tr>
                            <td class='option1' width='5%' align='center'><input type='checkbox' name='del_old_tickets' value='1' class='ckbox' /></td>
                            <td class='option1' width='95%'>Delete all tickets older than <input type='text' name='dot_days' id='dot_days' value='{$this->trellis->input['dot_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option2' align='center'><input type='checkbox' name='del_old_comments' value='1' class='ckbox' /></td>
                            <td class='option2' width='95%'>Delete all comments older than <input type='text' name='doc_days' id='doc_days' value='{$this->trellis->input['doc_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option1' align='center'><input type='checkbox' name='del_unapproved_mem' value='1' class='ckbox' /></td>
                            <td class='option1'>Delete all validating users who have been registered for more than <input type='text' name='dum_days' id='dum_days' value='{$this->trellis->input['dum_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option2' align='center'><input type='checkbox' name='del_inactive_mem' value='1' class='ckbox' /></td>
                            <td class='option2'>Delete all users who have been inactive for <input type='text' name='dim_days' id='dim_days' value='{$this->trellis->input['dim_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option1' align='center'><input type='checkbox' name='delete_core_logs' value='1' class='ckbox' /></td>
                            <td class='option1'>Delete all A5 Core logs.</td>
                        </tr>
                        <tr>
                            <td class='option2' align='center'><input type='checkbox' name='delete_tmp_files' value='1' class='ckbox' /></td>
                            <td class='option2'>Delete all A5 Core temporary files.</td>
                        </tr>
                        <tr>
                            <td class='option1' align='center'><input type='checkbox' name='del_logs_admin' value='1' class='ckbox' /></td>
                            <td class='option1'>Delete admin logs older than <input type='text' name='dla_days' id='dla_days' value='{$this->trellis->input['dla_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option2' align='center'><input type='checkbox' name='del_logs_mem' value='1' class='ckbox' /></td>
                            <td class='option2'>Delete user logs older than <input type='text' name='dlm_days' id='dlm_days' value='{$this->trellis->input['dlm_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option1' align='center'><input type='checkbox' name='del_logs_error' value='1' class='ckbox' /></td>
                            <td class='option1'>Delete error logs older than <input type='text' name='dle_days' id='dle_days' value='{$this->trellis->input['dle_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option2' align='center'><input type='checkbox' name='del_logs_sec' value='1' class='ckbox' /></td>
                            <td class='option2'>Delete security logs older than <input type='text' name='dls_days' id='dls_days' value='{$this->trellis->input['dls_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option1' align='center'><input type='checkbox' name='del_logs_tick' value='1' class='ckbox' /></td>
                            <td class='option1'>Delete ticket logs older than <input type='text' name='dlt_days' id='dlt_days' value='{$this->trellis->input['dlt_days']}' size='3' /> days.</td>
                        </tr>
                        <tr>
                            <td class='option2' align='center'><input type='checkbox' name='kill_asessions' value='1' class='ckbox' /></td>
                            <td class='option2'>Kill all administrative sessions (you will be logged out).</td>
                        </tr>
                        <tr>
                            <td class='option1' align='center'><input type='checkbox' name='kill_sessions' value='1' class='ckbox' /></td>
                            <td class='option1'>Kill all user sessions.</td>
                        </tr>
                        </table>
                        <div class='formtail'><input type='submit' name='submit' id='clean' value='Start Cleaning' class='button' /></div>
                        </form>";

        $this->trellis->skin->add_output( $this->output );

        $this->nav = array(
                           "<a href='<! TD_URL !>/admin.php?section=tools'>Tools</a>",
                           "<a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint'>Maintenance</a>",
                           "Spring Cleaning",
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => 'Maintenance' ) );
    }

    #=======================================
    # @ Do Recount
    # Perform the appropriate task to
    # recount or rebuild.
    #=======================================

    function do_recount()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['acp']['tools_maint_recount'] )
        {
            $this->trellis->skin->error('no_perm');
        }

        #=============================
        # Perform Our Action
        #=============================

        if ( substr( $this->trellis->input['type'], 0, 1 ) == 'c' )
        {
            if ( $this->trellis->input['type'] == 'cannounce' )
            {
                $this->trellis->rebuild_announce_cache();

                $this->trellis->log( 'other', "Announcement Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'ccanned' )
            {
                $this->trellis->rebuild_canned_cache();

                $this->trellis->log( 'other', "Canned Replies Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'ckbcat' )
            {
                $this->trellis->rebuild_cat_cache();

                $this->trellis->log( 'other', "Category Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'cdepart' )
            {
                $this->trellis->rebuild_dprt_cache();

                $this->trellis->log( 'other', "Department Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'cgroup' )
            {
                $this->trellis->rebuild_groups_cache();

                $this->trellis->log( 'other', "Group Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'clang' )
            {
                $this->trellis->rebuild_lang_cache();

                $this->trellis->log( 'other', "Language Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'cskin' )
            {
                $this->trellis->rebuild_skin_cache();

                $this->trellis->log( 'other', "Skin Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'cstaff' )
            {
                $this->trellis->rebuild_staff_cache();

                $this->trellis->log( 'other', "Staff Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'cconfig' )
            {
                $this->trellis->rebuild_set_cache();

                $this->trellis->log( 'other', "Settings Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'ccdfields' )
            {
                $this->trellis->rebuild_dfields_cache();

                $this->trellis->log( 'other', "Custom Department Fields Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'ccpfields' )
            {
                $this->trellis->rebuild_pfields_cache();

                $this->trellis->log( 'other', "Custom Profile Fields Cache Rebuilt" );
            }
            elseif ( $this->trellis->input['type'] == 'cchmod' )
            {
                $this->trellis->core->chmod();

                $this->trellis->log( 'other', "Cache Files CHMOD to 0777" );
            }
            elseif ( $this->trellis->input['type'] == 'crsskeys' )
            {
                $this->r_rss_keys();
            }
            elseif ( $this->trellis->input['type'] == 'cloginkeys' )
            {
                $this->r_login_keys();
            }

            #$this->trellis->skin->redirect( '?section=tools&act=maint&code=rebuild', 'cache_rebuilt' );
            $this->show_rebuild( 'The rebuild function has been successfully run.' );
        }
        elseif ( substr( $this->trellis->input['type'], 0, 1 ) == 'r' )
        {

            if ( $this->trellis->input['type'] == 'rticket' )
            {
                $this->trellis->r_ticket_stats();

                $this->trellis->log( 'other', "Rebuilt Ticket Statistics" );
            }
            elseif ( $this->trellis->input['type'] == 'rkb' )
            {
                $this->trellis->r_kb_stats();

                $this->trellis->log( 'other', "Rebuilt KB Statistics" );
            }
            elseif ( $this->trellis->input['type'] == 'ruser' )
            {
                $this->trellis->r_user_stats();

                $this->trellis->log( 'other', "Rebuilt User Statistics" );
            }
            elseif ( $this->trellis->input['type'] == 'rreplies' )
            {
                $this->r_replies_per_ticket();
            }
            elseif ( $this->trellis->input['type'] == 'rreplies' )
            {
                $this->r_replies_per_ticket();
            }
            elseif ( $this->trellis->input['type'] == 'rmemtick' )
            {
                $this->trellis->r_tickets_per_user();
            }
            elseif ( $this->trellis->input['type'] == 'rdeptick' )
            {
                $this->trellis->r_tickets_per_dept();
            }
            elseif ( $this->trellis->input['type'] == 'racomments' )
            {
                $this->r_acomments();
            }
            elseif ( $this->trellis->input['type'] == 'rncomments' )
            {
                $this->r_ncomments();
            }
            elseif ( $this->trellis->input['type'] == 'rusers' )
            {
                $this->r_users();
            }
            elseif ( $this->trellis->input['type'] == 'rartrate' )
            {
                $this->r_article_ratings();
            }
            elseif ( $this->trellis->input['type'] == 'rsettings' )
            {
                $this->r_settings();
            }
            elseif ( $this->trellis->input['type'] == 'rtassign' )
            {
                $this->r_tassigned();
            }

            #$this->trellis->skin->redirect( '?section=tools&act=maint&code=recount', 'maint_recount' );
            $this->show_rebuild( 'The recount function has been successfully run.' );
        }
    }

    #=======================================
    # @ Recount: Replies Per Ticket
    # Recounts the number of replies per
    # ticket.
    #=======================================

    function r_replies_per_ticket()
    {
        #=============================
        # Grab Replies
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id', 'tid' ),
                                                   'from'        => 'replies',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $r = $this->trellis->db->fetch_row() )
            {
                $replies[ $r['tid'] ] ++;
            }
        }

        #=============================
        # Grab Tickets
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'        => 'tickets',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $t = $this->trellis->db->fetch_row() )
            {
                $tickets[ $t['id'] ] = 1;
            }

            #=============================
            # Update Tickets
            #=============================

            while ( list( $tid, ) = each( $tickets ) )
            {
                $this->trellis->db->construct( array(
                                                           'update'    => 'tickets',
                                                           'set'        => array( 'replies' => $replies[ $tid ] ),
                                                           'where'    => array( 'id', '=', $tid ),
                                                     )     );

                $this->trellis->db->execute();
            }
        }

        $this->trellis->log( 'other', "Recounted Replies Per Ticket" );
    }

    #=======================================
    # @ Recount: Article Comments
    # Recounts the number of comments per
    # article.
    #=======================================

    function r_acomments()
    {
        #=============================
        # Grab Comments
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id', 'aid' ),
                                                   'from'        => 'comments',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $c = $this->trellis->db->fetch_row() )
            {
                $comments[ $c['aid'] ] ++;
            }
        }

        #=============================
        # Grab Articles
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'        => 'articles',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $a = $this->trellis->db->fetch_row() )
            {
                $articles[ $a['id'] ] = 1;
            }

            #=============================
            # Update Articles
            #=============================

            while ( list( $aid, ) = each( $articles ) )
            {
                $this->trellis->db->construct( array(
                                                           'update'    => 'articles',
                                                           'set'        => array( 'comments' => $comments[ $aid ] ),
                                                           'where'    => array( 'id', '=', $aid ),
                                                     )     );

                $this->trellis->db->execute();
            }
        }

        $this->trellis->log( 'other', "Recounted Article Comments" );
    }

    #=======================================
    # @ Recount: News Comments
    # Recounts the number of comments per
    # announcement.
    #=======================================

    function r_ncomments()
    {
        #=============================
        # Grab Comments
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id', 'nid' ),
                                                   'from'        => 'news_comments',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $c = $this->trellis->db->fetch_row() )
            {
                $comments[ $c['nid'] ] ++;
            }
        }

        #=============================
        # Grab Announcements
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'        => 'announcements',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $a = $this->trellis->db->fetch_row() )
            {
                $news[ $a['id'] ] = 1;
            }

            #=============================
            # Update Announcements
            #=============================

            while ( list( $nid, ) = each( $news ) )
            {
                $this->trellis->db->construct( array(
                                                           'update'    => 'announcements',
                                                           'set'        => array( 'comments' => $comments[ $nid ] ),
                                                           'where'    => array( 'id', '=', $nid ),
                                                     )     );

                $this->trellis->db->execute();
            }
        }

        $this->trellis->log( 'other', "Recounted News Comments" );
    }

    #=======================================
    # @ Recount: Users
    # Recounts the number of users per
    # group.
    #=======================================

    function r_users()
    {
        #=============================
        # Grab Users
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id', 'ugroup' ),
                                                   'from'        => 'users',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $m = $this->trellis->db->fetch_row() )
            {
                $users[ $m['ugroup'] ] ++;
            }
        }

        #=============================
        # Grab Groups
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'g_id' ),
                                                   'from'        => 'groups',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $g = $this->trellis->db->fetch_row() )
            {
                $groups[ $g['g_id'] ] = 1;
            }
        }

        #=============================
        # Update Groups
        #=============================

        while ( list( $gid, ) = each( $groups ) )
        {
            $this->trellis->db->construct( array(
                                                       'update'    => 'groups',
                                                       'set'        => array( 'g_users' => $users[ $gid ] ),
                                                       'where'    => array( 'g_id', '=', $gid ),
                                                 )     );

            $this->trellis->db->execute();
        }

        $this->trellis->log( 'other', "Recounted Users" );
    }

    #=======================================
    # @ Recount: Settings
    # Recounts the number of settings per
    # settings group.
    #=======================================

    function r_settings()
    {
        #=============================
        # Grab Settings
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'cf_id', 'cf_group' ),
                                                   'from'        => 'settings',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $s = $this->trellis->db->fetch_row() )
            {
                $settings[ $s['cf_group'] ] ++;
            }
        }

        #=============================
        # Grab Settings Groups
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'cg_id' ),
                                                   'from'        => 'settings_groups',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $g = $this->trellis->db->fetch_row() )
            {
                $groups[ $g['cg_id'] ] = 1;
            }
        }

        #=============================
        # Update Settings Groups
        #=============================

        while ( list( $gid, ) = each( $groups ) )
        {
            $this->trellis->db->construct( array(
                                                       'update'    => 'settings_groups',
                                                       'set'        => array( 'cg_set_count' => $settings[ $gid ] ),
                                                       'where'    => array( 'cg_id', '=', $gid ),
                                                 )     );

            $this->trellis->db->execute();
        }

        $this->trellis->log( 'other', "Recounted Settings" );
    }

    #=======================================
    # @ Recount: Article Ratings
    # Recounts the article rating for each
    # article.
    #=======================================

    function r_article_ratings()
    {
        #=============================
        # Grab Ratings
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id', 'aid', 'rating' ),
                                                   'from'        => 'article_rate',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $r = $this->trellis->db->fetch_row() )
            {
                $rates[ $r['aid'] ] += $r['rating'];
                $rate_count[ $r['aid'] ] ++;
            }

            #=============================
            # Calculate Ratings
            #=============================

            while ( list( $a_id, $t_rate ) = each( $rates ) )
            {
                $ratings[ $a_id ] = round( ( $t_rate / $rate_count[ $a_id ] ), 2 );
            }

            #=============================
            # Grab Articles
            #=============================

            $this->trellis->db->construct( array(
                                                       'select'    => array( 'id' ),
                                                       'from'        => 'articles',
                                                 )     );

            $this->trellis->db->execute();

            if ( $this->trellis->db->get_num_rows() )
            {
                while( $a = $this->trellis->db->fetch_row() )
                {
                    $articles[ $a['id'] ] = 1;
                }
            }

            #=============================
            # Update Articles
            #=============================

            while ( list( $aid, ) = each( $articles ) )
            {
                $this->trellis->db->construct( array(
                                                           'update'    => 'articles',
                                                           'set'        => array( 'votes' => $rate_count[ $aid ], 'rating' => $ratings[ $aid ] ),
                                                           'where'    => array( 'id', '=', $aid ),
                                                     )     );

                $this->trellis->db->execute();
            }
        }

        $this->trellis->log( 'other', "Recounted Article Ratings" );
    }

    #=======================================
    # @ Recount: RSS Keys
    # Regenerates new RSS keys for users.
    #=======================================

    function r_rss_keys()
    {
        #=============================
        # Grab Users
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'        => 'users',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $m = $this->trellis->db->fetch_row() )
            {
                $users[ $m['id'] ] = 1;
            }
        }

        #=============================
        # Generate Keys and Update
        #=============================

        while ( list( $uid, ) = each( $users ) )
        {
            $rss_key = md5( 'rk' . uniqid( rand(), true ) . $uid );

            $this->trellis->db->construct( array(
                                                       'update'    => 'users',
                                                       'set'        => array( 'rss_key' => $rss_key ),
                                                       'where'    => array( 'id', '=', $uid ),
                                                 )     );

            $this->trellis->db->execute();
        }

        $this->trellis->log( 'other', "RSS Keys Regenerated" );
    }

    #=======================================
    # @ Rebuild: Login Keys
    # Regenerates new login keys for users.
    #=======================================

    function r_login_keys()
    {
        #=============================
        # Grab Users
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id' ),
                                                   'from'        => 'users',
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $m = $this->trellis->db->fetch_row() )
            {
                $users[ $m['id'] ] = 1;
            }
        }

        #=============================
        # Generate Keys and Update
        #=============================

        while ( list( $uid, ) = each( $users ) )
        {
            $login_key = str_replace( "=", "", base64_encode( strrev( crypt( md5( 'lk'. uniqid( rand(), true ) . $uid ) ) ) ) );

            $this->trellis->db->construct( array(
                                                       'update'    => 'users',
                                                       'set'        => array( 'login_key' => $login_key ),
                                                       'where'    => array( 'id', '=', $uid ),
                                                 )     );

            $this->trellis->db->execute();
        }

        $this->trellis->log( 'other', "Login Keys Regenerated" );
    }

    #=======================================
    # @ Recount: Assigned Tickets
    # Recounts the number of assigned
    # tickets per staff user.
    #=======================================

    function r_tassigned()
    {
        #=============================
        # Grab Tickets
        #=============================

        $this->trellis->db->construct( array(
                                                   'select'    => array( 'id', 'auid' ),
                                                   'from'        => 'tickets',
                                                   'where'    => array( array( 'auid', '!=', 0 ), array( 'status', '!=', 6, 'and' ) )
                                             )     );

        $this->trellis->db->execute();

        if ( $this->trellis->db->get_num_rows() )
        {
            while( $t = $this->trellis->db->fetch_row() )
            {
                $staff[ $t['auid'] ] ++;
            }
        }

        #=============================
        # Update Staff
        #=============================

        while ( list( $suid, ) = each( $staff ) )
        {
            if ( $this->trellis->cache->data['staff'][ $suid ] )
            {
                $this->trellis->db->construct( array(
                                                           'update'    => 'users',
                                                           'set'        => array( 'assigned' => $staff[ $suid ] ),
                                                           'where'    => array( 'id', '=', $suid ),
                                                     )     );

                $this->trellis->db->execute();
            }
        }

        $this->trellis->log( 'other', "Recounted Assigned Tickets" );

        $this->trellis->rebuild_staff_cache();
    }

    #=======================================
    # @ System Check
    # Perform a self system check.
    #=======================================

    function syscheck()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['acp']['tools_maint_syscheck'] )
        {
            $this->trellis->skin->error('no_perm');
        }

        $this->output = "<div class='groupbox'>System Check</div>
                        <table width='100%' cellpadding='0' cellspacing='0'>
                        <tr>
                            <th width='50%' align='left'>Table</th>
                            <th width='12%'>Found</th>
                            <th width='38%'>Rows</th>
                        </tr>";

        #=============================
        # Check Database
        #=============================

        $datab_c = array(
                            'announcements',
                            'articles',
                            'article_rate',
                            'asessions',
                            'attachments',
                            'canned',
                            'categories',
                            'comments',
                            'departments',
                            'depart_fields',
                            'groups',
                            'languages',
                            'logs',
                            'users',
                            'news_comments',
                            'pages',
                            'profile_fields',
                            'replies',
                            'reply_rate',
                            'sessions',
                            'settings',
                            'settings_groups',
                            'skins',
                            'tickets',
                            'tokens',
                            'upg_history',
                            'validation',
                        );

        $sql = $this->trellis->db->get_tables();
        $num_rows = $this->trellis->db->get_num_rows( $sql );
        $row_count = 0; // Initialize for Security

        for ( $i = 0; $i < $num_rows; $i++ )
        {
            $tables[] = mysql_tablename( $sql, $i );
        }

        while ( list( , $ck_table ) = each( $datab_c ) )
        {            
            $row_count ++;
                    
            ( $row_count & 1 ) ? $row_class = 'option1-med' : $row_class = 'option2-med';
            
            if ( ! in_array( $this->trellis->db->db_prefix . $ck_table, $tables ) )
            {
                $this->output .= "<tr>
                                    <td class='{$row_class}'><font color='#FF0000'>". $ck_table ."</font></td>
                                    <td class='{$row_class}' align='center'><font color='#FF0000'>Not Found</font></td>
                                    <td class='{$row_class}' align='center'><font color='#FF0000'>X</font></td>
                                </tr>";
            }
            else
            {
                $this->trellis->db->construct( array(
                                                           'select'    => 'all',
                                                           'from'        => $ck_table,
                                                     )     );

                $this->trellis->db->execute();

                $temp_rows = $this->trellis->db->get_num_rows();

                $this->output .= "<tr>
                                    <td class='{$row_class}'><font color='#007900'>". $ck_table ."</font></td>
                                    <td class='{$row_class}' align='center'><font color='#007900'>Found</font></td>
                                    <td class='{$row_class}' align='center'><font color='#007900'>". $temp_rows ."</font></td>
                                </tr>";
            }
        }

        $this->output .= "</table>";

        $this->trellis->skin->add_output( $this->output );

        $this->nav = array(
                           "<a href='<! TD_URL !>/admin.php?section=tools'>Tools</a>",
                           "<a href='<! TD_URL !>/admin.php?section=tools&amp;act=maint'>Maintenance</a>",
                           "System Check",
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => 'Maintenance' ) );
    }

    #=======================================
    # @ Do Clean
    # Perform a Spring cleaning.
    #=======================================

    function do_clean()
    {
        #=============================
        # Security Checks
        #=============================

        if ( ! $this->trellis->user['acp']['tools_maint_clean'] )
        {
            $this->trellis->skin->error('no_perm');
        }

        #=============================
        # Do Some Cleanin'
        #=============================

        if ( $this->trellis->input['del_old_tickets'] && $this->trellis->input['dot_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'tickets',
                                                        'where'    => array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dot_days'] ) ) ),
                                                 )     );

            $this->trellis->db->execute();

            $rticket = 1;
            $rmemtick = 1;
            $rdeptick = 1;
        }

        if ( $this->trellis->input['del_old_comments'] && $this->trellis->input['doc_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'comments',
                                                        'where'    => array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['doc_days'] ) ) ),
                                                 )     );

            $this->trellis->db->execute();

            $rcomments = 1;
        }

        if ( $this->trellis->input['del_unapproved_mem'] && $this->trellis->input['dum_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'users',
                                                        'where'    => array( array( 'ugroup', '=', 3, ), array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dum_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();

            $ruser = 1;
            $rusers = 1;
        }

        if ( $this->trellis->input['del_inactive_mem'] && $this->trellis->input['dim_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'users',
                                                        'where'    => array( array( 'ugroup', '!=', 4, ), array( 'last_activity', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dim_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();

            $ruser = 1;
            $rusers = 1;
        }

        if ( $this->trellis->input['delete_core_logs'] )
        {
            if ( $handle = opendir( TD_PATH .'core/logs' ) )
            {
                 while ( ( $file = readdir($handle) ) !== false )
                {
                    if ( $file != "." && $file != ".." && $file != "index.html" )
                    {
                        if ( ! is_dir( TD_PATH .'core/logs/' . $file ) )
                        {
                            @unlink( TD_PATH .'core/logs/' . $file );
                        }
                    }
                }

                closedir($handle);
            }
        }

        if ( $this->trellis->input['delete_tmp_files'] )
        {
            if ( $handle = opendir( TD_PATH .'core/tmp' ) )
            {
                 while ( ( $file = readdir($handle) ) !== false )
                {
                    if ( $file != "." && $file != ".." && $file != "index.html" )
                    {
                        if ( ! is_dir( TD_PATH .'core/tmp/' . $file ) )
                        {
                            @unlink( TD_PATH .'core/tmp/' . $file );
                        }
                    }
                }

                closedir($handle);
            }
        }

        if ( $this->trellis->input['del_logs_admin'] && $this->trellis->input['dla_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'logs',
                                                       'where'    => array( array( 'type', '=', 2, ), array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dla_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();
        }

        if ( $this->trellis->input['del_logs_mem'] && $this->trellis->input['dlm_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'logs',
                                                        'where'    => array( array( 'type', '=', 6, ), array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dlm_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();
        }

        if ( $this->trellis->input['del_logs_error'] && $this->trellis->input['dle_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'logs',
                                                        'where'    => array( array( 'type', '=', 3, ), array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dle_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();
        }

        if ( $this->trellis->input['del_logs_sec'] && $this->trellis->input['dls_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'logs',
                                                        'where'    => array( array( 'type', '=', 4, ), array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dls_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();
        }

        if ( $this->trellis->input['del_logs_tick'] && $this->trellis->input['dlt_days'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'logs',
                                                        'where'    => array( array( 'type', '=', 7, ), array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dlt_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();
        }

        if ( $this->trellis->input['kill_asessions'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'asessions',
                                                       #'where'    => array( array( 'type', '=', 2, ), array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dla_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();
        }


        if ( $this->trellis->input['kill_sessions'] )
        {
            $this->trellis->db->construct( array(
                                                       'delete'    => 'sessions',
                                                       #'where'    => array( array( 'type', '=', 2, ), array( 'date', '<', ( time() - ( 60 * 60 * 24 * $this->trellis->input['dla_days'] ) ), 'and' ) ),
                                                 )     );

            $this->trellis->db->execute();
        }

        #=============================
        # Do We Need To Rebuild?
        #=============================

        if ( $rticket )
        {
            $this->trellis->r_ticket_stats();
        }

        if ( $rmemtick )
        {
            $this->r_tickets_per_user();
        }

        if ( $rdeptick )
        {
            $this->r_tickets_per_dept();
        }

        if ( $rcomments )
        {
            $this->r_comments();
        }

        if ( $ruser )
        {
            $this->trellis->r_user_stats();
        }

        if ( $rusers )
        {
            $this->r_users();
        }

        #=============================
        # Redirect
        #=============================

        $this->trellis->log( 'other', "Spring Cleaning Ran", 2 );

        #$this->trellis->skin->redirect( '?section=tools&act=maint&code=clean', 'spring_clean_success' );
        $this->show_rebuild( 'Spring cleaning has been successfully run.' );
    }

}

?>