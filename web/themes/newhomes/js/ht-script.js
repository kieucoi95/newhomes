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
            // Footer
            Drupal.ht_main.footer();
        });
    };

    Drupal.ht_main.header = function() {
        // Search box
        $('.search-btn>a').click(function() {
            if ($(this).hasClass('icon-search')) {
                $(this).hide();
                $('.search-btn>a.icon-close').show();
                $(this).parents('._header').find('.sb').show();
            } else {
                $(this).hide();
                $('.search-btn>a.icon-search').show();
                $(this).parents('._header').find('.sb').hide();
            }
        });

        // Lang switcher
        $('.lang-nav a').click(function() {
            let langcode = $(this).attr('lang');
            window.location.href = jQuery('a[hreflang="' + langcode + '"]').attr('href');
        });

        // Sticky header
        $(window).scroll(function() {
            if ($(document).scrollTop() >= 100) {
                $('header').addClass('header-sticky');
            } else {
                $('header').removeClass('header-sticky');
            }
        });

        // Mobile header
        $('.mb-btn').click(function() {
            $('.mb-header').toggleClass('open');
            $('.search-btn>a.icon-close').hide();
            $('.search-btn>a.icon-search').show();
            $(this).parents('._header').find('.sb').hide();
        });
    };

    Drupal.ht_main.footer = function() {
        $('form#register_email').submit(function(event) {
            event.preventDefault();
            let val = $(this).find('input[name="register_email"]').val();
            let input = $('.real_register input[type="email"]');
            let submit = $('.real_register input[type="submit"]');
            input.val(val);
            submit.trigger('click');
            return false;
        });
    };
})(jQuery, Drupal);