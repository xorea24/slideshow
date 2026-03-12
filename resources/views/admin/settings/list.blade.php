@extends('adminlte::page')

@section('title', 'System Configuration')

@section('content_header')@stop

@push('css')
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
        <input type="hidden" name="display_album_ids" id="display_album_ids" value="{{ $settings['display_album_ids'] ?? '' }}">

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
                        @php
                            $transitions = [
                                'fade'        => 'Smooth Fade',
                                'slide-up'    => 'Slide Up',
                                'slide-down'  => 'Slide Down',
                                'slide-left'  => 'Slide Left',
                                'slide-right' => 'Slide Right'
                            ];
                        @endphp
                        @foreach($transitions as $val => $label)
                            <option value="{{ $val }}" {{ ($settings['transition_effect'] ?? 'fade') == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <hr>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="text-uppercase small font-weight-bold text-muted">Available Albums</label>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let activeAlbums = [];
    const $queueContainer = $('#slideshow-queue');
    const $hiddenInput = $('#display_album_ids');

    toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right", "timeOut": "3000" };

    function init() {
        const savedValue = $hiddenInput.val();
        if (savedValue) {
            const initialIds = savedValue.split(',');
            initialIds.forEach(id => {
                const $item = $(`.available[data-id="${id}"]`);
                if ($item.length) {
                    activeAlbums.push({ id: id, name: $item.data('name') });
                    $item.addClass('selected');
                }
            });
        }
        renderQueue();
    }

    function renderQueue() {
        $queueContainer.empty();
        if (activeAlbums.length === 0) {
            $queueContainer.append('<div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted py-5"><i class="fas fa-layer-group mb-2 fa-2x opacity-50"></i><p class="small">Queue is empty</p></div>');
            return;
        }

        activeAlbums.forEach((album, index) => {
            $queueContainer.append(`
                <div class="album-item shadow-sm border-0" data-id="${album.id}">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-grip-vertical drag-handle mr-3 text-muted"></i>
                        <span class="badge-number">${index + 1}</span>
                        <span class="font-weight-bold text-dark">${album.name}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-light rounded-circle remove-btn" data-id="${album.id}">
                        <i class="fas fa-times text-danger"></i>
                    </button>
                </div>`);
        });
        updateHiddenInput();
    }

    function updateHiddenInput() {
        const ids = activeAlbums.map(a => a.id).join(',');
        $hiddenInput.val(ids);
    }

    $(document).on('click', '.available', function() {
        const id = $(this).data('id').toString();
        const name = $(this).data('name');
        if ($(this).hasClass('selected')) {
            activeAlbums = activeAlbums.filter(a => a.id != id);
            $(this).removeClass('selected');
        } else {
            activeAlbums.push({ id, name });
            $(this).addClass('selected');
        }
        renderQueue();
    });

    $(document).on('click', '.remove-btn', function(e) {
        e.stopPropagation();
        const id = $(this).data('id').toString();
        activeAlbums = activeAlbums.filter(a => a.id != id);
        $(`.available[data-id="${id}"]`).removeClass('selected');
        renderQueue();
    });

    if (document.getElementById('slideshow-queue')) {
        new Sortable(document.getElementById('slideshow-queue'), {
            handle: '.drag-handle',
            animation: 250,
            onEnd: function() {
                let newOrder = [];
                $('#slideshow-queue .album-item').each(function() {
                    const id = $(this).data('id').toString();
                    const album = activeAlbums.find(a => a.id == id);
                    if(album) newOrder.push(album);
                });
                activeAlbums = newOrder;
                renderQueue();
            }
        });
    }

    $('#album-search').on('keyup', function() {
        let val = $(this).val().toLowerCase();
        $('.available').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
        });
    });

    // MODERN SAVE LOGIC (Pinalitan ang luma mong browser alert)
    $('#save-settings-button').click(function(e) {
        e.preventDefault();
        const $btn = $(this);
        const originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> SAVING...');
        
        $.ajax({
            url: "{{ route('settings.update') }}",
            method: "POST",
            data: $('#settings-form').serialize(),
            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
            success: function(res) {
                // Magandang success popup
                Swal.fire({
                    icon: 'success',
                    title: 'Configuration Saved!',
                    text: 'Your changes have been applied successfully.',
                    timer: 2000,
                    showConfirmButton: false,
                    borderRadius: '1.25rem'
                });
                toastr.success('Update successful');
                $btn.prop('disabled', false).html(originalText);
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not save settings.' });
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    init();
});
</script>
@endsection
