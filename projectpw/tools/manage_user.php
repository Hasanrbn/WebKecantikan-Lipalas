<?php
/**
 * Alat baris perintah untuk mengelola akun (tanpa halaman web).
 *
 * Pakai dari terminal / Command Prompt, di folder project:
 *
 *   php tools/manage_user.php list
 *   php tools/manage_user.php create-admin email@domain.com "Nama Admin"
 *   php tools/manage_user.php set-password email@domain.com
 *
 * Password diketik saat diminta (tidak ditulis di perintah, jadi tidak tersimpan
 * di riwayat terminal) dan disimpan sebagai hash. Skrip ini menolak dijalankan
 * lewat browser.
 *
 * Di XAMPP/Windows, php.exe ada di C:\xampp\php\php.exe.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../core/helpers.php';

function out(string $line = ''): void { fwrite(STDOUT, $line . "\n"); }
function fail(string $line): never   { fwrite(STDERR, "Gagal: " . $line . "\n"); exit(1); }

/** Baca password tanpa menampilkan ketikan bila memungkinkan. */
function ask_password(string $prompt): string
{
    fwrite(STDOUT, $prompt);

    $interactive = function_exists('stream_isatty') && stream_isatty(STDIN);
    if ($interactive && PHP_OS_FAMILY === 'Windows') {
        $ps = 'powershell -NoProfile -Command "$p = Read-Host -AsSecureString; '
            . '[Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"';
        $value = shell_exec($ps);
        return rtrim((string) $value, "\r\n");
    }
    if ($interactive) {
        shell_exec('stty -echo');
        $value = fgets(STDIN);
        shell_exec('stty echo');
        out();
        return rtrim((string) $value, "\r\n");
    }
    return rtrim((string) fgets(STDIN), "\r\n");   // input dari pipe
}

function ask_new_password(): string
{
    $pw = ask_password('Password baru (8-72 karakter): ');
    if (($problem = password_problem($pw)) !== null) {
        fail($problem);
    }
    if (!hash_equals($pw, ask_password('Ulangi password: '))) {
        fail('Konfirmasi password tidak sama.');
    }
    return $pw;
}

function usage(): never
{
    out('Pemakaian:');
    out('  php tools/manage_user.php list');
    out('  php tools/manage_user.php create-admin <email> "<Nama>"');
    out('  php tools/manage_user.php set-password <email>');
    exit(1);
}

$cmd = $argv[1] ?? '';

switch ($cmd) {
    case 'list':
        $res = db()->query('SELECT id, email, role, nama FROM user ORDER BY id');
        out(sprintf('%-5s %-8s %-35s %s', 'ID', 'ROLE', 'EMAIL', 'NAMA'));
        foreach ($res as $r) {
            out(sprintf('%-5d %-8s %-35s %s', $r['id'], $r['role'], $r['email'], $r['nama']));
        }
        break;

    case 'create-admin':
        $email = strtolower(trim((string) ($argv[2] ?? '')));
        $nama  = clean_text((string) ($argv[3] ?? 'Admin'), 100);
        if (!valid_email_address($email)) {
            fail('Email tidak valid.');
        }
        if (!valid_name($nama)) {
            fail('Nama harus 2-100 karakter.');
        }
        $chk = db()->prepare('SELECT id FROM user WHERE email = ? LIMIT 1');
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            fail('Email sudah terdaftar. Untuk mengganti password gunakan: set-password ' . $email);
        }
        $chk->close();

        $hash = password_hash(ask_new_password(), PASSWORD_DEFAULT);
        $role = 'admin';
        $ins  = db()->prepare('INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)');
        $ins->bind_param('ssss', $nama, $email, $hash, $role);
        $ins->execute();
        out('Akun admin dibuat: ' . $email);
        break;

    case 'set-password':
        $email = strtolower(trim((string) ($argv[2] ?? '')));
        if (!valid_email_address($email)) {
            fail('Email tidak valid.');
        }
        $chk = db()->prepare('SELECT id FROM user WHERE email = ? LIMIT 1');
        $chk->bind_param('s', $email);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();
        if (!$row) {
            fail('Email tidak ditemukan.');
        }

        $hash = password_hash(ask_new_password(), PASSWORD_DEFAULT);
        $uid  = (int) $row['id'];
        $up   = db()->prepare('UPDATE user SET password = ? WHERE id = ?');
        $up->bind_param('si', $hash, $uid);
        $up->execute();
        out('Password diperbarui untuk: ' . $email);
        break;

    default:
        usage();
}
