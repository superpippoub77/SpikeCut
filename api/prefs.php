<?php
/**
 * Preferenze dell'utente (griglia, unità di misura, calamita…), salvate nell'account così
 * restano uguali su ogni browser e computer.
 * GET  → { prefs }      POST { "prefs": { … } } → salva (al massimo 8 KB)
 */
require_once __DIR__ . '/common.php';
check_api_key();
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] === 'GET') json_ok(['prefs' => (isset($user['prefs']) && is_array($user['prefs'])) ? $user['prefs'] : null]);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_error('Metodo non consentito.', 405);
$body = read_json_body();
$prefs = $body['prefs'] ?? null;
if (!is_array($prefs)) json_error('Preferenze non valide.', 400);
$json = json_encode($prefs, JSON_UNESCAPED_UNICODE);
if ($json === false || strlen($json) > 8192) json_error('Preferenze troppo grandi.', 413);
acquire_data_lock();
$users = read_users();
foreach ($users as &$u) { if ($u['id'] === $user['id']) { $u['prefs'] = $prefs; break; } }
unset($u);
write_users($users);
json_ok(['prefs' => $prefs]);
