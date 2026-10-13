<?php
$page_title = 'Detail Kampanye - PeduliBersama';
require_once 'includes/functions.php';
require_once 'config/database.php';

$id = isset($_GET['id']) ? sanitize($_GET['id']) : '';
if (empty($id)) {
    require_once 'includes/header.php';
    echo '<div class="container py-5 text-center"><div class="p-5 bg-white rounded-4 shadow-sm"><h4 class="fw-bold mb-3">Kampanye tidak ditemukan</h4><a href="kampanye.php" class="btn btn-donasi">Kembali ke Daftar Kampanye</a></div></div>';
    require_once 'includes/footer.php';
    exit;
}

$kampanye = fetch_single_campaign($id);
if (!$kampanye) {
    require_once 'includes/header.php';
    echo '<div class="container py-5 text-center"><div class="p-5 bg-white rounded-4 shadow-sm"><h4 class="fw-bold mb-3">Kampanye tidak ditemukan</h4><a href="kampanye.php" class="btn btn-donasi">Kembali ke Daftar Kampanye</a></div></div>';
    require_once 'includes/footer.php';
    exit;
}

$page_title = htmlspecialchars($kampanye['judul']) . ' - PeduliBersama';
$donasi_list = fetch_donations($id);
$progress = hitung_progress($kampanye['dana_terkumpul'] ?? 0, $kampanye['target_dana'] ?? 1);
$img_src = resolve_image_url($kampanye['gambar_url'] ?? '', 'assets/img/banjir.jpg');
$is_aktif = ($kampanye['status'] ?? 'aktif') === 'aktif';

