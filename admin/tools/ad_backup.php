<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_backup {

    #=======================================
    # @ Auto Run
    # Function that is run automatically
    # when the file is required.
    #=======================================

    function auto_run()
    {
		$this->trellis->check_perm( 'tools', 'backup' );

        //$this->trellis->load_functions('settings');
        //$this->trellis->load_lang('settings');

        $this->trellis->skin->set_active_link( 4 );
//        $this->trellis->skin->set_section( 'Tools &amp; Maintenance' );        
//        $this->trellis->skin->set_description( 'Run reports &amp; statistics,  maintenance utilities, recount functions, cleaning utilities, and backup functions.' );

        switch( $this->trellis->input['code'] )
        {
            case 'full':
                $this->show_full();
            break;
            case 'sql':
                $this->show_sql();
            break;
            case 'file':
                $this->show_file();
            break;

            case 'dofull':
                $this->do_full();
            break;
            case 'dosql':
                $this->do_sql();
            break;
            case 'dofile':
                $this->do_file();
            break;

            default:
                $this->show_full();
            break;
        }
    }

    #=======================================
    # @ Show Full
    # Show the full backup page.
    #=======================================

    function show_full()
    {
        #=============================
        # Do Output
        #=============================

        $this->output = "<div class='groupbox'>Full Backup</div>
                        <div class='subbox'>A full backup is a backup of all the files in the Trellis Desk directory, along with a backup of the SQL database.  To perform a full backup backup, simply select your desired options below and then click Backup.</div>
                        <form action='<! TD_URL !>/admin.php?section=tools&amp;page=backup&amp;code=dofull' method='post'>
                        <div class='option1'><label for='td_tables'><input type='checkbox' name='td_tables' id='td_tables' value='1' class='ckbox' checked='checked' /> Backup only Trellis Desk tables</label></div>
                        <div class='option2'><label for='drop_table'><input type='checkbox' name='drop_table' id='drop_table' value='1' class='ckbox' checked='checked' /> Add DROP TABLE IF EXISTS</label></div>
                        <div class='option1'><label for='if_not_exists'><input type='checkbox' name='if_not_exists' id='if_not_exists' value='1' class='ckbox' checked='checked' /> Use CREATE IF NOT EXISTS</label></div>
                        <div class='option2'><label for='gzip'><input type='checkbox' name='gzip' id='gzip' value='1' class='ckbox' checked='checked' /> GZip Backup</label></div>
                        <div class='formtail'><input type='submit' name='submit' id='submit' value='Backup' class='button' /></div>
                        </form>";

        $this->trellis->skin->add_output( $this->output );

        $this->nav = array(
                           "<a href='<! TD_URL !>/admin.php?section=tools'>Tools</a>",
                           "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=backup'>Backup</a>",
                           "SQL Backup",
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => 'Backups &amp; Updates' ) );
    }

    #=======================================
    # @ Show SQL
    # Show the SQL backup page.
    #=======================================

    function show_sql()
    {
        #=============================
        # Do Output
        #=============================

        $this->output = "<div class='groupbox'>SQL Backup</div>
                        <div class='subbox'>To perform an SQL backup, simply select your desired options below and then click Backup.</div>
                        <form action='<! TD_URL !>/admin.php?section=tools&amp;page=backup&amp;code=dosql' method='post'>
                        <div class='option1'><label for='td_tables'><input type='checkbox' name='td_tables' id='td_tables' value='1' class='ckbox' checked='checked' /> Backup only Trellis Desk tables</label></div>
                        <div class='option2'><label for='drop_table'><input type='checkbox' name='drop_table' id='drop_table' value='1' class='ckbox' checked='checked' /> Add DROP TABLE IF EXISTS</label></div>
                        <div class='option1'><label for='if_not_exists'><input type='checkbox' name='if_not_exists' id='if_not_exists' value='1' class='ckbox' checked='checked' /> Use CREATE IF NOT EXISTS</label></div>
                        <div class='option2'><label for='gzip'><input type='checkbox' name='gzip' id='gzip' value='1' class='ckbox' checked='checked' /> GZip Backup</label></div>
                        <div class='formtail'><input type='submit' name='submit' id='submit' value='Backup' class='button' /></div>
                        </form>";

        $this->trellis->skin->add_output( $this->output );

        $this->nav = array(
                           "<a href='<! TD_URL !>/admin.php?section=tools'>Tools</a>",
                           "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=backup'>Backup</a>",
                           "SQL Backup",
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => 'Backups &amp; Updates' ) );
    }

    #=======================================
    # @ Show File
    # Show the file backup page.
    #=======================================

    function show_file()
    {
        #=============================
        # Do Output
        #=============================

        $this->output = "<div class='groupbox'>File Backup</div>
                        <div class='subbox'>To perform a file backup, simply click the Backup button below.  This will take all the files and folders in the Trellis Desk directory and zips them.</div>
                        <form action='<! TD_URL !>/admin.php?section=tools&amp;page=backup&amp;code=dofile' method='post'>
                        <div class='formtail'><input type='submit' name='submit' id='submit' value='Backup' class='button' /></div>
                        </form>";

        $this->trellis->skin->add_output( $this->output );

        $this->nav = array(
                           "<a href='<! TD_URL !>/admin.php?section=tools'>Tools</a>",
                           "<a href='<! TD_URL !>/admin.php?section=tools&amp;page=backup'>Backup</a>",
                           "File Backup",
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => 'Backups &amp; Updates' ) );
    }

    #=======================================
    # @ Do Full
    # Perform a full backup.
    #=======================================

    function do_full()
    {
        if ( $this->trellis->input['td_tables'] )
        {
            $tables = array(
                            //TDDB_PRE .'announcements'        => 1,
                            TDDB_PRE .'articles'            => 1,
                            TDDB_PRE .'article_rate'        => 1,
                            TDDB_PRE .'asessions'            => 1,
                            TDDB_PRE .'attachments'        => 1,
                            //TDDB_PRE .'canned'            => 1,
                            TDDB_PRE .'categories'        => 1,
                            TDDB_PRE .'comments'            => 1,
                            TDDB_PRE .'departments'        => 1,
                            TDDB_PRE .'depart_fields'        => 1,
                            TDDB_PRE .'groups'            => 1,
                            TDDB_PRE .'languages'            => 1,
                            TDDB_PRE .'logs'                => 1,
                            TDDB_PRE .'users'            => 1,
                            TDDB_PRE .'news_comments'        => 1,
                            TDDB_PRE .'pages'                => 1,
                            TDDB_PRE .'profile_fields'    => 1,
                            TDDB_PRE .'replies'            => 1,
                            TDDB_PRE .'reply_rate'        => 1,
                            TDDB_PRE .'sessions'            => 1,
                            TDDB_PRE .'settings'            => 1,
                            TDDB_PRE .'settings_groups'    => 1,
                            TDDB_PRE .'skins'                => 1,
                            TDDB_PRE .'tickets'            => 1,
                            //TDDB_PRE .'tokens'            => 1,
                            TDDB_PRE .'upg_history'        => 1,
                            TDDB_PRE .'validation'        => 1,
                            );
        }

        $backup = $this->trellis->db->get_backup( $tables, $this->trellis->input['drop_table'], $this->trellis->input['if_not_exists'] );

        if ( $this->trellis->input['gzip'] )
        {
            $tmp_file = tempnam( TD_PATH .'core/tmp', 'sqlb_' ) .'.gz';
            $sql_file = 'sql_backup_td.sql.gz';

            $handle = gzopen( $tmp_file, 'w9' );

            gzwrite( $handle, $backup );

            gzclose( $handle );

            $f_tmp = @fopen( $tmp_file, 'r');

            $sql_data = @fread( $f_tmp, filesize( $tmp_file ) );

            fclose( $f_tmp );

            unlink( $tmp_file );
        }
        else
        {
            $sql_file = 'sql_backup_td.sql';
            $sql_data = $backup;
        }

        require_once TD_INC .'classes/class_zip.php';

        $this->zip = new zipfile();

        $this->zip_add_dir( TD_PATH );

        $this->zip->addFile( $sql_data, $sql_file );

        $zip_content = $this->zip->file();

        header('Content-type: application/zip');
        header('Content-length: ' . strlen( $zip_content ));
        header('Content-Disposition: attachment; filename="full_backup_td.zip"');

        print $zip_content;
    }

    #=======================================
    # @ Do SQL
    # Perform an SQL database backup.
    #=======================================

    function do_sql()
    {
        if ( $this->trellis->input['gzip'] )
        {
            header('Content-type: application/x-gzip gz tgz');
            header('Content-Disposition: attachment; filename="sql_backup_td.sql.gz"');
        }
        else
        {
            header('Content-type: application/sql');
            header('Content-Disposition: attachment; filename="sql_backup_td.sql"');
        }

        if ( $this->trellis->input['td_tables'] )
        {
            $tables = array(
                            //TDDB_PRE .'announcements'        => 1,
                            TDDB_PRE .'articles'            => 1,
                            TDDB_PRE .'article_rate'        => 1,
                            TDDB_PRE .'asessions'            => 1,
                            TDDB_PRE .'attachments'        => 1,
                            //TDDB_PRE .'canned'            => 1,
                            TDDB_PRE .'categories'        => 1,
                            TDDB_PRE .'comments'            => 1,
                            TDDB_PRE .'departments'        => 1,
                            TDDB_PRE .'depart_fields'        => 1,
                            TDDB_PRE .'groups'            => 1,
                            TDDB_PRE .'languages'            => 1,
                            TDDB_PRE .'logs'                => 1,
                            TDDB_PRE .'users'            => 1,
                            TDDB_PRE .'news_comments'        => 1,
                            TDDB_PRE .'pages'                => 1,
                            TDDB_PRE .'profile_fields'    => 1,
                            TDDB_PRE .'replies'            => 1,
                            TDDB_PRE .'reply_rate'        => 1,
                            TDDB_PRE .'sessions'            => 1,
                            TDDB_PRE .'settings'            => 1,
                            TDDB_PRE .'settings_groups'    => 1,
                            TDDB_PRE .'skins'                => 1,
                            TDDB_PRE .'tickets'            => 1,
                            //TDDB_PRE .'tokens'            => 1,
                            TDDB_PRE .'upg_history'        => 1,
                            TDDB_PRE .'validation'        => 1,
                            );
        }

        $backup = $this->trellis->mysql->get_backup( $tables, $this->trellis->input['drop_table'], $this->trellis->input['if_not_exists'] );

        if ( $this->trellis->input['gzip'] )
        {
            $tmp_file = tempnam( TD_PATH .'core/tmp', 'sqlb_' ) .'.gz';

            $handle = gzopen( $tmp_file, 'w9' );

            gzwrite( $handle, $backup );

            gzclose( $handle );

            readgzfile( $tmp_file );

            unlink( $tmp_file );
        }
        else
        {
            print $backup;
        }
    }

    #=======================================
    # @ Do File
    # Perform a file backup.
    #=======================================

    function do_file()
    {
        require_once TD_INC .'classes/class_zip.php';

        $this->zip = new zipfile();

        $this->zip_add_dir( TD_PATH );

        $zip_content = $this->zip->file();

        header('Content-type: application/zip');
        header('Content-length: ' . strlen( $zip_content ));
        header('Content-Disposition: attachment; filename="file_backup_td.zip"');

        print $zip_content;
    }

    #=======================================
    # @ Zip Add Directory
    # Adds a directory and its content to
    # the zip file.
    #=======================================

    function zip_add_dir($dir, $extdir="")
    {
        if ( $handle = opendir( $dir ) )
        {
             while ( ( $file = readdir($handle) ) !== false )
            {
                if ( $file != "." && $file != ".." )
                {
                    if ( ! is_dir( $dir . $file ) )
                    {
                        $f_tmp = @fopen( $dir . $file, 'r');

                        if( $f_tmp )
                        {
                            $file_data = @fread( $f_tmp, filesize( $dir . $file ) );

                            $this->zip->addFile( $file_data, $extdir . $file );

                               fclose( $f_tmp );
                        }
                    }
                    else
                    {
                        $this->zip_add_dir( $dir . $file .'/', $extdir . $file .'/' );
                    }
                }
            }

            closedir($handle);
        }
    }

}

?>