<?php
/**
 * Link pubblico di visione di un progetto (solo il proprietario).
 * GET  ?id=...                                   → { token | null }
 * POST { "id": "...", "action": "create" }       → crea il link (o restituisce quello che c'è già)
 * POST { "id": "...", "action": "renew" }        → nuovo token: il vecchio link smette di funzionare
 * POST { "id": "...", "action": "revoke" }       → disattiva il link
 */
require_once __DIR__ . '/common.php';
check_api_key();
$user = require_login();

$method = $_SERVER['REQUEST_METHOD'];
$body = $method === 'POST' ? read_json_body() : [];
$id = $method === 'POST' ? ($body['id'] ?? null) : ($_GET['id'] ?? null);
if (!safe_id($id)) json_error('Progetto non valido.', 400);

$entry = null;
foreach (read_index() as $e) if (($e['id'] ?? null) === $id) { $entry = $e; break; }
if (!$entry || project_is_trashed($entry)) json_error('Progetto non trovato.', 404);
if (($entry['ownerId'] ?? null) !== $user['id']) json_error('Solo il proprietario può gestire il link pubblico di questo progetto.', 403);

if ($method === 'GET') {
    $token = null; $created = null;
    foreach (read_view_links() as $l) if (($l['id'] ?? null) === $id) { $token = $l['token']; $created = $l['created'] ?? null; break; }
    json_ok(['token' => $token, 'created' => $created]);
}
if ($method !== 'POST') json_error('Metodo non consentito.', 405);

$action = $body['action'] ?? 'create';
acquire_data_lock();
$links = read_view_links();
$current = null;
foreach ($links as $l) if (($l['id'] ?? null) === $id) { $current = $l; break; }
if ($action === 'create' && $current) json_ok(['token' => $current['token'], 'created' => $current['created'] ?? null]);
$links = array_values(array_filter($links, function ($l) use ($id) { return ($l['id'] ?? null) !== $id; }));
if ($action === 'revoke') { write_view_links($links); json_ok(['token' => null]); }
if ($action !== 'create' && $action !== 'renew') json_error('Azione non valida.', 400);
$token = bin2hex(random_bytes(16));
$now = date('c');
$links[] = ['token' => $token, 'id' => $id, 'ownerId' => $user['id'], 'created' => $now];
write_view_links($links);
json_ok(['token' => $token, 'created' => $now]);
