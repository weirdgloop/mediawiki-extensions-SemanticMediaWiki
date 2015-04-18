<?php

namespace SMW\Tests;

use SMW\FileReader;

use RuntimeException;

/**
 * @license GNU GPL v2+
 * @since 2.2
 *
 * @author mwjames
 */
class JsonTestCaseFileHandler {

	/**
	 * @var FileReader
	 */
	private $fileReader;

	/**
	 * @var string
	 */
	private $reasonToSkip = '';

	/**
	 * @since 2.2
	 *
	 * @param FileReader $fileReader
	 */
	public function __construct( FileReader $fileReader ) {
		$this->fileReader = $fileReader;
	}

	/**
	 * @since 2.2
	 *
	 * @return boolean
	 */
	public function isIncomplete() {

		$meta = $this->getFileContentsFor( 'meta' );

		try{
			$isIncomplete = (bool)$meta['is-incomplete'];
		} catch( \Exception $e ) {
			$isIncomplete = false;
		}

		if ( $isIncomplete ) {
			$this->reasonToSkip = '"'. $this->getFileContentsFor( 'description' ) . '" has been marked as incomplete.';
		}

		return $isIncomplete;
	}

	/**
	 * @since 2.2
	 *
	 * @return boolean
	 */
	public function getDebugMode() {

		$meta = $this->getFileContentsFor( 'meta' );

		try{
			$debug = (bool)$meta['debug'];
		} catch( \Exception $e ) {
			$debug = false;
		}

		return $debug;
	}

	/**
	 * @since 2.2
	 *
	 * @return boolean
	 */
	public function requiredToSkipForConnector( $connectorId ) {

		$connectorId = strtolower( $connectorId );
		$meta = $this->getFileContentsFor( 'meta' );

		$skipOn = isset( $meta['skip-on'] ) ? $meta['skip-on'] : array();

		if ( in_array( $connectorId, array_keys( $skipOn ) ) ) {
			$this->reasonToSkip = $skipOn[$connectorId];
		}

		return $this->reasonToSkip !== '';
	}

	/**
	 * @since 2.2
	 *
	 * @return boolean
	 */
	public function requiredToSkipForJsonVersion( $version ) {

		$meta = $this->getFileContentsFor( 'meta' );

		if ( version_compare( $version, $meta['version'], 'lt' ) ) {
			$this->reasonToSkip = $meta['version'] . ' as file version is not supported';
		}

		return $this->reasonToSkip !== '';
	}

	/**
	 * @since 2.2
	 *
	 * @return boolean
	 */
	public function requiredToSkipForMwVersion( $mwVersion ) {

		$meta = $this->getFileContentsFor( 'meta' );
		$skipOn = isset( $meta['skip-on'] ) ? $meta['skip-on'] : array();

		foreach ( $skipOn as $id => $reason ) {

			if ( strpos( $id, 'mw-' ) === false ) {
				continue;
			}

			list( $mw, $versionToSkip ) = explode( "mw-", $id, 2 );

			if ( version_compare( $mwVersion, $versionToSkip, '=' ) ) {
				$this->reasonToSkip = "MediaWiki " . $mwVersion . " version is not supported ({$reason})";
				break;
			}
		}

		return $this->reasonToSkip !== '';
	}

	/**
	 * @since 2.2
	 *
	 * @return string
	 */
	public function getReasonForSkip() {
		return $this->reasonToSkip;
	}

	/**
	 * @since 2.2
	 *
	 * @return array|integer|string|boolean
	 * @throws RuntimeException
	 */
	public function getSettingsFor( $key ) {

		$settings = $this->getFileContentsFor( 'settings' );

		if ( ( $key === 'wgContLang' || $key === 'wgLang' ) && isset( $settings[$key] ) ) {
			return \Language::factory( $settings[$key] );
		}

		// Needs special attention due to NS constant usage
		if ( $key === 'smwgNamespacesWithSemanticLinks' && isset( $settings[$key] ) ) {
			$smwgNamespacesWithSemanticLinks = array();

			foreach ( $settings[$key] as $ns => $value ) {
				$smwgNamespacesWithSemanticLinks[constant( $ns )] = (bool)$value;
			}

			return $smwgNamespacesWithSemanticLinks;
		}

		if ( isset( $settings[$key] ) ) {
			return $settings[$key];
		}

		// Set default values
		if ( isset( $GLOBALS[$key] ) ) {
			return $GLOBALS[$key];
		}

		throw new RuntimeException( "{$key} is unknown" );
	}

	/**
	 * @since 2.2
	 *
	 * @return array
	 */
	public function getListOfProperties() {
		return $this->getFileContentsFor( 'properties' );
	}

	/**
	 * @since 2.2
	 *
	 * @return array
	 */
	public function getListOfSubjects() {
		return $this->getFileContentsFor( 'subjects' );
	}

	/**
	 * @since 2.2
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function findTestCasesFor( $key ) {

		try{
			$testcases = $this->getFileContentsFor( $key );
		} catch( \Exception $e ) {
			$testcases = array();
		}

		return $testcases;
	}

	/**
	 * @since 2.2
	 *
	 * @return array
	 */
	public function findRdfTestCases() {

		try{
			$testcases = $this->getFileContentsFor( 'rdf-testcases' );
		} catch( \Exception $e ) {
			$testcases = array();
		}

		return $testcases;
	}

	/**
	 * @since 2.2
	 *
	 * @return array
	 */
	public function findFormatTestCases() {

		try{
			$testcases = $this->getFileContentsFor( 'format' );
		} catch( \Exception $e ) {
			$testcases = array();
		}

		return $testcases;
	}

	/**
	 * @since 2.2
	 *
	 * @return array
	 */
	public function findQueryTestCases() {

		try{
			$testcases = $this->getFileContentsFor( 'queries' );
		} catch( \Exception $e ) {
			$testcases = array();
		}

		return $testcases;
	}

	/**
	 * @since 2.2
	 *
	 * @return array
	 */
	public function findConceptTestCases() {

		try{
			$testcases = $this->getFileContentsFor( 'concept-testcases' );
		} catch( \Exception $e ) {
			$testcases = array();
		}

		return $testcases;
	}

	private function getFileContentsFor( $index ) {

		$contents = $this->fileReader->read();

		if ( isset( $contents[$index] ) ) {
			return $contents[$index];
		}

		throw new RuntimeException( "{$index} is unknown" );
	}

}
