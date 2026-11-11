{{--
    Input form auth dengan label, ikon di kiri, dan (untuk password) tombol mata tampil/sembunyi.

    @param string      $name
    @param string      $label
    @param string      $icon         nama Bootstrap Icon tanpa prefix "bi-"
    @param string      $type         default "text"
    @param string|null $id           default = $name
    @param string|null $placeholder  default = $label
    @param string|null $value        default = old($name) (diabaikan untuk password)
    @param string|null $attrs        atribut HTML tambahan (ditulis developer, tidak di-escape)
    @param string|null $hint         teks bantuan kecil di bawah input
    @param string|null $errorKey     key error, default = $name
--}}
@php
    $type = $type ?? 'text';
    $id = $id ?? $name;
    $isPassword = $type === 'password';
    $errorKey = $errorKey ?? $name;
    $hasError = $errors->has($errorKey);
@endphp

<div class="mb-3">
    <label for="{{ $id }}" class="auth-label">{{ $label }}</label>
    <div class="auth-field @if ($isPassword) has-toggle @endif">
        <i class="bi bi-{{ $icon }} auth-field-icon" aria-hidden="true"></i>
        <input type="{{ $type }}" id="{{ $id }}" name="{{ $name }}"
            class="form-control @if ($hasError) is-invalid @endif"
            placeholder="{{ $placeholder ?? $label }}"
            @unless ($isPassword) value="{{ $value ?? old($name) }}" @endunless
            @if ($hasError) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {!! $attrs ?? '' !!}>

        @if ($isPassword)
            <button type="button" class="auth-toggle-password" data-password-toggle="{{ $id }}"
                aria-controls="{{ $id }}" aria-label="Tampilkan password" aria-pressed="false">
                <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
        @endif
    </div>

    @if ($hasError)
        <div class="invalid-feedback d-block" id="{{ $id }}-error">{{ $errors->first($errorKey) }}</div>
    @elseif (! empty($hint))
        <div class="form-text">{{ $hint }}</div>
    @endif
</div>
