<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_drop_downs {

    #=======================================
    # @ Build Department Drop-Down
    #=======================================

    function dprt_drop($select="", $exclude=0, $admin=0, $type=1)
    {
        # DO SUB DEPARTMENTS!

        $html = ""; // Initialize for Security

        if ( $admin == 1 )
        {
            if ( $this->trellis->user['id'] != 1 )
            {
                $include = array();

                if ( is_array( $this->trellis->user['g_acp_depart_perm'] ) )
                {
                    foreach( $this->trellis->user['g_acp_depart_perm'] as $did => $dperm )
                    {
                        if ( $dperm['v'] ) $include[ $did ] = 1;
                    }
                }
            }
        }
        else
        {
            $include = unserialize( $this->trellis->user['g_m_depart_perm'] );
        }

        foreach( $this->trellis->cache->data['departs'] as $id => $d )
        {
            if ( $id != $exclude )
            {
                if ( $admin == 2 )
                {
                    $do = 1;
                }
                elseif ( $include[ $id ] )
                {
                    $do = 1;
                }
                elseif ( $this->trellis->user['id'] == 1 )
                {
                    $do = 1;
                }

                if ( $do )
                {
                    if ( $type == 2 )
                    {
                        $html .= "<tr><td width='1%'><input type='radio' name='department' id='d_". $id ."' value='". $id ."' class='radio' /></td><td width='99%'><label for='d_". $id ."'>". $d['name'] ."</label><br /><span class='descb'>". $d['description'] ."</span></td></tr>";
                    }
                    else
                    {
                        if ( is_array( $select ) )
                        {
                            if ( $select[ $id ] )
                            {
                                $html .= "<option value='". $id ."' selected='selected'>". $d['name'] ."</option>";
                            }
                            else
                            {
                                $html .= "<option value='". $id ."'>". $d['name'] ."</option>";
                            }
                        }
                        else
                        {
                            if ( $id == $select )
                            {
                                $html .= "<option value='". $id ."' selected='selected'>". $d['name'] ."</option>";
                            }
                            else
                            {
                                $html .= "<option value='". $id ."'>". $d['name'] ."</option>";
                            }
                        }
                    }
                }
            }

            $do = 0; // Reset
        }

        return $html;
    }

    #=======================================
    # @ Build Category Drop-Down
    #=======================================

    public function cat_drop( $params=array() )
    {
        $parents = array();
        $childs = array();

        foreach( $this->trellis->cache->data['categories'] as $id => $c )
        {
            ( $c['parent_id'] ) ? $childs[ $c['parent_id'] ][ $id ] = $c : $parents[ $id ] = $c;
        }

        $html = "";

        foreach( $parents as $id => $c )
        {
            if ( $id != $params['exclude'] )
            {
                if ( ! $params['no_perm'] && ! $this->trellis->user['g_kb_perm'][ $id ] && $this->trellis->user['id'] != 1 ) continue;

                if ( ( is_array( $params['select'] ) && in_array( $id, $params['select'] ) ) || $id == $params['select'] )
                {
                    $html .= "<option value='". $id ."' selected='yes'>". $c['name'] ."</option>";
                }
                else
                {
                    $html .= "<option value='". $id ."'>". $c['name'] ."</option>";
                }
            }

            if ( $childs[ $id ] && $params['childs'] )
            {
                foreach( $childs[ $id ] as $id => $c )
                {
                    if ( ! $params['no_perm'] && ! $this->trellis->user['g_kb_perm'][ $id ] && $this->trellis->user['id'] != 1 ) continue;

                    if ( $id != $params['exclude'] )
                    {
                        if ( ( is_array( $params['select'] ) && in_array( $id, $params['select'] ) ) || $id == $params['select'] )
                        {
                            $html .= "<option value='". $id ."' selected='yes'>-- ". $c['name'] ."</option>";
                        }
                        else
                        {
                            $html .= "<option value='". $id ."'>-- ". $c['name'] ."</option>";
                        }
                    }
                }
            }
        }

        return $html;
    }

    #=======================================
    # @ Build Staff Drop-Down
    #=======================================

    public function staff_drop( $params=array() )
    {
        $html = "";

        if ( $params['assign'] )
        {
            ( $this->trellis->check_perm( 'manage', 'tickets', 'assign_outside', 0 ) ) ? $check_perm = 0 : $check_perm = 1;
        }

        if ( is_array( $params['select'] ) ) $select_array = 1;

        foreach( $this->trellis->cache->data['staff'] as $id => $s )
        {
            if ( $params['type'] == 'fcbkc' )
            {
                if ( $select_array )
                {
                    if ( in_array( $id, $params['select'] ) )
                    {
                        $html .= "<option value='". $id ."' class='selected'>". $s['name'] ."</option>";
                    }
                }
                else
                {
                    if ( $id == $params['select'] )
                    {
                        $html .= "<option value='". $id ."' class='selected'>". $s['name'] ."</option>";
                    }
                }
            }
            else
            {
                if ( $id != $params['exclude'] )
                {
                    if ( $check_perm )
                    {
                        $s['g_acp_depart_perm'] = unserialize( $s['g_acp_depart_perm'] );

                        if ( ! $s['g_acp_depart_perm'][ $params['assign'] ]['v'] ) continue;
                    }

                    $html .= "<option value='". $id ."'";

                    if ( $select_array )
                    {
                        if ( in_array( $id, $params['select'] ) )
                        {
                            $html .= " selected='yes'";
                        }
                    }
                    else
                    {
                        if ( $id == $params['select'] )
                        {
                            $html .= " selected='yes'";
                        }
                    }

                    $html .= ">". $s['name'] ."</option>";
                }
            }
        }

        return $html;
    }

    #=======================================
    # @ Build Group Drop-Down ***
    #=======================================

    public function group_drop( $params=array() )
    {
        $html = "";
        $select_array = 0;
        $acp_groups = 0;

        if ( is_array( $params['select'] ) ) $select_array = 1;

        foreach( $this->trellis->cache->data['groups'] as $id => $g )
        {
            if ( $id != $params['exclude'] )
            {
                if ( $params['staff_check'] && $g['g_acp_access'] && ! $this->trellis->user['g_acp_perm']['manage_users_staff'] && $this->trellis->user['id'] != 1 ) continue;

                $html .= "<option value='". $id ."'";

                if ( $select_array )
                {
                    if ( in_array( $id, $params['select'] ) )
                    {
                        $html .= " selected='yes'";
                    }
                }
                else
                {
                    if ( $id == $params['select'] )
                    {
                        $html .= " selected='yes'";
                    }
                }

                $html .= ">". $g['g_name'] ."</option>";
            }
        }

        return $html;
    }

    #=======================================
    # @ Build Priority Drop-Down
    #=======================================

    public function priority_drop( $params=array() )
    {
        $html = "";

        foreach( $this->trellis->cache->data['priorities'] as $id => $p )
        {
            if ( $id != $params['exclude'] )
            {
                if ( $id == $params['select'] )
                {
                    $html .= "<option value='". $id ."' selected='yes'>". $p['name'] ."</option>";
                }
                else
                {
                    $html .= "<option value='". $id ."'>". $p['name'] ."</option>";
                }
            }
        }

        return $html;
    }

    #=======================================
    # @ Build Status Drop-Down
    #=======================================

    public function status_drop( $params=array() )
    {
        $html = "";

        foreach( $this->trellis->cache->data['statuses'] as $id => $s )
        {
            if ( isset( $params['type'] ) )
            {
                if ( is_array( $params['type'] ) )
                {
                    if ( ! in_array( $s['type'], $params['type'] ) ) continue;
                }
                elseif ( $params['type'] != $s['type'] )
                {
                    continue;
                }
            }

            if ( $id != $params['exclude'] )
            {
                if ( $id == $params['select'] )
                {
                    $html .= "<option value='". $id ."' selected='yes'>". $s['name_staff'] ."</option>";
                }
                else
                {
                    $html .= "<option value='". $id ."'>". $s['name_staff'] ."</option>";
                }
            }
        }

        return $html;
    }

    #=======================================
    # @ Build Time Zone Drop-Down
    #=======================================

    public function time_zone_drop($select=0)
    {
        return $this->basic_drop( array( 'options' => $this->get_time_zones(), 'selected' => $select ) );
    }

    #=======================================
    # @ Build Language Drop
    #=======================================

    function lang_drop($select=0, $exclude=0)
    {
        return $this->basic_drop( array( 'options' => $this->get_languages(), 'selected' => $select, 'exclude' => $exclude ) );
    }

    #=======================================
    # @ Build Skin Drop
    #=======================================

    function skin_drop($select=0, $exclude=0)
    {
        return $this->basic_drop( array( 'options' => $this->get_skins(), 'selected' => $select, 'exclude' => $exclude ) );
    }

    #=======================================
    # @ Build Basic Drop
    #=======================================

    function basic_drop($params=array())
    {
        $html = "";

        foreach ( $params['options'] as $key => $value )
        {
            if ( isset($params['exclude']) && $params['exclude'] == $key ) continue;

            $html .= '<option value="'. $key .'"';

            if ( $params['selected'] == $key ) $html .= ' selected="yes"';

            $html .= '>'. $value .'</option>';
        }

        return $html;
    }

    #=======================================
    # @ Get Languages
    #=======================================

    function get_languages()
    {
        $langs = array();

        foreach( $this->trellis->cache->data['langs'] as $id => $l )
        {
            $langs[ $id ] = $l['name'];
        }

        return $langs;
    }

    #=======================================
    # @ Get Skins
    #=======================================

    function get_skins()
    {
        $skins = array();

        foreach( $this->trellis->cache->data['skins'] as $id => $s )
        {
            $skins[ $id ] = $s['name'];
        }

        return $skins;
    }

    #=======================================
    # @ Get Time Zones
    #=======================================

    function get_time_zones()
    {
        return array(
                     '-12'    => $this->trellis->lang['gmt_n_1200'],
                     '-11'    => $this->trellis->lang['gmt_n_1100'],
                     '-10'    => $this->trellis->lang['gmt_n_1000'],
                     '-9'    => $this->trellis->lang['gmt_n_900'],
                     '-8'    => $this->trellis->lang['gmt_n_800'],
                     '-7'    => $this->trellis->lang['gmt_n_700'],
                     '-6'    => $this->trellis->lang['gmt_n_600'],
                     '-5'    => $this->trellis->lang['gmt_n_500'],
                     '-4'    => $this->trellis->lang['gmt_n_400'],
                     '-3.5'    => $this->trellis->lang['gmt_n_350'],
                     '-3'    => $this->trellis->lang['gmt_n_300'],
                     '-2'    => $this->trellis->lang['gmt_n_200'],
                     '-1'    => $this->trellis->lang['gmt_n_100'],
                     '0'    => $this->trellis->lang['gmt'],
                     '1'    => $this->trellis->lang['gmt_p_100'],
                     '2'    => $this->trellis->lang['gmt_p_200'],
                     '3'    => $this->trellis->lang['gmt_p_300'],
                     '3.5'    => $this->trellis->lang['gmt_p_350'],
                     '4'    => $this->trellis->lang['gmt_p_400'],
                     '4.5'    => $this->trellis->lang['gmt_p_450'],
                     '5'    => $this->trellis->lang['gmt_p_500'],
                     '5.5'    => $this->trellis->lang['gmt_p_550'],
                     '6'    => $this->trellis->lang['gmt_p_600'],
                     '7'    => $this->trellis->lang['gmt_p_700'],
                     '8'    => $this->trellis->lang['gmt_p_800'],
                     '9'    => $this->trellis->lang['gmt_p_900'],
                     '9.5'    => $this->trellis->lang['gmt_p_950'],
                     '10'    => $this->trellis->lang['gmt_p_1000'],
                     '11'    => $this->trellis->lang['gmt_p_1100'],
                     '12'    => $this->trellis->lang['gmt_p_1200'],
                     );
    }

}

?>