<?php
$page_title = 'PeduliBersama - Platform Penggalangan Dana & Donasi Terbuka';
require_once 'includes/header.php';

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$kampanye_list = fetch_campaigns($search, 'aktif', 6);
?>

<!-- Hero Section with Real Photo -->
<section class="hero-section">
    <div class="hero-bg" style="background-image: url('assets/img/hero.jpg');"></div>
    <div class="container hero-content">
        <div class="row align-items-center py-5">
            <div class="col-lg-7">
                <div class="hero-badge animate-slideDown">
                    <i class="bi bi-patch-check-fill text-warning"></i>
                    <span>Platform Donasi Terbuka & Terpercaya Indonesia</span>
                </div>
                <h1 class="hero-title">
                    Ulurkan Tangan,<br>
                    <span class="text-warning">Wujudkan Harapan Sesama</span>
                </h1>
                <p class="hero-subtitle mb-4">
                    Bantu saudara kita yang terdampak bencana banjir, gempa bumi, anak-anak yang membutuhkan pendidikan layak, dan akses air bersih di pelosok Indonesia.
                </p>

                <!-- Search Box -->
                <form action="kampanye.php" method="GET" class="d-flex gap-2 mb-4" style="max-width: 520px;">
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-white border-0 text-success"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-0 ps-0" placeholder="Cari bencana, bantuan, atau program..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-warning px-4 fw-bold">
                            Cari
                        </button>
                    </div>
                </form>

                <div class="d-flex gap-3 flex-wrap">
                    <a href="kampanye.php" class="btn btn-light btn-lg px-4 fw-bold shadow-sm">
                        <i class="bi bi-heart-fill text-danger me-1"></i> Donasi Sekarang
                    </a>
                    <?php if (is_logged_in()): ?>
                        <a href="tambah_kampanye.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                            <i class="bi bi-plus-circle me-1"></i> Galang Dana
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                            <i class="bi bi-person-plus me-1"></i> Buat Akun
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hero Side Image with Real Photo -->
            <div class="col-lg-5 d-none d-lg-block hero-image-col">
                <div class="hero-image-card">
                    <img src="assets/img/hero-relawan.jpg" alt="Relawan Peduli Indonesia" class="img-fluid">
                </div>
                <div class="hero-float-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-check-lg fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-success small mb-0">+Rp 2.500.000 Masuk</div>
                            <small class="text-muted" style="font-size: 0.72rem;">Bantuan Korban Banjir Kalimantan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="container" style="margin-bottom: 2rem;">
    <div class="stats-section">
        <div class="row g-3">
            <div class="col-4">
                <div class="stat-item">
                    <div class="stat-icon"><i class="bi bi-megaphone-fill"></i></div>
                    <div class="stat-number">120+</div>
                    <div class="stat-label">Kampanye Berjalan</div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-item">
                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-number">15.400+</div>
                    <div class="stat-label">Orang Baik Berdonasi</div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-item">
                    <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div class="stat-number">Rp 3,8 M+</div>
                    <div class="stat-label">Dana Tersalurkan</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kategori Bantuan Nyata -->
<section class="container py-4">
    <div class="text-center mb-4">
        <h2 class="section-title">Fokus Bantuan <span class="text-hijau">PeduliBersama</span></h2>
        <p class="section-subtitle">Program bantuan nyata di berbagai pelosok nusantara</p>
    </div>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="kampanye.php?kategori=Bencana+Alam" class="category-card h-100 shadow-sm text-decoration-none">
                <img src="assets/img/banjir.jpg" alt="Korban Banjir" class="img-fluid rounded-3 mb-2" style="height: 110px; width: 100%; object-fit: cover;">
                <h6 class="fw-bold mb-1 text-dark text-decoration-none">Korban Banjir</h6>
                <small class="text-muted d-block text-decoration-none">Pangan & Evakuasi</small>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="kampanye.php?kategori=Bencana+Alam" class="category-card h-100 shadow-sm text-decoration-none">
                <img src="assets/img/gempa.jpg" alt="Gempa Bumi" class="img-fluid rounded-3 mb-2" style="height: 110px; width: 100%; object-fit: cover;">
                <h6 class="fw-bold mb-1 text-dark text-decoration-none">Gempa Bumi</h6>
                <small class="text-muted d-block text-decoration-none">Hunian & Obat</small>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="kampanye.php?kategori=Pendidikan" class="category-card h-100 shadow-sm text-decoration-none">
                <img src="assets/img/pendidikan.jpg" alt="Pendidikan" class="img-fluid rounded-3 mb-2" style="height: 110px; width: 100%; object-fit: cover;">
                <h6 class="fw-bold mb-1 text-dark text-decoration-none">Bantuan Pendidikan</h6>
                <small class="text-muted d-block text-decoration-none">Beasiswa & Seragam</small>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="kampanye.php?kategori=Air+Bersih" class="category-card h-100 shadow-sm text-decoration-none">
                <img src="assets/img/air-bersih.jpg" alt="Air Bersih" class="img-fluid rounded-3 mb-2" style="height: 110px; width: 100%; object-fit: cover;">
                <h6 class="fw-bold mb-1 text-dark text-decoration-none">Air Bersih</h6>
                <small class="text-muted d-block text-decoration-none">Sumur & Sanitasi</small>
            </a>
        </div>
    </div>
