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

$kampanye = fetch_single_campaign($id);
if (!$kampanye) {
    set_flash('danger', 'Kampanye tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hapus relasi donasi terlebih dahulu
    supabase_request('donasi?kampanye_id=eq.' . urlencode($id), 'DELETE', null, $token);
    // Hapus kampanye
    $res = supabase_request('kampanye?id=eq.' . urlencode($id), 'DELETE', null, $token);

    set_flash('success', 'Kampanye berhasil dihapus.');
    header('Location: dashboard.php');
    exit;
}
$page_title = 'Hapus Kampanye - PeduliBersama';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-kampanye border-0 shadow-lg p-4 p-md-5 text-center">
                <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:64px;height:64px;font-size:1.8rem;">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <h3 class="fw-bold text-danger mb-2">Hapus Kampanye?</h3>
                <p class="text-muted mb-4">
                    Apakah Anda yakin ingin menghapus kampanye <strong>"<?= htmlspecialchars($kampanye['judul']) ?>"</strong>? Tindakan ini tidak dapat dibatalkan.
                </p>

                <form method="POST" action="hapus_kampanye.php?id=<?= urlencode($id) ?>">
                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold">
                            <i class="bi bi-trash me-1"></i> Ya, Hapus Sekarang
                        </button>
                        <a href="dashboard.php" class="btn btn-light px-4 py-2 fw-semibold border">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
