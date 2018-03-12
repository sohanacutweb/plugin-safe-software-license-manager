<?php

//Defines
global $wpdb;
define('SLM_TBL_LICENSE_KEYS', $wpdb->prefix . "safe_lic_key_tbl");
define('SLM_TBL_LIC_DOMAIN', $wpdb->prefix . "safe_lic_reg_domain_tbl");
define('SLM_TBL_LIC_MAIL_CONTENT', $wpdb->prefix . "safe_mail_template");
define('SLM_MANAGEMENT_PERMISSION', 'manage_options');
define('SLM_MAIN_MENU_SLUG', 'slm-main');
define('SLM_MENU_ICON', 'dashicons-lock');

//Includes
include_once('includes/slm-debug-logger.php');
include_once('includes/slm-utility.php');
include_once('includes/slm-init-time-tasks.php');
include_once('includes/slm-api-utility.php');
include_once('includes/slm-api-listener.php');
include_once('includes/slm-third-party-integration.php');
//include_once('includes/savenana.php');
//Include admin side only files
if (is_admin()) {
    include_once('menu/slm-admin-init.php');
    include_once('menu/includes/slm-list-table-class.php'); //Load our own WP List Table class
}

//Action hooks
add_action('init', 'slm_init_handler');
add_action('plugins_loaded', 'slm_plugins_loaded_handler');

//Initialize debug logger
global $slm_debug_logger;
$slm_debug_logger = new SLM_Debug_Logger();

//Do init time tasks
function slm_init_handler() {
    $init_task = new SLM_Init_Time_Tasks();
    $api_listener = new SLM_API_Listener();
}

//Do plugins loaded time tasks
function slm_plugins_loaded_handler() {
    //Runs when plugins_loaded action gets fired
    if (is_admin()) {
        //Check if db update needed
        if (get_option('wp_lic_mgr_db_version') != WP_LICENSE_MANAGER_DB_VERSION) {
            require_once(dirname(__FILE__) . '/slm_installer.php');
        }
    }

}

//TODO - need to move this to an ajax handler file
add_action('wp_ajax_del_reistered_domain', 'slm_del_reg_dom');
function slm_del_reg_dom() {
    global $wpdb;
    $reg_table = SLM_TBL_LIC_DOMAIN;
    $id = strip_tags($_GET['id']);
    $ret = $wpdb->query("DELETE FROM $reg_table WHERE id='$id'");
    echo ($ret) ? 'success' : 'failed';
    exit(0);
}


/*
add_action( 'init', 'my_rewrite');
function my_rewrite() {
    global $wp_rewrite;
    add_rewrite_rule('^nanacast-listener.php$', WP_PLUGIN_URL . '/software-license-manager/nanacast-listener/savenana.php', 'top');
   $wp_rewrite->flush_rules(true);  // This should really be done in a plugin activation
	
} 

add_action('init', 'yourpluginname_rewrite_rules');
function yourpluginname_rewrite_rules() {
    add_rewrite_rule( 'nanacast-listener/?$', 'index.php?savenana=true', 'top');
}
add_filter( 'query_vars', 'yourpluginname_register_query_var' );
function yourpluginname_register_query_var( $vars ) {
    $vars[] = 'savenana';
    return $vars;
}

/* Template Include */
/*add_filter('template_include', 'yourpluginname_blah_template_include', 1, 1); 
function yourpluginname_blah_template_include($template)
{
    global $wp_query; //Load $wp_query object
    $page_value = $wp_query->query_vars['name']; //Check for query var "blah"
	//print_r($page_value);
	
    if ($page_value == "nanacast-listener") {
			//Verify "blah" exists and value is "true".
        return plugin_dir_path(__FILE__).'/nanacast-listener/savenana.php'; //Load your template or file
    }

    return $template; //Load normal template when $page_value != "true" as a fallback
} */