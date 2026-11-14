@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    @php
        $profileComplete = filled($user->name) && filled($profile?->phone) && filled($profile?->education_level);
        $avatarUrl = $profile?->avatar_path ? asset('storage/'.$profile->avatar_path) : null;
        $gender = $profile?->gender?->value;
        $educationLevel = $profile?->education_level?->value;
    @endphp
    <div class="container py-4 py-lg-5">
        @if (! $profileComplete)
            <div class="alert alert-info" role="alert">Lengkapi profilmu untuk pengalaman yang lebih baik</div>
        @endif

        <div class="row g-4">
            <aside class="col-lg-4 col-xl-3">
                <div class="card profile-card border-0 shadow-sm rounded-4 text-center p-4">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" class="profile-avatar mx-auto mb-3" alt="Foto profil {{ $user->name }}">
                    @else
                        <span class="profile-avatar profile-avatar-default mx-auto mb-3" aria-label="Avatar {{ $user->name }}">
                            {{ filled($user->name) ? mb_strtoupper(mb_substr($user->name, 0, 1)) : '' }}
                            @if (blank($user->name)) <i class="bi bi-person-fill"></i> @endif
                        </span>
                    @endif
                    <h2 class="h5 mb-1">{{ $user->name }}</h2>
                    <p class="text-secondary small mb-4">{{ $user->email }}</p>
                    <nav class="nav nav-pills flex-column text-start gap-1">
                        <a class="nav-link active" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil</a>
                        <a class="nav-link text-body" href="{{ route('profile.history.learning') }}"><i class="bi bi-play-circle me-2"></i>Aktivitas E-Learning</a>
                        <a class="nav-link text-body" href="{{ route('profile.history.events') }}"><i class="bi bi-calendar-event me-2"></i>Aktivitas Kegiatan</a>
                    </nav>
                </div>
            </aside>

            <section class="col-lg-8 col-xl-9">
                <div class="card profile-card border-0 shadow-sm rounded-4 p-4 p-lg-5">
                    <h1 class="h3 mb-1">Selamat Datang, {{ $user->name }}</h1>
                    <p class="text-secondary mb-4">Lengkapi informasi profilmu.</p>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama lengkap <span class="text-danger">*</span></label>
                                <input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="100" required class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email" value="{{ $user->email }}" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label">Tanggal lahir</label>
                                <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date', $profile?->birth_date?->format('Y-m-d')) }}" max="{{ now()->toDateString() }}" class="form-control @error('birth_date') is-invalid @enderror">
                                @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Jenis kelamin</label>
                                <select id="gender" name="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="male" @selected(old('gender', $gender) === 'male')>Laki-laki</option>
                                    <option value="female" @selected(old('gender', $gender) === 'female')>Perempuan</option>
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">No. HP/WhatsApp <span class="text-danger">*</span></label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone', $profile?->phone) }}" required class="form-control @error('phone') is-invalid @enderror">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">Alamat domisili</label>
                                <input id="address" name="address" value="{{ old('address', $profile?->address) }}" class="form-control @error('address') is-invalid @enderror">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="education_level" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="education_level" name="education_level" required class="form-select @error('education_level') is-invalid @enderror">
                                    <option value="">Pilih status</option>
                                    @foreach (['Pelajar SMA/SMK', 'Mahasiswa', 'Fresh Graduate'] as $level)
                                        <option value="{{ $level }}" @selected(old('education_level', $educationLevel) === $level)>{{ $level }}</option>
                                    @endforeach
                                </select>
                                @error('education_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="institution" class="form-label">Institusi</label>
                                <input id="institution" name="institution" value="{{ old('institution', $profile?->institution) }}" class="form-control @error('institution') is-invalid @enderror">
                                @error('institution') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="graduation_year" class="form-label">Tahun lulus</label>
                                <input id="graduation_year" name="graduation_year" inputmode="numeric" maxlength="4" value="{{ old('graduation_year', $profile?->graduation_year) }}" class="form-control @error('graduation_year') is-invalid @enderror">
                                @error('graduation_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="linkedin_url" class="form-label">Link CV/LinkedIn</label>
                                <input id="linkedin_url" name="linkedin_url" type="url" value="{{ old('linkedin_url', $profile?->linkedin_url) }}" class="form-control @error('linkedin_url') is-invalid @enderror">
                                @error('linkedin_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea id="bio" name="bio" rows="4" maxlength="1000" class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $profile?->bio) }}</textarea>
                                @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label for="avatar" class="form-label">Foto profil (JPG/PNG, maks. 2 MB)</label>
                                <input id="avatar" name="avatar" type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="form-control @error('avatar') is-invalid @enderror">
                                @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
