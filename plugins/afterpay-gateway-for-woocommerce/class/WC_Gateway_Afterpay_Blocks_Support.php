<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Afterpay payment method integration
 *
 * @since 3.4.0
 */
final class WC_Gateway_Afterpay_Blocks_Support extends AbstractPaymentMethodType {
	/**
	 * Name of the payment method.
	 *
	 * @var string
	 */
	protected $name = 'afterpay';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_afterpay_settings', [] );
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		$payment_gateways_class   = WC()->payment_gateways();
		$payment_gateways         = $payment_gateways_class->payment_gateways();

		return array_key_exists('afterpay', $payment_gateways)
			&& $payment_gateways['afterpay']->is_available_for_blocks();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$asset_path   = WC_GATEWAY_AFTERPAY_PATH . '/build/afterpay-blocks.asset.php';
		$version      = Afterpay_Plugin::$version;
		$dependencies = [];
		if ( file_exists( $asset_path ) ) {
			$asset        = require $asset_path;
			$version      = is_array( $asset ) && isset( $asset['version'] )
				? $asset['version']
				: $version;
			$dependencies = is_array( $asset ) && isset( $asset['dependencies'] )
				? $asset['dependencies']
				: $dependencies;
		}
		wp_register_script(
			'wc-afterpay-blocks-integration',
			WC_GATEWAY_AFTERPAY_URL . '/build/afterpay-blocks.js',
			$dependencies,
			$version,
			true
		);
		return [ 'wc-afterpay-blocks-integration' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$instance = WC_Gateway_Afterpay::getInstance();
		$locale = $instance->get_js_locale();
		$currency = get_woocommerce_currency();
		if ($locale == 'fr_CA' || $currency == 'EUR') {
			// Use JS Lib
			wp_enqueue_script('afterpay_js_lib');
		} else {
			// Use AfterPay Widgets
			$locale = 'en-' . $instance->get_country_code();
			wp_enqueue_script('afterpay_express_lib');
		}
		wp_enqueue_style( 'afterpay_css' );
		return [
			'min' => $instance->getOrderLimitMin(),
			'max' => $instance->getOrderLimitMax(),
			'logo_url' => $instance->get_static_url() . 'integration/checkout/logo-afterpay-colour-120x25.png',
			'testmode' => $this->get_setting('testmode'),
			'locale' => $locale,
			'supports' => $this->get_supported_features()
		];
	}

	/**
	 * Returns an array of supported features.
	 *
	 * @return string[]
	 */
	public function get_supported_features() {
		$features = [];
		$payment_gateways = WC()->payment_gateways->payment_gateways();
		if (array_key_exists('afterpay', $payment_gateways)) {
			$features = $payment_gateways['afterpay']->supports;
		}
		return $features;
	}
}
