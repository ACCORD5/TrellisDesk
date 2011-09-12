<?php

/**
* Project:     Smarty: the PHP compiling template engine
* File:        Smarty.class.php
* SVN:         $Id: Smarty.class.php 3420 2009-12-29 20:12:11Z Uwe.Tews $
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
* 
* You should have received a copy of the GNU Lesser General Public
* License along with this library; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
* 
* For questions, help, comments, discussion, etc., please join the
* Smarty mailing list. Send a blank e-mail to
* smarty-discussion-subscribe@googlegroups.com
* 
* @link http://www.smarty.net/
* @copyright 2008 New Digital Group, Inc.
* @author Monte Ohrt <monte at ohrt dot com> 
* @author Uwe Tews 
* @package Smarty
* @version 3-SVN$Rev: 3286 $
*/

/**
* define shorthand directory separator constant
*/
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
} 

/**
* set SMARTY_DIR to absolute path to Smarty library files.
* Sets SMARTY_DIR only if user application has not already defined it.
*/
if (!defined('SMARTY_DIR')) {
    define('SMARTY_DIR', dirname(__FILE__) . DS);
} 

/**
* set SMARTY_SYSPLUGINS_DIR to absolute path to Smarty internal plugins.
* Sets SMARTY_SYSPLUGINS_DIR only if user application has not already defined it.
*/
if (!defined('SMARTY_SYSPLUGINS_DIR')) {
    define('SMARTY_SYSPLUGINS_DIR', SMARTY_DIR . 'sysplugins' . DS);
} 
if (!defined('SMARTY_PLUGINS_DIR')) {
    define('SMARTY_PLUGINS_DIR', SMARTY_DIR . 'plugins' . DS);
} 
if (!defined('SMARTY_RESOURCE_CHAR_SET')) {
    define('SMARTY_RESOURCE_CHAR_SET', 'UTF-8');
} 
if (!defined('SMARTY_RESOURCE_DATE_FORMAT')) {
    define('SMARTY_RESOURCE_DATE_FORMAT', '%b %e, %Y');
} 

/**
* define variable scopes
*/
define('SMARTY_LOCAL_SCOPE', 0);
define('SMARTY_PARENT_SCOPE', 1);
define('SMARTY_ROOT_SCOPE', 2);
define('SMARTY_GLOBAL_SCOPE', 3);

/**
* define caching modes
*/
define('SMARTY_CACHING_OFF', 0);
define('SMARTY_CACHING_LIFETIME_CURRENT', 1);
define('SMARTY_CACHING_LIFETIME_SAVED', 2);

/**
* This determines how Smarty handles "<?php ... ?>" tags in templates.
* possible values:
*/
define('SMARTY_PHP_PASSTHRU', 0); //-> print tags as plain text
define('SMARTY_PHP_QUOTE', 1); //-> escape tags as entities
define('SMARTY_PHP_REMOVE', 2); //-> escape tags as entities
define('SMARTY_PHP_ALLOW', 3); //-> escape tags as entities

/**
* register the class autoloader
*/
if (!defined('SMARTY_SPL_AUTOLOAD')) {
    define('SMARTY_SPL_AUTOLOAD', 0);
} 

if (SMARTY_SPL_AUTOLOAD && set_include_path(get_include_path() . PATH_SEPARATOR . SMARTY_SYSPLUGINS_DIR) !== false) {
    $registeredAutoLoadFunctions = spl_autoload_functions();
    if (!isset($registeredAutoLoadFunctions['spl_autoload'])) {
        spl_autoload_register();
    } 
} else {
    spl_autoload_register('smartyAutoload');
} 

