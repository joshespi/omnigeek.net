<nav aria-label="{{ __('Pagination') }}" class="flex flex-wrap items-center justify-between gap-x-5 gap-y-3">
    <p class="text-sm text-editorial-muted">
        {{ __('Page :page of :total', ['page' => $paginator->currentPage(), 'total' => $paginator->lastPage()]) }}
    </p>

    <div class="flex flex-wrap items-center gap-2" x-on:click="$event.target.closest('button:not([disabled])') && document.getElementById('main-content')?.scrollIntoView()">
        <button type="button" class="category-chip disabled:cursor-default disabled:opacity-60"
            @disabled($paginator->onFirstPage()) wire:loading.attr="disabled"
            wire:click="previousPage('{{ $paginator->getPageName() }}')">
            {{ __('Previous') }}
        </button>

        <div class="hidden flex-wrap items-center gap-2 md:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-hidden="true" class="px-1 text-editorial-muted">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        <span wire:key="page-number-{{ $paginator->getPageName() }}-{{ $page }}">
                            @if ($page === $paginator->currentPage())
                                <span aria-current="page" class="category-chip">{{ $page }}</span>
                            @else
                                <button type="button" class="category-chip" aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    wire:loading.attr="disabled">{{ $page }}</button>
                            @endif
                        </span>
                    @endforeach
                @endif
            @endforeach
        </div>

        <button type="button" class="category-chip disabled:cursor-default disabled:opacity-60"
            @disabled(! $paginator->hasMorePages()) wire:loading.attr="disabled"
            wire:click="nextPage('{{ $paginator->getPageName() }}')">
            {{ __('Next') }}
        </button>
    </div>
</nav>
