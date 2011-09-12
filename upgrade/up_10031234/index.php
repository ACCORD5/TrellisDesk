<?php

/*
#======================================================
|    Trellis Desk
|    =====================================
|    By DJ Tarazona (dj@accord5.com)
|    (c) 2008 ACCORD5
|    http://www.trellisdesk.com/
|    =====================================
|    Email: sales@accord5.com
#======================================================
|    @ Version: v1.0 RC 2 Build 10032251
|    @ Version Int: 100.3.2.251
|    @ Version Num: 10032251
|    @ Build: 0251
#======================================================
|    | Upgrade 10031234
#======================================================
*/

class up_10031234 {

    var $u_ver_id = 10031234;
    var $u_ver_human = 'v1.0 RC 1';

    function auto_run()
    {
        $steps = array( 1 => 'Information', 2 => 'Update Templates', 3 => 'Run SQL Queries', 4 => 'Update Cache', 5 => 'Finish' );
        
        set_steps( 'Upgrade Trellis Desk', $steps );
        set_titles( $steps );    
        
        switch( $this->ifthd->input['step'] )
        {
            case 5:
                $this->step_5();
            break;
            case 4:
                $this->step_4();
            break;
            case 3:
                $this->step_3();
            break;
            case 2:
                $this->step_2();
            break;

            default:
                $this->step_1();
            break;
        }
    }

    function step_1()
    {
        $content = "<div class='groupbox'>Upgrade to ". $this->u_ver_human ."</div>
                    <div class='option1'>
                        Please review the information below.  When you are ready to upgrade to Trellis Desk ". $this->u_ver_human .", click Continue.<br /><br />
                        
                        What's New? (Overview)<br />
                        &rsaquo; Improved Session Handler<br />
                        &rsaquo; Improved 'In Progress' Ticket Status Handler<br />
                        &rsaquo; Improved Security<br />
                        &rsaquo; Minor UI Updates<br />
                        &rsaquo; TinyMCE and Spell Checker Upgraded<br />
                        &rsaquo; Ability to Unassign a Ticket<br />
                        &rsaquo; Removed character requirements for input.<br />
                        &rsaquo; Various Bugs Fixes and Other Minor Features
                    </div>
                    <div class='option2'>Don't forget to backup your files and databases.</div>
                    <div class='formtail'><div class='fb_pad'><a href='index.php?do=". $this->u_ver_id ."&amp;step=2' class='fake_button'>Continue</a></div></div>";
        
        do_output( $content, 1 );
    }

    function step_2($error='')
    {
        $content = "";
        $row_count = 0;
        
        if ( $error ) $content .= "<div class='critical'>{$error}</div>";
        
        $content .= "<form action='index.php?do=". $this->u_ver_id ."&amp;step=3' method='post'>
                    <div class='groupbox'>Select Skins to Update</div>
                    <div class='option1'>RC 1 includes updates to all skin templates and CSS.  It also includes a brand-new skin which will be automatically installed.  Select the skin sets you would like to update below with the updated <i>classic</i> templates.  If you choose to update a skin set, modifications made to the templates will be lost.  If you choose not to update, you may need to manually make modifications to the skin set's template in order for Trellis Desk to function properly.</div>";
        
        foreach ( $this->ifthd->cache->data['skin'] as $stype => $sk )
        {        
            $row_count ++;
            
            ( $row_count & 1 ) ? $row_class = 'option1' : $row_class = 'option2';
            
            if ( $stype != 'default' )
            {
                $content .= "<div class='{$row_class}'><input type='checkbox' name='sk_". $sk['id'] ."' id='sk_". $sk['id'] ."' value='1' class='radio' />&nbsp;&nbsp;<label for='sk_". $sk['id'] ."'>". $sk['name'] ."</label></div>";
            }
        }
        
        $content .= "<div class='formtail'><input type='submit' name='submit' id='continue_button' value='Continue' class='button' /></div>
                    </form>";
        
        do_output( $content, 2 );
    }

