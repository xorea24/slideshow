<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Gallery</title>

    {{-- Tailwind + Swiper, like uploading-slideshow --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        .swiper { width: 100%; height: 100vh; }
        .swiper-slide img { width: 100%; height: 100%; object-fit: cover; }

        .title-overlay {
            position: absolute;
            bottom: 10%;
            left: 5%;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-black">

    <div class="absolute top-5 left-5 z-20 pointer-events-none">
        <h1 class="text-white text-3xl font-bold drop-shadow-lg">Public Gallery</h1>
        <p class="text-white text-sm drop-shadow-lg">Slideshow using your photos</p>
    </div>

    <div class="swiper mySwiper">
        <div class="swiper-wrapper">

            @forelse($slides as $slide)
                <div class="swiper-slide">
                    {{-- IMPORTANT: make sure files are stored under storage/app/public/... --}}
                    <img src="{{ asset('storage/' . $slide->image_path) }}" alt="{{ $slide->name }}">

                    <div class="title-overlay bg-black/50 text-white px-6 py-3 rounded-lg backdrop-blur-md">
                        <h2 class="text-2xl font-bold">{{ $slide->name }}</h2>
                        <p class="text-sm text-gray-200">
                            {{ optional($slide->album)->name ?? 'No album' }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="swiper-slide flex items-center justify-center bg-gray-900 text-white">
                    <p>No images available in the gallery.</p>
                </div>
            @endforelse

        </div>

        <div class="swiper-pagination"></div>
        <div class="swiper-button-next !text-white"></div>
        <div class="swiper-button-prev !text-white"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        // From controller
        const slideDuration = {{ $seconds }} * 1000;
        const effectSetting = "{{ $effect }}";

        let swiperOptions = {
            loop: true,
            speed: 1000,
            autoplay: {
                delay: slideDuration,
                disableOnInteraction: false,
            },
            pagination: { el: ".swiper-pagination", clickable: true },
            navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
        };

        // Same effect options as in uploading-slideshow
        if (effectSetting === 'fade') {
            swiperOptions.effect = 'fade';
            swiperOptions.fadeEffect = { crossFade: true };
        } else if (effectSetting === 'slide-up') {
            swiperOptions.direction = 'vertical';
        } else if (effectSetting === 'slide-down') {
            swiperOptions.direction = 'vertical';
            swiperOptions.effect = 'creative';
            swiperOptions.creativeEffect = {
                prev: { translate: [0, '100%', 0] },
                next: { translate: [0, '-100%', 0] },
            };
        } else if (effectSetting === 'slide-left') {
            swiperOptions.direction = 'horizontal';
        } else if (effectSetting === 'slide-right') {
            swiperOptions.direction = 'horizontal';
            swiperOptions.effect = 'creative';
            swiperOptions.creativeEffect = {
                prev: { translate: ['100%', 0, 0] },
                next: { translate: ['-100%', 0, 0] },
            };
        } else if (effectSetting === 'zoom') {
            swiperOptions.effect = 'fade';
            document.styleSheets[0].insertRule(
                '.swiper-slide-active img { transform: scale(1.1); transition: transform ' + slideDuration + 'ms linear; }',
                0
            );
        }

        const swiper = new Swiper(".mySwiper", swiperOptions);
    </script>
</body>
</html>