<?php

namespace SMW\Schema;

use JsonSerializable;
use SMW\Utils\DotArray;

/**
 * @license GNU GPL v2+
 * @since 3.1
 *
 * @author mwjames
 */
class Compartment implements JsonSerializable {

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @since 3.1
	 *
	 * @param array $data
	 */
	public function __construct( array $data = [] ) {
		$this->data = $data;
	}

	/**
	 * @since 3.1
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function has( $key ) {
		return $this->get( $key, false ) !== false;
	}

	/**
	 * @since 3.1
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed|null
	 */
	public function get( $key, $default = null ) {
		return DotArray::get( $this->data, $key, $default );
	}

	/**
	 * @since 3.1
	 *
	 * @return string
	 */
	 public function jsonSerialize() {
		return json_encode( $this->data );
	}

	/**
	 * @since 3.1
	 *
	 * @return string
	 */
	 public function __toString() {
		return $this->jsonSerialize();
	}

}
