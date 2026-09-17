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

$raw = file_get_contents($path);
$project = json_decode($raw, true);
if (!is_array($project)) {
    json_error('Il file di progetto sul server risulta corrotto.', 500);
}

$isOwner  = ($project['ownerId'] ?? null) === $user['id'];
$isShared = !empty($project['shared']);
if (!$isOwner && !$isShared) {
    json_error('Non hai accesso a questo progetto.', 403);
}

json_ok(['project' => $project, 'mine' => $isOwner]);
