/**
 * @file
 * Custom JS library.
 */
(function($, Drupal) {
    Drupal.behaviors.ht_custom = {
        attach: function(context, settings) {
            $(document, context).once('ht_custom').each(function() {
            });
        }
    }
})(jQuery, Drupal);