require_once 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-hijau fw-semibold">Beranda</a></li>
            <li class="breadcrumb-item"><a href="kampanye.php" class="text-decoration-none text-hijau fw-semibold">Kampanye</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;"><?= htmlspecialchars($kampanye['judul']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Kolom Kiri: Detail & Deskripsi -->
        <div class="col-lg-8">
            <div class="card card-kampanye overflow-hidden border-0 shadow-sm mb-4">
                <div class="position-relative">
                    <img src="<?= htmlspecialchars($img_src) ?>" class="w-100" style="max-height: 440px; object-fit: cover;" alt="<?= htmlspecialchars($kampanye['judul']) ?>">
                    <div class="position-absolute top-0 start-0 m-3">
                        <span class="badge bg-success px-3 py-2 rounded-pill fs-6 shadow-sm">
                            <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($kampanye['kategori'] ?? 'Bencana Alam') ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="badge bg-<?= $is_aktif ? 'success-subtle text-success' : 'secondary' ?> border px-3 py-1 rounded-pill">
                            <i class="bi bi-circle-fill me-1 small"></i> Status: <?= htmlspecialchars(ucfirst($kampanye['status'] ?? 'aktif')) ?>
                        </span>
                        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> Dibuat pada <?= format_tanggal($kampanye['created_at']) ?></span>
                    </div>

                    <h1 class="fw-bold mb-4 fs-2"><?= htmlspecialchars($kampanye['judul']) ?></h1>

                    <!-- Box Capaian Dana -->
                    <div class="bg-light rounded-4 p-4 mb-4 border">
                        <div class="d-flex justify-content-between align-items-baseline mb-2">
                            <div>
                                <span class="fs-2 fw-bold text-hijau"><?= format_rupiah($kampanye['dana_terkumpul'] ?? 0) ?></span>
                                <span class="text-muted small ms-1">terkumpul dari target</span>
                            </div>
                            <span class="fs-4 fw-bold text-dark"><?= number_format($progress, 0) ?>%</span>
                        </div>
                        <div class="progress mb-3" style="height: 14px; border-radius: 50px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?= $progress ?>%;"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small flex-wrap gap-2">
                            <span><i class="bi bi-bullseye text-danger me-1"></i> Target: <strong><?= format_rupiah($kampanye['target_dana'] ?? 0) ?></strong></span>
                            <span><i class="bi bi-people-fill text-success me-1"></i> <strong id="donatur-count-stat"><?= count($donasi_list) ?></strong> Donatur</span>
                        </div>
                    </div>

                    <!-- Cerita / Deskripsi Lengkap -->
                    <h4 class="fw-bold mb-3"><i class="bi bi-file-text me-2 text-success"></i>Tentang Kampanye</h4>
                    <div class="text-secondary lh-lg mb-4" style="font-size: 1.05rem; white-space: pre-line;">
                        <?= nl2br(htmlspecialchars($kampanye['deskripsi'] ?? '')) ?>
                    </div>

                    <!-- Verifikasi Keamanan -->
                    <div class="p-3 bg-success-subtle rounded-3 border border-success-subtle d-flex align-items-center gap-3">
                        <i class="bi bi-shield-fill-check fs-2 text-success"></i>
                        <div>
                            <strong class="text-success d-block">Donasi Aman & Bebas Penipuan</strong>
                            <small class="text-muted">PeduliBersama menerapkan enkripsi SSL, audit penyaluran dana, dan verifikasi identitas penggalang dana.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Donatur Nyata & Realtime -->
            <div class="card card-kampanye border-0 shadow-sm">
                <div class="card-header bg-white border-0 p-4 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-chat-heart-fill text-danger me-2"></i>Doa & Dukungan Donatur
                        <span id="donatur-count-badge" class="badge bg-success ms-2"><?= count($donasi_list) ?></span>
                    </h5>
                    <span class="badge bg-light text-success border border-success-subtle">
                        <i class="bi bi-broadcast me-1 text-danger"></i> Live Realtime
                    </span>
                </div>
                <div class="card-body p-4 pt-2" id="donatur-list">
                    <?php if (empty($donasi_list)): ?>
                        <div class="text-center py-4 text-muted" id="empty-donatur">
                            <i class="bi bi-heart display-6 d-block mb-2 text-muted"></i>
                            Belum ada donasi. Jadilah orang baik pertama yang berdonasi!
                        </div>
                    <?php else: ?>
                        <?php foreach ($donasi_list as $d): ?>
                            <div class="d-flex align-items-start border-bottom py-3 donatur-item">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="text-dark"><?= htmlspecialchars($d['nama_donatur'] ?? 'Orang Baik') ?></strong>
                                        <span class="text-hijau fw-bold"><?= format_rupiah($d['jumlah'] ?? 0) ?></span>
                                    </div>
                                    <?php if (!empty($d['pesan'])): ?>
                                        <p class="text-secondary small mb-1 mt-1 fst-italic">"<?= htmlspecialchars($d['pesan']) ?>"</p>
                                    <?php endif; ?>
                                    <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i><?= format_tanggal($d['created_at']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Donasi Elegan -->
        <div class="col-lg-4">
            <div class="card card-kampanye sticky-top border-0 shadow-lg" style="top: 90px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-icon" style="width: 32px; height: 32px;">
                            <i class="bi bi-heart-fill text-warning"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Donasi Online</h5>
                    </div>

                    <?php if (!$is_aktif): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle me-1"></i> Kampanye ini sudah <strong><?= htmlspecialchars($kampanye['status']) ?></strong> dan target telah tercapai. Terima kasih atas partisipasi Anda!
                        </div>
                    <?php else: ?>
                        <form action="proses_donasi.php" method="POST" id="form-donasi" class="needs-validation">
                            <input type="hidden" name="kampanye_id" value="<?= htmlspecialchars($kampanye['id']) ?>">

                            <!-- Pilihan Nominal Cepat -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Pilih Nominal Donasi</label>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill nominal-btn" data-nominal="10000">10rb</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill nominal-btn" data-nominal="25000">25rb</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill nominal-btn" data-nominal="50000">50rb</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill nominal-btn active" data-nominal="100000">100rb</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill nominal-btn" data-nominal="250000">250rb</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill nominal-btn" data-nominal="500000">500rb</button>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">Rp</span>
                                    <input type="number" class="form-control form-control-lg fw-bold text-success" id="jumlah" name="jumlah" value="100000" min="1000" step="1000" required>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Minimal donasi Rp 1.000</small>
                            </div>

                            <div class="mb-3">
                                <label for="nama" class="form-label fw-bold small text-muted">Nama Donatur</label>
                                <input type="text" class="form-control" id="nama" name="nama" placeholder="Contoh: Budi Santoso / Hamba Allah" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold small text-muted">Alamat Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" required>
                                <div class="form-text" style="font-size:0.75rem;">Kwitansi donasi akan dikirimkan ke email ini</div>
                            </div>

                            <div class="mb-3">
                                <label for="metode" class="form-label fw-bold small text-muted">Metode Pembayaran</label>
                                <select class="form-select" id="metode" name="metode_pembayaran">
                                    <option value="qris" selected>QRIS (GoPay, OVO, Dana, ShopeePay)</option>
                                    <option value="transfer_bca">Transfer Bank BCA</option>
                                    <option value="transfer_mandiri">Transfer Bank Mandiri</option>
                                    <option value="transfer_bri">Transfer Bank BRI</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="pesan" class="form-label fw-bold small text-muted">Doa & Pesan Dukungan (Opsional)</label>
                                <textarea class="form-control" id="pesan" name="pesan" rows="2" placeholder="Tuliskan harapan dan doa terbaik Anda..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-donasi w-100 py-3 fw-bold fs-6 shadow-sm">
                                <i class="bi bi-heart-fill text-danger me-2"></i> Lanjutkan Pembayaran
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Bagikan Kampanye -->
                    <hr class="my-4">
                    <div class="text-center">
                        <small class="fw-bold text-muted d-block mb-2">Bantu Sebarkan Kampanye Ini</small>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode('Mari bantu: ' . $kampanye['judul'] . ' - Klik link: ') ?>" target="_blank" class="btn btn-success btn-sm rounded-circle p-2" title="WhatsApp" style="width:38px;height:38px;">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="https://t.me/share/url?url=<?= urlencode('https://pedulibersama.id/detail.php?id=' . $kampanye['id']) ?>&text=<?= urlencode($kampanye['judul']) ?>" target="_blank" class="btn btn-info btn-sm text-white rounded-circle p-2" title="Telegram" style="width:38px;height:38px;">
                                <i class="bi bi-telegram"></i>
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php" target="_blank" class="btn btn-primary btn-sm rounded-circle p-2" title="Facebook" style="width:38px;height:38px;">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <button type="button" class="btn btn-secondary btn-sm rounded-circle p-2" title="Salin Tautan" style="width:38px;height:38px;" onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan berhasil disalin ke clipboard!');">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Supabase JS CDN untuk Realtime Updates -->
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<script>
// Tombol Cepat Nominal
document.querySelectorAll('.nominal-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.nominal-btn').forEach(b => b.classList.remove('active', 'btn-success', 'text-white'));
        this.classList.add('active');
        document.getElementById('jumlah').value = this.dataset.nominal;
    });
});

