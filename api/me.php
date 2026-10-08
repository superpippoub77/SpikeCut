<?php
require_once __DIR__ . '/common.php';
check_api_key();

$u = current_user();
$out = ['user' => $u ? public_user($u) : null];
if ($u) {
    $fresh = refreshed_token_for($u);
    if ($fresh) $out['token'] = $fresh;           // accesso rinnovato: il client sostituisce il token
    $out['prefs'] = (isset($u['prefs']) && is_array($u['prefs'])) ? $u['prefs'] : null;
}
json_ok($out);
