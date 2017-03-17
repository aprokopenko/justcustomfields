var jcf_check_visibility_rules = function(){};
var jcf_visibility_rules_taxonomies = {
  hirerachical: {},
  linear: {}
};
// init global hooks storage
window.jcfActions = {};

/**
 * Analog of wordpress php add_action function
 * Adds callback to stack
 *
 * @param string action
 * @param string id
 * @param function callback
 */
function jcf_add_action(action, id, callback) {
  if ( 'undefined' == typeof(window.jcfActions[action]) ) {
    window.jcfActions[action] = {};
  }
  if ( 'undefined' == typeof(window.jcfActions[action][id]) ) {
    window.jcfActions[action][id] = callback;
  }
  else {
    window.jcfActions[action][id] = callback;
  }
}

/**
 * Analog of wordpress php do_action function
 * Runs callbacks from stack
 *
 * @param string action
 * @param mixed _this   the context where do_action is called
 * @returns {boolean}|object
 */
function jcf_do_action(action, _this) {
  if ( 'undefined' == typeof(window.jcfActions[action]) ) {
    return false;
  }

  var results = {};
  var args = [].slice.apply(arguments).slice(2);
  for (var k in window.jcfActions[action]) {
    var callback = window.jcfActions[action][k];
    if ( 'function' == typeof(callback) ) {
      results[k] = callback.apply(_this, args);
    }
  }
  return results;
}

