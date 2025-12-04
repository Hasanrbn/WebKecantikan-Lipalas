<?php
session_start();

// --- LAPISAN KEAMANAN 1: OTORISASI ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php?message=Akses ditolak.');
    exit;
}

// Koreksi path koneksi
require_once(__DIR__ . "/../core/koneksi.php");

$booking_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$booking = null;

// --- LOGIKA POST (UPDATE DATA LENGKAP) ---
if (strtolower($_SERVER['REQUEST_METHOD']) === 'post') {
    
    // Ambil data DARI SEMUA FIELD YANG INGIN DI-EDIT
    $tanggal = trim($_POST['tanggal'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $message = trim($_POST['message'] ?? '');
    // Tambahkan kolom status baru
    $status = trim($_POST['status'] ?? 'Pending'); 
    
    // Prepared Statement untuk UPDATE, kini termasuk kolom STATUS
    $stmt = $connection->prepare("
        UPDATE table_booking 
        SET tanggal = ?, service = ?, message = ?, status = ? 
        WHERE id = ?
    ");
    
    if (!$stmt) {
        $_SESSION['admin_message'] = '❌ Kesalahan sistem: Gagal menyiapkan query update.';
        header('Location: dashboard.php');
        exit;
    }

    // 'ssssi' = string (tanggal), string (service), string (message), string (status), integer (id)
    $stmt->bind_param("ssssi", $tanggal, $service, $message, $status, $booking_id);

    if ($stmt->execute()) {
        $_SESSION['admin_message'] = '✅ Booking ID: ' . $booking_id . ' berhasil diperbarui, Status diubah menjadi ' . htmlspecialchars($status) . '.';
        header('Location: dashboard.php');
    } else {
        // Pesan error generik untuk keamanan
        $_SESSION['admin_message'] = '❌ Gagal memperbarui booking. Terjadi kesalahan saat eksekusi.';
        header('Location: booking_edit.php?id=' . $booking_id);
    }
    $stmt->close();
    exit;
}

// --- LOGIKA GET (MENAMPILKAN FORM) ---

// 1. Ambil data booking berdasarkan ID (Kini mengambil semua kolom di booking)
if ($booking_id > 0) {
    // Pastikan query SELECT mengambil kolom STATUS dari tabel b (table_booking)
    $stmt = $connection->prepare("
        SELECT b.*, u.nama, u.email 
        FROM table_booking b 
        JOIN user u ON b.user_id = u.id 
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_object();
    $stmt->close();
}

// 2. Cek apakah booking ditemukan
if (!$booking) {
    $_SESSION['admin_message'] = '❌ Booking tidak ditemukan atau ID tidak valid.';
    header('Location: dashboard.php');
    exit;
}

// Daftar status yang tersedia untuk dropdown
$available_statuses = ['Pending', 'Confirmed', 'Canceled', 'Completed'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking #<?= $booking->id ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --pink-gradient: linear-gradient(135deg, #ec407a, #ab47bc);
            --main-pink: #ec407a;
            --main-purple: #ab47bc;
            --bg-color: #fce4ec; /* Lightest pink */
            --card-bg: rgba(255, 255, 255, 0.9);
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-color) 0%, #f3e5f5 50%, #e3f2fd 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .edit-container {
            width: 100%;
            max-width: 600px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        h2 {
            font-size: 28px;
            background: var(--pink-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            text-align: center;
        }
        .user-info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 14px;
            border-left: 5px solid var(--main-pink);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
        }
        input[type="date"], input[type="text"], textarea, select { 
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s;
            background: #f9f9f9;
        }
        input:focus, textarea:focus, select:focus {
            border-color: var(--main-pink);
            outline: none;
        }
        input[readonly], textarea[readonly] {
            background: #eee;
            cursor: not-allowed;
            color: #777;
            border-style: solid; 
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .btn-submit {
            padding: 12px 25px;
            background: var(--pink-gradient);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 5px 15px rgba(236, 64, 122, 0.2);
            width: 100%; /* Lebarkan tombol */
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 20px rgba(236, 64, 122, 0.3);
        }
        .action-links {
            margin-top: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            justify-content: space-between; /* Untuk memisahkan tombol simpan dan batal */
        }
        .action-links a {
            color: var(--main-purple);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }
        .action-links a:hover {
            color: var(--main-pink);
        }
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 15px;
            font-weight: 500;
        }
        .alert.success {
            background: #e8f5e9;
            color: #388e3c;
            border-left: 5px solid #388e3c;
        }
        .alert.error {
            background: #ffebee;
            color: #c62828;
            border-left: 5px solid #c62828;
        }

        /* Status Badge di halaman Edit */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 700;
            color: white;
            margin-top: 5px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .status-badge.pending { background-color: #ffc107; color: #333; }
        .status-badge.confirmed { background-color: #28a745; }
        .status-badge.canceled { background-color: #dc3545; }
        .status-badge.completed { background-color: #007bff; }

        .form-action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <h2><i class="fas fa-edit me-2"></i> Edit Booking #<?= $booking->id ?></h2>
        
        <?php 
        // Tampilkan pesan feedback (sukses/gagal)
        if(isset($_SESSION['admin_message'])): 
            $msg = $_SESSION['admin_message'];
            $class = strpos($msg, '✅') !== false ? 'success' : 'error';
            unset($_SESSION['admin_message']);
        ?>
            <div class="alert <?= $class ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="user-info">
            <i class="fas fa-user-tag me-1"></i> Booking oleh: <strong><?= htmlspecialchars($booking->nama) ?></strong> (<?= htmlspecialchars($booking->email) ?>)
        </div>
        
        <form method="POST" action="booking_edit.php?id=<?= $booking->id ?>">
            <input type="hidden" name="id" value="<?= $booking->id ?>">
            
            <!-- KOLOM STATUS BARU -->
            <div class="form-group">
                <label for="status"><i class="fas fa-info-circle me-1"></i> Ubah Status Booking:</label>
                <select id="status" name="status" required>
                    <?php foreach ($available_statuses as $s): ?>
                        <option value="<?= $s ?>" <?= ($s == $booking->status) ? 'selected' : '' ?>>
                            <?= $s ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <!-- Tampilkan status saat ini sebagai badge -->
                <span class="status-badge <?= strtolower($booking->status) ?>">
                    Status Saat Ini: <?= htmlspecialchars($booking->status) ?>
                </span>
            </div>
            <!---------------------->

            <div class="form-group">
                <label for="tanggal"><i class="fas fa-calendar-alt me-1"></i> Tanggal Booking:</label>
                <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($booking->tanggal) ?>" required>
            </div>

            <div class="form-group">
                <label for="service"><i class="fas fa-concierge-bell me-1"></i> Layanan Dipesan:</label>
                <!-- Asumsi daftar layanan tetap statis -->
                <select id="service" name="service" required>
                    <?php 
                    $services = [
                        "Premium Facial Care", 
                        "Advanced Laser Therapy", 
                        "Aromatherapy Spa", 
                        "Microneedling", 
                        "Body Sculpting", 
                        "Konsultasi Nutrisi"
                    ];
                    foreach ($services as $svc):
                        $selected = ($svc == $booking->service) ? 'selected' : '';
                    ?>
                        <option value="<?= $svc ?>" <?= $selected ?>><?= $svc ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="message"><i class="fas fa-comment-alt me-1"></i> Pesan User:</label>
                <textarea id="message" name="message"><?= htmlspecialchars($booking->message) ?></textarea>
            </div>
            
            <div class="form-action-row">
                 <a href="dashboard.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
                <button type="submit" class="btn-submit"><i class="fas fa-save me-1"></i> Simpan Semua Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>