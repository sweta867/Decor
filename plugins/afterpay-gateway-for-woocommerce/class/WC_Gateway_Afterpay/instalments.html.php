<?php
/**
 * Afterpay Checkout Instalments Display
 * @var WC_Gateway_Afterpay $this
 */

if ($this->settings['testmode'] != 'production') {
    ?><p class="afterpay-test-mode-warning-text"><?php _e( 'TEST MODE ENABLED', 'woo_afterpay' ); ?></p><?php
}

if ($this->get_js_locale() == 'fr_CA' || $currency == 'EUR') {
?>
    <div class="instalment-info-container" id="afterpay-checkout-instalment-info-container">
        <p class="header-text">
            <?php _e( 'Four ' . ($currency=='GBP'?'':'interest-free ') . 'payments totalling', 'woo_afterpay' ); ?>
            <strong><?php echo wc_price($order_total); ?></strong>
        </p>
        <div class="instalment-wrapper">
            <afterpay-price-table
                data-amount="<?php echo esc_attr($order_total); ?>"
                data-locale="<?php echo esc_attr($this->get_js_locale()); ?>"
                data-currency="<?php echo esc_attr($currency); ?>"
                data-price-table-theme="white"
            ></afterpay-price-table>
        </div>
    </div>
<?php
    wp_enqueue_script('afterpay_js_lib');
} else {
?>
    <div
        id="afterpay-widget-container"
        data-locale="<?php echo esc_attr($locale); ?>"
        data-amount="<?php echo esc_attr($order_total); ?>"
        data-currency="<?php echo esc_attr($currency); ?>">
    </div>
<?php
}
