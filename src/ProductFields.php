<?php

namespace Sz4h\KuwaitStar;

class ProductFields {

	public function __construct() {
		$cmbInitPath = SPWKS_PATH . 'vendor/cmb2/cmb2/init.php';
		if ( file_exists( $cmbInitPath ) ) {
			require_once $cmbInitPath;
		}

		add_action( 'woocommerce_product_after_variable_attributes', [
			$this,
			'woocommerce_product_after_variable_attributes'
		], 10, 3 );
		add_action( 'woocommerce_product_options_pricing', [
			$this,
			'woocommerce_product_options_pricing'
		], 10, 3 );

		add_action( 'woocommerce_save_product_variation', [ $this, 'woocommerce_save_product_variation' ], 10, 2 );

		add_action('woocommerce_process_product_meta', [$this,'woocommerce_process_product_meta']);

	}

	public function woocommerce_process_product_meta( $post_id ): void {
		$key = 'sz4h_kuwait_star_id';
		$value = (!empty($_POST[$key]) ? wc_clean(wp_unslash($_POST[$key])) : '' ); // phpcs:ignore WordPress.Security.NonceVerification

		if ($value) {
			update_post_meta($post_id, $key, $value);
		} else {
			delete_post_meta($post_id, $key);
		}
	}

//	function cmb2_admin_init(): void {
//		$box = new_cmb2_box( [
//			'id'           => $this->_( 'kuwait_star_meta_box' ),
//			'title'        => esc_html__( 'Kuwait Star Meta box', SPWKS_TD ),
//			'object_types' => [ 'product' ],
//		] );
//		$box->add_field( [
//			'name' => esc_html__( 'Kuwait Star ID', SPWKS_TD ),
//			'id'   => $this->_( 'kuwait_star_id' ),
//			'type' => 'text',
//		] );
//	}


	public function _( string $string ): string {
		return "sz4h_$string";
	}

	public function woocommerce_product_after_variable_attributes( $loop, $variation_data ): void {
		echo '<div class="variation-custom-fields">';
		woocommerce_wp_text_input(
			array(
				'id'            => 'sz4h_kuwait_star_id',
				'label'         => __( 'Kuwait Star ID', SPWKS_TD ),
				'placeholder'   => '',
				//'desc_tip'    => true,
				'wrapper_class' => 'form-row form-row-full',
				//'description' => __( 'Enter the custom value here.', 'woocommerce' ),
				'value'         => @$variation_data['sz4h_kuwait_star_id'][0]
			)
		);
		echo '</div>';
	}
	public function woocommerce_product_options_pricing( ): void {
		global $post;

		$current = get_post_meta($post->ID, 'sz4h_kuwait_star_id', true);
		woocommerce_wp_text_input(
			array(
				'id'          => 'sz4h_kuwait_star_id',
				'value'       => @$current,
				'data_type'   => 'number',
				'label'       => __( 'Kuwait Star ID', SPWKS_TD ),
			)
		);
	}

	public function woocommerce_save_product_variation( $variation_id ): void {
		$id = stripslashes( $_POST['sz4h_kuwait_star_id'] );
		if ( is_numeric( $id ) || empty( $id) ) {
			update_post_meta( $variation_id, 'sz4h_kuwait_star_id', esc_attr( $id ) );
		}

	}
}