/**
 * @file
 * Behavior for dialog formatter.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.blockSettingsSummary = {
    attach: function () {

      $('.double-field-dialog-link').once('double-field-dialog').click(function () {

        var data = $(this).next();
        $('<div/>')
          .html(data.html())
          .dialog({
            title: data.attr('title'),
            hide: {effect: 'explode'}
          });

          return false;

      })
    }
  }

})(jQuery);