( function( $ ) {

  $(document).ready(function() {
    jcf_init_shortcodes_popup();
    jcf_init_visibility_rules();
  });

  /*
   *	Get shortcodes
   */
  function jcf_init_shortcodes_popup() {
    // open shortcodes popup
    $('.jcf-get-shortcode').click(function() {
      var btn = $(this);

      var fieldbox = btn.parents('div.jcf_edit_field');
      var field_label = fieldbox.find('label:first').text();

      var postbox = btn.parents('div.postbox');
      var popup = postbox.find('div.jcf_shortcodes_tooltip');

      var field_id = btn.attr('rel');

      var shortcode = '[jcf-value field="' + field_id + '"]';
      popup.find('input.jcf-shortcode-value').val(shortcode);
      popup.find('input.jcf-template-value').val('<?php echo do_shortcode(\'' + shortcode + '\'); ?>');

      popup.find('h3.header span.field-name').text(field_label);
      // hide all other popups;
      $('div.jcf_shortcodes_tooltip').hide();
      $('span.jcf_copied_to_clip').remove();
      // finally show popup
      popup
          .css({'top': btn.position().top + 'px'})
          .show();
    });

    // hide tooltip on click in some other location outside tooltip
    $(document).click(function( e ) {
      if ( $(e.target).parents().filter('div.jcf_shortcodes_tooltip').length < 1 && $(e.target).parents().filter('div.jcf-get-shortcode').length < 1 ) {
        $('div.jcf_shortcodes_tooltip').hide();
      }
    });

    // init tooltip close btn
    $('a.jcf_shortcodes_tooltip-close').click(function() {
      $(this).parent().parent().parent().hide();
      return false;
    });

    $('div.jcf_shortcodes_tooltip a.copy-to-clipboard').bind('click', function() {
      var input = $(this).parent().find('input');
      jcf_copy_to_clipboard(input);
      return false;
    });
  }


  /*
   *	Copy to clipboard function
   */
  function jcf_copy_to_clipboard( element ) {
    $(element).select();
    $('span.jcf_copied_to_clip').remove();
    $(element).parent().append('<span class="jcf_copied_to_clip dashicons dashicons-yes wp-ui-text-highlight"></span>');
    document.execCommand("copy");
  }

  /**
   * Visibility rules check and events
   */

  function jcf_init_visibility_rules() {
    if ( typeof(jcf_fieldsets_visibility_rules) == 'undefined' ) return;

    // prepare object with taxonomy information,
    // to know which taxonomies are involved in visibility settings
    for ( fieldset_id in jcf_fieldsets_visibility_rules ) {
      for ( var i=0; i < jcf_fieldsets_visibility_rules[fieldset_id].length; i++ ) {
        var rule = jcf_fieldsets_visibility_rules[fieldset_id][i];
        if ( rule.based_on != 'taxonomy' ) continue;

        if ( rule.taxonomy.hierarchical ) {
          jcf_visibility_rules_taxonomies.hirerachical[rule.taxonomy.name] = rule.taxonomy.label;
        }
        else {
          jcf_visibility_rules_taxonomies.linear[rule.taxonomy.name] = rule.taxonomy.label;
        }
      }
    }

    // Patching WP standard javascript code to call our check functions on tags change
    tagBox._jcfQuickClickOrigin = tagBox.quickClicks;
    tagBox.quickClicks = function( el ) {
      this._jcfQuickClickOrigin(el);

      // validate that this taxonomy is used for visibility settings
      var id = $(el).attr('id');
      if ( ! jcf_visibility_rules_taxonomies.linear[id] ) return;

      // run our callback to update visibility
      jcf_check_visibility_rules();
    };

    // add events for categories checkboxes
    // (only for categories which used in visibility settings)
    for ( tax_id in jcf_visibility_rules_taxonomies.hirerachical ) {
      var checkbox_name_pattern = 'in\-' + tax_id;
      $('input[id^=' + checkbox_name_pattern + ']')
          .click( jcf_check_visibility_rules );
    }

    // init page template update
    var page_select = jcf_get_page_template_select();

    if ( page_select !== undefined ) {
       page_select.change( jcf_check_visibility_rules );
    }
    
    // run initial check
    jcf_check_visibility_rules();
  }
  
  /* get page template */
  function jcf_get_page_template_select()
  {
    var control = $('select[name=_wp_page_template], select[name=page_template]').first();
    if (control.length) return $(control);
  }

  /**
   * Main function entry to check fieldsets visibility
   */
  jcf_check_visibility_rules = function() {
    var post_type = $('#post_type').val();
    var selected_tags = jcf_get_post_selected_tags();
    var selected_cats = jcf_get_post_selected_categories();

    var page_select = jcf_get_page_template_select();
    if ( page_select !== undefined ) {
       var selected_page_template = page_select.val();
    }

    for ( fieldset_id in jcf_fieldsets_visibility_rules ) {

      // set init flags based on first rule
      var first_rule = jcf_fieldsets_visibility_rules[fieldset_id][0];
      var fieldset_display = (first_rule.join_condition == 'and')? 1 : 0;
      var invert_display = (first_rule.visibility_option == 'hide')? true : false;

      for ( var i=0; i < jcf_fieldsets_visibility_rules[fieldset_id].length; i++ ) {
        var rule = jcf_fieldsets_visibility_rules[fieldset_id][i];
        if ( rule.visibility_option != first_rule.visibility_option ) continue;

        if ( rule.join_condition == 'and' ) {
          fieldset_display &= jcf_visibility_rule_active(rule, selected_cats, selected_tags, selected_page_template);
        }
        else {
          fieldset_display |= jcf_visibility_rule_active(rule, selected_cats, selected_tags, selected_page_template);
        }
      }

      // show/hide fieldset
      if ( invert_display ) {
        fieldset_display = !fieldset_display;
      }

      var fieldset = $('#jcf_fieldset-' + fieldset_id);
      if ( fieldset_display ) {
        fieldset.show();
      }
      else {
        fieldset.hide();
      }

    }

    //console.log({tags: selected_tags, categories: selected_cats, page_tpl: selected_page_template});
  }

  /**
   * Check if rule settings match the real situation on screen
   *
   * @param Object rule  rule to check active status
   * @param array categories   selected post category-style taxonomies
   * @param array tags         selected post tag-style taxonomies
   * @param string page_template   selected page template
   * @returns {boolean}
   */
  function jcf_visibility_rule_active( rule, categories, tags, page_template ) {
    var selected_values = null;
    var rule_values = null;

    // define which values we need to compare
    if ( rule.based_on == 'page_template' ) {
      selected_values = [page_template];
      rule_values = rule.rule_templates;
    }
    else if ( rule.based_on == 'taxonomy' && rule.taxonomy.hierarchical ) {
      var taxonomy = rule.taxonomy.name;
      selected_values = categories[taxonomy];
      rule_values = rule.term_ids;
    }
    else if ( rule.based_on == 'taxonomy' && ! rule.taxonomy.hierarchical ) {
      var taxonomy = rule.taxonomy.name;
      selected_values = tags[taxonomy];
      rule_values = rule.term_names;
    }

    // if there are no values - there are no match
    if ( !selected_values || selected_values.length == 0 )
        return;

    // check array intersections. if intersection is not null - then rule is active
    var has_intersection = false;
    for ( var i=0; i < rule_values.length; i++ ) {
      for ( var j=0; j < selected_values.length; j++ ) {
        if ( rule_values[i] == selected_values[j] ) {
          has_intersection = true;
          break;
        }
      }

      if ( has_intersection ) break;
    }

    return has_intersection;
  }

  /**
   * Find selected tags on post edit screen. Summary is saved in hidden textarea, separated with comma
   * Group selected tags by taxonomy
   * 
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

  /**
   * Find checked categories and return IDs grouped by taxonomy
   * @returns {{}}
   */
  function jcf_get_post_selected_categories() {
    var selected = {};
    $('div.categorydiv input[id^=in\-]:checked').each(function(){
      // skip checkboxes in additional UI boxes (like popular)
      if ( ! $(this).attr('name') ) return;

      var id = $(this).attr('id');
      // custom taxonomies has complex id: "in-{taxo}-{id}"
      if ( matches = id.match(/^in\-(.*?)\-([0-9]+)$/) ) {
        var taxonomy = matches[1];
      }

      var term = $(this).val();
      if ( !selected[taxonomy] )
          selected[taxonomy] = [];

      selected[taxonomy].push(term);
    })
    return selected;
  }

}( jQuery ));