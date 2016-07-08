<?php
  class CustomAuthForm{
    public function __construct(){
      $CustomTemplate = new CustomTemplate();
      $AjaxFunctions = new AjaxFunctions();
      $GenerateShortcode = new GenerateShortcode();
      add_action( 'wp_enqueue_scripts', array( $this, 'load_style' ));
    }

    public function sendConfirmationEmail($email,$token,$subject,$buttontxt,$template,$page){
      $link = site_url();
      $to = $email;
      $linkparts = parse_url($link);
      $link = rtrim($linkparts['scheme'] . '://' . $linkparts['host'] . $linkparts['path'] , '/') . $page;
      $data = [
        "{link}" => $link,
        '{site_name}' => 'Divestmedia.com',
        '{site_email}' => 'help@divestmedia.com',
        '{btn_confirm_txt}' => $buttontxt
      ];
      ob_start();
      include(CUSTOM_AUTH_FORM_DIR . 'templates/'.$template.'.tpl');
      $ob = ob_get_clean();
      $content = str_replace(array_keys($data), array_values($data),$ob);
      $headers = 'From: wordpress@marketmasterclass.com' . "\r\n" .
      'Reply-To: wordpress@marketmasterclass.com' . "\r\n" .
      'Content-Type: text/html; charset=UTF-8' . "\r\n" .
      'X-Mailer: PHP/' . phpversion();
      print_r($content);
      // return mail($to, $subject, $content, $headers);
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
          `id` bigint(20) NOT NULL AUTO_INCREMENT,
          `user_id` bigint(20) NOT NULL,
          `fullname` varchar(100) NOT NULL,
          `token` varchar(64) NULL,
          `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `date_updated` TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP,
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
