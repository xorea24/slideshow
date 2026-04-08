@extends('adminlte::page')

@section('title', 'System Configuration')

@section('content_header')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="font-weight-bold mb-0">System Configuration</h3>
                <p class="text-muted mb-0">Customize your slideshow behavior and album priorities.</p>
            </div>
            <div>
                <a href="/" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-eye mr-2"></i> PREVIEW PUBLIC SCREEN
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <form id="settings-form">
        @csrf
        {{-- Hidden input to store the order of album IDs --}}
        <input type="hidden" name="display_album_ids" id="display_album_ids" value="{{ $settings['display_album_ids'] ?? '' }}">

        <div class="card settings-card shadow-sm border-0" style="border-radius: 1.5rem;">
            <div class="card-body p-4">
                <div class="row">
                    {{-- 1. SLIDE DURATION & FONT STYLE --}}
                    <div class="col-md-4 mb-3">
                        <label class="text-uppercase small font-weight-bold text-muted tracking-wider">Slide Duration (SEC)</label>
                        <input type="number" name="slide_duration" class="form-control form-control-lg border-0 bg-light mb-3" 
                               value="{{ $settings['slide_duration'] ?? 5 }}" min="1" max="60">

                        <label class="text-uppercase small font-weight-bold text-muted tracking-wider">Font Style</label>
                        <select name="font_style" class="form-control form-control-lg border-0 bg-light">
                            <option value="Inter" {{ ($settings['font_style'] ?? '') == 'Inter' ? 'selected' : '' }}>Inter (Modern Sans)</option>
                            <option value="Montserrat" {{ ($settings['font_style'] ?? '') == 'Montserrat' ? 'selected' : '' }}>Montserrat (Bold Sans)</option>
                            <option value="Tahoma" {{ ($settings['font_style'] ?? '') == 'Tahoma' ? 'selected' : '' }}>Tahoma (Standard)</option>
                            <option value="Book Antiqua" {{ ($settings['font_style'] ?? '') == 'Book Antiqua' ? 'selected' : '' }}>Book Antiqua</option>
                            <option value="Arial" {{ ($settings['font_style'] ?? '') == 'Arial' ? 'selected' : '' }}>Arial</option>
                            <option value="Georgia" {{ ($settings['font_style'] ?? '') == 'Georgia' ? 'selected' : '' }}>Georgia</option>
                            <option value="Times New Roman" {{ ($settings['font_style'] ?? '') == 'Times New Roman' ? 'selected' : '' }}>Times New Roman</option>
                            <option value="Verdana" {{ ($settings['font_style'] ?? '') == 'Verdana' ? 'selected' : '' }}>Verdana</option>
                        </select>
                    </div>

                    {{-- 2. TRANSITION EFFECT & FONT COLOR --}}
                    <div class="col-md-4 mb-3">
                        <label class="text-uppercase small font-weight-bold text-muted tracking-wider">Transition Effect</label>
                        <select name="transition_effect" class="form-control form-control-lg border-0 bg-light mb-3">
                            @php
                                $transitions = ['fade' => 'Smooth Fade', 'slide-up' => 'Slide Up', 'slide-down' => 'Slide Down', 'slide-left' => 'Slide Left', 'slide-right' => 'Slide Right'];
                            @endphp
                            @foreach($transitions as $val => $label)
                                <option value="{{ $val }}" {{ ($settings['transition_effect'] ?? 'fade') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>

                        <label class="text-uppercase small font-weight-bold text-muted tracking-wider">Font Color</label>
                        <select name="font_color" class="form-control form-control-lg border-0 bg-light">
                            <option value="white" {{ ($settings['font_color'] ?? 'white') == 'white' ? 'selected' : '' }}>White</option>
                            <option value="black" {{ ($settings['font_color'] ?? '') == 'black' ? 'selected' : '' }}>Black</option>
                        </select>
                    </div>

                    {{-- 3. OVERLAY INFO & POSITION --}}
                    <div class="col-md-4 mb-3">
                        <label class="text-uppercase small font-weight-bold text-muted tracking-wider d-block">Overlay Display</label>
                        <div class="custom-control custom-switch custom-switch-on-success mt-2">
                            <input type="hidden" name="show_photo_name" value="0">
                            <input type="checkbox" name="show_photo_name" class="custom-control-input" id="showPhotoName" value="1" {{ ($settings['show_photo_name'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="showPhotoName">Show Photo Name</label>
                        </div>
                        <div class="custom-control custom-switch custom-switch-on-success mt-2">
                            <input type="hidden" name="show_photo_description" value="0">
                            <input type="checkbox" name="show_photo_description" class="custom-control-input" id="showPhotoDescription" value="1" {{ ($settings['show_photo_description'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="showPhotoDescription">Show Description</label>
                        </div>

                        <div class="mt-3">
                            <label class="text-uppercase small font-weight-bold text-muted tracking-wider">Text Position</label>
                            <select name="overlay_position" class="form-control form-control-sm border-0 bg-light">
                                <option value="bottom-left" {{ ($settings['overlay_position'] ?? 'bottom-left') == 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                                <option value="bottom-center" {{ ($settings['overlay_position'] ?? 'bottom-left') == 'bottom-center' ? 'selected' : '' }}>Bottom Center</option>
                                <option value="bottom-right" {{ ($settings['overlay_position'] ?? 'bottom-left') == 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                                <option value="top-left" {{ ($settings['overlay_position'] ?? 'bottom-left') == 'top-left' ? 'selected' : '' }}>Top Left</option>
                                <option value="top-center" {{ ($settings['overlay_position'] ?? 'bottom-left') == 'top-center' ? 'selected' : '' }}>Top Center</option>
                                <option value="top-right" {{ ($settings['overlay_position'] ?? 'bottom-left') == 'top-right' ? 'selected' : '' }}>Top Right</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row mt-4">
                    {{-- Album Selection --}}
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="text-uppercase small font-weight-bold text-muted">Available Albums</label>
                            <input type="text" id="album-search" class="form-control form-control-sm w-50" placeholder="Search albums...">
                        </div>
                        <div class="list-container bg-light p-3 rounded" id="available-albums" style="height: 400px; overflow-y: auto; border-radius: 1rem;">
                            @foreach($albums as $album)
                                <div class="album-item available bg-white p-3 mb-2 rounded shadow-sm d-flex justify-content-between align-items-center cursor-pointer" 
                                     data-id="{{ $album->id }}" data-name="{{ $album->name }}">
                                    <span><i class="fas fa-images mr-2 text-muted"></i> {{ $album->name }}</span>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-outline-secondary mr-2 preview-album-btn" data-id="{{ $album->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <i class="fas fa-plus text-primary small"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Slideshow Queue --}}
                    <div class="col-md-6">
                        <label class="text-uppercase small font-weight-bold text-primary">Slideshow Queue (Drag to Reorder)</label>
                        <div class="list-container p-3 rounded" id="slideshow-queue" 
                             style="height: 400px; overflow-y: auto; border: 2px dashed #dee2e6; border-radius: 1rem;">
                            {{-- JS Populated --}}
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-white p-4 text-right">
                <button type="button" id="save-settings-button" class="btn btn-primary px-5 py-2 rounded-pill font-weight-bold shadow-sm">
                    APPLY CHANGES <i class="fas fa-check ml-2"></i>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- MODAL --}}
<div class="modal fade" id="albumPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 1rem; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header border-0">
                <h5 class="modal-title font-weight-bold" id="previewModalLabel">Album Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light" style="max-height: 60vh; overflow-y: auto;">
                <p class="text-center text-muted small">I-drag ang photos para baguhin ang pagkakasunod-sunod.</p>
                <div id="preview-image-container" class="d-flex flex-wrap justify-content-center" style="gap: 15px;">
                    {{-- Images load here via JS --}}
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" id="save-photo-order" class="btn btn-success btn-block rounded-pill font-weight-bold shadow-sm">
                    SAVE PHOTO SEQUENCE <i class="fas fa-save ml-2"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    .album-item.selected { opacity: 0.5; pointer-events: none; background-color: #f8f9fa !important; border: 1px solid #ddd; }
    .drag-handle { cursor: grab; padding: 10px; margin: -10px; }
    .cursor-pointer { cursor: pointer; }
    .album-item:hover:not(.selected) { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important; }
    
    .photo-drag-item { cursor: move; transition: transform 0.2s; border: 2px solid transparent; border-radius: 8px; overflow: hidden; }
    .photo-drag-item:hover { transform: scale(1.05); border-color: #007bff; }
    .sortable-ghost { opacity: 0.4; background: #c8ebfb; }

    .custom-switch-on-success .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
    }
</style>
@endpush

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let activeAlbums = [];
    let currentPreviewAlbumId = null;
    const $queueContainer = $('#slideshow-queue');
    const $hiddenInput = $('#display_album_ids');

    // 1. Initialize Sortable for Main Queue
    const queueSortable = new Sortable(document.getElementById('slideshow-queue'), {
        group: { name: 'slideshow', put: true },
        animation: 150,
        handle: '.drag-handle',
        onAdd: function (evt) {
            const $el = $(evt.item);
            const id = $el.data('id').toString();
            const name = $el.data('name');
            if ($el.hasClass('photo-drag-item')) { $el.remove(); }
            if (!activeAlbums.some(a => a.id === id)) {
                activeAlbums.splice(evt.newIndex, 0, { id: id, name: name });
                $(`.available[data-id="${id}"]`).addClass('selected');
            }
            renderQueue();
        },
        onEnd: function() { updateAlbumArrayFromDOM(); }
    });

    // 2. Initialize Sortable for Photo Preview Modal
    const modalSortable = new Sortable(document.getElementById('preview-image-container'), {
        animation: 150,
        ghostClass: 'sortable-ghost'
    });

    function updateAlbumArrayFromDOM() {
        let newOrder = [];
        $('#slideshow-queue .album-item').each(function() {
            const id = $(this).data('id').toString();
            const name = $(this).find('.font-weight-bold').text();
            if (id) newOrder.push({ id: id, name: name });
        });
        activeAlbums = newOrder;
        $hiddenInput.val(activeAlbums.map(a => a.id).join(','));
    }

    function renderQueue() {
        $queueContainer.empty();
        if (activeAlbums.length === 0) {
            $queueContainer.append('<div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted"><i class="fas fa-layer-group mb-2 fa-2x opacity-50"></i><p class="small">Queue is empty. Add or drag an album here.</p></div>');
        } else {
            activeAlbums.forEach((album, i) => {
                $queueContainer.append(`
                    <div class="album-item bg-white p-3 mb-2 rounded shadow-sm d-flex align-items-center justify-content-between border" data-id="${album.id}">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-grip-vertical mr-3 text-muted drag-handle"></i>
                            <span class="badge badge-primary mr-3">${i + 1}</span>
                            <span class="font-weight-bold">${album.name}</span>
                        </div>
                        <button type="button" class="btn btn-sm text-danger remove-btn" data-id="${album.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);
            });
        }
        $hiddenInput.val(activeAlbums.map(a => a.id).join(','));
    }

    // Modal: Load Photos
    $(document).on('click', '.preview-album-btn', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        const name = $(this).closest('.album-item').data('name');
        currentPreviewAlbumId = id;
        const $container = $('#preview-image-container');

        $('#previewModalLabel').text(`Sequence: ${name}`);
        $container.html('<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
        $('#albumPreviewModal').modal('show');

        $.get(`/admin/albums/${id}/photos`, function(photos) {
            $container.empty();
            if(photos.length === 0) {
                $container.html('<p class="p-4 text-muted">No photos in this album.</p>');
                return;
            }
            photos.forEach(p => {
                let path = p.image_path || p.path;
                path = path.replace('public/', '');
                if (path.startsWith('/')) path = path.substring(1);
                const imageUrl = `{{ asset('storage') }}/${path}`; 
                $container.append(`
                    <div class="photo-drag-item" data-photo-id="${p.id}" style="width: 120px; height: 80px;">
                        <img src="${imageUrl}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 5px;" alt="Photo"> 
                    </div>
                `);
            });
        }).fail(() => $container.html('<p class="text-danger p-4">Error loading images.</p>'));
    });

    // Save Photo Order
    $('#save-photo-order').on('click', function() {
        const $btn = $(this);
        let photoIds = [];
        $('#preview-image-container .photo-drag-item').each(function() {
            photoIds.push($(this).data('photo-id'));
        });
        if (photoIds.length === 0) return;
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> SAVING...');
        $.ajax({
            url: "{{ route('photos.reorder') }}",
            method: "POST",
            data: { _token: "{{ csrf_token() }}", photo_ids: photoIds },
            success: function() {
                Swal.fire({ icon: 'success', title: 'Sequence Saved!', timer: 1500, showConfirmButton: false });
                $('#albumPreviewModal').modal('hide');
            },
            complete: () => $btn.prop('disabled', false).html('SAVE PHOTO SEQUENCE <i class="fas fa-save ml-2"></i>')
        });
    });

    // Save Settings logic
    $('#save-settings-button').click(function() {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> SAVING...');
        $.ajax({
            url: "{{ route('settings.update') }}",
            method: "POST",
            data: $('#settings-form').serialize(),
            success: function() {
                Swal.fire({ icon: 'success', title: 'Settings Applied!', timer: 2000, showConfirmButton: false });
            },
            error: () => Swal.fire('Error', 'Something went wrong.', 'error'),
            complete: () => $btn.prop('disabled', false).html('APPLY CHANGES <i class="fas fa-check ml-2"></i>')
        });
    });

    // UI Click logic
    $(document).on('click', '.available', function() {
        const id = $(this).data('id').toString();
        const name = $(this).data('name');
        if (!activeAlbums.some(a => a.id === id)) {
            activeAlbums.push({ id: id, name: name });
            $(this).addClass('selected');
            renderQueue();
        }
    });

    $(document).on('click', '.remove-btn', function(e) {
        e.stopPropagation();
        const id = $(this).data('id').toString();
        activeAlbums = activeAlbums.filter(a => a.id !== id);
        $(`.available[data-id="${id}"]`).removeClass('selected');
        renderQueue();
    });

    $('#album-search').on('keyup', function() {
        let val = $(this).val().toLowerCase();
        $('.available').each(function() {
            $(this).toggle($(this).data('name').toLowerCase().indexOf(val) > -1);
        });
    });

    // Init Load
    const savedIds = $hiddenInput.val();
    if(savedIds) {
        savedIds.split(',').forEach(id => {
            const $item = $(`.available[data-id="${id}"]`);
            if ($item.length) {
                activeAlbums.push({ id: id, name: $item.data('name') });
                $item.addClass('selected');
            }
        });
        renderQueue();
    }
});
</script>
@stop