@if (session('status'))
    <div class="alert alert-success d-flex align-items-start gap-2 small" role="status">
        <i class="bi bi-check-circle-fill mt-1"></i>
        <div>{{ session('status') }}</div>
    </div>
@endif
