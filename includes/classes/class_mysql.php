<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_db_mysql {

    var $cid;
    var $query_id            = "";
    var $query_last;
    var $query_count        = 0;
    var $query_s_count        = 0;
    var $is_shutdown        = 0;
    var $all_tablesow_shutdown        = 0;
    var $shutdown_queries    = array();
    var $database_name        = "";
    var $db_prefix            = "";

    private $allow_shutdown = false;

    #=======================================
    # @ Constructor
    #=======================================

    function __construct($config)
    {
        #=============================
        # Connect to Database
        #=============================

        $this->allow_shutdown = $config['shutdown_queries'];

        define( 'TDDB_PRE', $config['prefix'] );

        if ( ! $this->cid = mysql_connect( $config['host'] . ( ( $config['port'] ) ? ( ':'. $config['port'] ) : null ), $config['user'], $config['pass'] ) ) trigger_error( "MySQL - Unable to connect to database. The following error was returned:<br />\n". mysql_errno() .": ". mysql_error(), E_USER_ERROR );

        if ( ! mysql_select_db( $config['name'], $this->cid ) ) trigger_error( "MySQL - Unable to select database", E_USER_ERROR );

        $this->database_name = $config['name'];
        $this->db_prefix = $config['prefix'];

        mysql_query( "SET NAMES 'utf8'" );

        return true;
    }

    #=======================================
    # @ Next Shutdown
    # Tells the module that the next query
    # should be scheduled for shutdown.
    #=======================================

    function next_shutdown()
    {
        if ( $this->allow_shutdown )
        {
            $this->is_shutdown = 1;

            $this->query_s_count ++;
        }
    }

    #=======================================
    # @ Run Query
    # Runs a MySQL query.
    #=======================================

    function query($query="")
    {
        if ( ! $query )
        {
            $query = $this->query_id;
        }

        if ( ! $this->query_last = mysql_query( $query, $this->cid ) ) trigger_error( "MySQL - Unable to process query:<br />\n". $query ."<br />\nThe following error was returned:<br />\n". mysql_errno() .": ". mysql_error(), E_USER_ERROR );

        $this->queries_ran .= $query .'<br /><br />';

        $this->query_count ++;

        #echo $query .'<br />';
        #echo 'Memory: '. ( memory_get_usage() / 1000000 ) .'<br /><br />';

        return $this->query_last;
    }

    #=======================================
    # @ Construct Query
    # Constructs our query for MySQL.
    #=======================================

    function construct($do)
    {
        $this->query_id = ""; // Initialize for Security

        #=============================
        # Standard
        #=============================

        # SELECT
        if ( $do['select'] )
        {
            $this->query_id = 'SELECT';

            if ( is_array( $do['select'] ) )
            {
                if ( is_array( $do['join'] ) )
                {
                    while( list( $id, $fields ) = each( $do['select'] ) )
                    {
                        if ( $fields == 'all')
                        {
                            $this->query_id .= ' '. $id .'.*,';
                        }
                        else
                        {
                            while( list( , $field ) = each( $fields ) )
                            {
                                if ( is_array( $field ) )
                                {
                                    $this->query_id .= ' '. $id .'.`'. mysql_real_escape_string( key( $field ) ) .'` AS `'. mysql_real_escape_string( current( $field ) ) .'`,';
                                }
                                else
                                {
                                    $this->query_id .= ' '. $id .'.`'. mysql_real_escape_string( $field ) .'`,';
                                }
                            }
                        }
                    }
                }
                else
                {
                    while( list( , $field ) = each( $do['select'] ) )
                    {
                        if ( is_array( $field ) )
                        {
                            $this->query_id .= ' `'. mysql_real_escape_string( key( $field ) ) .'` AS `'. mysql_real_escape_string( current( $field ) ) .'`,';
                        }
                        else
                        {
                            $this->query_id .= ' `'. mysql_real_escape_string( $field ) .'`,';
                        }
                    }
                }

                $this->query_id = substr( $this->query_id, 0, -1 );
            }
            elseif ( $do['select'] == 'all' )
            {
                $this->query_id .= ' *';
            }

            if ( is_array( $do['join'] ) )
            {
                while( list( $id, $table ) = each( $do['from'] ) )
                {
                    $this->query_id .= ' FROM `'. TDDB_PRE . mysql_real_escape_string( $table ) .'` '. $id ;
                }
            }
            else
            {
                $this->query_id .= ' FROM `'. TDDB_PRE . mysql_real_escape_string( $do['from'] ) .'`';
            }
        }

        # INSERT / UPDATE
        if ( $do['insert'] || $do['update'] )
        {
            if ( $do['insert'] )
            {
                $this->query_id = 'INSERT INTO `'. TDDB_PRE . mysql_real_escape_string( $do['insert'] ) .'`';
            }
            else
            {
                $this->query_id = 'UPDATE `'. TDDB_PRE . mysql_real_escape_string( $do['update'] ) .'`';
            }

            $this->query_id .= ' SET';

            while( list( $field, $value ) = each( $do['set'] ) )
            {
                if ( is_array( $value ) )
                {
                    $this->query_id .= ' `'. mysql_real_escape_string( $field ) .'` = ';

                    if ( $value[0] == '=' )
                    {
                        $this->query_id .= mysql_real_escape_string( $value );
                    }
                    elseif ( $value[0] == '-' )
                    {
                        $this->query_id .= '`'. mysql_real_escape_string( $field ) .'`-'. intval( $value[1] );
                    }
                    elseif ( $value[0] == '+' )
                    {
                        $this->query_id .= '`'. mysql_real_escape_string( $field ) .'`+'. intval( $value[1] );
                    }

                    $this->query_id .= ',';
                }
                else
                {
                    $this->query_id .= ' `'. mysql_real_escape_string( $field ) .'` = \''. mysql_real_escape_string( $value ) .'\',';
                }
            }

            $this->query_id = substr( $this->query_id, 0, -1 );
        }

        # DELETE
        if ( $do['delete'] )
        {
            $this->query_id = 'DELETE FROM `'. TDDB_PRE . mysql_real_escape_string( $do['delete'] ) .'`';
        }

        #=============================
        # Extras
        #=============================

        # JOIN
        if ( is_array( $do['join'] ) )
        {
            while( list( , $join ) = each( $do['join'] ) )
            {
                list( $final_fid, $final_table ) = each( $join['from'] );

                $this->query_id .= ' LEFT JOIN `'. TDDB_PRE . mysql_real_escape_string( $final_table ) .'` '. $final_fid .' ON ( ';

                if ( is_array( $join['where'][0] ) )
                {
                    $placedfirst = 0;

                    foreach( $join['where'] as $jcw )
                    {
                        if ( $placedfirst ) $this->query_id .= 'AND ';

                        $i = 0;

                        while( list( $wid, $wfield ) = each( $jcw ) )
                        {
                            $i ++;

                            if ( $i == 1 )
                            {
                                if( is_numeric( $wid ) )
                                {
                                    $this->query_id .= "'". mysql_real_escape_string( $wfield ) ."' ";
                                }
                                else
                                {
                                    $this->query_id .= $wid .'.';
                                    $this->query_id .= '`'. mysql_real_escape_string( $wfield ) .'` ';
                                }
                            }
                            elseif ( $i == 2 )
                            {
                                $this->query_id .= $wfield .' ';
                            }
                            elseif ( $i == 3 )
                            {
                                if( is_numeric( $wid ) )
                                {
                                    $this->query_id .= "'". mysql_real_escape_string( $wfield ) ."' ";
                                }
                                else
                                {
                                    $this->query_id .= $wid .'.';
                                    $this->query_id .= '`'. mysql_real_escape_string( $wfield ) .'` ';
                                }
                            }
                        }

                        if ( ! $placedfirst ) $placedfirst = 1;
                    }
                }
                else
                {
                    $i = 0;

                    while( list( $wid, $wfield ) = each( $join['where'] ) )
                    {
                        $i ++;

                        if ( $i == 1 )
                        {
                            if( is_numeric( $wid ) )
                            {
                                $this->query_id .= "'". mysql_real_escape_string( $wfield ) ."' ";
                            }
                            else
                            {
                                $this->query_id .= $wid .'.';
                                $this->query_id .= '`'. mysql_real_escape_string( $wfield ) .'` ';
                            }
                        }
                        elseif ( $i == 2 )
                        {
                            $this->query_id .= $wfield .' ';
                        }
                        elseif ( $i == 3 )
                        {
                            if( is_numeric( $wid ) )
                            {
                                $this->query_id .= "'". mysql_real_escape_string( $wfield ) ."' ";
                            }
                            else
                            {
                                $this->query_id .= $wid .'.';
                                $this->query_id .= '`'. mysql_real_escape_string( $wfield ) .'` ';
                            }
                        }
                    }
                }

                $this->query_id .= ')';
            }
        }

        # WHERE
        if ( is_array( $do['where'] ) && ! empty( $do['where'] ) )
        {
            $this->query_id .= ' WHERE';

            if ( is_array( $do['where'][0] ) )
            {
                if ( is_array( $do['join'] ) )
                {
                    if ( is_array( $do['where'][0][0] ) )
                    {
                        while( list( , $where ) = each( $do['where'] ) )
                        {
                            if ( is_array( $where[0][0] ) )  # FIXME: This whole file is a mess. Let's use functions and really clean this up. This slight modification is due to group where conditions. Don't break anything. Check with ticket list (should also show tickets that you're assigned to even if they are outside your department)
                            {
                                $this->query_id .= $this->add_logic( end( $where ) );

                                $this->query_id .= ' ( ';

                                $whereb = $where;

                                foreach( $whereb as $where )
                                {
                                    if ( ! is_array( $where[0] ) ) continue;

                                    list( $final_id, $final_field ) = each( $where[0] );

                                    $this->add_logic( $where[3] );

                                    if ( strpos( $final_field, '|' ) )
                                    {
                                        $wdata = explode( '|', $where[0] );

                                        $this->query_id .= $this->get_function( $final_id .'.`'. $wdata[0] .'`', $wdata[1] );
                                    }
                                    else
                                    {
                                        $this->query_id .= ' '. $final_id .'.`'. mysql_real_escape_string( $final_field ) .'`';
                                    }

                                    if ( $where[1] == 'in' )
                                    {
                                        $this->add_where_in( $where[2] );
                                    }
                                    elseif ( $where[1] == 'like' )
                                    {
                                        $this->add_where_like( $where[2] );
                                    }
                                    elseif ( $where[1] == 'is' )
                                    {
                                        $this->add_where_is( $where[2] );
                                    }
                                    else
                                    {
                                        $this->query_id .= ' '. $where[1].' \''. mysql_real_escape_string( $where[2] ) .'\'';
                                    }
                                }

                                $this->query_id .= ' )';
                            }
                            else
                            {
                                list( $final_id, $final_field ) = each( $where[0] );

                                $this->add_logic( $where[3] );

                                if ( strpos( $final_field, '|' ) )
                                {
                                    $wdata = explode( '|', $where[0] );

                                    $this->query_id .= $this->get_function( $final_id .'.`'. $wdata[0] .'`', $wdata[1] );
                                }
                                else
                                {
                                    $this->query_id .= ' '. $final_id .'.`'. mysql_real_escape_string( $final_field ) .'`';
                                }

                                if ( $where[1] == 'in' )
                                {
                                    $this->add_where_in( $where[2] );
                                }
                                elseif ( $where[1] == 'like' )
                                {
                                    $this->add_where_like( $where[2] );
                                }
                                elseif ( $where[1] == 'is' )
                                {
                                    $this->add_where_is( $where[2] );
                                }
                                else
                                {
                                    $this->query_id .= ' '. $where[1].' \''. mysql_real_escape_string( $where[2] ) .'\'';
                                }
                            }
                        }
                    }
                    else
                    {
                        list( $final_id, $final_field ) = each( $do['where'][0] );

                        $this->add_logic( $do['where'][3] );

                        if ( strpos( $final_field, '|' ) )
                        {
                            $wdata = explode( '|', $final_field );

                            $this->query_id .= ' '. $this->get_function( $final_id .'.`'. $wdata[0] .'`', $wdata[1] );
                        }
                        else
                        {
                            $this->query_id .= ' '. $final_id .'.`'. mysql_real_escape_string( $final_field ) .'`';
                        }

                        if ( $do['where'][1] == 'in' )
                        {
                            $this->add_where_in( $do['where'][2] );
                        }
                        elseif ( $do['where'][1] == 'like' )
                        {
                            $this->add_where_like( $do['where'][2] );
                        }
                        elseif ( $do['where'][1] == 'is' )
                        {
                            $this->add_where_is( $do['where'][2] );
                        }
                        else
                        {
                            $this->query_id .= ' '. $do['where'][1].' \''. mysql_real_escape_string( $do['where'][2] ) .'\'';
                        }
                    }
                }
                else
                {
                       while( list( , $where ) = each( $do['where'] ) )
                    {
                        $this->add_logic( $where[3] );

                        if ( strpos( $where[0], '|' ) )
                        {
                            $wdata = explode( '|', $where[0] );

                            $this->query_id .= ' '. $this->get_function( '`'. $wdata[0] .'`', $wdata[1] );
                        }
                        else
                        {
                            $this->query_id .= ' `'. mysql_real_escape_string( $where[0] ) .'`';
                        }

                        if ( $where[1] == 'in' )
                        {
                            $this->add_where_in( $where[2] );
                        }
                        elseif ( $where[1] == 'like' )
                        {
                            $this->add_where_like( $where[2] );
                        }
                        elseif ( $where[1] == 'is' )
                        {
                            $this->add_where_is( $where[2] );
                        }
                        else
                        {
                            $this->query_id .= ' '. $where[1].' \''. mysql_real_escape_string( $where[2] ) .'\'';
                        }
                    }
                }
            }
            else
            {
                if ( strpos( $do['where'][0], '|' ) )
                {
                    $wdata = explode( '|', $do['where'][0] );

                    $this->query_id .= ' '. $this->get_function( '`'. $wdata[0] .'`', $wdata[1] );
                }
                else
                {
                    $this->query_id .= ' `'. mysql_real_escape_string( $do['where'][0] ) .'` ';
                }

                if ( $do['where'][1] == 'in' )
                {
                    $this->add_where_in( $do['where'][2] );
                }
                elseif ( $do['where'][1] == 'like' )
                {
                    $this->add_where_like( $do['where'][2] );
                }
                elseif ( $do['where'][1] == 'is' )
                {
                    $this->add_where_is( $do['where'][2] );
                }
                else
                {
                    $this->query_id .= $do['where'][1].' \''. mysql_real_escape_string( $do['where'][2] ) .'\'';
                }
            }
        }

        # GROUP
        if ( $do['group'] )
        {
            if ( is_array( $do['group'] ) )
            {
                $this->query_id .= ' GROUP BY '. key( $do['group'] ) .'.`'. mysql_real_escape_string( current( $do['group'] ) ) .'`';
            }
            else
            {
                $this->query_id .= ' GROUP BY `'. mysql_real_escape_string( $do['group'] ) .'`';
            }
        }

        # ORDER
        if ( is_array( $do['order'] ) )
        {
            $this->query_id .= ' ORDER BY';

            while( list( $field, $order ) = each( $do['order'] ) )
            {
                if ( is_array( $do['join'] ) )
                {
                    list( $id, $real_order ) = each( $order );

                    $this->query_id .= ' '. $id .'.`'. mysql_real_escape_string( $field ) .'` '. mysql_real_escape_string( $real_order ) .',';
                }
                else
                {
                    $this->query_id .= ' `'. mysql_real_escape_string( $field ) .'` '. mysql_real_escape_string( $order ) .',';
                }
            }

            $this->query_id = substr( $this->query_id, 0, -1 );
        }

        # LIMIT
        if ( is_array( $do['limit'] ) )
        {
            if ( isset( $do['limit'][1] ) )
            {
                $this->query_id .= ' LIMIT '. mysql_real_escape_string( intval( $do['limit'][0] ) ) .','. mysql_real_escape_string( intval( $do['limit'][1] ) );
            }
            else
            {
                $this->query_id .= ' LIMIT '. mysql_real_escape_string( intval( $do['limit'][0] ) );
            }
        }

        return $this->query_id;
    }

    #=======================================
    # @ Get Function
    # Returns the appropriate SQL syntax for
    # the requested function.
    #=======================================

    function get_function($field, $func)
    {
        if ( $func == 'lower' )
        {
            $return = 'LOWER('. mysql_real_escape_string( $field ) .')';
        }

        return $return;
    }

    #=======================================
    # @ Add Logic
    # Adds the appropriate SQL syntax for
    # the requested logic operator to the
    # current query.
    #=======================================

    function add_logic($alias)
    {
        if ( $alias )
        {
            if ( $alias == 'and' )
            {
                $this->query_id .= ' AND';
            }
            elseif ( $alias == 'or' )
            {
                $this->query_id .= ' OR';
            }
            elseif ( $alias == 'xor' )
            {
                $this->query_id .= ' XOR';
            }
        }
    }

    #=======================================
    # @ Add Where IN
    # Adds the appropriate SQL syntax for
    # the WHERE IN clause to the current
    # query.
    #=======================================

    function add_where_in($values)
    {
        $this->query_id .= ' IN ';
        $this->query_id .= '( ';

        while( list( , $in ) = each( $values ) )
        {
            $this->query_id .= '\''. mysql_real_escape_string( $in ) .'\', ';
        }

        $this->query_id = substr( $this->query_id, 0, -2 );

        $this->query_id .= ' )';
    }

    #=======================================
    # @ Add Where LIKE
    # Adds the appropriate SQL syntax for
    # the WHERE LIKE clause to the current
    # query.
    #=======================================

    function add_where_like($value)
    {
        $this->query_id .= ' LIKE \''. mysql_real_escape_string( $value ) .'\'';
    }

    #=======================================
    # @ Add Where IS
    #=======================================

    function add_where_is($value)
    {
        $value = strtoupper( $value );

        if ( $value != 'NULL' && $value != 'NOT NULL' ) return false;

        $this->query_id .= ' IS '. $value;
    }

    #=======================================
    # @ Execute Query
    # Executes our cute litte query.
    #=======================================

    function execute($to_exe="")
    {
        if ( ! $to_exe )
        {
            $to_exe = $this->query_id;
        }

        if ( $this->is_shutdown )
        {
            $this->shutdown_queries[] = $to_exe;

            $this->is_shutdown = 0;
            $this->query_id = "";

            return TRUE;
        }

        $eq = $this->query($to_exe);

        $this->query_id = "";

        return $eq;
    }

    #=======================================
    # @ Clear Memory
    # Removes the last run query from cache.
    #=======================================

    function clear_memory()
    {
        $this->query_last = "";
    }

    #=======================================
    # @ Fetch Row
    # Fetches row information from query.
    #=======================================

    function fetch_row($query="")
    {
        if ( ! $query )
        {
            $query = $this->query_last;
        }

        $record = mysql_fetch_array( $query, MYSQL_ASSOC );

        return $record;
    }

    #=======================================
    # @ Get Number of Rows
    # Fetches the number of rows selected
    # by a query.
    #=======================================

    function get_num_rows($query="")
    {
        if ( ! $query )
        {
            $query = $this->query_last;
        }

        $rows = @mysql_num_rows($query);

        return $rows;
    }

    #=======================================
    # @ Get Number of Affected Rows
    # Fetches the number of rows affected
    # by a query.
    #=======================================

    function get_affected_rows()
    {
        return @mysql_affected_rows( $this->cid );
    }

    #=======================================
    # @ Query Count
    # Returns the number of queries executed.
    #=======================================

    function get_query_count()
    {
        return $this->query_count;
    }

    #=======================================
    # @ Query Shutdown Count
    # Returns the number of shutdown queries
    # scheduled.
    #=======================================

    function get_query_s_count()
    {
        return $this->query_s_count;
    }

    #=======================================
    # @ Query Total Count
    # Returns the total number of queries
    # (to be / already) executed.
    #=======================================

    function get_query_t_count()
    {
        return $this->query_count + $this->query_s_count;
    }

    #=======================================
    # @ Get Insert ID
    # Returns the insert id of previous
    # query.
    #=======================================

    function get_insert_id()
    {
        return mysql_insert_id( $this->cid );
    }

    #=======================================
    # @ Get Tables
    # Returns al list of tables in the
    # specified database.
    #=======================================

    function get_tables()
    {
        return $this->query( "SHOW TABLES FROM ". $this->database_name );
    }

    #=======================================
    # @ Get Backup
    # Generates a backup file by dumping
    # the SQL data and structure.
    # Courtesy of Unreal Ed from Programming Talk.
    # http://www.programmingtalk.com/member.php?userid=141445
    # Modified by someotherguy of ACCORD5
    #=======================================

    function get_backup($p_tables="", $p_drop_table=0, $p_if_not_exists=0)
    {
        $table_status = mysql_query("SHOW TABLE STATUS");

        while( $all_tables = mysql_fetch_assoc( $table_status ) )
        {
            $tbl_stat[ $all_tables[Name] ] = $all_tables[Auto_increment];
        }

        $backup = "-- Trellis Desk SQL Dump\n\n-- --------------------------------------------------------\n";

        $tables = $this->get_tables();

        while( $tabs = mysql_fetch_row( $tables ) )
        {
            $do_backup = 0; // Reset

            if ( is_array( $p_tables ) )
            {
                if ( $p_tables[ $tabs[0] ] )
                {
                    $do_backup = 1;
                }
            }
            else
            {
                $do_backup = 1;
            }

            if ( $do_backup )
            {
                   $backup .= "\n--\n-- Table structure for $tabs[0]\n--\n\n";

                   if ( $p_drop_table )
                   {
                       $backup .= "DROP TABLE IF EXISTS $tabs[0];\n";
                   }

                   if ( $p_if_not_exists )
                   {
                       $backup .= "CREATE TABLE IF NOT EXISTS $tabs[0] (";
                   }
                   else
                   {
                       $backup .= "CREATE TABLE $tabs[0] (";
                   }

                $res = mysql_query("SHOW CREATE TABLE $tabs[0]");

                while( $all_tables = mysql_fetch_assoc( $res ) )
                {
                    $str = str_replace("CREATE TABLE $tabs[0] (", "", $all_tables['Create Table']);
                    $str = str_replace(",", ",", $str);
                    $str2 = str_replace(") ) TYPE=MyISAM ", ")\n ) TYPE=MyISAM ", $str);

                    if ( $tbl_stat[$tabs[0]] )
                    {
                        $backup .= $str2 ." AUTO_INCREMENT=". $tbl_stat[$tabs[0]] .";\n\n";
                    }
                    else
                    {
                        $backup .= $str2 .";\n\n";
                    }
                }

                $backup .= "--\n-- Dumping data for table $tabs[0]\n--\n\n";
                   $data = mysql_query("SELECT * FROM $tabs[0]");

                while( $dt = mysql_fetch_row( $data ) )
                {
                       $backup .= "INSERT INTO $tabs[0] VALUES('". str_replace( "\r\n", '\r\n', addslashes($dt[0]) ) ."'";

                    for( $i=1; $i < sizeof($dt); $i++ )
                    {
                        #$dt[$i] = str_replace( "\r\n", '\r\n', $dt[$i] );
                        $backup .= ", '". str_replace( "\r\n", '\r\n', addslashes($dt[$i]) ) ."'";
                    }

                    $backup .= ");\n";
                }
            }
        }

        return $backup;
    }

    #=======================================
    # @ Get
    #=======================================

    public function get($input, $key)
    {
        $return = array();

        $this->construct( array(
                                'select'    => $input['select'],
                                'from'        => $input['from'],
                                'join'        => $input['join'],
                                'where'        => $input['where'],
                                'order'        => $input['order'],
                                'limit'        => $input['limit'],
                         )        );

        $this->execute();

        if ( ! $this->get_num_rows() ) return false;

        while ( $r = $this->fetch_row() )
        {
            $return[ $r[ $key ] ] = $r;
        }

        return $return;
    }

    #=======================================
    # @ Get Single
    #=======================================

    public function get_single($input)
    {
        $this->construct( array(
                                'select'    => $input['select'],
                                'from'        => $input['from'],
                                'join'        => $input['join'],
                                'where'        => $input['where'],
                                'order'        => $input['order'],
                                'limit'        => array( 0, 1 ),
                         )        );

        $this->execute();

        if ( ! $this->get_num_rows() ) return false;

        return $this->fetch_row();
    }

    #=======================================
    # @ Shutdown
    #=======================================

    public function shut_down()
    {
        if ( $this->allow_shutdown )
        {
            if ( is_array( $this->shutdown_queries ) )
            {
                foreach ( $this->shutdown_queries as &$q )
                {
                    $this->query( $q );
                }
            }
        }
    }
}

?>