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
 * Class Shortcodes
 *
 * @package WooVsl\App\General
 * @since 1.0.0
 */
class Shortcodes extends Base {
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
		 * Add plugin code here
		 */

		add_shortcode( 'foobar', [ $this, 'foobarFunc' ] );
	}

	/**
	 * Shortcode example
	 *
	 * @param array $atts Parameters.
	 * @return string
	 * @since 1.0.0
	 */
	public function foobarFunc( array $atts ): string {
		shortcode_atts(
			[
				'foo' => 'something',
				'bar' => 'something else',
			], $atts
		);
		return '<span class="foo">foo = ' . $atts['foo'] . '</span>' .
			'<span class="bar">foo = ' . $atts['bar'] . '</span>';
	}
}
