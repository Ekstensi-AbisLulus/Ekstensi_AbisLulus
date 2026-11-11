# Aturan Project AbisLulus
- Spesifikasi lengkap ada di docs/prd-abislulus.md. Selalu rujuk PRD sebelum menulis kode.
- Stack: Laravel + Blade + Bootstrap 5 + MySQL. JANGAN pakai Tailwind, React, Vue, atau Livewire.
- Jangan bangun fitur di PRD §3.2 (di luar lingkup): sertifikat, pembayaran, lowongan, integrasi Zoom API.
- Skema DB harus persis seperti PRD §6. Kalau butuh kolom baru, tanya dulu.
- Validasi pakai FormRequest, otorisasi pakai Policy, akses peran pakai middleware `role`.
- Teks UI bahasa Indonesia; kode dan nama variabel bahasa Inggris.
- Jangan ubah migration lama; buat migration baru untuk perubahan.
- Kerjakan satu PBI per tugas. Setelah selesai, sebutkan file yang diubah dan langkah mengetesnya.
- Jalankan `php artisan test` setelah perubahan.