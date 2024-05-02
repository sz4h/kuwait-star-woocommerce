<?php /** @noinspection PhpNoReturnAttributeCanBeAddedInspection */


use Sz4h\KuwaitStar\KuwaitStarApi;

if ( ! function_exists( 'dd' ) ) {
	/**
	 * @param mixed $value
	 *
	 * @return void
	 */
	function dd( mixed ...$value ): void {
		dump( ...func_get_args() );
		die();
	}
}
if ( ! function_exists( 'dump' ) ) {
	/**
	 * @param mixed $value
	 *
	 * @return void
	 */
	function dump( mixed ...$value ): void {
		foreach ( func_get_args() as $item ) {
			echo '<pre>';
			var_dump( $item );
			echo '</pre>';
		}
	}
}

if ( ! function_exists( 'kuwait_star_api' ) ) {
	function kuwait_star_api(): ?KuwaitStarApi {
		$options = get_option( 'kuwait_star_options' );

		if ( ! @$options['email'] || ! @$options['password'] || ! @$options['domain'] ) {
			return null;
		}

		return new KuwaitStarApi(
			email: $options['email'],
			password: $options['password'],
			base: trailingslashit( 'https://' . $options['domain'] ),
		);
	}
}

if (!function_exists('base_price')) {
	function base_price(float|int $amount, string $currency = 'KWD'): float
	{
		$currencies = get_option('woocs', array());

		if (empty($currencies) or !is_array($currencies) or count($currencies) < 2) {
			return (float)$amount;
		}

		$rate = @$currencies[$currency]['rate'] ?? 1;
		$rate_plus = @$currencies[$currency]['rate_plus'] ?? 0;

		return (float)number_format(($amount / ($rate + $rate_plus)), 2, '.', '');
	}
}