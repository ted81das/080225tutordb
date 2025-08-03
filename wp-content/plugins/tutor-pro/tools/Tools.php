<?php
/**
 * Main tool class
 *
 * @package Tutor\Tools
 * @author Themeum <support@themeum.com>
 * @link https://themeum.com
 * @since 3.6.0
 */

namespace TutorPro\Tools;

use AllowDynamicProperties;
use TUTOR\Singleton;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tools
 *
 * @since 3.6.0
 */
#[AllowDynamicProperties]
class Tools extends Singleton {

	/**
	 * Set tools property
	 *
	 * @since 3.6.0
	 */
	public function __construct() {
		parent::__construct();
		$this->exporter = new Exporter();
		$this->importer = new Importer();
	}
}
