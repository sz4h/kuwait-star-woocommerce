<?php

namespace Sz4h\KuwaitStar;

use CMB2;

class AdminSettings {


	public function __construct() {
		add_action( 'cmb2_admin_init', [ $this, 'cmb2_admin_init' ] );
		add_action( 'cmb2_before_form', [ $this, 'cmb2_before_form' ], 0, 4 );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'cmb2_save_options-page_fields_sz4h_kuwait_star_options', [ $this, 'onSaveSetting' ], 10, 2 );

	}

	public function cmb2_before_form( $id ): void {
		if ( $id !== 'sz4h_kuwait_star_options' ) {
			return;
		}
		$options = get_option( 'kuwait_star_options' );
		if ( ! @$options['email'] || ! @$options['password'] || ! @$options['domain'] ) {
			return;
		}
		$credit = get_transient( 'kuwait_star_credit' );
		if ( ! $credit ) {
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
			'parent_slug' => 'kuwait_star',
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
			'name'       => __( 'Password', SPWKS_TD ),
			'id'         => 'password',
			'type'       => 'text',
			'attributes' => array(
				'type' => 'password',
			),
			'default'    => 'Password1',
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

	public function admin_menu(): void {
		add_menu_page( __( 'Kuwait Star', SPWKS_TD ), __( 'Kuwait Star', SPWKS_TD ), 'manage_options', 'kuwait_star_options' );
		add_submenu_page(
			'kuwait_star_options',
			__( 'Logs', SPWKS_TD ), // Page title
			__( 'Logs', SPWKS_TD ), // Menu title
			'manage_options', // Capability
			'kuwait_star_logs', // Menu slug
			[ $this, 'logs_page' ], // Function to display the page content
			6 // Icon URL
		);
	}

	public function logs_page(): void {
		global $logs;
		if ( isset( $_REQUEST['clear'] ) ) {
			file_put_contents( SPWKS_PATH . 'logs/api-log.log', '' );
		}
		$file = file_get_contents( SPWKS_PATH . 'logs/api-log.log' );
		$logs = explode( "==========================", $file );
		if ( $logs[0] === '' ) {
			$logs = [];
		}
		$logs = array_map( [ $this, 'style_logs' ], $logs );
		include_once SPWKS_PATH . 'templates/admin-logs.php';
	}

	public function style_logs( string $item ): array {
		$item = str_replace( 'FILE:', '<span class="label file">File</span>', $item );
		$item = str_replace( 'METHOD:', '<span class="label method">Method</span>', $item );
		$item = str_replace( 'LINE:', '<span class="label line">Line</span>', $item );
		$item = str_replace( 'ERROR: SUCCESS:', '<span class="label success">Success</span>', $item );
		$item = str_replace( 'ERROR:', '<span class="label error">Error</span>', $item );
		$item = str_replace( 'Data:', '<span class="label data">Data</span>', $item );
		$item = preg_replace( '/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', '<span class="label date">$1</span>', $item );
		$data = null;
		if ( preg_match( '/a:\d+:{(?:[^{}]|(?R))*}/', $item, $matches ) ) {
			$data = $matches[0];
		}
		if ( $data ) {
			$item = str_replace( $data, '', $item );
		}


		return compact( 'item', 'data' );
	}

	public function onSaveSetting( string $object_id, array $updated ): void {
		if ( 'kuwait_star_options' === $object_id && count( $updated ) > 0 ) {
			delete_transient( 'kuwait_star_is_active');
			delete_transient( 'kuwait_star_daily_credit');
		}
	}


}