</section>

<!-- Kampanye Unggulan -->
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
        <div>
            <h2 class="section-title mb-1">Kampanye <span class="text-hijau">Mendesak</span></h2>
            <p class="text-muted mb-0">Mereka membutuhkan uluran tangan kita saat ini</p>
        </div>
        <a href="kampanye.php" class="btn btn-outline-donasi">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-4">
        <?php foreach ($kampanye_list as $k): ?>
            <?php
            $progress = hitung_progress($k['dana_terkumpul'] ?? 0, $k['target_dana'] ?? 1);
            $img_src = resolve_image_url($k['gambar_url'] ?? '', 'assets/img/banjir.jpg');
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-kampanye h-100 shadow-sm">
                    <div class="card-img-wrapper">
                        <img src="<?= htmlspecialchars($img_src) ?>" class="card-img-top" alt="<?= htmlspecialchars($k['judul']) ?>" loading="lazy">
                        <span class="badge bg-success badge-status"><?= htmlspecialchars($k['kategori'] ?? 'Bencana Alam') ?></span>
                    </div>
                    <div class="card-body d-flex flex-column p-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= format_tanggal($k['created_at']) ?></small>
                            <span class="badge bg-light text-success border border-success-subtle"><i class="bi bi-shield-check"></i> Terverifikasi</span>
                        </div>
                        <h5 class="card-title fw-bold mb-2">
                            <a href="detail_kampanye.php?id=<?= urlencode($k['id']) ?>" class="text-dark text-decoration-none hover-primary">
                                <?= htmlspecialchars($k['judul']) ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted small flex-grow-1">
                            <?= htmlspecialchars(mb_substr($k['deskripsi'] ?? '', 0, 110)) ?>...
                        </p>
                        <div class="mt-auto pt-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold text-hijau"><?= format_rupiah($k['dana_terkumpul'] ?? 0) ?></span>
                                <span class="fw-bold text-dark"><?= number_format($progress, 0) ?>%</span>
                            </div>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?= $progress ?>%;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Target: <?= format_rupiah($k['target_dana'] ?? 0) ?></small>
                                <a href="detail_kampanye.php?id=<?= urlencode($k['id']) ?>" class="btn btn-donasi btn-sm px-3 fw-semibold">
                                    Donasi <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Mengapa PeduliBersama -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Mengapa Memilih <span class="text-hijau">PeduliBersama</span>?</h2>
            <p class="section-subtitle">Transparansi, kecepatan, dan ketulusan menyalurkan kebaikan Anda</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-light h-100">
                    <div class="stat-icon mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem;"><i class="bi bi-shield-lock-fill"></i></div>
                    <h5 class="fw-bold">100% Aman & Terpercaya</h5>
                    <p class="text-muted small mb-0">Setiap kampanye melewati proses verifikasi data dan kebutuhan langsung di lapangan oleh tim kami.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-light h-100">
                    <div class="stat-icon mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem;"><i class="bi bi-lightning-charge-fill"></i></div>
                    <h5 class="fw-bold">Penyaluran Realtime</h5>
                    <p class="text-muted small mb-0">Update dana masuk dan donatur dapat dipantau secara realtime berkat integrasi teknologi Supabase.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 rounded-4 bg-light h-100">
                    <div class="stat-icon mx-auto mb-3" style="width:64px;height:64px;font-size:1.6rem;"><i class="bi bi-receipt"></i></div>
                    <h5 class="fw-bold">Laporan Terbuka</h5>
                    <p class="text-muted small mb-0">Donatur mendapatkan transparansi penggunaan dana secara berkala hingga program selesai.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="container py-5">
    <div class="cta-section">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-lg-8">
                <h3 class="fw-bold mb-2 text-white">Punya Program Sosial atau Membutuhkan Bantuan?</h3>
                <p class="mb-0 text-white-50">Mulai galang dana bersama ribuan orang baik di Indonesia secara gratis dan mudah.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <?php if (is_logged_in()): ?>
                    <a href="tambah_kampanye.php" class="btn btn-warning btn-lg px-4 fw-bold shadow">
                        <i class="bi bi-plus-circle me-1"></i> Buat Kampanye
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-warning btn-lg px-4 fw-bold shadow">
                        <i class="bi bi-person-plus me-1"></i> Daftar Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
