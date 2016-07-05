<?php
  class CustomAuthForm{
    public function __construct(){
      add_action( 'wp_enqueue_scripts', array( $this, 'load_style' ));
    }

    public function load_style(){
      wp_enqueue_style('clf_style', CUSTOM_AUTH_FORM_URL.'assets/css/caf_style.css', false, null);
    }

    public function caf_activate() {
      global $wpdb;
      $charset_collate = $wpdb->get_charset_collate();
      $table_name = $wpdb->prefix . 'user_details';
      if($wpdb->get_var("show tables like '$table_name'") != $table_name){
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id bigint(20) NOT NULL AUTO_INCREMENT,
          user_id bigint(20) NOT NULL,
          fullname varchar(100) NOT NULL,
          token varchar(64) NULL,
          UNIQUE KEY id (id)
        ) $charset_collate;";
        $wpdb->query($sql);
      }else{
        $table_name = $wpdb->prefix . 'user_details';
        $sql = "TRUNCATE TABLE IF EXISTS $table_name;";
        $wpdb->query($sql);
      }
      $usertable = $wpdb->prefix . 'users';
      $sql = "INSERT INTO $table_name (`user_id`,`fullname`) SELECT `id`, `display_name` FROM $usertable;";
      $wpdb->query($sql);
    }

    public function caf_deactivate() {
      global $wpdb;
      $table_name = $wpdb->prefix . 'user_details';
      $sql = "DROP TABLE IF EXISTS $table_name;";
      $wpdb->query($sql);
    }
  }
