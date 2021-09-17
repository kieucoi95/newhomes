(function($, Drupal) {
    Drupal.behaviors.ht_news = {
        attach: function(context, settings) {
            Drupal.ht_news.run(context, settings);
        }
    }

    Drupal.ht_news = Drupal.ht_news || {};

    Drupal.ht_news.run = function(context, settings) {
        $(document, context).once('ht_news').each(function() {
            Drupal.ht_news.handler();
        });
    };

    Drupal.ht_news.handler = function() {
        let real_f = $('.news_page_filter');
        let real_s = real_f.find('select');
        let real_sm = real_f.find('.form-actions input');

        let fake_f = "<ul>";
        real_s.find('option').each(function() {
            let v = $(this).attr('value');
            let t = $(this).text();
            if (v != 'All') {
                let _c = '';
                if (v == real_s.val()) {
                    _c = 'active';
                }
                fake_f += '<li><a _value="' + v + '" class="' + _c + '">' + t + '</a></li>';
            }
        });
        fake_f += "</ul>";
        fake_f += '<div class="ht_mobile_show open_filter"><a>☰</a></div>';
        $('.fake-filter-news').html(fake_f);

        $(document).on('click', '.fake-filter-news ul a', function() {
            let v = $(this).attr('_value');
            real_s.val(v);
            real_sm.trigger('click');
        });

        $(document).on('click', '.open_filter a', function() {
            $(this).parents('.fake-filter-news').find('ul').toggleClass('open');
        });
    };
})(jQuery, Drupal);