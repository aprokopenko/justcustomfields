jQuery(document).ready(function () {
  if (jQuery('#jcf_fieldsets').size()) {
    initAddFieldsetBox();
    initFieldsetsEdit();
    initAjaxBoxClose();
    initFieldsetFields();
  }
  initImport();
  initExport();
  initImportExportCheckboxes();
  initSettings();
  initMobileCompatibility();
});

jQuery(document).scroll(function () {
  initEditFormPosition();
});

function initMobileCompatibility() {
  jQuery('.show_modal, .edit').on('click', function () {
    jQuery('body').addClass('jcf_show_modal');
  });
  jQuery(document).on('click', '#jcf_ajax_container .jcf_close, .field-control-close, .field-control-remove, .jcf-btn-save', function () {
    if ( jQuery('body').hasClass('jcf_show_modal') ) {
      jQuery('body').removeClass('jcf_show_modal');
    }
  });
}

/**
 *	init fieldset add box
 */
function initAddFieldsetBox() {
  // init ajax submit
  jQuery('#jcform_add_fieldset').submit(function( e ) {
    e.preventDefault();

    var title = jQuery('#jcf_fieldset_title').val();
    var data = {
      'action': 'jcf_add_fieldset',
      'title': title
    };

    // ajax call
    jcf_ajax(data, 'json', null, function( response ) {
      // all is ok: refresh page
      window.location.reload(true);
    });

    return false;
  });
}

/**
 *	init all fieldset edit operations (edit/save/delete)
 */
