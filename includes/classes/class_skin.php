<?php

/**
 * Trellis Desk
 *
 * @copyright  Copyright (C) 2009-2012 ACCORD5. All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */

class td_class_skin {

    public $data;
    protected $smarty;

    #=======================================
    # @ Constructor
    #=======================================

    function __construct(&$trellis)
    {
        $this->trellis = $trellis;

        #=============================
        # Send Charset Header
        #=============================

        header('Content-Type: text/html; charset=utf-8');

        #=============================
        # Load Smarty
        #=============================

        require_once TD_INC .'smarty/Smarty.class.php';

        $this->smarty = new Smarty();

        #=============================
        # Do We Have A Skin ID?
        #=============================

        if ( ! $this->trellis->user['skin'] )
        {
            if ( ! $this->trellis->user['skin'] = $this->trellis->cache->data['misc']['default_skin'] )
            {
                $this->trellis->user['skin'] = $this->trellis->config['fallback_skin'];
            }
        }

        #=============================
        # Load Functions
        #=============================

        $this->smarty->register_function( 'lv_field', array( 'td_class_skin', 'lv_add_field' ) );
        $this->smarty->register_function( 'lv_rule', array( 'td_class_skin', 'lv_add_rule' ) );
        $this->smarty->register_function( 'focus', array( 'td_class_skin', 'focus_js' ) );
        $this->smarty->register_function( 'scroll', array( 'td_class_skin', 'scroll_to_js' ) );

        #=============================
        # Set Paths
        #=============================

        $this->smarty->template_dir = TD_SKIN .'s'. $this->trellis->user['skin'] .'/templates/';
        $this->smarty->compile_dir  = $this->trellis->config['skin_compile_path'];

        #=============================
        # Load Skin Data
        #=============================

        $this->data = &$this->trellis->cache->data['skins'][ $this->trellis->user['skin'] ];

        #=============================
        # Load Globals
        #=============================

        $this->trellis->load_lang('global');
    }

    #=======================================
    # @ Set Template Variable
    #=======================================

    function set_var($key, $value)
    {
        $this->smarty->assign( $key, $value );
    }

    #=======================================
    # @ Register Template Function
    #=======================================

    function register_function($tag, $function)
    {
        $this->smarty->register_function( $tag, $function );
    }

    #=======================================
    # @ Display
    #=======================================

    function display($template)
    {
        $this->smarty->display( $template );
    }

    #=======================================
    # @ Do Output
    #=======================================

