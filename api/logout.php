<?php
require_once __DIR__ . '/common.php';

// Uscita: viene revocato solo il token di questo dispositivo (gli accessi aperti altrove restano).
// Con { "everywhere": true } si esce da tutti i dispositivi (come dopo un cambio password).
$user = current_user();
if ($user) {
    $body = json_decode((string) file_get_contents('php://input'), true);
    $everywhere = is_array($body) && !empty($body['everywhere']);
    $payload = current_jwt_payload();
    $users = read_users();
    foreach ($users as &$u) {
        if ($u['id'] === $user['id']) {
            if ($everywhere || empty($payload['jti'])) {
                $u['tokenValidAfter'] = time();
            } else {
                $now = time();
                $list = array_values(array_filter((array) ($u['revokedJti'] ?? []), function ($r) use ($now) { return ($r['exp'] ?? 0) > $now; }));
                $list[] = ['jti' => $payload['jti'], 'exp' => (int) ($payload['exp'] ?? $now)];
                $u['revokedJti'] = array_slice($list, -200);
            }
            break;
        }
    }
    unset($u);
    write_users($users);
}

json_ok();
