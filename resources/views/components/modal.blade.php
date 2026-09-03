@props([
    'id',
    'title' => '',
    'size' => 'max-w-md',
    'scrollable' => true,
    'dialogClass' => '',
    'contentClass' => '',
])

<div id="{{ $id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200">
    <div class="relative w-full {{ $size }} rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden animate-fade-in {{ $dialogClass }} {{ $contentClass }}">
        
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white" id="{{ $id }}Label">{{ $title }}</h3>
            <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="px-6 py-5 {{ $scrollable ? 'max-h-[75vh] overflow-y-auto' : '' }}">
            {{ $slot }}
        </div>

        {{-- Modal Footer --}}
        @isset($footer)
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
