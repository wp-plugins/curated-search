<?php
/**
 * Installation related functions and actions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'CS_Install' ) ) :
	/**
	 * CS_Install Class
	 */
	class CS_Install {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			// Run this on activation.
			register_activation_hook( CS_PLUGIN_FILE, array( $this, 'install' ) );
			$plugin = plugin_basename(CS_PLUGIN_FILE); 
			add_filter("plugin_action_links_$plugin", array( $this, 'cs_plugin_settings_link') );
		}
	
		/**
		 * Install CS set values and create table
		 */
		public function install() {
			global $wpdb;
			update_option( "cs_search_pagination", "10" );
			update_option( "cs_one_result_redirect", 1 );			
			$table_name = $wpdb->prefix . "cs_excluded_list"; 
			$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `term_id` varchar(255) NOT NULL DEFAULT '',
			  `taxonomy_type` varchar(255) NOT NULL DEFAULT '',
			  `post_type` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`id`)
			)";
			$wpdb->query($sql);
		}
		
		/**
		 * Add settings link on plugin page
		 */
		function cs_plugin_settings_link($links) { 
		  $settings_link = '<a href="edit.php?post_type=special-search&page=curated-search">'.__('Settings', 'curated_search').'</a>'; 
		  array_unshift($links, $settings_link); 
		  return $links; 
		}
		
	}
endif;
return new CS_Install();
