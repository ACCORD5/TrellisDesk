<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_ad_about {

    private $output = "";

    #=======================================
    # @ Auto Run
    #=======================================

    public function auto_run()
    {
        $this->trellis->load_functions('admin');
        $this->trellis->load_lang('home');

        #=============================
        # Version Check
        #=============================

        $version_check = $this->trellis->func->admin->version_check();

        if ( $version_check == 1 )
        {
            $version_img_url = '<! IMG_DIR !>/vc_update_available.jpg';
        }
        elseif ( $version_check == 2 )
        {
            $version_img_url = '<! IMG_DIR !>/vc_up_to_date.jpg';
        }
        else
        {
            $version_img_url = '<! IMG_DIR !>/vc_unable_to_check.jpg';
        }

        #=============================
        # Sidebar Menu
        #=============================

        $mysql_version = mysql_get_server_info();

        if ( strpos( $mysql_version, '-' ) )
        {
            $mysql_version = substr( $mysql_version, 0, strpos( $mysql_version, '-' ) );
        }

        if ( $this->trellis->cache->data['misc']['vcheck_time'] )
        {
            $vcheck_date = $this->trellis->td_timestamp( array( 'time' => $this->trellis->cache->data['misc']['vcheck_time'], 'format' => 'date' ) );
        }
        else
        {
            $vcheck_date = '{lang.check_now}';
        }

        $system_status_html = "<table width='100%' border='0' cellspacing='0' cellpadding='0' class='blockstatus'>
                    <tr>
                        <td colspan='2' class='statusbadge'><a href='http://www.accord5.com/trellis/latest' target='_blank'><img src='{$version_img_url}' alt='{lang.version_check}' /></a></td>
                    </tr>
                    <tr>
                        <td class='statusleft'><strong>{lang.version_check}</strong></td>
                        <td class='statusright'><a href='<! TD_URL !>/admin.php?section=admin&amp;page=about&amp;vcheck=1' title='{lang.check_now}'>". $vcheck_date ."</a></td>
                    </tr>
                    <tr>
                        <td class='statusleft'><strong>{lang.product_version}</strong></td>
                        <td class='statusright'>{$this->trellis->version_short} ({$this->trellis->version_number})</td>
                    </tr>
                        <td class='statusleft'><strong>{lang.php_version}</strong></td>
                        <td class='statusright'>". phpversion() ."</td>

                    </tr>
                    <tr>
                        <td class='statusleft'><strong>{lang.mysql_version}</strong></td>
                        <td class='statusright'>". $mysql_version ."</td>
                    </tr>
                </table>";

        $this->trellis->skin->add_sidebar_block( 'System Status', $system_status_html );

        #=============================
        # Do Output
        #=============================

        $this->output .= "<div id='ticketroll'>
                        <div class='acpwelcome'><img src='<! IMG_DIR !>/td_welcome_acphome.png' alt='Welcome to Trellis Desk 2' /></div>
                        ". $this->trellis->skin->group_title( 'About Trellis Desk' ) ."
                        <div class='option1' style='font-weight: normal; margin-bottom: 8px;'><strong>Help is on the way.</strong>  Trellis Desk is a powerful and robust help desk solution for your business. Improve your company's service by allowing your customers to quickly and easily submit support tickets to your team. Trellis Desk sports a range of advanced features that enable your business to handle customer support more efficiently. Plus, it's completely free &mdash; you'll never have to pay any license fees to use Trellis Desk.<br /><br />Show your appreciation and support by helping out your Lead Trellis Desk Developer, DJ, with a <a href='http://www.djtarazona.com/index.php?page=donate' target='_blank'><strong>donation</strong></a>.</div>
                        ". $this->trellis->skin->group_title( 'Credits' ) ."
                        <div class='option1' style='font-weight: normal;'>Thanks to everyone who has contributed to Trellis Desk.  We will have a full, up-to-date list of appropriate credits here soon.</div>
                        </div>";

        $this->trellis->skin->add_sidebar_general( 'Icon Usage', 'We tip our hats to Yusuke Kamiyamane (<a href="http://www.pinvoke.com" target="_blank">pinvoke.com</a>) for graciously giving away his Fugue iconset to the world. Also, much thanks to Mark James (<a href="http://www.famfamfam.com" target="_blank">famfamfam.com</a>) for sharing his Silk iconset. These icons help make Trellis Desk look pretty.' );

        $this->trellis->skin->add_output( $this->output );

        $this->trellis->skin->do_output();
    }

}

?>