    function do_output($extra='', $type='')
    {
        #=============================
        # Initialize
        #=============================

        $footer = "";
        $nav_links = "";

        if ( $type == 'redirect' )
        {
            $this->trellis->load_lang('redirect');

            $extra['redirect_url'] = $this->trellis->config['hd_url'] .'/index.php'. $extra['redirect_url'];

            $this->set_var( 'redirect_url', str_replace( "&", '&amp;', $extra['redirect_url'] ) );
            $this->set_var( 'redirect_msg', $this->trellis->lang[ $extra['redirect_msg'] ] );

            $this->set_var( 'title', $this->trellis->cache->data['settings']['general']['hd_name'] .' :: '. $this->trellis->lang['please_wait'] );

            $nav_tree = ' &rsaquo; '. $this->trellis->lang['redirect'];
        }
        elseif ( $type == 'error' )
        {
            $this->trellis->load_lang('error');

            $this->set_var( 'error_msg', $this->trellis->lang[ $extra['error_msg'] ] );

            if ( $extra['error_login'] ) $this->set_var( 'token_e_login', $this->trellis->create_token('login') );

            $this->set_var( 'title', $this->trellis->cache->data['settings']['general']['hd_name'] .' :: '. $this->trellis->lang['error'] );

            $nav_tree = ' &rsaquo; '. $this->trellis->lang['error'];
        }
        else
        {
            #=============================
            # Something Extra 4 ME? :O
            #=============================

            if ( is_array( $extra ) )
            {
                if ( isset( $extra['title'] ) )
                {
                    $title = $extra['title'];
                }

                if ( isset( $extra['footer'] ) )
                {
                    $footer = $extra['footer'];
                }

                if ( isset( $extra['nav'] ) )
                {
                    $nav_links = $extra['nav'];
                }
            }

            #=============================
            # Do We Have A Title?
            #=============================

            if ( ! isset( $title ) )
            {
                $title = $this->trellis->cache->data['settings']['general']['hd_name'];
            }
            else
            {
                $title = $this->trellis->cache->data['settings']['general']['hd_name'] .' :: '. $title;
            }

            $this->set_var( 'title', $title );

            #=============================
            # Navigation
            #=============================

            $nav_tree = ""; // Initialize for Security

            if ( is_array( $nav_links ) )
            {
                while ( list( , $nlink ) = each( $nav_links ) )
                {
                    $nav_tree .= ' &rsaquo; '. $nlink;
                }
            }
        }

        if ( $type != 'redirect' )
        {
            #=============================
            # Sidebar
            #=============================

            if ( $type != 'error' && $this->trellis->input['page'] == 'kb' && ! $this->trellis->input['act'] && $this->trellis->cache->data['settings']['kb']['enable'] && $this->trellis->user['g_kb_access'] )
            {
                if ( $this->trellis->cache->data['settings']['kb']['sidebar_recent'] ) $this->set_var( 'articles_recent', $this->get_recent_articles( $this->trellis->cache->data['settings']['kb']['sidebar_recent_count'] ) );
                if ( $this->trellis->cache->data['settings']['kb']['sidebar_views'] ) $this->set_var( 'articles_most_viewed', $this->get_most_viewed_articles( $this->trellis->cache->data['settings']['kb']['sidebar_views_count'] ) );
                if ( $this->trellis->cache->data['settings']['kb']['sidebar_rating'] ) $this->set_var( 'articles_top_rated', $this->get_top_rated_articles( $this->trellis->cache->data['settings']['kb']['sidebar_rating_count'] ) );
            }

        }

        $this->set_var( 'nav_links', $nav_tree );

        /**********************************************************************/
        /* REMOVAL OF THE COPYRIGHT WITHOUT PURCHASING COPYRIGHT REMOVAL WILL */
        /* VIOLATE THE LICENSE YOU AGREED TO WHEN DOWNLOADING AND REGISTERING */
        /* THIS PORDUCT.  IF THIS HAPPENS, IT COULD RESULT IN REMOVAL OF THIS */
        /* SYSTEM AND POSSIBLY CRIMINAL CHARGES.  THANK YOU FOR UNDERSTANDING */
        /***********************************************************************/

        $query_count = $this->trellis->db->get_query_count();
        $query_s_count = $this->trellis->db->get_query_s_count();
        $exe_time = $this->trellis->end_timer();

        $copyright = "<div id='copyright'>Powered By <a href='http://www.accord5.com/products/trellis/'>Trellis Desk</a> {$this->trellis->version_name} &copy; ". date('Y') ." <a href='http://www.accord5.com/'>ACCORD5</a><br /><span title='". $query_count ." Normal | ". $query_s_count ." Shutdown'>". $query_count ." Queries</span> // ". $exe_time ." Seconds</div>";

        #=============================
        # Global Variables
        #=============================

        $this->set_var( 'css_url', $this->trellis->config['hd_url'] .'/skins/s'. $this->trellis->user['skin'] .'/css' );
        $this->set_var( 'img_url', $this->trellis->config['hd_url'] .'/skins/s'. $this->trellis->user['skin'] .'/images' );
        $this->set_var( 'js_url', $this->trellis->config['hd_url'] .'/skins/s'. $this->trellis->user['skin'] .'/scripts' );
        $this->set_var( 'tpl_url', $this->trellis->config['hd_url'] .'/skins/s'. $this->trellis->user['skin'] .'/templates' );
        $this->set_var( 'td_url', $this->trellis->config['hd_url'] );
        $this->set_var( 'td_name', $this->trellis->cache->data['settings']['general']['hd_name'] );
        $this->set_var( 'copyright', $copyright );
        $this->set_var( 'extra_l', $this->trellis->input['extra_l'] );

        $this->set_var( 'user', &$this->trellis->user );
        $this->set_var( 'cache', array( 'settings' => &$this->trellis->cache->data['settings'] ) ); # TODO: let's try to all cache references in templates for speed
        $this->set_var( 'input', &$this->trellis->input );

        $this->set_var( 'lang', &$this->trellis->lang );

        $self = $this->trellis->config['hd_url'] .'/index.php';

        if ( $this->trellis->input['act'] != 'logout' && $_SERVER['QUERY_STRING'] ) $self .= '?'. $this->trellis->sanitize_data( $_SERVER['QUERY_STRING'] );

        $this->set_var( 'self', &$self );

        $this->set_var( 'enable_disable_radio', array( 1 => $this->trellis->lang['enabled'], 0 => $this->trellis->lang['disabled'] ) );
        $this->set_var( 'yes_no_radio', array( 1 => $this->trellis->lang['yes'], 0 => $this->trellis->lang['no'] ) );

        #=============================
        # Output
        #=============================

        if ( $type == 'print' )
        {
            $this->display( 'print.tpl' );
        }
        elseif ( $type == 'redirect' )
        {
            $this->set_var( 'wrapper_type', 2 );

            $this->display( 'wrapper.tpl' );
        }
        elseif ( $type == 'error' )
        {
            if ( $extra['error_login'] )
            {
                $this->set_var( 'wrapper_type', 4 );
            }
            else
            {
                $this->set_var( 'wrapper_type', 3 );
            }

            $this->display( 'wrapper.tpl' );
        }
        else
        {
            $this->set_var( 'wrapper_type', 1 );

            $this->display( 'wrapper.tpl' );
        }

        if ( $type == 'redirect' ) header('Refresh: '. $extra['redirect_seconds'] .'; URL='. $extra['redirect_url']);

        if ( TD_DEBUG )
        {
            echo "<br /><br />------------------<br /><br />". $this->trellis->db->queries_ran;
        }

        ob_flush();
        flush();

        $this->trellis->shut_down();

        exit();
    }

