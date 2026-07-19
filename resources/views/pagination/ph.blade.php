@if ($paginator->hasPages())
    <nav class="ph-pager" role="navigation" aria-label="Navigasi halaman">
        {{-- Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="ph-pager-btn is-disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i></span>
        @else
            <button type="button" class="ph-pager-btn" wire:key="pg-prev"
                wire:click="previousPage('{{ $paginator->getPageName() }}')" rel="prev" aria-label="Sebelumnya">
                <i class="bi bi-chevron-left"></i>
            </button>
        @endif

        {{-- Nomor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="ph-pager-dots" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="ph-pager-btn is-active" wire:key="pg-{{ $page }}" aria-current="page">{{ $page }}</span>
                    @else
                        <button type="button" class="ph-pager-btn" wire:key="pg-{{ $page }}"
                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Berikutnya --}}
        @if ($paginator->hasMorePages())
            <button type="button" class="ph-pager-btn" wire:key="pg-next"
                wire:click="nextPage('{{ $paginator->getPageName() }}')" rel="next" aria-label="Berikutnya">
                <i class="bi bi-chevron-right"></i>
            </button>
        @else
            <span class="ph-pager-btn is-disabled" aria-disabled="true"><i class="bi bi-chevron-right"></i></span>
        @endif
    </nav>
@endif
