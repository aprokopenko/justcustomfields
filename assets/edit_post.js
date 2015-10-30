jQuery(document).ready(function(){
	jcf_init_shortcodes_popup();
});

/*
 *	Get shortcodes
 */
function jcf_init_shortcodes_popup(){
	// open shortcodes popup
	jQuery('.jcf-get-shortcode').click(function(){
		var btn = jQuery(this);
		
		var fieldbox = btn.parents('div.jcf_edit_field');
		var field_label = fieldbox.find('label:first').text();
		
		var postbox = btn.parents('div.postbox');
		var popup = postbox.find('div.jcf_shortcodes_tooltip');
		
		var field_id = btn.attr('rel');
		
		var shortcode = '[jcf-value field="' + field_id + '"]';
		popup.find('input.jcf-shortcode-value').val(shortcode);
		popup.find('input.jcf-template-value').val('<?php do_shortcode(\'' + shortcode + '\'); ?>');
		
		popup.find('h3.header span.field-name').text(field_label);
		// hide all other popups;
		jQuery('div.jcf_shortcodes_tooltip').hide();
		jQuery('span.jcf_copied_to_clip').remove();
		// finally show popup
		popup
			.css({'top': btn.position().top + 'px'})
			.show();
	});

	// hide tooltip on click in some other location outside tooltip
	jQuery(document).click(function(e){
		if(jQuery(e.target).parents().filter('div.jcf_shortcodes_tooltip').length < 1 && jQuery(e.target).parents().filter('div.jcf-get-shortcode').length < 1){
			jQuery('div.jcf_shortcodes_tooltip').hide();
		}
	});

	// init tooltip close btn
	jQuery('a.jcf_shortcodes_tooltip-close').click(function(){
		jQuery(this).parent().parent().parent().hide();
		return false;
	});

	jQuery('div.jcf_shortcodes_tooltip a.copy-to-clipboard').bind('click', function(){
		var input = jQuery(this).parent().find('input');
		jcf_copy_to_clipboard(input);
		return false;
	});
}

/*
 *	Copy to clipboard function
 */
function jcf_copy_to_clipboard(element) {
	jQuery(element).select();
	jQuery('span.jcf_copied_to_clip').remove();
	jQuery(element).parent().append('<span class="jcf_copied_to_clip dashicons dashicons-yes wp-ui-text-highlight"></span>');
	document.execCommand("copy");
}