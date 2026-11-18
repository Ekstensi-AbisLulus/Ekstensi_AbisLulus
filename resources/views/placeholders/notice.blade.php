<div class="card p-5 text-center text-secondary">
    <i class="bi bi-cone-striped fs-1 mb-2" aria-hidden="true"></i>
    <p class="mb-1 fw-medium">Halaman ini sedang dibangun.</p>
    @if ($pbi)
        <p class="small mb-0">Akan diimplementasikan pada {{ $pbi }}.</p>
    @endif
    @if (! empty($actionUrl) && ! empty($actionLabel))
        <div class="mt-3">
            <a href="{{ $actionUrl }}" class="btn btn-primary btn-panel">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