    function step_3($error='')
    {
        $content = "";
        
        if ( ! $error )
        {
            $parser = new td_parser();
            
            if ( ! $new_skin_id = $this->ifthd->cache->data['upgrade']['10031234']['updated_old'] )
            {
                if ( ! $skin_sql_u = mysql_query( "UPDATE `". DB_PRE ."skins` SET `img_dir` = 'classic' WHERE `img_dir` = 'default'" ) )
                {
                    $this->step_2( "An error encountered while trying to run the following SQL Query.<br /><br />". $skin_sql_u ."<br /><br />MySQL returned the following error.<br /><br />". mysql_error() ."<br /><br />". mysql_errno() );
                }
                
                $this->ifthd->core->add( 'upgrade', array( '10031234' => array( 'updated_old' => 1 ) ) );
                
                $upgrade_old = 1;
            }
            
            if ( ! $new_skin_id = $this->ifthd->cache->data['upgrade']['10031234']['new_skin_id'] )
            {
                if ( ! $skin_sql = mysql_query( "INSERT INTO `". DB_PRE ."skins` VALUES (NULL, 'Trellis Desk Default (New)', 'default', 0, 0, 'ACCORD5', 'sales@accord5.com', 'http://www.accord5.com/', '&copy; 2007 ACCORD5');" ) )
                {
                    $this->step_2( "An error encountered while trying to run the following SQL Query.<br /><br />". $skin_sql ."<br /><br />MySQL returned the following error.<br /><br />". mysql_error() ."<br /><br />". mysql_errno() );
                }
                
                $new_skin_id = mysql_insert_id();
                
                if ( $upgrade_old )
                {
                    $this->ifthd->core->add( 'upgrade', array( '10031234' => array( 'updated_old' => 1, 'new_skin_id' => $new_skin_id ) ) );
                }
                else
                {
                    $this->ifthd->core->add( 'upgrade', array( '10031234' => array( 'new_skin_id' => $new_skin_id ) ) );
                }
            }
            
            $data = $parser->parseFile( './up_10031234/skin_trellis_desk_default_td.xml' );
            $sinfo = $data[0];
            $templates = $data[1];
                    
            if ( ! is_dir( TD_PATH .'skin/s'. $new_skin_id ) && ! @ mkdir( TD_PATH .'skin/s'. $new_skin_id ) )
            {
                $this->step_2('We could not create the directory <i>skin/s'. $new_skin_id .'</i>.  Please CHMOD <i>skin</i> to 0777.');
            }
            
            foreach ( $templates as $tblah => $tinfo )
            {
                if( $handlet = @fopen( TD_PATH .'skin/s'. $new_skin_id .'/'. $tinfo['tname'], 'w' ) )
                {
                    if ( ! @fwrite( $handlet, $tinfo['tcontent'] ) )
                    {
                        $this->step_2('We could not write to the file <i>skin/s'. $new_skin_id .'/'. $tinfo['tname'] .'</i>.  Please CHMOD <i>skin/s'. $new_skin_id .'/'. $tinfo['tname'] .'</i> to 0777.');
                    }
            
                    @fclose($handlet);
                }
                else
                {
                    $this->step_2('We could not create the file <i>skin/s'. $new_skin_id .'/'. $tinfo['tname'] .'</i>.  Please CHMOD <i>skin/s'. $new_skin_id .'</i> to 0777.  If <i>skin/s'. $new_skin_id .'/'. $tinfo['tname'] .'</i> already exists, please CHMOD <i>skin/s'. $new_skin_id .'/'. $tinfo['tname'] .'</i> to 0777.');
                }
            }
            
            if( $handle = @fopen( TD_PATH .'skin/s'. $new_skin_id .'/style.css', 'w' ) )
            {
                if ( ! @fwrite( $handle, $sinfo['sk_css'] ) )
                {
                    $this->step_2('We could not write to the file <i>skin/s'. $new_skin_id .'/style.css</i>.  Please CHMOD <i>skin/s'. $new_skin_id .'/style.css</i> to 0777.');
                }
            
                @fclose($handle);
            }
            else
            {
                $this->step_2('We could not create the file <i>skin/s'. $new_skin_id .'/style.css</i>.  Please CHMOD <i>skin/s'. $new_skin_id .'</i> to 0777.  If <i>skin/s'. $new_skin_id .'/style.css</i> already exists, please CHMOD <i>skin/s'. $new_skin_id .'/style.css</i> to 0777.');
            }
    
            while ( list( $stype, $sk ) = each( $this->ifthd->cache->data['skin'] ) )
            {
                if ( $stype != 'default' && $this->ifthd->input[ 'sk_'. $sk['id'] ] )
                {
                    $do[ $sk['id'] ] = 1;
                }
            }
            
            if ( ! empty( $do ) )
            {
                $datab = $parser->parseFile( './up_10031234/skin_trellis_desk_classic_td.xml' );
                $sinfob = $datab[0];
                $templatesb = $datab[1];
            
                while ( list( $skin_id, ) = each( $do ) )
                {
                    foreach ( $templatesb as $tblah => $tinfo )
                    {
                        if( $handlet = @fopen( TD_PATH .'skin/s'. $skin_id .'/'. $tinfo['tname'], 'w' ) )
                        {
                            if ( ! @fwrite( $handlet, $tinfo['tcontent'] ) )
                            {
                                $this->step_2('We could not write to the file <i>skin/s'. $skin_id .'/'. $tinfo['tname'] .'</i>.  Please CHMOD <i>skin/s'. $skin_id .'/'. $tinfo['tname'] .'</i> to 0777.');
                            }
                    
                            @fclose($handlet);
                        }
                        else
                        {
                            $this->step_2('We could not create the file <i>skin/s'. $skin_id .'/'. $tinfo['tname'] .'</i>.  Please CHMOD <i>skin/s'. $skin_id .'</i> to 0777.  If <i>skin/s'. $skin_id .'/'. $tinfo['tname'] .'</i> already exists, please CHMOD <i>skin/s'. $skin_id .'/'. $tinfo['tname'] .'</i> to 0777.');
                        }
                    }
                    
                    if( $handle = @fopen( TD_PATH .'skin/s'. $skin_id .'/style.css', 'w' ) )
                    {
                        if ( ! @fwrite( $handle, $sinfob['sk_css'] ) )
                        {
                            $this->step_2('We could not write to the file <i>skin/s'. $skin_id .'/style.css</i>.  Please CHMOD <i>skin/s'. $skin_id .'/style.css</i> to 0777.');
                        }
                    
                        @fclose($handle);
                    }
                    else
                    {
                        $this->step_2('We could not create the file <i>skin/s'. $skin_id .'/style.css</i>.  Please CHMOD <i>skin/s'. $skin_id .'</i> to 0777.  If <i>skin/s'. $skin_id .'/style.css</i> already exists, please CHMOD <i>skin/s'. $skin_id .'/style.css</i> to 0777.');
                    }
                }
            }
        }
        
        $content .= "<div class='groupbox'>Update SQL Database</div>
                    <div class='option1'>Click Continue to run the required SQL queries for the upgrade.</div>
                    <div class='formtail'><div class='fb_pad'><a href='index.php?do=". $this->u_ver_id ."&amp;step=4' class='fake_button'>Continue</a></div></div>";
        
        do_output( $content, 3 );
    }

