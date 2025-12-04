<?php
session_start();
require_once(__DIR__ . '/../core/koneksi.php');

// --------------------------------
// FLASH MESSAGE
// --------------------------------
$message = "";
if (isset($_SESSION['public_message'])) {
    $message = $_SESSION['public_message'];
    unset($_SESSION['public_message']);
}

// --------------------------------
// HANDLE POST
// --------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? '');
    $newPassword = trim($_POST["password"] ?? '');

    // Validasi format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['public_message'] = "Format email tidak valid.";
        header("Location: lupapw.php");
        exit;
    }

    if (strlen($newPassword) < 6) {
        $_SESSION['public_message'] = "Password minimal 6 karakter.";
        header("Location: lupapw.php");
        exit;
    }

    // Cek apakah email ada di database
    $check = $connection->prepare("SELECT id FROM user WHERE email = ?");
    if ($check === false) {
        $_SESSION['public_message'] = "Terjadi kesalahan sistem.";
        header("Location: lupapw.php");
        exit;
    }

    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        // versi eksplisit, sesuai permintaanmu
        $_SESSION['public_message'] = "Email tidak ditemukan.";
        header("Location: lupapw.php");
        exit;
    }

    // Jika email ditemukan → update password
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

    $update = $connection->prepare("UPDATE user SET password = ? WHERE email = ?");
    if ($update === false) {
        $_SESSION['public_message'] = "Terjadi kesalahan saat memperbarui data.";
        header("Location: lupapw.php");
        exit;
    } else{
      $_SESSION['public_message'] = "Password berhasil diperbarui.";
        header("Location: lupapw.php");
    }

    $update->bind_param("ss", $hashed, $email);
    if ($update->execute()) {
        header("Location: login.php?status=reset_success");
        exit;
    }

    $_SESSION['public_message'] = "Gagal memperbarui password.";
    header("Location: lupapw.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password - Lipalas Beauty Clinic</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #fce4ec, #f3e5f5);
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .forgot-container {
      background: rgba(255, 255, 255, 0.9);
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    h2 {
      color: #ec407a;
      margin-bottom: 10px;
    }
    p {
      color: #666;
      margin-bottom: 30px;
      font-size: 14px;
    }
    input {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #ddd;
      margin-bottom: 15px;
      outline: none;
      font-size: 14px;
    }
    input:focus {
      border-color: #ec407a;
      box-shadow: 0 0 5px rgba(236,64,122,0.3);
    }
    button {
      width: 100%;
      padding: 12px;
      border: none;
      background: linear-gradient(135deg, #ec407a, #ab47bc);
      color: white;
      border-radius: 10px;
      font-weight: 600;
      cursor: pointer;
      font-size: 15px;
    }
    button:hover {
      opacity: 0.9;
    }
    .message {
      background: #ffebee;
      color: #d32f2f;
      padding: 10px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

  <div class="forgot-container">
    <h2>Lupa Password</h2>
    <p>Masukkan email terdaftar dan password baru</p>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Masukkan email terdaftar" required>
      <input type="password" name="password" placeholder="Password baru" required>
      <button type="submit">Ubah Password</button>
    </form>

    <p style="margin-top:15px;">Kembali ke <a href="login.php">Halaman Login</a></p>
  </div>

</body>
</html>
