<?php

namespace Sz4h\KuwaitStar;

use Exception;
use WC_Order;
use WC_Order_Item_Product;
use WC_Order_Query;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

class Api extends WP_REST_Controller {

	// Here initialize our namespace and resource name.
	public string $resource_name;

	public function __construct() {
		$this->namespace     = 'sz4h/v1';
		$this->resource_name = 'api';
		add_filter('woocommerce_rest_prepare_shop_order_object', [$this,'woocommerce_rest_prepare_shop_order_object'], 10, 3);
		add_action( 'rest_api_init', [$this,'rest_api_init'] );
	}

	public function register_routes(): void {
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/' . 'orders', array(
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'searchOrders' ),
				'permission_callback' => array( $this, 'get_permissions' ),
			),
		) );
	}

	public function get_permissions(): bool|WP_Error {
		if ( ! wc_rest_check_post_permissions( 'shop_order' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ),
				array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	function woocommerce_rest_prepare_shop_order_object( $response, $order, $request ) {
		if ( isset($response->data) && str_contains( $request->get_route(), '/wc/v3/orders' ) ) {
			/** @var WC_Order $order */
			foreach ($response->data['line_items'] as $k => $item) {
				foreach ($item['meta_data'] as $metas) {
					if ($metas['key'] == 'serials')
						$response->data['line_items'][$k]['serials'] = $metas['value'];
				}
			}
		}

		return $response;
	}
	/**
	 * Grabs the five most recent posts and outputs them as a rest response.
	 *
	 * @param WP_REST_Request $request Current request.
	 *
	 * @throws Exception
	 */
	public function searchOrders( WP_REST_Request $request ): WP_Error|WP_REST_Response|WP_HTTP_Response {
		$args                   = [];
		$phone                  = $request->get_param( 'phone' );
		$email                  = $request->get_param( 'email' );
		$country                = $request->get_param( 'country' );
		$name                   = $request->get_param( 'name' );
		$post_status            = $request->get_param( 'post_status' );
		$args['posts_per_page'] = $request->get_param( 'per_page' ) ?? 10;
		$args['page']           = $request->get_param( 'page' ) ?? 1;
		$args['orderby']        = $request->get_param( 'orderby' ) ?? 'id';
		$args['order']          = $request->get_param( 'order' ) ?? 'desc';
		$args['status']         = $request->get_param( 'status' ) ?? 'processing';

		if ( $phone ) {
			$args['billing_phone'] = $phone;
		}
		if ( $email ) {
			$args['billing_email'] = $email;
		}
		if ( $country ) {
			$args['billing_country'] = $country;
		}
		if ( $name ) {
			$args['billing_first_name'] = $name;
		}
		if ( $post_status ) {
			$args['post_status'] = $post_status;
		}
		$query  = new WC_Order_Query( $args );
		$orders = $query->get_orders();
		$data   = [];
		/** @var WC_Order $order */
		foreach ( $orders as $order ) {
			$products = [];
			$weight   = 0;
			foreach ( $order->get_items() as $item ) {
				/* @var WC_Order_Item_Product $item */
				$weight     += $item->get_quantity() * $item->get_product()?->get_weight();
				$products[] = [
					'id'         => $item->get_id(),
					'photo'      => $item->get_product()->get_image(),
					'product_id' => $item->get_product_id(),
					'name'       => $item->get_name(),
					'quantity'   => $item->get_quantity(),
					'total'      => base_price( $item->get_total(), $order->get_currency() ),
					'subtotal'   => base_price( $item->get_subtotal(), $order->get_currency() ),
					'serials'    => $item->get_meta( 'serials' ),
				];
			}

			$data[] = [
				'id'                => $order->get_id(),
				'name'              => $order->get_shipping_first_name(),
				'email'             => $order->get_billing_email(),
				'phone'             => $order->get_billing_phone(),
				'total'             => base_price( $order->get_total(), $order->get_currency() ),
				'address'           => $order->get_shipping_address_1(),
				'city'              => $order->get_shipping_city(),
				'state'             => $order->get_shipping_state(),
				'country'           => $order->get_shipping_country(),
				'items'             => $products,
				'weight'            => $weight,
				'number_of_pieces'  => $order->get_item_count(),
				'tracking_provider' => $order->get_meta( '_wc_shipment_tracking_items' )?->tracking_provider,
				'tracking_number'   => $order->get_meta( '_wc_shipment_tracking_items' )?->tracking_number,
				'post_status'       => $order->get_status(),
				'currency'          => $order->get_currency(),
				'subtotal'          => $order->get_subtotal(),
				'shipping'          => $order->get_shipping_total(),
				'discount'          => $order->get_discount_total(),
				'date'              => date( 'Y-m-d H:i:s', $order->get_date_created()?->getTimestamp() ),
			];
		}

		return rest_ensure_response( $data );
	}


	function rest_api_init(): void {
		$controller = new self();
		$controller->register_routes();
	}

}

