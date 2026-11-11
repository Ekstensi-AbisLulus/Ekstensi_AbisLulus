@foreach (['success' => 'check-circle-fill', 'error' => 'exclamation-triangle-fill'] as $type => $icon)
    @if (session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : 'success' }} alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-{{ $icon }}"></i>
            <div>{{ session($type) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif
@endforeach
