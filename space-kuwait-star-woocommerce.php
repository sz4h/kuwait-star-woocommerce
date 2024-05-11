<?php
/**
 * Plugin Name: Space Woocommerce Kuwait Star Integration
 * Description:
 * Plugin URI: https://sz4h.com/
 * Author: Ahmed Safaa
 * Version: 1.0.9
 * Author URI: https://sz4h.com/
 *
 * Text Domain: space-kuwait-star-woocommerce
 *
 */
use Sz4h\KuwaitStar\Initializer;

/** @noinspection SpellCheckingInspection */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'vendor/autoload.php';

new Initializer();