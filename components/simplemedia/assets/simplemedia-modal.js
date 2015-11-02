/*global window,jQuery,wp */
var JcfMediaModal = function (options) {
  'use strict';
  this.settings = {
    calling_selector: false,
    cb: function (attachment) {}
  };
  var that = this,
  frame = wp.media.frames.file_frame;

  this.attachEvents = function attachEvents() {
    jQuery(this.settings.calling_selector).on('click', this.openFrame);
  };

  this.openFrame = function openFrame(e) {
    e.preventDefault();

    // Create the media frame.
    frame = wp.media.frames.file_frame = wp.media({
      title: jQuery(this).data('uploader_title'),
      button: {
        text: jQuery(this).data('uploader_button_text')
      },
      library : {
        type : jQuery(this).data('media_type')
      }
    });
		
    // Set filterable state to uploaded to get select to show (setting this
    // when creating the frame doesn't work)
    frame.on('toolbar:create:select', function(){
      frame.state().set('filterable', 'uploaded');
    });

    // When an image is selected, run the callback.
    frame.on('select', function () {
      // We set multiple to false so only get one image from the uploader
      var attachment = frame.state().get('selection').first().toJSON();
      that.settings.cb(attachment);
    });

    frame.on('open activate', function() {
      // Get the link/button/etc that called us
      var $caller = jQuery(that.settings.calling_selector);

      // Select the thumbnail if we have one
      if ($caller.data('selected_id')) {
        var Attachment = wp.media.model.Attachment;
        var selection = frame.state().get('selection');
        selection.add(Attachment.get(jQuery('#'+$caller.data('selected_id')).val()));
      }
    });
        
    frame.open();
  };

  this.init = function init() {
    this.settings = jQuery.extend(this.settings, options);
    this.attachEvents();
  };
  this.init();

  return this;
};


window.JcfSimpleMedia = {
    
    selectMedia: function(attachment, id, type){
				
		var field = jQuery('#' + id);
		var row = field.parents('div.jcf-simple-row:first');

		if( type == 'image') {
			field.parent().parent().find('div.jcf-simple-image img').attr('src', attachment.url);
			field.parent().parent().find('div.jcf-simple-image a').attr('href', attachment.url);
			var html = '<a target="_blank" href="'+attachment.url+'">'+attachment.filename+'</a>';
		} else {
			var html = '<a target="_blank" href="'+attachment.url+'">'+attachment.filename+'</a>';
		}
		// set hidden value
		field.val( attachment.id );

		// update info and thumb
		row.find('p:first').html( html ).removeClass('jcf-hide').show();
		row.find('a.jcf_simple_delete').removeClass('jcf-hide').show();
    }
}

jQuery(document).ready(function(){
	var node = jQuery( '#post-body');
	node.find('div.jcf-simple-row a.jcf_simple_delete').live( 'click', function(e) {
		var value_id = jQuery(this).data('field_id');
		var row = jQuery(this).parents('div.jcf-simple-row');
		jQuery('#'+value_id).prop('disabled', true);
		row.find('.jcf_simple_delete').hide();
		row.find('#simplemedia-'+value_id).hide();
		row.find('div.jcf-simple-container').css({'opacity': 0.3});
		row.find('div.jcf-delete-layer').show();
		return false;
	});

	node.find('div.jcf-simple-row a.jcf_simple_cancel').live( 'click' ,function() {
		var value_id = jQuery(this).data('field_id');
		var row = jQuery(this).parents('div.jcf-simple-row');
		jQuery('#'+value_id).prop('disabled', false);
		row.find('div.jcf-simple-container').css({'opacity': 1});
		row.find('.jcf_simple_delete').show();
		row.find('#simplemedia-'+value_id).show();
		row.find('div.jcf-delete-layer').hide();
		return false;
	});	
})