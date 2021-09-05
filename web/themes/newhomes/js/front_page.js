(function( $ ){
    $(document).ready(function () {
        
        // Home slide banner
        var home_banner = new Swiper(".homes-slide", {
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });

        // Home about us
        var home_banner = new Swiper(".about-us-slide", {
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            loop: true,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });

        // Field of activity
        var home_banner = new Swiper(".field_of_activity", {
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            slidesPerView: 3,
            spaceBetween: 30,
        });

    });
})( jQuery );

