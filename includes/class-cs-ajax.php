<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Curated_Search_Ajax' ) ) :
	class Curated_Search_Ajax {
		/**
		 * init the constructor.
		 */
		function __construct() { 
			$ajax_events = array(
				'cs-get-taxonomies'			=>	true,
				'cs-get-terms'				=>	true,
				'cs_add_to_exclude_list'	=>	true,
				'cs_remove_exclude_list'	=>	true,
				'cs_pinned_sort_order'		=>	true,
				'cs_autocomplete'			=>	true,
				'cs_pintotop'				=>	true,
				'cs_remove_pinned'  		=>	true,
			);
		
			foreach( $ajax_events as $ajax_event => $nopriv ) {			
				$ajax_event_callback = str_replace('-', '_', $ajax_event);
				add_action( 'wp_ajax_'.$ajax_event_callback, array($this, $ajax_event_callback) );
				
				if($nopriv) {
					add_action( 'wp_ajax_nopriv_'.$ajax_event_callback, array($this, $ajax_event_callback) );
				}
			}
		}
		
		/**
		 * Get all taxonomies.
		 *
		 * @return string(json)
		 */
		public function cs_get_taxonomies() {
			check_ajax_referer( 'cs-ajax', 'cs_ajax_nonce' );
			
			if( isset($_POST['post_type']) ) {
				$data = array();
				$post_type = $_POST['post_type'];
				$taxonomies = get_object_taxonomies( $post_type, 'objects' );
				if(isset($taxonomies['post_format'])) {
					unset($taxonomies['post_format']);
				}
				$data['cs_html'] = '<option value="">'.__('Select', 'curated_search').'</option>';
				if( !empty($taxonomies) ) { 
					foreach($taxonomies as $key => $tax) { 
						$data['cs_html'] .= '<option value="'.$key.'||'.$post_type.'">'.$tax->labels->name.'</option>';
					}
				} 
				echo json_encode($data);
			}
			die();
		}
		
		/**
		 * Get all terms by taxonomy.
		 *
		 * @return string(json)
		 */
		public function cs_get_terms() {
			check_ajax_referer( 'cs-ajax', 'cs_ajax_nonce' );
			
			if( isset($_POST['cs_term']) ) {
				$data = array(); 
				$cs_term_data = $_POST['cs_term'];
				$cs_term = explode("||", $cs_term_data);
				$categories = get_terms( $cs_term[0], 'orderby=count&hide_empty=0' );
				$data['cs_html'] = '<option value="">'.__('Select', 'curated_search').'</option>';
				if( !empty($categories) ) { 
					foreach($categories as $key => $cat) {
						$data['cs_html'] .= '<option value="'.$cat->taxonomy.'||'.$cat->term_id.'||'.$cs_term[1].'">'. $cat->name.'</option>';
					}
				}
				echo json_encode($data);
			}
			die();
		}
		
		/**
		 * Ajax Add terms to exclude lists.
		 *
		 * @return string(json)
		 */
		public function cs_add_to_exclude_list() {
			check_ajax_referer( 'cs-ajax', 'cs_ajax_nonce' );
			
			if( isset($_POST['cs_term_data']) ) { 
				$data = array(); $response = array(); 
				$cs_term_data = $_POST['cs_term_data'];
				$cs_terms = explode("||", $cs_term_data);
				global $wpdb;
				$table_name = $wpdb->prefix . "cs_excluded_list"; 
				$already_exists = $wpdb->get_row("SELECT * FROM `$table_name` WHERE `term_id` = '".$cs_terms[1]."' AND `taxonomy_type`='".$cs_terms[0]."' AND `post_type`='".$cs_terms[2]."'");
				if(trim($cs_terms[0]) !='' && trim($cs_terms[1]) != '') {
					$data = array(
								'term_id' 		=>	$cs_terms[1],
								'taxonomy_type' =>	$cs_terms[0],
								'post_type' 	=>	$cs_terms[2]
							);
				} 
				if(empty($already_exists) && !empty($data)) { 
					$wpdb->insert( $table_name, $data );
					$cs_term_data = "'".$cs_term_data."'";
					$single_term = get_term_by('id', $data['term_id'], $data['taxonomy_type']);	
					$p_type = get_taxonomy($single_term->taxonomy)->object_type[0];				
					$cs_html = '<tr>
									<td class="column-cb">'.$single_term->name.' ( '.$p_type.' | '.$single_term->taxonomy.' )</td>
									<td class="column-cb"><a href="javascript:;" style="color:red;" onClick="cs_remove_exclude_list(this, '.$cs_term_data.');">'.__("Remove", "curated_search").'</a></td>
								</tr>';
					$response = array(
									'error' 	=>	0,
									'message' 	=>	__('Added Successfully!', 'curated_search'),
									'cs_html'   =>  $cs_html
								);
				} else {
					$response = array(
									'error' 	=>	1,
									'message' 	=>	__('Already exists in exclude list', 'curated_search')
								);
				}

				echo json_encode($response);
			}
			die();
		}
		
		/**
		 * Ajax remove terms to exclude lists.
		 *
		 * @return string(json)
		 */
		public function cs_remove_exclude_list() {
			check_ajax_referer( 'cs-ajax', 'cs_ajax_nonce' );
			
			if( isset($_POST['rmv_prams']) ) { 
				$data = array(); $response = array(); 
				$cs_term_data = $_POST['rmv_prams'];
				$cs_terms = explode("||", $cs_term_data);
				global $wpdb;
				$table_name = $wpdb->prefix . "cs_excluded_list"; 
				$already_exists = $wpdb->get_row("DELETE FROM `$table_name` WHERE `term_id` = '".$cs_terms[1]."' AND `taxonomy_type`='".$cs_terms[0]."'");
				$response = array(
					'error' 	=>	0,
					'message' 	=>	__('Removed Successfully!', 'curated_search')
				);
				
				echo json_encode($response);
			}
			die();
		}
		
		/**
		 * Ajax cs_pinned_sort_order.
		 *
		 * @return string(json)
		 */
		public function cs_pinned_sort_order() { 
			check_ajax_referer( 'cs-ajax', 'cs_ajax_nonce' );
			
			if( isset($_POST['pinned_orders']) ) { 
				parse_str($_POST['pinned_orders'], $pinned_posts);
				$pinned_posts = array_unique($pinned_posts['item']);
				$post_id = $_POST['post_id'];
				update_post_meta( $post_id, 'cs_pinned_post_ids', $pinned_posts );
			}
			die();
		}
		
		/**
		 * Ajax cs_autocomplete for get posts.
		 *
		 * @return string(json)
		 */
		public function cs_autocomplete() { 
			check_ajax_referer( 'cs-ajax', 'cs_ajax_nonce' );
			
			if( isset($_POST['post_type']) ) { 
				global $wpdb;
				$cs_posts = $wpdb->get_results("SELECT `ID`, `post_title` FROM $wpdb->posts WHERE `post_type` = '".trim($_POST['post_type'])."' AND `post_status` = 'publish' AND `post_title` LIKE '%".$_POST['post_parm']."%'");
				if(!empty($cs_posts)) {
					$response = array(
						'error' 	=>	0,
						'cs_posts' 	=>	$cs_posts
					);
				} else {
					$response = array(
						'error' 	=>	1,
						'message' 	=>	__('No Suggestion!', 'curated_search')
					);
				}
				echo json_encode($response);
			}
			die();
		}
		
		/**
		 * Ajax cs_pintotop for get posts.
		 *
		 * @return string(json)
		 */
		public function cs_pintotop() { 
			check_ajax_referer( 'cs-ajax', 'cs_ajax_nonce' );
			$post_id = $_POST['post_id'];			
			$cs_pinned_post_ids = get_post_meta($post_id, 'cs_pinned_post_ids', true); 
			if(!empty($cs_pinned_post_ids)) {
				$cs_pinned_post_ids = array_filter($cs_pinned_post_ids);
				if( isset($_POST['pin_post_ids'])  && !empty($_POST['pin_post_ids'])) {
					$pinned_posts = array_merge($cs_pinned_post_ids, $_POST['pin_post_ids']);	
				} else {
					$pinned_posts = $cs_pinned_post_ids;
				}
			} else {
				$pinned_posts = $_POST['pin_post_ids'];
			} 
				
			ksort($pinned_posts);
			if( isset($_POST['pin_post_ids']) ) {
				$pinned_posts = array_unique($pinned_posts); 
				update_post_meta( $post_id, 'cs_pinned_post_ids', $pinned_posts );
			}
			die();
		}
		
		/**
		 * Ajax remove_pinned.
		 *
		 * @return string(json)
		 */
		public function cs_remove_pinned() { 
			check_ajax_referer( 'cs-ajax', 'cs_ajax_nonce' );
			$rm_post_id = $_POST['rmv_post_id'];	
			$post_id = $_POST['post_id'];		
			$cs_pinned_post_ids = get_post_meta($post_id, 'cs_pinned_post_ids', true); 
			if(!empty($cs_pinned_post_ids) && $rm_post_id > 0) {
				unset( $cs_pinned_post_ids[array_search( $rm_post_id, $cs_pinned_post_ids )] );
				update_post_meta( $post_id, 'cs_pinned_post_ids', $cs_pinned_post_ids );
			}
			die();
		}
		
	
	}
endif;
new Curated_Search_Ajax();
