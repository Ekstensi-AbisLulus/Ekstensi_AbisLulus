{{--
    Tombol aksi cepat "Edit" ke modul terkait (PBI-09).
    Modul E-Learning/Kuis/Event dikerjakan anggota lain: bila route-nya belum ada,
    tampilkan tombol nonaktif dengan tooltip "Segera tersedia".
    Param: $route (nama route), $model, $label (judul item, untuk aria-label).
--}}
@if (Route::has($route))
    <a href="{{ route($route, $model) }}" class="btn-icon btn-icon-edit" title="Edit" aria-label="Edit {{ $label }}">
        <i class="bi bi-pencil-square" aria-hidden="true"></i>
    </a>
@else
    <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" data-bs-title="Segera tersedia">
        <button type="button" class="btn-icon btn-icon-edit opacity-50" disabled aria-label="Edit {{ $label }} (segera tersedia)">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
        </button>
    </span>
@endif
