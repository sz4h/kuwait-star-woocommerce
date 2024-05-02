<?php /** @noinspection PhpUnusedParameterInspection */

namespace Sz4h\KuwaitStar;

use WC_Order_Item_Product;

class Woocommerce {


	private Logger $logger;

	public function __construct() {

		$this->logger = new Logger();
		/* Show in order details */
		add_action( 'woocommerce_order_item_meta_end', [ $this, 'woocommerce_order_item_meta_end' ], 20, 4 );
		add_action( 'woocommerce_after_order_details', [ $this, 'woocommerce_after_order_details' ], 20 );
	}


	public function woocommerce_order_item_meta_end( $item_id, WC_Order_Item_Product $item, $order, $bool = false ): void {
		$serials = $item->get_meta( 'serials' ) ?: null;
		if ( ! $serials || count( $serials ) == 0 ) {
			return;
		}
		include SPWKS_PATH . 'templates/kuwait-star-serial.php';
	}

	public function woocommerce_after_order_details(): void {
		include SPWKS_PATH . 'templates/kuwait-star-serial-js.php';
	}

}