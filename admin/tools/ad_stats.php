<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_stats {

    #=======================================
    # @ Auto Run
    #=======================================

    function auto_run()
    {
        if ( ! $this->trellis->user['acp']['tools_stats'] )
        {
            #$this->trellis->skin->error('no_perm'); DO NOT FORGET TO REMOVE!!!
        }
        
        $this->trellis->skin->set_section( 'Tools & Maintenance' );        
        $this->trellis->skin->set_description( 'Run reports &amp; statistics,  maintenance utilities, recount functions, cleaning utilities, and backup functions.' );
        
        include_once TD_INC ."charts/charts.php";

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
                $this->show_splash();
            break;
        }
    }

    #=======================================
    # @ Splash Page
    #=======================================

    function show_splash()
    {
        #=============================
        # Do Output
        #=============================

        $this->output = InsertChart ( TD_INC ."charts/charts.swf", TD_INC ."charts/charts_library", TD_ADMIN ."tools/charts/sample.php", 700, 525, NULL, true ); // 4:3 Ratio

        $this->trellis->skin->add_output( $this->output );

        $this->nav = array(
                           "<a href='<! TD_URL !>/admin.php?section=tools'>Tools</a>",
                           "<a href='<! TD_URL !>/admin.php?section=tools&amp;act=stats'>Reports &amp; Statistics</a>",
                           "SQL Backup",
                           );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => 'Reports &amp; Statistics' ) );
    }

}

?>