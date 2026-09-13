<?php
/**
 * Configurazione della libreria progetti.
 *
 * $API_KEY: se valorizzata, tutte le chiamate all'API dovranno includere
 * l'header "X-Api-Key" con lo stesso valore, altrimenti verranno rifiutate.
 * Lasciala vuota ('') per un uso interno/fidato senza protezione.
 *
 * Se attivi una chiave qui, ricordati di impostarla anche nel file HTML
 * dell'editor (costante API_KEY in cima al blocco <script>).
 */
$API_KEY = '';

// Cartella dove vengono salvati i progetti (un file .json per progetto
// più un indice). Deve essere scrivibile dal server web.
define('LIBRARY_DIR', __DIR__ . '/../library');
define('INDEX_FILE', LIBRARY_DIR . '/index.json');

// Dimensione massima accettata per un progetto (bytes). Alzala se prevedi
// di allegare immagini di riferimento grandi.
define('MAX_PAYLOAD_BYTES', 15 * 1024 * 1024); // 15 MB
