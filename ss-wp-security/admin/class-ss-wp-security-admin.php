<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    SS-WP-Security
 * @subpackage SS-WP-Security/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    SS-WP-Security
 * @subpackage SS-WP-Security/admin
 * @author     Alec Dhuse <alec@scarletshark.com>
 */
class SS_WP_Security_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $ss_wp_security    The ID of this plugin.
	 */
	private $ss_wp_security;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $ss_wp_security       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $ss_wp_security, $version ) {

		$this->ss_wp_security = $ss_wp_security;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in SS-WP-Security_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SS-WP-Security_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->ss_wp_security, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in SS-WP-Security_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The SS-WP-Security_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->ss_wp_security, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );

	}

	function login_failed($username) {
		global $wpdb;

		/* Collect information on source of login */
	  $ip = $_SERVER['REMOTE_ADDR'];
	  $user_agent = esc_sql($_SERVER['HTTP_USER_AGENT']);

		$table_name = $wpdb->prefix . "littlebonsai_failed_logins";
		$results = $wpdb->get_results("SELECT id, seen_count, reported, reported_time FROM $table_name WHERE ip='$ip' AND user_agent='$user_agent'");

		if (sizeof($results) == 0) {
			$wpdb->insert(
				$table_name,
				array(
					'ip' => $ip,
					'user_agent' => $user_agent,
					'first_seen' => current_time('mysql')
				)
			);
		} else {
			$ip_id = $results[0]->id;
			$seen_count = $results[0]->seen_count;
			$seen_count_new = $seen_count + 1;
			$reported = $results[0]->reported;

			$current_time = time();
			$reported_time_str = $results[0]->reported_time;
			$reported_time = strtotime($reported_time_str);

			// If IP was reported over 30 days ago, re-report
			if (($current_time - $reported_time) > 2592000) {
				$reported = False;
			}

			/* Update seen count */
			$wpdb->update(
				$table_name,
				array('seen_count' => $seen_count_new),
				array('id' => $ip_id ),
				array('%d'),
				array('%d')
			);

			/* If there are more than two failed login send alert */
			if ($seen_count_new > 2) {
				if ($reported == False) {
					/* Get API key */
					$table_name = $wpdb->prefix . "littlebonsai_settings";
					$results = $wpdb->get_results("SELECT setting_value FROM $table_name WHERE setting_name='api_key'");
					$api_key = $results[0]->setting_value;

					$url = 'https://scarletshark.com/api/v0.1/report_ip.php';
				  $data = array('ip' => $ip, 'user_agent' => $user_agent, 'comment' => 'WordPress Login Brute-forcing', 'tags' => 'malicious-login,wordpress', 'ref_url' => 'https://littlebonsai.co/docs/reported-ip-tags.html#WordPressLoginBrute-forcing');

				  $options = array(
				      'http' => array(
				          'method'  => 'POST',
				          'content' => http_build_query($data),
				          'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
				                       "Accept: application/json\r\n" .
				                       "Authorization: Bearer $api_key\r\n"
				      )
				  );

				  $context  = stream_context_create($options);
				  $result = file_get_contents($url, false, $context);
				  if ($result === FALSE) {
						echo ("Error adm-inc-01");
					} else {
						/* Change status to reported */
						$table_name = $wpdb->prefix . "littlebonsai_failed_logins";

						$wpdb->update(
							$table_name,
							array(
								'reported' => 1,
								'reported_time' => current_time('mysql')
							),
							array('id' => $ip_id ),
							array('%d', '%s'),
							array('%d')
						);

					}
				}
			}
		}
	}

	function login_successful($user_login, $user) {
		global $wpdb;

		/* Collect information on source of login */
	  $ip = $_SERVER['REMOTE_ADDR'];
	  $user_agent = $_SERVER['HTTP_USER_AGENT'];

		/* Add successful login info to table */
		$table_name = $wpdb->prefix . "littlebonsai_successful_logins";

		$wpdb->insert(
			$table_name,
			array(
				'ip' => $ip,
				'user' => $user_login,
				'user_agent' => $user_agent
			)
		);

		/* Reset failed logins count */
		$table_name = $wpdb->prefix . "littlebonsai_failed_logins";
		$results = $wpdb->get_results("SELECT id FROM $table_name WHERE ip='$ip' AND user_agent='$user_agent'");

		if (sizeof($results) > 0) {
			$wpdb->update(
				$table_name,
				array('seen_count' => 0),
				array('id' => $ip_id ),
				array('%d'),
				array('%d')
			);
		}
	}

	/* Future code fired when a comment is marked as spam */
	function mark_comment_as_spam($comment_ID) {
		return True;
	}

	/* Future code for determining if a comment is spam */
	function post_comment($comment_ID, $comment_approved, $commentdata) {
		return True;
	}

	/* Future code fired when a comment is unmarked as spam */
	function unmark_comment_as_spam($comment_ID) {
		return True;
	}

	function comment_blacklist_check($author_name, $author_email, $author_url, $comment_text, $author_ip_address, $author_user_agent) {
		return False;
		//get score data from littlebonsai
		//score for user_agent, ip_address, email, url
		//add up scores to see if they meet a threashold
		//execute a wp_die to reject the comment, if threshold is reached.
	}
}
