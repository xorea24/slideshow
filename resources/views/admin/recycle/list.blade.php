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
    .album-card { transition: all 0.3s ease; }
</style>
<script src="https://cdn.tailwindcss.com"></script> 
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stop

@section('content')
    
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