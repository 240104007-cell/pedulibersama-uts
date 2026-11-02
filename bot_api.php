<?php
declare(strict_types=1);

/**
 * API Endpoint Khusus Integrasi n8n & Telegram Bot
 * Platform Donasi PeduliBersama
 * 
 * Endpoint ini membaca database Supabase yang sama dengan website,
 * sehingga data kampanye dan donasi selalu 100% konsisten.
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Bot-Token');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

// Base URL Website untuk tautan donasi & detail
$webBaseUrl = getenv('WEB_BASE_URL') ?: 'https://donasi-platform.vercel.app';

// Validasi Token Opsional (jika BOT_API_KEY diatur di environment)
$botApiKey = getenv('BOT_API_KEY') ?: '';
if (!empty($botApiKey)) {
    $clientToken = $_SERVER['HTTP_X_BOT_TOKEN'] ?? $_GET['token'] ?? '';
    if ($clientToken !== $botApiKey) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized: Token bot tidak valid.'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Tangani input GET / POST
$action = $_GET['action'] ?? $_POST['action'] ?? 'help';

// Format progress bar visual berbasis teks untuk pesan Telegram
if (!function_exists('buat_text_progress_bar')) {
    function buat_text_progress_bar(float $percent, int $length = 10): string {
        $percentClamped = max(0, min(100, $percent));
        $filledLength = (int) round(($percentClamped / 100) * $length);
        $emptyLength = $length - $filledLength;
        return str_repeat('🟩', $filledLength) . str_repeat('⬜', $emptyLength);
    }
}

switch ($action) {
    /**
     * 0. Action 'chat': Pemroses Utama Pesan Telegram untuk n8n
     * Menerima { text, chat_id, from_name } dan mengembalikan respon instan
     * Endpoint: POST /bot_api.php?action=chat atau GET /bot_api.php?action=chat&text=...
     */
    case 'chat':
    case 'process':
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $rawText = trim($body['text'] ?? $_POST['text'] ?? $_GET['text'] ?? '');
        $chatId = $body['chat_id'] ?? $_POST['chat_id'] ?? $_GET['chat_id'] ?? '';
        $fromName = trim($body['from_name'] ?? $_POST['from_name'] ?? $_GET['from_name'] ?? 'Sahabat');
        if (empty($fromName)) $fromName = 'Sahabat';

        $lower = strtolower($rawText);
        $replyText = '';
        $replyMarkup = [];

        // 1. Menu Start / Halo
        if ($lower === '' || str_starts_with($lower, '/start') || $lower === 'halo' || $lower === 'hai' || $lower === 'menu' || $lower === '/bantuan') {
            $replyText = "👋 *Halo, {$fromName}!*\n\n";
            $replyText .= "Selamat datang di Bot Resmi *PeduliBersama* — Platform Donasi & Penggalangan Dana Terbuka.\n\n";
            $replyText .= "Melalui bot ini, Anda dapat melihat program donasi aktif, memantau capaian dana secara real-time, dan berdonasi langsung ke website resmi kami.\n\n";
            $replyText .= "Silakan pilih menu di bawah ini:";

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '📋 Daftar Program Donasi', 'callback_data' => 'cmd_program']
                    ],
                    [
                        ['text' => '🌐 Kunjungi Website Resmi', 'url' => $webBaseUrl],
                        ['text' => '❓ Bantuan', 'callback_data' => 'cmd_help']
                    ]
                ]
            ];
        }
        // 2. Daftar Program Donasi
        elseif (str_starts_with($lower, '/program') || $lower === 'cmd_program' || str_contains($lower, 'program') || str_contains($lower, 'donasi')) {
            $campaigns = fetch_campaigns('', 'aktif', 6);
            $replyText = "🌟 *PROGRAM DONASI TERSEDIA* 🌟\n";
            $replyText .= "Platform PeduliBersama (Live Supabase)\n\n";

            if (empty($campaigns)) {
                $replyText .= "Saat ini belum ada program donasi aktif.\nSilakan kunjungi website kami untuk info terbaru.";
            } else {
                foreach ($campaigns as $idx => $c) {
                    $progress = hitung_progress($c['dana_terkumpul'] ?? 0, $c['target_dana'] ?? 1);
                    $detailUrl = $webBaseUrl . '/detail_kampanye.php?id=' . urlencode($c['id']);
                    $progressBar = buat_text_progress_bar($progress, 8);
                    $nomor = $idx + 1;

                    $replyText .= "*{$nomor}. {$c['judul']}*\n";
                    $replyText .= "📂 Kategori: _{$c['kategori']}_\n";
                    $replyText .= "💰 Terkumpul: *" . format_rupiah($c['dana_terkumpul'] ?? 0) . "* (" . round($progress, 1) . "%)\n";
                    $replyText .= "🎯 Target: " . format_rupiah($c['target_dana'] ?? 0) . "\n";
                    $replyText .= "{$progressBar}\n";
                    $replyText .= "👉 [Donasi Sekarang di Web]({$detailUrl})\n\n";
                }
                $replyText .= "ℹ️ _Ketik nama program untuk mencari atau klik link donasi di atas._";
            }

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '🌐 Buka Semua Program di Website', 'url' => $webBaseUrl . '/kampanye.php']
                    ]
                ]
            ];
        }
        // 3. Fallback / Pesan Tidak Dikenali
        else {
            $replyText = "❓ *Pesan Tidak Dikenali*\n\n";
            $replyText .= "Maaf, saya belum memahami pesan Anda: \"_{$rawText}_\"\n\n";
            $replyText .= "Silakan pilih salah satu opsi bantuan berikut:";

            $replyMarkup = [
                'inline_keyboard' => [
                    [
                        ['text' => '📋 Lihat Daftar Program', 'callback_data' => 'cmd_program']
                    ],
                    [
                        ['text' => '🌐 Kunjungi Website Resmi', 'url' => $webBaseUrl]
                    ]
                ]
            ];
        }

        echo json_encode([
            'status' => 'success',
            'chat_id' => $chatId,
            'from_name' => $fromName,
            'raw_text' => $rawText,
            'reply_text' => $replyText,
            'reply_markup' => $replyMarkup
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        break;

    /**
     * 1. Ambil daftar program donasi aktif
     * Endpoint: GET /bot_api.php?action=list atau action=programs
     */
    case 'list':
    case 'programs':
    case 'campaigns':
        $kategori = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $campaigns = fetch_campaigns('', 'aktif', $limit);
        
        if (!empty($kategori)) {
            $campaigns = array_values(array_filter($campaigns, function($k) use ($kategori) {
                return strcasecmp($k['kategori'] ?? '', $kategori) === 0;
            }));
        }

        $formatted = [];
        $telegramMessage = "🌟 *PROGRAM DONASI TERSEDIA* 🌟\n";
        $telegramMessage .= "Platform PeduliBersama\n\n";

        if (empty($campaigns)) {
            $telegramMessage .= "Saat ini belum ada program donasi aktif.\nSilakan kunjungi website kami untuk informasi terbaru.";
        } else {
            foreach ($campaigns as $idx => $c) {
                $progress = hitung_progress($c['dana_terkumpul'] ?? 0, $c['target_dana'] ?? 1);
                $detailUrl = $webBaseUrl . '/detail_kampanye.php?id=' . urlencode($c['id']);
                $progressBar = buat_text_progress_bar($progress, 8);
                
                $formatted[] = [
                    'id' => $c['id'],
                    'judul' => $c['judul'],
                    'kategori' => $c['kategori'] ?? 'Umum',
                    'target_dana' => (int)($c['target_dana'] ?? 0),
                    'target_dana_rupiah' => format_rupiah($c['target_dana'] ?? 0),
                    'dana_terkumpul' => (int)($c['dana_terkumpul'] ?? 0),
                    'dana_terkumpul_rupiah' => format_rupiah($c['dana_terkumpul'] ?? 0),
                    'progress_percent' => round($progress, 1),
                    'progress_bar_text' => $progressBar,
                    'status' => $c['status'] ?? 'aktif',
                    'url_detail' => $detailUrl
                ];

                $nomor = $idx + 1;
                $telegramMessage .= "*{$nomor}. {$c['judul']}*\n";
                $telegramMessage .= "📂 Kategori: _{$c['kategori']}_\n";
                $telegramMessage .= "💰 Terkumpul: *" . format_rupiah($c['dana_terkumpul'] ?? 0) . "* (" . round($progress, 1) . "%)\n";
                $telegramMessage .= "🎯 Target: " . format_rupiah($c['target_dana'] ?? 0) . "\n";
                $telegramMessage .= "{$progressBar}\n";
                $telegramMessage .= "👉 [Donasi Sekarang]({$detailUrl})\n\n";
            }
            $telegramMessage .= "ℹ️ _Ketik /detail <nomor/id> untuk info lengkap atau klik link di atas._";
        }

        echo json_encode([
            'status' => 'success',
            'total' => count($formatted),
            'data' => $formatted,
            'telegram_message' => $telegramMessage
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        break;

    /**
     * 2. Ambil detail satu program donasi berdasarkan ID
     * Endpoint: GET /bot_api.php?action=detail&id=...
     */
    case 'detail':
        $id = sanitize($_GET['id'] ?? $_POST['id'] ?? '');
        if (empty($id)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Parameter id wajib disertakan.'
            ], JSON_PRETTY_PRINT);
            break;
        }

        $campaign = fetch_single_campaign($id);
        if (!$campaign) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Program donasi tidak ditemukan.'
            ], JSON_PRETTY_PRINT);
            break;
        }

        $donations = fetch_donations($id);
        $progress = hitung_progress($campaign['dana_terkumpul'] ?? 0, $campaign['target_dana'] ?? 1);
        $detailUrl = $webBaseUrl . '/detail_kampanye.php?id=' . urlencode($campaign['id']);
        $progressBar = buat_text_progress_bar($progress, 10);

        $telegramMessage = "📋 *DETAIL PROGRAM DONASI*\n\n";
        $telegramMessage .= "🏷️ *{$campaign['judul']}*\n";
        $telegramMessage .= "📂 Kategori: _{$campaign['kategori']}_\n";
        $telegramMessage .= "📅 Tanggal Dibuat: " . format_tanggal($campaign['created_at']) . "\n\n";
        $telegramMessage .= "📝 *Deskripsi:*\n" . $campaign['deskripsi'] . "\n\n";
        $telegramMessage .= "📊 *Capaian Dana:*\n";
        $telegramMessage .= "💰 Terkumpul: *" . format_rupiah($campaign['dana_terkumpul'] ?? 0) . "* (" . round($progress, 1) . "%)\n";
        $telegramMessage .= "🎯 Target: *" . format_rupiah($campaign['target_dana'] ?? 0) . "*\n";
        $telegramMessage .= "👥 Donatur: *" . count($donations) . " orang*\n";
        $telegramMessage .= "{$progressBar}\n\n";
        $telegramMessage .= "🔗 *Link Donasi Resmi:*\n{$detailUrl}\n\n";
        $telegramMessage .= "❤️ _Mari salurkan kebaikan Anda melalui website kami._";

        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $campaign['id'],
                'judul' => $campaign['judul'],
                'deskripsi' => $campaign['deskripsi'],
                'kategori' => $campaign['kategori'] ?? 'Umum',
                'target_dana' => (int)($campaign['target_dana'] ?? 0),
                'target_dana_rupiah' => format_rupiah($campaign['target_dana'] ?? 0),
                'dana_terkumpul' => (int)($campaign['dana_terkumpul'] ?? 0),
                'dana_terkumpul_rupiah' => format_rupiah($campaign['dana_terkumpul'] ?? 0),
                'progress_percent' => round($progress, 1),
                'progress_bar_text' => $progressBar,
                'total_donatur' => count($donations),
                'donasi_terbaru' => array_slice($donations, 0, 5),
                'status' => $campaign['status'] ?? 'aktif',
                'url_detail' => $detailUrl
            ],
            'telegram_message' => $telegramMessage
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        break;

    /**
     * 3. Cari program donasi berdasarkan kata kunci
     * Endpoint: GET /bot_api.php?action=search&q=...
     */
    case 'search':
        $query = sanitize($_GET['q'] ?? $_POST['q'] ?? '');
        if (empty($query)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Parameter kata kunci pencarian (q) wajib disertakan.'
            ], JSON_PRETTY_PRINT);
            break;
        }

        $results = fetch_campaigns($query, 'semua', 10);
        $formatted = [];
        $telegramMessage = "🔍 *HASIL PENCARIAN:* \"_{$query}_\"\n\n";

        if (empty($results)) {
            $telegramMessage .= "Maaf, program donasi dengan kata kunci tersebut tidak ditemukan.\n";
            $telegramMessage .= "Silakan ketik /program untuk melihat semua daftar bantuan yang tersedia.";
        } else {
            foreach ($results as $idx => $c) {
                $progress = hitung_progress($c['dana_terkumpul'] ?? 0, $c['target_dana'] ?? 1);
                $detailUrl = $webBaseUrl . '/detail_kampanye.php?id=' . urlencode($c['id']);
                $formatted[] = [
                    'id' => $c['id'],
                    'judul' => $c['judul'],
                    'kategori' => $c['kategori'] ?? 'Umum',
                    'target_dana_rupiah' => format_rupiah($c['target_dana'] ?? 0),
                    'dana_terkumpul_rupiah' => format_rupiah($c['dana_terkumpul'] ?? 0),
                    'status' => $c['status'] ?? 'aktif',
                    'url_detail' => $detailUrl
                ];
                $nomor = $idx + 1;
                $telegramMessage .= "*{$nomor}. {$c['judul']}*\n";
                $telegramMessage .= "💰 Terkumpul: " . format_rupiah($c['dana_terkumpul'] ?? 0) . " / " . format_rupiah($c['target_dana'] ?? 0) . "\n";
                $telegramMessage .= "👉 [Buka Detail & Donasi]({$detailUrl})\n\n";
            }
        }

        echo json_encode([
            'status' => 'success',
            'query' => $query,
            'total' => count($formatted),
            'data' => $formatted,
            'telegram_message' => $telegramMessage
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        break;

    /**
     * 4. Kirim donasi langsung melalui Bot Telegram (masuk ke Supabase)
     * Endpoint: POST /bot_api.php?action=donate
     */
    case 'donate':
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $kampanye_id = sanitize($body['kampanye_id'] ?? '');
        $nama = sanitize($body['nama'] ?? $body['name'] ?? 'Donatur Telegram');
        $email = sanitize($body['email'] ?? 'donatur.telegram@pedulibersama.id');
        $jumlah = (int)($body['jumlah'] ?? $body['amount'] ?? 0);
        $metode = sanitize($body['metode_pembayaran'] ?? 'qris_telegram');
        $pesan = sanitize($body['pesan'] ?? 'Donasi via Telegram Bot');

        if (empty($kampanye_id) || $jumlah < 1000) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'kampanye_id wajib diisi dan jumlah minimal donasi adalah Rp 1.000.'
            ], JSON_PRETTY_PRINT);
            break;
        }

        $campaign = fetch_single_campaign($kampanye_id);
        if (!$campaign) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Program donasi tidak ditemukan.'
            ], JSON_PRETTY_PRINT);
            break;
        }

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
            $dana_baru = ($campaign['dana_terkumpul'] ?? 0) + $jumlah;
            $update_data = ['dana_terkumpul' => $dana_baru];
            if ($dana_baru >= ($campaign['target_dana'] ?? 0)) {
                $update_data['status'] = 'selesai';
            }
            supabase_request('kampanye?id=eq.' . urlencode($kampanye_id), 'PATCH', $update_data, SUPABASE_SERVICE_KEY);

            $telegramMessage = "✅ *DONASI BERHASIL DICATAT!*\n\n";
            $telegramMessage .= "Terima kasih, *{$nama}*!\n";
            $telegramMessage .= "Donasi sebesar *" . format_rupiah($jumlah) . "* untuk program *{$campaign['judul']}* telah berhasil masuk ke database platform kami.\n\n";
            $telegramMessage .= "Semoga menjadi amal jariyah yang membawa berkah. Aamiin! 🤲\n";
            $telegramMessage .= "Pantau realtime di: " . $webBaseUrl . '/detail_kampanye.php?id=' . urlencode($kampanye_id);

            echo json_encode([
                'status' => 'success',
                'message' => 'Donasi berhasil dicatat.',
                'data' => [
                    'nama_donatur' => $nama,
                    'jumlah' => $jumlah,
                    'jumlah_rupiah' => format_rupiah($jumlah),
                    'kampanye' => $campaign['judul']
                ],
                'telegram_message' => $telegramMessage
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal mencatat donasi ke database Supabase.',
                'supabase_error' => $donasi_response['data'] ?? null
            ], JSON_PRETTY_PRINT);
        }
        break;

    /**
     * 5. Pesan Bantuan & Informasi Bot (Fallback Response)
     */
    case 'help':
    default:
        $welcomeText = "👋 *Halo! Selamat Datang di Bot PeduliBersama*\n\n";
        $welcomeText .= "Saya adalah asisten virtual resmi dari platform donasi *PeduliBersama*.\n\n";
        $welcomeText .= "📌 *Perintah yang dapat Anda gunakan:*\n";
        $welcomeText .= "• /program - Menampilkan daftar program donasi aktif\n";
        $welcomeText .= "• /cari <kata kunci> - Mencari bantuan bencana / pendidikan\n";
        $welcomeText .= "• /detail <id> - Melihat rincian capaian donasi & target\n";
        $welcomeText .= "• /website - Mengunjungi website resmi platform\n";
        $welcomeText .= "• /bantuan - Panduan bantuan interaksi\n\n";
        $welcomeText .= "🌐 Kunjungi website kami untuk transparansi penuh & donasi online:\n{$webBaseUrl}";

        $fallbackText = "❓ *Pesan Tidak Dikenali*\n\n";
        $fallbackText .= "Maaf, saya belum memahami pesan Anda. Silakan gunakan perintah berikut:\n";
        $fallbackText .= "• Ketik */program* untuk melihat program donasi yang tersedia\n";
        $fallbackText .= "• Ketik */cari banjir* untuk mencari kampanye tertentu\n";
        $fallbackText .= "• Atau kunjungi langsung portal website kami di:\n{$webBaseUrl}";

        echo json_encode([
            'status' => 'success',
            'api_name' => 'PeduliBersama Telegram Bot & n8n Bridge API',
            'version' => '1.0.0',
            'website' => $webBaseUrl,
            'database' => 'Supabase PostgreSQL (Realtime)',
            'available_actions' => [
                'GET ?action=list' => 'Daftar program donasi aktif',
                'GET ?action=detail&id={id}' => 'Detail satu program donasi',
                'GET ?action=search&q={keyword}' => 'Cari program donasi',
                'POST ?action=donate' => 'Kirim donasi langsung ke Supabase',
                'GET ?action=help' => 'Panduan perintah bot'
            ],
            'telegram_welcome_message' => $welcomeText,
            'telegram_fallback_message' => $fallbackText
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        break;
}
