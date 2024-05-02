<?php

namespace Sz4h\KuwaitStar;

class Logger {

	private string $filename = 'api-log.log';
	private string $file;

	public function __construct() {
		$this->file = SPWKS_PATH . 'logs/' . $this->filename;
	}

	public function log( string $error = '', array $data = null, string $file = '', string $method = '', string $line = '' ): bool|int {
		$message = date( 'Y-m-d H:i:s') . "\n";
		if ( $file ) {
			$message .= 'FILE: ' . $file . "\n";
		}
		if ( $method ) {
			$message .= 'METHOD: ' . $method . "\n";
		}
		if ( $line ) {
			$message .= 'LINE: ' . $line . "\n";
		}
		$message .= 'ERROR: ' . $error . "\n";
		if ( $data ) {
			$message .= 'Data: ' . "\n";
			$message .= serialize( $data );
		}
		$message .= PHP_EOL;

		return file_put_contents( $this->file, $message, FILE_APPEND );
	}


}