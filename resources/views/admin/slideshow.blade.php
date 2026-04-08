@php
    // 1. FETCH SETTINGS & DATA
    $settingsRaw = \DB::table('settings')->get()->keyBy('key');
    
    $getS = fn($k, $d) => isset($settingsRaw[$k]) ? $settingsRaw[$k]->value : $d;

    $seconds = $getS('slide_duration', 5);
    $effect = $getS('transition_effect', 'fade');
    $showName = $getS('show_photo_name', '1');
    $showDesc = $getS('show_photo_description', '1');
    $overlayPos = $getS('overlay_position', 'bottom-left');
    $fontStyle = $getS('font_style', 'Arial');
    $fontColor = $getS('font_color', 'white');
    $displayAlbumIds = $getS('display_album_ids', '');

    // Determine shadow color for contrast
    $shadowColor = ($fontColor === 'black') ? 'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.8)';
    $shadowGlow = ($fontColor === 'black') ? 'rgba(255, 255, 255, 0.4)' : 'rgba(0, 0, 0, 0.4)';
    $albumIdArray = array_filter(explode(',', $displayAlbumIds));

    // 2. FETCH SLIDES
    $slidesQuery = \DB::table('photos')
        ->join('albums', 'photos.album_id', '=', 'albums.id')
        ->select('photos.*', 'albums.name as album_title', 'albums.description as album_desc')
        ->where('photos.is_active', 1); 

    if (!empty($albumIdArray)) {
        $slidesQuery->whereIn('photos.album_id', $albumIdArray)
                    ->orderByRaw("FIELD(photos.album_id, " . implode(',', $albumIdArray) . ")")
                    ->orderBy('photos.sort_order', 'asc')
                    ->orderBy('photos.created_at', 'desc');
    } else {
        $slidesQuery->orderBy('photos.sort_order', 'asc')
                    ->orderBy('photos.created_at', 'desc');
    }

    $slides = $slidesQuery->get();

    // 3. MASTER TIMESTAMP
    $lastSettingUpdate = \DB::table('settings')->max('updated_at');
    $lastImageUpdate = \DB::table('photos')->max('updated_at');
    $lastAlbumUpdate = \DB::table('albums')->max('updated_at');
    
    $masterTimestamp = max($lastSettingUpdate, $lastImageUpdate, $lastAlbumUpdate) ?? now();

    // Map Font Styles to CSS Stacks
    $fontStacks = [
        'Inter' => '"Inter", sans-serif',
        'Montserrat' => '"Montserrat", sans-serif',
        'Tahoma' => 'Tahoma, Verdana, "Segoe UI", sans-serif',
        'Book Antiqua' => '"Book Antiqua", Palatino, serif',
        'Arial' => 'Arial, Helvetica, sans-serif',
        'Georgia' => 'Georgia, serif',
        'Times New Roman' => '"Times New Roman", Times, serif',
        'Verdana' => 'Verdana, Geneva, sans-serif',
    ];
    $selectedFont = $fontStacks[$fontStyle] ?? 'Arial, sans-serif';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Access - Mayor's Office</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Montserrat:wght@400;700;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; background-color: black; }
        .swiper { width: 100%; height: 100vh; }
        .swiper-slide {
            display: flex;
            align-items: center;
            justify-content: center;
            background: black;
            height: 100vh !important; 
        }
        .swiper-slide img { 
            width: 100%; 
            height: 100% !important; 
            object-fit: cover;
        }
        
        @media (orientation: portrait) {
            .swiper-slide img {
                object-fit: contain;
                background-color: black;
            }
        }

        .title-overlay {
            position: absolute;
            z-index: 50;
            max-width: 90%;
            pointer-events: none;
            --tx: 0;
            --ty: 30px;
        }

        /* Responsive Positioning Classes */
        .pos-bottom-left { bottom: 10%; left: 5%; text-align: left; }
        .pos-bottom-right { bottom: 10%; right: 5%; text-align: right; }
        .pos-bottom-center { bottom: 10%; left: 50%; --tx: -50%; transform: translateX(-50%); text-align: center; }
        
        .pos-top-left { top: 10%; left: 5%; text-align: left; --ty: -30px; }
        .pos-top-right { top: 10%; right: 5%; text-align: right; --ty: -30px; }
        .pos-top-center { top: 10%; left: 50%; --tx: -50%; --ty: -30px; transform: translateX(-50%); text-align: center; }

        @media (min-width: 1024px) {
            .title-overlay { max-width: 70%; }
            .pos-bottom-left { bottom: 60px; left: 60px; }
            .pos-bottom-right { bottom: 60px; right: 60px; }
            .pos-bottom-center { bottom: 60px; left: 50%; }
            .pos-top-left { top: 60px; left: 60px; }
            .pos-top-right { top: 60px; right: 60px; }
            .pos-top-center { top: 60px; left: 50%; }
        }
        
        .swiper-slide-active .animate-text {
            animation: slideInText 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            animation-delay: 0.2s;
            opacity: 0;
        }
        @keyframes slideInText {
            from { opacity: 0; transform: translate(var(--tx), var(--ty)); filter: blur(5px); }
            to { opacity: 1; transform: translate(var(--tx), 0); filter: blur(0); }
        }
        .custom-font {
            font-family: {!! $selectedFont !!} !important;
            color: {{ $fontColor }} !important;
            text-shadow: 2px 2px 8px {{ $shadowColor }}, 
                         0px 0px 15px {{ $shadowGlow }};
        }

        #loading-overlay {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            background: black; color: white; transition: opacity 0.5s;
        }
        .hidden-overlay { opacity: 0; pointer-events: none; }
        .visible-overlay { opacity: 1; pointer-events: auto; }
    </style>
