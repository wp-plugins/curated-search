<?php
/**
 * Plugin Name: Curated Search
 * Description: Quickly and easily specify the content you want users to see for specific search queries.
 * Version: 1.2
 * Author: LaunchSite.us
 * Author URI: http://launchsite.us/curated-search
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Curated_Search' ) ) :

/**
 * Main Curated Search Class
 *
 * @class Curated Search
 */
final class Curated_Search {

	/**
	 * @var string
	 */
	public $version = '1.2';

	/**
	 * @var Curated Search The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Main Curated Search Instance
	 *
	 * Ensures only one instance of Curated Search is loaded or can be loaded.
	 *
	 * @see curated_search()
	 * @return Curated Search - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Curated Search Constructor.
	 * @access public
	 * @return Curated Search
	 */
	public function __construct() {

		// Define constants
		$this->define_constants();

		// Include required files
		$this->includes();

		// Hooks
		add_action( 'init', array( $this, 'init' ), 0 );

	}


	/**
	 * Define Curated Search Constants
	 */
	private function define_constants() {
		define( 'CS_PLUGIN_FILE', __FILE__ );
		define( 'CS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'CS_VERSION', $this->version );
		define( 'CS_PLUGIN_URL', $this->plugin_url() );
		define( 'CS_PLUGIN_PATH', $this->plugin_path() );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function includes() {
		include_once( 'includes/cs-core-functions.php' );
		include_once( 'includes/class-cs-install.php' );
		include_once( 'includes/cs-hooks.php' );
		
		if ( is_admin() ) {
			include_once( 'includes/admin/cs-admin.php' );
		}

		$this->ajax_includes();	

	}
	
	/**
	 * Include required ajax files.
	 */
	public function ajax_includes() {
		include_once( 'includes/class-cs-ajax.php' );  // Ajax functions for admin and the front-end
	}

	
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'curated_search', false, plugin_basename( dirname( __FILE__ ) ) . "/i18n/languages" );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}
	
	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}
	
	/**
	 * Init Cards when WordPress Initialises.
	 */
	public function init() {
		
		$this->load_plugin_textdomain();
		
		do_action( 'curated_search_init' );
	}

}

endif;

/**
 * Returns the main instance of curated search to prevent the need to use globals.
 *
 * @since  1.2
 * @return Curated Search
 */

function curated_search() {
	return Curated_Search::instance();
}

curated_search();
