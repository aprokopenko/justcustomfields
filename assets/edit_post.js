jQuery(document).ready(function(){
	jcf_getShortcode();
});

/*
 *	Get shortcodes
 */
function jcf_getShortcode(){
	jQuery('.jcf-get-shortcode').click(function(){
		var btn = jQuery(this);
		var parent_block = btn.parent();
		var shortcode = parent_block.find('.jcf_shortcodes_tooltip');
		var btn_position = btn.position();
		jQuery('.jcf_shortcodes_tooltip').hide();
		shortcode.css({'top': btn_position.top + 'px'}).show();
	});

	jQuery('.jcf_shortcodes_tooltip-close').click(function(){
		jQuery(this).parent().parent().parent().hide();
		return false;
	});

	jQuery('.copy-to-clipboard').bind('click', function(){
		var field_id = jQuery(this).attr('rel');
		var copy_element = jQuery('input#' + field_id);
		jcf_copyToClipboard(copy_element);
		return false;
	});
}

/*
 *	Copy to clipboard function
 */
function jcf_copyToClipboard(element) {
	jQuery("body").append("<input type='text' id='jcf_temp' style='position:absolute;opacity:0;'>");
	jQuery("#jcf_temp").val(element.val()).select();
	document.execCommand("copy");
	jQuery("#jcf_temp").remove();
}