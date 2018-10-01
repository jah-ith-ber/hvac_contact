<?php
/*
Plugin Name:  HVAC Contact Plugin
Plugin URI:   https://www.monarchdigital.com
Description:  Basic WordPress Plugin for a contact form.
Version:      1.0.0
Author:       Michael Williams
Author URI:   https://www.monarchdigital.com
*/

// Prevent public access
if ( !defined( 'ABSPATH' ) ) exit;

// Not required, but we'll create a version option for detecting out of date versions of the plugin.
global $hvac_contact_version;
$hvac_contact_version = '1.0';

/* https://codex.wordpress.org/Creating_Tables_with_Plugins
Our install callback
*/
function hvac_contact_install() {
  global $wpdb;
  global $hvac_contact_version;

  $table_name = $wpdb->prefix . "hvac_contact";

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(320) NOT NULL,
    phone VARCHAR(120) NOT NULL,
    message TEXT NOT NULL,
    time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY  (id)
  ) " . $charset_collate . ";";

  // Modifies the database based on specified SQL statements.
  $wpdb->query( $sql );  

  add_option('hvac_contact_version', $hvac_contact_version);
}

// Our uninstall callback
function hvac_contact_uninstall() {
  global $wpdb;

  $table_name = $wpdb->prefix . "hvac_contact";

  $sql = "DROP TABLE IF EXISTS " . $table_name;

  $wpdb->query( $sql );

  delete_option('hvac_contact_version');
}

// Our callback to create the contact form page.
function hvac_contact_create_form() {
  $form = array(
    'post_title'    => wp_strip_all_tags( 'HVAC Contact Page' ),
    'post_name'    => 'hvac-contact-form',
    'post_status'   => 'publish',
    'post_author'   => 1,
    'post_type'     => 'page',
  );
  // Check if page exists
  $page = get_page_by_path('hvac-contact-form');
  if ($page == NULL) {
    // Create a page upon creation.
    wp_insert_post( $form );
  }
}

// Mock data
function hvac_contact_install_data() {
  global $wpdb;
  $table_name = $wpdb->prefix . "hvac_contact";

  $test_name = 'Michael Williams';
  $test_email = 'mikew@monarchdigital.com';
  $test_message = 'This is my test message. My heater needs repair.';
  $test_phone = '(719)344-2118';

  $wpdb->insert(
		$table_name, 
		array( 
			'name' => $test_name, 
			'email' => $test_email, 
      'message' => $test_message, 
      'phone' => $test_phone,
			'time' => current_time( 'mysql' )
		) 
	);
}

// Hook up our template file for the contact form - https://codex.wordpress.org/Plugin_API/Filter_Reference/page_template
add_filter( 'page_template', 'hvac_contact_form_template' );
function hvac_contact_form_template( $page_template ) {
  if ( is_page( 'HVAC Contact Page' ) ) {
    $page_template = dirname( __FILE__ ) . '/assets/templates/hvac_contact_form.php';
  }
  return $page_template;
}

// Adding a js file just to do it.
add_action( 'admin_enqueue_scripts', 'hvac_contact_js_script' );
function hvac_contact_js_script() {
  wp_enqueue_script( 'hvac-contact-script',  plugin_dir_url(__FILE__) . '/assets/js/hvac_contact.js' );
}

// Register our activation/deactivation hooks - https://developer.wordpress.org/plugins/the-basics/activation-deactivation-hooks/
register_activation_hook( __FILE__, 'hvac_contact_install' );
register_activation_hook( __FILE__, 'hvac_contact_create_form');
register_activation_hook( __FILE__, 'hvac_contact_install_data' );
register_deactivation_hook( __FILE__, 'hvac_contact_uninstall' );

/* Admin settings section - https://codex.wordpress.org/Adding_Administration_Menus */

// Options page callback
function hvac_contact_options() {
  // Admins only!
	if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
  // Include our template
  include_once(dirname( __FILE__ ) . '/assets/templates/hvac_contact_options.php');
}

// Register the email setting that our contact form sends to
add_action( 'admin_init', 'hvac_contact_register_settings' );
function hvac_contact_register_settings() {
  register_setting( 'hvac-contact', 'hvac_contact_admin_email' );
}

// Submissions page callback
function hvac_contact_submissions() {
  global $wpdb;
  $table_name = $wpdb->prefix . "hvac_contact";
  $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name" );
  // get_template_part() is only for themes.
  // Include our template
  include_once(dirname( __FILE__ ) . '/assets/templates/hvac_contact_submissions.php');
}

// Register our Options page and our View Submissions page
add_action( 'admin_menu', 'hvac_contact_plugin_menu' );
function hvac_contact_plugin_menu() {
  add_options_page( 'HVAC Contact Options', 'HVAC Contact', 'manage_options', 'hvac-contact-options', 'hvac_contact_options' );
  add_menu_page( 'HVAC Contact Submissions', 'HVAC Contact', 'manage_options', 'hvac-contact', 'hvac_contact_submissions');
}

// Ajax delete contact entry
add_action( 'wp_ajax_hvac_contact_delete_entry', 'hvac_contact_delete_entry' );
function hvac_contact_delete_entry() {
	global $wpdb;

	$post_id = $_POST['row_id'];
  $element = '#delete_button' . $post_id;

  echo $element;

  $table_name = $wpdb->prefix . "hvac_contact";

  $wpdb->delete(
    $table_name, 
    array( 
      'id' => $post_id
    ) 
  );

	wp_die(); // this is required to terminate immediately and return a proper response
}


?>