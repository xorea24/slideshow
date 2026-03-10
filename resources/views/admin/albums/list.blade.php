@extends('adminlte::page')

@section('title', $title ?? 'Manage Gallery')

@section('css')
<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .z-modal { z-index: 9999; }
    .btn-upload-shadow { box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4); }
    .album-card { transition: all 0.3s ease; scroll-margin-top: 100px; }
</style>
<script src="https://cdn.tailwindcss.com"></script> 
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stop

@section('content')
<div x-data="galleryManager()" x-init="init()" class="p-4 bg-[#f8fafc] min-h-screen">
    
    {{-- Header & Sign Out --}}
    <div class="flex justify-between items-center mb-8 px-4">
        <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Gallery Manager</h1>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-red-500 hover:bg-red-50 rounded-xl transition text-xs font-bold uppercase tracking-widest">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Sign Out
            </button>
        </form>
    </div>

    <div x-show="tab === 'manage'" x-cloak x-transition:enter="transition ease-out duration-300">
        <div class="max-w-6xl mx-auto px-4 pb-20">
            
            {{-- Top Toolbar --}}
            <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4 bg-white p-5 rounded-[2rem] shadow-sm border border-gray-100">
                <div class="relative w-full md:w-96 group">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-blue-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" x-model="search" placeholder="Search albums..." 
                        @input="page = 1"
                        class="pl-12 pr-4 py-3 w-full border border-gray-100 rounded-2xl text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all bg-gray-50/50 font-bold">
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1 bg-gray-100/50 p-1.5 rounded-2xl border border-gray-100">
                        <button @click="prevPage()" :disabled="page === 1" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition disabled:opacity-30 active:scale-95 hover:bg-white hover:shadow-sm">Prev</button>
                        <div class="px-4 py-2 text-[10px] font-black text-blue-900 border-x border-gray-200">Page <span x-text="page"></span> of <span x-text="totalPages"></span></div>
                        <button @click="nextPage()" :disabled="page === totalPages" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition disabled:opacity-30 active:scale-95 hover:bg-white hover:shadow-sm text-blue-600">Next</button>
                    </div>
                </div>
            </div>

            @php $albumIndex = 0; @endphp 
            @forelse($albums as $album)
                @php 
                    $albumIndex++; 
                    $groupedSlides = $album->photos; 
                @endphp
        
                <div :id="'album-' + {{ $album->id }}" class="album-card bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 mb-12 transition-all"
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

                    {{-- Album Header --}}
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
                            
                            <button @click="openUploadModal(albumId)" 
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
                    
                    {{-- Photos Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @forelse($album->photos as $photoIdx => $photo)
                            <div :id="'photo-' + {{ $photo->id }}"
                                x-data="{ 
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
                                        class="absolute top-3 right-3 p-2 rounded-xl bg-white/90 backdrop-blur shadow-md text-blue-600 hover:bg-blue-600 hover:text-white transition-all z-20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                </div>

                                <div class="p-4 bg-white">
                                    <div class="mb-3">
                                        <h4 class="text-[11px] font-black text-slate-800 truncate uppercase tracking-tighter" x-text="localPhotoTitle || 'UNTITLED'"></h4>
                                        <p class="text-[10px] text-gray-400 font-medium italic line-clamp-1" x-text="localPhotoDesc || 'No description.'"></p>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <form @submit.prevent="submitForm($event, {{ $album->id }}, {{ $photo->id }})" action="{{ route('photos.toggle', $photo->id) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" 
                                            class="w-full py-2.5 text-center text-[10px] font-black rounded-lg transition-all border"
                                            :class="photoActive ? 'bg-white border-gray-200 text-gray-500' : 'bg-blue-600 border-blue-600 text-white'">
                                                <span x-text="photoActive ? 'HIDE' : 'SHOW'"></span>
                                            </button>
                                        </form>

                                        <form action="{{ route('photos.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('Delete this photo?')" class="flex-shrink-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 border border-gray-50 rounded-xl transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Photo Edit Modal (Teleported) --}}
                                <template x-teleport="body">
                                    <div x-show="showPhotoModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak x-transition>
                                        <div @click.away="showPhotoModal = false" class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl">
                                            <h3 class="text-xl font-black text-gray-900 mb-6 uppercase tracking-tight">Edit Photo Info</h3>
                                            <form @submit.prevent="submitForm($event, {{ $album->id }}, {{ $photo->id }})" action="{{ route('photos.update', $photo->id) }}" method="POST">
                                                @csrf @method('PATCH')
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
                                                    <button type="submit" class="flex-1 px-6 py-3 bg-slate-900 text-white text-[11px] font-black rounded-xl hover:bg-black shadow-lg uppercase transition-all">SAVE CHANGES</button>
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

                    {{-- Album Photo Pagination --}}
                    <div class="flex justify-center mt-12" x-show="totalPhotoPages > 1 && photoSearch.trim() === ''">
                        <div class="flex items-center gap-1 bg-white p-1.5 rounded-2xl border border-gray-100 shadow-lg">
                            <button @click="if(photoPage > 1) { photoPage--; $el.closest('.album-card').scrollIntoView({behavior: 'smooth'}); }" :disabled="photoPage === 1" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl disabled:opacity-30 text-gray-600 hover:bg-blue-50">Prev</button>
                            <div class="px-6 text-[10px] font-black text-slate-300 uppercase">Page <span class="text-blue-600" x-text="photoPage"></span> / <span x-text="totalPhotoPages"></span></div>
                            <button @click="if(photoPage < totalPhotoPages) { photoPage++; $el.closest('.album-card').scrollIntoView({behavior: 'smooth'}); }" :disabled="photoPage === totalPhotoPages" class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl disabled:opacity-30 text-blue-600 hover:bg-blue-50">Next</button>
                        </div>
                    </div>

                    {{-- Album Edit Modal (Teleported) --}}
                    <template x-teleport="body">
                        <div x-show="showEditModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition>
                            <div @click.away="showEditModal = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden">
                                <div class="p-8">
                                    <h3 class="text-xl font-black text-slate-800 uppercase mb-6 tracking-tight">Edit Album Info</h3>
                                    <form @submit.prevent="submitForm($event, {{ $album->id }})" action="{{ route('albums.update', $album->id) }}" method="POST">
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
                                            <button type="submit" class="flex-1 py-3 bg-slate-900 text-white text-[11px] font-black uppercase rounded-2xl shadow-lg hover:bg-black active:scale-95 transition-all">Save Changes</button>
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

    {{-- GLOBAL UPLOAD MODAL --}}
    <div x-show="showModal" class="fixed inset-0 z-modal overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="max-w-5xl w-full bg-white p-10 rounded-[40px] shadow-2xl relative" @click.stop>
                <button @click="closeModal()" class="absolute top-8 right-8 text-gray-300 hover:text-gray-500">
                    <i class="fas fa-times text-2xl"></i>
                </button>
                <div class="mb-10">
                    <h2 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Upload Photos</h2>
                </div>
                <form @submit.prevent="submitForm($event)" action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-[#f8fbff] p-8 rounded-[32px] border border-[#eef4ff] mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-blue-900 uppercase tracking-widest ml-1">Target Album</label>
                                <select name="album_id" x-model="selectedAlbum" @change="isNewAlbum = (selectedAlbum === 'new')" 
                                        class="w-full px-5 py-4 rounded-2xl border border-blue-100 text-sm font-bold text-gray-600 bg-white outline-none">
                                    <option value="">-- No Album (Unassigned) --</option>
                                    @foreach($albums as $album)
                                        <option value="{{ $album->id }}">{{ $album->name }}</option>
                                    @endforeach
                                    <option value="new" class="text-blue-600 font-bold">+ Create New Album</option>
                                </select>
                            </div>
                            <template x-if="isNewAlbum">
                                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" name="new_album_name" placeholder="New Album Title" required class="w-full px-5 py-4 rounded-2xl border border-blue-100 text-sm font-bold outline-none">
                                    <input type="text" name="new_album_desc" placeholder="New Album Description" class="w-full px-5 py-4 rounded-2xl border border-blue-100 text-sm font-bold outline-none">
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-4 max-h-[40vh] overflow-y-auto px-1 mb-8 custom-scrollbar">
                        <template x-for="(row, index) in uploadRows" :key="row.id">
                            <div class="group border border-gray-100 rounded-[32px] p-6 flex flex-col md:flex-row gap-6 relative bg-white hover:border-blue-100 transition-all">
                                <div class="w-32 h-32 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 flex-shrink-0 relative flex flex-col items-center justify-center overflow-hidden">
                                    <template x-if="row.preview">
                                        <img :src="row.preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!row.preview">
                                        <i class="far fa-image text-gray-300 text-xl"></i>
                                    </template>
                                    <input type="file" name="images[]" required class="absolute inset-0 opacity-0 cursor-pointer" @change="handleFileChange($event, index)">
                                </div>
                                <div class="flex-1 space-y-4">
                                    <input type="text" name="name[]" x-model="row.title" placeholder="Image Title" class="w-full px-6 py-4 rounded-2xl bg-[#f9fafb] border-none text-sm font-bold outline-none">
                                    <input type="text" name="descriptions[]" placeholder="Description (Optional)" class="w-full px-6 py-4 rounded-2xl bg-[#f9fafb] border-none text-sm font-medium outline-none">
                                </div>
                                <button type="button" @click="removePhotoRow(index)" x-show="uploadRows.length > 1" class="absolute -top-2 -right-2 bg-white shadow-md rounded-full w-8 h-8 text-red-400">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-between items-center">
                        <button type="button" @click="addPhotoRow()" class="flex items-center gap-2 text-blue-600 font-black text-[11px] uppercase tracking-widest">
                            <i class="fas fa-plus-circle"></i> Add Another Photo
                        </button>
                        <button type="submit" :disabled="isLoading" class="bg-slate-900 hover:bg-black text-white px-10 py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest btn-upload-shadow transition-all disabled:opacity-50">
                            <span x-show="!isLoading">Save All Images</span>
                            <span x-show="isLoading"><i class="fas fa-spinner fa-spin"></i> Uploading...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
function galleryManager() {
    return {
        tab: 'manage',
        search: '',
        page: 1,
        perPage: 3,
        totalAlbums: {{ $albums->count() }},
        showModal: false,
        isLoading: false,
        selectedAlbum: '',
        isNewAlbum: false,
        uploadRows: [],

        init() { 
            // Load page from URL
            const params = new URLSearchParams(window.location.search);
            this.page = parseInt(params.get('p')) || 1;
            this.resetUploadRows(); 

            // Handle anchor scroll after reload
            if (window.location.hash) {
                setTimeout(() => {
                    const el = document.querySelector(window.location.hash);
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                }, 500);
            }
        },

        get totalPages() { 
            return this.search.trim() !== '' ? 1 : Math.ceil(this.totalAlbums / this.perPage); 
        },

        updateURL() {
            const url = new URL(window.location);
            url.searchParams.set('p', this.page);
            window.history.replaceState({}, '', url);
        },

        nextPage() {
            if(this.page < this.totalPages) {
                this.page++;
                this.updateURL();
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        },

        prevPage() {
            if(this.page > 1) {
                this.page--;
                this.updateURL();
                window.scrollTo({top: 0, behavior: 'smooth'});
            }
        },

        openUploadModal(albumId = '') {
            this.selectedAlbum = albumId;
            this.isNewAlbum = (albumId === 'new');
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.showModal = false;
            this.resetUploadRows();
            document.body.style.overflow = 'auto';
        },

        resetUploadRows() { this.uploadRows = [{ id: Date.now(), title: '', preview: null }]; },
        addPhotoRow() { this.uploadRows.push({ id: Date.now(), title: '', preview: null }); },
        removePhotoRow(index) { this.uploadRows.splice(index, 1); },
        
        handleFileChange(event, index) {
            const file = event.target.files[0];
            if (file) {
                this.uploadRows[index].preview = URL.createObjectURL(file);
                if (!this.uploadRows[index].title) {
                    this.uploadRows[index].title = file.name.split('.').slice(0, -1).join('.');
                }
            }
        },

        async submitForm(e, albumId = null, photoId = null) {
            this.isLoading = true;
            const form = e.target;
            const formData = new FormData(form);
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });

                if (response.ok) {
                    // Build redirect URL with current page and anchor
                    const url = new URL(window.location.origin + window.location.pathname);
                    url.searchParams.set('p', this.page);
                    
                    let anchor = '';
                    if (photoId) anchor = `#photo-${photoId}`;
                    else if (albumId) anchor = `#album-${albumId}`;
                    
                    window.location.href = url.toString() + anchor;
                    window.location.reload(); // Force reload to show changes
                } else {
                    const error = await response.json();
                    alert("Error: " + (error.message || "Action failed"));
                    this.isLoading = false;
                }
            } catch (err) {
                console.error(err);
                alert("Network error occurred.");
                this.isLoading = false;
            }
        }
    }   
}
</script>
@endsection