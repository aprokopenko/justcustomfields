jQuery(document).ready(function(){
	//JSON.parse('{"jsontest":"1"}');
	
	initAddFieldsetBox();
	initFieldsetsEdit();
	initAjaxBoxClose();
	initFieldsetFields();
})

/**
 *	init fieldset add box
 */
function initAddFieldsetBox(){
	// init ajax submit
	jQuery('#jcform_add_fieldset').submit(function(e){
		e.preventDefault();
		
		var title = jQuery('#jcf_fieldset_title').val();
		var data = {
			'action': 'jcf_add_fieldset',
			'title': title
		};

		var loader = jQuery(this).find('img.ajax-feedback');
		
		// ajax call
		jcf_ajax(data, 'json', loader, function(response){
			// all is ok: refresh page
			window.location.reload(true);
		});
		
		return false;
	});
}

/**
 *	init all fieldset edit operations (edit/save/delete)
 */
function initFieldsetsEdit(){
	
	// delete
	jQuery('#jcf_fieldsets a.jcf_fieldset_delete').click(function(){
		
		if( confirm( jcf_textdomain.confirm_fieldset_delete ) )
		{
			var f_id = jQuery(this).attr('rel');
			var data = {
				'action': 'jcf_delete_fieldset',
				'fieldset_id': f_id
			};
				
			var loader = jQuery(this).parents('h3').find('img.ajax-feedback');

			jcf_ajax(data, 'json', loader, function(response){
				jQuery('#jcf_fieldset_' + f_id).remove();
				// clean ajax container
				jcf_hide_ajax_container();
			});
		}
	})
	
	// change
	jQuery('#jcf_fieldsets a.jcf_fieldset_change').click(function(){
		var loader = jQuery(this).parents('h3').find('img.ajax-feedback');

		var f_id = jQuery(this).attr('rel');
		var data = {
			'action': 'jcf_change_fieldset',
			'fieldset_id': f_id
		};

		jcf_ajax(data, 'html', loader, function(response){
			jcf_show_ajax_container( response );
		});
	})
	
	// init delete button on change popup
	jQuery('#jcf_ajax_content .jcf_edit_fieldset a.field-control-remove').live('click', function(){
		var f_id = jQuery(this).parents('form:first').find('input[name=fieldset_id]').val();
		jQuery('#jcf_fieldset_' + f_id + ' a.jcf_fieldset_delete').click();
		return false;
	});
	
	// save on edit form
	jQuery('#jcform_edit_fieldset').live('submit', function(e){
		e.preventDefault();
		var f_id = jQuery(this).find('input[name=fieldset_id]').val();
		var data = {
			'action': 'jcf_update_fieldset',
			'fieldset_id': f_id,
			'title': jQuery('#jcf_edit_fieldset_title').val()
		};
		
		var loader = jQuery(this).find('img.ajax-feedback');

		jcf_ajax(data, 'html', loader, function(response){
			// update title
			jQuery('#jcf_fieldset_' + f_id + ' h3 span').text( response.title );
			
			jcf_hide_ajax_container();
		});
		
		return false;
	})
	
}

/**
 *	init fieldset fields grid and add form
 */
