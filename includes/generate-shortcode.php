<?php
  class GenerateShortcode {
    public function __construct(){
      $this->initshortcode();
    }

    public function initshortcode(){
      add_shortcode( 'CAF_LOGIN', [&$this,'generate_login_form'] );
      add_shortcode( 'CAF_REGISTER', [&$this,'generate_register_form'] );
      add_shortcode( 'CAF_SETPASSWORD', [&$this,'generate_setpassword_form'] );
      add_shortcode( 'CAF_RESETPASSWORD', [&$this,'generate_resetpassword_form'] );
      add_shortcode( 'CAF_SUCCESSVERIFICATION', [&$this,'generate_successverification_form'] );
    }
    public function generate_login_form(){
      if (is_user_logged_in())
        wp_redirect( home_url() );
      ?>
      <div class="col-md-6 col-md-offset-3">
        <div class="container_caf" id="container_caf_login">
          <?php if (!is_user_logged_in()) { ?>
          <div class="alert alert-mini alert-danger cont-status"></div>
          <form name="loginform" id="login" action="<?= site_url() ?>/accounts/login" method="post" autocomplete="off">
            <div class="form-group">
							<label>Username or Email</label>
							<label class="input">
								<i class="ico-append fa fa-envelope"></i>
								<input type="text" name="username" id="username" class="input form-control" required>
								<b class="tooltip tooltip-bottom-right">Needed to verify your account</b>
							</label>
						</div>
            <div class="form-group">
							<label>Password</label>
							<label class="input">
								<i class="ico-append fa fa-lock"></i>
								<input type="password" name="password" id="password" class="input form-control" required>
								<b class="tooltip tooltip-bottom-right">Type your account password</b>
							</label>
						</div>
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <label><input name="remember" type="checkbox" id="rememberme" checked="checked"> Remember Me</label>
              </div>
            </div>
            <div class="row">
							<div class="col-md-6 col-sm-6 col-xs-6">
								<div class="form-tip pt-20">
                  <a class="a-lostpassword" href="<?= site_url() ?>/forgot-password">Lost your password?</a>
								</div>
							</div>
							<div class="col-md-6 col-sm-6 col-xs-6 text-right">
                <button type="submit" name="wp-submit" id="submit" class="btn btn-primary">Log In</button>
							</div>
						</div>
            <p class="status alert"></p>
            <?php wp_nonce_field( 'ajax-login-nonce', 'security' ); ?>
          </form>
          <?php } else { ?>
            <a class="login_button btn btn-success" href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a>
          <?php } ?>
        </div>
      </div>
      <?php
    }

    public function generate_register_form(){
      if (is_user_logged_in())
        wp_redirect( home_url() );
      ?>
      <div class="col-md-6 col-md-offset-3">
        <div class="container_caf" id="container_caf_register">
          <div class="alert alert-mini alert-danger cont-status"></div>
          <p class="lbl-register">Register now, join the Divest Media team and become a member of our valued community in order to receive our daily newsletters.</p>
          <form name="registerform" id="form-register" action="<?= site_url() ?>/accounts/register" method="post">
            <div class="form-group">
							<label>Username</label>
							<label class="input">
								<i class="ico-append fa fa-user"></i>
								<input type="text" name="rg-username" id="rg-username" class="input form-control" required="required">
								<b class="tooltip tooltip-bottom-right">Your Username</b>
							</label>
						</div>
            <div class="form-group">
							<label>Full Name</label>
							<label class="input">
								<i class="ico-append fa fa-user"></i>
								<input type="text" name="rg-fullname" id="rg-fullname" class="input form-control" required="required">
								<b class="tooltip tooltip-bottom-right">Your Full Name</b>
							</label>
						</div>
            <div class="form-group">
							<label>Email</label>
							<label class="input">
								<i class="ico-append fa fa-envelope"></i>
								<input type="email" name="rg-email" id="rg-email" class="input form-control" required="required">
								<b class="tooltip tooltip-bottom-right">Your Email</b>
							</label>
						</div>
            <div><input name="rg-termsandconditions" type="checkbox" id="termsandconditions" required="required"> Agree with our <a href="<?= site_url() ?>/terms-and-conditions/">Terms & Conditions</a></div>
            <!-- <div><label for="rg-password">Password: </label> <input type="password" name="rg-password" id="rg-password" class="input form-control" required="required"></div> -->
            <div class="cont-btn"><button type="submit" class="btn btn-primary btn-register">Register</button></div>
            <p class="status alert"></p>
            <?php wp_nonce_field('ajax-register-nonce', 'registersecurity'); ?>
          </form>
        </div>
      </div>
      <?php
    }

    public function generate_setpassword_form(){
      global $tisvalid;
      $msg = '';
      if(!$tisvalid['status']){
        $msg = '<div class="alert alert-danger">Invalid verification token. To continue setting a password for your account, click <a href="#" class="msg-link"><b>here</b></a> to resend a new token to your email address ['.$tisvalid['email'].'].</div>';
      }
      if(is_user_logged_in()){
        wp_redirect( home_url() );
      }
    ?>
    <div class="col-md-6 col-md-offset-3">
      <div class="container_caf" id="container_caf_setpassword">
        <div class="alert alert-mini alert-danger cont-status"></div>
        <form name="setpasswordfrom" id="form-setpassword" action="<?= site_url() ?>/accounts/set-password" method="post">
          <?=$msg?>
          <div class="form-group">
            <label>Password</label>
            <label class="input">
              <i class="ico-append fa fa-lock"></i>
              <input type="password" name="sp-password" id="sp-password" class="input form-control" required="required" />
              <b class="tooltip tooltip-bottom-right">Min. 6 & Max. 15 characters</b>
            </label>
          </div>
          <div class="form-group">
            <label>Confirm Password</label>
            <label class="input">
              <i class="ico-append fa fa-lock"></i>
              <input type="password" name="sp-cpassword" id="sp-cpassword" class="input form-control" required="required" />
              <b class="tooltip tooltip-bottom-right">Type your password again</b>
            </label>
          </div>
          <!-- <div><label for="rg-username">Password: </label> </div>
          <div><label for="rg-username">Confirm Password: </label> </div> -->
          <div class="row">
            <div class="col-xs-12">
              <script src='https://www.google.com/recaptcha/api.js?hl=en&onload=reCaptchaCallback&render=explicit'></script>
              <script>
              var RC2KEY = '<?=get_option('caf_recaptcha_client_key')?>',
              doSubmit = false;
              function reCaptchaVerify(response) {
                if (response === document.querySelector('.g-recaptcha-response').value) {
                  jQuery('.btn-savepassword').prop('disabled',false);
                }
              }
              function reCaptchaExpired () {
                window.location.reload();
              }
              function reCaptchaCallback () {
                jQuery('.btn-savepassword').prop('disabled',true);
                grecaptcha.render('recaptcha', {
                  'sitekey': RC2KEY,
                  'callback': reCaptchaVerify,
                  'expired-callback': reCaptchaExpired
                });
              }
              </script>
              <div id="recaptcha"></div>

            </div>
          </div>
          <div class="cont-btn"><button type="submit" class="btn btn-primary btn-savepassword">Set Password</button></div>
          <p class="status alert"></p>
          <?php wp_nonce_field('ajax-setpassword-nonce', 'setpasswordsecurity'); ?>
          <input type="hidden" id="sp-confirm" name="sp-confirm" value="<?= esc_attr( $_GET['confirm'] )?>" />
          <input type="hidden" id="sp-email" name="sp-email" value="<?= esc_attr( $_GET['email'] )?>" />
        </form>
      </div>
    </div>
    <?php
    }

    public function generate_successverification_form(){
      if(empty($_GET['email'])||empty($_GET['confirm'])||is_user_logged_in()){
        wp_redirect( home_url() );
      }else{
        $link = site_url();
        $linkparts = parse_url($link);
        $link = rtrim($linkparts['scheme'] . '://' . $linkparts['host'] . $linkparts['path'] , '/') . '/accounts/set-password?' . http_build_query([
          'confirm' => $_GET['confirm'],
          'email' => $_GET['email']
        ]);
      }
      ?>
      <div class="col-md-8 col-md-offset-2">
        <div class="container_caf" id="container_caf_successverification">
          <div class="alert alert-success">You have succssfully verified your account. To set a password for your account click <a href="<?=$link?>">here</a>.</div>
        </div>
      </div>
      <?php
    }


    public function generate_resetpassword_form(){
      if(is_user_logged_in()){
        wp_redirect( home_url() );
      }
      ?>
        <div class="col-md-6 col-md-offset-3">
          <div class="container_caf" id="container_caf_resetpassword">
            <div class="alert alert-mini alert-danger cont-status"></div>
            <form name="setpasswordfrom" id="form-resetpassword" action="<?= site_url() ?>/accounts/forgot-password" method="post">
              <div>
                <input type="email" name="rp-email" id="rp-email" class="input form-control inp_forgot" required="required" placeholder="Email" />
                <button type="submit" class="btn btn-resetpassword">Reset Password</button>
              </div>
              <p class="status alert"></p>
              <?php wp_nonce_field('ajax-resetpassword-nonce', 'resetpasswordsecurity'); ?>
            </form>
          </div>
        </div>
      <?php
    }

  }
