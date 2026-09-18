<?php
require_once __DIR__ . '/common.php';

$user = current_user(); // funziona anche se collegati solo tramite il cookie "ricordami"
if ($user) {
    $users = read_users();
    foreach ($users as &$u) {
        if ($u['id'] === $user['id']) {
            $u['rememberToken'] = null;
            $u['rememberTokenExpires'] = null;
            break;
        }
    }
    unset($u);
    write_users($users);
}
setcookie(REMEMBER_COOKIE_NAME, '', ['expires' => time() - 3600, 'path' => '/']);

$_SESSION = [];
session_destroy();

json_ok();
