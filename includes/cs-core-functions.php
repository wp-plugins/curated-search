<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( !session_id() ) {
	session_start();
}

/**
 * Add new coulmns in CS post type listing.
 *
 * @return string
 */
function cs_add_post_columns($columns) {
	$date_label = false;
	
	if( isset($columns['date']) ) {
		$date_label = $columns['date'];
		unset($columns['date']);
	}
	
    $columns = array_merge($columns, array(	
		'search_term' =>__( 'Search Term', 'curated_search'),
		'synonyms' => __('Synonyms', 'curated_search'),
		'destination_url' => __('Destination URL', 'curated_search')
	));
	
	if( $date_label ) {
		$columns['date'] = $date_label;
	}
	
	return $columns;
}

/**
 * Show the value in CS post type listing columns.
 *
 * @return string
 */
function cs_show_columns($name) {
	global $post;
	switch ($name) {
		case 'synonyms':
			$views = esc_attr( get_post_meta($post->ID, 'cs_synonyms', true) );
			echo $views;
		break;	
		case 'search_term':
			$views = esc_attr( get_post_meta($post->ID, 'cs_search_term', true) );
			echo $views;
		break;
		case 'destination_url':
			$views = esc_attr( get_post_meta($post->ID, 'cs_destination_url', true) );
			echo $views;
		break;	
	}
}

/**
 * Include the css & js script to CS admin settings .
 *
 * @return string
 */
function cs_enqueue_assets() {
	global $wp_scripts;
	wp_enqueue_style('cs_front_style', CS_PLUGIN_URL.'/assets/css/style.css');
	wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('jquery-ui-tabs');
    $ui = $wp_scripts->query('jquery-ui-core');
	$protocol = is_ssl() ? 'https' : 'http';
	$url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
	wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
	wp_enqueue_script( 'cs_front_script', CS_PLUGIN_URL.'/assets/js/script.js',array('jquery') );
	$args = array(
		'ajax_url'			=>	admin_url('admin-ajax.php'),
		'ajax_nonce'		=>	wp_create_nonce('cs-ajax'),
		'ajax_delete_text'	=>	__('Are you sure to remove this?', 'curated_search'),
		'ajax_select'		=>	__('Select', 'curated_search')
	);
	wp_localize_script( 'cs_front_script', 'cs', $args );
}

/**
 * Filter the search where query.
 *
 * @return string
 */
function cs_posts_search($where, $query) {	
	if( $query->is_admin ) {
		return $where;
	}
	
	if( !$query->is_main_query() ) {
		return $where;
	}
	
	if( $query->is_search ) {
		global $wpdb;
		$args = array(
			'posts_per_page'   => 1,
			'post_type'        => 'special-search',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'cs_search_term',
					'value' => $query->query_vars['s'],
					'compare' => '='
				),
				array(
					'key' => 'cs_synonyms',
					'value' => $query->query_vars['s'].',',
					'compare' => 'LIKE'
				)
			)
		);
		$cs_post = get_posts( $args );		
		$keywords = array(); 
		if( !empty($cs_post) ) {
			$cs_post = $cs_post[0];
			$exclude_posts = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->prefix . "postmeta` WHERE meta_key = 'cs_hide_post_id' AND meta_value = 1");
			if(!empty($exclude_posts)) {
				if(in_array($cs_post->ID, $exclude_posts)) {
					return $where;
				}
			}
			
			// set searched_post_id into the session
			$_SESSION['cs_searched_post_id'] = $cs_post->ID; 
			$cs_search_term = get_post_meta($cs_post->ID, 'cs_search_term', true);
			$cs_synonyms = get_post_meta($cs_post->ID, 'cs_synonyms', true);
			if( !empty($cs_synonyms) ) {
				$cs_synonyms = explode(",", $cs_synonyms);
			}
			
			$keywords = $cs_synonyms;
			$keywords[] = $cs_search_term;
			$keywords = array_filter($keywords);
			
			if( !empty($keywords) ) {
				$where_string = '';
				$searchor = '';
				foreach($keywords as $k=>$keyword) {
					$keyword = '%' . $wpdb->esc_like( $keyword ) . '%';
					$where_string .= $wpdb->prepare( "$searchor ( ($wpdb->posts.post_title LIKE %s) OR ($wpdb->posts.post_content LIKE %s) )", $keyword, $keyword );
					$searchor = ' OR ';
				}
				
				if ( ! empty( $where_string ) ) {
					$where_string = " AND ( {$where_string} ) ";
					
					if ( !is_user_logged_in() ) {
						$where_string .= " AND ($wpdb->posts.post_password = '') ";
					}
				}
		
				$where = $where_string;				
			}
		} else {
			unset($_SESSION['cs_searched_post_id']);
		}
	}
	
	return $where;
}

