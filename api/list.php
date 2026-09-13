<?php
require_once __DIR__ . '/common.php';
check_api_key();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metodo non consentito, usare GET.', 405);
}

$index = read_index();
usort($index, function ($a, $b) {
    return strcmp($b['updated'] ?? '', $a['updated'] ?? '');
});

json_ok(['items' => $index]);
