<style>
  ul.pagination {
    justify-content: flex-end;
  }
</style>
<div class="row">
  <div class="col-md-7 pull-left text-muted">
    {{__('Showing')}} {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} / {{ $paginator->total() }} ({{__('page')}} {{ $paginator->currentPage() }} )
  </div>
  <div class="col-md-5 pull-right">
    @if ($paginator->hasPages())
    <ul class="pagination pull-right" role="navigation">
      {{-- Previous Page Link --}}
      @if ($paginator->onFirstPage())
      <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
        <span class="page-link" aria-hidden="true">Previous
        </span>
      </li>
      @else
      <li class="page-item">
        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">Previous
        </a>
      </li>
      @endif
      <?php
$start = $paginator->currentPage() - 2; // show 3 pagination links before current
$end = $paginator->currentPage() + 2; // show 3 pagination links after current
if($start < 1) {
$start = 1; // reset start to 1
$end += 1;
}
if($end >= $paginator->lastPage() ) $end = $paginator->lastPage(); // reset end to last page
?>
      @if($start > 1)
      <li class="page-item">
        <a class="page-link" href="{{ $paginator->url(1) }}">{{1}}
        </a>
      </li>
      @if($paginator->currentPage() != 4)
      {{-- "Three Dots" Separator --}}
      <li class="page-item disabled" aria-disabled="true">
        <span class="page-link">...
        </span>
      </li>
      @endif
      @endif
      @for ($i = $start; $i
      <= $end; $i++)
             <li class="page-item {{ ($paginator->currentPage() == $i) ? ' active' : '' }}">
      <a class="page-link" href="{{ $paginator->url($i) }}">{{$i}}
      </a>
      </li>
    @endfor
    @if($end
    < $paginator->lastPage())
      @if($paginator->currentPage() + 3 != $paginator->lastPage())
      {{-- "Three Dots" Separator --}}
      <li class="page-item disabled" aria-disabled="true">
        <span class="page-link">...
        </span>
      </li>
      @endif
      <li class="page-item">
        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{$paginator->lastPage()}}
        </a>
      </li>
      @endif
      {{-- Next Page Link --}}
      @if ($paginator->hasMorePages())
      <li class="page-item">
        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">Next
        </a>
      </li>
      @else
      <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
        <span class="page-link" aria-hidden="true">Next
        </span>
      </li>
      @endif
      </ul>
    @endif
  </div>
</div>