function initFieldsetsEdit() {

  // delete
  jQuery('#jcf_fieldsets a.jcf_fieldset_delete').click(function(e) {
    e.preventDefault();

    if ( confirm(jcf_textdomain.confirm_fieldset_delete) )
    {
      var f_id = jQuery(this).attr('rel');
      var data = {
        'action': 'jcf_delete_fieldset',
        'fieldset_id': f_id
      };

      jcf_ajax(data, 'json', null, function( response ) {
        jQuery('#jcf_fieldset_' + f_id).remove();
        // clean ajax container
        jcf_hide_ajax_container();
      });
    }
  })

  // change
  jQuery('#jcf_fieldsets a.jcf_fieldset_change').click(function(e) {
    e.preventDefault();

    var f_id = jQuery(this).attr('rel');
    var data = {
      'action': 'jcf_change_fieldset',
      'fieldset_id': f_id
    };

    jcf_ajax(data, 'html', null, function( response ) {
      jcf_show_ajax_container(response);
    });
  })

  // init delete button on change popup
  jQuery('#jcf_ajax_content .jcf_edit_fieldset a.field-control-remove').live('click', function() {
    var f_id = jQuery(this).parents('form:first').find('input[name=fieldset_id]').val();
    jQuery('#jcf_fieldset_' + f_id + ' a.jcf_fieldset_delete').click();
    return false;
  });

  // save on edit form
  jQuery('#jcform_edit_fieldset').live('submit', function( e ) {
    e.preventDefault();
    var f_id = jQuery(this).find('input[name=fieldset_id]').val();
    var data = {
      'action': 'jcf_update_fieldset',
      'fieldset_id': f_id,
      'title': jQuery('#jcf_edit_fieldset_title').val(),
      'position': jQuery('#jcf_edit_fieldset_position').val(),
      'priority': jQuery('#jcf_edit_fieldset_priority').val()
    };

    jcf_ajax(data, 'json', null, function( response ) {
      // update title
      jQuery('#jcf_fieldset_' + f_id + ' h3 strong').text(response.title);

      jcf_hide_ajax_container();
    });

    return false;
  });

  // init sortable
  jQuery('#jcf_fieldsets').sortable({
    handle: 'h3.header span.drag-handle',
    opacity: 0.7,
    stop: function( event, ui ) {
      var order = '';
      jQuery(this).find('.jcf_inner_box').each(function( i, tr ) {
        order += jQuery(this).attr('id').replace('jcf_fieldset_', '') + ',';
      });

      var data = {
        'action': 'jcf_order_fieldsets',
        'fieldsets_order': order
      };
      jcf_ajax(data, 'json');
    }
  });

  // choose base for visibility rule (page template/taxonomy)
  jQuery('#rule-based-on').live('change', function() {
    var data = {
      'based_on': jQuery(this).val(),
      'action': 'jcf_get_rule_options',
    };

    jcf_ajax(data, 'html', null, function( response ) {
      jQuery('.rules-options').html(response);
    });
  });

  // choose taxonomy terms for visibility rule
  jQuery('#rule-taxonomy').live('change', function() {
    var data = {
      'taxonomy': jQuery(this).val(),
      'action': 'jcf_get_taxonomy_terms',
    };

    jcf_ajax(data, 'html', null, function( response ) {
      jQuery('.taxonomy-terms-options').html(response);
      var input = jQuery('#new-term');
      jcf_attach_autocomplete_event(input);
    });
  });

  //parse rule block for saving
  jQuery('.save_rule_btn, .update_rule_btn').live('click', function(e) {
    e.preventDefault();

    var f_id = jQuery(this).parents('form').find('input[name=fieldset_id]').val();
    var rule_id = jQuery(this).data('rule_id');

    var data = {
      'action': 'jcf_save_visibility_rules',
      'fieldset_id': f_id,
      'rule_id': rule_id,
      'rule_data': {}
    };

    data.rule_data = jcf_form_serialize_object(jQuery(this).parent('fieldset'));

    // check the data is valid
    if ( (data.rule_data.rule_taxonomy_terms && data.rule_data.rule_taxonomy_terms.length)
            || (data.rule_data.rule_templates && data.rule_data.rule_templates.length)
    ) {
      jcf_ajax(data, 'html', null, function( response ) {
        jQuery('div.rules').remove();
        jQuery('div#visibility').append(response);
        jQuery('fieldset#fieldset_visibility_rules').remove();
      });
      return;
    }

    // data is not valid - show error
    var msg_invalid_input = jcf_textdomain.err_fieldset_visibility_invalid;
    if ( jQuery('.jcf_edit_fieldset select[name=based_on]').size() ) {
      msg_invalid_input = jcf_textdomain.err_fieldset_visibility_invalid_page;
    }
    alert( msg_invalid_input );
    return;
  });

  // add form for new visibility rule
  jQuery('.add_rule_btn').live('click', function(e) {
    e.preventDefault();

    var data = {
      'action': 'jcf_add_visibility_rules_form',
      'scenario': 'create'
    }
    jcf_ajax(data, 'html', null, function( response ) {
      jQuery('div#visibility').append(response);
      jQuery('.add_rule_btn').hide();
    });
  });

  // delete visibility rule
  jQuery('a.remove-rule').live('click', function(e) {
    e.preventDefault();
    var rule_id = jQuery(this).data('rule_id');
    var f_id = jQuery(this).parents('form').find('input[name=fieldset_id]').val();
    var data = {
      'action': 'jcf_delete_visibility_rule',
      'rule_id': rule_id,
      'fieldset_id': f_id
    }
    jcf_ajax(data, 'html', null, function( response ) {
      jQuery('div.rules').remove();
      jQuery('div#visibility').append(response);
      jQuery('fieldset#fieldset_visibility_rules').remove();
    });
  });

  // edit visibility rule
  jQuery('a.edit-rule').live('click', function(e) {
    e.preventDefault();
    var rule_id = jQuery(this).data('rule_id');
    var f_id = jQuery(this).parents('form').find('input[name=fieldset_id]').val();
    var data = {
      'action': 'jcf_add_visibility_rules_form',
      'rule_id': rule_id,
      'fieldset_id': f_id,
      'scenario': 'update'
    }
    jcf_ajax(data, 'html', null, function( response ) {
      jQuery('fieldset#fieldset_visibility_rules').remove();
      jQuery('div#visibility').append(response);
      jQuery('.add_rule_btn').hide();
      var input = jQuery('#new-term');
      jcf_attach_autocomplete_event(input)
    });
  });

  // show/hide visibility options for fieldset
  jQuery('a.visibility_toggle').live('click', function(e) {
    e.preventDefault();
    jQuery('#visibility').toggle();
    jQuery(this).find('span').toggleClass('dashicons-arrow-down-alt2');
    jQuery(this).find('span').toggleClass('dashicons-arrow-up-alt2');
  });

  // cancel form for add or edit visibility rule
  jQuery('.cancel_rule_btn').live('click', function(e) {
    e.preventDefault();
    jQuery(this).parents('fieldset#fieldset_visibility_rules').remove();
    jQuery('.add_rule_btn').show();
  });

  // adding new term for visility
  jQuery('.termadd').live('click', function(e) {
    e.preventDefault();
    if ( !jQuery('#new-term').attr('data-term_id') && !jQuery('#new-term').attr('data-term_label') ) {
      var taxonomy = jQuery('.taxonomy-options #rule-taxonomy').val();
      var data = {
        action: 'jcf_visibility_autocomplete',
        taxonomy: taxonomy,
        term: jQuery('#new-term').val()
      };
      var status = false;
      jQuery.post(ajaxurl, data, function( response ) {
        for ( var key in response ) {
          if ( response[key].label == data.term ) {
            status = true;
            jQuery('#new-term').attr({'data-term_id': response[key].id, 'data-term_label': response[key].label});
            var term_id = response[key].id;
            var term_label = response[key].label;
            jcf_add_terms_to_list(term_id, term_label);
          }
          break;
        }
        if ( !status ) {
          jQuery('#new-term').removeAttr('data-term_id data-term_label');
        }
      });
    }
    else {
      var term_id = jQuery('#new-term').attr('data-term_id');
      var term_label = jQuery('#new-term').attr('data-term_label');
      jcf_add_terms_to_list(term_id, term_label);
    }
    jQuery('#new-term').val('').focus();
    return false;
  });

  /*
   * Add terms to list when add rules
   */
  function jcf_add_terms_to_list( term_id, term_label ) {
    var wrapper_for_terms = jQuery('.taxonomy-terms-options ul.visibility-list-items');
    if ( typeof term_id !== 'undefined' && typeof term_label !== 'undefined' ) {
      jQuery('.taxonomy-terms-options p.visible-notice').remove();
      var label = '<label>' + term_label + '</label>';
      var chbox = '<input type="checkbox" checked="checked" name="rule_taxonomy_terms" value="' + term_id + '" />';
      if ( wrapper_for_terms.length < 1 ) {
        jQuery(this).parent().append('<ul class="visibility-list-items"></ul>');
      }
      jQuery('.taxonomy-terms-options ul.visibility-list-items').append('<li>' + chbox + label + '</li>');
    }
    else {
      jQuery('.taxonomy-terms-options').append('<p class="visible-notice">' + jcf_textdomain.no_term + '</p>');
    }
  }

}

