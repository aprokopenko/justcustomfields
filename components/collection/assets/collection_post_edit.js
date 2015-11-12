/* 
 * collection_post_edit
 */

jQuery(document).ready(function(){
	jcf_collection_fields_control();
	
	
	//accordion
	jQuery('.collection_fields').accordion({
		header: "h3",
		icons: false,
		beforeActivate: function(event, ui){
			if(jQuery(ui.newHeader).hasClass('jcf_field_removed')){
				return false;
			}
		}
	});
	
	// init sortable
	jQuery('.jcf-field-container').sortable({
		handle: 'span.dashicons-editor-justify',
		opacity:0.7,
		placeholder: 'sortable-placeholder',
		scroll: true, 
		start: function (event, ui) { 
			console.log(ui);
			//jQuery('.collection_field_group').css({overflow: 'hidden', height: '50px'});
			ui.placeholder.html('<div class="sort-placheholder"></div>');
		},
		/*stop: function (event, ui){
			
		}*/
	});
});

function jcf_collection_fields_control(){
	
	// add more button
	jQuery('input.jcf_add_more_collection').click(function(){
		var container = jQuery(this).parent();
		
		var next_field_group_index = container.find('.collection_field_group').size();
		var new_html = container.find('div.collection_field_group:first').html();
		new_html = new_html
			.replace(/\[00\]/g, '[' + next_field_group_index + ']')
			.replace(/\-00\-/g, '-' + next_field_group_index + '-');
		new_html = '<div class="collection_field_group">' + new_html + '</div>';
		
		// add new html row
		container.find('div.collection_field_group:last').after( new_html );
		jQuery('.collection_fields').accordion('refresh');
		container.find('div.collection_field_group:last').find('h3').click();
		
		return false;
	})
	
	jQuery('div.collection_field_group span.dashicons-trash').live( 'click', function(e) {
		e.preventDefault();		
			/*jQuery(this).parent().find('.collection_group_title').after('<span class="jcf_collection_removed">Removed</span>');
			jQuery(this).parent().addClass('jcf_field_removed');
			jQuery(this).parent().next('div').slideToggle();*/
		if(confirm('Are you sure you want to delete the Collection Fields Group?')){
			jQuery(this).parent().parent().remove();
		}
		return false;
	});
}