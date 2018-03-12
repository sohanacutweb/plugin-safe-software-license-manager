<?php
/*
Plugin Name: Plugin Safe Software License Manager
Version: 2.4
Plugin URI: http://theprotectorplugin.com
Author: The Protector
Author URI: http://theprotectorplugin.com
Description: Software license management solution for your Plugin Safe Plugin.
*/

if(!defined('ABSPATH')){
    exit; //Exit if accessed directly
}

//Short name/slug "SLM" or "slm"

define('WP_LICENSE_MANAGER_VERSION', "2.4");
define('WP_LICENSE_MANAGER_DB_VERSION', '1.3');
define('WP_LICENSE_MANAGER_FOLDER', dirname(plugin_basename(__FILE__)));
define('WP_LICENSE_MANAGER_URL', plugins_url('',__FILE__));
define('WP_LICENSE_MANAGER_PATH', plugin_dir_path(__FILE__));
define('SLM_SITE_HOME_URL', home_url());
define('SLM_WP_SITE_URL', site_url());

include_once('slm_plugin_core.php');

//Activation handler
function slm_activate_handler(){
    //Do installer task
    slm_db_install();
    
    //schedule a daily cron event
    wp_schedule_event(time(), 'daily', 'slm_daily_cron_event'); 

    do_action('slm_activation_complete');
}
register_activation_hook(__FILE__,'slm_activate_handler');

//Deactivation handler
function slm_deactivate_handler(){
    //Clear the daily cron event
    wp_clear_scheduled_hook('slm_daily_cron_event');
    
    do_action('slm_deactivation_complete');
}
register_deactivation_hook(__FILE__,'slm_deactivate_handler');

//Installer function
function slm_db_install ()
{
    //run the installer
    require_once(dirname(__FILE__).'/slm_installer.php');
}
/**
 * Plugin Update Features Will Integrate in Next Phase if required
 */
function filter_plugin_updates( $value ) {
	$slug = plugin_basename( __FILE__ );
    unset( $value->response[$slug] );
    return $value;
}
add_filter( 'site_transient_update_plugins', 'filter_plugin_updates' );