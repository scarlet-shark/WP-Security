<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    SS_WP_Security
 * @subpackage SS_WP_Security/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.2
 * @package    SS_WP_Security
 * @subpackage SS_WP_Security/includes
 * @author     Alec Dhuse <alec@scarletshark.com>
 */
class SS_WP_Security_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.2
	 */
	public static function activate() {

		/* Create database tables */
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();

		/* Failed logins */
		$table_name = $wpdb->prefix . "scarletshark_failed_logins";
		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
			ip tinytext NOT NULL,
			user_agent text NOT NULL,
			seen_count mediumint(9) DEFAULT '1' NOT NULL,
		  first_seen timestamp DEFAULT 0,
			last_seen timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
			reported_time timestamp DEFAULT 0,
			reported boolean DEFAULT False NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql );

		/* successful logins */
		$table_name = $wpdb->prefix . "scarletshark_successful_logins";
		$sql = "CREATE TABLE $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
			ip tinytext NOT NULL,
			user tinytext NOT NULL,
			user_agent text NOT NULL,
			login_time timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql );

		/* settings table */
		$table_name = $wpdb->prefix . "scarletshark_settings";
		$sql = "CREATE TABLE $table_name (
			setting_id mediumint(9) NOT NULL AUTO_INCREMENT,
			setting_name tinytext NOT NULL,
			setting_value tinytext NOT NULL,
			PRIMARY KEY  (setting_id)
		) $charset_collate;";

		dbDelta( $sql );

		/* Add default settings */
		$wpdb->insert(
			$table_name,
			array(
				'setting_name' => "api_key",
				'setting_value' => ""
			)
		);

	}
}
