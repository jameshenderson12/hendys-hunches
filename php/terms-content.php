<?php

require_once __DIR__ . '/terms.php';

header('Content-Type: text/html; charset=utf-8');

$assetPrefix = trim((string) ($_GET['asset_prefix'] ?? ''));
$showLogo = (string) ($_GET['logo'] ?? '1') !== '0';

hh_render_terms_content($assetPrefix, $showLogo);
