@extends('layouts.panel')

@section('title', 'Profil Admin')
@section('page-title', 'Profil')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4 col-xl-3">
            @include('admin.partials.profile-nav')
        </div>

        <div class="col-lg-8 col-xl-9">
            <div class="card">
                <div class="card-body p-4">
                    <h2 class="h5 fw-semibold mb-1">Profil Admin</h2>
                    <p class="text-secondary small mb-4">Data ini membantu pengguna mengenal pengelola E-Learning dan program karier.</p>

                    @unless ($profile->isComplete())
                        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <div>Lengkapi profilmu agar identitas pengelola jelas.</div>
                        </div>
                    @endunless

                    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')

                        {{-- Foto profil --}}
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mb-4">
                            <div class="position-relative">
                                @include('partials.avatar', ['user' => $user, 'size' => 'xl', 'id' => 'avatarPreview'])
                            </div>
                            <div>
                                <label for="avatar" class="form-label fw-medium mb-1">Foto Profil</label>
                                <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar"
                                    accept=".jpg,.jpeg,.png,image/jpeg,image/png" data-avatar-input="#avatarPreview" aria-describedby="avatarHelp">
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="avatarHelp" class="form-text">Format JPG/PNG, maksimal 2 MB. Kosongkan bila tidak ingin mengganti.</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                    value="{{ old('name', $user->name) }}" maxlength="100" autocomplete="name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium">Email</label>
                                <input type="email" class="form-control bg-light" id="email" value="{{ $user->email }}" readonly aria-describedby="emailHelp">
                                <div id="emailHelp" class="form-text">Email tidak dapat diubah.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-medium">No. HP/WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
                                    value="{{ old('phone', $profile->phone) }}" placeholder="08123456789" inputmode="tel" autocomplete="tel" required
                                    aria-describedby="phoneHelp">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="phoneHelp" class="form-text">10–15 digit, boleh diawali +.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="institution" class="form-label fw-medium">Institusi/Komunitas</label>
                                <input type="text" class="form-control @error('institution') is-invalid @enderror" id="institution" name="institution"
                                    value="{{ old('institution', $profile->institution) }}" maxlength="150" placeholder="Contoh: Komunitas Digital Talent">
                                @error('institution')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="bio" class="form-label fw-medium">Bio</label>
                                <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="4" maxlength="1000"
                                    placeholder="Ceritakan singkat pengalaman dan bidang keahlianmu" aria-describedby="bioHelp">{{ old('bio', $profile->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="bioHelp" class="form-text">Maksimal 1000 karakter.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
