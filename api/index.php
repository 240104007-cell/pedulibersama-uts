<?php
declare(strict_types=1);
ob_start();
// Vercel Serverless Entrypoint / Router for PeduliBersama

// Set root directory
$rootDir = dirname(__DIR__);
chdir($rootDir);

// Parse incoming request path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim((string)$path, '/');

// Route root or empty to index.php
if ($path === '' || $path === 'index.php') {
    require $rootDir . '/index.php';
    exit;
}

// Serve static assets if routed through here
if (str_starts_with($path, 'assets/')) {
    $assetFile = realpath($rootDir . '/' . $path);
    if ($assetFile && file_exists($assetFile) && str_starts_with($assetFile, $rootDir . DIRECTORY_SEPARATOR)) {
        $ext = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        readfile($assetFile);
        exit;
    }
}

// Allowed PHP pages
$allowedPages = [
    'index',
    'login',
    'register',
    'logout',
    'kampanye',
    'detail_kampanye',
    'dashboard',
    'tambah_kampanye',
    'edit_kampanye',
    'hapus_kampanye',
    'proses_donasi'
];

$page = str_ends_with($path, '.php') ? substr($path, 0, -4) : $path;

if (in_array($page, $allowedPages, true)) {
    $target = $rootDir . '/' . $page . '.php';
    if (file_exists($target)) {
        require $target;
        exit;
    }
}

// Direct file check
$directTarget = realpath($rootDir . '/' . $path);
if ($directTarget && file_exists($directTarget) && str_starts_with($directTarget, $rootDir . DIRECTORY_SEPARATOR) && str_ends_with($directTarget, '.php')) {
    require $directTarget;
    exit;
}

// Fallback: 404 to index.php
http_response_code(404);
require $rootDir . '/index.php';
