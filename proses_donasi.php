<?php
ob_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validasi input
$kampanye_id = sanitize($_POST['kampanye_id'] ?? '');
$nama = sanitize($_POST['nama'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$jumlah = intval($_POST['jumlah'] ?? 0);
$metode = sanitize($_POST['metode_pembayaran'] ?? 'qris');
$pesan = sanitize($_POST['pesan'] ?? '');

$errors = [];

if (empty($kampanye_id)) {
    $errors[] = 'ID Kampanye tidak valid.';
}
if (empty($nama) || strlen($nama) < 2) {
    $errors[] = 'Nama donatur harus diisi (minimal 2 karakter).';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Alamat email tidak valid.';
}
if ($jumlah < 1000) {
    $errors[] = 'Jumlah donasi minimal Rp 1.000.';
}

if (!empty($errors)) {
    set_flash('danger', implode('<br>', $errors));
    header('Location: detail_kampanye.php?id=' . urlencode($kampanye_id));
    exit;
}

$kampanye = fetch_single_campaign($kampanye_id);
if (!$kampanye) {
    set_flash('danger', 'Kampanye tidak ditemukan.');
    header('Location: kampanye.php');
    exit;
}

if (($kampanye['status'] ?? 'aktif') !== 'aktif') {
    set_flash('warning', 'Kampanye ini sudah tidak menerima donasi karena target sudah terpenuhi.');
    header('Location: detail_kampanye.php?id=' . urlencode($kampanye_id));
    exit;
}

// Kirim ke Supabase
$donasi_data = [
    'kampanye_id' => $kampanye_id,
    'nama_donatur' => $nama,
    'email_donatur' => $email,
    'jumlah' => $jumlah,
    'metode_pembayaran' => $metode,
    'pesan' => $pesan,
    'status' => 'berhasil'
];

$donasi_response = supabase_request('donasi', 'POST', $donasi_data);

if ($donasi_response['status'] === 201 || $donasi_response['status'] === 200) {
    $dana_baru = ($kampanye['dana_terkumpul'] ?? 0) + $jumlah;
    $update_data = ['dana_terkumpul' => $dana_baru];
    if ($dana_baru >= ($kampanye['target_dana'] ?? 0)) {
        $update_data['status'] = 'selesai';
    }
    // Update dana_terkumpul dan status di tabel kampanye Supabase
    supabase_request('kampanye?id=eq.' . urlencode($kampanye_id), 'PATCH', $update_data, SUPABASE_SERVICE_KEY);
    
    set_flash('success', 'Alhamdulillah! Terima kasih <strong>' . htmlspecialchars($nama) . '</strong>, donasi sebesar <strong>' . format_rupiah($jumlah) . '</strong> berhasil disalurkan dan tercatat di database.');
} else {
    $errMsg = $donasi_response['data']['message'] ?? $donasi_response['data']['details'] ?? 'Gagal menyimpan ke database Supabase.';
    set_flash('danger', 'Gagal memproses donasi: ' . htmlspecialchars($errMsg));
}

header('Location: detail_kampanye.php?id=' . urlencode($kampanye_id));
exit;
