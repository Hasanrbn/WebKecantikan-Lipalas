<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php?message=Akses ditolak untuk non-admin.");
    exit;
}

require_once("../core/koneksi.php");

// 1. Ambil Data Booking DENGAN KOLOM STATUS
$sql = "SELECT id, nama, email, tanggal, service, message, status FROM table_booking ORDER BY id DESC";
$result = $connection->query($sql);

// 2. Hitung Statistik Sederhana untuk Kartu Ringkasan
$total_bookings = $result ? $result->num_rows : 0;

// Logika menghitung booking hari ini (contoh, perlu disesuaikan dengan kolom tanggal di DB)
$today = date('Y-m-d');
$sql_today = "SELECT COUNT(id) FROM table_booking WHERE DATE(tanggal) = '$today'"; // Menghitung yang masih Pending/Baru
$result_today = $connection->query($sql_today);

// Query menghitung semua booking dengan status Pending
$sql_pending = "SELECT COUNT(*) AS total_pending 
                FROM table_booking 
                WHERE status = 'Pending'";

$result_pending = $connection->query($sql_pending);

// Jika query berhasil, ambil nilai totalnya, jika tidak, beri nilai 0
$total_pending = ($result_pending && $result_pending->num_rows > 0) 
                    ? $result_pending->fetch_assoc()['total_pending'] 
                    : 0;


// Menghitung booking yang sudah terkonfirmasi
$sql_confirmed = "SELECT COUNT(id) AS total_confirmed FROM table_booking WHERE status = 'Confirmed'";
$result_confirmed = $connection->query($sql_confirmed);
$total_confirmed = $result_confirmed ? $result_confirmed->fetch_assoc()['total_confirmed'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Kelola Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Palet Warna yang Lebih Estetik */
        :root {
            --primary-pink: #ff69b4; /* Hot Pink/Deep Pink */
            --secondary-color: #8a2be2; /* Blue Violet */
            --bg-light: #f4f7f9;
            --text-dark: #333;
        }
        
        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
        }
        .header-admin {
            background: linear-gradient(135deg, var(--primary-pink), var(--secondary-color));
            color: white;
            padding: 20px 0;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .btn-primary-glow {
            background-color: var(--primary-pink);
            border-color: var(--primary-pink);
            transition: all 0.3s;
        }
        .btn-primary-glow:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Kartu Ringkasan */
        .summary-card {
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .summary-card:hover {
            transform: translateY(-5px);
        }
        .summary-card .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: white;
        }

        /* Area Tabel */
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .table th {
            background-color: var(--primary-pink);
            color: white;
            border-color: var(--primary-pink) !important;
        }
        .table-responsive {
            /* Memastikan responsif di HP */
            overflow-x: auto;
        }
        
        /* Badge Status */
        .badge-status {
            font-size: 0.8rem;
            padding: 0.5em 0.8em;
            font-weight: 600;
        }

        /* Detail Card untuk Mobile */
        @media (max-width: 767.98px) {
            .table-container {
                /* Menghapus padding agar tabel responsif dapat mengisi penuh layar */
                padding: 10px;
            }
            .table-responsive > .table {
                width: 100%;
                min-width: 900px; /* Lebar minimum untuk tabel agar tidak terlalu sempit */
            }
            .header-admin .container {
                text-align: center;
            }
            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: center;
            }
            .d-flex.justify-content-between h2, .d-flex.justify-content-between a {
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>

<header class="header-admin">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold m-0"><i class="fas fa-chart-line me-2"></i> Dashboard Admin</h2>
            <a href="../auth/logout.php" class="btn btn-light"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
        </div>
    </div>
</header>

<div class="container mt-4">

    <p class="text-muted mb-4"><i class="fas fa-info-circle me-1"></i> Selamat datang kembali, Admin! Kelola semua data booking klinik Anda.</p>
    
    <div class="row mb-5">
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card summary-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle me-3" style="background-color: var(--primary-pink);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Total Semua Booking</h6>
                        <h3 class="fw-bold mb-0"><?= $total_bookings ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card summary-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle me-3" style="background-color: #ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Booking Baru/Pending</h6>
                        <h3 class="fw-bold mb-0"><?= $total_pending ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card summary-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle me-3 bg-success">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Booking Dikonfirmasi</h6>
                        <h3 class="fw-bold mb-0"><?= $total_confirmed ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card summary-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle me-3" style="background-color: var(--secondary-color);">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Layanan Populer</h6>
                        <h5 class="fw-bold mb-0 text-success">Facial Care</h5>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-info shadow-sm">
            <i class="fas fa-bell me-2"></i> <?= htmlspecialchars($_SESSION['admin_message']); ?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>

    <h4 class="mb-3 fw-bold text-dark"><i class="fas fa-table me-2"></i> Data Booking Terbaru</h4>
    
    <div class="table-container mt-3">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="text-center">
                        <th>ID</th>
                        <th><i class="fas fa-user"></i> Nama</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-calendar-alt"></i> Tanggal</th>
                        <th><i class="fas fa-concierge-bell"></i> Layanan</th>
                        <th><i class="fas fa-comment-alt"></i> Pesan</th>
                        <th><i class="fas fa-info-circle"></i> Status</th>
                        <th><i class="fas fa-cog"></i> Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            // Logika untuk menentukan warna badge status
                            $status = htmlspecialchars($row['status']);
                            $badge_class = '';
                            if ($status == 'Confirmed') {
                                $badge_class = 'bg-success';
                            } elseif ($status == 'Canceled') {
                                $badge_class = 'bg-danger';
                            } elseif ($status == 'Completed') {
                                $badge_class = 'bg-primary';
                            } else {
                                $badge_class = 'bg-warning text-dark'; // Default: Pending
                            }
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['tanggal']) ?></td>
                                <td><?= htmlspecialchars($row['service']) ?></td>
                                <td class="text-truncate" style="max-width: 150px;"><?= htmlspecialchars($row['message']) ?></td>
                                
                                <td class="text-center">
                                    <span class="badge badge-status <?= $badge_class ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="text-center" style="min-width: 150px;">
                                    <a href="booking_edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mb-1 mb-md-0"><i class="fas fa-edit"></i> Edit</a>
                                    <form method="POST" action="booking_delete.php" style="display:inline-block;">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Yakin ingin hapus booking ID <?= $row['id'] ?>?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-folder-open me-2"></i> Belum ada data booking.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<footer class="text-center py-3 mt-5 bg-white shadow-sm">
    <small class="text-muted">&copy; 2023 GlowUp Clinic Admin Dashboard</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>