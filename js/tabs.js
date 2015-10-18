/**
 * @file
 * Behavior for tabs formatter.
 */

(function ($) {

  'use strict';

  Drupal.behaviors.doubleFieldTabs = {
    attach: function () {

      jQuery(".double-field-tabs").tabs();

    }
  }

})(jQuery);
