<?php
require_once __DIR__ . '/common.php';
check_api_key();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Metodo non consentito, usare POST.', 405);
}

$body = read_json_body();
$identifier = trim((string)($body['identifier'] ?? ''));
$password   = (string)($body['password'] ?? '');

if ($identifier === '' || $password === '') {
    json_error('Inserisci nome utente (o email) e password.');
}

$users = read_users();
$user = find_user_by_username($users, $identifier);
if (!$user) $user = find_user_by_email($users, $identifier);

if (!$user || !password_verify($password, $user['passwordHash'])) {
    json_error('Nome utente/email o password non corretti.', 401);
}

$_SESSION['user_id'] = $user['id'];

if (!empty($body['remember'])) {
    $token = bin2hex(random_bytes(32));
    foreach ($users as &$u) {
        if ($u['id'] === $user['id']) {
            $u['rememberToken'] = $token;
            $u['rememberTokenExpires'] = time() + REMEMBER_TTL;
            break;
        }
    }
    unset($u);
    write_users($users);
    setcookie(REMEMBER_COOKIE_NAME, $token, [
        'expires'  => time() + REMEMBER_TTL,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

json_ok(['user' => public_user($user)]);
