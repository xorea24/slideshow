
<!-- ============================================================ -->
<!-- ADMIN DASHBOARD - MAYOR'S OFFICE                            -->
<!-- Photo Management System                                 -->
<!-- ============================================================ -->
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Tags & Title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <head>
    <title img src="{{ asset('image/stc_logo5.png') }}"> Mayor's Office</title>
</head>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <!-- Tailwind CSS Framework -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js for Reactive Components -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>

<!-- ============================================================ -->
<!-- MAIN BODY - Dashboard Container                             -->
<!-- ============================================================ -->
<body class="bg-gray-50 font-sans"
    x-data="{
        tab: '{{ session('last_tab', 'manage') }}',
        tab: '{{ session('last_tab') ?? (session('status') ? 'manage' : 'upload') }}',
        sidebarOpen: false,
        search: '',
        page: 1,
        perPage: 1,
        isNewAlbum: false,

        // --- NEW DATA FOR MULTI-UPLOAD ---
        uploadRows: [{ id: Date.now(), name: '', desc: '', file: null, preview: null }],

        addPhotoRow() {
            this.uploadRows.push({ id: Date.now(), name: '', desc: '', file: null, preview: null });
        },

        removePhotoRow(index) {
            this.uploadRows.splice(index, 1);
        },

        handleFileChange(event, index) {
            const file = event.target.files[0];
            if (file) {
                this.uploadRows[index].file = file;
                this.uploadRows[index].preview = URL.createObjectURL(file);
            }
        },
        // --------------------------------

        // --- SETTINGS DATA ---
        albumSearch: '',
        availableAlbums: [
            @foreach($albums as $album)
                { id: {{ $album->id }}, name: '{{ addslashes($album->name) }}' },
            @endforeach
        ],
        activeAlbums: [],
        displayAlbumIds: '',

        init() {
            let rawSavedValue = '{{ $settings['display_album_ids'] ?? '' }}';
            let savedIds = rawSavedValue
                ? rawSavedValue.split(',').map(id => id.trim()).filter(id => id !== '')
                : [];
            this.activeAlbums = this.availableAlbums.filter(a => savedIds.includes(a.id.toString()));
            this.availableAlbums = this.availableAlbums.filter(a => !savedIds.includes(a.id.toString()));
            this.displayAlbumIds = this.activeAlbums.map(a => a.id).join(',');
        },

        moveToActive(album) {
            this.activeAlbums.push(album);
            this.availableAlbums = this.availableAlbums.filter(a => a.id !== album.id);
            this.displayAlbumIds = this.activeAlbums.map(a => a.id).join(',');
        },

        moveToAvailable(album) {
            this.availableAlbums.push(album);
            this.activeAlbums = this.activeAlbums.filter(a => a.id !== album.id);
            this.displayAlbumIds = this.activeAlbums.map(a => a.id).join(',');
        },
        // --------------------

        get totalVisible() { return document.querySelectorAll('.album-card').length; },
        get totalPages() { return Math.ceil(this.totalVisible / this.perPage) || 1; },
        trashSearch: ''
    }"
    x-init="init()">
    <div class="flex min-h-screen">
        <!-- ============================================================ -->
        <!-- MOBILE MENU TOGGLE BUTTON                                    -->
        <!-- Hidden on desktop, shown on mobile devices                    -->
        <!-- ============================================================ -->
        <div class="md:hidden fixed top-4 left-4 z-50">
            <button @click="sidebarOpen = !sidebarOpen" class="bg-red-950 text-white p-2 rounded-lg shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

     <!-- ============================================================ -->
     <!-- SIDEBAR NAVIGATION                                           -->
     <!-- Responsive sidebar with navigation menu & logout button     -->
     <!-- ============================================================ -->
    <aside class="w-64 bg-blue-700 text-white fixed md:static inset-y-0 left-0 z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col shadow-2xl" 
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    
   <div class="p-6 flex items-center gap-4">   
    <img src="{{ asset('image/stc_logo5.png') }}" alt="Logo" class="w-16 h-16">
    
    <div>
        <h1 class="text-2xl font-bold tracking-wider uppercase leading-none">
            Mayor's <span class="text-blue-200">Office</span>
        </h1>
        <p class="text-[10px] text-blue-100 tracking-[0.2em] mt-1">
            UPLOADING SYSTEM
        </p>
    </div>
