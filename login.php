<?php
ob_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format alamat email tidak valid.';
    } else {
        $result = supabase_login($email, $password);

        if ($result['status'] === 200 && isset($result['data']['access_token'])) {
            $_SESSION['access_token'] = $result['data']['access_token'];
            $_SESSION['user'] = [
                'id' => $result['data']['user']['id'],
                'email' => $result['data']['user']['email'],
                'name' => $result['data']['user']['user_metadata']['full_name'] ?? 'Pengguna PeduliBersama'
            ];
            // Simpan session cookie sebagai fallback untuk serverless (Vercel)
            setcookie('pb_session', json_encode([
                'user' => $_SESSION['user'],
                'access_token' => $_SESSION['access_token']
            ]), [
                'expires' => time() + 86400 * 7,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            set_flash('success', 'Selamat datang kembali, ' . htmlspecialchars($_SESSION['user']['name']) . '!');
            header('Location: dashboard.php');
            exit;
        } else {
            $msg = $result['data']['error_description'] ?? $result['data']['msg'] ?? 'Email atau password salah. Silakan periksa kembali.';
            $error = htmlspecialchars($msg);
        }
    }
}
$page_title = 'Masuk Akun - PeduliBersama';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 60vh;">
        <div class="col-md-6 col-lg-5">
            <div class="card card-kampanye p-4 p-md-5 border-0 shadow-lg">
                <div class="text-center mb-4">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 1.8rem;">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <h3 class="fw-bold mt-3 mb-1">Masuk ke Akun</h3>
                    <p class="text-muted small">Kelola kampanye penggalangan dana Anda di PeduliBersama</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small text-muted">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($email) ?>" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold small text-muted">Kata Sandi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-donasi w-100 py-3 fw-bold fs-6 shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                    </button>
                </form>

                <div class="text-center mt-4 pt-2 border-top">
                    <p class="text-muted small mb-0">
                        Belum punya akun? <a href="register.php" class="text-hijau fw-bold text-decoration-none">Daftar sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
