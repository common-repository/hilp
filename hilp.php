<?php defined('ABSPATH') or die('hilp.me');
/*
 * Plugin Name: Hilp - Wordpress support
 * Plugin URI: https://hilp.me/
 * Description: Get help and support from developers to fix bugs or make the changes you want. With a few clicks you can send your case to a developer that does the job for you.
 * Author: Fokusiv
 * Author URI: https://fokusiv.com/
 * Version: 1.0.3
 */

define('HILP_VERSION', '1.0.3');
define('HILP_STAMP', (WP_DEBUG ? time() : HILP_VERSION));
define('HILP_PATH', plugin_dir_path(__FILE__));
define('HILP_URI', plugins_url('', __FILE__).'/');
define('HILP_SERVER_URL', 'https://hilp.me/');
define('HILP_SERVER_API', HILP_SERVER_URL.'wp-json/');
define('HILP_MAIL', 'hilp@fokusiv.com');
define('HILP_INC_PATH', HILP_PATH.'admin/back/inc/');

require_once(HILP_PATH.'functions.php');
require_once(HILP_PATH.'actions.php');

//Plugin links
add_filter('plugin_action_links', function($actions, $plugin_file){
    static $plugin;

    if(!isset($plugin))
        $plugin = plugin_basename(__FILE__);
    if($plugin == $plugin_file){

        $settings = array('settings' => '<a href="'.admin_url('options-general.php?page=hilp_settings').'">'.__('Settings', 'General').'</a>');
        $site_link = array('support' => '<a href="https://hilp.me" target="_blank">Site</a>');

        $actions = array_merge($settings, $actions);
        $actions = array_merge($site_link, $actions);

    }

    return $actions;
}, 10, 5);

/* Activation hook */
register_activation_hook(__FILE__, function(){
    add_option('hilp_activation_redirect', true);
});
add_action('admin_init', function(){
    if(get_option('hilp_activation_redirect', false)){
        delete_option('hilp_activation_redirect');

        //Show admin bar
        update_user_option(get_current_user_id(), 'show_admin_bar_front', "true");
        wp_redirect(admin_url('options-general.php?page=hilp_settings&action="activated"'));
    }
});

/* Deactivation hook */
register_deactivation_hook(__FILE__, function(){
    require_once(ABSPATH.'wp-includes/pluggable.php');
    //Deregister the Hilp user
    $username = 'hilp';
    if(username_exists($username)){
        $user = get_user_by('login', $username);
        $user_id = $user->ID;
        wp_delete_user($user_id);
    }
});
