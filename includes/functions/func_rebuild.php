<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_func_rebuild {

    #=======================================
    # @ Rebuild Settings Cache
    #=======================================

    public function settings_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => array( 'cf_key', 'cf_group', 'cf_value' ),
                                             'from'        => 'settings',
                                             'where'    => array( 'cf_cache', '=', 1 ),
                                      )      );

        $this->trellis->db->execute();

        while ( $cf = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $cf['cf_group'] ][ $cf['cf_key'] ] = $cf['cf_value'];
        }

        $this->trellis->cache->add( 'settings', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Departments Cache
    #=======================================

    public function departs_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => array( 'id', 'name', 'description', 'assign_auto', 'assign_move', 'escalate_enable', 'escalate_wait', 'escalate_depart', 'escalate_assign', 'close_auto', 'allow_attach', 'close_own', 'reopen_own', 'position' ),
                                             'from'        => 'departments',
                                             'order'    => array( 'position' => 'asc' ),
                                      )      );

        $this->trellis->db->execute();

        while ( $d = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $d['id'] ] = $d;
        }

        $this->trellis->cache->add( 'departs', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Categories Cache
    #=======================================

    public function categories_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => array( 'id', 'name', 'parent_id', 'allow_rating', 'allow_comments' ),
                                             'from'        => 'categories',
                                             'order'    => array( 'position' => 'asc' ),
                                      )      );

        $this->trellis->db->execute();

        while ( $c = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $c['id'] ] = $c;
        }

        $this->trellis->cache->add( 'categories', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Groups Cache
    #=======================================

    public function groups_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => 'all',
                                             'from'        => 'groups',
                                      )      );

        $this->trellis->db->execute();

        while ( $g = $this->trellis->db->fetch_row() )
        {
            if ( $g['g_id'] == 2 )
            {
                unset( $g['g_acp_acess'] );
                unset( $g['g_acp_perm'] );
                unset( $g['g_acp_depart_perm'] );
            }

            $to_cache[ $g['g_id'] ] = $g;
        }

        $this->trellis->cache->add( 'groups', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild News Cache
    #=======================================

    public function news_cache($limit=0)
    {
        $to_cache = array();

        if ( ! $limit ) $limit = $this->trellis->cache->data['settings']['news']['dashboard_amount'];

        $this->trellis->db->construct( array(
                                             'select'    => array( 'id', 'title', 'excerpt', 'date' ),
                                             'from'        => 'news',
                                             'order'    => array( 'date' => 'desc' ),
                                             'limit'    => array( 0, $limit ),
                                      )      );

        $this->trellis->db->execute();

        while ( $a = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $a['id'] ] = $a;
        }

        $this->trellis->cache->add( 'news', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Languages Cache
    #=======================================

    public function langs_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => 'all',
                                             'from'        => 'languages',
                                      )      );

        $this->trellis->db->execute();

        while ( $l = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $l['id'] ] = $l;

            if ( $l['default'] )
            {
                $this->trellis->cache->add( 'misc', array( 'default_lang' => $l['id'] ) );
            }
        }

        $this->trellis->cache->add( 'langs', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Skins Cache
    #=======================================

    public function skins_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => array( 'id', 'name', 'users', 'default' ),
                                             'from'        => 'skins',
                                      )      );

        $this->trellis->db->execute();

        while ( $s = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $s['id'] ] = $s;

            if ( $s['default'] )
            {
                $this->trellis->cache->add( 'misc', array( 'default_skin' => $s['id'] ) );
            }
        }

        $this->trellis->cache->add( 'skins', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Profile Fields Cache
    #=======================================

    public function pfields_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => 'all',
                                             'from'        => 'profile_fields',
                                      )      );

        $this->trellis->db->execute();

        while ( $f = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $f['id'] ] = $f;
        }

        $this->trellis->cache->add( 'pfields', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Deparmtnet Fields Cache
    #=======================================

    public function dfields_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => 'all',
                                             'from'        => 'depart_fields',
                                      )      );

        $this->trellis->db->execute();

        while ( $f = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $f['id'] ] = $f;
        }

        $this->trellis->cache->add( 'dfields', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Reply Templates Cache
    #=======================================

    public function rtemplates_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => array( 'id', 'name', 'description' ),
                                             'from'        => 'reply_templates',
                                             'order'    => array( 'position' => 'asc' ),
                                      )      );

        $this->trellis->db->execute();

        while ( $rt = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $rt['id'] ] = $rt;
        }

        $this->trellis->cache->add( 'rtemplates', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Priorities Cache
    #=======================================

    public function priorities_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => 'all',
                                             'from'        => 'priorities',
                                             'order'    => array( 'position' => 'asc' ),
                                      )      );

        $this->trellis->db->execute();

        while ( $p = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $p['id'] ] = $p;
        }

        $this->trellis->cache->add( 'priorities', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Flags Cache
    #=======================================

    public function flags_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => 'all',
                                             'from'        => 'flags',
                                             'order'    => array( 'position' => 'asc' ),
                                      )      );

        $this->trellis->db->execute();

        while ( $f = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $f['id'] ] = $f;
        }

        $this->trellis->cache->add( 'flags', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Staff Cache
    #=======================================

    public function staff_cache()
    {
        $to_cache = array();

        # TODO: Sub-groups

        $this->trellis->db->construct( array(
                                             'select'    => array( 'u' => array( 'id', 'name', 'ugroup', 'ugroup_sub', 'ugroup_sub_acp' ), 'g' => array( 'g_hide_names', 'g_acp_access', 'g_acp_depart_perm' ) ),
                                             'from'        => array( 'u' => 'users' ),
                                             'join'        => array( array( 'from' => array( 'g' => 'groups' ), 'where' => array( 'u' => 'ugroup', '=', 'g' => 'g_id' ) ) ),
                                             'where'    => array( array( array( 'g' => 'g_acp_access' ), '=', 1 ), array( array( 'u' => 'ugroup_sub_acp' ), '=', 1, 'or' ) ),
                                             'order'    => array( 'name' => array( 'u' => 'desc' ) ),
                                      )      );

        $this->trellis->db->execute();

        while ( $s = $this->trellis->db->fetch_row() )
        {
            // Sub-Groups
            if ( ! $s['g_acp_access'] )
            {
                $s['ugroup_sub'] = unserialize( $s['ugroup_sub'] );

                if ( $s['ugroup_sub_acp'] && is_array( $s['ugroup_sub'] ) && ! empty( $s['ugroup_sub'] ) )
                {
                    foreach ( $s['ugroup_sub'] as $g )
                    {
                        if ( $this->trellis->cache->data['groups'][ $g ]['g_acp_access'] ) $s['g_acp_access'] = 1;

                        break;
                    }
                }
            }

            if ( $s['g_acp_access'] ) $to_cache[ $s['id'] ] = $s;
        }

        $this->trellis->cache->add( 'staff', $to_cache, 1 );
    }

    #=======================================
    # @ Rebuild Statuses Cache
    #=======================================

    public function statuses_cache()
    {
        $to_cache = array();
        $to_cache_defaults = array();

        $this->trellis->db->construct( array(
                                             'select'    => 'all',
                                             'from'        => 'statuses',
                                             'order'    => array( 'position' => 'asc' ),
                                      )      );

        $this->trellis->db->execute();

        while ( $s = $this->trellis->db->fetch_row() )
        {
            $to_cache[ $s['id'] ] = $s;

            if ( $s['default'] ) $to_cache_defaults[ $s['type'] ] = $s['id'];
        }

        $this->trellis->cache->add( 'statuses', $to_cache, 1 );
        $this->trellis->cache->add( 'misc', array( 'default_statuses' => $to_cache_defaults ) );
    }

    #=======================================
    # @ Rebuild Miscellaneous Cache
    #=======================================

    public function misc_cache()
    {
        $to_cache = array();

        $this->trellis->db->construct( array(
                                             'select'    => array( 'id', 'type' ),
                                             'from'        => 'statuses',
                                             'where'    => array( 'default', '=', 1 ),
                                      )      );

        $this->trellis->db->execute();

        while ( $s = $this->trellis->db->fetch_row() )
        {
            $to_cache['default_statuses'][ $s['type'] ] = $s['id'];
        }

        $this->trellis->db->construct( array(
                                             'select'    => array( 'id' ),
                                             'from'        => 'languages',
                                             'where'    => array( 'default', '=', 1 ),
                                             'limit'    => array( 0, 1 ),
                                      )      );

        $this->trellis->db->execute();

        $l = $this->trellis->db->fetch_row();

        $to_cache['default_lang'] = $l['id'];

        $this->trellis->db->construct( array(
                                             'select'    => array( 'id' ),
                                             'from'        => 'skins',
                                             'where'    => array( 'default', '=', 1 ),
                                             'limit'    => array( 0, 1 ),
                                      )      );

        $this->trellis->db->execute();

        $s = $this->trellis->db->fetch_row();

        $to_cache['default_skin'] = $s['id'];

        $this->trellis->cache->add( 'misc', $to_cache, 1 );
    }

}

?>