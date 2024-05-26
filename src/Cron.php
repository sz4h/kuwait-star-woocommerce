<?php

namespace Sz4h\KuwaitStar;


use JetBrains\PhpStorm\NoReturn;

class Cron {

	private Logger $logger;

	public function __construct() {
		register_activation_hook( SPWKS_PATH . 'space-kuwait-star-woocommerce.php', [ $this, 'on_activation' ] );
		register_deactivation_hook( SPWKS_PATH . 'space-kuwait-star-woocommerce.php', [ $this, 'on_deactivation' ] );
		add_action( 'kuwait_star_update_stock_cron', [ $this, 'update_stock_cron' ] );
		add_action( 'kuwait_star_get_token_cron', [ $this, 'get_token_cron' ] );

		$this->logger = new Logger();
	}

	public function on_activation(): void {
		if ( ! wp_next_scheduled( 'kuwait_star_update_stock_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'kuwait_star_update_stock_cron' );
		}
		if ( ! wp_next_scheduled( 'kuwait_star_get_token_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'kuwait_star_get_token_cron' );
		}
	}

	function on_deactivation(): void {
		$timestamp = wp_next_scheduled( 'kuwait_star_update_stock_cron' );
		wp_unschedule_event( $timestamp, 'kuwait_star_update_stock_cron' );
		$timestamp = wp_next_scheduled( 'kuwait_star_get_token_cron' );
		wp_unschedule_event( $timestamp, 'kuwait_star_get_token_cron' );
	}

	#[NoReturn] public function update_stock_cron(): void {
		$products = $this->getProducts();
		if ( ! count( $products ) ) {
			exit;
		}
		$data = $this->apiCall( $products );

		foreach ( $data as $product ) {
			$currentProducts = $products[ $product->Id ];
			foreach ( $currentProducts as $id ) {
				$stock        = ( $product->inStock ) ? $product->quantity : '0';
				$stock_status = ( $product->inStock ) ? 'instock' : 'outofstock';
				update_post_meta( $id, '_stock_status', $stock_status );
				update_post_meta( $id, '_stock', $stock );
				echo "$id Done \n";
			}
		}
		exit;
	}

	#[NoReturn] public function get_token_cron(): void {
		try {
			kuwait_star_api()->login();
		} catch ( Exception\ApiException $e ) {
			$this->logger->log( error: $e->getMessage(), file: __FILE__, method: __METHOD__, line: __LINE__ );
		}
		exit;
	}


	/**
	 * @return array
	 */
	private function getProducts(): array {
		global $wpdb;

		$data     = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'sz4h_kuwait_star_id'" );
		$products = [];
		foreach ( $data as $product ) {
			$products[ $product->meta_value ][] = $product->post_id;
		}

		return $products;
	}

	private function apiCall( array $products ): array {
		$barcodes = array_keys( $products );
		$barcodes = array_map( 'floatval', $barcodes );
		$chunks   = array_chunk( $barcodes, 10 );
		$data     = [];
		foreach ( $chunks as $chunk ) {
			$barcodes      = implode( ',', $chunk );
			$responseArray = kuwait_star_api()->stock( $barcodes );
			$data          = array_merge_recursive( $data, $responseArray );
		}

		return $data;
	}
}