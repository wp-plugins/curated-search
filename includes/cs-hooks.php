<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Hooks for add Add new coulmns in CS post type listing.
add_filter('manage_edit-special-search_columns' , 'cs_add_post_columns');
// Hooks for Show the value in CS post type listing columns.
add_action('manage_special-search_posts_custom_column', 'cs_show_columns');

// Hooks for include css & js script to CS admin settings.
add_action('admin_enqueue_scripts','cs_enqueue_assets');

// Hooks for filter the search 'where' query.
//add_action('posts_where', 'cs_search', 10, 2);
add_filter('posts_search', 'cs_posts_search', 10, 2);

// Hooks for filter the pre get posts
add_filter( 'pre_get_posts' , 'cs_pre_get_posts' );

// Hooks for show CS post type content at top of the search page
add_action('cs_search_after_title', 'cs_search_show_post');

// Hooks for reorder the posts acc to sticky posts show at top
add_filter( 'the_posts', 'cs_reorder_the_posts' );

// Hooks for limit the no of posts in search results
add_filter( 'post_limits', 'cs_filter_main_search_post_limits', 10, 2 );

// Hooks for set the found posts in search results
add_filter( 'found_posts', 'cs_set_main_search_post_limits', 10, 2 );

// Hooks for set searched results order 
add_filter('posts_search_orderby', 'cs_parse_search_order', 10, 2);
