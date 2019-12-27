jQuery(function($) {

  /**
   * Justcoded Form elements editor plugin
   * @return {Object} - DOM element
   */
  $.fn.jcMultiField = function(options) {
    var counter = 0;

    var settings = $.extend({
      addButton: { class: 'jcmf-btn' },
      removeButton: { class: 'jcmf-btn-remove' },
      dragHandler: { class: 'jcmf-handle' }
    }, options);

    var $el = this,
        $addInputBtn = $('<button class="jcmf-add-input-btn ' + settings.addButton.class + '" >Add item</button>'),
        $row = $('<div class="jcmf-form-item input-item "><span class="handle sortable ' + settings.dragHandler.class + '" ></span><span class="jcmf-remove-input ' + settings.removeButton.class + '" ></span></div>');

    /**
     * Add inputs and add button elements to page and add events
     */
    var init = function() {
      $el.addClass('jcmf-multi-field');

      if ( ( 'object' != typeof(settings.data) ) || settings.data.length == 0 ) {
        settings.data = [];
        settings.data.push( {} );
      }

      addRows();

      $el.append($addInputBtn);

      //EVENTS
      $el.on("click", 'button.jcmf-add-input-btn', function(e) {
        e.preventDefault();
        addRow();
      });

      $el.on("click", '.jcmf-remove-input', function(e) {
        e.preventDefault();
        removeItem($(e.currentTarget).closest('.jcmf-form-item'));
      });
      //check sortable row
      $el.on("keyup", '.jcmf-input', function(e) {
        e.preventDefault();
        checkSortableOrder($(e.currentTarget).closest('.jcmf-form-item'));
      });
    }

    /**
     * Append rows to module
     */
    var addRows = function() {
      var index = 0;
      $.each(settings.data, function(i, d) {
        addRow(index);
        index++;
      });
    }

    /**
     * Create row, add inputs to row and append it to the module
     * @param {[type]} index [description]
     */
    var addRow = function(index) {
      var row = $row.clone().addClass('cols col-' + settings.structure.length);
      $el.append(row);

      $.each(settings.structure, function(i, d) {
        var value = settings.data[index]? settings.data[index][d.name] : null;
        var title = d.placeholder ? d.placeholder : '';

        switch (d.type) {
          case 'select':
            var control = $("<select>");
            control.addClass('jcmf-input');
            for ( var key in d.items ) {
              $(control).append('<option value="' + key + '">' + d.items[key] + '</option>');
            }
            if ( typeof(value) == 'undefined' || null === value ) {
              value = $(control).find('option:first').val();
            }
            break;
          case 'textarea':
            var control = $("<textarea>");
            control.attr({
              placeholder: title
            }).addClass('jcmf-input');
            break;
          default:
            var control = $("<input />");
            control.attr({
              placeholder: title,
              type: d.type ? d.type : 'text',
            }).addClass('jcmf-input');
        }

        $(control).val(value).attr({
          name: settings.fieldId + '[' + counter + ']' + '[' + d.name + ']',
          title: title
        }).appendTo(row);

        if ( settings.data[index] && settings.data[index]['_hasError'] ) {
          $(control).parents('.jcmf-form-item').addClass('has-error');
        }
      });

      checkSortableOrder(row);

      counter++;
    };

    /**
     * If one of the input not empty - row can be sortable
     * @param  {Object} row - row element
     */
    var checkSortableOrder = function(row) {
      var disableSorting = row.find('input').filter(function() {
            return $.trim($(this).val()).length > 0
          }).length > 0;
      if (disableSorting) {
        row.find('.handle').addClass('sortable');
      }else{
        row.find('.handle').removeClass('sortable');
      }
    };


    /**
     * Remove element form the DOM
     * @param  {Oblect} $item - element to delete
     */
    var removeItem = function($item) {
      $item.remove();
    }

    init();

    return this.sortable({
      items: ".jcmf-form-item",
      handle: ".handle.sortable"
    });
  };

});
