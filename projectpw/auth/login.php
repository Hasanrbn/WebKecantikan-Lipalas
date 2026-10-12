<?php
/**
 * Halaman masuk.
 * - CSRF, pembatasan percobaan (5 gagal / 15 menit per email+IP), session id baru setelah login.
 * - Pesan gagal sengaja sama untuk "email tidak ada" dan "password salah" (anti enumerasi akun).
 */
require_once __DIR__ . '/../core/helpers.php';

guest_only();

// Hash palsu: password_verify tetap dijalankan walau email tidak ada, agar waktu respons seragam.
const LOGIN_DUMMY_HASH = '$2y$10$8WbU.vnl/odJ5ygmUV0VMO6dcMrF6p7V8GdDAHFjt8F37PXDV5F26';

$self = url('auth/login.php');

if (is_post()) {
    require_csrf($self);

    $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember_me']);
    $ip       = client_ip();

    old_set(['email' => $email]);

    if ($email === '' || $password === '') {
        flash_set('danger', 'Email dan password wajib diisi.');
        redirect($self);
    }
    if (!valid_email_address($email)) {
        flash_set('danger', 'Format email tidak valid.');
        redirect($self);
    }

    $identKey = $ip . '|' . $email;
    if (rate_limit_blocked('login', $identKey, 5, 900) || rate_limit_blocked('login_ip', $ip, 30, 900)) {
        flash_set('warning', 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam 15 menit.');
        redirect($self);
    }

    $stmt = db()->prepare('SELECT id, nama, email, password, role FROM user WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $hash = $user['password'] ?? LOGIN_DUMMY_HASH;
    $passwordOk = password_verify($password, $hash);

    if (!$user || !$passwordOk) {
        rate_limit_hit('login', $identKey);
        rate_limit_hit('login_ip', $ip);
        flash_set('danger', 'Email atau password salah.');
        redirect($self);
    }

    // Hash lama yang kurang kuat diperbarui otomatis saat login berhasil.
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $up = db()->prepare('UPDATE user SET password = ? WHERE id = ?');
        $uid = (int) $user['id'];
        $up->bind_param('si', $newHash, $uid);
        $up->execute();
        $up->close();
    }

    rate_limit_clear('login', $identKey);
    unset($_SESSION['old']);
    login_user($user);

    // "Ingat email": hanya menyimpan email (bukan password) agar kolom email terisi otomatis.
    $cookieOpts = [
        'path'     => base_path(),
        'secure'   => request_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    if ($remember) {
        setcookie('remembered_email', $email, $cookieOpts + ['expires' => time() + 30 * 24 * 3600]);
    } else {
        setcookie('remembered_email', '', $cookieOpts + ['expires' => time() - 3600]);
    }

    // Pelanggan kembali ke halaman yang tadi dituju; admin selalu ke dashboard admin.
    $home = role_home($user['role']);
    redirect($user['role'] === 'admin' ? $home : intended_url($home));
}

$savedEmail = '';
if (isset($_COOKIE['remembered_email']) && is_string($_COOKIE['remembered_email'])
    && valid_email_address($_COOKIE['remembered_email'])) {
    $savedEmail = $_COOKIE['remembered_email'];
}
$emailValue = old('email', $savedEmail);

$pageTitle       = 'Masuk';
$metaDescription = 'Masuk ke akun ' . APP_NAME . ' untuk membuat janji temu.';
$noindex         = true;
$bodyClass       = 'page-auth';
require __DIR__ . '/../includes/head.php';
?>
<div class="auth-layout">
  <aside class="auth-visual" aria-hidden="true">
    <img src="<?= e(asset('img/ruang-1200.jpg')) ?>" alt="" width="1200" height="800">
    <div class="auth-visual-text">
      <h2><?= e(APP_NAME) ?></h2>
      <p class="mb-0">Masuk untuk mengirim dan memantau permintaan janji temu Anda.</p>
    </div>
  </aside>

  <main id="main" class="auth-panel">
    <div class="auth-card">
      <img class="auth-logo" src="<?= e(asset('img/logo-256.png')) ?>" width="96" height="96" alt="Logo <?= e(APP_NAME) ?>">
      <h1>Selamat datang kembali</h1>
      <p class="auth-sub">Masuk ke akun Anda untuk melanjutkan.</p>

      <?= flash_render() ?>

      <form method="post" action="<?= e($self) ?>" data-single-submit>
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?= e($emailValue) ?>"
                 placeholder="nama@email.com" autocomplete="username" maxlength="100" required>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="password-wrap">
            <input type="password" class="form-control" id="password" name="password"
                   autocomplete="current-password" maxlength="72" required>
            <button type="button" class="password-toggle" data-toggle-password="#password"
                    aria-label="Tampilkan password" aria-pressed="false">
              <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me"
                   <?= $savedEmail !== '' ? 'checked' : '' ?>>
            <label class="form-check-label" for="remember_me">Ingat email saya</label>
          </div>
          <a href="<?= e(url('auth/lupapw.php')) ?>">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-lipalas w-100">Masuk</button>
      </form>

      <p class="auth-foot">Belum punya akun? <a href="<?= e(url('auth/register.php')) ?>">Daftar di sini</a></p>
      <a class="auth-back" href="<?= e(url('')) ?>"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke beranda</a>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
