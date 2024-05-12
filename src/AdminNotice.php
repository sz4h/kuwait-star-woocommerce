<?php

namespace Sz4h\KuwaitStar;

class AdminNotice {


	/**
	 * @var float|mixed|null
	 */
	private ?float $credit;

	public function __construct() {
		$credit  = get_transient( 'kuwait_star_daily_credit' );
		$options = get_option( 'kuwait_star_options' );
		if ( ! @$options['alert_threshold'] ) {
			return;
		}
		if ( ! $credit && @$options['email'] && @$options['password'] && @$options['domain'] ) {
			$credit = kuwait_star_api()?->credit();

			set_transient( 'kuwait_star_daily_credit', $credit, 60 * 60 * 24 );
		}
		if ( $credit && $credit <= $options['alert_threshold'] ) {
			$this->credit = $credit;
			add_action( 'admin_notices', [ $this, 'credit_low' ] );
		}

	}

	public function credit_low(): void {
		$class   = 'notice notice-error';
		$message = sprintf( __( 'Kuwait Star credit low (Current credit: %s)', SPWKS_TD ), $this->credit );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}
}