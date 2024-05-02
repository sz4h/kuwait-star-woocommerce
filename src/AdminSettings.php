<?php

namespace Sz4h\KuwaitStar;

class AdminSettings {


	public function __construct() {
		add_action( 'cmb2_admin_init', [ $this, 'cmb2_admin_init' ] );
		add_action( 'cmb2_before_form', [ $this, 'cmb2_before_form' ], 0, 4 );
	}

	public function cmb2_before_form( $id, $object_id, $object_type, $class ): void {
		if ( $id !== 'sz4h_kuwait_star_options' ) {
			return;
		}
		$credit = get_transient( 'kuwait_star_credit' );
		if (!$credit) {
			$credit = kuwait_star_api()->credit();
			set_transient( 'kuwait_star_credit', $credit, 60 * 60 );
		}
		include_once SPWKS_PATH . 'templates/admin-credit.php';
	}

	function cmb2_admin_init(): void {
		/**
		 * Registers options page menu item and form.
		 */
		$cmb_options = new_cmb2_box( array(
			'id'           => 'sz4h_kuwait_star_options',
			'title'        => esc_html__( 'Kuwait Star', SPWKS_TD ),
			'object_types' => array( 'options-page' ),

			/*
			 * The following parameters are specific to the options-page box
			 * Several of these parameters are passed along to add_menu_page()/add_submenu_page().
			 */

			'option_key'  => 'kuwait_star_options',
			// The option key and admin menu page slug.
			'parent_slug' => 'options-general.php',
			// Make options page a submenu item of the theme's menu.
			'capability'  => 'manage_options',
			// Cap required to view options-page.
			// 'position'        => 1, // Menu position. Only applicable if 'parent_slug' is left empty.
			// 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
			// 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
		) );

		/*
		 * Options fields ids only need
		 * to be unique within this box.
		 * Prefix is not needed.
		 */

		$cmb_options->add_field( array(
			'name'    => __( 'Email', SPWKS_TD ),
			'id'      => 'email',
			'type'    => 'text',
			'default' => 'janedoe@example.com',
		) );

		$cmb_options->add_field( array(
			'name'    => __( 'Password', SPWKS_TD ),
			'id'      => 'password',
			'type'    => 'text',
			'default' => 'Password1',
		) );

		$cmb_options->add_field( array(
			'name'    => __( 'Domain', SPWKS_TD ),
			'id'      => 'domain',
			'type'    => 'text',
			'default' => '',
		) );
		$cmb_options->add_field( array(
			'name'    => __( 'Alert me when credit reach', SPWKS_TD ),
			'id'      => 'alert_threshold',
			'type'    => 'text',
			'default' => '10',
		) );

	}
}