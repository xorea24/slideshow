@extends('adminlte::page')

@section('title', $title ?? 'Albums Management')

@section('content')
<div class="albums-dashboard py-4">
    <div class="container-fluid">
        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Albums Management</h3>
                <p class="text-muted mb-0 small">Organize your slideshow albums and manage their photos.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-primary rounded-pill px-4 fw-bold text-uppercase small"
                        data-bs-toggle="modal" data-bs-target="#upload-photos-modal">
                    <span class="me-1">+</span> Add Album / Photos
                </button>
            </div>
        </div>

        {{-- Search row --}}
        <div class="row mb-4">
            <div class="col-md-6 mb-2">
                <div class="albums-search-box">
                    <i class="fas fa-search text-muted me-2"></i>
                    <input type="text" class="form-control border-0 shadow-none p-0" placeholder="Search albums...">
                </div>
            </div>
        </div>

        {{-- Albums Grid --}}
        @forelse($albums as $album)
            <div class="albums-card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0 albums-title text-uppercase">
                            <span class="albums-title-bar"></span>
                            {{ $album->name }}
                        </h5>
                        <p class="mb-0 text-muted small">{{ $album->description ?: 'No description.' }}</p>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach($album->photos as $photo)
                        <div class="col-md-3 col-sm-6">
                            <div class="photo-card h-100 d-flex flex-column">
                                <div class="photo-card-image flex-grow-1 position-relative mb-2">
                                    <img src="{{ asset('storage/' . $photo->image_path) }}" class="w-100 h-100 object-fit-cover rounded-4">
                                </div>
                                <div class="photo-card-body">
                                    <div class="fw-semibold small text-truncate">{{ $photo->name }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="albums-card text-center py-5">
                <p class="text-muted">No albums created yet.</p>
            </div>
        @endforelse
    </div>
</div>



                    {{-- Dynamic Photo Rows --}}
                    <div id="photo-rows">
                        <div class="upload-photo-row d-flex flex-column flex-md-row align-items-stretch gap-3 mb-3 p-3 border rounded-4 bg-white">
                            <div class="upload-browse-box d-flex align-items-center justify-content-center flex-shrink-0" style="width: 150px; border: 2px dashed #dee2e6; border-radius: 15px;">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-muted mb-1 d-block"></i>
                                    <input type="file" name="images[]" class="d-none upload-input-file" accept="image/*" required>
                                    <button type="button" class="btn btn-xs btn-outline-primary select-file-btn">Browse</button>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <input type="text" name="titles[]" class="form-control mb-2 rounded-pill" placeholder="Image Title">
                                <textarea name="descriptions[]" rows="2" class="form-control rounded-3" placeholder="Description..."></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-photo-row" class="btn btn-link text-decoration-none px-0 mt-2 fw-bold small">
                        <i class="fas fa-plus-circle me-1"></i> ADD ANOTHER PHOTO
                    </button>
                </form>
            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="submit" form="combined-upload-form" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm upload-save-all-btn">
                    SAVE ALL DATA
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const newAlbumToggle = document.getElementById('is_new_album');
    const existingGroup = document.getElementById('existing-album-group');
    const newGroup = document.getElementById('new-album-group');
    const albumSelect = existingGroup.querySelector('select');
    const newAlbumInput = newGroup.querySelector('input[name="new_album_name"]');

    // Toggle logic for Album
    newAlbumToggle.addEventListener('change', function() {
        if (this.checked) {
            existingGroup.style.display = 'none';
            newGroup.style.display = 'block';
            albumSelect.removeAttribute('required');
            newAlbumInput.setAttribute('required', 'required');
        } else {
            existingGroup.style.display = 'block';
            newGroup.style.display = 'none';
            albumSelect.setAttribute('required', 'required');
            newAlbumInput.removeAttribute('required');
        }
    });

    // Handle "Browse" button click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('select-file-btn')) {
            e.target.parentElement.querySelector('.upload-input-file').click();
        }
    });

    // Add more photo rows
    const addBtn = document.getElementById('add-photo-row');
    const container = document.getElementById('photo-rows');

    addBtn.addEventListener('click', function() {
        const firstRow = container.querySelector('.upload-photo-row');
        const newRow = firstRow.cloneNode(true);
        
        // Reset values
        newRow.querySelectorAll('input, textarea').forEach(input => input.value = '');
        container.appendChild(newRow);
    });
});
</script>
@endsection

@section('css')

@endsection
