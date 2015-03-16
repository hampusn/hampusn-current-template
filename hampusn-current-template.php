<?php
/*
Plugin Name: Current Template
Plugin URI: http://hampusnord.in/
Description: This plugin adds the current template to the body and/or in the admin menu bar.
Version: 0.0.1
Author: Hampus Nordin <hej@hampusnord.in>
Author URI: http://hampusnord.in/
Text Domain: hampusn_current_template
*/

// No Direct Access!
defined('ABSPATH') or die('Plugin file cannot be accessed directly.');

/**
 * The option name which will be used with get_option() and update_option().
 * This is the name which will be used to store the settings array into the database.
 * 
 * WARNING: CHANGING THIS MIGHT RESET AND/OR BLOAT THE SETTINGS FOR THIS PLUGIN IN THE DATABASE.
 **/
define('HAMPUSN_CURRENT_TEMPLATE_OPTION_NAME', 'hampusn_current_template');

/**
 * Init Admin
 *
 * @author Hampus Nordin
 **/
if (! class_exists('Hampusn_Current_Template_Admin')) {
  require 'includes/class-admin.php';
  new Hampusn_Current_Template_Admin( HAMPUSN_CURRENT_TEMPLATE_OPTION_NAME );
}


/**
 * Helper function which gets the currently stored settings from the database.
 *
 * @param  boolean $reset Get fresh settings from the database.
 * @return array
 * @author Hampus Nordin
 * @see    http://codex.wordpress.org/Function_Reference/get_option
 **/
function hampusn_current_template_get_settings( $reset = false ) {
  // Create a static cache so we only have to 
  // call the database once per page load.
  static $hampusn_current_template_settings = array();
  if ( empty( $hampusn_current_template_settings ) || true === $reset ) {
    $hampusn_current_template_settings = get_option( HAMPUSN_CURRENT_TEMPLATE_OPTION_NAME, array() );
  }
  return $hampusn_current_template_settings;
}


/**
 * Helper function which returns a list of the currently used templates.
 *
 * @return array
 * @author Hampus Nordin
 **/
function hampusn_current_template_templates() {
  // Create a static cache so this information isn't 
  // processed more than once per page load.
  static $hampusn_current_template_templates = array();
  if ( empty( $hampusn_current_template_templates ) ) {
    // All information about the current template 
    // is stored in a global variable by WordPress.
    global $template;
    // We shouldn't work with the global directly.
    $templates = $template;
    // Make sure it's an array since we will assume it is 
    // when we use it later.
    if ( ! is_array( $templates ) ) {
      $templates = array( $templates );
    }
    // Remove the absolute path to the wordpress installation 
    // from all templates since it's irrelevant.
    if ( defined( 'ABSPATH' ) ) {
      foreach ( $templates as &$tpl ) {
        $tpl = str_replace( ABSPATH, '', $tpl );
      }
    }
    $hampusn_current_template_templates = $templates;
  }
  return $hampusn_current_template_templates;
}


/**
 * Get the first template from the list/array.
 *
 * @return string
 * @author Hampus Nordin
 **/
function hampusn_current_template_get_first_template() {
  $templates = hampusn_current_template_templates();
  return ! empty( $templates ) ? $templates[ 0 ] : '';
}


/**
 * WP hook callback which adds css and js.
 * 
 * Action hook: wp_enqueue_scripts
 *
 * @return void
 * @author Hampus Nordin
 * @see    http://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
 **/
function hampusn_current_template_add_css_js() {
  // Only enqueue styling and script if user is logged in.
  if ( is_user_logged_in() ) {
    // Enqueue styling
    wp_enqueue_style( 'hampusn-current-template-css', plugins_url( 'hampusn-current-template.css', __FILE__ ), array(), '0.0.1', 'screen' );
    // So far, the js file is only used for printing the modal box. 
    // So, no need to include anything if the modal shouldn't even be printed.
    $settings = hampusn_current_template_get_settings();
    if ( ! empty( $settings[ 'show_modal_in_body_top' ] ) ) {
      wp_enqueue_script( 'hampusn-current-template-js', plugins_url( 'hampusn-current-template.js', __FILE__ ), array( 'jquery' ), '0.0.0', true );
    }
  }
}
add_action( 'wp_enqueue_scripts', 'hampusn_current_template_add_css_js' );


/**
 * WP hook callback which exposes js variables.
 * 
 * Action hook: wp_head
 *
 * @return void
 * @author Hampus Nordin
 * @see    http://codex.wordpress.org/Plugin_API/Action_Reference/wp_head
 **/
function hampusn_current_template_wp_head() {
  $settings = hampusn_current_template_get_settings();
  // So far, the js file is only used for printing the modal box. 
  // So, no need to include anything if the modal shouldn't even be printed.
  if ( ! empty( $settings[ 'show_modal_in_body_top' ] ) ) {
    // Pass variables to frontend for js usage.
    wp_localize_script( 'hampusn-current-template-js', 'hampusnCurrentTemplate', array(
      'templates' => hampusn_current_template_templates(),
      'isAdmin' => (int) current_user_can( 'install_themes' ),
      'showModalInBodyTop' => ! empty( $settings[ 'show_modal_in_body_top' ] ) ? 1 : 0,
    ) );
  }
}
add_action( 'wp_head', 'hampusn_current_template_wp_head' );


/**
 * WP hook callback which adds the template to the admin menu bar.
 * 
 * Action hook: admin_bar_menu
 *
 * @return void
 * @author Hampus Nordin
 * @see    http://codex.wordpress.org/Plugin_API/Action_Reference/admin_bar_menu
 * @see    http://codex.wordpress.org/Class_Reference/WP_Admin_Bar
 **/
function hampusn_current_template_modify_admin_bar( $wp_admin_bar )  {
  // Get the first template if one exist.
  $current_template = hampusn_current_template_get_first_template();
  // Get the stored settings.
  $settings = hampusn_current_template_get_settings();
  // Make sure at least one template exist and that the current template should be shown in the admin menu bar.
  if ( $current_template && ! empty( $settings[ 'show_in_admin_menu' ] ) ) {
    // Get a list of all current templates.
    $templates = hampusn_current_template_templates();
    // Get the filename from the current template.
    $filename = basename( $current_template );
    // Add the main menu item which will show only the filename of the current template.
    $wp_admin_bar->add_menu( array(
      'id' => 'hampusn-current-template',
      'title' => "Current Template: <strong>{$filename}</strong>",
      'parent' => false,
      'href' => false,
      'group' => false,
      'meta' => array(
        'class' => 'hampusn-current-template-admin-menu-item',
      ),
    ) );    
    // Add the rest of the templates (including the first one) with full path.
    foreach ( $templates as $key => $tpl ) {
      // Split up the template path into filename and 
      // filepath for the specific html markup below.
      $filename = basename( $tpl );
      $filepath = dirname( $tpl );
      // Add template to menu.
      $wp_admin_bar->add_node( array(
        'id' => 'hampusn-current-template-tpl-' . $key,
        'title' => "<strong>{$key}:</strong> <span>{$filepath}/</span><strong>{$filename}</strong>",
        'parent' => 'hampusn-current-template',
      ) );
    }
  }
}
add_action( 'admin_bar_menu', 'hampusn_current_template_modify_admin_bar', 100 );
