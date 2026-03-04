@extends('adminlte::page')

@section('title', 'System Configuration')

@section('content_header')@stop

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    /* Mimicking your modern look within AdminLTE */
    .settings-card { border-radius: 1.5rem; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .list-container { height: 400px; overflow-y: auto; background: #f8f9fa; border-radius: 1rem; padding: 15px; }
    .album-item { 
        background: white; border: 2px solid transparent; border-radius: 12px; 
        padding: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center; justify-content: space-between;
    }
    .album-item:hover { border-color: #dee2e6; }
    .album-item.selected { border-color: #007bff; background: #f0f7ff; }
    .drag-handle { cursor: grab; color: #ced4da; margin-right: 10px; }
    .drag-handle:active { cursor: grabbing; }
    .badge-number { width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #007bff; color: white; font-size: 10px; margin-right: 8px; }
</style>
@endpush

@section('content')
<div class="container-fluid pt-4">
    <div class="mb-4">
        <h3 class="font-weight-bold">System Configuration</h3>
        <p class="text-muted">Customize your slideshow behavior and album priorities.</p>
    </div>

    <form id="settings-form">
        @csrf
        <input type="hidden" name="display_album_ids" id="display_album_ids">

        <div class="card settings-card">
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-uppercase small font-weight-bold text-muted tracking-wider">Slide Duration (SEC)</label>
                        <div class="input-group">
                            <input type="number" name="slide_duration" class="form-control form-control-lg border-0 bg-light" 
                                   value="{{ $settings['slide_duration'] ?? 5 }}" min="1" max="60">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-uppercase small font-weight-bold text-muted tracking-wider">Transition Effect</label>
                        <select name="transition_effect" class="form-control form-control-lg border-0 bg-light">
                            @foreach(['fade' => 'Smooth Fade', 'slide-up' => 'Slide Up', 'slide-left' => 'Slide Left'] as $val => $label)
                                <option value="{{ $val }}" {{ ($settings['transition_effect'] ?? 'fade') == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="text-uppercase small font-weight-bold text-muted">Available Library</label>
                            <input type="text" id="album-search" class="form-control form-control-sm w-50" placeholder="Search albums...">
                        </div>
                        <div class="list-container" id="available-albums">
                            @foreach($albums as $album)
                                <div class="album-item available" data-id="{{ $album->id }}" data-name="{{ $album->name }}">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-images mr-3 text-muted"></i>
                                        <span class="font-weight-600">{{ $album->name }}</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-light"></i>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-uppercase small font-weight-bold text-primary">Slideshow Queue</label>
                        <div class="list-container" id="slideshow-queue" style="border: 2px dashed #e9ecef;">
                            </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-white p-4 d-flex justify-content-between align-items-center">
                <a href="/" target="_blank" class="text-muted small font-weight-bold">
                    <i class="fas fa-eye mr-2"></i> LIVE PREVIEW
                </a>
                <button type="button" id="save-settings-button" class="btn btn-primary px-5 py-3 rounded-pill font-weight-bold">
                    APPLY CHANGES <i class="fas fa-check ml-2"></i>
                </button>
            </div>
        </div>
    </form>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    let activeAlbums = [];
    const savedValue = '{{ $settings['display_album_ids'] ?? '' }}';
    const initialIds = savedValue ? savedValue.split(',') : [];

    // 1. Initialize logic
    function init() {
        initialIds.forEach(id => {
            const $item = $(`.available[data-id="${id}"]`);
            if ($item.length) {
                addToQueue($item.data('id'), $item.data('name'), false);
            }
        });
        updateHiddenInput();
    }

    // 2. Add to Queue function
    function addToQueue(id, name, updateInput = true) {
        if (activeAlbums.find(a => a.id == id)) return;

        activeAlbums.push({ id, name });
        const $availableItem = $(`.available[data-id="${id}"]`);
        $availableItem.addClass('selected');

        renderQueue();
        if (updateInput) updateHiddenInput();
    }

    // 3. Remove from Queue
    function removeFromQueue(id) {
        activeAlbums = activeAlbums.filter(a => a.id != id);
        $(`.available[data-id="${id}"]`).removeClass('selected');
        renderQueue();
        updateHiddenInput();
    }

    // 4. Render the Queue UI
    function renderQueue() {
        const $container = $('#slideshow-queue');
        $container.empty();

        if (activeAlbums.length === 0) {
            $container.append('<div class="h-100 d-flex align-items-center justify-content-center text-muted">Queue is empty</div>');
            return;
        }

        activeAlbums.forEach((album, index) => {
            const html = `
                <div class="album-item shadow-sm" data-id="${album.id}">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-grip-vertical drag-handle"></i>
                        <span class="badge-number">${index + 1}</span>
                        <span class="font-weight-bold">${album.name}</span>
                    </div>
                    <button type="button" class="btn btn-sm text-danger remove-btn" data-id="${album.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
            $container.append(html);
        });
    }

    function updateHiddenInput() {
        const ids = activeAlbums.map(a => a.id).join(',');
        $('#display_album_ids').val(ids);
    }

    // --- Events ---

    // Click Available
    $(document).on('click', '.available', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        if ($(this).hasClass('selected')) {
            removeFromQueue(id);
        } else {
            addToQueue(id, name);
        }
    });

    // Remove button in Queue
    $(document).on('click', '.remove-btn', function(e) {
        e.stopPropagation();
        removeFromQueue($(this).data('id'));
    });

    // Sortable Initialization
    new Sortable(document.getElementById('slideshow-queue'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function() {
            const newOrder = [];
            $('#slideshow-queue .album-item').each(function() {
                const id = $(this).data('id');
                const album = activeAlbums.find(a => a.id == id);
                newOrder.push(album);
            });
            activeAlbums = newOrder;
            renderQueue();
            updateHiddenInput();
        }
    });

    // Search Logic
    $('#album-search').on('keyup', function() {
        const val = $(this).val().toLowerCase();
        $('.available').each(function() {
            const text = $(this).data('name').toLowerCase();
            $(this).toggle(text.includes(val));
        });
    });

    // Save Logic via SBR
    $('#save-settings-button').click(function() {
        const data = SBR.form.getData('settings-form');
        SBR.ui.toggleLoading('save-settings-button', true);

        SBR.crud.post("{{ route('settings.update') }}", data, 
            function(response) {
                SBR.ui.toggleLoading('save-settings-button', false);
                SBR.alert.success('Settings updated!');
            },
            function(err) {
                SBR.ui.toggleLoading('save-settings-button', false);
                SBR.alert.error(err);
            }
        );
    });

    init();
});
</script>
@endsection