@extends('adminlte::page')

@section('content')
{{-- Alpine JS Data Wrapper --}}
<div x-data="{ 
    tab: '{{ session('last_tab') ?? (session('status') ? 'manage' : 'upload') }}',
    search: '', 
    page: 1, 
    perPage: 4,
    totalAlbums: {{ $albums->count() }},
    isNewAlbum: false, {{-- FIXED: Added missing state --}}
    selectedAlbum: '', {{-- FIXED: Added missing state --}}
    newAlbumName: '', {{-- FIXED: Added missing state --}}

    get totalPages() { 
        return Math.ceil(this.totalAlbums / this.perPage) || 1; 
    },
    
    // Upload logic
    uploadRows: [{ id: Date.now(), preview: null, title: '' }],
    addPhotoRow() { this.uploadRows.push({ id: Date.now(), preview: null, title: '' }); },
    handleFileChange(event, index) {
        const file = event.target.files[0];
        if (file) {
            this.uploadRows[index].preview = URL.createObjectURL(file);
            if (!this.uploadRows[index].title) this.uploadRows[index].title = file.name.split('.')[0];
        }
    }
}" class="container-fluid py-4 bg-light min-vh-100">

    {{-- TOP SEARCH AND NAVIGATION --}}
    <div class="row mb-5 align-items-center bg-white mx-1 py-3 px-4 rounded-pill shadow-sm" x-show="tab === 'manage'">
        <div class="col-md-5">
            <div class="input-group bg-light rounded-pill px-3 py-1">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input type="text" x-model="search" class="form-control bg-transparent border-0" placeholder="Search albums..." @input="page = 1">
            </div>
        </div>
        <div class="col-md-7 d-flex justify-content-end align-items-center">
            <button @click="tab = 'upload'" class="btn btn-primary rounded-circle mr-4 shadow-sm d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                <i class="fas fa-plus"></i>
            </button>

            <div class="bg-light rounded-pill p-1 d-flex align-items-center">
                <button @click="if(page > 1) page--" :disabled="page === 1" class="btn btn-sm px-3 text-muted font-weight-bold border-0">PREV</button>
                <div class="mx-3 text-primary font-weight-bold" style="font-size: 0.85rem;">
                    Page <span x-text="page"></span> of <span x-text="totalPages"></span>
                </div>
                <button @click="if(page < totalPages) page++" :disabled="page === totalPages" class="btn btn-sm px-3 text-primary font-weight-bold border-0">NEXT</button>
            </div>
        </div>
    </div>

    {{-- UPLOAD SECTION --}}
    <div x-show="tab === 'upload'" x-cloak x-transition class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-40 p-5 bg-white position-relative">
                <button @click="tab = 'manage'" class="btn btn-link position-absolute text-muted" style="top: 30px; right: 30px; font-size: 1.5rem;">&times;</button>
                
                <h2 class="font-weight-black text-dark mb-1">UPLOAD NEW PHOTOS</h2>
                <p class="text-muted mb-5">Add images to your library or create a new collection.</p>

                <form action="{{ route('photos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-alice-blue p-4 rounded-20 mb-4 border-light-blue">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="small font-weight-bold text-primary text-uppercase">Target Album</label>
                                <select name="album_id" 
                                        id="album_select"
                                        x-model="selectedAlbum" 
                                        @change="isNewAlbum = ($event.target.value === 'new')"
                                        class="form-control border-0 shadow-sm rounded-pill px-4">
                                    <option value="">-- No Album (Unassigned) --</option>
                                    @foreach($albums as $album)
                                        <option value="{{ $album->id }}">{{ $album->name }}</option>
                                    @endforeach
                                    <option value="new" class="text-primary font-weight-bold">+ Create New Album</option>
                                </select>
                            </div>
                            
                            {{-- Conditional New Album Inputs --}}
                            <div class="col-md-6" x-show="isNewAlbum" x-transition>
                                <label class="small font-weight-bold text-success text-uppercase">New Album Name</label>
                                <input type="text" name="new_album_name" x-model="newAlbumName" class="form-control border-0 shadow-sm rounded-pill px-4" placeholder="Enter new album name...">
                            </div>
                        </div>
                    </div>

                    <div class="upload-scroll-area pr-2" style="max-height: 400px; overflow-y: auto;">
                        <template x-for="(row, index) in uploadRows" :key="row.id">
                            <div class="photo-row-card border mb-4 p-4 rounded-20 position-relative">
                                <button type="button" @click="if(uploadRows.length > 1) uploadRows.splice(index, 1)" class="btn btn-sm btn-outline-danger rounded-circle position-absolute" style="top: -10px; right: -10px; z-index: 10;">&times;</button>
                                
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="upload-placeholder border-dashed rounded-20 d-flex flex-column align-items-center justify-content-center position-relative overflow-hidden" style="height: 140px; background: #fcfcfc;">
                                            <template x-if="row.preview">
                                                <img :src="row.preview" class="img-fluid h-100 w-100" style="object-fit: cover;">
                                            </template>
                                            <template x-if="!row.preview">
                                                <div class="text-center">
                                                    <i class="fas fa-image text-muted fa-2x mb-2"></i>
                                                    <p class="small text-muted mb-0 font-weight-bold">BROWSE</p>
                                                </div>
                                            </template>
                                            <input type="file" name="images[]" required class="position-absolute opacity-0 w-100 h-100 cursor-pointer" @change="handleFileChange($event, index)">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group mb-3">
                                            <label class="x-small text-muted font-weight-bold text-uppercase">Image Title</label>
                                            <input type="text" name="titles[]" x-model="row.title" class="form-control bg-light border-0 rounded-pill px-4" placeholder="Enter title..." required>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="x-small text-muted font-weight-bold text-uppercase">Description (Optional)</label>
                                            <textarea name="descriptions[]" class="form-control bg-light border-0 rounded-20 px-4" rows="2" placeholder="Write a subtitle..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <button type="button" @click="addPhotoRow()" class="btn btn-link text-primary font-weight-bold px-0">
                            <i class="fas fa-plus mr-2"></i> ADD ANOTHER PHOTO
                        </button>
                        <button type="submit" 
                                :disabled="isNewAlbum && newAlbumName.trim() === ''"
                                class="btn btn-primary rounded-pill px-5 py-3 shadow-lg font-weight-black">SAVE ALL IMAGES</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MANAGE LIST --}}
    <div x-show="tab === 'manage'" x-cloak x-transition>
        @forelse($albums as $album)
            <div class="album-card-container mb-5"
                 x-data="{ myIndex: {{ $loop->iteration }}, albumName: '{{ addslashes($album->name) }}' }"
                 x-show="search.trim() === '' ? (myIndex > (page - 1) * perPage && myIndex <= page * perPage) : albumName.toLowerCase().includes(search.toLowerCase())">
                
                <div class="card shadow-lg border-0 rounded-40 p-5 bg-white">
                    <div class="d-flex justify-content-between align-items-start mb-5">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded mr-3" style="width: 8px; height: 55px;"></div>
                            <div>
                                <div class="d-flex align-items-center">
                                    <h1 class="mb-0 font-weight-black text-dark text-uppercase mr-3" style="font-size: 2.5rem;">{{ $album->name }}</h1>
                                </div>
                                <p class="text-muted italic mb-0">{{ $album->description ?? 'No description provided.' }}</p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center mt-3">
                             <button @click="tab = 'upload'; selectedAlbum = '{{ $album->id }}'; isNewAlbum = false;" class="btn btn-alice-blue shadow-none rounded-lg mr-2 p-2 px-3 text-primary">
                                <i class="fas fa-plus"></i>
                            </button>

                            <form action="{{ route('albums.destroy', $album->id) }}" method="POST" onsubmit="return confirm('Delete Album?')">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="btn btn-light shadow-none rounded-lg p-2 px-3 text-muted">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Photos Grid --}}
                    <div class="row">
                        @forelse($album->photos ?? [] as $photo) {{-- Ensure relationship is correct --}}
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="card photo-card-styled rounded-20 overflow-hidden">
                                    <img src="{{ Storage::url($photo->image_path) }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <div class="p-3">
                                        <h6 class="mb-0 font-weight-bold">{{ $photo->name }}</h6>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <p class="text-muted italic">Walang photos sa album na ito.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-white rounded-40 shadow-sm">
                <p class="text-muted">No albums found.</p>
            </div>
        @endforelse
    </div>
</div>
@stop

@section('css')
<style>
    [x-cloak] { display: none !important; }
    .bg-alice-blue { background-color: #f4f8ff; }
    .border-light-blue { border: 1px solid #e1e9f5; }
    .rounded-40 { border-radius: 2.8rem !important; }
    .rounded-20 { border-radius: 1.2rem !important; }
    .font-weight-black { font-weight: 900; }
    .italic { font-style: italic; }
    .x-small { font-size: 0.65rem; }
    .border-dashed { border: 2px dashed #d1d9e6; }
    .btn-alice-blue { background: #eef4ff; color: #007bff; }
    .photo-card-styled { transition: 0.3s; border: 1px solid #f0f0f0 !important; }
    .photo-card-styled:hover { transform: translateY(-3px); border-color: #007bff !important; }
</style>
@stop