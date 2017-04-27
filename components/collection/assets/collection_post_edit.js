/* 
 * collection_post_edit
 */

jQuery(document).ready(function() {
  jcf_collection_fields_control();

  //accordion
  jQuery('.collection_fields').accordion({
    header: "h3",
    icons: false,
    heightStyle: "content",
    collapsible: true,
    active: false,
    beforeActivate: function( event, ui ) {
      if ( jQuery(ui.newHeader).hasClass('jcf_field_removed') ) {
        return false;
      }
    },
    activate: function (event, ui) {
      jcf_do_action('collection_row_activated', this, event, ui);
    }
  });

  // init sortable
  jQuery('.collection_fields').sortable({
    handle: 'span.dashicons-editor-justify',
    opacity: 0.7,
    placeholder: 'sortable-placeholder',
    scroll: true,
    start: function( event, ui ) {
      ui.placeholder.html('<div class="sortable-placeholder"></div>');
    },
    /*stop: function (event, ui){
     
     }*/
  });
});

function jcf_collection_fields_control() {

  // add more button
  jQuery('input.jcf_add_more_collection').click(function() {
    var container = jQuery(this).parent();

    var next_field_group_index = container.find('.collection_field_group').size();
    var data = {
      action: 'jcf_collection_add_new_field_group',
      fieldset_id: jQuery(this).data('fieldset_id'),
      collection_id: jQuery(this).data('collection_id'),
      group_id: next_field_group_index,
      post_type: jQuery(this).data('post_type')
    };

    jQuery.post(ajaxurl, data, function( response ) {
      container.find('div.collection_field_group:last').after(response);
      jQuery('.collection_fields').accordion('refresh');
      // open last section
      container.find('div.collection_field_group:last').find('h3').click();

      // run hook to be able to patch this with other components or plugins
      jcf_do_action('collection_row_added', this, container.find('div.collection_field_group:last'));
    });

    return false;
  })

  jQuery('div.collection_field_group h3 span.dashicons-trash').live('click', function( e ) {
    e.preventDefault();
    jQuery(this).parent().find('.collection_group_title').after('<span class="jcf_collection_removed">To be deleted</span>');
    jQuery(this).parent().addClass('jcf_field_removed');
    jQuery(this).parent().next('div').hide();
    jQuery(this).parent().find('.collection_undo_remove_group').show();
    jQuery(this).parent().next('div').find('input,select,textarea').attr('disabled', 'disabled');
    jQuery(this).hide();
    return false;
  });

  jQuery('div.collection_field_group a.collection_undo_remove_group').live('click', function( e ) {
    e.preventDefault();
    jQuery(this).parent().find('.jcf_collection_removed').remove();
    jQuery(this).parent().removeClass('jcf_field_removed');
    jQuery(this).parent().find('span.dashicons-trash').show();
    jQuery(this).parent().next('div').find('input,select,textarea').removeAttr('disabled');
    jQuery(this).hide();
    return false;
  });
}