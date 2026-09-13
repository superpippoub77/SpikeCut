<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
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
