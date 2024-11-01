<?php
/*
Plugin Name: Tennis Court Bookings
Plugin URI: http://www.ezihosting.com/tennis-court-bookings/
Version: 1.2.7
Author: renoirIII
Author URI: http://www.ezihosting.com.au/
Text Domain: rncbc
Description: Tennis Court Bookings come in 4 modules ranging from our free lite version to a comprehensive tennis court booking, coaching and payment system.
*/

if ( ! defined( 'RNCBC_PLUGIN_NAME' ) )
    define( 'RNCBC_PLUGIN_NAME', __FILE__ );

if ( ! defined( 'RNCBC_PLUGIN_VERSION' ) )
    define( 'RNCBC_PLUGIN_VERSION', '1.2.7' );

if ( ! defined( 'RNCBC_PLUGIN_DIR' ) )
    define( 'RNCBC_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

require_once RNCBC_PLUGIN_DIR.'/libs/init.php';

require_once RNCBC_PLUGIN_DIR.'/libs/functions.php';

require_once RNCBC_PLUGIN_DIR.'/libs/ajax.php';

require_once RNCBC_PLUGIN_DIR.'/libs/shortcodes.php';

require_once RNCBC_PLUGIN_DIR.'/libs/class-manage.php';

register_activation_hook( __FILE__, 'rncbc_activation' );
function rncbc_activation() {
    $init = new RNCbcInit();
    $init->run();
}

function rncbc_update_db_check() {
    $init = new RNCbcInit();
    $init->upgrade();
}
add_action( 'plugins_loaded', 'rncbc_update_db_check' );

if ( is_admin() ) {
    function register_rncbc_options_page() {
        $manage = new RNCbcManage();

        add_menu_page(__('Tennis Court Bookings', 'rncbc'), __('Tennis Court Bookings', 'rncbc'), 'manage_options', 'rncbc_manage', '', 'dashicons-calendar');

        add_submenu_page( 'rncbc_manage', __('Settings', 'rncbc'), __('Settings', 'rncbc'), 'manage_options', 'rncbc_setting', array($manage, 'setting'));
        add_submenu_page( 'rncbc_manage', __('Calendars', 'rncbc'), __('Calendars', 'rncbc'), 'manage_options', 'rncbc_calendars', array($manage, 'calendars'));
        add_submenu_page( 'rncbc_manage', __('Coaching', 'rncbc'), __('Coaching', 'rncbc'), 'manage_options', 'rncbc_coaching', array($manage, 'coaching'));
        add_submenu_page( 'rncbc_manage', __('Go Pro', 'rncbc'), __('Go Pro', 'rncbc'), 'manage_options', 'rncbc_go_pro', array($manage, 'go_pro'));

        remove_submenu_page('rncbc_manage','rncbc_manage');

    }
    add_action('admin_menu', 'register_rncbc_options_page');
} else {
    wp_enqueue_style('rncbc-calendar', plugins_url('assets/css/rncbc_calendar.css', RNCBC_PLUGIN_NAME), array(), RNCBC_PLUGIN_VERSION);
}



function rncbc_yui3_admin_body_classes( $classes ) {
    $classes .= 'yui3-skin-sam';
    return $classes;
}
add_filter( 'admin_body_class','rncbc_yui3_admin_body_classes' );

function rncbc_yui3_body_classes( $classes ) {
    $classes[] = 'yui3-skin-sam';
    return $classes;
}
add_filter( 'body_class','rncbc_yui3_body_classes' );



function rncbc_load_plugin_textdomain() {
    load_plugin_textdomain( 'rncbc', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'rncbc_load_plugin_textdomain' );