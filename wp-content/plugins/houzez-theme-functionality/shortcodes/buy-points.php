<?php
if(!function_exists('houzez_render_plans')) {
  function houzez_render_plans($gateway,$plans) {
    foreach ($plans as $plan) {
      ?>
      <span class="p-2">
      <?php
      if($gateway == 'stripe'){
        echo do_shortcode( '[mycred_stripe_buy amount='.$plan[0].']'.$plan[1].'[/mycred_buy]');
      } else {
        echo do_shortcode( '[mycred_buy gateway="'.$gateway.'" amount='.$plan[0].']'.$plan[1].'[/mycred_buy]');
      }
      ?>
      </span>
      <?php
    }
  }
}
if(!function_exists('houzez_mycred_buy_per_gateway')){
  function houzez_mycred_buy_per_gateway(string $gateway) {
    $current_user = wp_get_current_user();
    if($current_user->roles[0] == 'houzez_seller'){
      $plans = [
        ['1000','1000'],
        ['3000','3000'],
        ['5000','5000'],
        ['10000','10000'],
        ['20000','20000']
      ];
      houzez_render_plans($gateway,$plans);
    } elseif ($current_user->roles[0] == 'houzez_agent') {
      $plans = [
        ['1000','1000 + 100 FREE'],
        ['3000','3000 + 400 FREE'],
        ['5000','5000 + 600 FREE'],
        ['10000','10000 + 1600 FREE'],
        ['20000','20000 + 3500 FREE']
      ];
      houzez_render_plans($gateway,$plans);
    }
  }
}
if(!function_exists('houzez_buy_credpoints')) {
  function houzez_buy_credpoints($atts) {
    echo do_shortcode( '[mycred_buy_form]');
    echo '<br/>';
    ?>
    <label>Payment Gateway</label>
    <select class="form-control" id="select-gateway" value="<?php echo $_GET['gateway'] ?? ''; ?>">
      <option value="no-gateway">No Gateway</option>
      <option value="stripe">Stripe</option>
      <option value="bank">Bank Transfer</option>
    </select><br>
    <script>
      document.querySelector('#select-gateway').addEventListener('change',()=>{
        if (event.value !== 'no-gateway') {
          window.location.assign("?gateway="+document.querySelector('#select-gateway').value);
        }
      });
    </script>
    <?php
    if(array_key_exists('gateway',$_GET)) {
      houzez_mycred_buy_per_gateway($_GET['gateway']);
    }
  }
  add_shortcode( 'houzez-buy-credpoints', 'houzez_buy_credpoints');
}
?>