/**
 * Filter the pre get posts.
 *
 * @return string
 */
function cs_pre_get_posts( $query ) {
	if( $query->is_admin ) {
		return $query;
	}
	
	if( !$query->is_main_query() ) {
		return $query;
	}
	
	if( $query->is_search ) { 
		global $wpdb;
		
		$exclude_posts = $wpdb->get_col("SELECT `post_id` FROM `".$wpdb->prefix . "postmeta` WHERE meta_key = 'cs_hide_post_id' AND meta_value = 1");
		if(!empty($exclude_posts)) {
			$query->set('post__not_in', $exclude_posts);
		}
		
		$exculded_term_array = array();
		$exclude_terms_data = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix . "cs_excluded_list`");
		if( !empty($exclude_terms_data) ) {
			$exclude_terms = array();
			
			foreach($exclude_terms_data as $value) {
				$exclude_terms[$value->taxonomy_type][] = $value->term_id;
			}
			
			foreach($exclude_terms as $taxonomy_type=>$term) {			
				$exculded_term_array[] = array(
					'taxonomy' => $taxonomy_type,
					'field' => 'id',
					'terms' => $term,
					'operator' => 'NOT IN'
				);
			}
		}
		
		if( !empty($exculded_term_array) ) {
			$tax_query = array(
				'tax_query' => array(
					'relation' => 'AND',
					$exculded_term_array
				)
		    );
			$query->set( 'tax_query', $tax_query);
		}
        
	}
	return $query;
}

/**
 * Single posts cs_filter_main_search_post_limits.
 *
 */
function cs_filter_main_search_post_limits( $limit, $query ) {

	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$cs_search_pagination = get_option( "cs_search_pagination" );
		if($cs_search_pagination > 0) {
        	$limit = "LIMIT 0, $cs_search_pagination";
        }
	}

	return $limit;
}

/**
 * Sets number of posts found cs_set_main_search_post_limits.
 *
 */
function cs_set_main_search_post_limits( $found_posts, $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		$cs_search_pagination = get_option( "cs_search_pagination" );
		if($cs_search_pagination > 0) {
			if( $found_posts > $cs_search_pagination ) {
        		$found_posts = $cs_search_pagination;
        	}
        }
	}
	
	return $found_posts;
}

/**
 * Show CS post type content at top of the search page.
 *
 * @return string(html)
 */
function cs_search_show_post() {
	global $wp;
	if(isset( $_SESSION['cs_searched_post_id'] )) {
		$cs_post = get_post($_SESSION['cs_searched_post_id']);
		if( !empty($cs_post) ) {
			echo "<article class='cs-search-wrap hentry'><div class='entry-summary'>".apply_filters('the_content', $cs_post->post_content)."</div></article>";
		}
	}
}

/**
 * Reorder the post acc to sticky posts show at top.
 *
 * @return string
 */
function cs_reorder_the_posts($posts, $query = false) {  //echo $GLOBALS['wp_query']->request; die;		
    if( is_search() && 0 == get_query_var( 'paged' )){ 
    	if(isset( $_SESSION['cs_searched_post_id'] )) {
    				
			$cs_pinned_post_ids = get_post_meta($_SESSION['cs_searched_post_id'], 'cs_pinned_post_ids', true);  
			if(!empty($cs_pinned_post_ids)) {
				$args = array( 'posts_per_page' => -1, 'post_type' => 'any', 'post__in' => $cs_pinned_post_ids , 'orderby'	=>	'post__in');
				$pinned_posts = get_posts($args);
				foreach( $posts as $post_index => $post ) {
					if( in_array($posts[ $post_index ]->ID, $cs_pinned_post_ids) ) {
				 		unset( $posts[ $post_index ] );
			 		} 
			 	}
			 }
		 	// single result redirected to post				 
			$cs_destination_url = get_post_meta($_SESSION['cs_searched_post_id'], 'cs_destination_url', true);        	
			if(trim($cs_destination_url) != '') {
				wp_redirect( get_bloginfo('url').$cs_destination_url );
				exit;
			} elseif(!empty($posts) && count($posts) == 1) {
				$cs_one_result_redirect = get_option( "cs_one_result_redirect" );
				if($cs_one_result_redirect == 1) {
					wp_redirect( get_permalink( $posts['0']->ID ) );
					exit;
				}
			}
						
		 	if(!empty($pinned_posts)) { 
	 			$posts = array_merge( $pinned_posts, $posts );
	 		}
			
		 }
    }
    return $posts;
}

/**
 * Set search results order.
 *
 * @return string
 */
function cs_parse_search_order($clause, $query) {

	return '';
}
