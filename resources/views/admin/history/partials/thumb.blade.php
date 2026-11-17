{{--
    Thumbnail/sampul kecil di tabel riwayat. thumbnail_url bisa URL penuh atau path upload (disk public).
    Param: $url (string|null), $alt.
--}}
@php
    $src = blank($url ?? null)
        ? null
        : (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://', '//']) ? $url : asset('storage/'.ltrim($url, '/')));
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $alt }}" class="history-thumb" loading="lazy" width="72" height="44">
@else
    <span class="history-thumb history-thumb-empty" aria-hidden="true"><i class="bi bi-image"></i></span>
@endif
