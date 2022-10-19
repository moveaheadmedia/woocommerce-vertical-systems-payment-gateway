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

namespace WooVsl\App\General;

use WooVsl\Common\Abstracts\Base;

/**
 * Class Widgets
 *
 * @package WooVsl\App\General
 * @since 1.0.0
 */
class Widgets extends Base {

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		/**
		 * This general class is always being instantiated as requested in the Bootstrap class
		 *
		 * @see Bootstrap::__construct
		 *
		 * Widget is registered from the Integrations folder, but it can also be registered
		 * from the integration class file self
		 * @see HTML_Widget::init()
		 */
		add_action( 'widgets_init', function () {
			register_widget( $this->plugin->namespace() . '\Integrations\Widget\HTML_Widget' );
		} );
	}
}