/**
* This is the main Smarty class
*/
class Smarty extends Smarty_Internal_Data {
    // smarty version
    const SMARTY_VERSION = 'Smarty3-b7'; 
    // auto literal on delimiters with whitspace
    public $auto_literal = true; 
    // display error on not assigned variables
    public $error_unassigned = false; 
    // template directory
    public $template_dir = null; 
    // default template handler
    public $default_template_handler_func = null; 
    // compile directory
    public $compile_dir = null; 
    // plugins directory
    public $plugins_dir = null; 
    // cache directory
    public $cache_dir = null; 
    // config directory
    public $config_dir = null; 
    // force template compiling?
    public $force_compile = false; 
    // check template for modifications?
    public $compile_check = true; 
    // use sub dirs for compiled/cached files?
    public $use_sub_dirs = false; 
    // compile_error?
    public $compile_error = false; 
    // caching enabled
    public $caching = false; 
    // merge compiled includea
    public $merge_compiled_includes = false; 
    // cache lifetime
    public $cache_lifetime = 3600; 
    // force cache file creation
    public $force_cache = false; 
    // cache_id
    public $cache_id = null; 
    // compile_id
    public $compile_id = null; 
    // template delimiters
    public $left_delimiter = "{";
    public $right_delimiter = "}"; 
    // security
    public $security_class = 'Smarty_Security';
    public $php_handling = SMARTY_PHP_PASSTHRU;
    public $allow_php_tag = false;
    public $allow_php_templates = false;
    public $security = false;
    public $security_policy = null;
    public $security_handler = null;
    public $direct_access_security = true; 
    // debug mode
    public $debugging = false;
    public $debugging_ctrl = 'URL';
    public $smarty_debug_id = 'SMARTY_DEBUG';
    public $debug_tpl = null; 
    // When set, smarty does uses this value as error_reporting-level.
    public $error_reporting = null; 
    // config var settings
    public $config_overwrite = true; //Controls whether variables with the same name overwrite each other.
    public $config_booleanize = true; //Controls whether config values of on/true/yes and off/false/no get converted to boolean
    public $config_read_hidden = true; //Controls whether hidden config sections/vars are read from the file.                                              
    // config vars
    public $config_vars = array(); 
    // assigned tpl vars
    public $tpl_vars = array(); 
    // assigned global tpl vars
    public $global_tpl_vars = array(); 
    // dummy parent object
    public $parent = null; 
    // global template functions
    public $template_functions = array(); 
    // resource type used if none given
    public $default_resource_type = 'file'; 
    // caching type
    public $caching_type = 'file'; 
    // internal cache resource types
    public $cache_resource_types = array('file'); 
    // config type
    public $default_config_type = 'file'; 
    // exception handler: array('ExceptionClass','ExceptionMethod');
    public $exception_handler = null; 
    // cached template objects
    public $template_objects = null; 
    // check If-Modified-Since headers
    public $cache_modified_check = false; 
    // registered plugins
    public $registered_plugins = array(); 
    // plugin search order
    public $plugin_search_order = array('function', 'block', 'compiler', 'class'); 
    // registered objects
    public $registered_objects = array(); 
    // registered filters
    public $registered_filters = array(); 
    // autoload filter
    public $autoload_filters = array(); 
    // status of filter on variable output
    public $variable_filter = true; 
    // global internal smarty  vars
    public $_smarty_vars = array(); 
    // start time for execution time calculation
    public $start_time = 0; 
    // default file permissions
    public $_file_perms = 0644; 
    // default dir permissions
    public $_dir_perms = 0771; 
    // smarty object reference
    public $smarty = null;

    /**
    * Class constructor, initializes basic smarty properties
    */
    public function __construct() {
        // self reference needed by other classes methods
        $this->smarty = $this;

        if (is_callable('mb_internal_encoding')) {
            mb_internal_encoding(SMARTY_RESOURCE_CHAR_SET);
        } 
        $this->start_time = $this->_get_time(); 
        // set exception handler
        if (!empty($this->exception_handler))
        set_exception_handler($this->exception_handler); 
        // set default dirs
        $this->template_dir = array('.' . DS . 'templates' . DS);
        $this->compile_dir = '.' . DS . 'templates_c' . DS;
        $this->plugins_dir = array(SMARTY_PLUGINS_DIR);
        $this->cache->data_dir = '.' . DS . 'cache' . DS;
        $this->config_dir = '.' . DS . 'configs' . DS;
        $this->debug_tpl = SMARTY_DIR . 'debug.tpl';
        if (!$this->debugging && $this->debugging_ctrl == 'URL') {
            if (isset($_SERVER['QUERY_STRING'])) {
                $_query_string = $_SERVER['QUERY_STRING'];
            } 
            else {
                $_query_string = '';
            } 
            if (false !== strpos($_query_string, $this->smarty_debug_id)) {
                if (false !== strpos($_query_string, $this->smarty_debug_id . '=on')) {
                    // enable debugging for this browser session
                    setcookie('SMARTY_DEBUG', true);
                    $this->debugging = true;
                } 
                elseif (false !== strpos($_query_string, $this->smarty_debug_id . '=off')) {
                    // disable debugging for this browser session
                    setcookie('SMARTY_DEBUG', false);
                    $this->debugging = false;
                } 
                else {
                    // enable debugging for this page
                    $this->debugging = true;
                } 
            } 
            else {
                if (isset($_COOKIE['SMARTY_DEBUG'])) {
                    $this->debugging = true;
                } 
            } 
        } 
        $this->assign_global('SCRIPT_NAME', $_SERVER['SCRIPT_NAME']);
    } 