    #=======================================
    # @ Do Print
    #=======================================

    function do_print($extra="")
    {
        $this->do_output( $extra, 'print' );
    }

    #=======================================
    # Redirect
    #=======================================

    function redirect($url, $msg, $seconds=3)
    {
        $this->do_output( array( 'redirect_url' => $url, 'redirect_msg' => $msg, 'redirect_seconds' => $seconds ), 'redirect' );
    }

    #=======================================
    # Error
    #=======================================

    function error($msg, $login=0)
    {
        $this->do_output( array( 'error_msg' => $msg, 'error_login' => $login ), 'error' );
    }

    #=======================================
    # @ Live Validation Add Field
    #=======================================

    public static function lv_add_field($params, &$smarty)
    {
        return "var F{$params['name']} = new LiveValidation( '{$params['name']}', { validMessage: ' ' } );\n";
    }

    #=======================================
    # @ Live Validation Add Rule
    #=======================================

    public static function lv_add_rule($params, &$smarty)
    {
        if ( $params['type'] == 'presence' )
        {
            return td_class_skin::lv_presence( $params );
        }
        elseif ( $params['type'] == 'format' )
        {
            return td_class_skin::lv_format( $params );
        }
        elseif ( $params['type'] == 'number' )
        {
            return td_class_skin::lv_number( $params );
        }
        elseif ( $params['type'] == 'email' )
        {
            return td_class_skin::lv_email( $params );
        }
        elseif ( $params['type'] == 'match' )
        {
            return td_class_skin::lv_match( $params );
        }
        elseif ( $params['type'] == 'custom' )
        {
            return td_class_skin::lv_custom( $params );
        }
    }

    #=======================================
    # @ Live Validation Presence
    #=======================================

    protected static function lv_presence($raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )
        {
            $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        }
        else
        {
            $params .= "failureMessage: ' ', ";
        }

        return "F{$raw_params['name']}.add( Validate.Presence, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Format
    #=======================================

