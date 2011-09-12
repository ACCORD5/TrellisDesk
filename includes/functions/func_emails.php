<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2011 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_emails {

    public $error = '';

    #=======================================
    # @ Get Email Templates
    #=======================================

    public function &get($id)
    {
        if ( ! $this->trellis->cache->data['langs'][ $id ] ) return false;

        require TD_PATH .'languages/'. $this->trellis->cache->data['langs'][ $id ]['key'] .'/lang_email_content.php';

        return $lang;
    }

    #=======================================
    # @ Get Single Email Template
    #=======================================

    public function get_single($id, $key)
    {
        if ( ! $this->trellis->cache->data['langs'][ $id ] ) return false;

        require TD_PATH .'languages/'. $this->trellis->cache->data['langs'][ $id ]['key'] .'/lang_email_content.php';

        if ( ! isset( $lang[ $key ] ) ) return false;

        return array( 'subject' => $lang[ $key .'_sub' ], 'plaintext' => $lang[ $key ], 'html' => $lang[ $key .'_html' ] );
    }

    #=======================================
    # @ Edit
    #=======================================

    public function edit($id, $key, $subject, $html, $plaintext)
    {
        if ( ! $this->trellis->cache->data['langs'][ $id ] ) return false;

        require TD_PATH .'languages/'. $this->trellis->cache->data['langs'][ $id ]['key'] .'/lang_email_content.php';

        if ( ! isset( $lang[ $key ] ) ) return false;

        if ( $key != 'header' && $key != 'footer' ) $lang[ $key .'_sub' ] = $subject;

        $lang[ $key .'_html' ] = $html;
        $lang[ $key ] = $plaintext;

        if ( ! $handle = fopen( TD_PATH .'languages/'. $this->trellis->cache->data['langs'][ $id ]['key'] .'/lang_email_content.php', 'wb' ) ) return false;

        $file_start = "<?php\n\n/*\n#======================================================\n";
        $file_start .= "|    | Trellis Desk Language File\n";
        $file_start .= "|    | lang_email_content.php\n";
        $file_start .= "#======================================================\n*/\n\n";

        fwrite( $handle, $file_start );

        foreach( $lang as $k => $l )
        {
            if ( substr( $k, -4, 4) == '_sub' )
            {
                fwrite( $handle, "\$lang['". $k ."'] = \"". $lang[ $k ] ."\";\n\n" );
            }
            else
            {
                fwrite( $handle, "\$lang['". $k ."'] = <<<EOF\n". $lang[ $k ] ."\nEOF;\n\n" );
            }
        }

        fwrite( $handle, "?>" );

        @fclose( $handle );

        return true;
    }

}

?>