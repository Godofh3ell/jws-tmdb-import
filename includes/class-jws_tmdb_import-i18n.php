<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://streamvid.jwsuperthemes.com/
 * @since      1.0.0
 *
 * @package    Jws_tmdb_import
 * @subpackage Jws_tmdb_import/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Jws_tmdb_import
 * @subpackage Jws_tmdb_import/includes
 * @author     Jws Theme <jwsthemes@gmail.com>
 */
class Jws_tmdb_import_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'jws_tmdb_import',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
