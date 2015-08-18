<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Curated_Search_Settings' ) ) :
class Curated_Search_Settings {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'init', array( $this, 'export_import_settings' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_submenu_page( 
        	'edit.php?post_type=special-search',
        	__('Curated Search Settings','curated-search'), 
        	__('Settings','curated-search'),
        	'manage_options',
        	'curated-search',
        	array($this,'settings_callback')
    	); 
    }

    /**
     * Options page callback
     */
    public function settings_callback() {
    	
        $message = '';
		if(!empty($_POST)) {
			if(isset($_POST['cs_setting_for']) && $_POST['cs_setting_for'] == 'curated-search') {
				$cs_search_pagination = $_POST['cs_search_pagination'];
				update_option( "cs_search_pagination", "{$cs_search_pagination}" );
				if(isset($_POST['cs_one_result_redirect']) && $_POST['cs_one_result_redirect'] == 1) {
					$cs_one_result_redirect = 1;

				} else {
					$cs_one_result_redirect = 0;
				}
				update_option( "cs_one_result_redirect", $cs_one_result_redirect );
		
				$message = __('Updated successfully.', 'curated_search');
			}
			
		}
	
        $cs_search_pagination = get_option( "cs_search_pagination" );
		$cs_one_result_redirect = get_option( "cs_one_result_redirect" );
		
        ?>
        
        <div class="wrap">
        	<?php
        	$tabs = array('general' => __('General','curated_search'), 'support' => __('Support','curated_search') );
        	if ( isset ( $_GET['tab'] ) ) $current = $_GET['tab'];
   			else $current = 'general';
			echo '<div id="icon-themes" class="icon32"><br></div>';
			echo '<h2 class="nav-tab-wrapper">';
			foreach( $tabs as $tab => $name ){
				$class = ( $tab == $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab$class' href='?post_type=special-search&page=curated-search&tab=$tab'>$name</a>";

			}
			echo '</h2>'; 
			switch ( $current ){
				case 'general' :
					if($message) {
						echo '<div class="updated below-h2" id="message"><p>'.$message.'</p></div>';
					}
					?>  
					<h3 class="no-margin"><?php _e('Single Result','curated_search'); ?></h3>      
					<p>
						<?php _e('If a search term returns only one result, Curated search can redirect the user to the URL of the result and bypass the search results page entirely', 'curated_search'); ?>
					</p>
				    <form method="post" action="edit.php?post_type=special-search&page=curated-search">
				    	<input type="hidden" name="cs_setting_for" value="curated-search" />
				    	<label for="cs_one_result_redirect">
							<input type="checkbox" value="1" <?php echo ($cs_one_result_redirect==1)? 'checked':''; ?> name="cs_one_result_redirect"/>
							<?php _e('Redirect when only one result', 'curated_search'); ?>
						</label>
						<br><br>
						<h3 class="no-margin"><?php _e('Maximum Number of Results', 'curated_search'); ?></h3>
						<p>
							<?php _e('By default, Wordpress will return all search results that match the given search term. Depending on the term and how many pieces of content are on the site, this could mean dozens of results over several pages. Since users are unlikely to visit more than one or two pages of results, you can limit the total number of results returned to a more manageable number. Leaving this field blank will keep the default settings and return all results for a given term.', 'curated_search'); ?>
						</p>
						<input type="number" class="regular-text" id="cs_search_pagination" value="<?php echo $cs_search_pagination; ?>" name="cs_search_pagination" placeholder="10" min="1">
						
				    	<div id="cs_status" class="below-h2"></div>
						<h3><?php _e('Exclude Content', 'curated_search'); ?></h3> 		        
						<div class="cs-excluded-wrap">
							<div class="tablenav top cs-select-wrap">
								<?php 
								$args = array(
										   'public'   => true							  
										);
						
								$post_types = get_post_types( $args, 'object'); 
								unset($post_types['attachment']);
								unset($post_types['special-search']);		        		
								?>
								<select id="cs_selected_post_type" name="cs_post_type">
									<?php
									foreach($post_types as $key => $post_type) { ?>
										<option value="<?php echo $key; ?>"><?php echo $post_type->label; ?></option>	  
									<?php } ?>     			
								</select>
							
								<select id="cs_loaded_post_type" name="cs_loaded_post_type">
									<option value=""><?php _e('Select', 'curated_search'); ?></option>     			
								</select>
								
								<select id="cs_load_terms" name="cs_load_terms">
									<option value=""><?php _e('Select', 'curated_search'); ?></option>     			
								</select>
								<a href="javascript:;" id="cs_add_to_exclude_list" class="button"><?php _e('Add', 'curated_search'); ?></a>
							</div>
							<table class="widefat">
								<thead>
									<tr>
										<th class="column-cb"><?php _e('Excluded', 'curated_search'); ?></th>
										<th class="column-cb"><?php _e('Action', 'curated_search'); ?></th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th class="column-cb"><?php _e('Excluded', 'curated_search'); ?></th>
										<th class="column-cb"><?php _e('Action', 'curated_search'); ?></th>
									</tr>
								</tfoot>
								<tbody id="add_exclude_list">
									<?php 
									global $wpdb;
									$table_name = $wpdb->prefix . "cs_excluded_list"; 
									$exclude_lists = $wpdb->get_results("SELECT * FROM `$table_name` ORDER BY id DESC");
									if(!empty($exclude_lists)) {
										foreach($exclude_lists as $exclude_list) { 
											$single_term = get_term_by('id', $exclude_list->term_id, $exclude_list->taxonomy_type);
											if(!empty($single_term)) {
												$p_type = get_taxonomy($single_term->taxonomy)->object_type[0];		
												?>
												<tr>
													<td class="column-cb"><?php echo $single_term->name.' ( '.$p_type.' | '.$single_term->taxonomy.' ) '; ?></td>
													<td class="column-cb"><a href="javascript:;" style="color:red;" onClick="cs_remove_exclude_list(this, '<?php echo $exclude_list->taxonomy_type.'||'.$exclude_list->term_id; ?>');"><?php _e('Remove', 'curated_search'); ?></a></td>
												</tr>
												<?php 
											} 
										}
							
									} ?>
								</tbody>
							</table>
						</div>	
						<?php submit_button(); ?>
				    </form>
				    <hr><br>
				    <div class="imp_exp_wrap">
				    	
				    	<div class="block-bgcolr">
				    		<h3 class="no-margin"><?php _e('Export/Import Special Searches', 'curated_search'); ?></h3>
							<form action="<?php echo admin_url( 'export.php'); ?>">
								<input type="hidden" value="true" name="download">
								<input type="hidden" value="special-search" name="content">
								<?php submit_button( __('Export Special Searches', 'curated_search') ); ?>
							</form>	
							
							<?php 
							$wp_importer_dir = 'wordpress-importer';
							$plugin_file = $wp_importer_dir.'/'.$wp_importer_dir.'.php';				    	
							if( !is_file(WP_PLUGIN_DIR.'/'.$plugin_file) ) {
								add_thickbox(); 
								$link = esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $wp_importer_dir . '&from=import&TB_iframe=true&width=600&height=550' ) );
								echo '<a href="'.$link.'" class="thickbox">'.__('Install Wordpress Importer Plugin', 'curated_search').'</a>';
							} elseif ( !is_plugin_active( $plugin_file ) ) {
								$link = esc_url(wp_nonce_url(admin_url('plugins.php?action=activate&plugin=' . $plugin_file . '&from=import'), 'activate-plugin_' . $plugin_file));
								echo '<a href="'.$link.'">'.__('Active Wordpress Importer Plugin', 'curated_search').'</a>';
							} else {
								$link = esc_url(admin_url('admin.php?import=wordpress'));
								echo '<a href="'.$link.'">'.__('Import Special Searches', 'curated_search').'</a>';
							}
							?>
				    	</div>
				    	<?php /*
				    	<div class="block-bgcolr">	
				    		<h3 class="no-margin"><?php _e('Export/Import Settings', 'curated_search'); ?></h3>			    	
							<form method="post" action="edit.php?post_type=special-search&page=curated-search">
								<input type="hidden" value="true" name="cs_download_settings">
								<?php submit_button( __('Export Settings','curated_search') ); ?>
							</form>	
							<form method="post" enctype="multipart/form-data" action="edit.php?post_type=special-search&page=curated-search">
								<input type="hidden" value="true" name="cs_upload_settings">
								<input type="file" name="cs_settings_file">
								<?php submit_button( __('Import','curated_search'), 'primary', 'submit', false ); ?>
							</form>	
				    	</div>
				    	*/ ?>    	
				    </div>
				  	<?php
				break;
      			case 'support' : ?>
      				<h3 class="no-margin"><?php _e('Overview Video','curated_search'); ?></h3>
      				<p><?php _e('A brief walkthrough on configuring the plugin for your site.','curated_search'); ?></p>
      				<p><iframe width="560" height="315" src="https://www.youtube.com/embed/nO75kPExREw" frameborder="0" allowfullscreen></iframe></p>
      				<h3 class="no-margin"><?php _e('Contextual Content','curated_search'); ?></h3>
      				<p><?php _e('Place the code below the header and above the loop in search.php of you current theme file:','curated_search'); ?> <code>&lt;?php do_action('cs_search_after_title'); ?&gt;</code></p>
      				<h3 class="no-margin"><?php _e('Support Forum','curated_search'); ?></h3>
      				<p><a href="https://wordpress.org/plugins/curated-search/" target="_blank">https://wordpress.org/plugins/curated-search/</a></p>
      				<h3 class="no-margin"><?php _e('Official Site','curated_search'); ?></h3>
      				<p><a href="http://launchsite.us/curated-search/" target="_blank">http://launchsite.us/curated-search/</a></p>
      				<?php
      			break;      			
				} ?>
        </div>
        <?php
    }
    
    /**
     * Export/Import Settings 
     * TODO: For the future use
     */
    public function export_import_settings() {
    	// Download xml settings starts
		if(isset($_POST['cs_download_settings']) && $_POST['cs_download_settings'] == true) {
			// Get cs_search_pagination
			$cs_search_pagination = get_option( "cs_search_pagination" ); 
			// Get cs_one_result_redirect
			$cs_one_result_redirect = get_option( "cs_one_result_redirect" );
			global $wpdb;
			$table_name = $wpdb->prefix . "cs_excluded_list"; // Declare the table name
			$excluded_results = $wpdb->get_results("SELECT * FROM `$table_name`");

			$xml = new SimpleXMLElement('<xml/>');
			$cs_settings = $xml->addChild('cs_settings');
			
			// XML for Terms
			$cs_terms = $cs_settings->addChild('ex_terms');		
			if(!empty($excluded_results)) {
				foreach($excluded_results as $key => $result) {
					$term = get_term_by('id', $result->term_id, $result->taxonomy_type);
					if(!empty($term)) {	
						$cs_term = $cs_terms->addChild('term');			
						$cs_term->addChild('slug', $term->slug);
						$cs_term->addChild('taxonomy_type', $result->taxonomy_type);
						$cs_term->addChild('post_type', $result->post_type);
					}
				}
			}
			
			// XML for options
			$cs_options = $cs_settings->addChild('options');
			if( !empty($cs_one_result_redirect) || !empty($cs_search_pagination) ) {
				 if( !empty($cs_search_pagination) ) {
				 	$cs_option = $cs_options->addChild('option');
				 	$cs_option->addChild('key', 'cs_search_pagination');
				 	$cs_option->addChild('value', $cs_search_pagination);
				 }
				 if( !empty($cs_one_result_redirect) ) {
				 	$cs_option = $cs_options->addChild('option'); 
				 	$cs_option->addChild('key', 'cs_one_result_redirect');
				 	$cs_option->addChild('value', $cs_one_result_redirect);
				 }
			}
			
			// Force XML download code
			$name = strftime('cs_settings_%m_%d_%Y.xml');
			header('Content-Disposition: attachment;filename=' . $name);
			header('Content-Type: text/xml');
			header("Pragma: no-cache");
			header("Expires: 0");
			echo $xml->saveXML();
			exit;			
			// Download xml settings ends
			
		} 
		// Upload xml settings starts
		else if(isset($_POST['cs_upload_settings']) && $_POST['cs_upload_settings'] == true) { 
			if (isset($_FILES['cs_settings_file']) && ($_FILES['cs_settings_file']['error'] == UPLOAD_ERR_OK) && ($_FILES["cs_settings_file"]["type"] == "text/xml")) {
				$xml = simplexml_load_file($_FILES['cs_settings_file']['tmp_name']); 
				
				if( !empty($xml->cs_settings->ex_terms) ) {
					global $wpdb, $not_exsting_terms;
					$not_exsting_terms = array();
					$table_name = $wpdb->prefix . "cs_excluded_list";  // Declare the table name
					foreach($xml->cs_settings->ex_terms->term as $term) {
						$new_term = get_term_by('slug', (string)$term->slug, (string)$term->taxonomy_type);
						if(!empty($new_term)) {
							// Query for check the value exists or not
							$already_exists = $wpdb->get_row("SELECT * FROM `$table_name` WHERE `term_id` = '".(string)$new_term->term_id."' AND `taxonomy_type`='".(string)$term->taxonomy_type."' AND `post_type`='".(string)$term->post_type."'");
							// Already Exists Checks
							if(empty($already_exists)) { 
								$data = array(
									'term_id' 		=>	(string)$new_term->term_id,
									'taxonomy_type' =>	(string)$term->taxonomy_type,
									'post_type' 	=>	(string)$term->post_type
								);
								// Inserted the terms values to db
								$wpdb->insert( $table_name, $data );
							}
						} else {
							$not_exsting_terms[] = $term->slug;
						}
					}
				}
				
				if( !empty($xml->cs_settings->options) ) {
					foreach($xml->cs_settings->options->option as $option) {
						// Update the option values
						update_option( (string)$option->key, (string)$option->value );
					}
				}
				
				if(!empty($not_exsting_terms)) {
					// Action hook for show success message
					add_action( 'admin_notices', array( $this, 'cs_terms_not_existing_notice' ));
				} else {
					// Action hook for show success message
					add_action( 'admin_notices', array( $this, 'cs_upload_notice' ) ); 
				} 
				
			} else {
				// Action hook for show error message
				add_action( 'admin_notices', array( $this, 'cs_upload_file_error' ) ); 
			}
		}
		// Upload xml settings ends
		
    }
    
    /**
     * Success notice 
     */
    function cs_upload_notice() {
    	?>
		<div class="updated">
		    <p><?php _e( 'Updated Successfully!', 'curated_search' ); ?></p>
		</div>
		<?php
	}
	
	/**
     * Terms not existing notice
     */
    function cs_terms_not_existing_notice($not_exsting_terms) {
    	global $not_exsting_terms;
    	?>
		<div class="error">
		    <p><?php printf( __( '<b>%s</b> does not exist.', 'curated_search' ), implode(", ",$not_exsting_terms) ); ?></p>
		</div>
		<?php
	}
	
	/**
     * File type error notice 
     */
	function cs_upload_file_error() {
    	?>
		<div class="error">
		    <p><?php _e( 'Please select a xml file!', 'curated_search' ); ?></p>
		</div>
		<?php
	}

}
endif;
new Curated_Search_Settings();