function initFieldsetFields(){
	
	// init add form
	jQuery('#jcf_fieldsets form.jcform_add_field').submit(function(e){
		e.preventDefault();
		
		var data = { action: 'jcf_add_field' };
		
		jQuery(this).find('input,select').each(function(i, input){
			data[ jQuery(input).attr('name') ] = jQuery(input).val();
		})
		
		var loader = jQuery(this).find('img.ajax-feedback');
		
		jcf_ajax(data, 'html', loader, function(response){
			jcf_show_ajax_container( response );
		})
		
		return false;
	});
	
	// init save button on edit form
	jQuery('#jcform_edit_field').live('submit', function(e){
		e.preventDefault();

		// get query string from the form
		var query = jQuery('#jcform_edit_field').formSerialize();
		var data = 'action=jcf_save_field' + '&' + query;

		var loader = jQuery(this).find('img.ajax-feedback');
		
		// send request
		jcf_ajax(data, 'json', loader, function(response){
			
			var fieldset = jQuery('#the-list-' + response.fieldset_id);
			
			if( response.is_new ){
				// check if fieldset is empty
				if( fieldset.find('td').size() == 1 ){
					// remove empty row container
					fieldset.find('tr').remove();
				}
				// add new row
				var html;
				html = '<tr id="field_row_' + response.id + '">';
				html += '	<td class="check-column"><span class="drag-handle">move</span></td>';
				html += '<td><strong><a href="#" rel="' + response.id + '">' + response.instance.title + '</a></strong>';
				html += '	<div class="row-actions">';
				html += '		<span class="edit"><a href="#" rel="' + response.id + '">'+ jcf_textdomain.edit +'</a></span> |';
				html += '		<span class="delete"><a href="#" rel="' + response.id + '">'+ jcf_textdomain.delete +'</a></span>';
				html += '	</div>';
				html += '</td>';
				html += '<td>'+response.instance.slug+'</td>';
				html += '<td>'+response.id_base+'</td>';
				html += '<td>'+( (response.instance.enabled)? jcf_textdomain.yes : jcf_textdomain.no )+'</td>';
				fieldset.append(html);
			}
			
			// update fieldset row
			var row = jQuery('#field_row_' + response.id);
			row.find('strong a').text(response.instance.title);
			row.find('td:eq(2)').text(response.instance.slug);
			row.find('td:eq(4)').text( (response.instance.enabled)? jcf_textdomain.yes : jcf_textdomain.no );
			
			// close add box at the end
			jcf_hide_ajax_container();
		})
		
		return false;
	});
	
	// delete button
	jQuery('#jcf_fieldsets tbody span.delete a').live('click', function(){
		if( confirm( jcf_textdomain.confirm_field_delete ) ){
			var row = jQuery(this).parents('tr:first');
			var f_id = jQuery(this).parents('tbody:first').attr('id').replace('the-list-', '');
			var data = {
				action: 'jcf_delete_field',
				fieldset_id: f_id,
				field_id: jQuery(this).attr('rel')
			};
			
			jcf_ajax(data, 'json', null, function(response){
				row.remove();
				// close edit box if exists
				jcf_hide_ajax_container();
			});
		}
		return false;
	})
	
	// edit button
	jQuery('#jcf_fieldsets tbody span.edit a, #jcf_fieldsets tbody strong > a').live('click', function(){
		var f_id = jQuery(this).parents('tbody:first').attr('id').replace('the-list-', '');
		var data = {
			action: 'jcf_edit_field',
			fieldset_id: f_id,
			field_id: jQuery(this).attr('rel')
		};
			
		jcf_ajax(data, 'html', null, function(response){
			
			jcf_show_ajax_container(response);
			
		});
		
		return false;
	})
	
	// delete button in edit form
	jQuery('#jcform_edit_field a.field-control-remove').live('click', function(e){
		var field_id = jQuery(this).parents('form:first').find('input[name=field_id]').val();
		var row = jQuery('#field_row_' + field_id);
		row.find('span.delete a').click();
		return false;
	});
	
	// init sortable
	jQuery('#jcf_fieldsets tbody').sortable({
		handle: 'span.drag-handle',
		opacity:0.7,
		placeholder: 'sortable_placeholder',
		start: function (event, ui) { 
			ui.placeholder.html('<td colspan="4"><br>&nbsp;</td>');
		},
		stop: function(event, ui){
			// ui.item - item in the list
			var order = '';
			var fieldset = jQuery(ui.item).parent();
			var f_id = fieldset.attr('id').replace('the-list-', '');
			fieldset.find('tr').each(function(i, tr){
				order += jQuery(tr).attr('id').replace('field_row_', '') + ',';
			});
			
			var data = {
				'action': 'jcf_fields_order',
				'fieldset_id': f_id,
				'fields_order': order
			};

			//console_log(data);
			jcf_ajax(data, 'json');
		}
	});
}

/**
 *	ajax functions below
 */
function initAjaxBoxClose(){
	jQuery('#jcf_ajax_content a.field-control-close').live('click', function(){
		jcf_hide_ajax_container();
	});
}

function jcf_hide_ajax_container(){
	jQuery('#jcf_ajax_content').html( '' );
	jQuery('#jcf_ajax_container').hide();
}

function jcf_show_ajax_container( response ){
	jQuery('#jcf_ajax_container').show();
	jQuery('#jcf_ajax_content').html( response );
}

function jcf_ajax( data, respType, loader, callback ){
	// save to local variables to have ability to call them inside ajax
	var _callback = callback;
	var _loader = loader;
	var _respType = respType;
	
	console_log('wp-ajax call: ' + data.action);
	
	// add post_type to data
	var post_type = jQuery('#jcf_post_type_hidden').val();
	if( typeof(data) == 'object' ){
		data.post_type = post_type;
	}
	else if( typeof(data) == 'string' ){
		data += '&post_type=' + post_type;
	}
	
	// if we have loader - show loader
	if(_loader && _loader.size) _loader.css('visibility', 'visible');
	
	// send ajax
	jQuery.post(ajaxurl, data, function(response){
		console_log(response);
		
		// if we have loader - hide loader
		if(_loader && _loader.size) _loader.css('visibility', 'hidden');
		
		// if json - check for errors
		if( _respType == 'json' && response.status != '1' ){
			alert( response.error );
			return;
		}
		
		// if no errors - call main callback
		_callback( response );
	})	
}

function console_log( mixed ){
	if( window.console ){
		window.console.info(mixed);
	}
}