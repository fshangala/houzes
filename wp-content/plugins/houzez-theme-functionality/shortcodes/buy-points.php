<?php
if(!function_exists('houzez_buy_credpoints')) {
  function houzez_buy_credpoints($atts) {
    echo do_shortcode( '[mycred_buy_form]');
    echo '<br/>';
    $current_user = wp_get_current_user();
    if($current_user->roles[0] == 'houzez_seller'){
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=1000]1000[/mycred_buy]');
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=3000]3000[/mycred_buy]');
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=5000]5000[/mycred_buy]');
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=10000]10000[/mycred_buy]');
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=20000]20000[/mycred_buy]');
    } elseif ($current_user->roles[0] == 'houzez_agent') {
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=1000]1000 + 100 FREE[/mycred_buy]');
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=3000]3000 + 400 FREE[/mycred_buy]');
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=5000]5000 + 600 FREE[/mycred_buy]');
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=10000]10000 + 1600 FREE[/mycred_buy]');
      echo do_shortcode( '[mycred_buy gateway="paypal-standard" amount=20000]20000 + 3500 FREE[/mycred_buy]');
    }
  }
  add_shortcode( 'houzez-buy-credpoints', 'houzez_buy_credpoints');
}
?>