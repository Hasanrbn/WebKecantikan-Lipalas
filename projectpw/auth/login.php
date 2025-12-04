<?php
session_start();
require_once(__DIR__ . '/../core/koneksi.php');

$message = "";

// ---------------------------
// AMBIL FLASH MESSAGE (GET)
// ---------------------------
if (isset($_SESSION['public_message'])) {
    $message = $_SESSION['public_message'];
    unset($_SESSION['public_message']);
}

// ---------------------------
// REMEMBER ME (COOKIE EMAIL)
// ---------------------------
$saved_email = isset($_COOKIE['remembered_email']) 
    ? htmlspecialchars($_COOKIE['remembered_email']) 
    : "";

// ---------------------------
// SUDAH LOGIN? ARAHKAN
// ---------------------------
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit;
}

// ---------------------------
// HANDLE FORM LOGIN (POST)
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']);

    // VALIDASI
    if ($email === "" || $password === "") {
        $_SESSION['public_message'] = "Email dan Password wajib diisi.";
        header('Location: login.php');
        exit;
    }

    // CEK USER
    $stmt = $connection->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $_SESSION['public_message'] = "Email tidak terdaftar.";
        header('Location: login.php');
        exit;
    }

    $user = $result->fetch_assoc();

    // VERIFIKASI PASSWORD
    if (!password_verify($password, $user['password'])) {
        $_SESSION['public_message'] = "Password salah. Coba lagi.";
        header('Location: login.php');
        exit;
    }

    // LOGIN BERHASIL → SET SESSION
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = $user;
    $_SESSION['role'] = $user['role'] ?? "user";

    // REMEMBER ME
    if ($remember) {
        setcookie('remembered_email', $email, time() + 30*24*60*60, '/');
    } else {
        setcookie('remembered_email', '', time() - 3600, '/');
    }

    // REDIRECT SESUAI ROLE
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }

    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Lipalas Beauty Clinic</title>
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
      background: url('../aset/background.jpg') no-repeat;
      background-size: cover;
      background-position: center;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    body::before, body::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      opacity: 0.3;
      z-index: 0;
    }

    .login-container {
      width: 100%;
      max-width: 450px;
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
      width: 150px;
      height: 150px;
      margin: 0 auto 10px;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .logo-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    h2 {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 10px;
      background: linear-gradient(135deg, #ec407a, #ab47bc);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .subtitle {
      color: honeydew;
      font-size: 18px;
      margin-bottom: 35px;
    }

    .message-box {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .error-message {
      background: #ffebee;
      color: #c62828;
      border-left: 4px solid #c62828;
    }

    .success-message {
      background: #e8f5e9;
      color: #2e7d32;
      border-left: 4px solid #2e7d32;
    }

    .input-group {
      margin-bottom: 24px;
      text-align: left;
    }

    .input-group label {
      color: honeydew;
      font-weight: 500;
      font-size: 14px;
      margin-bottom: 8px;
      display: block;
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
    }

    input {
      width: 100%;
      padding: 14px 48px;
      border: 1px solid #e0e0e0;
      border-radius: 15px;
      background: #f5f5f5;
      font-size: 15px;
      transition: all 0.3s ease;
    }

    input:focus {
      background: white;
      border-color: #ec407a;
      box-shadow: 0 0 0 4px rgba(236,64,122,0.1);
    }

    .toggle-password {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      font-size: 18px;
      color: #999;
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        color: white;
    }

    .form-options label {
        display: flex;
        align-items: center;
        cursor: pointer; 
        font-size: 15px;
    }

    .form-options input[type="checkbox"] {
        width: 18px; 
        height: 18px; 
        margin-right: 8px;
        padding: 0;
        border-radius: 4px;
    }

    .login-button {
      width: 100%;
      padding: 15px;
      border: none;
      border-radius: 15px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      background: linear-gradient(135deg, #ec407a, #ab47bc);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
      box-shadow: 0 8px 20px rgba(236,64,122,0.3);
      transition: 0.3s;
    }

    .login-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(236,64,122,0.4);
    }

    .register-link {
      margin-top: 20px;
      font-size: 16px;
      color: honeydew;
    }

    .register-link a {
      color: #ec407a;
      font-weight: 600;
      text-decoration: none;
    }

  </style>
</head>

<body>

<div class="login-container">
  
  <div class="logo-container">
    <img src="../aset/logoproject.png" alt="Logo">
  </div>

  <h2>Welcome Back</h2>
  <p class="subtitle">Masuk untuk melanjutkan ke dashboard Anda</p>

  <?php if (!empty($message)): 
    // Tentukan apakah pesan itu sukses atau error
    $is_success = strpos($message, 'berhasil') !== false || strpos($message, '✅') !== false;
    $class = $is_success ? 'success-message' : 'error-message';
  ?>
    <div class="message-box <?= $class ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="login.php">

    <div class="input-group">
      <label>Email</label>
      <div class="input-wrapper">
        <span class="input-icon"><i class="fa fa-envelope"></i></span>
        <input type="email" name="email" value="<?= $saved_email ?>" placeholder="nama@gmail.com" required>
      </div>
    </div>

    <div class="input-group">
      <label>Password</label>
      <div class="input-wrapper">
        <span class="input-icon"><i class="fa fa-lock"></i></span>
        <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
        <button type="button" class="toggle-password" id="togglePassword">
          <i class="fa fa-eye-slash"></i>
        </button>
      </div>
    </div>

    <div class="form-options">
      <label>
        <input type="checkbox" name="remember_me" <?= isset($_COOKIE['remembered_email']) ? 'checked' : '' ?>>
        <span style="color:honeydew">Ingat saya</span>
      </label>
      <a href="lupapw.php" style="color:honeydew; text-decoration:none">Lupa password?</a>
    </div>

    <button type="submit" class="login-button">
      <i class="fas fa-sign-in-alt"></i> Masuk Sekarang
    </button>

    <div class="register-link">
      Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>

  </form>
</div>

<script>
const pass = document.getElementById('password');
const btn = document.getElementById('togglePassword');
btn.onclick = () => {
  const hide = pass.type === 'password';
  pass.type = hide ? 'text' : 'password';
  btn.querySelector('i').classList.toggle('fa-eye');
  btn.querySelector('i').classList.toggle('fa-eye-slash');
};
</script>

</body>
</html>