    /**
    * Class destructor
    */
    public function __destruct() {
        // restore to previous exception handler, if any
        if (!empty($this->exception_handler))
        restore_exception_handler();
    } 

    /**
    * fetches a rendered Smarty template
    * 
    * @param string $template the resource handle of the template file or template object
    * @param mixed $cache_id cache id to be used with this template
    * @param mixed $compile_id compile id to be used with this template
    * @param object $ |null $parent next higher level of Smarty variables
    * @return string rendered template output
    */
    public function fetch($template, $cache_id = null, $compile_id = null, $parent = null) {
        if (is_object($cache_id)) {
            $parent = $cache_id;
            $cache_id = null;
        } 
        if ($parent === null) {
            // get default Smarty data object
            $parent = $this;
        } 
        // create template object if necessary
        ($template instanceof $this->template_class)? $_template = $template :
        $_template = $this->createTemplate ($template, $cache_id, $compile_id, $parent);
        $_smarty_old_error_level = $this->debugging ? error_reporting() : error_reporting(isset($this->error_reporting)
            ? $this->error_reporting : error_reporting() &~E_NOTICE); 
        // return redered template
        if (isset($this->autoload_filters['output']) || isset($this->registered_filters['output'])) {
            $_output = Smarty_Internal_Filter_Handler::runFilter('output', $_template->getRenderedTemplate(), $this);
        } 
        else {
            $_output = $_template->getRenderedTemplate();
        } 
        $_template->rendered_content = null;
        error_reporting($_smarty_old_error_level);
        return $_output;
    } 

    /**
    * displays a Smarty template
    * 
    * @param string $ |object $template the resource handle of the template file  or template object
    * @param mixed $cache_id cache id to be used with this template
    * @param mixed $compile_id compile id to be used with this template
    * @param object $parent next higher level of Smarty variables
    */
    public function display($template, $cache_id = null, $compile_id = null, $parent = null) {
        // display template
        echo $this->fetch ($template, $cache_id, $compile_id, $parent); 
        // debug output
        if ($this->debugging) {
            Smarty_Internal_Debug::display_debug($this);
        } 
        return true;
    } 

    /**
    * test if cache i valid
    * 
    * @param string $ |object $template the resource handle of the template file or template object
    * @param mixed $cache_id cache id to be used with this template
    * @param mixed $compile_id compile id to be used with this template
    * @return boolean cache status
    */
    public function is_cached($template, $cache_id = null, $compile_id = null) {
        if (!($template instanceof $this->template_class)) {
            $template = $this->createTemplate ($template, $cache_id, $compile_id, $this);
        } 
        // return cache status of template
        return $template->isCached();
    } 

    /**
    * Loads security class and enables security
    */
    public function enableSecurity() {
        if (isset($this->security_class)) {
            $this->security_policy = new $this->security_class;
            $this->security_handler = new Smarty_Internal_Security_Handler($this);
            $this->security = true;
        } 
        else {
            throw new Exception('Property security_class is not defined');
        } 
    } 

