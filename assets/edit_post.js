jQuery(document).ready(function(){
	jcf_get_shortcode();
});

/*
 *	Get shortcodes
 */
function jcf_get_shortcode(){

	jQuery('.jcf-get-shortcode').click(function(){
		var btn = jQuery(this);
		var parent_block = btn.parent().parent().parent();
		var field_label = btn.parent().find('label').text();
		var shortcode = parent_block.find('.jcf_shortcodes_tooltip');
		var btn_position = btn.position();
		var slug = jQuery(this).attr('rel');
		var shortcode_value = '[jcf-value slug="' + slug + '"]';
		var template_value = '<?php do_shortcode(\'' + shortcode_value + '\'); ?>';
		var shortcode_label = '[jcf-label slug="' + slug + '"]';
		var template_label = '<?php do_shortcode(\'' + shortcode_label + '\'); ?>';
		var copy_to_clipboard_value = shortcode.find('a.copy-to-clipboard.copy-value');
		var copy_to_clipboard_label = shortcode.find('a.copy-to-clipboard.copy-label');

		copy_to_clipboard_value.attr({'rel' : slug+'_value'});
		copy_to_clipboard_label.attr({'rel' : slug+'_label'});
		shortcode.find('h3.header span.field-name').text(field_label);
		jQuery('.jcf_shortcodes_tooltip').hide();
		shortcode.find('input.jcf-value').val(shortcode_value).attr({'id' : slug+'_value'});
		shortcode.find('span.jcf-value').text(template_value);
		shortcode.find('input.jcf-label').val(shortcode_label).attr({'id' : slug+'_label'});
		shortcode.find('span.jcf-label').text(template_label);
		shortcode.css({'top': btn_position.top + 'px'}).show();
	});

	jQuery(document).click(function(e){
		if(jQuery(e.target).parents().filter('.jcf_shortcodes_tooltip').length < 1 && jQuery(e.target).parents().filter('.jcf-get-shortcode').length < 1){
			jQuery('.jcf_shortcodes_tooltip').hide();
		}
	});

	jQuery('.jcf_shortcodes_tooltip-close').click(function(){
		jQuery(this).parent().parent().parent().hide();
		return false;
	});

	jQuery('.copy-to-clipboard').bind('click', function(){
		var field_id = jQuery(this).attr('rel');
		var copy_element = jQuery('input#' + field_id);
		jcf_copy_to_clipboard(copy_element);
		return false;
	});
}

/*
 *	Copy to clipboard function
 */
function jcf_copy_to_clipboard(element) {
	jQuery("body").append("<input type='text' id='jcf_temp' style='position:absolute;opacity:0;'>");
	jQuery("#jcf_temp").val(element.val()).select();
	document.execCommand("copy");
	jQuery("#jcf_temp").remove();
}