    function step_4()
    {
        $this->ukey = md5( $this->u_ver_id . time() . $this->m['id'] . uniqid( rand(), true ) );

        require_once "./up_". $this->u_ver_id ."/sql_queries.php";

        while ( list( , $sql_query ) = each( $SQL ) )
        {
            if ( ! mysql_query($sql_query) )
            {
                $this->step_3( "An error encountered while trying to run the following SQL Query.<br /><br />". $sql_query ."<br /><br />MySQL returned the following error.<br /><br />". mysql_error() ."<br /><br />". mysql_errno() );
            }
        }
        
        $content .= "<div class='groupbox'>Flat-File Database</div>
                    <div class='option1'>All SQL queries have been run successfully.  Click Continue to rebuild the necessary cache.</div>
                    <div class='formtail'><div class='fb_pad'><a href='index.php?do=". $this->u_ver_id ."&amp;step=5' class='fake_button'>Continue</a></div></div>";
        
        do_output( $content, 4 );
    }

    function step_5()
    {
        $this->ifthd->rebuild_set_cache();
        $this->ifthd->rebuild_skin_cache();
        
        $this->ifthd->core->add( 'upgrade', array( '10031234' => array( 'updated_old' => 0, 'new_skin_id' => 0) ) );        
        
        $content = "<div class='groupbox'>Upgrade Complete</div>
                    <div class='option1'>Congratulations, Trellis Desk has been successfully upgraded to ". $this->u_ver_human .".  Click the link below to return to Trellis Desk.</div>
                    <div class='option2'><a href='http://docs.accord5.com/trellis/whatsnew' target='_blank'>To learn more about this upgrade, such as where new features are located and how to configure them, click here.</a></div>
                    <div class='formtail'><div class='fb_pad'><a href='../index.php' class='fake_button'>Finish</a></div></div>";
        
        do_output( $content, 5 );
    }
}

?>