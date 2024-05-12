<?php

namespace Sz4h\KuwaitStar;

class AdminNotice {


	/**
	 * @var float|mixed|null
	 */
	private ?float $credit;

	public function __construct() {
		$options = get_option( 'kuwait_star_options' );
		if ( ! @$options['alert_threshold'] || ! @$options['email'] && ! @$options['password'] && ! @$options['domain'] ) {
			return;
		}

		$this->creditCheck( alert_threshold: $options['alert_threshold'] );
		$this->checkLogin();

	}

	public function credit_low(): void {
		$class   = 'notice notice-error';
		$message = sprintf( __( 'Kuwait Star credit low (Current credit: %s)', SPWKS_TD ), $this->credit );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	public function kuwait_star_credentials_wrong(): void {
		$class   = 'notice notice-error';
		$message = __( 'Please check Kuwait Star credentials)', SPWKS_TD );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * @param string|float $alert_threshold
	 *
	 * @return void
	 */
	public function creditCheck( string|float $alert_threshold ): void {
		$credit = get_transient( 'kuwait_star_daily_credit' );

		if ( ! $credit ) {
			$credit = kuwait_star_api()?->credit();

			set_transient( 'kuwait_star_daily_credit', $credit, 60 * 60 * 24 );
		}
		if ( $credit && $credit <= $alert_threshold ) {
			$this->credit = $credit;
			add_action( 'admin_notices', [ $this, 'credit_low' ] );
		}
	}

	private function checkLogin(): void {
		$isActive = get_transient( 'kuwait_star_is_active' );
		if ( ! $isActive ) {
			try {
				$isActive = kuwait_star_api()?->login() ? 'active' : 'inactive';
			} catch ( Exception\ApiException ) {
				$isActive = 'inactive';
			}
			set_transient( 'kuwait_star_is_active', $isActive, 60 * 60 * 24 );
		}
		if ( 'inactive' === $isActive ) {
			add_action( 'admin_notices', [ $this, 'kuwait_star_credentials_wrong' ] );
		}
	}
}