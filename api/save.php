<?php
require_once __DIR__ . '/common.php';
check_api_key();
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metodo non consentito, usare POST.', 405);
}

$body = read_json_body();

$name = trim((string)($body['name'] ?? ''));
if ($name === '') {
    json_error('Il progetto deve avere un nome.');
}
$name = function_exists('mb_substr') ? mb_substr($name, 0, 120) : substr($name, 0, 120);
$shared = !empty($body['shared']);

$id = $body['id'] ?? null;
$index = read_index();
$existing = null;
if (safe_id($id)) {
    foreach ($index as $entry) {
        if (isset($entry['id']) && $entry['id'] === $id) { $existing = $entry; break; }
    }
}
// un progetto esistente può essere risalvato solo dal suo proprietario;
// se l'id non corrisponde a nessun progetto esistente, ne viene creato uno nuovo.
if ($existing && ($existing['ownerId'] ?? null) !== $user['id']) {
    json_error('Non puoi modificare un progetto che non ti appartiene.', 403);
}
if (!$existing) {
    $id = bin2hex(random_bytes(12));
}

$now = date('c');
$project = [
    'name'          => $name,
    'page'          => $body['page'] ?? null,
    'shapes'        => $body['shapes'] ?? [],
    'refImage'      => $body['refImage'] ?? null,
    'layers'        => $body['layers'] ?? null,
    'activeLayerId' => $body['activeLayerId'] ?? null,
    'customFonts'   => $body['customFonts'] ?? null,
    'ownerId'       => $user['id'],
    'ownerName'     => $user['username'],
    'shared'        => $shared,
    'created'       => $existing['created'] ?? $now,
    'updated'       => $now,
];

if (file_put_contents(project_path($id), json_encode($project, JSON_UNESCAPED_UNICODE)) === false) {
    json_error('Impossibile scrivere il progetto sul server.', 500);
}

// aggiorna l'indice (rimuove eventuale voce precedente con lo stesso id, poi la riaggiunge)
$index = array_values(array_filter($index, function ($e) use ($id) {
    return !isset($e['id']) || $e['id'] !== $id;
}));
$index[] = [
    'id'        => $id,
    'name'      => $name,
    'ownerId'   => $user['id'],
    'ownerName' => $user['username'],
    'shared'    => $shared,
    'created'   => $project['created'],
    'updated'   => $project['updated'],
];
write_index($index);

json_ok(['id' => $id, 'updated' => $project['updated']]);
