<?php
require_once __DIR__ . '/common.php';
check_api_key();

$id = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = read_json_body();
    $id = $body['id'] ?? null;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
} else {
    json_error('Metodo non consentito, usare POST o GET.', 405);
}

if (!safe_id($id)) {
    json_error('Identificativo progetto non valido.', 400);
}

$path = project_path($id);
if (file_exists($path)) {
    unlink($path);
}

$index = read_index();
$index = array_values(array_filter($index, function ($e) use ($id) {
    return !isset($e['id']) || $e['id'] !== $id;
}));
write_index($index);

json_ok(['id' => $id]);
