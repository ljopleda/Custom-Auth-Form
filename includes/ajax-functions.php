<?php
  if ( ! defined('ABSPATH')) exit; // if direct access

  class AjaxFunctions extends CustomAuthForm{
    public function __construct(){
      // Execute the action only if the user isn't logged in
      if (!is_user_logged_in()) {
          add_action('init', [$this,'ajax_login_init']);
      }
    }

    public function ajax_login_init(){
        wp_enqueue_script('caf_script', CUSTOM_AUTH_FORM_URL.'assets/js/caf_script.js', ['jquery'], null );
        wp_localize_script( 'caf_script', 'ajax_auth_object', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'redirecturl' => home_url(),
            'loadingmessage' => __('Sending user info, please wait...')
        ));
        // Enable the user with no privileges to run ajax_login(), ajax_register(), ajax_setpassword() and ajax_resetpassword() in AJAX
        add_action( 'wp_ajax_nopriv_ajaxlogin', [$this,'ajax_login'] );
        add_action( 'wp_ajax_nopriv_ajaxregister', [$this,'ajax_register'] );
        add_action( 'wp_ajax_nopriv_ajaxsetpassword', [$this,'ajax_setpassword'] );
        add_action( 'wp_ajax_nopriv_ajaxresetpassword', [$this,'ajax_resetpassword'] );
    }

    public function ajax_resetpassword(){
      check_ajax_referer( 'ajax-resetpassword-nonce', 'security' );
      $flag = 0;
      if(!empty($_POST['email'])&&is_email($_POST['email'])){
        global $wpdb;
        $table_users = $wpdb->prefix . 'users';
        $user = $wpdb->get_row( 'SELECT `ID` FROM '.$table_users.' WHERE `user_email` = "'.sanitize_email( $_POST['email']).'"' );
        if(!empty($user->ID)){
          $token = wp_create_nonce($_POST['email']).md5($_POST['email']);
          $table_details = $wpdb->prefix . 'user_details';
          $wpdb->query(' UPDATE '.$table_details.' SET `token` = "'. $token .'" WHERE `user_id` = "'. $user->ID .'" ');
          $subject = "Lost password reset";
          $buttontxt = "Reset your password";
          $template = "reset-password";
          $page = '/account/set-password?' . http_build_query([
            'confirm' => $token,
            'email' => $_POST['email']
          ]);
          $this->sendConfirmationEmail($_POST['user_email'],$token,$subject,$buttontxt,$template,$page);
          $flag = 1;
          echo json_encode(array('status'=>$flag,'message'=>__('A password reset email has been sent to your email ['.$_POST['user_email'].'].')));
        }else{
          echo json_encode(array('status'=>$flag));
        }
      }else{
        echo json_encode(array('status'=>$flag));
      }
    }

    public function ajax_setpassword(){
      check_ajax_referer( 'ajax-setpassword-nonce', 'security' );
      $flag = 0;
      if(!empty($_POST['confirm'])&&!empty($_POST['email'])&&!empty($_POST['password'])&&!empty($_POST['recaptcha'])){
        $secret = get_option('subscribenow_recaptcha_site_key');
        // $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['recaptcha']);
        // $responseData = json_decode($verifyResponse);
        // if(!$responseData->success){
        //   echo json_encode(array('status'=>$flag, 'message'=>__('Invalid Captcha code.')));
        //   die();
        // }

        if(!strcasecmp($_POST['password'],$_POST['cpassword'])){
          global $wpdb;
          $password = sanitize_text_field($_POST['password']);
          $table_users = $wpdb->prefix . 'users';
          $table_details = $wpdb->prefix . 'user_details';
          $user = $wpdb->get_row( ' SELECT u.`ID`,u.`user_login` FROM `'.$table_details.'` AS ud, `'.$table_users.'` AS u WHERE ud.`token` = "'. sanitize_text_field( $_POST['confirm']) .'" AND u.`ID` = ud.`user_id` AND u.`user_email` = "'. sanitize_email( $_POST['email']) .'" ' );
          if(!empty($user->ID)){
            $wpdb->query(' UPDATE `'.$table_details.'` SET `token` = "" WHERE `user_id` = "'. $user->ID .'" ');
            wp_set_password($password,$user->ID);
            $this->auth_user_login($user->user_login, $password, 'Setting password');
            $flag = 1;
          }
        }else{
          $flag = 2;
        }
      }
    }

    public function ajax_login(){
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

    public function ajax_register(){

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
           $subject = "Please verify your account";
           $buttontxt = "Verify your Account";
           $template = "email-confirm";
           $page = '/account/verify?' . http_build_query([
             'confirm' => $token,
             'email' => $info['user_email']
           ]);
           $this->sendConfirmationEmail($info['user_email'],$token,$subject,$buttontxt,$template,$page);
          //  auth_user_login($info['nickname'], $info['user_pass'], 'Registration');
          echo json_encode(array('status'=>$flag));
        }
        die();
      }
    }

    public function auth_user_login($user_login, $password, $login){
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
  }