// Realtime Listener menggunakan Supabase JS
(function() {
    const SUPABASE_URL = '<?= SUPABASE_URL ?>';
    const SUPABASE_KEY = '<?= SUPABASE_KEY ?>';
    const kampanyeId = '<?= $kampanye['id'] ?>';

    if (typeof supabase !== 'undefined' && SUPABASE_URL && SUPABASE_KEY) {
        try {
            const { createClient } = supabase;
            const client = createClient(SUPABASE_URL, SUPABASE_KEY);

            client
                .channel('realtime-donasi-' + kampanyeId)
                .on('postgres_changes', {
                    event: 'INSERT',
                    schema: 'public',
                    table: 'donasi',
                    filter: 'kampanye_id=eq.' + kampanyeId
                }, (payload) => {
                    const d = payload.new;
                    const list = document.getElementById('donatur-list');
                    const emptyMsg = document.getElementById('empty-donatur');
                    if (emptyMsg) emptyMsg.remove();

                    const formatRupiah = (val) => 'Rp ' + Number(val).toLocaleString('id-ID');
                    const itemHtml = `
                        <div class="d-flex align-items-start border-bottom py-3 donatur-item animate-slideDown" style="background: #e8f5e9;">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:48px;height:48px;font-size:1.2rem;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-dark">${d.nama_donatur || 'Orang Baik'}</strong>
                                    <span class="text-hijau fw-bold">${formatRupiah(d.jumlah || 0)}</span>
                                </div>
                                ${d.pesan ? `<p class="text-secondary small mb-1 mt-1 fst-italic">"${d.pesan}"</p>` : ''}
                                <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i>Baru saja</small>
                            </div>
                        </div>
                    `;
                    list.insertAdjacentHTML('afterbegin', itemHtml);

                    const badge = document.getElementById('donatur-count-badge');
                    if (badge) badge.textContent = parseInt(badge.textContent || 0) + 1;
                    const stat = document.getElementById('donatur-count-stat');
                    if (stat) stat.textContent = parseInt(stat.textContent || 0) + 1;
                })
                .subscribe();
        } catch (e) {
            console.warn('Realtime Supabase subscription skipped:', e);
        }
    }
})();
</script>

<?php require_once 'includes/footer.php'; ?>
