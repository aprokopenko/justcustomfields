var jcf_relatedcontent_max_index = 0;
var jcf_relatedcontent_inited = false;
var jcf_post_body_content_container = '#post-body';
if ( jQuery('body').hasClass('edit-tags-php') ) {
  jcf_post_body_content_container = '#addtag';
} else if ( jQuery('body').hasClass('term-php') ) {
  jcf_post_body_content_container = '#edittag';
}

jQuery(document).ready(function() {

  jcf_relatedcontent_init();

  // add collection hook integration
  jcf_add_action('collection_row_added', 'init_relatedcontent', jcf_relatedcontent_collection_init);

});

function jcf_relatedcontent_collection_init() {
  jcf_relatedcontent_init();
}

function jcf_relatedcontent_init() {
  var node = jQuery(jcf_post_body_content_container);
  if ( node.find('div.jcf-relatedcontent-field').size() == 0 || jcf_relatedcontent_inited )
    return;

  jcf_relatedcontent_max_index = jQuery(jcf_post_body_content_container).find('div.jcf-relatedcontent-row').size();

  node.find('div.jcf-relatedcontent-field a.jcf_delete').live('click', function() {
    var row = jQuery(this).parents('div.jcf-relatedcontent-row:first');
    row.find('div.jcf-relatedcontent-container').css({'opacity': 0.3});
    row.find('div.jcf-relatedcontent-container .jcf_delete').hide();
    row.find('div.jcf-delete-layer')
        .show()
        .find('input:hidden').val('1');
    return false;
  });

  node.find('div.jcf-relatedcontent-field a.jcf_cancel').live('click', function() {
    var row = jQuery(this).parents('div.jcf-relatedcontent-row:first');
    row.find('div.jcf-relatedcontent-container').css({'opacity': 1});
    row.find('div.jcf-relatedcontent-container .jcf_delete').show();
    row.find('div.jcf-delete-layer')
        .hide()
        .find('input:hidden').val('0');
    return false;
  });

  // add more button
  node.find('div.jcf-relatedcontent-field a.jcf_add_more').live('click', function() {
    var container = jQuery(this).parent();

    jcf_relatedcontent_max_index++;
    var new_html = container.find('div.jcf-relatedcontent-row:first').html();
    new_html = new_html
        .replace(/\[00\]/g, '[' + jcf_relatedcontent_max_index + ']')
        .replace(/\-00\-/g, '-' + jcf_relatedcontent_max_index + '-');
    new_html = '<div class="jcf-relatedcontent-row">' + new_html + '</div>';

    // add new html row
    container.find('div.jcf-relatedcontent-row:last').after(new_html);
    container.find('p.howto').removeClass('jcf-hide');

    // attach new autocomplete event
    var input = container.find('div.jcf-relatedcontent-row:last p input:text').get(0);
    jcf_attach_autocomplete_event(input);
    jcf_relatedcontent_init_sortable(container);

    return false;
  })

  function jcf_attach_autocomplete_event( input ) {
    var post_types = jQuery(input).attr('alt');
    var input_id = jQuery(input).parent().find('input:hidden');
    jQuery(input).autocomplete({
      minLength: 2,
      source: function( request, response ) {
        var data = {
          action: 'jcf_related_content_autocomplete',
          term: request.term,
          post_types: post_types
        };
        jQuery.post(ajaxurl, data, response);
      },
      select: function( event, ui ) {
        input_id.val(ui.item.id);
      },
      open: function( event, ui ) {
        // mark in dropdown list query
        var term = jQuery(input).val();
        var term_re = term.replace(/\s/g, '\\s').replace(/\(/g, '\\(').replace(/\)/g, '\\)').replace(/\./g, '\\.').replace(/\*/g, '\\*');
        var re = new RegExp('(' + term_re + ')', 'gi');
        jQuery('ul.ui-autocomplete:visible a').each(function( i, a ) {
          var text = jQuery(a).text();
          var marked = text.replace(re, '<b>$1</b>');
          jQuery(a).html(marked);
        })
      }
    });

  }

  // init autocomplete
  node.find('div.jcf-relatedcontent-container p input:text').each(function( i, input ) {
    jcf_attach_autocomplete_event(input)
  });

  // init sortable
  jcf_relatedcontent_init_sortable( node.find('.jcf-relatedcontent-field') );

  jcf_relatedcontent_inited++;
}

function jcf_relatedcontent_init_sortable(node) {
  node.sortable({
    handle: 'span.drag-handle',
    opacity: 0.7,
    placeholder: 'sortable-placeholder',
    start: function( event, ui ) {
      ui.placeholder.html('<div class="sort-placheholder"></div>');
    },
  });
}