</div>

    <nav class="flex-1 px-4 space-y-2 mt-4">
        <a href="#" @click.prevent="tab = 'manage'; sidebarOpen = false" 
            :class="tab === 'manage' ? 'bg-white text-blue-700 shadow-lg' : 'text-blue-50 hover:bg-blue-600'"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Albums
    </a>

        <a href="#" @click.prevent="tab = 'trash'; sidebarOpen = false" 
            :class="tab === 'trash' ? 'bg-white text-blue-700 shadow-lg' : 'text-blue-50 hover:bg-blue-600'"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Recycle Bin
            <span class="ml-auto bg-blue-900 Ilipat sa Recycle Bin?text-white text-[10px] px-2 py-0.5 rounded-full font-bold">
                {{ \App\Models\Photo::onlyTrashed()->count() }}
            </span>
        </a>

        <a href="#" @click.prevent="tab = 'settings'; sidebarOpen = false" 
            :class="tab === 'settings' ? 'bg-white text-blue-700 shadow-lg' : 'text-blue-50 hover:bg-blue-600'"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition font-medium text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
        </a>
    </nav>

    <div class="p-4 border-t border-blue-800/50">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full flex items-center gap-3 px-4 py-3 text-blue-100 hover:bg-blue-800 hover:text-white rounded-xl transition text-sm font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Sign Out
            </button>
        </form>
    </div>
