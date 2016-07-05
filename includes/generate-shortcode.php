<?php

  function generate_login_form(){
    if (is_user_logged_in())
      wp_redirect( home_url() );
    ?>
    <div class="container container_caf" id="container_caf_login">
      <?php if (!is_user_logged_in()) { ?>
      <form name="loginform" id="login" action="<?= site_url() ?>/login" method="post">
        <div class="login-input">
          <label for="user_login">Username or Email</label>
          <input type="text" name="username" id="username" class="input form-control" value="" size="20">
        </div>
        <div class="login-input">
          <label for="user_pass">Password</label>
          <input type="password" name="password" id="password" class="input form-control" value="" size="20">
        </div>
        <div class="login-remember cont-btn">
          <label><input name="remember" type="checkbox" id="rememberme" checked="checked"> Remember Me</label>
          <button type="submit" name="wp-submit" id="submit" class="submit_button">Log In</button>
        </div>
        <p class="status alert"></p>
        <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
      </form>
      <?php } else { ?>
        <a class="login_button btn btn-success" href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a>
      <?php } ?>
    </div>
    <?php
  }

  function generate_register_form(){
    if (is_user_logged_in())
      wp_redirect( home_url() );
    ?>
    <div class="container container_caf" id="container_caf_register">
      <p class="lbl-register">Register now, join the Divest Media team and become a member of our valued community in order to receive our daily newsletters.</p>
      <form name="registerform" id="form-register" action="<?= site_url() ?>/register" method="post">
        <div><label for="rg-username">Username: </label> <input type="text" name="rg-username" id="rg-username" class="input form-control" required="required"></div>
        <div><label for="rg-fullname">Full Name: </label> <input type="text" name="rg-fullname" id="rg-fullname" class="input form-control" required="required"></div>
        <div><label for="rg-email">E-mail Address: </label> <input type="email" name="rg-email" id="rg-email" class="input form-control" required="required"></div>
        <!-- <div><label for="rg-password">Password: </label> <input type="password" name="rg-password" id="rg-password" class="input form-control" required="required"></div> -->
        <div class="cont-btn"><button type="submit" class="btn-register">Register</button></div>
        <p class="status alert"></p>
        <?php wp_nonce_field('ajax-register-nonce', 'registersecurity'); ?>
      </form>
    </div>
    <?php
  }

  function generate_setpassword_form(){
    if(empty($_GET['email'])||empty($_GET['confirm'])||is_user_logged_in()){
      wp_redirect( home_url() );
    }
  ?>
    <div class="container container_caf" id="container_caf_setpassword">
      <form name="setpasswordfrom" id="form-setpassword" action="<?= site_url() ?>/set-password" method="post">
        <div><label for="rg-username">Password: </label> <input type="password" name="sp-password" id="sp-password" class="input form-control" required="required" /></div>
        <div><label for="rg-username">Confirm Password: </label> <input type="password" name="sp-cpassword" id="sp-cpassword" class="input form-control" required="required" /></div>
        <div class="cont-btn"><button type="submit" class="btn btn-savepassword">Set Password</button></div>
        <p class="status alert"></p>
        <?php wp_nonce_field('ajax-register-nonce', 'setpasswordsecurity'); ?>
        <input type="hidden" id="sp-confirm" name="sp-confirm" value="<?= esc_attr( $_GET['confirm'] )?>" />
        <input type="hidden" id="sp-email" name="sp-email" value="<?= esc_attr( $_GET['email'] )?>" />
      </form>
    </div>
  <?php
  }

  function generate_successverification_form(){
    if(empty($_GET['email'])||empty($_GET['confirm'])||is_user_logged_in()){
      wp_redirect( home_url() );
    }else{
      $link = site_url();
      $linkparts = parse_url($link);
      $link = rtrim($linkparts['scheme'] . '://' . $linkparts['host'] . $linkparts['path'] , '/') . '/set-password?' . http_build_query([
        'confirm' => $_GET['confirm'],
        'email' => $_GET['email']
      ]);
    }
    ?>
      <div class="container container_caf" id="container_caf_successverification">
        <div class="alert alert-success">You have succssfully verified your account. To set a password for your account click <a href="<?=$link?>">here</a>.</div>
      </div>
    <?php
  }

  add_shortcode( 'CAF_LOGIN', 'generate_login_form' );
  add_shortcode( 'CAF_REGISTER', 'generate_register_form' );
  add_shortcode( 'CAF_SETPASSWORD', 'generate_setpassword_form' );
  add_shortcode( 'CAF_SUCCESSVERIFICATION', 'generate_successverification_form' );
