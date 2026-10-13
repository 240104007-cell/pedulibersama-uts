<?php
$page_title = 'Dashboard Penggalang Dana - PeduliBersama';
require_once 'includes/header.php';
require_login();

$user = get_current_user_data();
$token = get_access_token();

// Ambil kampanye milik user dari Supabase
$response = supabase_request('kampanye?user_id=eq.' . urlencode($user['id']) . '&select=*&order=created_at.desc', 'GET', null, $token);
$kampanye_list = ($response['status'] === 200 && is_array($response['data']) && !isset($response['data']['code'])) ? $response['data'] : [];

// Hitung statistik
$total_kampanye = count($kampanye_list);
$total_terkumpul = 0;
$kampanye_aktif = 0;
foreach ($kampanye_list as $k) {
    $total_terkumpul += ($k['dana_terkumpul'] ?? 0);
    if (($k['status'] ?? '') === 'aktif') $kampanye_aktif++;
}
?>

<div class="container py-5">
    <!-- Header Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1">Halo, <?= htmlspecialchars($user['name'] ?? 'Penggalang Dana') ?>! 👋</h2>
            <p class="text-muted mb-0">Kelola dan pantau kampanye kebaikan Anda di <strong>PeduliBersama</strong></p>
        </div>
        <a href="tambah_kampanye.php" class="btn btn-donasi px-4 py-2 shadow-sm fw-semibold">
            <i class="bi bi-plus-circle me-1"></i> Buat Kampanye Baru
        </a>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="card card-kampanye p-4 border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="bg-success-subtle text-success rounded-4 d-flex align-items-center justify-content-center me-3" style="width:58px;height:58px;font-size:1.5rem;">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-dark"><?= $total_kampanye ?></div>
                        <small class="text-muted fw-semibold">Total Kampanye</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-kampanye p-4 border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="bg-warning-subtle text-warning rounded-4 d-flex align-items-center justify-content-center me-3" style="width:58px;height:58px;font-size:1.5rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-dark"><?= $kampanye_aktif ?></div>
                        <small class="text-muted fw-semibold">Kampanye Aktif</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-kampanye p-4 border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="bg-success text-white rounded-4 d-flex align-items-center justify-content-center me-3" style="width:58px;height:58px;font-size:1.5rem;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold text-success"><?= format_rupiah($total_terkumpul) ?></div>
                        <small class="text-muted fw-semibold">Total Terkumpul</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel / List Kampanye User -->
    <div class="card card-kampanye border-0 shadow-sm">
        <div class="card-header bg-white border-0 p-4 pb-2 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-task text-success me-2"></i>Daftar Kampanye Anda</h5>
            <span class="badge bg-light text-muted border"><?= count($kampanye_list) ?> Kampanye</span>
        </div>
        <div class="card-body p-4 pt-2">
            <?php if (empty($kampanye_list)): ?>
                <div class="text-center py-5">
                    <img src="assets/img/hero-relawan.jpg" class="rounded-4 shadow-sm mb-3" style="width:160px;height:110px;object-fit:cover;" alt="Mulai Galang Dana">
                    <h5 class="fw-bold mb-2">Anda Belum Memiliki Kampanye</h5>
                    <p class="text-muted mb-4 small">Mulai galang dana untuk membantu keluarga, korban bencana, atau program pendidikan.</p>
                    <a href="tambah_kampanye.php" class="btn btn-donasi px-4">
                        <i class="bi bi-plus-circle me-1"></i> Buat Kampanye Pertama
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">Foto</th>
                                <th>Judul & Kategori</th>
                                <th>Target Dana</th>
                                <th>Terkumpul</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kampanye_list as $k): ?>
                                <?php
                                $progress = hitung_progress($k['dana_terkumpul'] ?? 0, $k['target_dana'] ?? 1);
                                $img_src = resolve_image_url($k['gambar_url'] ?? '', 'assets/img/banjir.jpg');
                                ?>
                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars($img_src) ?>" class="rounded-3 shadow-sm" style="width:70px;height:50px;object-fit:cover;" alt="Foto Kampanye">
                                    </td>
                                    <td>
                                        <strong class="d-block text-dark"><?= htmlspecialchars($k['judul']) ?></strong>
                                        <small class="badge bg-light text-secondary border"><?= htmlspecialchars($k['kategori'] ?? 'Umum') ?></small>
                                        <small class="text-muted ms-2"><?= format_tanggal($k['created_at']) ?></small>
                                    </td>
                                    <td><strong><?= format_rupiah($k['target_dana'] ?? 0) ?></strong></td>
                                    <td>
                                        <span class="text-hijau fw-bold d-block"><?= format_rupiah($k['dana_terkumpul'] ?? 0) ?></span>
                                        <div class="progress" style="height:6px;width:120px;">
                                            <div class="progress-bar" style="width:<?= $progress ?>%;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($k['status'] ?? 'aktif') === 'aktif' ? 'success' : 'secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($k['status'] ?? 'aktif')) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="detail_kampanye.php?id=<?= urlencode($k['id']) ?>" class="btn btn-sm btn-outline-info rounded-pill" title="Lihat">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit_kampanye.php?id=<?= urlencode($k['id']) ?>" class="btn btn-sm btn-outline-warning rounded-pill" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="hapus_kampanye.php?id=<?= urlencode($k['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