    protected static function lv_format($raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )     $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        if ( $raw_params['pattern'] )     $params .= "pattern: {$raw_params['pattern']}, ";
        if ( $raw_params['negate'] )     $params .= "negate: true, ";

        if ( ! $raw_params['fail_msg'] ) $params .= "failureMessage: ' ', ";

        return "F{$raw_params['name']}.add( Validate.Format, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Number
    #=======================================

    protected static function lv_number($raw_params)
    {
        $params = "";

        if ( $raw_params['not_num_msg'] )     $params .= "notANumberMessage: '{$raw_params['not_num_msg']}', ";
        if ( $raw_params['not_int_msg'] )     $params .= "notAnIntegerMessage: '{$raw_params['not_int_msg']}', ";
        if ( $raw_params['wrong_num_msg'] ) $params .= "wrongNumberMessage: '{$raw_params['wrong_num_msg']}', ";
        if ( $raw_params['too_low_msg'] )     $params .= "tooLowMessage: '{$raw_params['too_low_msg']}', ";
        if ( $raw_params['too_high_msg'] )     $params .= "tooHighMessage: '{$raw_params['too_high_msg']}', ";
        if ( $raw_params['is'] )             $params .= "is: '{$raw_params['is']}', ";
        if ( $raw_params['minimum'] )         $params .= "minimum: '{$raw_params['not_num_msg']}', ";
        if ( $raw_params['maximum'] )         $params .= "maximum: '{$raw_params['maximum']}', ";
        if ( $raw_params['int_only'] )         $params .= "onlyInteger: true, ";

        if ( ! $raw_params['not_num_msg'] ) $params .= "notANumberMessage: ' ', ";

        return "F{$raw_params['name']}.add( Validate.Numericality, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Email
    #=======================================

    protected static function lv_email($raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )
        {
            $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        }
        else
        {
            $params .= "failureMessage: ' ', ";
        }

        return "F{$raw_params['name']}.add( Validate.Email, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Match
    #=======================================

    protected static function lv_match($raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )     $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        if ( $raw_params['against'] )     $params .= 'against: function(value,args) { return value == $("#'. $raw_params['against'] .'").val(); }, ';
        if ( $raw_params['args'] )         $params .= "args: {$raw_params['wrong_num_msg']}, ";

        if ( ! $raw_params['fail_msg'] ) $params .= "failureMessage: ' ', ";

        return "F{$raw_params['name']}.add( Validate.Custom, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Live Validation Custom
    #=======================================

    protected static function lv_custom($raw_params)
    {
        $params = "";

        if ( $raw_params['fail_msg'] )     $params .= "failureMessage: '{$raw_params['fail_msg']}', ";
        if ( $raw_params['against'] )     $params .= "against: function(value,args) { {$raw_params['against']} }, ";
        if ( $raw_params['args'] )         $params .= "args: {$raw_params['wrong_num_msg']}, ";

        if ( ! $raw_params['fail_msg'] ) $params .= "failureMessage: ' ', ";

        return "F{$raw_params['name']}.add( Validate.Custom, { ". substr( $params, 0, -2 ) ." } );\n";
    }

    #=======================================
    # @ Focus JavaScript
    #=======================================

    public static function focus_js($params, $smarty)
    {
        return '$("#'. $params['name'] .'").focus();';
    }

    #=======================================
    # @ Scroll To JavaScript
    #=======================================

    public static function scroll_to_js($params, $smarty)
    {
        return '<script type="text/javascript">$(function(){$.scrollTo($(\'#'. $params['element'] .'\'), {duration:1000});});</script>';
    }

    #=======================================
    # @ Upload Form
    #=======================================

    public function upload_form($input, $data=array(), $params=array())
    {
        $html = "<script language='javascript' type='text/javascript'>
                // <![CDATA[
                $(function() {
                $('#". $input ."').uploadify({
                'uploader'  : 'includes/uploadify/uploadify.swf',
                'script'    : 'index.php',
                'cancelImg' : '{$this->trellis->config['hd_url']}/skins/s{$this->trellis->user['skin']}/images/icons/cross.png',
                'scriptData': {
                    'session_id': '". $this->trellis->user['s_id'] ."'";

        if ( ! empty( $data ) && is_array( $data ) )
        {
            $query_data = '';

            foreach( $data as $field => $value )
            {
                $query_data .= ",\n\t\t\t\t\t'". $field ."': '". $value ."'";
            }

            $html .= $query_data;
        }

        $fileDesc = 'Files ('. implode( ', ', ( $extensions = array_map( 'trim', explode( ',', $this->trellis->cache->data['settings']['general']['upload_exts'] ) ) ) ) .')';
        $fileExt = implode( ';', array_map( create_function( '$a', 'return \'*.\'. $a;' ), $extensions ) );

        $multi = ( $params['multi'] ) ? 'true' : 'false';

        $html .= "
                },
                'auto': true,
                'fileDesc': '{$fileDesc}',
                'fileExt': '{$fileExt}',
                'simUploadLimit': 1,
                ";

        if ( $this->trellis->user['g_upload_size_max'] )
        {
            $html .= "'sizeLimit': {$this->trellis->user['g_upload_size_max']},";
        }

        $html .= "
                'multi': {$multi},
                'hideButton': true,
                'width': '70px',
                'height': '28px',
                'wmode': 'transparent',
                'onCancel': function(event,queueID,fileObj,data) {
                    uploadUpdate('remove');
                },
                'onComplete': function(event,queueID,fileObj,response,data) {
                    uploadComplete(event,queueID,fileObj,response,data);
                },
                'onError': function(event,queueID,fileObj,errorObj) {
                    uploadError(event,queueID,fileObj,errorObj);
                },
                'onSelect': function(event,queueID,fileObj) {
                    uploadUpdate('add');
                }
                });

                var simpleUpload = new AjaxUpload('#simple_upload_file', {
                    action: 'admin.php',
                    name: 'Filedata',
                    data: {";

        if ( $query_data )
        {
            $html .= substr( $query_data, 1 );
        }

        $html .= "
                    },
                    autoSubmit: false,
                    onChange: function(file, ext) {
                        $('#simple_upload_file .ui-button-text').text(file);
                    },
                    onSubmit: function(file, ext) {
                        $('#simple_upload').val('{lang.button_uploading}');
                        $('#simple_upload').attr('disabled', true);
                    },
                    onComplete: function(file, response) {
                        uploadComplete(null, null, null, response, null);
                        $('#simple_upload_file .ui-button-text').text('{lang.browse}');
                        $('#simple_upload').val('{lang.button_upload}');
                        $('#simple_upload').removeAttr('disabled');
                    }
                });

                $('#simple_upload').click(function() {
                    simpleUpload.submit();
                });

                $('#upload_switch_simple').click(function() {
                    $('#upload_flash').hide();
                    $('#upload_simple').show();
                });

                $('#upload_switch_flash').click(function() {
                    $('#upload_simple').hide();
                    $('#upload_flash').show();
                });
                });

                function uploadComplete(event, queueID, fileObj, response, data) {
                    jsonResponse = convertFromJson(response);
                    if (jsonResponse.success) {
                        $('#upload_msg').text(jsonResponse.successmsg);
                        $('#upload_list').append(\"<li id='uf_\"+jsonResponse.id+\"'><input type='hidden' name='fuploads[]' value='\"+jsonResponse.id+\"' />\"+jsonResponse.name+\"<span class='uploaddel' onclick='uploadDelete(\"+jsonResponse.id+\")'></span></li>\");
                        $('#upload_list').show();
                    }
                    else {
                        if (jsonResponse.error) {
                            $('#upload_msg').text(jsonResponse.errormsg);
                        }
                        else {
                            $('#upload_msg').text('unknown error');
                        }
                    }
                    $('#upload_msg').stop().show('blind');
                    $('#upload_msg').animate({opacity: 1.0}, 5000);
                    $('#upload_msg').hide('blind');
                    uploadUpdate();
                    return false;
                }

                function uploadError(event, queueID, fileObj, errorObj) {
                    uploadUpdate();
                }

                function uploadUpdate(adjust) {
                    if ( $('.uploadifyQueueItem').size() > 1 ) {
                        var newheight = $('#upload_fileQueue').outerHeight();
                        if (adjust == 'add') {
                            newheight += $('.uploadifyQueueItem').outerHeight();
                        } else if (adjust == 'remove') {
                            newheight -= $('.uploadifyQueueItem').outerHeight();
                        }
                        $('#upload_fileQueue').parent().parent().height( newheight );
                    }
                    return true;
                }

                function uploadDelete(fid) {
                    $.getJSON('<! TD_URL !>/admin.php?page={$this->trellis->input['page']}&act=dodelupload&id='+fid, function(jsonResponse) {
                        if (jsonResponse.success) {
                            $('#uf_'+fid).remove();
                        }
                        else {
                            if (jsonResponse.error) {
                                $('#upload_msg').text('{$this->trellis->lang['error_upload_delete']}');
                            }
                            else {
                                $('#upload_msg').text('unknown error');
                            }
                            $('#upload_msg').stop().show('blind');
                            $('#upload_msg').animate({opacity: 1.0}, 5000);
                            $('#upload_msg').hide('blind');
                        }
                    });
                }
                // ]]>
                </script>
                <div id='upload_flash' style='position: relative;'>
                    <span class='button'>{lang.browse}</span>
                    <div style='position:absolute; top: 0;'>
                        <input id='upload_file' name='upload_file' type='file' />
                    </div>
                    <div style='float: right;'><span id='upload_switch_simple' class='button'>{lang.upload_switch_simple}</span></div>
                </div>
                <div id='upload_simple' style='display: none;'>
                    <span id='simple_upload_file' class='button'>{lang.browse}</span> <input type='button' id='simple_upload' name='simple_upload' value='{lang.button_upload}' class='button' />
                    <div style='float: right;'><span id='upload_switch_flash' class='button'>{lang.upload_switch_flash}</span></div>
                </div>
                <div id='upload_msg' style='display:none;margin-top:12px'>&nbsp;</div>
                <ul id='upload_list'></ul>";

        return $html;
    }

    #=======================================
    # @ Get Recent Articles
    #=======================================

    protected function get_recent_articles($count)
    {
        $perms = array();

        foreach ( $this->trellis->user['g_kb_perm'] as $cid => $p )
        {
            if ( $p ) $perms[] = $cid;
        }

        if ( empty( $perms ) ) return false;

        return $this->trellis->db->get( array(
                                              'select'    => array( 'id', 'title', 'description' ),
                                              'from'    => 'articles',
                                              'where'    => array( 'cid', 'in', $perms ),
                                              'order'    => array( 'date' => 'desc' ),
                                              'limit'    => array( 0, $count ),
                                       ), 'id' );
    }

    #=======================================
    # @ Get Most Vieed Articles
    #=======================================

    protected function get_most_viewed_articles($count)
    {
        $perms = array();

        foreach ( $this->trellis->user['g_kb_perm'] as $cid => $p )
        {
            if ( $p ) $perms[] = $cid;
        }

        if ( empty( $perms ) ) return false;

        return $this->trellis->db->get( array(
                                              'select'    => array( 'id', 'title', 'description' ),
                                              'from'    => 'articles',
                                              'where'    => array( 'cid', 'in', $perms ),
                                              'order'    => array( 'views' => 'desc' ),
                                              'limit'    => array( 0, $count ),
                                       ), 'id' );
    }

    #=======================================
    # @ Get Top Rated Articles
    #=======================================

    protected function get_top_rated_articles($count)
    {
        $perms = array();

        foreach ( $this->trellis->user['g_kb_perm'] as $cid => $p )
        {
            if ( $p ) $perms[] = $cid;
        }

        if ( empty( $perms ) ) return false;

        return $this->trellis->db->get( array(
                                              'select'    => array( 'id', 'title', 'description' ),
                                              'from'    => 'articles',
                                              'where'    => array( 'cid', 'in', $perms ),
                                              'order'    => array( 'rating_average' => 'desc' ),
                                              'limit'    => array( 0, $count ),
                                       ), 'id' );
    }
}

?>