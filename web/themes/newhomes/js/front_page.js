(function( $ ){
    $(document).ready(function () {
        if ($('.path-frontpage').length) {
            // Home slide banner
            var home_banner = new Swiper(".homes-slide", {
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
            });

            // Home about us
            var about_us = new Swiper(".about-us-slide", {
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                loop: true,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                effect: 'fade'
            });

            // Field of activity
            var field_of_activity = new Swiper(".field_of_activity", {
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                slidesPerView: 3,
                spaceBetween: 30,
            });

            // subsidiaries
            var subsidiaries = new Swiper(".subsidiaries", {
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
                slidesPerView: 6,
                spaceBetween: 30,
                autoHeight:true, 
                autoWidth:true, 
            });

            // partner
            var partner = new Swiper(".partner-slide", {
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
                slidesPerView: 6,
                spaceBetween: 30,
                autoHeight:true, 
                autoWidth:true, 
            });
        }
        

    });
})( jQuery );

