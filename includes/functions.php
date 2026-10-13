<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restore session dari cookie di environment serverless (Vercel) jika session hilang
if ((!isset($_SESSION['user']) || !isset($_SESSION['access_token'])) && !empty($_COOKIE['pb_session'])) {
    $savedSession = json_decode($_COOKIE['pb_session'], true);
    if (is_array($savedSession) && isset($savedSession['user'], $savedSession['access_token'])) {
        $_SESSION['user'] = $savedSession['user'];
        $_SESSION['access_token'] = $savedSession['access_token'];
    }
}

/**
 * Cek apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user']) && isset($_SESSION['access_token']);
}

/**
 * Redirect jika belum login
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get current user data
 */
function get_current_user_data() {
    return $_SESSION['user'] ?? null;
}

/**
 * Get access token
 */
function get_access_token() {
    return $_SESSION['access_token'] ?? null;
}

/**
 * Format rupiah
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Format tanggal Indonesia
 */
function format_tanggal($date) {
    if (empty($date)) return date('d F Y');
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    try {
        $d = new DateTime($date);
        return $d->format('d') . ' ' . $bulan[(int)$d->format('m')] . ' ' . $d->format('Y');
    } catch (Exception $e) {
        return date('d/m/Y');
    }
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) return $input;
    return htmlspecialchars(strip_tags(trim((string)$input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Hitung persentase progress donasi
 */
function hitung_progress($terkumpul, $target) {
    $target = (float)$target;
    $terkumpul = (float)$terkumpul;
    if ($target <= 0) return 0;
    $persen = ($terkumpul / $target) * 100;
    return min($persen, 100);
}

/**
 * Resolusi URL Gambar yang valid
 */
function resolve_image_url($url, $default_fallback = 'assets/img/banjir.jpg') {
    if (empty($url)) return $default_fallback;
    if (file_exists(__DIR__ . '/../' . $url)) {
        return $url;
    }
    if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
        return $url;
    }
    return $default_fallback;
}

/**
 * Kampanye Default dengan Foto Nyata Indonesia
 */
function get_default_campaigns() {
    return [
        [
            'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'judul' => 'Tanggap Darurat Banjir Bandang Kalimantan',
            'deskripsi' => "Banjir bandang merendam ratusan rumah warga di Kalimantan. Ribuan keluarga kehilangan tempat tinggal dan membutuhkan bantuan darurat makanan, pakaian bersih, obat-obatan, dan tempat penampungan sementara.\n\nMari ulurkan tangan untuk meringankan beban saudara-saudara kita yang sedang diuji musibah ini.",
            'kategori' => 'Bencana Alam',
            'target_dana' => 75000000,
            'dana_terkumpul' => 48500000,
            'gambar_url' => 'assets/img/banjir.jpg',
            'status' => 'aktif',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
        ],
        [
            'id' => 'b2c3d4e5-f6a7-8901-bcde-f12345678901',
            'judul' => 'Peduli Gempa: Bangun Hunian Layak Korban Bencana',
            'deskripsi' => "Gempa bumi merusak pemukiman warga di wilayah pelosok. Mari bersama-sama membangun kembali hunian sementara dan fasilitas sanitasi bagi saudara-saudara kita yang terdampak agar mereka dapat beraktivitas kembali dengan aman.\n\nSetiap bantuan material dan donasi Anda akan langsung disalurkan ke posko darurat.",
            'kategori' => 'Bencana Alam',
            'target_dana' => 120000000,
            'dana_terkumpul' => 89000000,
            'gambar_url' => 'assets/img/gempa.jpg',
            'status' => 'aktif',
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
        ],
        [
            'id' => 'c3d4e5f6-a7b8-9012-cdef-123456789012',
            'judul' => 'Beasiswa & Seragam Sekolah Anak Pedalaman NTT',
            'deskripsi' => "Program bantuan biaya pendidikan, seragam sekolah baru, buku pelajaran, dan perlengkapan sekolah untuk anak-anak di pelosok Nusa Tenggara Timur agar tetap dapat menuntut ilmu dengan senyum ceria.\n\nMasa depan bangsa ada di tangan mereka. Jangan biarkan mimpi mereka terhenti karena keterbatasan biaya.",
            'kategori' => 'Pendidikan',
            'target_dana' => 50000000,
            'dana_terkumpul' => 34200000,
            'gambar_url' => 'assets/img/pendidikan.jpg',
            'status' => 'aktif',
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
        ],
        [
            'id' => 'd4e5f6a7-b8c9-0123-def1-234567890123',
            'judul' => 'Aksi Nyata: Akses Air Bersih & Sanitasi Desa Kekeringan',
            'deskripsi' => "Pembangunan sumur bor, instalasi tandon, dan pipanisasi air bersih untuk warga desa di Gunungkidul yang mengalami krisis kekeringan parah setiap musim kemarau.\n\nAir bersih adalah hak dasar setiap insan. Mari wujudkan sumber kehidupan berkelanjutan bagi warga.",
            'kategori' => 'Air Bersih',
            'target_dana' => 65000000,
            'dana_terkumpul' => 52100000,
            'gambar_url' => 'assets/img/air-bersih.jpg',
            'status' => 'aktif',
            'created_at' => date('Y-m-d H:i:s', strtotime('-20 days'))
        ],
        [
            'id' => 'e5f6a7b8-c9d0-1234-ef12-345678901234',
            'judul' => 'Bantuan Operasi & Nutrisi Balita Kurang Mampu',
            'deskripsi' => "Bantuan darurat biaya tindakan operasi dan pemenuhan gizi balita prasejahtera yang berjuang melawan kelainan jantung bawaan dan penyakit kronis.\n\nMari temani perjuangan mereka menuju kesembuhan.",
            'kategori' => 'Kesehatan',
            'target_dana' => 45000000,
            'dana_terkumpul' => 28000000,
            'gambar_url' => 'assets/img/kesehatan.jpg',
            'status' => 'aktif',
            'created_at' => date('Y-m-d H:i:s', strtotime('-25 days'))
        ],
        [
            'id' => 'f6a7b8c9-d0e1-2345-f123-456789012345',
            'judul' => 'Santunan Pangan & Kasih Sayang Anak Yatim Piatu',
            'deskripsi' => "Program pemenuhan kebutuhan pangan pokok, gizi seimbang, dan santunan rutin untuk anak-anak yatim piatu di panti asuhan binaan se-Jabodetabek.\n\nSenyum mereka adalah berkah bagi kita semua.",
            'kategori' => 'Sosial',
            'target_dana' => 40000000,
            'dana_terkumpul' => 31500000,
            'gambar_url' => 'assets/img/yatim.jpg',
            'status' => 'aktif',
            'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
        ]
    ];
}

/**
 * Donasi Default untuk Kampanye Contoh
 */
function get_default_donations($campaign_id = null) {
    $donations = [
        'a1b2c3d4-e5f6-7890-abcd-ef1234567890' => [
            ['nama_donatur' => 'Rizki Pratama', 'jumlah' => 1500000, 'pesan' => 'Semoga saudara-saudara kita lekas pulih dari bencana.', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hours'))],
            ['nama_donatur' => 'Siti Rahmawati', 'jumlah' => 500000, 'pesan' => 'Bismillah, sedikit bantuan dari kami sekeluarga.', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))],
            ['nama_donatur' => 'Hamba Allah', 'jumlah' => 1000000, 'pesan' => 'Semoga berkah dan meringankan beban.', 'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours'))]
        ],
        'b2c3d4e5-f6a7-8901-bcde-f12345678901' => [
            ['nama_donatur' => 'Keluarga Wijaya', 'jumlah' => 2000000, 'pesan' => 'Tetap kuat dan bersabar saudara-saudaraku!', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
            ['nama_donatur' => 'Dewi Lestari', 'jumlah' => 750000, 'pesan' => 'Semoga hunian segera terbangun kembali.', 'created_at' => date('Y-m-d H:i:s', strtotime('-8 hours'))]
        ],
        'c3d4e5f6-a7b8-9012-cdef-123456789012' => [
            ['nama_donatur' => 'Budi Santoso', 'jumlah' => 1000000, 'pesan' => 'Semangat belajar untuk adik-adik di NTT!', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))],
            ['nama_donatur' => 'Alumni Peduli', 'jumlah' => 2500000, 'pesan' => 'Untuk masa depan generasi penerus bangsa.', 'created_at' => date('Y-m-d H:i:s', strtotime('-12 hours'))]
        ],
        'd4e5f6a7-b8c9-0123-def1-234567890123' => [
            ['nama_donatur' => 'Komunitas Berbagi', 'jumlah' => 3000000, 'pesan' => 'Semoga segera mengalir air bersih yang berkah.', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))]
        ]
    ];

    if ($campaign_id && isset($donations[$campaign_id])) {
        return $donations[$campaign_id];
    }
    return [
        ['nama_donatur' => 'Hamba Allah', 'jumlah' => 250000, 'pesan' => 'Semoga bermanfaat bagi sesama.', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hours'))]
    ];
}

