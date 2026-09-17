<?php
require_once __DIR__ . '/common.php';
check_api_key();
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metodo non consentito, usare GET.', 405);
}

$id = $_GET['id'] ?? '';
if (!safe_id($id)) {
    json_error('Identificativo progetto non valido.', 400);
}

$path = project_path($id);
if (!file_exists($path)) {
    json_error('Progetto non trovato.', 404);
}
$current = json_decode(file_get_contents($path), true);
if (!is_array($current)) {
    json_error('Il file di progetto sul server risulta corrotto.', 500);
}

$isOwner  = ($current['ownerId'] ?? null) === $user['id'];
$isShared = !empty($current['shared']);
if (!$isOwner && !$isShared) {
    json_error('Non hai accesso a questo progetto.', 403);
}

$dir = versions_dir($id);
$items = [];
if (is_dir($dir)) {
    $files = glob($dir . '/*.json');
    foreach ($files as $f) {
        $fname = basename($f);
        $data = json_decode(file_get_contents($f), true);
        $parts = explode('_', $fname);
        $ts = isset($parts[0]) ? intdiv((int) $parts[0], 1000) : time();
        $items[] = [
            'file'    => $fname,
            'name'    => is_array($data) ? ($data['name'] ?? '') : '',
            'savedAt' => date('c', $ts),
        ];
    }
}
usort($items, function ($a, $b) { return strcmp($b['file'], $a['file']); }); // più recenti prima

json_ok(['items' => $items, 'mine' => $isOwner]);
