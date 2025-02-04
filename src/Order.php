<?php /** @noinspection PhpNoReturnAttributeCanBeAddedInspection */

/** @noinspection PhpUnusedParameterInspection */

namespace Sz4h\KuwaitStar;

use Exception;
use Sz4h\KuwaitStar\Exception\ApiException;
use WC_Email_Admin_Customer_Completed_Order;
use WC_Order;
use WC_Order_Item;
use WC_Order_Item_Product;
use WC_Order_Refund;
use WC_Product;

class Order {


	private Logger $logger;

	public function __construct() {
		$this->logger = new Logger();
		add_filter('woocommerce_email_classes',[$this,'woocommerce_email_classes'] );
//		add_action( 'woocommerce_add_to_cart', [ $this, 'add_to_cart' ], 10, 6 );
//		add_action( 'woocommerce_pre_payment_complete', [ $this, 'woocommerce_payment_complete' ], 10, 2 );
		add_action( 'woocommerce_order_status_completed', [ $this, 'woocommerce_order_status_completed' ], 10, 2 );
	}

	/**
	 * @throws ApiException
	 * @throws Exception
	 */
	function add_to_cart( $cart_id, $productId, $quantity, $variation_id, $variation, $cart_item_data ): void {
		$product = wc_get_product( $productId );

		if ( $variation_id ) {
			$refId = (int) get_post_meta( $variation_id, 'sz4h_kuwait_star_id', true );
		} else {
			$refId = (int) $product->get_meta( 'sz4h_kuwait_star_id' );
		}
		if ( $refId === 0 ) {
			return;
		}
		$this->getProductsAvailability( [ $refId ], $product );
	}

	public function woocommerce_order_status_completed( $order_id, $transaction_id = null ): void {
		$order = wc_get_order( $order_id );

		if ( $order->get_meta( 'kuwait_star_completed' ) ) {
			return;
		}
		$items        = $order->get_items();
		$cardProducts = $this->getCardProducts( $items );
		if ( count( $cardProducts ) === 0 ) {
			$this->completeOrderKuwaitStarProcess( $order );

			return;
		}


		/* Check Cards Availability */
//		try {
//			$this->getProductsAvailability( array_keys( $cardProducts) );
//		} catch ( Exception $e ) {
//			$this->failed( [ $e->getMessage() ] );
//		}
		$createOrderResponse      = $this->createBulkOrder( $order, $cardProducts );
		$this->setItemsMeta( $order, $createOrderResponse );

		$items = $order->get_items();

		foreach ( $items as $item ) {

			$serials = $item->get_meta( 'serials' ) ?: [];

			foreach ( $serials as $serial ) {
				$order->add_order_note( sprintf( __( 'Code for %s is: %s and it\'s pin is %s', SPWKS_TD ), $item->get_name(), $serial->SN_VALUE, @$serial->PIN_VALUE ) );
			}
		}
		$this->completeOrderKuwaitStarProcess( $order );

		$email = WC()->mailer()->emails['WC_Email_Admin_Customer_Completed_Order'];
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		$email->trigger($order_id, $order);

	}


	protected function failed( array $errors, bool $refresh = false, bool $reload = false ): void {
		$errors     = '<li>' . implode( '</li><li>', $errors );
		$errorsHtml = "<ul class=\"woocommerce-error\" role=\"list\">\n\t\t\t$errors\n\t</ul>\n";
		echo json_encode( [
			'result'   => 'failure',
			'messages' => $errorsHtml,
			'refresh'  => $refresh,
			'reload'   => $reload,
		] );
		die();
	}

	/**
	 * @param array $ids
	 * @param bool|WC_Product|null $product
	 *
	 * @return void
	 * @throws ApiException
	 */
	public function getProductsAvailability( array $ids, bool|null|WC_Product $product = null ): void {
		$response = kuwait_star_api()->request( url: 'rest/en/V1/product/status', params: [
			'products_ids' => implode( ',', $ids )
		] );


		$ids = [];
		foreach ( $response as $p ) {
			if ( ! $p?->inStock || $p?->quantity <= 0 ) {
				$ids[] = $p?->Id;
			}
		}

		if ( count( $ids ) ) {
			$ids      = implode( ',', $ids );
			$response = json_encode( $response );
			$error    = "{$product?->get_name()} can't be ordered with ids $ids \n Response: $response";
			$this->logger->log( error: $error, file: __FILE__, method: __METHOD__, line: __LINE__ );
			throw new ApiException( __( 'Error in ordering', SPWKS_TD ) . ' (' . $ids . ').' );
		}
	}


	function getCardProducts( array $items ): array {
		$products = [];

		foreach ( $items as $item ) {
			/** @var WC_Order_Item_Product $item */
			$productId = $this->getKuwaitStarId( $item );
			if ( ! $productId ) {
				continue;
			}
			$products[ $productId ] = [
//				'productId'      => $productId,
//				'storeProductId' => $this->getOriginalItemId( $item ),
				'qty' => $item->get_quantity(),
			];
		}

		return $products;
	}

	private function getKuwaitStarId( WC_Order_Item_Product|WC_Order_Item $item ): ?int {
		$productId = $this->getOriginalItemId( $item );
		$refId     = (int) get_post_meta( $productId, 'sz4h_kuwait_star_id', true );

		return $refId !== 0 ? $refId : null;
	}

	/**
	 * @param WC_Order|bool $order
	 * @param array $cardProducts
	 *
	 * @return mixed
	 */
	public function createBulkOrder( WC_Order|bool $order, array $cardProducts ): string {
		$response = kuwait_star_api()->order( $order, $cardProducts );
//		$order->update_meta_data( 'kuwait_star_ref_order_id', $response );
//		$order->save_meta_data();

		return $response;
	}


	/**
	 * @param WC_Order|bool $order
	 * @param mixed $createOrderResponse
	 *
	 * @return void
	 */
	public function setItemsMeta( WC_Order|bool $order, mixed $createOrderResponse ): void {
		$serials  = [];
//		foreach ( kuwait_star_api()->order_details( $createOrderResponse ) as $ordered ) {
		foreach ( $createOrderResponse as $ordered ) {
			$serials[ $ordered->id ] = $ordered->serials;
		}
		foreach ( $order->get_items() as $item ) {
			/** @var WC_Order_Item_Product $item */
			$product = $item->get_product();
			$refId   = $product->get_meta( 'sz4h_kuwait_star_id' );
			$item->update_meta_data( 'serials', @$serials[ $refId ] );
			$item->save_meta_data();
		}
	}


	/**
	 * @param WC_Order_Item_Product|WC_Order_Item $item
	 *
	 * @return int
	 */
	public function getOriginalItemId( WC_Order_Item_Product|WC_Order_Item $item ): int {
		return $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
	}

	/**
	 * @param WC_Order|bool|WC_Order_Refund $order
	 *
	 * @return void
	 */
	public function completeOrderKuwaitStarProcess( WC_Order|bool|WC_Order_Refund $order ): void {
		$order->update_meta_data( 'kuwait_star_completed', 1 );
		$order->save_meta_data();
	}

	public function woocommerce_email_classes( array $email_classes ): array {
		require_once SPWKS_PATH . 'mail/class-wc-email-admin-customer-completed-order.php';
		$email_classes['WC_Email_Admin_Customer_Completed_Order'] = new WC_Email_Admin_Customer_Completed_Order();
		return $email_classes;
	}
}