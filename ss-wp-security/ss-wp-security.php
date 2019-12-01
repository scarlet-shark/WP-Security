<?php
/*
* Scarlet Shark WP Security
*
* @link              https://scarletshark.com/wp-security/
* @since             0.0.1
* @package           ss-wp-security
*
* @wordpress-plugin
* Plugin Name:       Scarlet Shark WP Security
* Plugin URI:        https://scarletshark.com/wp-security/
* Description:       Basic security features for WordPress.
* Version:           0.0.5
* Author:            Scarlet Shark
* Author URI:        https://scarletshark.com/wp-security/
* License:           TBD
* License URI:        https://scarletshark.com/wp-security/license.html
* Text Domain:       ss-wp-security
* Domain Path:       /
*/

defined('ABSPATH') or die('Direct access is not allowed.');

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SS-WP-SECURITY', '0.0.5' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ss-wp-security-activator.php
 */
function activate_ss_wp_security() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ss-wp-security-activator.php';
	SS_WP_Security_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ss-wp-security-deactivator.php
 */
function deactivate_ss_wp_security() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ss-wp-security-deactivator.php';
	SS_WP_Security_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ss_wp_security' );
register_deactivation_hook( __FILE__, 'deactivate_ss_wp_security' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ss-wp-security.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_ss_wp_security() {
	$plugin = new SS_WP_Security();
	$plugin->run();
}

run_ss_wp_security();
