<?php
/**
 * Configurazione della libreria progetti e degli account utente.
 *
 * $API_KEY: se valorizzata, tutte le chiamate all'API dovranno includere
 * l'header "X-Api-Key" con lo stesso valore, altrimenti verranno rifiutate.
 * Lasciala vuota ('') per un uso interno/fidato senza questa protezione
 * aggiuntiva (l'accesso alla libreria resta comunque protetto dal login
 * utente descritto sotto).
 *
 * Se attivi una chiave qui, ricordati di impostarla anche nel file HTML
 * dell'editor (costante API_KEY in cima al blocco <script>).
 */
$API_KEY = '';

// Cartella dove vengono salvati i progetti (un file .json per progetto
// più un indice). Deve essere scrivibile dal server web.
define('LIBRARY_DIR', __DIR__ . '/../library');
define('INDEX_FILE', LIBRARY_DIR . '/index.json');

// Cartella dove vengono salvati gli account utente (un unico file indice,
// nessun database richiesto). Deve essere scrivibile dal server web e,
// come per LIBRARY_DIR, non è mai raggiungibile direttamente via URL.
define('USERS_DIR', __DIR__ . '/../users');
define('USERS_INDEX_FILE', USERS_DIR . '/index.json');

// Dimensione massima accettata per un progetto (bytes). Alzala se prevedi
// di allegare immagini di riferimento grandi.
define('MAX_PAYLOAD_BYTES', 15 * 1024 * 1024); // 15 MB

// Quante versioni precedenti di ciascun progetto restano conservate nella
// cronologia (le più vecchie oltre questo numero vengono eliminate ad ogni
// nuovo salvataggio, per non far crescere la libreria all'infinito).
define('MAX_VERSIONS_PER_PROJECT', 30);

// Nome del cookie di sessione (puoi lasciarlo così).
define('SESSION_COOKIE_NAME', 'spikecut_session');

// Per quanto tempo (in secondi) resta valido il link di recupero password
// inviato via email prima di scadere. Default: 1 ora.
define('RESET_TOKEN_TTL', 3600);

/**
 * Indirizzo "From" con cui vengono inviate le email di recupero password.
 * Su molti hosting condivisi (incluso Aruba) funziona solo se il dominio
 * corrisponde a quello dello spazio web: modificalo se necessario, ad
 * esempio 'no-reply@ilTuoDominio.it'.
 */
define('FROM_EMAIL', 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
