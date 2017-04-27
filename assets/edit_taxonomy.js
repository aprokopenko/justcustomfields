( function( $ ) {

  function jcf_taxonomy_refresh_custom_fields() {
    var taxonomy = $('#addtag input[name=taxonomy]').val();
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: {
        'action': 'jcf_ajax_get_taxonomy_custom_fields',
        'taxonomy': taxonomy
      },
      success: function(response) {
        jcf_do_action('taxonomy_term_added', this, response);

        $('#jcf_taxonomy_fields').html(response);

        jcf_do_action('taxonomy_term_added_form_refreshed', this, response);
      }
    })
  }

  $( document ).ready(function(){
    if ( $('#addtag').size() ) {
      $( document ).ajaxSuccess(function( event, xhr, settings ) {
        if ( ajaxurl && ajaxurl == settings.url && settings.data && -1 != settings.data.indexOf('action=add-tag') ) {
          if ( ! $('#ajax-response .error').size() ) {
            // this means we don't have errors. we should cleanup meta boxes
            jcf_taxonomy_refresh_custom_fields();
          }
        }
      });
    }
  })

}( jQuery ));
