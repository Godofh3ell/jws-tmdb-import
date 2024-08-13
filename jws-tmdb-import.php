<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://streamvid.gavencreative.com/
 * @since             1.0.3
 * @package           Jws_tmdb_import
 *
 * @wordpress-plugin
 * Plugin Name:       Jws Tmdb Import
 * Plugin URI:        https://streamvid.gavencreative.com/
 * Description:       Plugin developed to import videos from themoviedb.
 * Version:           1.0.3
 * Author:            Jws Theme
 * Author URI:        https://streamvid.gavencreative.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jws_tmdb_import
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.3 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

define( 'JWS_VERSION', '1.0.3' );
define( 'JWS_ABSPATH', plugin_dir_path( __FILE__ ) );
define( 'JWS_TMDB_IMPORT_ABSPATH', plugin_dir_path( __FILE__ ) );
define( 'JWS_STREAMVID_URL', trailingslashit( plugin_dir_url( __FILE__ )));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-jws_tmdb_import-activator.php
 */
function activate_jws_tmdb_import() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jws_tmdb_import-activator.php';
	Jws_tmdb_import_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-jws_tmdb_import-deactivator.php
 */
function deactivate_jws_tmdb_import() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jws_tmdb_import-deactivator.php';
	Jws_tmdb_import_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_jws_tmdb_import' );
register_deactivation_hook( __FILE__, 'deactivate_jws_tmdb_import' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
include_once plugin_dir_path( __FILE__ ) . 'includes/class-jws_tmdb_import.php';
include_once( 'check_update.php' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_jws_tmdb_import() {

	$plugin = new Jws_tmdb_import();
	$plugin->run();

}
run_jws_tmdb_import();
