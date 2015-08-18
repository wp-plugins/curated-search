<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Curated_Search_Metabox' ) ) :

class Curated_Search_Metabox {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}
	
	/**
	 * Register special search custom post type.
	 */	
	function register_post_type() {
		$labels = array(
			'name'               => _x( 'Special Searches', 'post type general name', 'curated_search' ),
			'singular_name'      => _x( 'Special Search', 'post type singular name', 'curated_search' ),
			'menu_name'          => _x( 'Curated Search', 'admin menu', 'curated_search' ),
			'name_admin_bar'     => _x( 'Special Search', 'add new on admin bar', 'curated_search' ),
			'add_new'            => _x( 'Add New', 'special-search', 'curated_search' ),
			'add_new_item'       => __( 'Add New Special Search', 'curated_search' ),
			'new_item'           => __( 'New Special Search', 'curated_search' ),
			'edit_item'          => __( 'Edit Special Search', 'curated_search' ),
			'view_item'          => __( 'View Special Search', 'curated_search' ),
			'all_items'          => __( 'Special Searches', 'curated_search' ),
			'search_items'       => __( 'Search Special Searches', 'curated_search' ),
			'parent_item_colon'  => __( 'Parent Special Searches:', 'curated_search' ),
			'not_found'          => __( 'No special searches found.', 'curated_search' ),
			'not_found_in_trash' => __( 'No special searches found in Trash.', 'curated_search' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'special-search' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 4,
			'menu_icon'			 => 'dashicons-search',
			'supports'           => array( 'title', 'editor', 'author' )
		);

		register_post_type( 'special-search', $args );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
		// metabox for add additional information
		add_meta_box(
			'product_meta_details'
			,__( 'Search Options', 'curated_search' )
			,array( $this, 'render_meta_box_content' )
			,'special-search'
			,'advanced'
			,'high'
		);
		// metabox for hide page on Curated Search
		$args = array(
		   'public'   => true							  
		);
		$post_types = get_post_types( $args, 'object'); 
		unset($post_types['attachment']); 
		unset($post_types['special-search']);
		foreach($post_types as $key=>$post_type) {
			add_meta_box(
				'curated_search'
				,__( 'Curated Search', 'curated_search' )
				,array( $this, 'render_cs_meta_box_content' )
				,$key
				,'side'
				,'default'
			);
		}
		// metabox for pinned posts
		add_meta_box(
			'listing_pinned_content'
			,__( 'Pinned Content', 'curated_search' )
			,array( $this, 'render_cs_meta_box')
			,'special-search'
			,'advanced'
			,'default'

		);
	}
	
	/**
	 * Render Meta Box For Pinned Posts.
	 *
	 * @param WP_Post $post The post object and $array array.
	 */
	public function render_cs_meta_box($post) {
		$args = array(
		   'public'   => true							  
		);
		$post_types = get_post_types( $args, 'object'); 
		unset($post_types['attachment']); 
		unset($post_types['special-search']);
		?>
		<div class='post_list_wrap'>
			<ul id="pinned-lists">
				<?php
				$cs_pinned_post_ids = get_post_meta($post->ID, 'cs_pinned_post_ids', true);
				if(!empty($cs_pinned_post_ids) && is_array($cs_pinned_post_ids)) {
					foreach($cs_pinned_post_ids as $p_ids) {
						echo '<li class="menu-item-handle ui-sortable-handle" id="item-'.$p_ids.'">'.get_the_title( $p_ids ).'<span class="remove handle dashicons-dismiss" onclick="cs_remove_pinned(\''.$p_ids.'\')"></span></li>';
					} 
				}
				?>
			</ul>
			<div class="pinned_add_posts">
				<div id="cs_tabs">
					<ul>
						<?php
						$tab_html = '';
						foreach($post_types as $key=>$post_type) { 
							echo '<li><a href="#tabs-'.$key.'">'.__($post_type->labels->name, 'curated_search').'</a></li>';
							$tab_html .= '<div id="tabs-'.$key.'"><input type="text" class="cs_load_posts" onkeyup="cs_load_posts(\''.$key.'\', this);" placeholder="Type to search" /></div>';
						}
						?>
					</ul>
					<?php echo $tab_html; ?>
					<div class="cs_response"></div>
				</div>
				<div class="ptot"><input type="button" name="cs_pintotop" class="button" id="cs_pintotop" value="<?php _e('Pin to top', 'curated_search'); ?>" /></div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_cs_meta_box_content( $post ) {
     	
     	// Add an nonce field so we can check for it later.
		wp_nonce_field( 'curated-search_inner_box', 'curated-search_inner_box_nonce' );
		$cs_hide_post_id = get_post_meta( $post->ID, "cs_hide_post_id", true );
        echo '<div class="cs-meta-right">';
       	echo '<input type="checkbox" value="1" name="cs_hide_post_id" '.(($cs_hide_post_id)? 'checked':'').' /> ';
       	_e('Hide from search results', 'curated_search'); 
        echo '</div>';
        
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		if ( ! isset( $_POST['curated-search_inner_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['curated-search_inner_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'curated-search_inner_box' ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;
			
		update_post_meta( $post_id, 'cs_hide_post_id', $_REQUEST['cs_hide_post_id'] );
		
		$metakeys = array('cs_search_term', 'cs_destination_url', 'cs_synonyms');
		foreach($metakeys as $metakey) {
			$metadata = sanitize_text_field( $_POST[$metakey] );
			if($metakey == 'cs_synonyms') {
				$cs_synonyms = explode(",", $_POST[$metakey]);
				foreach($cs_synonyms as $cs_synonym) {
					$cs_synonyms_new[] = trim($cs_synonym);
				}
				$metadata = implode(",", $cs_synonyms_new);
				$metadata = rtrim($metadata,",");
				if(trim($metadata) != '') {
					$metadata = $metadata.',';
				}
			}
			update_post_meta( $post_id, $metakey, $metadata );
		}
		
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
     	
     	// Add an nonce field so we can check for it later.
		wp_nonce_field( 'curated-search_inner_box', 'curated-search_inner_box_nonce' );
		
		$fields[] =  array(
            'name'			=>	'cs_search_term',
            'id'			=>	'cs_search_term', 
            'title' 		=>	'Search Term',
            'description' 	=>	'The primary search term.',
            'type'			=>	'text',
            'default'		=>	'',
            'placeholder'	=>	'Term',
        );
                
        $fields[] =  array(
            'name'			=>	'cs_synonyms',
            'id'			=>	'cs_synonyms', 
            'title' 		=>	'Synonyms',
            'description' 	=>	'Additional search terms that should display the same results as the primary term. Separate terms with commas.',
            'type'			=>	'text',
            'default'		=>	'',
            'placeholder'	=>	'Term1, Term2',
        );
        
        $fields[] =  array(
            'name'			=>	'cs_destination_url',
            'id'			=>	'cs_destination_url', 
            'description' 	=>	'Relative URL where user who enter the search term or synonyms will be redirected.',
            'title' 		=>	'Destination URL',
            'type'			=>	'text',
            'default'		=>	'',
            'placeholder'	=>	'/sample-page/',
        );
        
        $html = '';
        foreach( $fields as  $field) {
        	switch($field['type']) {
        		case 'text':
        		$html .= '<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><label for="'.$field['name'].'">'.__( $field['title'], 'curated_search' ).' </label></th>
							<td><input type="text" placeholder="'.__( $field['placeholder'], 'curated_search' ).'" name="'.$field['name'].'" value="'.esc_attr( get_post_meta($post->ID,$field['name'],true) ).'" id="'.$field['id'].'" class="regular-text">
							<p class="description">'.__($field['description'], 'curated_search').'</p></td>
						</tr>
					</tbody>
				</table>';
       		}
        }
        echo $html; 
        
	}
	
}
new Curated_Search_Metabox();
endif;
