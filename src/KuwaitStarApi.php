<?php

namespace Sz4h\KuwaitStar;

use GuzzleHttp\Exception\GuzzleException;
use Sz4h\KuwaitStar\Exception\ApiException;
use GuzzleHttp\Client;

class KuwaitStarApi {


	private string $email;
	private string $password;
	private string $base;

	private string $token;
	private int $timeout = 30;
	private Client $client;
	private Logger $logger;

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $base
	 */
	public function __construct( string $email, string $password, string $base ) {
		$this->email    = $email;
		$this->password = $password;
		$this->base     = $base;
		$this->client   = new Client( [
			// Base URI is used with relative requests
			'base_uri' => $this->get_base(),
			// You can set any number of default request options.
			'timeout'  => $this->get_timeout(),
		] );
		$this->logger   = new Logger();

	}

	/**
	 * @throws ApiException
	 */
	public function login(
		string $url = 'rest/V1/integration/customer/token', array $headers = [
		'Accept'       => 'application/json',
		'Content-Type' => 'application/json'
	], string $method = 'POST'
	): bool {

		try {
			$response = $this->client->request( $method, $url, [
				'headers' => $headers,
				'body'    => json_encode( [
					'username' => $this->get_email(),
					'password' => $this->get_password(),
				] )
			] );
			$token    = $response->getBody()->__toString();
			if ( ! $token ) {
				throw new ApiException( 'Error in retrieving token' );
			}
			$token = trim( $token, '"' );
			$this->set_token( $token );
			return true;
		} catch ( GuzzleException|ApiException $e ) {
			$this->logger->log( error: $e->getMessage(), file: __FILE__, method: __METHOD__, line: __LINE__ );
			throw new ApiException( 'Error in retrieving token' );
		}
	}


	/**
	 */
	public function request(
		string $url, array $params = [], array $headers = [
		'accept'       => 'application/json',
		'Content-Type' => 'application/json'
	], string $method = 'GET'
	): mixed {
		try {
			$this->login();
			$headers['Authorization'] = 'Bearer ' . $this->get_token();
			$data                     = [
				'headers' => $headers
			];
			if ( $method == 'POST' ) {
				$data['body'] = json_encode( $params );
			}
			if ( $method == 'GET' ) {
				$data['query'] = $params;
			}
			$response = $this->client->request( $method, $url, $data );

			$response = $response->getBody()->__toString();
			if ( ! $response ) {
				throw new ApiException( 'Error in sending request' );
			}
			$this->logger->log( error: 'SUCCESS: ' . $url, data: $params, file: __FILE__, method: __METHOD__, line: __LINE__ );

			return json_decode( $response );
		} catch ( GuzzleException|ApiException $e ) {
			$this->logger->log( error: $e->getMessage(), data: $params, file: __FILE__, method: __METHOD__, line: __LINE__ );
		}

		return null;
	}

	/**
	 */
	public function order( array $items ): string {
		$response = $this->request( url: "rest/ar/V1/buynow", params: [
			'data' => [
				'client'        => [
					'email' => $this->email,
				],
				'cart'          => $items,
				'paymentmethod' => 'wallet'
			]
		] );

		return @$response[0];
	}

	public function order_details( string $order_id ): mixed {
		$response = $this->request( url: 'rest/en/V1/order/myorder', params: [
			'status'      => $order_id,
			'pageSize'    => 1,
			'currentPage' => 1,
		] );

		return @$response;
	}

	public function credit(): ?float {
		$response = $this->request( url: 'rest/en/V1/Customer/me/wallet', method: 'get' );

		return @$response[0]->amount;
	}


	/**
	 * @return string
	 */
	public function get_base(): string {
		return $this->base;
	}


	/**
	 * @return string
	 */
	public function get_email(): string {
		return $this->email;
	}


	/**
	 * @return string
	 */
	public function get_password(): string {
		return $this->password;
	}


	/**
	 * @return int
	 */
	public function get_timeout(): int {
		return $this->timeout;
	}

	public function get_token(): string {
		return $this->token;
	}

	public function set_token( string $token ): KuwaitStarApi {
		$this->token = $token;

		return $this;
	}

	public function logger( string $method = '', string $error = '', array $data = [] ): bool|int {
		return $this->logger->log( error: $error, data: $data, file: __FILE__, method: $method );
	}
}