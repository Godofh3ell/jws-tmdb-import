<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://streamvid.jwsuperthemes.com/
 * @since      1.0.0
 *
 * @package    Jws_tmdb_import
 * @subpackage Jws_tmdb_import/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Jws_tmdb_import
 * @subpackage Jws_tmdb_import/includes
 * @author     Jws Theme <jwsthemes@gmail.com>
 */
class Jws_tmdb_import {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Jws_tmdb_import_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'JWS_TMDB_IMPORT_VERSION' ) ) {
			$this->version = JWS_TMDB_IMPORT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'jws_tmdb_import';
        
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'admin_init', array( $this, 'buffer' ), 1 );

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
        
	}
    
    /**
     * Output buffering allows admin screens to make redirects later on.
     */
    public function buffer() {
        ob_start();
    }
    
      /**
         * Main MasVideos Instance.
         *
         * Ensures only one instance of MasVideos is loaded or can be loaded.
         *
         * @static
         * @return MasVideos - Main instance.
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Jws_tmdb_import_Loader. Orchestrates the hooks of the plugin.
	 * - Jws_tmdb_import_i18n. Defines internationalization functionality.
	 * - Jws_tmdb_import_Admin. Defines all hooks for the admin area.
	 * - Jws_tmdb_import_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jws_tmdb_import-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jws_tmdb_import-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-jws_tmdb_import-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-jws_tmdb_import-public.php';

		$this->loader = new Jws_tmdb_import_Loader();
        
        
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/template-function.php';
        
        

	}
    
    
    
    /**
     * Include any classes we need within admin.
     */
    public function includes() {
        include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-streamvid-import-admin.php';
        // Importers
        if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
  
            include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-streamvid-import-admin.php';
        }
    }


	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Jws_tmdb_import_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Jws_tmdb_import_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Jws_tmdb_import_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
  

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Jws_tmdb_import_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        
        

	}
    


     
     
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Jws_tmdb_import_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
