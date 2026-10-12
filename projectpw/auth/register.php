<?php
/**
 * Pendaftaran akun pelanggan.
 * Pendaftaran publik HANYA menghasilkan role 'user'. Role ditentukan server, bukan form.
 * Akun admin dibuat lewat tools/manage_user.php (lihat README).
 */
require_once __DIR__ . '/../core/helpers.php';

guest_only();

$self = url('auth/register.php');

if (is_post()) {
    require_csrf($self);

    $nama     = clean_text((string) ($_POST['nama'] ?? ''), 100);
    $email    = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');
    $ip       = client_ip();

    old_set(['nama' => $nama, 'email' => $email]);

    if (rate_limit_blocked('register', $ip, 10, 3600)) {
        flash_set('warning', 'Terlalu banyak percobaan pendaftaran. Silakan coba lagi nanti.');
        redirect($self);
    }
    rate_limit_hit('register', $ip);

    $errors = [];
    if (!valid_name($nama)) {
        $errors[] = 'Nama wajib diisi (2-100 karakter).';
    }
    if (!valid_email_address($email)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (($problem = password_problem($password)) !== null) {
        $errors[] = $problem;
    } elseif (!hash_equals($password, $confirm)) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }
    if ($errors) {
        foreach ($errors as $err) {
            flash_set('danger', $err);
        }
        redirect($self);
    }

    $db = db();
    $check = $db->prepare('SELECT id FROM user WHERE email = ? LIMIT 1');
    $check->bind_param('s', $email);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
    $check->close();

    if ($exists) {
        flash_set('danger', 'Email telah digunakan. Silakan gunakan email lain atau masuk ke akun Anda.');
        redirect($self);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user';

    try {
        $stmt = $db->prepare('INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $nama, $email, $hash, $role);
        $stmt->execute();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        if ((int) $e->getCode() === 1062) { // balapan: email yang sama baru saja didaftarkan
            flash_set('danger', 'Email telah digunakan. Silakan gunakan email lain atau masuk ke akun Anda.');
            redirect($self);
        }
        error_log('[Lipalas] register: ' . $e->getMessage());
        flash_set('danger', 'Pendaftaran gagal diproses. Silakan coba lagi.');
        redirect($self);
    }

    unset($_SESSION['old']);
    flash_set('success', 'Pendaftaran berhasil. Silakan masuk dengan akun Anda.');
    redirect(url('auth/login.php'));
}

$pageTitle       = 'Daftar';
$metaDescription = 'Buat akun ' . APP_NAME . ' untuk mengirim permintaan janji temu.';
$noindex         = true;
$bodyClass       = 'page-auth';
require __DIR__ . '/../includes/head.php';
?>
<div class="auth-layout">
  <aside class="auth-visual" aria-hidden="true">
    <img src="<?= e(asset('img/ruang-1200.jpg')) ?>" alt="" width="1200" height="800">
    <div class="auth-visual-text">
      <h2><?= e(APP_NAME) ?></h2>
      <p class="mb-0">Buat akun untuk mengirim dan memantau permintaan janji temu.</p>
    </div>
  </aside>

  <main id="main" class="auth-panel">
    <div class="auth-card">
      <img class="auth-logo" src="<?= e(asset('img/logo-256.png')) ?>" width="96" height="96" alt="Logo <?= e(APP_NAME) ?>">
      <h1>Buat akun baru</h1>
      <p class="auth-sub">Daftar untuk mulai membuat janji temu.</p>

      <?= flash_render() ?>

      <form method="post" action="<?= e($self) ?>" data-single-submit>
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label" for="nama">Nama lengkap</label>
          <input type="text" class="form-control" id="nama" name="nama" value="<?= e(old('nama')) ?>"
                 autocomplete="name" minlength="2" maxlength="100" required>
        </div>

        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>"
                 placeholder="nama@email.com" autocomplete="email" maxlength="100" required>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="password-wrap">
            <input type="password" class="form-control" id="password" name="password"
                   autocomplete="new-password" minlength="8" maxlength="72" aria-describedby="passwordHelp" required>
            <button type="button" class="password-toggle" data-toggle-password="#password"
                    aria-label="Tampilkan password" aria-pressed="false">
              <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
            </button>
          </div>
          <div id="passwordHelp" class="form-text">Minimal 8 karakter.</div>
        </div>

        <div class="mb-4">
          <label class="form-label" for="password_confirm">Ulangi password</label>
          <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                 autocomplete="new-password" minlength="8" maxlength="72" required>
        </div>

        <button type="submit" class="btn btn-lipalas w-100">Daftar</button>
      </form>

      <p class="auth-foot">Sudah punya akun? <a href="<?= e(url('auth/login.php')) ?>">Masuk di sini</a></p>
      <a class="auth-back" href="<?= e(url('')) ?>"><i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali ke beranda</a>
    </div>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
