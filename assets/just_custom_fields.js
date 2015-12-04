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
	
	// choose base for visibility rule
	jQuery('#rule-based-on').live('change', function() {
		var data = {
			'rule': jQuery(this).val(),
			'action': 'jcf_get_rule_options',
		};
		
		var loader = jQuery(this).find('img.ajax-feedback');
		
		jcf_ajax(data, 'html', loader, function(response){
			jQuery('.rules-options').html(response);
		});
	});
	
	// choose taxonomy terms for visibility rule
	jQuery('.taxonomy-options #rule-taxonomy').live('change', function() {
		var data = {
			'taxonomy': jQuery(this).val(),
			'action': 'jcf_get_taxonomy_terms',
		};
		
		var loader = jQuery(this).find('img.ajax-feedback');
		
		jcf_ajax(data, 'html', loader, function(response){
			jQuery('.taxonomy-terms-options').html(response);
			var input = jQuery('#new-term');
			jcf_attach_autocomplete_event( input );
		});
	});
	
	//parse rule block for saving
	jQuery('.save_rule_btn, .update_rule_btn').live('click', function() {
		var f_id = jQuery(this).parents('form').find('input[name=fieldset_id]').val();
		var rule_id = jQuery(this).data('rule_id');

		var data = { 
			'action': 'jcf_save_visibility_rules',
			'fieldset_id': f_id,
			'rule_id': rule_id,
			'visibility_rules': {}
		};

		jQuery(this).parent('fieldset').find('input,select').each(function(i, input){
			if(jQuery(input).attr('type') == 'radio'){
				if(jQuery(input).is(':checked')){
					data.visibility_rules[ jQuery(input).attr('name') ] = jQuery(input).val();
				}
			}
			else if(jQuery(input).attr('type') == 'checkbox'){
				if(typeof data.visibility_rules[ jQuery(input).attr('name') ] === 'undefined'){
					data.visibility_rules[ jQuery(input).attr('name') ] = new Array();
				}
				if(jQuery(input).is(':checked')){
					data.visibility_rules[ jQuery(input).attr('name') ].push(jQuery(this).val());
				}
			}
			else{
				if(jQuery(input).attr('type') != 'button'){
					data.visibility_rules[ jQuery(input).attr('name') ] = jQuery(input).val();
				}
			}
		});
		var loader = jQuery(this).find('img.ajax-feedback');

		jcf_ajax(data, 'html', loader, function(response){
			jQuery('div.rules').remove();
			jQuery('div#visibility').append(response);
			jQuery('fieldset#fieldset_visibility_rules').remove();
		});
	});
	
    // add form for new visibility rule
	jQuery('.add_rule_btn').live('click', function() {
		var loader = jQuery(this).find('img.ajax-feedback');
		var data = {
			'action': 'jcf_add_visibility_rules_form',
			'add_rule': true
		}
		jcf_ajax(data, 'html', loader, function(response){
			jQuery('div#visibility').append(response);
			jQuery('.add_rule_btn').hide();
		});
	});
	
    // delete visibility rule 
	jQuery('a.remove-rule').live('click', function(){
		var rule_id = jQuery(this).data('rule_id');
		var loader = jQuery(this).find('img.ajax-feedback');
		var f_id = jQuery(this).parents('form').find('input[name=fieldset_id]').val();
		var data = {
			'action': 'jcf_delete_visibility_rule',
			'rule_id': rule_id,
			'fieldset_id' : f_id
		}
		jcf_ajax(data, 'html', loader, function(response){
			jQuery('div.rules').remove();
			jQuery('div#visibility').append(response);
			jQuery('fieldset#fieldset_visibility_rules').remove();
		});
	});
	
    // edit visibility rule 
	jQuery('a.edit-rule').live('click', function(){
		var rule_id = jQuery(this).data('rule_id');
		var loader = jQuery(this).find('img.ajax-feedback');
		var f_id = jQuery(this).parents('form').find('input[name=fieldset_id]').val();
		var data = {
			'action': 'jcf_add_visibility_rules_form',
			'rule_id': rule_id,
			'fieldset_id' : f_id,
			'edit_rule' : true
		}
		jcf_ajax(data, 'html', loader, function(response){
			jQuery('fieldset#fieldset_visibility_rules').remove();
			jQuery('div#visibility').append(response);
			jQuery('.add_rule_btn').hide();
		});
	});
	
    // show/hide visibility options for fieldset
	jQuery('a.visibility_toggle').live('click', function(){
		jQuery('#visibility').toggle();
		jQuery(this).find('span').toggleClass('dashicons-arrow-down-alt2');
		jQuery(this).find('span').toggleClass('dashicons-arrow-up-alt2');
	});
	
    // cancel form for add or edit visibility rule
	jQuery('.cancel_rule_btn').live('click', function(){
		jQuery(this).parents('fieldset#fieldset_visibility_rules').remove();
		jQuery('.add_rule_btn').show();
	});
    
	var input = jQuery('#new-term');
	jcf_attach_autocomplete_event( input );
    
    jQuery('#new-term').live('keyup', function(){
        var taxonomy = jQuery('.taxonomy-options #rule-taxonomy').val();
        var data = {
            action: 'jcf_visibility_autocomplete',
            taxonomy: taxonomy,
            term: jQuery(this).val()
        };
        var status = false;
        jQuery.post(ajaxurl, data, function(response){
            for(var key in response){ 
                if(response[key].label == data.term) {
                    status = true;
                    jQuery('#new-term').attr({'data-term_id': response[key].id, 'data-term_label': response[key].label});
                }
                break;
            }
        });
        if(!status){
            jQuery('#new-term').removeAttr('data-term_id data-term_label');
        }
    });
    
	jQuery('.termadd').live('click', function(){
		var term_id = jQuery('#new-term').attr('data-term_id');
		var term_label = jQuery('#new-term').attr('data-term_label');
        var wrapper_for_terms = jQuery('.taxonomy-terms-options ul.visibility-list-items');
        if( typeof term_id !== 'undefined' && typeof term_label !== 'undefined' ){
            jQuery('.taxonomy-terms-options p.visible-notice').remove();
            var label = '<label>' + term_label + '</label>';
            var chbox = '<input type="checkbox" checked="checked" name="rule_taxonomy_terms" value="' + term_id + '" />';
            if(wrapper_for_terms.length < 1){
                jQuery(this).parent().append('<ul class="visibility-list-items"></ul>');
            }
            jQuery('.taxonomy-terms-options ul.visibility-list-items').append('<li>' + chbox + label +'</li>');
        }
        else{
            jQuery('.taxonomy-terms-options').append('<p class="visible-notice">' + jcf_textdomain.no_term  + '</p>');
        }
		return false;
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
				html += '</td>';
				html += '<td>'+response.instance.slug+'</td>';
				html += '<td>'+response.id_base+'</td>';
				html += '<td>'+( (response.instance.enabled)? jcf_textdomain.yes : jcf_textdomain.no )+'</td>';
				if(response.collection_fields){
					html +='<tr class="collection_list" >';
					html += '<td colspan="2" data-collection_id="' + response.id + '"></td>';
					html += '<td colspan="3">'+response.collection_fields+'</td>';
					html += '</tr>';
				}
				fieldset.append(html);
				if(response.collection_fields){
						jQuery('tbody[id^=the-collection-list-collection-]').sortable({
							handle: 'span.drag-handle',
							opacity:0.7,
							placeholder: 'collection_sortable_placeholder',
							scroll: true,
							start: function (event, ui) { 
								ui.placeholder.html('<td colspan="4"><br>&nbsp;</td>');
							},
							stop: function(event, ui){ collectionFieldSortableStop(event, ui, this); }
						});
				}
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
				row.next('tr.collection_list:first').remove();
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
	jQuery('#jcf_fieldsets tbody:first').sortable({
		handle: 'span.drag-handle',
		opacity:0.7,
		placeholder: 'sortable_placeholder',
		scroll: true,
		start: function (event, ui) { 
			jQuery('.collection_list').hide();
			ui.placeholder.html('<td colspan="4"><br>&nbsp;</td>');
		},
		stop: function(event, ui){
			// ui.item - item in the list
			var order = '';
			var fieldset = jQuery(ui.item).parent();
			var f_id = fieldset.attr('id').replace('the-list-', '');
			fieldset.find('tr.field_row').each(function(i, tr){
				if(jQuery(tr).attr('id')) order += jQuery(tr).attr('id').replace('field_row_', '') + ',';
			});
			setCollectionFieldsToPosition(fieldset)
			jQuery('.collection_list').show();
			
			var data = {
				'action': 'jcf_fields_order',
				'fieldset_id': f_id,
				'fields_order': order
			};
			//pa(data);
			jcf_ajax(data, 'json');
		}
	});
	
	function setCollectionFieldsToPosition( fieldset ){
		fieldset.find('tr.collection_list').each(function(i, tr){
			var collection_id = jQuery(tr).find('td:first').data('collection_id');
			jQuery('tr.'+collection_id).after('<tr class="collection_list">'+jQuery(tr).html()+'</tr>');
			tr.remove();
		});
	}
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

//add autocomplete
function jcf_attach_autocomplete_event( input ){
	var taxonomy = jQuery('.taxonomy-options #rule-taxonomy').val();
	var data = {
		action: 'jcf_visibility_autocomplete',
		taxonomy: taxonomy
	};
	input.autocomplete({
		minLength: 2,
		source: function( request, response ) {
				data.term = request.term;
				jQuery.post(ajaxurl, data, response);
			},
		select: function(event, ui){
			input.attr({'data-term_id': ui.item.id, 'data-term_label': ui.item.label});
		},
		search: function( event, ui ) {
				input.parent().find('span.loading').remove();
				input.parent().append('<span class="loading">loading...</span>');
			},
		open: function( event, ui ){
				input.parent().find('span.loading').remove();
			}
	});

}