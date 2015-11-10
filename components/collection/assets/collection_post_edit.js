/* 
 * collection_post_edit
 */

jQuery(document).ready(function(){
	jcf_collection_fields_control();
	
	
	// init sortable
	jQuery('.jcf-field-container').sortable({
		handle: 'span.dashicons-sort',
		opacity:0.7,
		placeholder: 'sortable-placeholder',
		scroll: true, 
		start: function (event, ui) { 
			//jQuery('.collection_field_group').css({overflow: 'hidden', height: '50px'});
			ui.placeholder.html('<div class="sort-placheholder"></div>');
		},
		/*stop: function (event, ui){
			
		}*/
	});
});

function jcf_collection_fields_control(){
	
	// add more button
	jQuery('a.jcf_add_more_collection').click(function(){
		var container = jQuery(this).parent();
		
		var next_field_group_index = container.find('.collection_field_group').size();
		var new_html = container.find('div.collection_field_group:first').html();
		new_html = new_html
			.replace(/\[00\]/g, '[' + next_field_group_index + ']')
			.replace(/\-00\-/g, '-' + next_field_group_index + '-');
		new_html = '<div class="collection_field_group">' + new_html + '</div>';
		
		// add new html row
		container.find('div.collection_field_group:last').after( new_html );
		
		return false;
	})
	
	jQuery('div.collection_field_group span.dashicons-trash').live( 'click', function() {
		if(confirm('Are you sure you want to delete the Collection Fields Group?')){
			jQuery(this).parent().remove();
		}
		return false;
	});
}