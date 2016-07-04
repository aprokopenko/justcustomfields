var jcf_check_visibility_rules = function(){};
jQuery(document).ready(function() {
  jcf_init_shortcodes_popup();
});

/*
 *	Get shortcodes
 */
function jcf_init_shortcodes_popup() {
  // open shortcodes popup
  jQuery('.jcf-get-shortcode').click(function() {
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
  jQuery(document).click(function( e ) {
    if ( jQuery(e.target).parents().filter('div.jcf_shortcodes_tooltip').length < 1 && jQuery(e.target).parents().filter('div.jcf-get-shortcode').length < 1 ) {
      jQuery('div.jcf_shortcodes_tooltip').hide();
    }
  });

  // init tooltip close btn
  jQuery('a.jcf_shortcodes_tooltip-close').click(function() {
    jQuery(this).parent().parent().parent().hide();
    return false;
  });

  jQuery('div.jcf_shortcodes_tooltip a.copy-to-clipboard').bind('click', function() {
    var input = jQuery(this).parent().find('input');
    jcf_copy_to_clipboard(input);
    return false;
  });
}


/*
 *	Copy to clipboard function
 */
function jcf_copy_to_clipboard( element ) {
  jQuery(element).select();
  jQuery('span.jcf_copied_to_clip').remove();
  jQuery(element).parent().append('<span class="jcf_copied_to_clip dashicons dashicons-yes wp-ui-text-highlight"></span>');
  document.execCommand("copy");
}

/**
 * Visibility rules check and events
 */
( function( $ ) {
  /**
   * Patch for WP standard javascript code to call our check functions on tags change
   * @type {tagBox.quickClicks|*}
   * @private
   */
  tagBox._jcfQuickClickOrigin = tagBox.quickClicks;
  tagBox.quickClicks = function( el ) {
    this._jcfQuickClickOrigin(el);

    jcf_check_visibility_rules();
  };

  /**
   * Main function entry to check fieldsets visibility
   */
  jcf_check_visibility_rules = function() {
    var selected_tags = jcf_get_post_selected_tags();
    console.log(selected_tags);

    var selected_cats = jcf_get_post_selected_categories();
    console.log(selected_cats);
  }

  $(document).ready(function() {
    // TODO: init events for categories and page template

    // run initial check
    jcf_check_visibility_rules();
  })

  /**
   * Find selected tags on post edit screen. Summary is saved in hidden textarea, separated with comma
   * @returns {{}}
   */
  function jcf_get_post_selected_tags() {
    var selected = {};
    $('textarea.the-tags').each(function () {
      var tags = $(this).val();
      if ( tags == '' ) return;

      var taxonomy = $(this).attr('id').replace('tax-input-', '');
      var tags = tags.split(',');
      selected[taxonomy] = tags;
    })
    return selected;
  }

  function jcf_get_post_selected_categories() {
    var selected = {};
    $('div.categorydiv input[id^=in\-]:checked').each(function(){
      // skip checkboxes in additional UI boxes (like popular)
      if ( ! $(this).attr('name') ) return;

      var taxonomy = $(this).attr('name').replace('[]', '');
      // custom taxonomies has complex name: "tax_input[{taxo}][]"
      if ( custom_taxo = taxonomy.match(/^tax_input\[(.+)\]/) ) {
        taxonomy = custom_taxo[1];
      }

      var term = $(this).val();
      if ( !selected[taxonomy] )
          selected[taxonomy] = [];

      selected[taxonomy].push(term);
    })
    return selected;
  }

  /**
   * Set visibility rules for fieldsets
   * /
  function jcf_init_fieldset_visibility_rules() {
    var checked_taxonomies = jcf_get_selected_taxonomies();
    jcf_apply_visibility_rules(checked_taxonomies);
  console.log(checked_taxonomies);
    // Check selected categories
    jQuery('input[id^="in-category"]').change(function() {
      checked_taxonomies = jcf_get_selected_taxonomies();
      jcf_apply_visibility_rules(checked_taxonomies);
    });

    //Check selected tags and custom taxonomies
    jQuery('.tagchecklist').bind("DOMSubtreeModified", function() {
      checked_taxonomies = jcf_get_selected_taxonomies();
      jcf_apply_visibility_rules(checked_taxonomies);
    });

    //Check selected template
    jQuery('#page_template').change(function() {
      checked_taxonomies = jcf_get_selected_taxonomies();
      jcf_apply_visibility_rules(checked_taxonomies);
    });

  }




  /*
   * Get selected terms of taxonomies
   * @returns {jcf_get_selected_taxonomies.tags|Array}
   */
  function jcf_get_selected_taxonomies() {
    var tags = [];
    jQuery('input[id^="in-category"]').each(function() {
      if ( jQuery(this).is(':checked') ) {
        tags.push(jQuery(this).parent().text().trim());
      }
    });
    jQuery('.tagchecklist').find('span').each(function() {
      tags.push(jQuery(this)[0].lastChild.data.trim());
    });
    return tags;
  }

  /*
   * Apply rules for display fieldsets
   * @param {Array} checked_taxonomies
   */
  function jcf_apply_visibility_rules( checked_taxonomies ) {
    var visibility_rules = fieldsets_visibility_rules;

    for ( var fieldset_id in visibility_rules ) {

      if ( visibility_rules[fieldset_id].length < 1 ) {//fieldset doesn't have any rules
        var display = true;
      }
      else if ( visibility_rules[fieldset_id].length < 2 ) {//fieldset has just one rule
        var rule = visibility_rules[fieldset_id][0];
        var default_display = (rule.visibility_option == 'hide');
        var display = jcf_set_display_option(rule, checked_taxonomies, default_display);
      }
      else { //fieldset has many rules
        for ( var key in visibility_rules[fieldset_id] ) {
          var rule = visibility_rules[fieldset_id][key];
          if ( key == 0 ) { //set default visibility option
            var default_display = (rule.visibility_option == 'hide');
            var display = jcf_set_display_option(rule, checked_taxonomies, default_display);
          }
          else { //set options in dependence of conditions
            if ( rule.join_condition == 'and' ) {
              default_display &= (rule.visibility_option == 'hide');
              display &= jcf_set_display_option(rule, checked_taxonomies, default_display);
            }
            else {
              default_display |= (rule.visibility_option == 'hide');
              display |= jcf_set_display_option(rule, checked_taxonomies, default_display);
            }
          }
        }
      }

      if ( display ) {
        jQuery('#jcf_fieldset-' + fieldset_id).show();
      }
      else {
        jQuery('#jcf_fieldset-' + fieldset_id).hide();
      }
    }
  }

  /*
   * Set display option for fieldsets
   * @param {Object} rule
   * @param {Array} checked_taxonomies
   * @param {Boolean} default_display
   * @returns {Boolean}
   */
  function jcf_set_display_option( rule, checked_taxonomies, default_display ) {
    if ( rule.based_on == 'page_template' ) {
      var templates = rule.rule_templates;
      var selected_template = jQuery('#page_template').val();
      if ( templates.indexOf(selected_template) > -1 ) {
        return (rule.visibility_option == 'show');
      }
      else {
        return (rule.visibility_option == 'hide');
      }
    }
    else if ( rule.based_on == 'taxonomy' ) {
      var terms = rule.rule_taxonomy_terms;
      for ( var i = 0; i < terms.length; i++ ) {
        if ( checked_taxonomies.indexOf(terms[i].name) > -1 ) {
          return (rule.visibility_option == 'show');
        }
        else {
          return (rule.visibility_option == 'hide');
        }
      }
    }
    return default_display;
  }
}( jQuery ));