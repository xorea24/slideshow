@php
    // 1. FETCH SETTINGS & DATA
    $settingsRaw = \DB::table('settings')->get()->keyBy('key');
    
    $seconds = $settingsRaw->get('slide_duration')->value ?? 5;
    $effect = $settingsRaw->get('transition_effect')->value ?? 'fade';
    
    // FETCH THE NEW SEPARATED KEYS
    $showName = $settingsRaw->get('show_photo_name')->value ?? '1';
    $showDesc = $settingsRaw->get('show_photo_description')->value ?? '1';
    $overlayPos = $settingsRaw->get('overlay_position')->value ?? 'bottom-left';
    
    $displayAlbumIds = $settingsRaw->get('display_album_ids')->value ?? '';
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
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Access - Mayor's Office</title>
    
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

                    {{-- Separate Logic for Name and Description --}}
                    @if($showName == '1' || ($showDesc == '1' && $slide->description))
                        <div class="title-overlay animate-text pos-{{ $overlayPos }}">
                            
                            @if($showName == '1')
                                <h1 class="text-white text-7xl font-black drop-shadow-2xl uppercase tracking-tighter mb-2">
                                    {{ $slide->name }}
                                </h1>
                            @endif

                            @if($showDesc == '1' && $slide->description)
                                <p class="text-white text-xl font-black drop-shadow-2xl uppercase tracking-tighter mb-2">
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

    // Apply Transition Effects
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