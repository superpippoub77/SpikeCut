<?php
require_once __DIR__ . '/common.php';
check_api_key();
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metodo non consentito, usare GET.', 405);
}

$index = read_index();
$items = array_values(array_filter($index, function ($e) use ($user) {
    return ($e['ownerId'] ?? null) === $user['id'] || project_is_public($e);
}));
foreach ($items as &$it) {
    $it['mine'] = ($it['ownerId'] ?? null) === $user['id'];
    $it['generic'] = project_is_generic($it);
    if ($it['generic']) $it['shared'] = true; // i progetti generici sono sempre pubblici
    if (!array_key_exists('folderId', $it)) $it['folderId'] = null;
}
unset($it);

usort($items, function ($a, $b) {
    return strcmp($b['updated'] ?? '', $a['updated'] ?? '');
});

$folders = read_folders();
$myFolders = array_values(array_filter($folders, function ($f) use ($user) {
    return ($f['ownerId'] ?? null) === $user['id'];
}));

json_ok(['items' => $items, 'folders' => $myFolders]);
