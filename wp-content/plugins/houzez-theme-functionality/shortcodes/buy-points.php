<?php
if(!function_exists('houzez_render_plans')) {
  function houzez_render_plans($gateway,$plans,$customPoints) {
    ?>
    <p class="m-4">
      <?php
      if($gateway == 'stripe'){
        echo do_shortcode( '[mycred_stripe_buy amount='.$customPoints.']Buy '.$customPoints.'[/mycred_stripe_buy]');
      } else {
        echo do_shortcode( '[mycred_buy gateway="'.$gateway.'" amount='.$customPoints.']Buy '.$customPoints.'[/mycred_buy]');
      }
      ?>
    </p>
    <hr>
    <p>
    <?php
    foreach ($plans as $plan) {
      ?>
      <span class="p-2">
      <?php
      if($gateway == 'stripe'){
        echo do_shortcode( '[mycred_stripe_buy amount='.$plan[0].']'.$plan[1].'[/mycred_stripe_buy]');
      } else {
        echo do_shortcode( '[mycred_buy gateway="'.$gateway.'" amount='.$plan[0].']'.$plan[1].'[/mycred_buy]');
      }
      ?>
      </span>
      <?php
    }
    ?>
    </p>
    <?php
  }
}
if(!function_exists('houzez_mycred_buy_per_gateway')){
  function houzez_mycred_buy_per_gateway(string $gateway, string $customPoints) {
    $current_user = wp_get_current_user();
    if($current_user->roles[0] == 'houzez_seller'){
      $plans = [
        ['1000','1000'],
        ['3000','3000'],
        ['5000','5000'],
        ['10000','10000'],
        ['20000','20000']
      ];
      houzez_render_plans($gateway,$plans,$customPoints);
    } elseif ($current_user->roles[0] == 'houzez_agent') {
      $plans = [
        ['1000','1000 + 100 FREE'],
        ['3000','3000 + 400 FREE'],
        ['5000','5000 + 600 FREE'],
        ['10000','10000 + 1600 FREE'],
        ['20000','20000 + 3500 FREE']
      ];
      houzez_render_plans($gateway,$plans,$customPoints);
    } else {
      echo "User is neither seller nor agent. No button could be generated!";
    }
  }
}
if(!function_exists('houzez_buy_credpoints')) {
  function houzez_buy_credpoints($atts) {
    ?>
    <label>Payment Gateway</label>
    <select class="form-control" id="select-gateway" selected="<?php echo $_GET['gateway']; ?>">
      <option value="bank" <?php if(array_key_exists('gateway',$_GET) && $_GET['gateway']=="bank"){echo "selected";}?>>Bank Transfer</option>
      <option value="stripe" <?php if(array_key_exists('gateway',$_GET) && $_GET['gateway']=="stripe"){echo "selected";}?>>Stripe</option>
    </select><br>
    <input class="form-control" id="points-to-buy" type="number" value=100><br>
    <button type="button" onclick="generateButton();" class="btn btn-primary">Generate Payment Buttons</button><br>
    <script>
      function generateButton(){
        window.location.assign("?gateway="+document.querySelector('#select-gateway').value+"&points="+document.querySelector('#points-to-buy').value);
      }
    </script>
    <?php
    if(array_key_exists('gateway',$_GET) && array_key_exists('points',$_GET)) {
      houzez_mycred_buy_per_gateway($_GET['gateway'],$_GET['points']);
    } else {
      houzez_mycred_buy_per_gateway('bank',"100");
    }
  }
  add_shortcode( 'houzez-buy-credpoints', 'houzez_buy_credpoints');
}
?>