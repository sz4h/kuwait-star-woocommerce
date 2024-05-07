<?php /** @noinspection PhpUnusedParameterInspection */

namespace Sz4h\KuwaitStar;

use WC_Order_Item_Product;

class Woocommerce {


	public function __construct() {

		/* Show in order details */
//		add_action( 'woocommerce_order_item_meta_end', [ $this, 'woocommerce_order_item_meta_end' ], 20, 4 );
		add_action( 'woocommerce_after_order_details', [ $this, 'woocommerce_after_order_details' ], 20 );
		add_action( 'woocommerce_after_template_part', [ $this, 'woocommerce_after_template_part' ], 10, 4 );
	}

	/*
		public function woocommerce_order_item_meta_end( $item_id, WC_Order_Item_Product $item, $order, $bool = false ): void {
			$this->show_serial( $item );
		}*/

	public function woocommerce_after_order_details(): void {
		include SPWKS_PATH . 'templates/kuwait-star-serial-js.php';
	}


	public function woocommerce_after_template_part( $template_name, $template_path, $located, $args ): void {
		if ( $template_name === 'order/order-details-item.php' ) {
			/** @var \Automattic\WooCommerce\Admin\Overrides\Order $order */
			$item = @$args['item'];

			echo '<tr><td colspan="2">';
			$this->show_serial( $item );
			echo '</td></tr>';
		} elseif ( in_array( $template_name, [
			'emails/plain/email-order-items.php',
			'emails/email-order-items.php'
		] ) ) {
			foreach ( @$args['items'] as $item ) {
				echo '<tr><td colspan="3">';
				$this->show_serial( $item, 'in-mail' );
				echo '</td></tr>';
			}
		}
	}

	public function show_serial( $item, $wrapper_class = 'in-web' ): void {
		$serials = $item?->get_meta( 'serials' ) ?: null;
		if ( ! $serials || count( $serials ) == 0 ) {
			return;
		}
		if ( $wrapper_class === 'in-web' ) {
			include SPWKS_PATH . 'templates/kuwait-star-serial.php';
		} else {
			include SPWKS_PATH . 'templates/kuwait-star-serial-email.php';
		}
	}

}