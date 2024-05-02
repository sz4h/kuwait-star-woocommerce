<?php /** @noinspection PhpPropertyOnlyWrittenInspection */

namespace Sz4h\KuwaitStar;


class Initializer {


	private AdminSettings $admin_settings;
	private ProductFields $product_fields;
	private Woocommerce $woocommerce;
	private AdminNotice $admin_notice;
	private Order $order;
	private Api $api;

	public function __construct() {
		define( 'SPWKS_PATH', trailingslashit( plugin_dir_path( __DIR__ ) ) );
		define( 'SPWKS_URL', plugin_dir_url( __DIR__ ) );
		define( 'SPWKS_TD', 'space-kuwait-star-woocommerce' );
		add_action( 'plugins_loaded', [ $this, 'text_domain' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );

		$this->admin_settings = new AdminSettings();
		$this->product_fields = new ProductFields();
		$this->woocommerce = new Woocommerce();
		$this->admin_notice = new AdminNotice();
		$this->order = new Order();
		$this->api = new Api();
	}


	function text_domain(): void {
		load_plugin_textdomain( SPWKS_TD, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	function wp_enqueue_scripts(): void {
		if ( ! activate_plugin( 'woocommerce' ) ) {
			return;
		}
		if ( is_view_order_page() || is_order_received_page() ) {
			wp_enqueue_style( 'woocommerce-kuwait-star', SPWKS_URL . '/assets/css/woocommerce-kuwait-star.css', [], date( 'YmdHis' ) );
			wp_enqueue_style( 'line-awesome', 'https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css' );
		}
	}
}