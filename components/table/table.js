var add_row_btn = '.jcf_add_row';
function addRowToTable(node) {
	var count_cols = node.find( 'td' ).lenght();
	console.log(count_cols);
	node.append()
}

jQuery(document).ready(function() {
	jQuery( add_row_btn ).click(function() {
		var tbl = jQuery( this ).parent().parent().find('table');
		var new_row = tbl.find('tr:last-child').clone();
		tbl.append(new_row);
		return false;
	});
	jQuery( '.sortable' ).each(function() {
		jQuery( this ).find( 'tbody' ).sortable({ containment: jQuery( this ), scroll: false });
	});
});
