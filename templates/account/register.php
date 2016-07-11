<?php
add_filter('the_content','tempcontent');

function tempcontent(){
  ?>
<div class="container">
    <div class="text-center" style="margin-bottom:10px;margin-top:50px;"><img style="width:100%;max-width:472px;height:auto;" src="http://www.marketmasterclass.com/wp-content/themes/sage-8.4/dist/images/DivestMedia-Logo.png" class="login-image" alt="DivestMedia-Logo"></div>
    <?php do_shortcode('[CAF_REGISTER]');?>
</div>
<?php }
include( get_stylesheet_directory() . '/page.php');
?>