</aside>

       <!-- ============================================================ -->
       <!-- MAIN CONTENT AREA                                            -->
       <!-- Displays different tab content based on user selection       -->
       <!-- ============================================================ -->
       <main class="flex-1 overflow-y-auto h-screen bg-gray-50 custom-scrollbar">
            <header class="bg-white border-b border-gray-100 px-8 py-4 flex justify-between items-center sticky top-0 z-10 shadow-sm">
                <!-- Page Title -->
                <h2 class="text-xl font-bold text-gray-800" 
                    x-text="tab === 'upload' ? 'Upload New Content' : (tab === 'manage' ? 'Albums Management' : (tab === 'trash' ? 'Recycle Bin' : 'System Settings'))">
                </h2>

                <!-- User Info & Avatar -->
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                    </div>
                    
                </div>
            </header>

            <div class="p-4"> 
                @if (session('status'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-2 flex items-center justify-between bg-green-50 border-l-2 border-green-500 p-2 rounded shadow-sm max-w-xs ml-auto">
                    <span class="text-green-700 text-xs font-semibold">{{ session('status') }}</span> 
                    <button @click="show = false" class="text-green-500 hover:text-green-700"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                @endif
            </div>

            <!-- ============================================================ -->
            <!-- UPLOAD TAB CONTENT                                           -->
            <!-- File upload form with drag & drop support                    -->
            <!-- ============================================================ -->
        <div x-show="tab === 'upload'" 
                class="fixed inset-0 z-50 overflow-y-auto" 
                x-cloak 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-data="{ 
            isNewAlbum: false,
            newAlbumName: '',
            uploadRows: [
                { id: Date.now(), preview: null, title: '' }
            ],
            existingAlbums: [
                @foreach($albums as $album)
                    '{{ strtolower(addslashes($album->name)) }}',
                @endforeach
            ],
            get isDuplicate() {
                return this.existingAlbums.includes(this.newAlbumName.trim().toLowerCase());
            },
            addPhotoRow() {
                this.uploadRows.push({ id: Date.now(), preview: null, title: '' });
            },
            removePhotoRow(index) {
                this.uploadRows.splice(index, 1);
            },
            handleFileChange(event, index) {
                const file = event.target.files[0];
                if (file) {
                    // Generate Preview
                    this.uploadRows[index].preview = URL.createObjectURL(file);
                    
                    // Default Title Logic: Kunin ang filename, tanggalin ang extension
                    let fileName = file.name.split('.').slice(0, -1).join('.');
                    
                    // I-set ang title kung wala pang nilalagay ang user
                    if (!this.uploadRows[index].title) {
                        this.uploadRows[index].title = fileName;
                    }
                }
            }
        }">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="tab = 'manage'"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="max-w-5xl w-full bg-white p-8 rounded-3xl shadow-2xl border border-gray-100 relative" @click.stop>
                
                <button @click="tab = 'manage'" class="absolute top-6 right-6 text-gray-400 hover:text-blue-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="mb-8">
                    <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Upload New Photos</h2>
                    <p class="text-sm text-gray-500">Add images to your library or create a new collection.</p>
                </div>

               <form action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf   
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-blue-50/50 p-6 rounded-2xl border border-blue-100">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-blue-900 uppercase tracking-widest">Target Album</label>
                            <select name="album_id" id="album_select" 
                                    @change="isNewAlbum = $el.value === 'new'" 
                                    class="w-full px-4 py-3 rounded-xl border border-blue-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none text-sm font-bold text-gray-700 bg-white transition-all">
                                <option value="">-- No Album (Unassigned) --</option>
                                @foreach($albums as $album)
                                    <option value="{{ $album->id }}">{{ $album->name }}</option>
                                @endforeach
                                <option value="new" class="font-bold text-blue-600">+ Create New Album</option>
                            </select>
                        </div>

                        <div x-show="isNewAlbum" x-transition.scale.origin.left class="md:col-span-2 space-y-2">
                            <label class="block text-[10px] font-black text-blue-900 uppercase tracking-widest">New Album Details</label>
                            <div class="flex flex-col md:flex-row gap-3">
                                <div class="flex-1 relative">
                                    <input type="text" 
                                        name="new_album_name" 
                                        x-model="newAlbumName"
                                        placeholder="Album Title" 
                                        :class="isDuplicate ? 'border-red-500 focus:ring-red-500/10 focus:border-red-500' : 'border-blue-200 focus:ring-blue-500/10 focus:border-blue-500'"
                                        class="w-full px-4 py-3 rounded-xl border text-sm font-bold transition-all outline-none">
                                    
                                    <template x-if="isDuplicate">
                                        <div class="absolute -bottom-5 left-0 text-[9px] text-red-600 font-black uppercase tracking-tighter">
                                            ⚠ Name already exists! Use a different name.
                                        </div>
                                    </template>
                                </div>
                                <input type="text" name="new_album_desc" placeholder="Album Subtitle/Description" class="flex-1 px-4 py-3 rounded-xl border border-blue-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none text-sm bg-white transition-all">
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div class="space-y-4 max-h-[40vh] overflow-y-auto px-2 custom-scrollbar">
                        <template x-for="(row, index) in uploadRows" :key="row.id">
                            <div class="flex flex-col md:flex-row gap-6 p-5 bg-white border border-gray-100 rounded-2xl relative group hover:border-blue-200 transition-all hover:shadow-sm">
                                
                                <div class="w-full md:w-32 h-32 bg-gray-50 rounded-xl flex-shrink-0 border-2 border-dashed border-gray-200 group-hover:border-blue-300 overflow-hidden relative transition-colors">
                                    <template x-if="row.preview">
                                        <img :src="row.preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!row.preview">
                                        <div class="flex flex-col items-center justify-center h-full text-gray-400">
                                            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span class="text-[8px] font-black uppercase">Browse</span>
                                        </div>
                                    </template>
                                    <input type="file" name="images[]" required class="absolute inset-0 opacity-0 cursor-pointer" @change="handleFileChange($event, index)">
                                </div>

                                <div class="flex-1 grid grid-cols-1 gap-3">
                                    <div>
                                        <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Image Title</label>
                                        <input type="text" 
                                            name="titles[]" 
                                            x-model="row.title"
                                            placeholder="Enter title (or leave blank for filename)" 
                                            class="w-full px-5 py-3 rounded-xl border border-gray-100 text-sm focus:border-blue-500 outline-none font-bold text-gray-700 bg-gray-50/30">
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Description (Optional)</label>
                                        <textarea name="descriptions[]" 
                                                placeholder="Write a subtitle..." 
                                                class="w-full px-5 py-3 rounded-xl border border-gray-100 text-sm focus:border-blue-500 outline-none h-16 bg-gray-50/30"></textarea>
                                    </div>
                                </div>

                                <button type="button" x-show="uploadRows.length > 1" @click="removePhotoRow(index)" class="absolute -top-2 -right-2 bg-white text-gray-400 border border-gray-100 rounded-full p-1.5 shadow-md hover:text-red-600 hover:border-red-100 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t border-gray-50">
                        <button type="button" @click="addPhotoRow()" class="flex items-center gap-2 text-blue-600 font-black text-xs uppercase tracking-widest hover:text-blue-800 transition-colors">
                            <div class="p-1.5 bg-blue-50 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            Add Another Photo
                        </button>

                       <button type="submit" 
                            :disabled="isNewAlbum && (isDuplicate || newAlbumName.trim() === '')"
                            :class="(isNewAlbum && (isDuplicate || newAlbumName.trim() === '')) ? 'bg-gray-200 cursor-not-allowed text-gray-400 shadow-none' : 'bg-blue-700 hover:bg-blue-800 text-white shadow-xl shadow-blue-200'"
                            class="px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all active:scale-95">
                        Save All Images
                    </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <!-- ============================================================ -->
        <!-- MANAGE ALBUMS TAB CONTENT                                    -->
        <!-- Display, search, edit & manage Photo albums                 -->
        <!-- ============================================================ -->
<div x-show="tab === 'manage'" x-cloak x-transition:enter="transition ease-out duration-300">
    <div class="max-w-6xl mx-auto px-4 pb-20">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4 bg-white p-5 rounded-[2rem] shadow-sm border border-gray-100">
            <div class="relative w-full md:w-96 group">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" x-model="search" placeholder="Search albums..." 
                    class="pl-12 pr-4 py-3 w-full border border-gray-100 rounded-2xl text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all bg-gray-50/50 font-bold">
            </div>

            <div class="flex items-center gap-1 bg-gray-100/50 p-1.5 rounded-2xl border border-gray-100">
                <button @click="if(page > 1) page--" :disabled="page === 1" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition disabled:opacity-30 active:scale-95 hover:bg-white hover:shadow-sm">Prev</button>
                <div class="px-4 py-2 text-[10px] font-black text-blue-900 border-x border-gray-200">Page <span x-text="page"></span> of <span x-text="totalPages"></span></div>
                <button @click="if(page < totalPages) page++" :disabled="page === totalPages" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition disabled:opacity-30 active:scale-95 hover:bg-white hover:shadow-sm text-blue-600">Next</button>
            </div>
        </div>

        @php $albumIndex = 0; @endphp 
        @forelse($albums as $album)
            @php 
                $albumIndex++; 
                $groupedSlides = $album->slides; 
            @endphp
    
            <div class="album-card bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 mb-12 transition-all"
                x-data="{ 
                    myIndex: {{ $albumIndex }},
                    albumId: {{ $album->id }},
                    localCategory: '{{ addslashes($album->name) }}',
                    localDesc: '{{ addslashes($album->description) }}',
                    showEditModal: false,
                    tempTitle: '{{ addslashes($album->name) }}',
                    tempDesc: '{{ addslashes($album->description) }}',
                    
                    photoSearch: '',
                    photoPage: 1,
                    photosPerPage: 4,
                    totalPhotos: {{ $groupedSlides->count() }},
                    
                    get totalPhotoPages() { 
                        if (this.photoSearch.trim() !== '') return 1;
                        return Math.ceil(this.totalPhotos / this.photosPerPage);
                    }
                }"
                x-show="search.trim() === '' ? (myIndex > (page - 1) * perPage && myIndex <= page * perPage) : localCategory.toLowerCase().includes(search.toLowerCase())"
                x-transition:enter="transition ease-out duration-300">

                <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-8 border-b border-gray-50 gap-6">
                    <div class="flex-1 space-y-2">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-1.5 bg-blue-600 rounded-full shadow-[0_0_15px_rgba(37,99,235,0.4)]"></div>
                            <h3 class="text-3xl font-black text-slate-800 tracking-tighter uppercase" x-text="localCategory"></h3>
                            <button @click="showEditModal = true" class="p-2 text-gray-300 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </button>
                        </div>
                        <p class="text-sm text-slate-400 font-medium italic pl-4" x-text="localDesc || 'No description provided.'"></p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="text" x-model="photoSearch" @input="photoPage = 1" placeholder="Search photos..." 
                                class="pl-9 pr-4 py-2.5 border-none rounded-xl text-xs focus:ring-4 focus:ring-blue-500/5 outline-none w-48 bg-gray-50/80 font-bold transition-all">
                            <svg class="absolute left-3 top-3 h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="3"/></svg>
                        </div>
                        
                        <button @click="tab = 'upload'; isNewAlbum = false; $nextTick(() => { document.getElementById('album_select').value = albumId })" 
                                class="p-2.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        </button>
                        
                        <form action="{{ route('albums.destroy', $album->id) }}" method="POST" onsubmit="return confirm('Delete entire album?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @forelse($album->slides as $photoIdx => $photo)
                        <div x-data="{ 
                                showPhotoModal: false,
                                photoId: {{ $photo->id }},
                                localPhotoTitle: '{{ addslashes($photo->name) }}',
                                localPhotoDesc: '{{ addslashes($photo->description) }}',
                                tempPhotoTitle: '{{ addslashes($photo->name) }}',
                                tempPhotoDesc: '{{ addslashes($photo->description) }}',
                                photoActive: {{ $photo->is_active ? 'true' : 'false' }}
                            }"
                            x-show="(function() {
                                const matches = localPhotoTitle.toLowerCase().includes(photoSearch.toLowerCase());
                                if (!matches) return false;
                                if (photoSearch.trim() !== '') return true;
                                const idx = {{ $photoIdx + 1 }};
                                return idx > (photoPage - 1) * photosPerPage && idx <= photoPage * photosPerPage;
                            })()"
                            class="relative bg-white rounded-3xl border border-gray-100 overflow-hidden shadow-sm group hover:shadow-xl transition-all duration-500">
                            
                            <div class="relative aspect-video bg-gray-100 overflow-hidden">
                                 <img src="{{ Storage::url($photo->image_path) }}" 
                                    class="w-full h-full object-cover transition-all duration-700" 
                                    :class="!photoActive ? 'grayscale opacity-40 blur-[1px]' : 'group-hover:scale-110'">
                                
                                <div class="absolute top-3 left-3">
                                    <span x-show="photoActive" class="px-2 py-1 bg-green-500 text-white text-[8px] font-black rounded-md uppercase shadow-sm">Live</span>
                                    <span x-show="!photoActive" class="px-2 py-1 bg-gray-500 text-white text-[8px] font-black rounded-md uppercase shadow-sm">Hidden</span>
                                </div>

                                <button @click="showPhotoModal = true" 
                                        class="absolute top-3 right-3 p-2 rounded-xl bg-white/90 backdrop-blur shadow-sm text-gray-500 hover:text-blue-600 transition-all opacity-0 group-hover:opacity-100 z-10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" stroke-width="2"/></svg>
                                </button>
                            </div>

                            <div class="p-4 bg-white">
                                <div class="mb-3">
                                    <h4 class="text-[11px] font-black text-slate-800 truncate uppercase tracking-tighter" x-text="localPhotoTitle || 'UNTITLED'"></h4>
                                    <p class="text-[10px] text-gray-400 font-medium italic line-clamp-1" x-text="localPhotoDesc || 'No description.'"></p>
                                </div>
                                
                                <div class="flex gap-2">
                                    <a href="{{ route('photos.toggle', $photo->id) }}" 
                                    class="flex-1 py-1.5 text-center text-[10px] font-black rounded-lg transition-all border {{ $photo->is_active ? 'bg-white border-gray-200 text-gray-500' : 'bg-blue-600 border-blue-600 text-white' }}">
                                        {{ $photo->is_active ? 'HIDE' : 'SHOW' }}
                                    </a>
                                    <form action="{{ route('photos.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('Delete this photo?')" class="flex-shrink-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 border border-gray-50 rounded-xl transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <template x-teleport="body">
                                <div x-show="showPhotoModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak x-transition>
                                    <div @click.away="showPhotoModal = false" class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl">
                                        <h3 class="text-xl font-black text-gray-900 mb-6 uppercase tracking-tight">Edit Photo Info</h3>
                                        
                                        <form action="{{ route('photos.update', $photo->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Title</label>
                                                    <input type="text" name="name" x-model="tempPhotoTitle" class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 font-bold text-slate-700 outline-none">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Description</label>
                                                    <textarea name="description" x-model="tempPhotoDesc" rows="3" class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 text-sm italic outline-none"></textarea>
                                                </div>
                                            </div>
                                            <div class="flex gap-3 mt-8">
                                                <button type="button" @click="showPhotoModal = false" class="flex-1 px-6 py-3 text-[11px] font-black text-gray-400 hover:text-gray-600 transition-colors uppercase">CANCEL</button>
                                                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white text-[11px] font-black rounded-xl hover:bg-blue-700 shadow-lg uppercase transition-all">SAVE CHANGES</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-10 text-gray-400 italic text-xs">No photos in this album.</div>
                    @endforelse
                </div>

                <div class="flex justify-center mt-12" x-show="totalPhotoPages > 1 && photoSearch.trim() === ''">
                    <div class="flex items-center gap-1 bg-white p-1.5 rounded-2xl border border-gray-100 shadow-lg">
                        <button @click="if(photoPage > 1) { photoPage--; $el.closest('.album-card').scrollIntoView({behavior: 'smooth'}); }" :disabled="photoPage === 1" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl disabled:opacity-30 text-gray-600 hover:bg-blue-50">Prev</button>
                        <div class="px-6 text-[10px] font-black text-slate-300 uppercase">Page <span class="text-blue-600" x-text="photoPage"></span> / <span x-text="totalPhotoPages"></span></div>
                        <button @click="if(photoPage < totalPhotoPages) { photoPage++; $el.closest('.album-card').scrollIntoView({behavior: 'smooth'}); }" :disabled="photoPage === totalPhotoPages" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl disabled:opacity-30 text-blue-600 hover:bg-blue-50">Next</button>
                    </div>
                </div>

                <template x-teleport="body">
                    <div x-show="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
                        <div @click.away="showEditModal = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden">
                            <div class="p-8">
                                <h3 class="text-xl font-black text-slate-800 uppercase mb-6 tracking-tight">Edit Album Info</h3>
                                
                                <form action="{{ route('albums.update', $album->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Album Title</label>
                                            <input type="text" name="name" x-model="tempTitle" class="w-full px-5 py-3 bg-gray-50 border border-gray-100 rounded-2xl outline-none font-bold text-slate-700">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Description</label>
                                            <textarea name="description" x-model="tempDesc" rows="3" class="w-full px-5 py-3 bg-gray-50 border border-gray-100 rounded-2xl outline-none text-sm italic text-slate-600"></textarea>
                                        </div>
                                    </div>
                                    <div class="mt-8 flex gap-3">
                                        <button type="button" @click="showEditModal = false" class="flex-1 py-3 text-[11px] font-black text-gray-400 uppercase hover:bg-gray-50 rounded-2xl transition-colors">Cancel</button>
                                        <button type="submit" class="flex-1 py-3 bg-blue-600 text-white text-[11px] font-black uppercase rounded-2xl shadow-lg hover:bg-blue-700 active:scale-95 transition-all">Save Changes</button>
                                    </div>
                                </form>
                            </div>  
                        </div>
                    </div>
                </template> 
            </div>
        @empty
            <div class="text-center py-32 bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                <p class="text-slate-400 font-black uppercase tracking-widest text-sm">No albums created yet.</p>
            </div>
        @endforelse
    </div>
