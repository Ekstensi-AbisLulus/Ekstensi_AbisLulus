@extends('layouts.app')

@section('title', 'Aktivitas Kegiatan')

@section('content')
    <div class="container py-4 py-lg-5">
        <div class="row g-4">
            @include('profile.partials.history-sidebar')

            <section class="col-lg-8 col-xl-9">
                <div class="card profile-card border-0 shadow-sm rounded-4 p-4 p-lg-5">
                    <h1 class="h3 mb-1">Aktivitas Kegiatan</h1>
                    <p class="text-secondary mb-4">Riwayat pendaftaran career event-mu.</p>

                    @if ($registrations->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-calendar2-x display-4 text-secondary" aria-hidden="true"></i>
                            <p class="mt-3 mb-4">Belum ada riwayat aktivitas</p>
                            @if (Route::has('events.index'))
                                <a href="{{ route('events.index') }}" class="btn btn-primary">Jelajahi Kegiatan</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Sampul</th>
                                        <th scope="col">Judul kegiatan</th>
                                        <th scope="col">Kategori</th>
                                        <th scope="col">Jadwal</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Tautan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($registrations as $registration)
                                        @php
                                            $event = $registration->event;
                                            $status = $registration->status;
                                            $statusClass = match ($status?->value) {
                                                'registered' => 'text-bg-primary',
                                                'attended' => 'text-bg-success',
                                                'cancelled' => 'text-bg-secondary',
                                                default => 'text-bg-light',
                                            };
                                            $canOpenMeeting = $event
                                                && $status === \App\Enums\RegistrationStatus::Registered
                                                && $event->end_at->isFuture()
                                                && ! in_array($event->status, [\App\Enums\EventStatus::Completed, \App\Enums\EventStatus::Cancelled], true);
                                        @endphp
                                        <tr>
                                            <td>
                                                @if ($event?->thumbnail_url)
                                                    <img src="{{ $event->thumbnail_url }}" alt="Sampul {{ $event->title }}" width="64" height="40" class="rounded object-fit-cover">
                                                @else
                                                    <span class="d-inline-flex align-items-center justify-content-center bg-light rounded" style="width:64px;height:40px"><i class="bi bi-calendar-event text-secondary"></i></span>
                                                @endif
                                            </td>
                                            <td>{{ $event?->title ?? 'Kegiatan tidak tersedia' }}</td>
                                            <td>{{ $event?->category?->label() ?? '—' }}</td>
                                            <td>
                                                @if ($event)
                                                    {{ $event->start_at->locale('id')->translatedFormat('d F Y, H:i') }}–{{ $event->end_at->locale('id')->translatedFormat('H:i') }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td><span class="badge {{ $statusClass }}">{{ $status?->label() ?? '—' }}</span></td>
                                            <td>
                                                @if ($canOpenMeeting)
                                                    <a href="{{ $event->meeting_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">Buka Link</a>
                                                @else
                                                    <span class="text-secondary">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $registrations->links() }}
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
