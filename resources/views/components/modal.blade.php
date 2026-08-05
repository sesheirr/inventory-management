@props([
    'id',
    'title' => '',
    'size' => '',
    'scrollable' => true,
    'dialogClass' => '',
    'contentClass' => '',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $size }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }} {{ $dialogClass }}">
        <div class="modal-content rounded-4 border-0 shadow {{ $contentClass }}">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer border-0 form-actions">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
