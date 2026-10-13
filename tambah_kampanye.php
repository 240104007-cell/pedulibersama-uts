<?php
ob_start();
require_once 'includes/functions.php';
require_once 'config/database.php';
require_login();

$user = get_current_user_data();
$token = get_access_token();

$error = '';
$judul = '';
$deskripsi = '';
$target_dana = '';
$kategori = 'Bencana Alam';
$gambar_url = 'assets/img/banjir.jpg';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');
    $target_dana = intval($_POST['target_dana'] ?? 0);
    $kategori = sanitize($_POST['kategori'] ?? 'Bencana Alam');
    $gambar_url = sanitize($_POST['gambar_url'] ?? 'assets/img/banjir.jpg');

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
        $data = [
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'target_dana' => $target_dana,
            'dana_terkumpul' => 0,
            'kategori' => $kategori ?: 'Umum',
            'status' => 'aktif',
            'user_id' => $user['id'],
            'gambar_url' => $gambar_url ?: 'assets/img/banjir.jpg'
        ];

        $response = supabase_request('kampanye', 'POST', $data, $token);

        if ($response['status'] === 201 || $response['status'] === 200) {
            set_flash('success', 'Alhamdulillah! Kampanye Anda berhasil dibuat dan langsung tayang.');
            header('Location: dashboard.php');
            exit;
        } else {
            // Jika Supabase tabel belum dibuat atau ada kendala RLS, tetap beri respons yang jelas
            $msg = $response['data']['message'] ?? 'Gagal menyimpan ke database Supabase. Pastikan tabel kampanye sudah dibuat di SQL Editor.';
            $error = htmlspecialchars($msg);
        }
    }
}
$page_title = 'Buat Kampanye Baru - PeduliBersama';
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
                        <h3 class="fw-bold mb-0">Buat Kampanye Donasi Baru</h3>
                        <p class="text-muted small mb-0">Mulai galang dana dengan transparansi dan foto yang jelas</p>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="tambah_kampanye.php">
                    <div class="mb-3">
                        <label for="judul" class="form-label fw-bold small text-muted">Judul Kampanye <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="judul" name="judul" placeholder="Contoh: Bantuan Darurat Korban Banjir Bandang" value="<?= htmlspecialchars($judul) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="kategori" class="form-label fw-bold small text-muted">Kategori Bantuan</label>
                            <select class="form-select form-select-lg" id="kategori" name="kategori">
                                <option value="Bencana Alam" <?= $kategori === 'Bencana Alam' ? 'selected' : '' ?>>Bencana Alam</option>
                                <option value="Pendidikan" <?= $kategori === 'Pendidikan' ? 'selected' : '' ?>>Pendidikan</option>
                                <option value="Air Bersih" <?= $kategori === 'Air Bersih' ? 'selected' : '' ?>>Air Bersih</option>
                                <option value="Kesehatan" <?= $kategori === 'Kesehatan' ? 'selected' : '' ?>>Kesehatan</option>
                                <option value="Sosial" <?= $kategori === 'Sosial' ? 'selected' : '' ?>>Sosial / Panti Asuhan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="target_dana" class="form-label fw-bold small text-muted">Target Dana (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text fw-bold">Rp</span>
                                <input type="number" class="form-control" id="target_dana" name="target_dana" placeholder="50000000" value="<?= htmlspecialchars((string)$target_dana) ?>" min="10000" step="10000" required>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Cepat Foto Relevan Asli -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Pilih Foto Asli yang Relevan</label>
                        <div class="row g-2 mb-2">
                            <div class="col-4 col-md-2">
                                <div class="border rounded p-1 text-center cursor-pointer preset-photo" data-url="assets/img/banjir.jpg" style="cursor:pointer;">
                                    <img src="assets/img/banjir.jpg" class="w-100 rounded mb-1" style="height:55px;object-fit:cover;">
                                    <small style="font-size:0.65rem;" class="d-block text-truncate">Banjir</small>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="border rounded p-1 text-center cursor-pointer preset-photo" data-url="assets/img/gempa.jpg" style="cursor:pointer;">
                                    <img src="assets/img/gempa.jpg" class="w-100 rounded mb-1" style="height:55px;object-fit:cover;">
                                    <small style="font-size:0.65rem;" class="d-block text-truncate">Gempa</small>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="border rounded p-1 text-center cursor-pointer preset-photo" data-url="assets/img/pendidikan.jpg" style="cursor:pointer;">
                                    <img src="assets/img/pendidikan.jpg" class="w-100 rounded mb-1" style="height:55px;object-fit:cover;">
                                    <small style="font-size:0.65rem;" class="d-block text-truncate">Pendidikan</small>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="border rounded p-1 text-center cursor-pointer preset-photo" data-url="assets/img/air-bersih.jpg" style="cursor:pointer;">
                                    <img src="assets/img/air-bersih.jpg" class="w-100 rounded mb-1" style="height:55px;object-fit:cover;">
                                    <small style="font-size:0.65rem;" class="d-block text-truncate">Air Bersih</small>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="border rounded p-1 text-center cursor-pointer preset-photo" data-url="assets/img/kesehatan.jpg" style="cursor:pointer;">
                                    <img src="assets/img/kesehatan.jpg" class="w-100 rounded mb-1" style="height:55px;object-fit:cover;">
                                    <small style="font-size:0.65rem;" class="d-block text-truncate">Medis</small>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="border rounded p-1 text-center cursor-pointer preset-photo" data-url="assets/img/yatim.jpg" style="cursor:pointer;">
                                    <img src="assets/img/yatim.jpg" class="w-100 rounded mb-1" style="height:55px;object-fit:cover;">
                                    <small style="font-size:0.65rem;" class="d-block text-truncate">Anak Yatim</small>
                                </div>
                            </div>
                        </div>
                        <input type="text" class="form-control" id="gambar_url" name="gambar_url" placeholder="assets/img/... atau URL foto HTTPS" value="<?= htmlspecialchars($gambar_url) ?>">
                    </div>

                    <div class="mb-4">
                        <label for="deskripsi" class="form-label fw-bold small text-muted">Deskripsi & Cerita Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="6" placeholder="Ceritakan secara detail tentang kondisi penerima bantuan, rencana penggunaan dana, dan harapan program..." required><?= htmlspecialchars($deskripsi) ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-donasi px-5 py-3 fw-bold fs-6 shadow-sm flex-grow-1">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Terbitkan Kampanye
                        </button>
                        <a href="dashboard.php" class="btn btn-light py-3 px-4 fw-semibold border">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.preset-photo').forEach(box => {
    box.addEventListener('click', function() {
        document.querySelectorAll('.preset-photo').forEach(b => b.classList.remove('border-success', 'bg-light'));
        this.classList.add('border-success', 'bg-light');
        document.getElementById('gambar_url').value = this.dataset.url;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
