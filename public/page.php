<?php
require_once 'config.php';

// ============================================================
// FAILLE LFI-01 : Local File Inclusion
// Inclusion d'un fichier local basé sur un paramètre GET non filtré
// Test : ?p=../../../../etc/passwd
// Test : ?p=../../../../var/log/apache2/access.log  (log poisoning)
// Test : ?p=php://filter/convert.base64-encode/resource=config
// ============================================================
$p = $_GET['p'] ?? 'home';  // ❌ FAILLE: non validé

// Aucune whitelist, aucune validation du chemin
include($p . '.php');  // ❌ FAILLE LFI-01
