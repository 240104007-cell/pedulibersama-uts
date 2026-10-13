<?php
$page_title = 'Daftar Kampanye Kemanusiaan - PeduliBersama';
require_once 'includes/header.php';

// Filter & pencarian
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'aktif';
$kategori_filter = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : '';

$kampanye_list = fetch_campaigns($search, $status_filter, 50);

if ($kategori_filter) {
    $kampanye_list = array_values(array_filter($kampanye_list, fn($k) =>
        strcasecmp($k['kategori'] ?? '', $kategori_filter) === 0
    ));
}
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill mb-2">
            <i class="bi bi-heart-pulse-fill me-1"></i> Donasi Terbuka
        </div>
        <h1 class="fw-bold section-title">Pilih & Salurkan <span class="text-hijau">Kebaikan Anda</span></h1>
        <p class="text-muted section-subtitle">Setiap donasi disalurkan secara transparan dan langsung berdampak bagi penerima manfaat</p>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card card-kampanye p-4 mb-4 border-0 shadow-sm">
        <form method="GET" action="kampanye.php" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-bold small text-muted">Cari Kampanye</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Kata kunci judul atau bencana..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-muted">Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="" <?= empty($kategori_filter) ? 'selected' : '' ?>>Semua Kategori</option>
                    <option value="Bencana Alam" <?= $kategori_filter === 'Bencana Alam' ? 'selected' : '' ?>>Bencana Alam</option>
                    <option value="Pendidikan" <?= $kategori_filter === 'Pendidikan' ? 'selected' : '' ?>>Pendidikan</option>
                    <option value="Air Bersih" <?= $kategori_filter === 'Air Bersih' ? 'selected' : '' ?>>Air Bersih</option>
                    <option value="Kesehatan" <?= $kategori_filter === 'Kesehatan' ? 'selected' : '' ?>>Kesehatan</option>
                    <option value="Sosial" <?= $kategori_filter === 'Sosial' ? 'selected' : '' ?>>Sosial / Yatim</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="semua" <?= $status_filter === 'semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="aktif" <?= $status_filter === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-donasi w-100 py-2 fw-semibold">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan
                </button>
            </div>
        </form>
    </div>

    <?php if ($search || $kategori_filter || ($status_filter && $status_filter !== 'aktif')): ?>
        <div class="d-flex align-items-center mb-4 flex-wrap gap-2">
            <span class="badge bg-light text-dark border p-2">
                Ditemukan <strong><?= count($kampanye_list) ?></strong> kampanye
            </span>
            <a href="kampanye.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-x-circle me-1"></i> Reset Filter
            </a>
        </div>
    <?php endif; ?>

    <?php if (empty($kampanye_list)): ?>
        <div class="text-center py-5 my-4 bg-white rounded-4 shadow-sm p-5">
            <div class="stat-icon mx-auto mb-3" style="width:70px;height:70px;font-size:2rem;">
                <i class="bi bi-search"></i>
            </div>
            <h4 class="fw-bold">Kampanye Tidak Ditemukan</h4>
            <p class="text-muted mb-4">Coba cari dengan kata kunci lain atau reset filter pencarian Anda.</p>
            <a href="kampanye.php" class="btn btn-donasi px-4">Lihat Semua Kampanye</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($kampanye_list as $k): ?>
                <?php
                $progress = hitung_progress($k['dana_terkumpul'] ?? 0, $k['target_dana'] ?? 1);
                $img_src = resolve_image_url($k['gambar_url'] ?? '', 'assets/img/banjir.jpg');
                $is_selesai = ($k['status'] ?? '') === 'selesai';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-kampanye h-100 shadow-sm border-0">
                        <div class="card-img-wrapper">
                            <img src="<?= htmlspecialchars($img_src) ?>" class="card-img-top" alt="<?= htmlspecialchars($k['judul']) ?>" loading="lazy">
                            <span class="badge bg-<?= $is_selesai ? 'secondary' : 'success' ?> badge-status">
                                <?= htmlspecialchars($k['kategori'] ?? 'Bencana Alam') ?>
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= format_tanggal($k['created_at']) ?></small>
                                <span class="badge bg-<?= $is_selesai ? 'secondary' : 'success-subtle text-success' ?>">
                                    <?= $is_selesai ? 'Tercapai' : 'Aktif' ?>
                                </span>
                            </div>
                            <h5 class="card-title fw-bold mb-2">
                                <a href="detail_kampanye.php?id=<?= urlencode($k['id']) ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars($k['judul']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <?= htmlspecialchars(mb_substr($k['deskripsi'] ?? '', 0, 110)) ?>...
                            </p>
                            <div class="mt-auto pt-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-bold text-hijau"><?= format_rupiah($k['dana_terkumpul'] ?? 0) ?></span>
                                    <span class="fw-bold"><?= number_format($progress, 0) ?>%</span>
                                </div>
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?= $progress ?>%;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Target: <?= format_rupiah($k['target_dana'] ?? 0) ?></small>
                                    <a href="detail_kampanye.php?id=<?= urlencode($k['id']) ?>" class="btn btn-donasi btn-sm px-3 fw-semibold">
                                        <?= $is_selesai ? 'Lihat Detail' : 'Bantu Sekarang' ?> <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
