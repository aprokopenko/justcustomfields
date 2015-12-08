jQuery(document).ready(function(){
	//JSON.parse('{"jsontest":"1"}');
	
	initAddFieldsetBox();
	initFieldsetsEdit();
	initAjaxBoxClose();
	initFieldsetFields();
	initImport();
	initExport();
	initSettings();
});

jQuery(document).scroll(function(){
	initEditFormPosition();
});

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
	});

	// init sortable
	jQuery('#jcf_fieldsets').sortable({
		handle: 'h3.header span.drag-handle',
		opacity:0.7,
		stop: function(event, ui){
			var order = '';
			jQuery(this).find('.jcf_inner_box').each(function(i, tr){
				order += jQuery(this).attr('id').replace('jcf_fieldset_', '') + ',';
			});

			var data = {
				'action': 'jcf_order_fieldsets',
				'fieldsets_order': order
			};
			jcf_ajax(data, 'json');
		}
	});

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
				html = '<tr id="field_row_' + response.id + '" class="field_row ' + response.id + '">';
				html += '	<td class="check-column"><span class="drag-handle">move</span></td>';
				html += '	<td><strong><a href="#" rel="' + response.id + '">' + response.instance.title + '</a></strong>';
				html += '	<div class="row-actions">';
				html += '		<span class="edit"><a href="#" rel="' + response.id + '">'+ jcf_textdomain.edit +'</a></span> |';
				html += '		<span class="delete"><a href="#" rel="' + response.id + '">'+ jcf_textdomain.delete +'</a></span>';
				html += '	</div>';
				if(response.collection_fields) {
					html += '   <ul>';
					html += '       <li><strong>' + jcf_textdomain.type + '</strong>: '+response.id_base+'</li>';
					html += '       <li><strong>' + jcf_textdomain.slug + '</strong>: '+response.instance.slug+'</li>';
					html += '       <li><strong>' + jcf_textdomain.enabled + '</strong>: '+( (response.instance.enabled)? jcf_textdomain.yes : jcf_textdomain.no )+'</li>';
					html += '   </ul>';
				}
				html += '</td>';
				if(response.collection_fields) {
					html += '<td colspan="3" class="collection_list" data-collection_id="' + response.id + '">' + response.collection_fields + '</td>';
				}
				else {
					html += '<td>'+response.instance.slug+'</td>';
					html += '<td>'+response.id_base+'</td>';
					html += '<td>'+( (response.instance.enabled)? jcf_textdomain.yes : jcf_textdomain.no )+'</td>';
				}
				fieldset.append(html);
				if(response.collection_fields){
						jQuery('#jcf_fieldsets table.collection-fields-table > tbody').sortable({
							handle: 'span.drag-handle',
							opacity:0.7,
							placeholder: 'collection_sortable_placeholder',
							scroll: true,
							start: function (event, ui) { 
								ui.placeholder.html('<td colspan="5"><br>&nbsp;</td>');
							},
							stop: function(event, ui){ collectionFieldSortableStop(event, ui, this); }
						});
				}
			}
			if(!response.collection_fields) {
				// update fieldset row
				var row = jQuery('#field_row_' + response.id);
				row.find('strong a').text(response.instance.title);
				row.find('td:eq(2)').text(response.instance.slug);
				row.find('td:eq(4)').text( (response.instance.enabled)? jcf_textdomain.yes : jcf_textdomain.no );
			}
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
				row.next('td.collection_list:first').remove();
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
	jQuery('#jcf_fieldsets table.fieldset-fields-table > tbody').sortable({
		handle: 'span.drag-handle',
		opacity:0.7,
		placeholder: 'sortable_placeholder',
		scroll: true,
		start: function (event, ui) {
			ui.placeholder.html('<td colspan="5"><br>&nbsp;</td>');
		},
		stop: function(event, ui){
			// ui.item - item in the list
			var order = '';
			var fieldset = jQuery(ui.item).parent();
			var f_id = fieldset.attr('id').replace('the-list-', '');
			fieldset.find('tr.field_row').each(function(i, tr){
				if(jQuery(tr).attr('id')) order += jQuery(tr).attr('id').replace('field_row_', '') + ',';
			});
			
			var data = {
				'action': 'jcf_fields_order',
				'fieldset_id': f_id,
				'fields_order': order
			};
			//pa(data);
			jcf_ajax(data, 'json');
		}
	});

}

/**
 *	init import
 */
function initImport(){
	jQuery('#jcf_import_fields').submit(function(e){
		e.preventDefault();

		var query = new FormData(jQuery( this ).get(0));
		jQuery.ajax({
			url: 'admin-ajax.php',
			type: 'post',
			contentType: false,
			processData: false,
			data: query,
			success: function(responce){
				modalWindow(responce);
			}
		  });
	});
	initImportExportCheckboxes();
}

/**
 *	init export
 */
function initExport(){
	jQuery('a#export-button').click(function(){
		var data = {
			'action': 'jcf_export_fields_form'
		}
		jcf_ajax(data, 'html', null, function(response){
			modalWindow(response);
		});
	});
	initImportExportCheckboxes();
}

