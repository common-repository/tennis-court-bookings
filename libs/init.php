<?php
if ( ! defined( 'RNCBC_PLUGIN_DIR' ) ) {
    exit; // Exit if accessed directly
}
class RNCbcInit {

    public function __construct() {
//		$this->run();
    }

    public function run() {

        $this->create_tables();
    }

    public function upgrade() {
        $database_version = get_option( "rncbc_database_version" );
        if(!$database_version)
            $database_version = 0;

        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        if($database_version < 1) {
            $this->db_update_v1();
            update_option( "rncbc_database_version", '1' );
        }

        if($database_version < 2) {
            $this->db_update_v2();
            update_option( "rncbc_database_version", '2' );
        }
    }


    private function create_tables() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        $table_name = $wpdb->prefix.'rncbc_calendar';
        if($wpdb->get_var("show tables like '$table_name'") != $table_name){
            $sql = 'CREATE TABLE IF NOT EXISTS `'.$table_name.'` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL,
			  `min_day` varchar(255) DEFAULT NULL,
			  `max_day` varchar(255) DEFAULT NULL,
			  `from` varchar(10) DEFAULT NULL,
			  `to` varchar(10) DEFAULT NULL,
			  `working_day` varchar(255) DEFAULT NULL,
			  `holiday` text,
			  `booking_window_close` int(11) DEFAULT NULL,
			  `success_tip` varchar(255) DEFAULT NULL,
			`create_time` int(11) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
            dbDelta($sql);
        }

        $table_name = $wpdb->prefix.'rncbc_court';
        if($wpdb->get_var("show tables like '$table_name'") != $table_name){
            $sql = 'CREATE TABLE IF NOT EXISTS `'.$table_name.'` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			  `calendar_id` int(11) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `status` tinyint(1) NOT NULL DEFAULT "1",
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            dbDelta($sql);
        }

        $table_name = $wpdb->prefix.'rncbc_reservation';
        if($wpdb->get_var("show tables like '$table_name'") != $table_name){
            $sql = 'CREATE TABLE IF NOT EXISTS `'.$table_name.'` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`calendar_id` int(11) NOT NULL,
			  `court_id` int(11) DEFAULT NULL,
			  `day` int(8) DEFAULT NULL,
			  `name` varchar(255) NOT NULL,
			  `email` varchar(255) DEFAULT NULL,
			  `phone` varchar(255) DEFAULT NULL,
			  `comments` varchar(255) DEFAULT NULL,
			  `reservation` varchar(255) DEFAULT NULL,
			  `create_time` int(11) NOT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;';

            dbDelta($sql);
        }
    }


    private function db_update_v1() {
        global $wpdb;
        $table_name = $wpdb->prefix.'rncbc_calendar';
        if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$table_name}` LIKE 'time_slot';" ) ) {
            $wpdb->query( "ALTER TABLE {$table_name} ADD `time_slot` INT NULL DEFAULT '30';" );
        }
        if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$table_name}` LIKE 'step_size';" ) ) {
            $wpdb->query( "ALTER TABLE {$table_name} ADD `step_size` INT NULL DEFAULT '1';" );
        }
    }

    private function db_update_v2()
    {
        global $wpdb;
        $table_name = $wpdb->prefix.'rncbc_reservation';
        if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$table_name}` LIKE 'status';" ) ) {
            $wpdb->query( "ALTER TABLE {$table_name} ADD `status` INT NULL DEFAULT '1';" );
        }
    }
}