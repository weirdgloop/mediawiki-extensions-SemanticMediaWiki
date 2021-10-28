<?php

namespace SMW\Connection;

use RuntimeException;

/**
 * @license GNU GPL v2+
 * @since 2.1
 *
 * @author mwjames
 */
class ConnectionManager {

	/**
	 * @var boolean|null
	 */
	private static $isConnectable;

	/**
	 * By design this variable is static to ensure that ConnectionProvider
	 * instances are only initialized once per request.
	 *
	 * @var array
	 */
	private static $connectionProviders = [];

	/**
	 * @since 2.1
	 *
	 * @param string|null $id
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	public function getConnection( $id = null ) {

		$id = strtolower( $id );

		if ( self::$isConnectable === null ) {
			self::$isConnectable = $this->isConnectable();
		}

		if ( isset( self::$connectionProviders[$id] ) ) {
			return self::$connectionProviders[$id]->getConnection();
		}

		throw new RuntimeException( "{$id} is not registered as connection provider" );
	}

	/**
	 * @since 2.1
	 */
	public function releaseConnections() {
		foreach ( self::$connectionProviders as $connectionProvider ) {
			$connectionProvider->releaseConnection();
		}
	}

	/**
	 * @since 2.1
	 *
	 * @param string $id
	 * @param ConnectionProvider $connectionProvider
	 */
	public function registerConnectionProvider( $id, ConnectionProvider $connectionProvider ) {
		self::$connectionProviders[strtolower( $id )] = $connectionProvider;
	}

	/**
	 * @since 3.0
	 *
	 * @param string $id
	 * @param callable $callback
	 */
	public function registerCallbackConnection( $id, callable $callback ) {
		self::$connectionProviders[strtolower( $id )] = new CallbackConnectionProvider( $callback );
	}

	private function isConnectable() {

		if ( defined( 'SMW_VERSION' ) && defined( 'SMW_EXTENSION_LOADED' ) ) {
			return true;
		}
		return false;
	}

}
