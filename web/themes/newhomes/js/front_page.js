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
                slidesPerView: 1,
                spaceBetween: 30,
                breakpoints: {
                    // when window width is >= 480px
                    576: {
                        slidesPerView: 2,
                        spaceBetween: 30
                    },
                    // when window width is >= 640px
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 30
                    }
                }
            });

            // Project
            var project = new Swiper(".project", {
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
                slidesPerView: 1,
                spaceBetween: 30,
                breakpoints: {
                    // when window width is >= 480px
                    768: {
                        slidesPerView: 2,
                        spaceBetween: 30
                    },
                    // when window width is >= 640px
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 30
                    }
                }
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
                slidesPerView: 3,
                spaceBetween: 15,
                breakpoints: {
                    // when window width is >= 480px
                    768: {
                        slidesPerView: 4,
                        spaceBetween: 20
                    },
                    // when window width is >= 640px
                    992: {
                        slidesPerView: 5,
                        spaceBetween: 30
                    },
                    // when window width is >= 1200px
                    1200: {
                        slidesPerView: 6,
                        spaceBetween: 30
                    }
                }
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
                slidesPerView: 3,
                spaceBetween: 15,
                breakpoints: {
                    // when window width is >= 480px
                    768: {
                        slidesPerView: 4,
                        spaceBetween: 20
                    },
                    // when window width is >= 640px
                    992: {
                        slidesPerView: 5,
                        spaceBetween: 30
                    },
                    // when window width is >= 1200px
                    1200: {
                        slidesPerView: 6,
                        spaceBetween: 30
                    }
                }
            });
        }
        
        if ($('.path-gioi-thieu').length) {
            // about us
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

            // mission
            var mission = new Swiper(".mission", {
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                spaceBetween: 0,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                loop: true,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                breakpoints: {
                    // when window width is >= 480px
                    768: {
                        slidesPerView: 2
                    },
                    // when window width is >= 1200px
                    1200: {
                        slidesPerView: 3,
                        allowTouchMove: false,
                        autoplay: false,
                        pagination: false,
                        navigation: false
                    }
                }
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
                slidesPerView: 3,
                spaceBetween: 15,
                breakpoints: {
                    // when window width is >= 480px
                    768: {
                        slidesPerView: 4,
                        spaceBetween: 20
                    },
                    // when window width is >= 640px
                    992: {
                        slidesPerView: 5,
                        spaceBetween: 30
                    },
                    // when window width is >= 1200px
                    1200: {
                        slidesPerView: 6,
                        spaceBetween: 30
                    }
                }
            });

            // Company
            var partner = new Swiper(".company_member", {
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
                slidesPerView: 1,
                spaceBetween: 30,
                breakpoints: {
                    // when window width is >= 640px
                    992: {
                        slidesPerView: 2,
                        spaceBetween: 30
                    }
                }
            });
        }

    });
})( jQuery );