</div>
             <!-- ============================================================ -->
             <!-- RECYCLE BIN / TRASH TAB CONTENT                              -->
             <!-- Manage deleted items, restore or permanently delete         -->
             <!-- ============================================================ -->
             <div x-show="tab === 'trash'" x-cloak x-transition:enter="transition ease-out duration-300">
    <div class="max-w-6xl mx-auto space-y-8">
        
        <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 flex items-center gap-4">
            <div class="bg-blue-600 p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-blue-900 font-bold">Recycle Bin Storage</p>
                <p class="text-xs text-blue-700">Deleted items are grouped by their original album. Restoring them will put them back into active rotation.</p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 group-focus-within:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" x-model="trashSearch" placeholder="Search deleted albums..." 
                    class="pl-10 pr-4 py-3 border border-gray-200 rounded-2xl text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 outline-none transition-all w-full md:w-96 shadow-sm bg-white font-medium">
            </div>
            
            <div class="flex items-center gap-3">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest" x-show="trashSearch.length > 0">
                    Results for: <span x-text="trashSearch" class="text-blue-600"></span>
                </p>
            </div>
        </div>

        @forelse(\App\Models\Photo::onlyTrashed()->with(['album' => fn($q) => $q->withTrashed()])->get()->groupBy('album_id') as $albumId => $trashedSlides)
            @php 
                $album = \App\Models\Album::withTrashed()->find($albumId); 
            @endphp
            
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden transition-all hover:shadow-md mb-6"
                 x-show="trashSearch === '' || '{{ $album ? strtolower($album->name) : 'deleted album' }}'.includes(trashSearch.toLowerCase())">
                
                <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-700 uppercase tracking-wide">
                                {{ $album ? $album->name : 'Deleted Album' }}
                            </h3>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">{{ $trashedSlides->count() }} Archived Items</p>
                        </div>
                    </div>
            
                    <div class="flex items-center gap-2">
                        <form action="{{ route('photos.restore-album') }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="album_id" value="{{ $albumId }}">
                            <button type="submit" class="text-[10px] bg-blue-600 text-white px-4 py-2 rounded-xl font-black uppercase hover:bg-blue-700 transition shadow-lg shadow-blue-100 active:scale-95 flex items-center gap-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="3"></path>
                                </svg>
                                Restore Album Items
                            </button>
                        </form>

                        <form action="{{ route('Photo.delete-album', $albumId) }}" method="POST"
                              onsubmit="return confirm('WARNING: This will permanently delete all trashed photos in this group. Continue?')">
                            @csrf @method('DELETE')
                            <button class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all active:scale-90">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($trashedSlides as $trash)
                        <div class="relative bg-gray-50 rounded-2xl border border-gray-100 overflow-hidden group transition-all hover:border-blue-300">
                            <div class="relative aspect-square overflow-hidden bg-gray-200">
                              <img src="{{ Storage::url($photo->image_path) }}" 
                                    class="w-full h-full object-cover transition-all duration-700" 
                                    :class="!photoActive ? 'grayscale opacity-40 blur-[1px]' : 'group-hover:scale-110'">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </div>

                            <div class="absolute inset-x-0 bottom-0 p-2 flex gap-1 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300 bg-white/90 backdrop-blur-sm">
                                <form action="{{ route('photos.restore', $trash->id) }}" method="POST" class="flex-1">
                                    @csrf @method('PATCH')
                                    <button class="w-full py-2 bg-blue-600 text-white text-[9px] font-black rounded-lg hover:bg-blue-700 transition shadow-sm active:scale-95">
                                        RESTORE
                                    </button>
                                </form>
                                
                                <form action="{{ route('photos.forceDelete', $trash->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Permanently delete this photo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition active:scale-90">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            <div class="absolute top-2 left-2">
                                <span class="bg-black/50 backdrop-blur-md text-white text-[8px] font-black px-2 py-1 rounded-lg uppercase tracking-tighter">Trashed</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-24 bg-white rounded-3xl border-2 border-dashed border-gray-100">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-gray-500 font-black uppercase tracking-widest text-sm">Recycle Bin is Empty</h3>
                <p class="text-gray-400 text-xs mt-2 font-medium">No items found in the trash. Your gallery is clean!</p>
            </div>
        @endforelse
    </div>
