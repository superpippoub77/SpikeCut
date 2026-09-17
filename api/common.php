<?php
require_once __DIR__ . '/config.php';

// Sessioni: servono per riconoscere l'utente collegato tra una richiesta e
// l'altra. Vanno avviate prima di qualunque output (gli header sotto non
// contano come output, va bene).
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'samesite' => 'Lax',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
]);
session_name(SESSION_COOKIE_NAME);
session_start();

header('Content-Type: application/json; charset=utf-8');
// Con le sessioni (cookie) il CORS "*" non è consentito dai browser insieme
// alle credenziali: rispecchio l'origine della richiesta quando presente.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function json_error($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_ok($data = []) {
    echo json_encode(array_merge(['ok' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function check_api_key() {
    global $API_KEY;
    if ($API_KEY === '') return;
    $given = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';
    if (!hash_equals($API_KEY, $given)) {
        json_error('Chiave API mancante o non valida.', 401);
    }
}

function ensure_library_dir() {
    if (!is_dir(LIBRARY_DIR)) {
        if (!mkdir(LIBRARY_DIR, 0775, true) && !is_dir(LIBRARY_DIR)) {
            json_error('Impossibile creare la cartella della libreria sul server. Verifica i permessi di scrittura.', 500);
        }
    }
    $htaccess = LIBRARY_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        // Blocca l'accesso diretto ai file della libreria via URL: si passa sempre dall'API.
        @file_put_contents($htaccess, "Require all denied\nDeny from all\n");
    }
}

function read_index() {
    ensure_library_dir();
    if (!file_exists(INDEX_FILE)) return [];
    $raw = @file_get_contents(INDEX_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function write_index($items) {
    ensure_library_dir();
    file_put_contents(INDEX_FILE, json_encode(array_values($items), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// Accetta solo id generati dal server (esadecimali): evita path traversal
// o accessi a file arbitrari tramite l'id passato dal client.
function safe_id($id) {
    return is_string($id) && preg_match('/^[a-f0-9]{16,40}$/', $id) === 1;
}

function project_path($id) {
    return LIBRARY_DIR . '/' . $id . '.json';
}

/* ============================================================
   CRONOLOGIA VERSIONI — ad ogni salvataggio che sovrascrive un
   progetto esistente, lo stato precedente viene conservato qui
   prima di essere sostituito, così si può tornare indietro.
============================================================ */

function versions_dir($id) {
    return LIBRARY_DIR . '/versions/' . $id;
}

function ensure_versions_dir($id) {
    $dir = versions_dir($id);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            json_error('Impossibile creare la cronologia versioni sul server.', 500);
        }
    }
}

// Salva $projectData (lo stato PRECEDENTE al salvataggio in corso) come
// nuova voce della cronologia, e pota le versioni più vecchie oltre il
// limite configurato.
function snapshot_version($id, $projectData) {
    ensure_versions_dir($id);
    $fname = ((int) round(microtime(true) * 1000)) . '_' . bin2hex(random_bytes(2)) . '.json';
    file_put_contents(versions_dir($id) . '/' . $fname, json_encode($projectData, JSON_UNESCAPED_UNICODE));

    $files = glob(versions_dir($id) . '/*.json');
    if ($files === false) return $fname;
    sort($files); // i nomi iniziano col timestamp: ordine cronologico crescente
    $excess = count($files) - MAX_VERSIONS_PER_PROJECT;
    for ($i = 0; $i < $excess; $i++) {
        @unlink($files[$i]);
    }
    return $fname;
}

// Accetta solo nomi di file versione generati da snapshot_version(): evita
// path traversal tramite il parametro passato dal client.
function safe_version_file($f) {
    return is_string($f) && preg_match('/^[0-9]+_[a-f0-9]{4}\.json$/', $f) === 1;
}

function read_json_body() {
    $raw = file_get_contents('php://input');
    if (strlen($raw) > MAX_PAYLOAD_BYTES) {
        json_error('Il progetto supera la dimensione massima consentita.', 413);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_error('Corpo della richiesta non valido: atteso JSON.', 400);
    }
    return $data;
}

/* ============================================================
   ACCOUNT UTENTE — registrazione, login, recupero password.
   Nessun database: un unico file indice con tutti gli utenti,
   password sempre salvate come hash (mai in chiaro).
============================================================ */

function ensure_users_dir() {
    if (!is_dir(USERS_DIR)) {
        if (!mkdir(USERS_DIR, 0775, true) && !is_dir(USERS_DIR)) {
            json_error('Impossibile creare la cartella utenti sul server. Verifica i permessi di scrittura.', 500);
        }
    }
    $htaccess = USERS_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "Require all denied\nDeny from all\n");
    }
}

function read_users() {
    ensure_users_dir();
    if (!file_exists(USERS_INDEX_FILE)) return [];
    $raw = @file_get_contents(USERS_INDEX_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function write_users($users) {
    ensure_users_dir();
    file_put_contents(USERS_INDEX_FILE, json_encode(array_values($users), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function find_user_by_username($users, $username) {
    foreach ($users as $u) {
        if (isset($u['username']) && strcasecmp($u['username'], $username) === 0) return $u;
    }
    return null;
}

function find_user_by_email($users, $email) {
    foreach ($users as $u) {
        if (isset($u['email']) && strcasecmp($u['email'], $email) === 0) return $u;
    }
    return null;
}

function find_user_by_id($users, $id) {
    foreach ($users as $u) {
        if (isset($u['id']) && $u['id'] === $id) return $u;
    }
    return null;
}

// Utente attualmente collegato (in base alla sessione), oppure null.
function current_user() {
    if (empty($_SESSION['user_id'])) return null;
    $users = read_users();
    return find_user_by_id($users, $_SESSION['user_id']);
}

// Come current_user(), ma interrompe la richiesta con errore 401 se non
// c'è nessuno collegato: da chiamare in cima alle API che richiedono login.
function require_login() {
    $u = current_user();
    if (!$u) json_error('Devi accedere al tuo account per usare questa funzione.', 401);
    return $u;
}

// Versione dell'utente sicura da restituire al client: mai l'hash password
// né il token di reset.
function public_user($u) {
    return ['id' => $u['id'], 'username' => $u['username'], 'email' => $u['email']];
}

function safe_username($u) {
    return is_string($u) && preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $u) === 1;
}

function valid_email($e) {
    return is_string($e) && filter_var($e, FILTER_VALIDATE_EMAIL) !== false;
}

// URL di base del progetto (la cartella che contiene index.html), dedotto
// dalla richiesta corrente: usato per costruire il link nell'email di
// recupero password senza doverlo configurare a mano.
function site_base_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // questo file sta in api/, la root del progetto è una cartella sopra
    $dir = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
    return $scheme . '://' . $host . $dir;
}
