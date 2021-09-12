(function( $ ){
    $(document).ready(function () {
        // Gallery
        if ($('.page-node-type-du-an').length) {
            var gallery_project_thmub = new Swiper(".gallery_project_thmub", {
                spaceBetween: 10,
                slidesPerView: 2,
                freeMode: true,
                watchSlidesProgress: true,
                breakpoints: {
                    // when window width is >= 480px
                    768: {
                        slidesPerView: 3
                    },
                    // when window width is >= 1200px
                    1200: {
                        slidesPerView: 4,
                    }
                }
            });
            var gallery_project = new Swiper(".gallery_project", {
                spaceBetween: 10,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                thumbs: {
                    swiper: gallery_project_thmub,
                },
            });

            $('.gallery_project_thmub .swiper-slide:first-child').addClass('active');
            gallery_project.on('slideChange', function () {
                let total = $('.gallery_project .swiper-slide').length,
                    index = gallery_project.activeIndex + 1;
                $('.gallery_project_thmub .swiper-slide[aria-label="' + index + ' / ' + total +'"]').addClass('active');
                $('.gallery_project_thmub .swiper-slide[aria-label="' + index + ' / ' + total +'"]').siblings().removeClass('active');
            });
        }
        

        $('.category_item').click(function (e) { 
            e.preventDefault();
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            let id = $(this).attr('data-id');
            $('.views-exposed-form .form-select').val(id);
            $('.views-exposed-form input[type="submit"]').trigger('click');
        });

        $(document).ajaxStop(function(){
            $('.category_item').click(function (e) { 
                e.preventDefault();
                $(this).addClass('active');
                $(this).siblings().removeClass('active');
                let id = $(this).attr('data-id');
                $('.views-exposed-form .form-select').val(id);
                $('.views-exposed-form input[type="submit"]').trigger('click');
            });
        });
    });
})( jQuery );

