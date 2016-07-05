<?php
  if ( ! defined('ABSPATH')) exit; // if direct access

  function ajax_setpassword(){
    check_ajax_referer( 'ajax-register-nonce', 'security' );
    global $wpdb;
    $flag = 0;
    if(!empty($_POST['confirm'])&&!empty($_POST['email'])&&!empty($_POST['password'])){
      if(!strcasecmp($_POST['password'],$_POST['cpassword'])){
        $password = sanitize_text_field($_POST['password']);
        $tableusers = $wpdb->prefix . 'users';
        $table_name = $wpdb->prefix . 'user_details';
        $userid = $wpdb->get_row( ' SELECT u.`ID`,u.`user_login` FROM `'.$table_name.'` AS ud, `'.$tableusers.'` AS u WHERE ud.`token` = "'. sanitize_text_field( $_POST['confirm']) .'" AND u.`ID` = ud.`user_id` AND u.`user_email` = "'. sanitize_email( $_POST['email']) .'" ' );
        if(!empty($userid->ID)){
          $wpdb->query(' UPDATE `'.$table_name.'` SET `token` = "" WHERE `user_id` = "'. $userid->ID .'" ');
          wp_set_password($password,$userid->ID);
          auth_user_login($userid->user_login, $password, 'Setting password');
          $flag = 1;
        }
      }else{
        $flag = 2;
      }
    }
  }

  function sendConfirmationEmail($email,$token){
    $link = site_url();
    $to = $email;
    $subject = "Please verify your account";
    $linkparts = parse_url($link);
    $link = rtrim($linkparts['scheme'] . '://' . $linkparts['host'] . $linkparts['path'] , '/') . '/verify?' . http_build_query([
      'confirm' => $token,
      'email' => $email
    ]);
    $data = [
      "{link}" => $link,
      '{site_name}' => 'Divestmedia.com',
      '{site_email}' => 'help@divestmedia.com',
      '{btn_confirm_txt}' => 'Verify your Account'
    ];
    ob_start();
    include(CUSTOM_AUTH_FORM_DIR . 'templates/email-confirm.tpl');
    $ob = ob_get_clean();
    $content = str_replace(array_keys($data), array_values($data),$ob);
    $headers = 'From: wordpress@marketmasterclass.com' . "\r\n" .
    'Reply-To: wordpress@marketmasterclass.com' . "\r\n" .
    'Content-Type: text/html; charset=UTF-8' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
    print_r($content);
    // return mail($to, $subject, $content, $headers);
  }


  function ajax_login(){
    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-login-nonce', 'security' );

    // Nonce is checked, get the POST data and sign user on
    $info = array();
    $info['user_login'] = $_POST['username'];
    $info['user_password'] = $_POST['password'];
    $info['remember'] = !empty($_POST['remember']) && $_POST['remember']=='on' ? true : false;

    $user_signon = wp_signon( $info, false );
    if ( is_wp_error($user_signon) ){
        echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
        if (function_exists('w3tc_pgcache_flush')) {
            w3tc_pgcache_flush();
        }
        echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
    }
    die();
  }

  function ajax_register(){

    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-register-nonce', 'security' );
    if (!empty($_POST['username']) && !validate_username($_POST['username'])) {

    }else if(!empty($_POST['username'])){
      // Nonce is checked, get the POST data and sign user on
      $info = array();
      $info['user_nicename'] = $info['nickname'] = $info['display_name'] = $info['first_name'] = $info['user_login'] = sanitize_user($_POST['username']) ;
      // $info['user_pass'] = sanitize_text_field($_POST['password']);
      $info['user_email'] = sanitize_email( $_POST['email']);

      // Register the user
      $user_register = wp_insert_user( $info );

      if ( is_wp_error($user_register) ){
        $error  = $user_register->get_error_codes()	;

        if(in_array('empty_user_login', $error))
          echo json_encode(array('loggedin'=>false, 'message'=>__($user_register->get_error_message('empty_user_login'))));
        elseif(in_array('existing_user_login',$error))
          echo json_encode(array('loggedin'=>false, 'message'=>__('This username is already registered.')));
        elseif(in_array('existing_user_email',$error))
          echo json_encode(array('loggedin'=>false, 'message'=>__('This email address is already registered.')));
      } else {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_details';
        $token = wp_create_nonce($info['user_email']).md5($info['user_email']);
        $flag = $wpdb->query("INSERT into $table_name SET
             `user_id` = '". $user_register ."' ,
             `fullname` = '". sanitize_user($_POST['fullname']) ."',
             `token` = '".$token."'
             ");
         sendConfirmationEmail($info['user_email'],$token);
        //  auth_user_login($info['nickname'], $info['user_pass'], 'Registration');
        echo json_encode(array('status'=>$flag));
      }
      die();
    }
  }

  function auth_user_login($user_login, $password, $login)
  {
  	$info = array();
      $info['user_login'] = $user_login;
      $info['user_password'] = $password;
      $info['remember'] = true;

  	$user_signon = wp_signon( $info, false );
      if ( is_wp_error($user_signon) ){
  		    echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
      } else {
  		wp_set_current_user($user_signon->ID);
          echo json_encode(array('status'=>1,'loggedin'=>true, 'message'=>__($login.' successful, redirecting...')));
      }

  	die();
  }

  function ajax_login_init(){
      wp_enqueue_script('caf_script', CUSTOM_AUTH_FORM_URL.'assets/js/caf_script.js', ['jquery'], null );
      wp_localize_script( 'caf_script', 'ajax_auth_object', array(
          'ajaxurl' => admin_url( 'admin-ajax.php' ),
          'redirecturl' => home_url(),
          'loadingmessage' => __('Sending user info, please wait...')
      ));
      // Enable the user with no privileges to run ajax_login() in AJAX
      add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );

      add_action( 'wp_ajax_nopriv_ajaxregister', 'ajax_register' );
      add_action( 'wp_ajax_nopriv_ajaxsetpassword', 'ajax_setpassword' );
  }

  // Execute the action only if the user isn't logged in
  if (!is_user_logged_in()) {
      add_action('init', 'ajax_login_init');
  }