/**
 *	init fieldset fields grid and add form
 */
function initFieldsetFields() {

  // init add form
  jQuery('#jcf_fieldsets form.jcform_add_field').submit(function( e ) {
    e.preventDefault();

    var data = {action: 'jcf_add_field'};

    jQuery(this).find('input,select').each(function( i, input ) {
      data[ jQuery(input).attr('name') ] = jQuery(input).val();
    })

    var loader = jQuery(this).find('img.ajax-feedback');

    jcf_ajax(data, 'html', loader, function( response ) {
      jcf_show_ajax_container(response);
    })

    return false;
  });

  // init save button on edit form
  jQuery('#jcform_edit_field').live('submit', function( e ) {
    e.preventDefault();

    // get query string from the form
    var query = jQuery('#jcform_edit_field').formSerialize();
    var data = 'action=jcf_save_field' + '&' + query;

    var loader = jQuery(this).find('img.ajax-feedback');

    // send request
    jcf_ajax(data, 'json', loader, function( response ) {

      var fieldset = jQuery('#the-list-' + response.fieldset_id);

      if ( response.is_new ) {
        // check if fieldset is empty
        if ( fieldset.find('td').size() == 1 ) {
          // remove empty row container
          fieldset.find('tr').remove();
        }
        // add new row
        var html;
        html = '<tr id="field_row_' + response.id + '" class="field_row ' + response.id + '">';
        html += '	<td class="jcf-check-column"><span class="dashicons dashicons-menu drag-handle"></span></td>';
        html += '	<td><strong><a href="#" rel="' + response.id + '">' + response.instance.title + '</a></strong>';
        html += '	<div class="row-actions">';
        html += '		<span class="edit"><a href="#" rel="' + response.id + '">' + jcf_textdomain.edit + '</a></span> |';
        html += '		<span class="delete"><a href="#" rel="' + response.id + '">' + jcf_textdomain.delete + '</a></span>';
        html += '	</div>';
        if ( response.collection_fields ) {
          html += '   <ul>';
          html += '       <li><strong>' + jcf_textdomain.type + '</strong>: ' + response.id_base + '</li>';
          html += '       <li><strong>' + jcf_textdomain.slug + '</strong>: ' + response.instance.slug + '</li>';
          html += '       <li><strong>' + jcf_textdomain.enabled + '</strong>: ' + ((response.instance.enabled) ? jcf_textdomain.yes : jcf_textdomain.no) + '</li>';
          html += '   </ul>';
        }
        html += '</td>';
        if ( response.collection_fields ) {
          html += '<td colspan="3" class="collection_list" data-collection_id="' + response.id + '">' + response.collection_fields + '</td>';
        }
        else {
          html += '<td>' + response.instance.slug + '</td>';
          html += '<td>' + response.id_base + '</td>';
          html += '<td>' + ((response.instance.enabled) ? jcf_textdomain.yes : jcf_textdomain.no) + '</td>';
        }
        fieldset.append(html);
        if ( response.collection_fields ) {
          jQuery('#jcf_fieldsets table.collection-fields-table > tbody').sortable({
            handle: 'span.drag-handle',
            opacity: 0.7,
            placeholder: 'collection_sortable_placeholder',
            scroll: true,
            start: function( event, ui ) {
              ui.placeholder.html('<td colspan="5"><br>&nbsp;</td>');
            },
            stop: function( event, ui ) {
              collectionFieldSortableStop(event, ui, this);
            }
          });
        }
      }
      var row = jQuery('#field_row_' + response.id);
      row.find('td:eq(1) strong a').text(response.instance.title);
      if ( !response.collection_fields ) {
        // update fieldset row
        row.find('td:eq(2)').text(response.instance.slug);
        row.find('td:eq(4)').text((response.instance.enabled) ? jcf_textdomain.yes : jcf_textdomain.no);
      }
      else {
        html = '       <li><strong>' + jcf_textdomain.type + '</strong>: ' + response.id_base + '</li>';
        html += '       <li><strong>' + jcf_textdomain.slug + '</strong>: ' + response.instance.slug + '</li>';
        html += '       <li><strong>' + jcf_textdomain.enabled + '</strong>: ' + ((response.instance.enabled) ? jcf_textdomain.yes : jcf_textdomain.no) + '</li>';
        row.find('td:eq(1) ul').html(html);
      }
      // close add box at the end
      jcf_hide_ajax_container();
    })

    return false;
  });

  // delete button
  jQuery('#jcf_fieldsets tbody span.delete a').live('click', function() {
    if ( confirm(jcf_textdomain.confirm_field_delete) ) {
      var row = jQuery(this).parents('tr:first');
      var f_id = jQuery(this).parents('tbody:first').attr('id').replace('the-list-', '');
      var data = {
        action: 'jcf_delete_field',
        fieldset_id: f_id,
        field_id: jQuery(this).attr('rel')
      };

      jcf_ajax(data, 'json', null, function( response ) {
        row.next('td.collection_list:first').remove();
        row.remove();
        // close edit box if exists
        jcf_hide_ajax_container();
      });
    }
    return false;
  })

  // edit button
  jQuery('#jcf_fieldsets tbody span.edit a, #jcf_fieldsets tbody strong > a').live('click', function() {
    var f_id = jQuery(this).parents('tbody:first').attr('id').replace('the-list-', '');
    var data = {
      action: 'jcf_edit_field',
      fieldset_id: f_id,
      field_id: jQuery(this).attr('rel')
    };

    jcf_ajax(data, 'html', null, function( response ) {

      jcf_show_ajax_container(response);

    });

    return false;
  })

  // delete button in edit form
  jQuery('#jcform_edit_field a.field-control-remove').live('click', function( e ) {
    var field_id = jQuery(this).parents('form:first').find('input[name=field_id]').val();
    var row = jQuery('#field_row_' + field_id);
    row.find('span.delete a').click();
    return false;
  });

  // init sortable
  jQuery('#jcf_fieldsets table.fieldset-fields-table > tbody').sortable({
    handle: 'span.drag-handle',
    opacity: 0.7,
    placeholder: 'sortable_placeholder',
    scroll: true,
    start: function( event, ui ) {
      ui.placeholder.html('<td colspan="5"><br>&nbsp;</td>');
    },
    stop: function( event, ui ) {
      // ui.item - item in the list
      var order = '';
      var fieldset = jQuery(ui.item).parent();
      var f_id = fieldset.attr('id').replace('the-list-', '');
      fieldset.find('tr.field_row').each(function( i, tr ) {
        if ( jQuery(tr).attr('id') )
          order += jQuery(tr).attr('id').replace('field_row_', '') + ',';
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
function initImport() {
  jQuery('#jcf_import_fields').submit(function( e ) {
    e.preventDefault();

    var query = new FormData(jQuery(this).get(0));
    jQuery.ajax({
      url: 'admin-ajax.php',
      type: 'post',
      contentType: false,
      processData: false,
      data: query,
      success: function( responce ) {
        if ( 'object' == typeof(responce) && responce.error ) {
          alert(responce.error[0]);
        } else {
          modalWindow(responce);
        }
      }
    });
  });
}

/**
 *	init export
 */
function initExport() {
  jQuery('a#export-button').click(function(e) {
    e.preventDefault();

    var data = {
      'action': 'jcf_export_fields_form'
    }
    jcf_ajax(data, 'html', null, function( response ) {
      modalWindow(response);
    });

    return false;
  });
}

/**
 *	init Import/Export checkboxes changing
 */
function initImportExportCheckboxes() {
  // checked fields
  jQuery('#jcf_save_import_fields input[type="checkbox"], #jcf_export_fields input[type="checkbox"]').live('change', function() {
    var $this = jQuery(this);
    var data_checked = $this.is(':checked');

    var descendants_container = null;
    if ( $this.hasClass('jcf_field_select') ) {
      if ( $this.data('is_collection') == 1 ) {
        descendants_container = $this.data('collection_container');
      }
    }
    else if ( $this.hasClass('jcf_fieldset_select_all') ) {
      descendants_container = $this.data('fields_container');
    }
    else if ( $this.hasClass('jcf_content_type_select_all') ) {
      descendants_container = $this.data('cpt_container');
    }

    // mark all descendant checkboxes with the same status
    if ( descendants_container ) {
      jQuery(descendants_container).find('input[type="checkbox"]').attr({'checked': data_checked});
    }
  });
}

/**
 *	ajax functions below
 */
function initAjaxBoxClose() {
  jQuery('#jcf_ajax_content a.field-control-close').live('click', function() {
    jcf_hide_ajax_container();
  });
}

function jcf_hide_ajax_container() {
  jQuery('#jcf_ajax_content').html('');
  jQuery('#jcf_ajax_container').hide();
}

function jcf_show_ajax_container( response ) {
  jQuery('#jcf_ajax_container').show();
  jQuery('#jcf_ajax_content').html(response);
}

function jcf_ajax( data, respType, loader, callback ) {
  // save to local variables to have ability to call them inside ajax
  var _callback = callback;
  var _respType = respType;

  //pa('wp-ajax call: ' + data.action);

  // add post_type to data
  var post_type = jQuery('#jcf_post_type_hidden').val();
  if ( typeof (data) == 'object' ) {
    data.post_type = post_type;
  }
  else if ( typeof (data) == 'string' ) {
    data += '&post_type=' + post_type;
  }

  // if we have loader - show loader
  jQuery('body').addClass('jcf-loading');

  // send ajax
  jQuery.post(ajaxurl, data, function( response ) {
    //pa(response);

    // if we have loader - hide loader
    jQuery('body').removeClass('jcf-loading');

    // if json - check for errors
    if ( _respType == 'json' && (response.status != '1' || response.status != true) ) {
      if ( jQuery.isArray(response.error) ) {
        alert(response.error[0]);
      }
      else {
        alert(response.error);
      }
      return;
    }

    // if no errors - call main callback
    if ( _callback )
      _callback(response);
  })
}

function pa( mixed ) {
  if ( window.console ) {
    window.console.info(mixed);
  }
}

function modalWindow( content ) {
  jQuery('body').append(
      '<div class="media-modal wp-core-ui jcf_modalWindow">' +
        '<button class="button-link media-modal-close" type="button"><span class="media-modal-icon"></span></button>' +
        '<div class="media-modal-content">' +
            content +
        '</div>' +
      '</div>' +
      '<div class="media-modal-backdrop"></div>'
  );

  jQuery('.media-modal-close').click(function() {
    jQuery('.jcf_modalWindow').remove();
    jQuery('.media-modal-backdrop').remove();
  });
}

/**
 *	init settings
 */
function initSettings() {
  var jcf_read_settings_active = jQuery('#jcform_settings').find('input[name="jcf_read_settings"]:checked').attr('id');

  jQuery('#jcform_settings input[name="jcf_multisite_setting"]').change(function() {
    if ( jQuery(this).val() == 'network' ) {
      jQuery('input[type="radio"]#jcf_read_file_global, label[for="jcf_read_file_global"]').show();
    }
    else {
      jQuery('input[type="radio"]#jcf_read_file_global, label[for="jcf_read_file_global"]').hide();
      if ( jcf_read_settings_active == 'jcf_read_file_global' ) {
        jQuery('#jcform_settings').find('input[name="jcf_read_settings"]#jcf_read_file').attr({'checked': 'checked'});
      } else {
        jQuery('#jcform_settings').find('input[name="jcf_read_settings"]#' + jcf_read_settings_active).attr({'checked': 'checked'});
      }
    }
  });
}

/*
 * Position for edit form
 */
function initEditFormPosition() {
  if ( !jQuery('#jcf_fieldsets').size() ) return;

  var scrolling = jQuery(document).scrollTop();
  var edit_form = jQuery('#jcf_ajax_container');
  var wrap_position = jQuery('.wrap').offset().left;
  var wrap_width = jQuery('.wrap').css('width').replace('px', '') * 1;
  var left_bar_width = jQuery('#jcf_fieldsets').css('width').replace('px', '') * 1;
  var pos_left = wrap_position + left_bar_width;
  var edit_form_width = edit_form.width();
  if ( scrolling >= 140 ) {
    edit_form.css({'position': 'fixed', 'top': '40px', 'width': edit_form_width, 'left': pos_left + 'px'});
    setScrollOnEditForm();
  }else {
    edit_form.css({'position': 'relative', 'top': '', 'width': '36%', 'left': ''});
    removeScrollOnEditForm();
  }
}


function setScrollOnEditForm (){
  var $editForm = jQuery('#jcf_ajax_container');
    $editFormContent = $editForm.find('fieldset'),
    editFormHeight = $editForm.height(),
    wpAdminBarHeight = jQuery('#wpadminbar').height(),
    contentWrapHeight = 92,
    editFormBottomMargin = 20,
    windowHeight = jQuery(window).height() - wpAdminBarHeight - 50;

  if (editFormHeight >=  windowHeight ) {
    $editFormContent.css({});
    $editForm.css({'height': windowHeight  });
    $editFormContent.css({'height': windowHeight - contentWrapHeight, 'overflow-y': 'auto', 'margin-bottom': editFormBottomMargin});
  }
}


function removeScrollOnEditForm(){
  var $editForm = jQuery('#jcf_ajax_container'),
    $editFormContent = $editForm.find('fieldset');
  $editForm.css({'height': 'auto'});
  $editFormContent.css({'height': 'auto', 'overflow-y': 'auto', 'margin-bottom': 0});
}

/**
 * Add autocomplete
 */
function jcf_attach_autocomplete_event( input ) {
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
    select: function( event, ui ) {
      input.attr({'data-term_id': ui.item.id, 'data-term_label': ui.item.label});
    },
    search: function( event, ui ) {
      input.parent().find('span.loading').remove();
      input.parent().append('<span class="loading">loading...</span>');
      input.attr({'data-term_id': '', 'data-term_label': ''});
    },
    open: function( event, ui ) {
      input.parent().find('span.loading').remove();
    }
  });

}

/**
 * Serialize object
 * @param {type} obj
 * @returns {Array|jQuery}
 */
function jcf_form_serialize_object( obj ) {
  var data = {};
  obj.find('input, select').each(function( i, input ) {
    if ( jQuery(input).attr('type') == 'radio' ) {
      if ( jQuery(input).is(':checked') ) {
        data[ jQuery(input).attr('name') ] = jQuery(input).val();
      }
    }
    else if ( jQuery(input).attr('type') == 'checkbox' ) {
      if ( typeof data[ jQuery(input).attr('name') ] === 'undefined' ) {
        data[ jQuery(input).attr('name') ] = new Array();
      }
      if ( jQuery(input).is(':checked') ) {
        data[ jQuery(input).attr('name') ].push(jQuery(this).val());
      }
    }
    else {
      if ( jQuery(input).attr('type') != 'button' ) {
        data[ jQuery(input).attr('name') ] = jQuery(input).val();
      }
    }
  });
  return data;
}