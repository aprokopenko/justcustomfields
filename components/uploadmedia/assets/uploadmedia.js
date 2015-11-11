var jcf_upload_related_field = null;
var jcf_upload_max_index = 0;
var jcf_upload_type = null;
var jcf_post_body_content_container = '#post-body-content';
jQuery(document).ready(function() {
	// 2012-06-15: check WP version. 3.4+ has new class in body
	if( parseFloat(jcf_textdomain.wp_version) >= 3.4 ){
		jcf_post_body_content_container = '#post-body';
	}

	jcf_upload_max_index = jQuery( jcf_post_body_content_container ).find('a.jcf_upload').size();
	if( jcf_upload_max_index == 0 ) return;

	// remember default_send to editor function
	window.default_send_to_editor = window.send_to_editor; 

	// init controls
	jcf_init_image_upload_controls();
	
	// init sortable
	jQuery('.jcf-upload-field').sortable({
		handle: 'span.drag-handle',
		opacity:0.7,
		placeholder: 'sortable-placeholder',
		start: function (event, ui) { 
			ui.placeholder.html('<div class="sort-placheholder"></div>');
		},
	});
});
function jcf_uploadmedia_send_to_editor( html ){
	if( jcf_upload_related_field === null ){
		window.default_send_to_editor(html);
		return;
	}

	if( jcf_upload_type == 'image' ){
		var fileurl = jQuery('img', '<div>'+html+'</div>').attr('src');
	}
	else{
		var fileurl = jQuery('a', '<div>'+html+'</div>').attr('href');
	}
	
	var link_text = fileurl.split('/');
	link_text = link_text[ link_text.length-1 ];
	
	var field = jQuery('#' + jcf_upload_related_field);
	var row = field.parents('div.jcf-upload-row:first');
	
	// set hidden value
	field.val( fileurl );
	
	// update info and thumb
	row.find('p:first')
		.html( '<a href="' + fileurl + '" target="_blank">' + link_text + '</a>' )
		.removeClass('jcf-hide')
		.show();
	
	var update_text = ( jcf_upload_type == 'image' )? jcf_textdomain.update_image : jcf_textdomain.update_file;
	row.find('a.jcf_upload').text(update_text)
	row.find('a.jcf_delete').removeClass('jcf-hide').show();
	
	if( jcf_upload_type == 'image' ){
		var thumburl = userSettings.url.replace('\/cms\/', '\/') + 'wp-content/plugins/just-custom-fields/components/uploadmedia/thump.php?image=' + escape(fileurl) + '&size=100x77';
		row.find('div.jcf-upload-image img').attr( 'src', thumburl );
	}

	tb_remove();
	
	// clean globals
	jcf_upload_related_field = null;
	jcf_upload_type = null;
	// return default send to editor event
	window.send_to_editor = window.default_send_to_editor;
}

function jcf_init_image_upload_controls( node ){
	if( node == null ) node = jQuery( jcf_post_body_content_container );
	
	if( node.find('a.jcf_upload').size() == 0 ) return;
	
	node.find('a.jcf_upload').live( 'click', function() {
		var $this = jQuery(this);
		// assign gloval vars
		jcf_upload_related_field = $this.attr('rel');
		jcf_upload_type = ($this.attr('href').match(/\=image/))? 'image' : 'all';
		
		// replace standard send to editor func
		window.send_to_editor = jcf_uploadmedia_send_to_editor;
		// open popup
		var mediaURL = $this.attr('href');
		tb_show('', mediaURL);
		
		// add close event to clear vars
		jQuery('#TB_closeWindowButton').click(function(){
			// clean globals
			jcf_upload_related_field = null;
			jcf_upload_type = null;
			// return default send to editor event
			window.send_to_editor = window.default_send_to_editor;
		})
		
		return false;
	});
	
	node.find('div.jcf-upload-field a.jcf_delete').live( 'click', function() {
		var row = jQuery(this).parents('div.jcf-upload-row:first');
		row.find('div.jcf-upload-container').css({'opacity': 0.3});
		row.find('div.jcf-delete-layer')
			.show()
			.find('input:hidden').val('1');
		return false;
	});

	node.find('div.jcf-upload-field a.jcf_cancel').live( 'click' ,function() {
		var row = jQuery(this).parents('div.jcf-upload-row:first');
		row.find('div.jcf-upload-container').css({'opacity': 1});
		row.find('div.jcf-delete-layer')
			.hide()
			.find('input:hidden').val('0');
		return false;
	});
	
	// add more button
	node.find('div.jcf-upload-field a.jcf_add_more').click(function(){
		var container = jQuery(this).parent();
		
		jcf_upload_max_index++;
		var new_html = container.find('div.jcf-upload-row:first').html();
		new_html = new_html
			.replace(/\[00\]/g, '[' + jcf_upload_max_index + ']')
			.replace(/\-00\-/g, '-' + jcf_upload_max_index + '-');
		new_html = '<div class="jcf-upload-row">' + new_html + '</div>';
		
		// add new html row
		container.find('div.jcf-upload-row:last').after( new_html );
		
		return false;
	})
}