</head>
<body>

    <div id="loading-overlay" class="hidden-overlay">
        <div class="relative w-24 h-24 mb-6">
            <div class="absolute inset-0 border-4 border-gray-800 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-red-600 rounded-full border-t-transparent animate-spin"></div>
        </div>
        <h2 class="text-white text-xl font-light tracking-[0.3em] uppercase animate-pulse">Updating images...</h2>
    </div>

    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            @forelse($slides as $slide)
                <div class="swiper-slide relative">
                    <img src="{{ asset('storage/' . $slide->image_path) }}" alt="Slideshow Image">

                    @if($showName == '1' || ($showDesc == '1' && $slide->description))
                        <div class="title-overlay animate-text pos-{{ $overlayPos }}">
                            
                            @if($showName == '1')
                                <h1 class="custom-font text-4xl md:text-5xl lg:text-7xl font-bold drop-shadow-2xl uppercase tracking-tighter mb-2">
                                    {{ $slide->name }}
                                </h1>
                            @endif

                            @if($showDesc == '1' && $slide->description)
                                <p class="custom-font text-base md:text-lg lg:text-xl font-medium drop-shadow-2xl uppercase tracking-tighter mb-2" 
                                   style="opacity: 0.9;">
                                    {{ $slide->description }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="swiper-slide flex items-center justify-center bg-gray-900 text-white">
                    <p class="text-2xl font-bold uppercase tracking-widest text-red-500">No Active Photos Found</p>
                </div>
            @endforelse
        </div>
        <div class="swiper-pagination"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    let currentUpdateTimestamp = "{{ $masterTimestamp }}";

    async function fetchLatestData() {
        try {
            const response = await fetch('/api/get-latest-settings?t=' + Date.now()); 
            const data = await response.json();

            if (data.last_update && data.last_update > currentUpdateTimestamp) {
                const overlay = document.getElementById('loading-overlay');
                overlay.classList.replace('hidden-overlay', 'visible-overlay');
                setTimeout(() => { window.location.reload(); }, 1500);
            }
        } catch (e) { console.log("Sync check failed."); }
    }

    setInterval(fetchLatestData, 10000);

    const slideDuration = {{ $seconds }} * 1000; 
    const effectSetting = "{{ $effect }}";

    let swiperOptions = {
        loop: true,
        speed: 1200,
        autoplay: { delay: slideDuration, disableOnInteraction: false },
        pagination: { el: ".swiper-pagination", clickable: true },
        autoHeight: false, 
    };

    if (effectSetting === 'fade') {
        swiperOptions.effect = 'fade';
        swiperOptions.fadeEffect = { crossFade: true };
    } 
    else if (effectSetting === 'slide-up') {
        swiperOptions.direction = 'vertical';
    } 
    else if (effectSetting === 'slide-down') {
        swiperOptions.direction = 'vertical';
        swiperOptions.effect = 'creative';
        swiperOptions.creativeEffect = {
            prev: { translate: [0, '100%', 0] },
            next: { translate: [0, '-100%', 0] },
        };
    }
    else if (effectSetting === 'slide-right') {
        swiperOptions.effect = 'creative';
        swiperOptions.creativeEffect = {
            prev: { translate: ['100%', 0, 0] },
            next: { translate: ['-100%', 0, 0] },
        };
    }

    new Swiper(".mySwiper", swiperOptions);
    </script>
</body>
</html>