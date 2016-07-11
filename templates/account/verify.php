<?php

global $wp_query;
$wp_query->set('posts_per_page', 1);
$wp_query->query($wp_query->query_vars);

add_filter('the_title', 'set_cus_title',1,0);
add_filter('wp_title', 'set_cus_title',1,0);

add_filter('the_content','tempcontent');
function set_cus_title(){
  return 'Verify';
}
function tempcontent(){
  ?>
<div class="container">
    <div class="text-center" style="margin-bottom:10px;margin-top:50px;"><img style="width:100%;max-width:472px;height:auto;" src="http://www.marketmasterclass.com/wp-content/themes/sage-8.4/dist/images/DivestMedia-Logo.png" class="login-image" alt="DivestMedia-Logo"></div>
    <?php do_shortcode('[CAF_SUCCESSVERIFICATION]');?>
</div>
<?php }

include( get_stylesheet_directory() . '/page.php');
?>
