<?php

  class CustomTemplate{
    public function __construct(){
      self::custom_template_init();
    }

    public function custom_template_init(){
      add_filter('rewrite_rules_array',[$this,'rewriteRules']);
      add_filter( 'template_include', [ $this, 'template_include' ],1,1 );
      add_filter( 'query_vars', [ $this, 'prefix_register_query_var' ]);
    }

    public function prefix_register_query_var($vars){
      $vars[] = 'cl';
      $vars[] = 'par';
      $vars[] = 'tem';
      return $vars;
    }

    public function rewriteRules($rules){
  		$newrules = self::rewrite();
  		return $newrules + $rules;
  	}

  	public function rewrite(){
  		$newrules = array();

  		$newrules['account/(.*)$'] = 'index.php?cl=user&par=account&tem=$matches[1]';
  		$newrules['account'] = 'index.php?cl=user&par=account&tem=index';

  		return $newrules;
  	}

  	public function removeRules($rules){
  		$newrules = self::rewrite();
  		foreach ($newrules as $rule => $rewrite) {
  	        unset($rules[$rule]);
  	    }
  		return $rules;
  	}

  	public function template_include($template){
  		$_templateDir = CUSTOM_AUTH_FORM_DIR . 'templates';
  		$_class = get_query_var( 'cl' );
  		$_parent = get_query_var('par');
  		$_template = get_query_var('tem');
  		if($_class == "user"){
  			switch ($_parent){
  				case 'account':
  					$_access = $_templateDir . '/'. $_parent .'/'. $_template .'.php';
  					if(file_exists($_access)){
              $_template_wtoken = ['set-password','verify'];
              if(in_array($_template,$_template_wtoken)){
                global $wpdb;
                $userstable = $wpdb->prefix . 'users';
                $userdetailstable = $wpdb->prefix . 'user_details';
                $isvalid = $wpdb->get_row(' SELECT ud.date_created FROM '.$userstable.' AS u, '.$userdetailstable.' AS ud WHERE u.ID = ud.user_id AND ud.token = "'. sanitize_text_field($_GET['confirm']) .'" AND u.user_email = "'. sanitize_email($_GET['email']) .'" ');

                if(empty($isvalid->date_created)){
                  wp_redirect( home_url() );
                  exit();
                }else{
                  global $tisvalid;
                  $cur_time = time();
                  $exp_date = strtotime($isvalid->date_created . ' +1 day');
                  // if($exp_date<$cur_time){
                    $tisvalid = ['status'=>true,'email'=>$_GET['email']];
                  // }else{
                    // $tisvalid = ['status'=>false,'email'=>$_GET['email']];
                  // }
                }

              }
  						return $_access;
  					}
  					if(empty($_template))
  						return $_templateDir . '/'. $_parent .'/index.php';
  					return $_templateDir . '/'. $_parent .'/404.php';
          break;
  				default: echo "Template not found"; break;
  			}
  		}
  		return $template;
  	}
  }
