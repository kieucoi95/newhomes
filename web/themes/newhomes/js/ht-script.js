(function($, Drupal) {
    Drupal.behaviors.ht_script = {
        attach: function(context, settings) {
            Drupal.ht_main.run(context, settings);
        }
    }

    Drupal.ht_main = Drupal.ht_main || {};

    Drupal.ht_main.run = function(context, settings) {
        $(document, context).once('ht_script').each(function() {
            // Main menu
            Drupal.ht_main.header();
        });
    };

    Drupal.ht_main.header = function() {
        console.log(2);
    };
})(jQuery, Drupal);