</div>
                <!-- ============================================================ -->
                <!-- SETTINGS TAB CONTENT                                        -->
                <!-- Configure Photo duration & transition effects           -->
                <!-- ============================================================ -->
<div x-show="tab === 'settings'" x-cloak x-transition:enter="transition ease-out duration-300"
    x-data="{
        albumSearch: '',
        allAlbums: [
            @foreach($albums as $album)
                { id: {{ $album->id }}, name: '{{ addslashes($album->name) }}' },
            @endforeach
        ],
        activeAlbums: [],
        displayAlbumIds: '',

        init() {
            // 1. Load initial data with correct sequence from DB
            let rawSavedValue = '{{ $settings['display_album_ids'] ?? '' }}';
            let savedIds = rawSavedValue ? rawSavedValue.split(',').map(id => id.trim()) : [];
            
            this.activeAlbums = savedIds.map(id => {
                return this.allAlbums.find(a => a.id.toString() === id);
            }).filter(Boolean);

            // 2. Initialize SortableJS for the Slideshow Queue
            this.$nextTick(() => {
                new Sortable(this.$refs.sortableList, {
                    animation: 200,
                    ghostClass: 'bg-blue-50',
                    chosenClass: 'border-blue-400',
                    dragClass: 'opacity-50',
                    handle: '.drag-handle',
                    onEnd: (evt) => {
                        let list = [...this.activeAlbums];
                        const movedItem = list.splice(evt.oldIndex, 1)[0];
                        list.splice(evt.newIndex, 0, movedItem);
                        
                        // Force Alpine to re-render to keep DOM sync
                        this.activeAlbums = [];
                        this.$nextTick(() => {
                            this.activeAlbums = list;
                            this.updateIds();
                        });
                    }
                });
            });

            this.updateIds();
        },

        updateIds() {
            this.displayAlbumIds = this.activeAlbums.map(a => a.id).join(',');
        },

        toggleAlbum(album) {
            const index = this.activeAlbums.findIndex(a => a.id === album.id);
            if (index === -1) {
                this.activeAlbums.push(album);
            } else {
                this.activeAlbums.splice(index, 1);
            }
            this.updateIds();
        },

        isSelected(id) {
            return this.activeAlbums.some(a => a.id === id);
        }
    }">

    <div class="max-w-5xl mx-auto py-8">
        <div class="mb-8">
            <h2 class="text-2xl font-black text-gray-900 tracking-tight">System Configuration</h2>
            <p class="text-sm text-gray-500">Customize your slideshow behavior and album priorities.</p>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="bg-white p-8 rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-100 space-y-10">
            @csrf
            
            @php
                $currentDuration = $settings['slide_duration'] ?? 5;
                $currentEffect = $settings['transition_effect'] ?? 'fade';
            @endphp

            <input type="hidden" name="display_album_ids" :value="displayAlbumIds">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-4">
                    <label class="flex items-center gap-2 text-xs font-black text-gray-400 uppercase tracking-widest">
                        <span class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round"></path></svg>
                        </span>
                        Slide Duration
                    </label>
                    <div class="relative">
                        <input type="number" name="slide_duration" value="{{ $currentDuration }}" min="1" max="60"
                            class="w-full pl-5 pr-14 py-4 rounded-2xl border-2 border-gray-50 bg-gray-50/50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all text-lg font-bold text-gray-800">
                        <span class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-[10px] tracking-widest">SEC</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="flex items-center gap-2 text-xs font-black text-gray-400 uppercase tracking-widest">
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z" stroke-width="2"></path></svg>
                        </span>
                        Transition Effect
                    </label>
                    <div class="relative">
                        <select name="transition_effect" class="w-full px-5 py-4 rounded-2xl border-2 border-gray-50 bg-gray-50/50 focus:bg-white focus:border-blue-500 outline-none transition-all font-bold text-gray-700 cursor-pointer appearance-none">
                            <option value="fade" {{ $currentEffect == 'fade' ? 'selected' : '' }}>Smooth Fade</option>
                            <option value="slide-up" {{ $currentEffect == 'slide-up' ? 'selected' : '' }}>Slide Upward</option>
                            <option value="slide-down" {{ $currentEffect == 'slide-down' ? 'selected' : '' }}>Slide Downward</option>
                            <option value="slide-left" {{ $currentEffect == 'slide-left' ? 'selected' : '' }}>Slide Leftward</option>
                            <option value="slide-right" {{ $currentEffect == 'slide-right' ? 'selected' : '' }}>Slide Rightward</option>
                        </select>
                        <div class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-gray-50">

            <div class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Active Albums</h3>
                        <p class="text-xs text-gray-400">Click to select, drag to reorder.</p>
                    </div>
                    <div class="relative">
                        <input type="text" x-model="albumSearch" placeholder="Search albums..." 
                               class="w-full md:w-64 pl-10 pr-4 py-2.5 bg-gray-50 border-transparent rounded-xl text-sm focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all">
                        <svg class="h-4 w-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center px-1">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Available Library</span>
                            <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-bold" x-text="allAlbums.length"></span>
                        </div>
                        <div class="bg-gray-50/50 rounded-3xl p-4 border border-gray-100 h-[400px] overflow-y-auto space-y-2 custom-scrollbar">
                            <template x-for="album in allAlbums.filter(a => a.name.toLowerCase().includes(albumSearch.toLowerCase()))" :key="album.id">
                                <div @click="toggleAlbum(album)" 
                                     class="group flex items-center justify-between p-4 rounded-2xl cursor-pointer transition-all border-2 bg-white active:scale-95"
                                     :class="isSelected(album.id) ? 'border-blue-500 shadow-md ring-4 ring-blue-500/5' : 'border-transparent hover:border-gray-200 shadow-sm'">
                                    
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all shadow-sm"
                                             :class="isSelected(album.id) ? 'bg-blue-600 text-white rotate-6' : 'bg-gray-50 text-gray-400 group-hover:bg-gray-100'">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black" :class="isSelected(album.id) ? 'text-blue-900' : 'text-gray-700'" x-text="album.name"></span>
                                            <span x-show="isSelected(album.id)" class="text-[9px] text-blue-500 font-black uppercase tracking-widest mt-0.5">Selected</span>
                                        </div>
                                    </div>
                                    
                                    <div class="transition-all" :class="isSelected(album.id) ? 'rotate-90 scale-110' : 'text-gray-300'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center px-1">
                            <span class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em]">Slideshow Queue</span>
                            <span class="text-[10px] bg-blue-600 text-white px-2 py-0.5 rounded-full font-bold" x-text="activeAlbums.length"></span>
                        </div>
                        <div x-ref="sortableList" class="bg-blue-50/30 rounded-3xl p-4 border-2 border-dashed border-blue-100 h-[400px] overflow-y-auto space-y-2 custom-scrollbar">
                            <template x-for="(album, index) in activeAlbums" :key="album.id">
                                <div class="drag-handle flex items-center justify-between p-4 bg-white border border-blue-100 rounded-2xl shadow-sm group cursor-grab active:cursor-grabbing hover:border-blue-300 transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="flex flex-col items-center justify-center text-blue-200 group-hover:text-blue-400">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 7h2v2H7V7zm0 4h2v2H7v-2zm4-4h2v2h-2V7zm0 4h2v2h-2v-2zM7 15h2v2H7v-2zm4 0h2v2h-2v-2z"/></svg>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-50 text-[10px] font-black text-blue-600" x-text="index + 1"></span>
                                            <span class="text-sm font-black text-gray-800" x-text="album.name"></span>
                                        </div>
                                    </div>
                                    <button @click="toggleAlbum(album)" type="button" class="p-2 rounded-xl hover:bg-red-50 text-gray-300 hover:text-red-500 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round"></path></svg>
                                    </button>
                                </div>
                            </template>
                            
                            <template x-if="activeAlbums.length === 0">
                                <div class="h-full flex flex-col items-center justify-center text-center opacity-40">
                                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke-width="2"></path></svg>
                                    </div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-500">Queue is Empty</p>
                                    <p class="text-[10px] text-gray-400 mt-1">Select albums from the library to begin.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t border-gray-50 flex flex-col md:flex-row items-center justify-between gap-6">
                <a href="/" target="_blank" class="group flex items-center gap-3 text-[10px] font-black text-gray-400 hover:text-blue-600 transition-all tracking-[0.2em]">
                    <span class="p-3 bg-gray-50 group-hover:bg-blue-50 rounded-2xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"></path></svg>
                    </span>
                    LIVE PREVIEW
                </a>
                
                <button type="submit" class="w-full md:w-auto bg-blue-700 text-white px-10 py-5 rounded-[1.5rem] font-black tracking-[0.15em] text-xs hover:bg-blue-800 transition-all shadow-xl shadow-blue-200 active:scale-95 flex items-center justify-center gap-3">
                    APPLY CHANGES
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>

</body>
</html>

<!-- ============================================================ -->
<!-- END OF DASHBOARD                                             -->
<!-- ============================================================ -->W
