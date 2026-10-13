<?php
ob_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Semua data wajib diisi.';
    } elseif (strlen($name) < 3) {
        $error = 'Nama lengkap minimal 3 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        $result = supabase_register($email, $password, $name);

        if ($result['status'] === 200 || $result['status'] === 201) {
            set_flash('success', 'Pendaftaran akun berhasil! Silakan masuk dengan email dan kata sandi Anda.');
            header('Location: login.php');
            exit;
        } else {
            $msg = $result['data']['msg'] ?? $result['data']['message'] ?? $result['data']['error_description'] ?? 'Registrasi gagal.';
            $error = 'Gagal mendaftar: ' . htmlspecialchars($msg);
        }
    }
}
$page_title = 'Daftar Akun Baru - PeduliBersama';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-7 col-lg-5">
            <div class="card card-kampanye p-4 p-md-5 border-0 shadow-lg">
                <div class="text-center mb-4">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <h3 class="fw-bold mt-3 mb-1">Daftar Akun Baru</h3>
                    <p class="text-muted small">Bergabung bersama ribuan relawan & penggalang dana di PeduliBersama</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold small text-muted">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Nama lengkap Anda" value="<?= htmlspecialchars($name) ?>" required minlength="3">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small text-muted">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold small text-muted">Kata Sandi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-bold small text-muted">Konfirmasi Kata Sandi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi kata sandi" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-donasi w-100 py-3 fw-bold fs-6 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Buat Akun Sekarang
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <p class="text-muted small mb-0">
                        Sudah punya akun? <a href="login.php" class="text-hijau fw-bold text-decoration-none">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
