<?php
require_once __DIR__ . '/common.php';
check_api_key();

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

$raw = file_get_contents($path);
$project = json_decode($raw, true);
if (!is_array($project)) {
    json_error('Il file di progetto sul server risulta corrotto.', 500);
}

json_ok(['project' => $project]);
