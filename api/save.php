<?php
require_once __DIR__ . '/common.php';
check_api_key();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metodo non consentito, usare POST.', 405);
}

$body = read_json_body();

$name = trim((string)($body['name'] ?? ''));
if ($name === '') {
    json_error('Il progetto deve avere un nome.');
}
$name = mb_substr($name, 0, 120);

$id = $body['id'] ?? null;
if (!safe_id($id)) {
    $id = bin2hex(random_bytes(12)); // nuovo progetto
}

$now = date('c');
$index = read_index();
$existing = null;
foreach ($index as $entry) {
    if (isset($entry['id']) && $entry['id'] === $id) { $existing = $entry; break; }
}

$project = [
    'name'     => $name,
    'page'     => $body['page'] ?? null,
    'shapes'   => $body['shapes'] ?? [],
    'refImage' => $body['refImage'] ?? null,
    'created'  => $existing['created'] ?? $now,
    'updated'  => $now,
];

if (file_put_contents(project_path($id), json_encode($project, JSON_UNESCAPED_UNICODE)) === false) {
    json_error('Impossibile scrivere il progetto sul server.', 500);
}

// aggiorna l'indice (rimuove eventuale voce precedente con lo stesso id, poi la riaggiunge)
$index = array_values(array_filter($index, function ($e) use ($id) {
    return !isset($e['id']) || $e['id'] !== $id;
}));
$index[] = [
    'id'      => $id,
    'name'    => $name,
    'created' => $project['created'],
    'updated' => $project['updated'],
];
write_index($index);

json_ok(['id' => $id, 'updated' => $project['updated']]);
