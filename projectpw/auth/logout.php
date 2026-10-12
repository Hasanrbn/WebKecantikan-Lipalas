<?php
/**
 * Keluar dari akun. Hanya memproses POST + token CSRF (tombol "Keluar" di navbar berupa form).
 * Membuka halaman ini lewat GET tidak mengeluarkan siapa pun.
 */
require_once __DIR__ . '/../core/helpers.php';

if (!is_post()) {
    redirect(is_logged_in() ? role_home($_SESSION['role'] ?? null) : url(''));
}

require_csrf(url(''));

logout_user();
flash_set('success', 'Anda telah keluar.');
redirect(url('auth/login.php'));
