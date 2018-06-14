<div class="wc_strike_payment">
  <h2>Please proceed to the payment</h2>
  <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
    <li class="woocommerce-order-overview__order order">Amount to pay with Lightning:<strong><br /><?php echo esc_html(number_format($amount_satoshi / (1000 * 100 * 1000), 8)) ?> BTC</strong></li>
    <li class="woocommerce-order-overview__order order">Scan this QR code with a Lightning wallet:<strong><br /><canvas id="wc_strike_canvas_paymentrequest"></canvas></strong></li>
    <li class="woocommerce-order-overview__order order">Or use the raw invoice:<strong><?php echo esc_html($payment_request) ?></strong></li>
    <li class="woocommerce-order-overview__order order">Looking for a lightning wallet?<strong><a href="https://acinq.co/">Visit this website</a></strong></li>
  </ul>
</div>

<script src="<?php echo WC_HTTPS::force_https_url(plugins_url('/assets/js/qrious.min.js', WC_STRIKE_MAIN_FILE)) ?>"></script>

<script>
(function($) {
  
  const ping_interval = 5 * 1000;
  new QRious({
    element: document.getElementById("wc_strike_canvas_paymentrequest"),
    value: "<?php echo $payment_request ?>",
    size: 200,
  });
  
  function poll_order_paid() {
    $.get('/?wc-api=WC_Gateway_Strike', { id: <?php echo $order->get_id() ?>, order_key: '<?php echo $order->get_order_key() ?>' })
    .success((code, state, res) => {
      if (res.responseJSON === true) {
        document.location = <?php echo json_encode($order->get_checkout_order_received_url()) ?>
      } else {
        setTimeout(poll_order_paid, ping_interval);
      }
    })
    .fail(res => {
      poll_order_paid();
    })
  }
  
  poll_order_paid();
})(jQuery);
</script>