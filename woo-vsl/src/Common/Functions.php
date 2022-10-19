<?php
/**
 * Woo VSL
 *
 * @package   woo-vsl
 * @author    Move Ahead Media <ali@moveaheadmedia.co.uk>
 * @copyright 2022 Woo VSL
 * @license   MIT
 * @link      https://moveaheadmedia.com
 */

declare( strict_types = 1 );

namespace WooVsl\Common;

use WooVsl\App\Frontend\Templates;
use WooVsl\Common\Abstracts\Base;

/**
 * Main function class for external uses
 *
 * @see woo_vsl()
 * @package WooVsl\Common
 */
class Functions extends Base {
	/**
	 * Get plugin data by using woo_vsl()->getData()
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function getData(): array {
		return $this->plugin->data();
	}

	/**
	 * Get the template instantiated class using woo_vsl()->templates()
	 *
	 * @return Templates
	 * @since 1.0.0
	 */
	public function templates(): Templates {
		return new Templates();
	}
}
