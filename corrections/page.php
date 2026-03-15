<?php
require_once 'config.php';

// ✅ LFI-01 : whitelist stricte des pages autorisées
$allowed = ['home', 'about'];
$p       = basename($_GET['p'] ?? 'home');

if (!in_array($p, $allowed, true)) {
    http_response_code(403);
    die('Page non autorisée.');
}

// Chemin absolu — impossible d'en sortir avec ../
include __DIR__ . '/' . $p . '.php';
