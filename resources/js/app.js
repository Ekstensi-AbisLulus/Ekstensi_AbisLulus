import './bootstrap';
import * as bootstrap from 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';
import './password-toggle';
import './avatar-preview';

window.bootstrap = bootstrap;

// Tooltip Bootstrap (mis. tombol "Segera tersedia" di riwayat admin).
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
