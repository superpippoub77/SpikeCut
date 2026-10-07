<?php
/**
 * Visione pubblica di un progetto tramite link: GET ?t=<token>
 * Non serve l'accesso. Restituisce solo ciò che serve a disegnare il progetto
 * (niente note interne, cartelle o dati del proprietario oltre al nome).
 */
require_once __DIR__ . '/common.php';
check_api_key();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_error('Metodo non consentito, usare GET.', 405);

$t = $_GET['t'] ?? '';
if (!safe_view_token($t)) json_error('Link non valido.', 400);
$link = null;
foreach (read_view_links() as $l) if (hash_equals((string)($l['token'] ?? ''), $t)) { $link = $l; break; }
if (!$link || !safe_id($link['id'] ?? '')) json_error('Questo link non è più attivo: chiedi a chi te l\'ha mandato un link nuovo.', 404);

$entry = null;
foreach (read_index() as $e) if (($e['id'] ?? null) === $link['id']) { $entry = $e; break; }
if (!$entry || project_is_trashed($entry)) json_error('Il progetto non è più disponibile.', 404);
$raw = @file_get_contents(project_path($link['id']));
$project = $raw !== false ? json_decode($raw, true) : null;
if (!is_array($project)) json_error('Il progetto non è più disponibile.', 404);

header('Cache-Control: no-store');
json_ok(['project' => [
    'name'        => $project['name'] ?? 'Disegno',
    'page'        => $project['page'] ?? null,
    'shapes'      => $project['shapes'] ?? [],
    'layers'      => $project['layers'] ?? null,
    'customFonts' => $project['customFonts'] ?? null,
    'ownerName'   => $project['ownerName'] ?? '',
    'updated'     => $project['updated'] ?? null,
]]);
