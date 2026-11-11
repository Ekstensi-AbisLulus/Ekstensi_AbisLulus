@extends('layouts.panel')

@section('title', 'Riwayat Aktivitas')
@section('page-title', 'Riwayat Aktivitas')

@php
    $tabs = [
        'materials' => ['label' => 'E-Learning', 'icon' => 'play-btn'],
        'quizzes' => ['label' => 'Kuis', 'icon' => 'patch-question'],
        'events' => ['label' => 'Kegiatan', 'icon' => 'calendar-event'],
    ];
    $date = fn ($value, $format = 'd M Y') => $value?->locale('id')->translatedFormat($format) ?? '-';
@endphp

@section('content')
    <div class="row g-4">
        <div class="col-lg-4 col-xl-3">
            @include('admin.partials.profile-nav')
        </div>

        <div class="col-lg-8 col-xl-9">
            <div class="card">
                <div class="card-body p-4">
                    <h2 class="h5 fw-semibold mb-1">Riwayat Aktivitas</h2>
                    <p class="text-secondary small mb-4">
                        @if ($isSuperAdmin)
                            Menampilkan materi, kuis, dan kegiatan yang dibuat oleh semua admin.
                        @else
                            Materi, kuis, dan kegiatan yang kamu buat. Klik ikon edit untuk melakukan perubahan.
                        @endif
                    </p>

                    {{-- Tab (tersimpan di query string ?tab=) --}}
                    <ul class="nav nav-tabs history-tabs mb-4" role="tablist">
                        @foreach ($tabs as $key => $meta)
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-2 {{ $tab === $key ? 'active' : '' }}"
                                    href="{{ route('admin.history', ['tab' => $key]) }}"
                                    @if ($tab === $key) aria-current="page" @endif>
                                    <i class="bi bi-{{ $meta['icon'] }}" aria-hidden="true"></i>
                                    <span>{{ $meta['label'] }}</span>
                                    <span class="badge rounded-pill {{ $tab === $key ? 'text-bg-primary' : 'text-bg-light border' }}">{{ $counts[$key] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Pencarian sederhana berdasarkan judul --}}
                    <form method="GET" action="{{ route('admin.history') }}" class="d-flex flex-wrap gap-2 mb-3" role="search">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="input-group history-search">
                            <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
                            <input type="search" name="q" value="{{ $search }}" class="form-control border-start-0"
                                placeholder="Cari judul {{ strtolower($tabs[$tab]['label']) }}…" aria-label="Cari judul" maxlength="100">
                        </div>
                        <button type="submit" class="btn btn-primary">Cari</button>
                        @if ($search !== '')
                            <a href="{{ route('admin.history', ['tab' => $tab]) }}" class="btn btn-light">Reset</a>
                        @endif
                    </form>

                    @if ($items->isEmpty())
                        <div class="text-center text-secondary py-5 history-empty">
                            <i class="bi bi-inbox d-block mb-2" aria-hidden="true"></i>
                            <p class="fw-semibold text-body mb-1">Belum ada riwayat aktivitas</p>
                            <p class="small mb-0">
                                @if ($search !== '')
                                    Tidak ada {{ strtolower($tabs[$tab]['label']) }} dengan judul “{{ $search }}”.
                                @else
                                    {{ $tabs[$tab]['label'] }} yang dibuat akan muncul di sini.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 history-table">
                                @switch($tab)
                                    @case('quizzes')
                                        <thead>
                                            <tr>
                                                <th scope="col">Judul Kuis</th>
                                                <th scope="col">Materi Terkait</th>
                                                @if ($isSuperAdmin) <th scope="col">Pembuat</th> @endif
                                                <th scope="col" class="text-center">Jumlah Soal</th>
                                                <th scope="col" class="text-center">Percobaan</th>
                                                <th scope="col">Dibuat</th>
                                                <th scope="col" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $quiz)
                                                <tr>
                                                    <td class="fw-medium">{{ $quiz->title }}</td>
                                                    <td class="text-secondary">{{ $quiz->material?->title ?? '-' }}</td>
                                                    @if ($isSuperAdmin) <td>{{ $quiz->creator?->name ?? '-' }}</td> @endif
                                                    <td class="text-center">{{ $quiz->questions_count }}</td>
                                                    <td class="text-center">{{ $quiz->attempts_count }}</td>
                                                    <td class="text-nowrap">{{ $date($quiz->created_at) }}</td>
                                                    <td class="text-center">
                                                        @include('admin.history.partials.edit-button', ['route' => 'admin.quizzes.edit', 'model' => $quiz, 'label' => $quiz->title])
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        @break

                                    @case('events')
                                        <thead>
                                            <tr>
                                                <th scope="col">Sampul</th>
                                                <th scope="col">Judul</th>
                                                @if ($isSuperAdmin) <th scope="col">Pembuat</th> @endif
                                                <th scope="col">Kategori</th>
                                                <th scope="col">Jadwal Mulai</th>
                                                <th scope="col">Status</th>
                                                <th scope="col" class="text-center">Pendaftar</th>
                                                <th scope="col" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $event)
                                                <tr>
                                                    <td>@include('admin.history.partials.thumb', ['url' => $event->thumbnail_url, 'alt' => 'Sampul '.$event->title])</td>
                                                    <td class="fw-medium">{{ $event->title }}</td>
                                                    @if ($isSuperAdmin) <td>{{ $event->admin?->name ?? '-' }}</td> @endif
                                                    <td>{{ $event->category?->label() ?? '-' }}</td>
                                                    <td class="text-nowrap">{{ $date($event->start_at, 'd M Y, H:i') }}</td>
                                                    <td><span class="badge {{ $event->status->badgeClass() }}">{{ $event->status->label() }}</span></td>
                                                    <td class="text-center text-nowrap">
                                                        {{ $event->registrants_count }}@if ($event->capacity) <span class="text-secondary">/ {{ $event->capacity }}</span>@endif
                                                    </td>
                                                    <td class="text-center">
                                                        @include('admin.history.partials.edit-button', ['route' => 'admin.events.edit', 'model' => $event, 'label' => $event->title])
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        @break

                                    @default
                                        <thead>
                                            <tr>
                                                <th scope="col">Thumbnail</th>
                                                <th scope="col">Judul</th>
                                                @if ($isSuperAdmin) <th scope="col">Pembuat</th> @endif
                                                <th scope="col">Status</th>
                                                <th scope="col">Dibuat</th>
                                                <th scope="col">Terakhir Diubah</th>
                                                <th scope="col" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $material)
                                                <tr>
                                                    <td>@include('admin.history.partials.thumb', ['url' => $material->thumbnail_url, 'alt' => 'Thumbnail '.$material->title])</td>
                                                    <td class="fw-medium">{{ $material->title }}</td>
                                                    @if ($isSuperAdmin) <td>{{ $material->admin?->name ?? '-' }}</td> @endif
                                                    <td>
                                                        @if ($material->is_published)
                                                            <span class="badge text-bg-success">Dipublikasikan</span>
                                                        @else
                                                            <span class="badge text-bg-secondary">Draf</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-nowrap">{{ $date($material->created_at) }}</td>
                                                    <td class="text-nowrap">{{ $date($material->updated_at) }}</td>
                                                    <td class="text-center">
                                                        @include('admin.history.partials.edit-button', ['route' => 'admin.materials.edit', 'model' => $material, 'label' => $material->title])
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                @endswitch
                            </table>
                        </div>

                        @if ($items->hasPages())
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
                                <p class="small text-secondary mb-0">
                                    Menampilkan {{ $items->firstItem() }}–{{ $items->lastItem() }} dari {{ $items->total() }}
                                </p>
                                {{ $items->onEachSide(1)->links('partials.pagination') }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
