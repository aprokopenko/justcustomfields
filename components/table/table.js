jQuery(document).ready(function() {
  jQuery('.jcf-table .jcf_add_row').live('click', function() {
    var container = jQuery(this).parent().parent().find('table');
    var jcf_table_row_max_index = container.find('tr').size();
    jcf_table_row_max_index--;
    var new_row = container.find('tr.hide').html();
    new_row = new_row.replace(/\[00\]/g, '[' + jcf_table_row_max_index + ']')
        .replace(/\-00\-/g, '-' + jcf_table_row_max_index + '-');
    new_row = '<tr>' + new_row + '</tr>';
    container.find('tr.hide').before(new_row);
    return false;
  });

  jQuery('.jcf-table .jcf_delete_row').live('click', function() {
    console.log('clicked');
    jQuery(this).parent().parent().remove();
  })

  jQuery('table.sortable').each(function() {
    jQuery(this).find('tbody').sortable({containment: jQuery(this), scroll: false, items: 'tr[class!=table-header]'});
  });
});
