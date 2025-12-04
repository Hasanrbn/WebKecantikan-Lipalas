<?php
session_start();
require_once('../core/koneksi.php');

// --------------------------------
// AMBIL FLASH MESSAGE (GET)
// --------------------------------
$message = "";
if (isset($_SESSION['public_message'])) {
    $message = $_SESSION['public_message'];
    unset($_SESSION['public_message']);
}

// --------------------------------
// HANDLE REGISTER (POST)
// --------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi
    if ($nama === "") {
        $_SESSION['public_message'] = "Nama wajib diisi.";
        header("Location: register.php");
        exit;
    }

    if ($email === "" || $password === "") {
        $_SESSION['public_message'] = "Email dan Password wajib diisi.";
        header("Location: register.php");
        exit;
    }

    // Cek duplikasi email
    $check = $connection->prepare("SELECT id FROM user WHERE email = ?");
    if ($check === false) {
        $_SESSION['public_message'] = "Terjadi kesalahan sistem.";
        header("Location: register.php");
        exit;
    }

    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['public_message'] = "Email telah digunakan. Silakan gunakan email lain.";
        header("Location: register.php");
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $defaultRole = "user";

    // Insert ke database
    $stmt = $connection->prepare("INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        $_SESSION['public_message'] = "Terjadi kesalahan sistem.";
        header("Location: register.php");
        exit;
    }

    $stmt->bind_param("ssss", $nama, $email, $hashedPassword, $defaultRole);

    if ($stmt->execute()) {
        header("Location: login.php?status=success");
        exit;
    } else {
        $_SESSION['public_message'] = "Pendaftaran gagal. Coba lagi.";
        header("Location: register.php");
        exit;
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Lipalas Beauty Clinic</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      min-height: 100vh;
      /* background: linear-gradient(135deg, #fce4ec 0%, #f3e5f5 50%, #e3f2fd 100%); */
      background: url('../aset/background.jpg') no-repeat;
      display: flex;
      justify-content: center;
      align-items: center;
      background-size: cover;
      background-position: center;
      /* padding: 20px;
      position: relative;
      overflow: hidden; */
    }

    body::before, body::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      opacity: 0.3;
      z-index: 0;
    }

    .decorative-circle {
      position: absolute;
      width: 200px;
      height: 200px;
      background: #bbdefb;
      border-radius: 50%;
      filter: blur(50px);
      opacity: 0.2;
      top: 50%;
      left: 25%;
      z-index: 0;
    }

    .register-container {
      width: 100%;
      max-width: 450px;
      /* background: rgba(255, 255, 255, 0.85); */
      border: 1px solid honeydew;
      backdrop-filter: blur(8px);
      border-radius: 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      padding: 50px 40px;
      text-align: center;
      position: relative;
      z-index: 1;
    }

    .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 150px;
      height: 150px;
      margin: 0 auto 10px;
      background: transparent;
      overflow: hidden;
    }

    .logo-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    h2 {
      font-size: 32px;
      background: linear-gradient(135deg, #ec407a, #ab47bc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 10px;
      font-weight: 700;
    }

    p {
      color: honeydew;
      font-size: 18px;
      margin-bottom: 35px;
      font-weight: 400;
    }

    .error-message {
      background: #ffebee;
      color: #c62828;
      padding: 12px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
      border-left: 4px solid #c62828;
    }

    .input-group {
      margin-bottom: 24px;
      text-align: left;
    }

    .input-group label {
      display: block;
      font-weight: 500;
      margin-bottom: 8px;
      color: honeydew;
      font-size: 14px;
      margin-left: 5px;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #999;
      font-size: 18px;
      display: flex;
    }

    .toggle-password {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      font-size: 20px;
      color: #999;
      transition: color 0.2s;
      padding: 5px;
    }

    .toggle-password:hover {
      color: #ec407a;
    }

    input {
      width: 100%;
      padding: 14px 48px;
      border: 1px solid #e0e0e0;
      border-radius: 15px;
      font-size: 15px;
      outline: none;
      transition: all 0.3s ease;
      background: #f5f5f5;
    }

    input:focus {
      border-color: #ec407a;
      box-shadow: 0 0 0 4px rgba(236, 64, 122, 0.1);
      background: white;
    }

    .register-button {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, #ec407a, #ab47bc);
      color: white;
      border: none;
      border-radius: 15px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 8px 20px rgba(236, 64, 122, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .register-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(236, 64, 122, 0.4);
    }

    .register-link {
      color: honeydew;
      font-size: 16px;
      margin-top: 20px;
    }

    .register-link a {
      color: #ec407a;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .register-link a:hover {
      color: #ab47bc;
    }

    @media (max-width: 480px) {
      .register-container {
        padding: 40px 30px;
      }

      h2 {
        font-size: 28px;
      }
    }
  </style>
</head>

<body>
  <div class="decorative-circle"></div>
  <div class="register-container">
    <div class="logo-container">
            <img src="../aset/logoproject.png" alt="Logo Klinik Cantika">
    </div>

    <h2>Daftar Akun Baru</h2>
    <p>Daftar untuk mulai menggunakan layanan kami</p>

    <?php if (!empty($message)): ?>
      <div class="error-message">❌ <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <div class="input-group">
        <label for="nama">Nama</label>
        <div class="input-wrapper">
          <span class="input-icon"><i class="fa fa-user"></i></span>
          <input type="text" id="nama" name="nama" placeholder="Username" required>
        </div>
      </div>

      <div class="input-group">
        <label for="email">Email</label>
        <div class="input-wrapper">
          <span class="input-icon"><i class="fa fa-envelope"></i></span>
          <input type="email" id="email" name="email" placeholder="nama@email.com" required>
        </div>
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <span class="input-icon"><i class="fa fa-lock"></i></span>
          <input type="password" id="password" name="password" placeholder="Masukkan password" required>
          <button type="button" class="toggle-password" id="togglePassword"><i class="fa-solid fa-eye-slash"></i></button>
        </div>
      </div>

      <button type="submit" class="register-button">Daftar</button>

      <div class="register-link">
        Sudah punya akun? <a href="login.php">Masuk di sini</a>
      </div>
    </form>
  </div>

  <script>
  const passwordInput = document.getElementById('password');
  const toggleBtn = document.getElementById('togglePassword');
  const icon = toggleBtn.querySelector('i');

  toggleBtn.addEventListener('click', () => {
    const isHidden = passwordInput.type === 'password';
    passwordInput.type = isHidden ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  });
  </script>
</body>
</html>
