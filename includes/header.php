<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'PeduliBersama - Platform Donasi' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <div class="brand-icon me-2">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <span class="brand-text">Peduli<span class="text-warning">Bersama</span></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link px-3" href="index.php"><i class="bi bi-house-door me-1"></i>Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="kampanye.php"><i class="bi bi-megaphone me-1"></i>Kampanye</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm px-3 rounded-pill" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Keluar
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-warning btn-sm px-3 rounded-pill fw-semibold" href="register.php">
                            <i class="bi bi-person-plus me-1"></i>Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div style="height:72px;"></div>

<main>
    <div class="container mt-3">
        <?php
        $flash = get_flash();
        if ($flash):
        ?>
            <div class="alert alert-<?= $flash['type'] ?> flash-message alert-dismissible fade show animate-slideDown" role="alert">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle-fill' : ($flash['type'] === 'danger' ? 'exclamation-triangle-fill' : 'info-circle-fill') ?>"></i>
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