/**
 * Ambil daftar kampanye: Supabase lebih dulu, fallback ke data contoh bila tabel belum dibuat
 */
function fetch_campaigns($search = '', $status = 'aktif', $limit = null) {
    if (function_exists('supabase_request')) {
        $query = 'kampanye?select=*&order=created_at.desc';
        if ($status && $status !== 'semua') {
            $query .= '&status=eq.' . urlencode($status);
        }
        if ($search) {
            $query .= '&or=(judul.ilike.%25' . urlencode($search) . '%25,deskripsi.ilike.%25' . urlencode($search) . '%25)';
        }
        if ($limit) {
            $query .= '&limit=' . intval($limit);
        }

        $res = supabase_request($query);
        if ($res['status'] === 200 && is_array($res['data']) && !isset($res['data']['code']) && !empty($res['data'])) {
            return $res['data'];
        }
    }

    $list = get_default_campaigns();
    if ($status && $status !== 'semua') {
        $list = array_values(array_filter($list, fn($k) => ($k['status'] ?? 'aktif') === $status));
    }
    if ($search) {
        $s = strtolower($search);
        $list = array_values(array_filter($list, fn($k) =>
            str_contains(strtolower($k['judul']), $s) || str_contains(strtolower($k['deskripsi']), $s)
        ));
    }
    if ($limit) {
        $list = array_slice($list, 0, $limit);
    }
    return $list;
}

/**
 * Ambil satu kampanye berdasarkan ID
 */
function fetch_single_campaign($id) {
    if (function_exists('supabase_request')) {
        $res = supabase_request('kampanye?id=eq.' . urlencode($id) . '&select=*');
        if ($res['status'] === 200 && is_array($res['data']) && !empty($res['data']) && !isset($res['data']['code'])) {
            return $res['data'][0];
        }
    }

    $defaults = get_default_campaigns();
    foreach ($defaults as $k) {
        if ($k['id'] === $id) {
            return $k;
        }
    }
    return $defaults[0] ?? null;
}

/**
 * Ambil daftar donasi untuk kampanye
 */
function fetch_donations($campaign_id) {
    if (function_exists('supabase_request')) {
        $res = supabase_request('donasi?kampanye_id=eq.' . urlencode($campaign_id) . '&select=*&order=created_at.desc');
        if ($res['status'] === 200 && is_array($res['data']) && !empty($res['data']) && !isset($res['data']['code'])) {
            return $res['data'];
        }
    }

    return get_default_donations($campaign_id);
}
