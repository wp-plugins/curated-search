jQuery(document).ready(function($) {
	
	if( $('#cs_selected_post_type').length ) {
		$('#cs_selected_post_type').on('change', function() {
			
			var data = {
				'action'		: 'cs_get_taxonomies',
				'cs_ajax_nonce'	: cs.ajax_nonce,
				'post_type'	: $(this).val()
			};
			$.post(cs.ajax_url, data, function(response) {
				if( response != '' ) {
					response = JSON.parse(response);
					if(response.cs_html) {
						$('#cs_loaded_post_type').html(response.cs_html);
						$('#cs_load_terms').html('<option value="">'+cs.ajax_select+'</option>');
					}
				}
				
			});
		});
	}
	
	if( $('#cs_loaded_post_type').length ) {
		$('#cs_loaded_post_type').on('change', function() {
			
			var data = {
				'action'		: 'cs_get_terms',
				'cs_ajax_nonce'	: cs.ajax_nonce,
				'cs_term'	    : $(this).val()
			};
			$.post(cs.ajax_url, data, function(response) {
				if( response != '' ) {
					response = JSON.parse(response);
					if(response.cs_html) {
						$('#cs_load_terms').html(response.cs_html);
					} else {
						$('#cs_load_terms').html('<option value="">'+cs.ajax_select+'</option>');
					}
						
				}
				
			});
		});
	}
	
	if( $('#cs_add_to_exclude_list').length ) {
		$('#cs_add_to_exclude_list').on('click', function() {
			$('#cs_status').html('');
			$('#cs_status').removeClass('updated');
			$('#cs_status').removeClass('error');
			var data = {
				'action'		: 'cs_add_to_exclude_list',
				'cs_ajax_nonce'	: cs.ajax_nonce,
				'cs_term_data'	: $('#cs_load_terms').val()
			};
			$.post(cs.ajax_url, data, function(response) {
				if( response != '' ) {
					response = JSON.parse(response);
					if(response.error == 0) {
						$('#add_exclude_list').prepend(response.cs_html);
						$('#cs_status').html('<p>'+response.message+'</p>');
						$('#cs_status').addClass('updated');
					} else {
						$('#cs_status').html('<p>'+response.message+'</p>');
						$('#cs_status').addClass('error');
					}
				}
				
			});
		});
	}
	
	$('#cs_selected_post_type').trigger('change');
	$('#cs_loaded_post_type').trigger('change');
	
	$( "#pinned-lists" ).sortable({ 
		update: function( event, ui ) {
			var sorted = $(this).sortable( "serialize");
			var data = {
				'action'		: 'cs_pinned_sort_order',
				'cs_ajax_nonce'	: cs.ajax_nonce,
				'pinned_orders'	    : sorted,
				'post_id'   : jQuery('#post_ID').val()
			};
			$.post(cs.ajax_url, data);
		}
	});
	
	// Tabs
	$('#cs_tabs').tabs({
		activate: function(event, ui) {
			$('.cs_response').html('');
			$('.cs_load_posts').val('');
		}
	});
	$('.cs_load_posts').val('');
	
	if( $('#cs_pintotop').length ) {
		$('#cs_pintotop').on('click', function() {
			var $html = '';
			var selected_ids = [];
			$(document).find('.cs_response ul li input:checked').each(function() {
				selected_ids.push($(this).val());
				$html += '<li id="item-'+$(this).val()+'" class="menu-item-handle ui-sortable-handle">'+$(this).data('post_title')+'<span class="remove handle dashicons-dismiss" onclick="cs_remove_pinned('+$(this).val()+')"></span></li>';
			});
			
			var data = {
				'action'		: 'cs_pintotop',
				'cs_ajax_nonce'	: cs.ajax_nonce,
				'pin_post_ids'	: selected_ids,
				'post_id'   : jQuery('#post_ID').val()
			};
			$.post(cs.ajax_url, data, function(response) {
				$('#pinned-lists').append($html);				
			});
			
		});
	}

});

function cs_remove_exclude_list(elmnt, rmv_prams) {
	if (confirm(cs.ajax_delete_text) == true) {
		jQuery('#cs_status').html('');
		jQuery('#cs_status').removeClass('updated');
		var data = {
			'action'		: 'cs_remove_exclude_list',
			'cs_ajax_nonce'	: cs.ajax_nonce,
			'rmv_prams'	    : rmv_prams
		};
		jQuery.post(cs.ajax_url, data, function(response) {
			if( response != '' ) {
				response = JSON.parse(response);
				if(response.error == 0) {
					jQuery(elmnt).closest('tr').remove();
					jQuery('#cs_status').html('<p>'+response.message+'</p>');
					jQuery('#cs_status').addClass('updated');
				}
			}
		
		});
	}
}

function cs_load_posts(post_type, post_parm) {
	var uLhtmL = '<ul>';
	if (post_type) {
		var data = {
			'action'		: 'cs_autocomplete',
			'cs_ajax_nonce'	: cs.ajax_nonce,
			'post_type'	    : post_type,
			'post_parm'	: jQuery(post_parm).val()
		};
		jQuery.ajax({
			method: "POST",
			url: cs.ajax_url,
			data: data,
			dataType : 'json'
		})
		.done(function( response ) {
			if( response != '' ) {
				if(response.error == 0) {
					jQuery.each(response.cs_posts, function( index, value ) {
						uLhtmL += '<li><input type="checkbox" name="pin_post_ids[]" data-post_title="'+value.post_title+'" value="'+value.ID+'" >'+value.post_title+'</li>';
					});
					uLhtmL += '</ul>';
					jQuery('.cs_response').html(uLhtmL);
				}
			}
		});
	}
}

function cs_remove_pinned(rm_post_id) {
	if (post_type) {
		var data = {
			'action'		: 'cs_remove_pinned',
			'cs_ajax_nonce'	: cs.ajax_nonce,
			'rmv_post_id'	: rm_post_id,
			'post_id'   : jQuery('#post_ID').val()
		};
		jQuery.ajax({
			method: "POST",
			url: cs.ajax_url,
			data: data,
			dataType : 'json'
		})
		.done(function( response ) {
			jQuery('#item-'+rm_post_id).remove();
		});
	}
}

