<?php
require_once __DIR__ . '/common.php';
check_api_key();
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Metodo non consentito, usare GET.', 405);
}

$index = read_index();
$items = array_values(array_filter($index, function ($e) use ($user) {
    return ($e['ownerId'] ?? null) === $user['id'] || !empty($e['shared']);
}));
foreach ($items as &$it) {
    $it['mine'] = ($it['ownerId'] ?? null) === $user['id'];
}
unset($it);

usort($items, function ($a, $b) {
    return strcmp($b['updated'] ?? '', $a['updated'] ?? '');
});

json_ok(['items' => $items]);
