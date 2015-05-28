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
		
				$message = 'Updated successfully.';
			}
		}
	
        $cs_search_pagination = get_option( "cs_search_pagination" );
		$cs_one_result_redirect = get_option( "cs_one_result_redirect" );
		
        ?>
        <div class="wrap">
            <h2><?php echo __('Curated Search Settings','curated_search'); ?></h2>    
            <?php
			if($message) {
				echo '<div class="updated below-h2" id="message"><p>'.$message.'</p></div>';
			}
			?>  
			<h3><?php echo __('General','curated_search'); ?></h3>      
            <form method="post" action="edit.php?post_type=special-search&page=curated-search">
            	<input type="hidden" name="cs_setting_for" value="curated-search" />
            	<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="blogname"><?php _e('Maximum results:' , 'curated_search'); ?></label></th>
							<td><input type="number" class="regular-text" id="cs_search_pagination" value="<?php echo $cs_search_pagination; ?>" name="cs_search_pagination" placeholder="10" min="1">
							<p class="description"><?php _e('Enter the maximum number of search results you wish to display. Leave blank for unlimited.' , 'curated_search'); ?></p></td>
						</tr>
						<tr>
							<th scope="row"><label for="blogdescription"><?php _e('Redirect when one result:' , 'curated_search'); ?></label></th>
							<td><input type="checkbox" value="1" <?php echo ($cs_one_result_redirect==1)? 'checked':''; ?> name="cs_one_result_redirect"/>
							<p class="description"><?php _e('If only one result is present, the user will be redirected to the post/page and bypass the search page entirely.' , 'curated_search'); ?></p></td>
						</tr>
						<tr><td colspan=2><?php _e('Place the code below the title in search.php of your current theme file', 'curated_search'); ?>: <code onclick="this.select();">&lt;?php do_action('cs_search_after_title'); ?&gt;</code></td></tr>
					</tbody>
				</table>
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
		        <div>	
		        <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

}
endif;
new Curated_Search_Settings();