    /**
    * Set template directory
    * 
    * @param string $ |array $template_dir folder(s) of template sorces
    */
    public function setTemplateDir($template_dir) {
        $this->template_dir = (array)$template_dir;
        return;
    } 
    /**
    * Adds template directory(s) to existing ones
    * 
    * @param string $ |array $template_dir folder(s) of template sources
    */
    public function addTemplateDir($template_dir) {
        $this->template_dir = array_merge((array)$this->template_dir, (array)$template_dir);
        $this->template_dir = array_unique($this->template_dir);
        return;
    } 
    /**
    * Set compile directory
    * 
    * @param string $compile_dir folder of compiled template sources
    */
    public function setCompileDir($compile_dir) {
        $this->compile_dir = $compile_dir;
        return;
    } 
    /**
    * Set cache directory
    * 
    * @param string $cache_dir folder of cache files
    */
    public function setCacheDir($cache_dir) {
        $this->cache->data_dir = $cache_dir;
        return;
    } 
    /**
    * Enable Caching
    */
    public function enableCaching() {
        $this->caching = SMARTY_CACHING_LIFETIME_CURRENT;
        return;
    } 
    /**
    * Set caching life time
    * 
    * @param integer $lifetime lifetime of cached file in seconds
    */
    public function setCacheLifetime($lifetime) {
        $this->cache->data_lifetime = $lifetime;
        return;
    } 
    /**
    * Takes unknown classes and loads plugin files for them
    * class name format: Smarty_PluginType_PluginName
    * plugin filename format: plugintype.pluginname.php
    * 
    * @param string $plugin_name class plugin name to load
    * @return string|boolean filepath of loaded file or false
    */
    public function loadPlugin($plugin_name, $check = true) {
        // if function or class exists, exit silently (already loaded)
        if ($check && (is_callable($plugin_name) || class_exists($plugin_name, false)))
        return true; 
        // Plugin name is expected to be: Smarty_[Type]_[Name]
        $_plugin_name = strtolower($plugin_name);
        $_name_parts = explode('_', $_plugin_name, 3); 
        // class name must have three parts to be valid plugin
        if (count($_name_parts) < 3 || $_name_parts[0] !== 'smarty') {
            throw new Exception("plugin {$plugin_name} is not a valid name format");
            return false;
        } 
        // if type is "internal", get plugin from sysplugins
        if ($_name_parts[1] == 'internal') {
            $file = SMARTY_SYSPLUGINS_DIR . $_plugin_name . '.php';
            if (file_exists($file)) {
                require_once($file);
                return $file;
            } 
            else {
                return false;
            } 
        } 
        // plugin filename is expected to be: [type].[name].php
        $_plugin_filename = "{$_name_parts[1]}.{$_name_parts[2]}.php"; 
        // loop through plugin dirs and find the plugin
        foreach((array)$this->plugins_dir as $_plugin_dir) {
            if (strpos('/\\', substr($_plugin_dir, -1)) === false) {
                $_plugin_dir .= DS;
            } 
            $file = $_plugin_dir . $_plugin_filename;
            if (file_exists($file)) {
                require_once($file);
                return $file;
            } 
        } 
        // no plugin loaded
        return false;
    } 

    /**
    * Sets the exception handler for Smarty.
    * 
    * @param mixed $handler function name or array with object/method names
    * @return string previous exception handler
    */
    public function setExceptionHandler($handler) {
        $this->exception_handler = $handler;
        return set_exception_handler($handler);
    } 

    /**
    * Loads cache resource.
    * 
    * @return object of cache resource
    */
    public function loadCacheResource($type = null) {
        if (!isset($type)) {
            $type = $this->caching_type;
        } 
        // already loaded?
        if (isset($this->cache->data_resource_objects[$type])) {
            return $this->cache->data_resource_objects[$type];
        } 
        if (in_array($type, $this->cache->data_resource_types)) {
            $cache_resource_class = 'Smarty_Internal_CacheResource_' . ucfirst($type);
            return $this->cache->data_resource_objects[$type] = new $cache_resource_class($this);
        } 
        else {
            // try plugins dir
            $cache_resource_class = 'Smarty_CacheResource_' . ucfirst($type);
            if ($this->loadPlugin($cache_resource_class)) {
                return $this->cache->data_resource_objects[$type] = new $cache_resource_class($this);
            } 
            else {
                throw new Exception("Unable to load cache resource '{$type}'");
            } 
        } 
    } 

    /**
    * trigger Smarty error
    * 
    * @param string $error_msg 
    * @param integer $error_type 
    */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING) {
        throw new Exception("Smarty error: $error_msg");
    } 

    /**
    * Takes unknown class methods and lazy loads sysplugin files for them
    * class name format: Smarty_Method_MethodName
    * plugin filename format: method.methodname.php
    * 
    * @param string $name unknown methode name
    * @param array $args aurgument array
    */
    public function __call($name, $args) {
        $name = strtolower($name);
        if ($name == 'smarty') {
            throw new Exception('Please use parent::__construct() to call parent constuctor');
        } 
        $function_name = 'smarty_method_' . $name;
        if (!is_callable($function_name)) {
            if (!file_exists(SMARTY_SYSPLUGINS_DIR . $function_name . '.php')) {
                throw new Exception('Undefined Smarty method "' . $name . '"');
            } 
            require_once(SMARTY_SYSPLUGINS_DIR . $function_name . '.php');
        } 
        return call_user_func_array($function_name, array_merge(array($this), $args));
    } 
} 

function smartyAutoload($class) {
    $_class = strtolower($class);
    if (substr($_class, 0, 16) === 'smarty_internal_' || $_class == 'smarty_security') {
        include SMARTY_SYSPLUGINS_DIR . $_class . '.php';
    } 
} 

?>
