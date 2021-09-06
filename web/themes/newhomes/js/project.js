(function( $ ){
    $(document).ready(function () {
        // Gallery
        var gallery_project_thmub = new Swiper(".gallery_project_thmub", {
            spaceBetween: 10,
            slidesPerView: 4,
            freeMode: true,
            watchSlidesProgress: true,
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
    });
})( jQuery );

