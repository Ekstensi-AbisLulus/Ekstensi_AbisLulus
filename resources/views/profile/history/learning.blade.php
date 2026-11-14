@extends('layouts.app')

@section('title', 'Aktivitas E-Learning')

@section('content')
    <div class="container py-4 py-lg-5">
        <div class="row g-4">
            @include('profile.partials.history-sidebar')

            <section class="col-lg-8 col-xl-9">
                <div class="card profile-card border-0 shadow-sm rounded-4 p-4 p-lg-5">
                    <h1 class="h3 mb-1">Aktivitas E-Learning</h1>
                    <p class="text-secondary mb-4">Riwayat percobaan kuis terakhirmu.</p>

                    @if ($attempts->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-journal-x display-4 text-secondary" aria-hidden="true"></i>
                            <p class="mt-3 mb-4">Belum ada riwayat aktivitas</p>
                            @if (Route::has('materials.index'))
                                <a href="{{ route('materials.index') }}" class="btn btn-primary">Jelajahi E-Learning</a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Materi</th>
                                        <th scope="col">Kuis</th>
                                        <th scope="col">Nilai</th>
                                        <th scope="col">Benar/Salah</th>
                                        <th scope="col">Tanggal selesai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attempts as $attempt)
                                        @php
                                            $material = $attempt->quiz?->material;
                                            $score = (float) $attempt->score;
                                            $scoreClass = $score >= 70 ? 'text-bg-success' : ($score >= 50 ? 'text-bg-warning' : 'text-bg-danger');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2" style="min-width: 200px">
                                                    @if ($material?->thumbnail_url)
                                                        <img src="{{ $material->thumbnail_url }}" alt="" width="64" height="40" class="rounded object-fit-cover">
                                                    @else
                                                        <span class="d-inline-flex align-items-center justify-content-center bg-light rounded" style="width:64px;height:40px"><i class="bi bi-play-btn text-secondary"></i></span>
                                                    @endif
                                                    <span>
                                                        @if ($material && Route::has('materials.show'))
                                                            <a href="{{ route('materials.show', $material) }}">{{ $material->title }}</a>
                                                        @else
                                                            {{ $material?->title ?? 'Materi tidak tersedia' }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($attempt->quiz && Route::has('quizzes.show'))
                                                    <a href="{{ route('quizzes.show', $attempt->quiz) }}">{{ $attempt->quiz->title }}</a>
                                                @else
                                                    {{ $attempt->quiz?->title ?? 'Kuis tidak tersedia' }}
                                                @endif
                                            </td>
                                            <td><span class="badge {{ $scoreClass }}">{{ rtrim(rtrim(number_format($score, 2, '.', ''), '0'), '.') }}</span></td>
                                            <td>{{ $attempt->correct_count }}/{{ $attempt->incorrect_count }}</td>
                                            <td>{{ $attempt->completed_at?->locale('id')->translatedFormat('d F Y, H:i') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $attempts->links() }}
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
