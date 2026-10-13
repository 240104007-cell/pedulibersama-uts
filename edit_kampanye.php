<?php
ob_start();
require_once 'includes/functions.php';
require_once 'config/database.php';
require_login();

$user = get_current_user_data();
$token = get_access_token();

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';
if (empty($id)) {
    set_flash('danger', 'ID kampanye tidak valid.');
    header('Location: dashboard.php');
    exit;
}

// Ambil data kampanye
$kampanye = fetch_single_campaign($id);
if (!$kampanye) {
    set_flash('danger', 'Kampanye tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

$error = '';
$judul = $kampanye['judul'] ?? '';
$deskripsi = $kampanye['deskripsi'] ?? '';
$target_dana = $kampanye['target_dana'] ?? 0;
$kategori = $kampanye['kategori'] ?? 'Umum';
$status = $kampanye['status'] ?? 'aktif';
$gambar_url = $kampanye['gambar_url'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $target_dana = intval($_POST['target_dana'] ?? 0);
    $kategori = sanitize($_POST['kategori'] ?? 'Umum');
    $status = sanitize($_POST['status'] ?? 'aktif');
    $gambar_url = sanitize($_POST['gambar_url'] ?? '');

    if (empty($judul)) {
        $error = 'Judul kampanye harus diisi.';
    } elseif (strlen($judul) < 5) {
        $error = 'Judul kampanye minimal 5 karakter.';
    } elseif (empty($deskripsi)) {
        $error = 'Deskripsi kampanye harus diisi.';
    } elseif (strlen($deskripsi) < 20) {
        $error = 'Deskripsi kampanye minimal 20 karakter.';
    } elseif ($target_dana < 10000) {
        $error = 'Target dana minimal Rp 10.000.';
    } else {
        $update_data = [
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'target_dana' => $target_dana,
            'kategori' => $kategori,
            'status' => $status,
            'gambar_url' => $gambar_url
        ];

        $response = supabase_request('kampanye?id=eq.' . urlencode($id), 'PATCH', $update_data, $token);

        set_flash('success', 'Kampanye berhasil diperbarui!');
        header('Location: dashboard.php');
        exit;
    }
}
$page_title = 'Edit Kampanye - PeduliBersama';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-kampanye border-0 shadow-lg p-4 p-md-5">
                <div class="d-flex align-items-center mb-4">
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-circle me-3" style="width:36px;height:36px;">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h3 class="fw-bold mb-0">Edit Kampanye</h3>
                        <p class="text-muted small mb-0">Perbarui informasi penggalangan dana Anda</p>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit_kampanye.php?id=<?= urlencode($id) ?>">
                    <div class="mb-3">
                        <label for="judul" class="form-label fw-bold small text-muted">Judul Kampanye <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="judul" name="judul" value="<?= htmlspecialchars($judul) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="kategori" class="form-label fw-bold small text-muted">Kategori</label>
                            <select class="form-select form-select-lg" id="kategori" name="kategori">
                                <option value="Bencana Alam" <?= $kategori === 'Bencana Alam' ? 'selected' : '' ?>>Bencana Alam</option>
                                <option value="Pendidikan" <?= $kategori === 'Pendidikan' ? 'selected' : '' ?>>Pendidikan</option>
                                <option value="Air Bersih" <?= $kategori === 'Air Bersih' ? 'selected' : '' ?>>Air Bersih</option>
                                <option value="Kesehatan" <?= $kategori === 'Kesehatan' ? 'selected' : '' ?>>Kesehatan</option>
                                <option value="Sosial" <?= $kategori === 'Sosial' ? 'selected' : '' ?>>Sosial</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label fw-bold small text-muted">Status</label>
                            <select class="form-select form-select-lg" id="status" name="status">
                                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="dibatalkan" <?= $status === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="target_dana" class="form-label fw-bold small text-muted">Target Dana (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-lg" id="target_dana" name="target_dana" value="<?= htmlspecialchars((string)$target_dana) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">URL Foto</label>
                        <input type="text" class="form-control" id="gambar_url" name="gambar_url" value="<?= htmlspecialchars($gambar_url) ?>">
                    </div>

                    <div class="mb-4">
                        <label for="deskripsi" class="form-label fw-bold small text-muted">Deskripsi Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" required><?= htmlspecialchars($deskripsi) ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-donasi px-5 py-3 fw-bold fs-6 shadow-sm flex-grow-1">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="dashboard.php" class="btn btn-light py-3 px-4 fw-semibold border">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