/**
 *	init Import/Export checkboxes changing
 */
function initImportExportCheckboxes(){
	// checked fields
	jQuery('#jcf_save_import_fields input[type="checkbox"], #jcf_export_fields input[type="checkbox"]').live('change', function(){
		var data_val = jQuery( this ).val();
		var data_id =  jQuery( this ).attr('id');
		var data_checked = jQuery( this ).is(':checked');
		if( jQuery( this ).hasClass('choose_field') ){
				jQuery('input[data-fieldset="' + data_val + '"].jcf_hidden_fieldset').attr({'disabled':!data_checked});
				jQuery('input[data-field="' + data_id + '"]').attr({'disabled':!data_checked});
		}
		else if( jQuery( this ).hasClass('jcf-choose_fieldset') ){
				jQuery( this ).parent().parent().parent().find('input[type="checkbox"]').attr({'checked':!data_checked});
				jQuery('input[data-fieldset="' + data_val + '"]').attr({'disabled':data_checked});
		}
		else if( jQuery( this ).hasClass('jcf-select_content_type') ){
				jQuery( this ).parent().parent().parent().find('input[type="checkbox"]').attr({'checked':!data_checked});
				jQuery( this ).parent().parent().parent().find('input[type="hidden"]').attr({'disabled':data_checked});
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
	
	//pa('wp-ajax call: ' + data.action);
	
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
		//pa(response);
		
		// if we have loader - hide loader
		if(_loader && _loader.size) _loader.css('visibility', 'hidden');
		
		// if json - check for errors
		if( _respType == 'json' && response.status != '1' ){
			alert( response.error );
			return;
		}
		
		// if no errors - call main callback
		if(_callback) _callback( response );
	})	
}

function pa( mixed ){
	if( window.console ){
		window.console.info(mixed);
	}
}

function modalWindow(content){
	jQuery('body').append('<div class="media-modal wp-core-ui jcf_modalWindow"><div class="media-modal-content">'+content+'</div><a href="#" class="media-modal-close"><span class="media-modal-icon"></span></a></div>');

	jQuery('.media-modal-close').click(function(){
		jQuery('.jcf_modalWindow').remove();
	});
}

/**
 *	init settings
 */
function initSettings(){
	var jcf_read_settings_active = jQuery('#jcform_settings').find('input[name="jcf_read_settings"]:checked').attr('id');
	jQuery('#jcform_settings input[name="jcf_read_settings"]').live('change', function(){
		var data = {
				'action' : 'jcf_check_file',
				'jcf_multisite_setting' : jQuery('#jcform_settings').find('input[name="jcf_multisite_setting"]:checked').val(),
				'jcf_read_settings' : jQuery('#jcform_settings').find('input[name="jcf_read_settings"]:checked').val()
		};

		jcf_ajax(data, 'json', null, function(response){
			if( response.msg ){
				if( confirm(response.msg) ){
					jQuery('#jcform_settings').find('input[name="jcf_keep_settings"]').removeAttr('disabled');
				}
				else{
					jQuery('#jcform_settings').find('input[name="jcf_read_settings"]#'+jcf_read_settings_active).attr({'checked':'checked'});
					jQuery('#jcform_settings').find('input[name="jcf_keep_settings"]').attr({'disabled':'disabled'});
				}
			}
			else if( response.file){
				jQuery('#jcform_settings').find('input[name="jcf_keep_settings"]').removeAttr('disabled');
			}
			else{
				jQuery('#jcform_settings').find('input[name="jcf_keep_settings"]').attr({'disabled':'disabled'});
			}
		});
	});

	jQuery('#jcform_settings input[name="jcf_multisite_setting"]').change(function(){
		if( jQuery( this ).val() == 'network' ){
			jQuery('input[type="radio"]#jcf_read_file_global, label[for="jcf_read_file_global"]').show();
		}
		else{
			jQuery('input[type="radio"]#jcf_read_file_global, label[for="jcf_read_file_global"]').hide();
			if(jcf_read_settings_active == 'jcf_read_file_global'){
				jQuery('#jcform_settings').find('input[name="jcf_read_settings"]#jcf_read_file').attr({'checked':'checked'});
			}else{
				jQuery('#jcform_settings').find('input[name="jcf_read_settings"]#'+jcf_read_settings_active).attr({'checked':'checked'});
			}
		}
	});
}

/*
 * Position for edit form
 */
function initEditFormPosition(){
	var scrolling = jQuery(document).scrollTop();
	var edit_form = jQuery('#jcf_ajax_container');
	var wrap_position = jQuery('.wrap').offset().left;
	var wrap_width = jQuery('.wrap').css('width').replace('px', '') * 1;
	var left_bar_width = jQuery('#jcf_fieldsets').css('width').replace('px', '') * 1;
	var pos_left = wrap_position + left_bar_width;
	var edit_form_width = wrap_width / 100 * 30 + 'px';
	if(scrolling >= 250){
		edit_form.css({'position':'fixed', 'top':'40px', 'width': edit_form_width, 'left' : pos_left + 'px'});
	}
	else{
		edit_form.css({'position':'relative', 'top':'', 'width':'30%', 'left' : ''});
	}
}