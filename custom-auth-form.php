<?php
  /*
  Plugin Name: Custom Auth Form
  Plugin URI:
  Description: Custom authentication form
  Author: ljopleda
  Version: 1.0
  Author URI:
  */

  if ( ! defined('ABSPATH')) exit;  // if direct access

  define( 'CUSTOM_AUTH_FORM_URL' , plugins_url('/', __FILE__)  );
  define( 'CUSTOM_AUTH_FORM_DIR' , plugin_dir_path( __FILE__ ) );

  require_once( ABSPATH . "wp-includes/pluggable.php" );
  require_once( CUSTOM_AUTH_FORM_DIR . 'includes/main-custom-auth-form.php');
  require_once( CUSTOM_AUTH_FORM_DIR . 'includes/ajax-functions.php');
  require_once( CUSTOM_AUTH_FORM_DIR . 'includes/generate-table.php');
  require_once( CUSTOM_AUTH_FORM_DIR . 'includes/generate-shortcode.php');

  if(class_exists('CustomAuthForm')){

    register_activation_hook( __FILE__, array('CustomAuthForm', 'caf_activate') );
    register_deactivation_hook( __FILE__, array('CustomAuthForm', 'caf_deactivate') );

    $CustomAuthForm = new CustomAuthForm();
    
  }
