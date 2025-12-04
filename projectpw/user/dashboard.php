<?php
// 1. Mulai Sesi PHP
session_start();

// PERBAIKAN LOGIKA 1: Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    header('Location: ../auth/login.php?message=Silakan login terlebih dahulu.');
    exit;
}


// 2. PERBAIKAN LOGIKA 2: Ambil data user yang sudah login
// Jika sesi sudah berhasil diisi saat login, ini akan mencegah array key error
$user = $_SESSION["user"]; // Line 13

// Asumsi file koneksi.php ada di direktori yang benar
require_once(__DIR__ . '/../core/koneksi.php');


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Klinik Kecantikan GlowUp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #ffe4e1, #f0e68c, #dda0dd);
            color: #333;
            overflow-x: hidden;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            /* Perbaikan: Menetapkan margin-top untuk memberi ruang agar konten tidak tertutup fixed navbar */
        }
        /* Setelah dimuat: tampil sepenuhnya */
        body.fade-in {
          opacity: 1;
        }

        /* PERBAIKAN UTAMA UNTUK HERO SECTION */
        .hero {
            /* Menggunakan 100vh agar hero section mengisi 100% tinggi viewport */
            height: 100vh;
            /* Remove min-height: 50vh */
            background: linear-gradient(to right, rgba(255, 20, 147, 0.7), rgba(138, 43, 226, 0.7)), url('https://source.unsplash.com/featured/?luxury,spa') no-repeat center center;
            background-size: cover;
            /* Mengatur background-attachment ke fixed agar gambar latar belakang tidak bergerak saat scroll */
            background-attachment: fixed; 
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            padding-top: 0; /* Menghilangkan padding atas yang tidak diperlukan karena sudah 100vh */
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .navbar-nav{
            align-items: center;
        }

        .hero .container {
            z-index: 2;
            position: relative;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem; 
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }
        @keyframes glow {
            from { text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 15px #ff69b4; }
            to { text-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #ff69b4; }
        }
        .hero p {
            font-size: 1.2rem; 
            margin-bottom: 2rem;
        }
        .btn-glow {
            background: linear-gradient(45deg, #ff69b4, #dda0dd);
            border: none;
            color: white;
            padding: 10px 20px; 
            font-size: 1rem; 
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(255, 105, 180, 0.4);
            transition: all 0.3s;
        }
        .btn-glow:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(255, 105, 180, 0.6);
        }
        .navbar {
            background: transparent;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        /* Saat user scroll */
        .navbar.navbar-colored {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #ff69b4 !important;
        }

        .navbar-nav a{
            color: #ff69b4;
        }
        .navbar-nav a:hover {
            font-weight: bold;
        }
        .welcome-text { 
            color: #ff69b4; 
            font-weight: bold; 
            margin-right: 15px; 
        } 
        
        .service-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s;
            background: white;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(255, 105, 180, 0.3);
        }
        .service-card img {
            height: 200px;
            object-fit: cover;
        }
        .testimonial {
            background: linear-gradient(135deg, #fff, #f8f9fa);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            position: relative;
        }
        .testimonial::before {
            content: '"';
            font-size: 4rem;
            color: #ff69b4;
            position: absolute;
            top: -10px;
            left: 20px;
            font-family: 'Playfair Display', serif;
        }

        .contact-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        footer {
            background: linear-gradient(135deg, #343a40, #495057);
            color: white;
            padding: 30px 0;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            color: #ff69b4;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 3px;
            background: linear-gradient(45deg, #ff69b4, #dda0dd);
            margin: 10px auto;
        }

        /* Geser tombol arrows ke samping kanan & kiri luar carousel */
        #servicesCarousel .carousel-control-prev,
        #servicesCarousel .carousel-control-next {
            width: 5%;         /* kecilkan area klik agar tidak makan space */
        }

        #servicesCarousel .carousel-control-prev {
            left: -60px;       /* geser lebih keluar kiri */
        }

        #servicesCarousel .carousel-control-next {
            right: -60px;      /* geser lebih keluar kanan */
        }

        /* Agar tombol tetap rapi di tengah vertikal */
        #servicesCarousel .carousel-control-prev-icon,
        #servicesCarousel .carousel-control-next-icon {
            background-color: #000;
            border-radius: 50%;
            padding: 15px;
        }

        /* Hilangkan tombol naik turun (spinner) di Chrome, Edge, Safari */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Hilangkan tombol naik turun di Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Mengatasi masalah ketika navbar fixed overlap dengan hero 100vh */
        /* Berikan jarak tambahan agar konten di bawah hero muncul dengan benar */
        .content-after-hero {
            margin-top: 0; /* Setelah hero 100vh, section berikutnya langsung di bawahnya */
        }

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home"><i class="fas fa-spa"></i> Lipalas Beauty Clinic</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Book Now</a></li>
                    
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-danger" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section id="home" class="hero">
        <div class="container">
            <h1>Selamat Datang, <?= htmlspecialchars($user['nama'] ?? 'User'); ?>!</h1>
            <p class="lead">Dashboard Anda siap. Silahkan buat janji baru.</p>
            <a href="#services" class="btn btn-glow me-2">Discover Our Services</a>
        </div>
    </section>
    
    <section id="about" class="py-5">
        <div class="container">
            <h2 class="section-title">About GlowUp Clinic</h2>
            <div class="row">
                <div class="col-md-6">
                    <p>At GlowUp Clinic, we blend cutting-edge technology with natural ingredients to deliver unparalleled beauty transformations. Our certified experts ensure safe, personalized care in a luxurious, spa-like atmosphere. From rejuvenating facials to advanced laser therapies, we're here to make you feel and look your absolute best.</p>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success"></i> FDA-Approved Treatments</li>
                        <li><i class="fas fa-check-circle text-success"></i> Eco-Friendly Products</li>
                        <li><i class="fas fa-check-circle text-success"></i> 100% Satisfaction Guarantee</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="../aset/background.jpg" alt="About Us" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Our Exclusive Services</h2>

        <div id="servicesCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-touch="true">
            <div class="carousel-inner">

                                <div class="carousel-item active">
                    <div class="row">

                        <div class="col-md-4 mb-4">
                            <div class="card service-card">
                                <img src="https://i.pinimg.com/1200x/8c/9f/a7/8c9fa7dbc6e87d9a2d83c5bf0acf7874.jpg" class="card-img-top">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-magic"></i> Premium Facial Care</h5>
                                    <p class="card-text">Hydrating masks and exfoliation for glowing, youthful skin.</p>
                                    <button class="btn btn-glow" data-bs-toggle="modal" data-bs-target="#serviceModal1">Learn More</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="card service-card">
                                <img src="https://i.pinimg.com/1200x/8c/9f/a7/8c9fa7dbc6e87d9a2d83c5bf0acf7874.jpg" class="card-img-top">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-bolt"></i> Advanced Laser Therapy</h5>
                                    <p class="card-text">Permanent hair reduction with painless, high-tech lasers.</p>
                                    <button class="btn btn-glow" data-bs-toggle="modal" data-bs-target="#serviceModal2">Learn More</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="card service-card">
                                <img src="https://i.pinimg.com/1200x/8c/9f/a7/8c9fa7dbc6e87d9a2d83c5bf0acf7874.jpg" class="card-img-top">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-leaf"></i> Aromatherapy Spa</h5>
                                    <p class="card-text">Relaxing massages with essential oils for ultimate bliss.</p>
                                    <button class="btn btn-glow" data-bs-toggle="modal" data-bs-target="#serviceModal3">Learn More</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                                <div class="carousel-item">
                    <div class="row">

                        <div class="col-md-4 mb-4">
                            <div class="card service-card">
                                <img src="https://i.pinimg.com/1200x/8c/9f/a7/8c9fa7dbc6e87d9a2d83c5bf0acf7874.jpg" class="card-img-top">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-leaf"></i> Microneedling</h5>
                                    <p class="card-text">Prosedur untuk merangsang produksi kolagen.</p>
                                    <button class="btn btn-glow" data-bs-toggle="modal" data-bs-target="#serviceModal4">Learn More</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="card service-card">
                                <img src="https://i.pinimg.com/1200x/8c/9f/a7/8c9fa7dbc6e87d9a2d83c5bf0acf7874.jpg" class="card-img-top">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-dumbbell"></i> Body Sculpting</h5>
                                    <p class="card-text">Perawatan non-invasif untuk membentuk tubuh ideal.</p>
                                    <button class="btn btn-glow" data-bs-toggle="modal" data-bs-target="#serviceModal5">Learn More</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="card service-card">
                                <img src="https://i.pinimg.com/1200x/8c/9f/a7/8c9fa7dbc6e87d9a2d83c5bf0acf7874.jpg" class="card-img-top">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><i class="fas fa-apple-alt"></i> Konsultasi Nutrisi</h5>
                                    <p class="card-text">Panduan diet dan gaya hidup sehat untuk kecantikan dari dalam.</p>
                                    <button class="btn btn-glow" data-bs-toggle="modal" data-bs-target="#serviceModal6">Learn More</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#servicesCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-3"></span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#servicesCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-3"></span>
            </button>

        </div>
    </div>
</section>


    <div class="modal fade" id="serviceModal1" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Premium Facial Care Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Our facials use organic ingredients and advanced techniques for deep cleansing and nourishment. Starting at Rp 200.000. Book now for a transformative experience!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="serviceModal2" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Advanced Laser Therapy Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Perawatan laser canggih untuk menghilangkan bulu, menyamarkan noda, dan meremajakan kulit. Aman dan efektif. Mulai dari Rp 500.000.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="serviceModal3" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aromatherapy Spa Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Pengalaman spa relaksasi penuh dengan minyak esensial pilihan. Membantu mengurangi stres dan meningkatkan kualitas tidur. Mulai dari Rp 150.000/jam.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="serviceModal4" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Microneedling Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>modal 4 slide 2 (Deskripsi Microneedling).</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="serviceModal5" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Body Sculpting Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>modal 5 slide 2 (Deskripsi Body Sculpting).</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="serviceModal6" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konsultasi Nutrisi Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>modal 6 slide 2 (Deskripsi Konsultasi Nutrisi).</p>
                </div>
            </div>
        </div>
    </div>
    
    <section id="testimonials" class="py-5">
        <div class="container">
            <h2 class="section-title">What Our Clients Say</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="testimonial">
                        <p>"GlowUp Clinic transformed my skin! I feel like a new woman. Highly recommend!"</p>
                        <cite>- Sari, 28
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial">
                        <p>"The laser treatment was painless and effective. Best beauty clinic ever!"</p>
                        <cite>- Maya, 35
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial">
                        <p>"Their spa is pure heaven. I leave feeling rejuvenated every time."</p>
                        <cite>- Lina, 42
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="py-5">
        <div class="container">
            <?php if (!empty($_SESSION['booking_errors'])): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($_SESSION['booking_errors'] as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['booking_errors']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['booking_success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['booking_success']); ?>
    </div>
    <?php unset($_SESSION['booking_success']); ?>
<?php endif; ?>

            <h2 class="section-title">Book Your Glow-Up Today</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="contact-form">
                                                <form action="booking_process.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user['nama'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="number" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>
                                                    <div class="mb-3">
                                <label class="form-label">Layanan Yang Diinginkan</label>
                                <select name="service" class="form-select" required>
                                    <option>Premium Facial Care</option>
                                    <option>Advanced Laser Therapy</option>
                                    <option>Aromatherapy Spa</option>
                                    <option>Microneedling</option>
                                    <option>Body Sculpting</option>
                                    <option>Konsultasi Nutrisi</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Preferensi</label>
                                <input type="date" class="form-control" name="tanggal" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" name="message" rows="3"></textarea>
                            </div>

                            <button type="submit" class="btn btn-glow w-100">Send Message</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-map-marker-alt text-primary"></i> Jl. Elegance No. 123, Jakarta, Indonesia</p>
                    <p><i class="fas fa-phone text-primary"></i> +62 812-3456-7890</p>
                    <p><i class="fas fa-envelope text-primary"></i> glow@glowupclinic.com</p>
                    <p><i class="fas fa-clock text-primary"></i> Open: Mon-Sat 9AM-8PM</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container text-center">
            <p>&copy; 2023 GlowUp Clinic. All rights reserved. | Designed for Beauty Enthusiasts</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    
    // Smooth scrolling untuk navigasi antar section (Logika tetap sama)
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (!targetElement) return;

            if (targetId === '#home') {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }
            
            const navbarHeight = document.querySelector('.navbar').offsetHeight;
            const offsetPosition = targetElement.offsetTop - navbarHeight;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        });
    });

    // ------------------------------------------------------------------
    // Fungsi untuk mengelola tampilan navbar (transparan vs. berwarna)
    // ------------------------------------------------------------------
    const navbar = document.querySelector('.navbar');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.getElementById('navbarNav');

    /**
     * Mengatur apakah navbar harus berwarna atau transparan.
     * @param {boolean} isColored - True jika navbar harus memiliki warna solid.
     */
    function setNavbarColor(isColored) {
        if (isColored) {
            navbar.classList.add('navbar-colored');
        } else {
            navbar.classList.remove('navbar-colored');
        }
    }

    // 1. LOGIKA SCROLL
    window.addEventListener('scroll', () => {
        // Jika user scroll ke bawah (melewati 0px)
        if (window.scrollY > 0) {
            setNavbarColor(true);
        } 
        // Jika di posisi paling atas DAN menu mobile TIDAK terbuka
        else if (!navbarCollapse.classList.contains('show')) {
            setNavbarColor(false);
        }
    });

    // 2. LOGIKA TOGGLER/MENU MOBILE
    if (navbarToggler) {
        
        // Memastikan navbar berwarna ketika menu mobile dibuka
        navbarCollapse.addEventListener('shown.bs.collapse', () => {
            setNavbarColor(true); 
        });

        // Mengembalikan logika ke scroll setelah menu mobile ditutup
        navbarCollapse.addEventListener('hidden.bs.collapse', () => {
            if (window.scrollY === 0) {
                setNavbarColor(false); // Transparan hanya jika di posisi paling atas
            } else {
                setNavbarColor(true); // Tetap berwarna jika sudah di-scroll
            }
        });
        
        // Kasus spesial: Mengatur warna saat klik pertama di posisi scroll 0
        navbarToggler.addEventListener('click', () => {
             // Jika menu akan dibuka (belum memiliki kelas 'show' sebelum klik diproses) dan di posisi paling atas
             if (!navbarCollapse.classList.contains('show') && window.scrollY === 0) {
                 setNavbarColor(true); // Paksa jadi berwarna saat toggler diklik
             }
        });
    }

    // Jalankan sekali saat load untuk memastikan status navbar awal sudah benar
    window.dispatchEvent(new Event('scroll'));
    </script>
    <script>
    // Saat halaman selesai dimuat
    window.addEventListener('load', () => {
        document.body.classList.add('fade-in');
    });
    </script>
</body>
</html>