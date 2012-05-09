<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_source_pages {

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        #=============================
        # Initialize
        #=============================

        $this->trellis->load_functions('pages');

        $this->show_page();
    }

    #=======================================
    # @ Show Page
    #=======================================

    private function show_page()
    {
        #=============================
        # Grab Page
        #=============================

        if ( ! $p = $this->trellis->func->pages->get_single_by_alias( array( 'id', 'title', 'alias', 'content' ), $this->trellis->input['id'] ) ) $this->trellis->skin->error('no_page');

        #=============================
        # Format Page
        #=============================

        $p['content'] = $this->trellis->prepare_output( $p['content'], array( 'linkify' => 1, 'html' => 1 ) );

        $this->trellis->skin->set_var( 'p', $p );

        #=============================
        # Do Output
        #=============================

        $this->nav = array(
                           '<a href="'. $this->trellis->config['hd_url'] .'/index.php?page=pages&amp;id='. $p['alias'] .'">'. $p['title'] .'</a>'
                           );

        $this->trellis->skin->set_var( 'sub_tpl', 'page.tpl' );

        $this->trellis->skin->do_output( array( 'nav' => $this->nav, 'title' => $p['title'] ) );
    }

}

?>