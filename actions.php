<?php defined('ABSPATH') or die('hilp.me');
function hilp_init(){
    //Get class from functions.php
    $hilp = new hilp();

    //Don't allow users that have expired
    add_action( 'wp_authenticate_user' , array($hilp, 'admin_check_time'), 10, 3 );

    //We only want admin users to load this. All admin actions are after this
    if(!current_user_can('activate_plugins')):
        return;
    endif;

    //Add hilp to admin bar
    add_action('admin_bar_menu', array($hilp, 'create_admin_bar_menu'), 0);

    if(is_admin()):

        //Settings
        add_action('admin_menu', array($hilp, 'create_settings_menu'));
        add_action('admin_init', array($hilp, 'register_settings'));

        //Notice
        add_action('admin_init', array($hilp, 'create_notices_if_necessery'));

        //Developer mode
        add_action('admin_head', array($hilp, 'developer_mode'), 9999);

        //Enqueue styles and scripts
        add_action('admin_enqueue_scripts', array($hilp, 'enqueue_required_script_style'), 11);
        add_action('admin_enqueue_scripts', array($hilp, 'enqueue_admin_back_script_style'), 12);

        //Add save function to wp_ajax
        add_action('wp_ajax_hilp_save_list', array($hilp, 'hilp_save_list'));
        add_action('wp_ajax_hilp_save_case', array($hilp, 'hilp_save_case'));
        add_action('wp_ajax_hilp_save_pending', array($hilp, 'hilp_save_pending'));
        add_action('wp_ajax_hilp_save_remote_user', array($hilp, 'hilp_save_remote_user'));

        add_filter('sanitize_post_meta_hilp_list', array($hilp, 'sanitize_meta_hilp_list'));
        add_filter('sanitize_post_meta_hilp_case', array($hilp, 'sanitize_meta_hilp_case'));

    else:
        //Enqueue styles and scripts
        add_action('wp_enqueue_scripts', array($hilp, 'enqueue_required_script_style'), 11);
        add_action('wp_enqueue_scripts', array($hilp, 'enqueue_front_script_style'), 12);

        //Create front-end vue app
        add_action('wp_head', array($hilp, 'create_front_output'), 9999);

    endif;
}

add_action('init', 'hilp_init');