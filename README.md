# SpikeCut — Editor vettoriale per fustellatura, taglio e piega

SpikeCut è un editor grafico vettoriale pensato per disegnare fustelle:
tracciati di **taglio**, **piega**, **fustella speciale** e **incollaggio/aletta**,
con quotature precise, importazione/esportazione SVG e PDF, e una libreria
progetti condivisa con account utente e cronologia versioni.

È pensato per essere semplice da mettere online: un solo file HTML per
l'editor, più un piccolo backend PHP (senza database) per account e libreria.

---

## 1. Cosa contiene il progetto

```
index.html          → l'intero editor (HTML + CSS + JS in un unico file)
api/                 → backend PHP: account utente e libreria progetti
library/             → progetti salvati in libreria (creata/gestita dal server)
users/               → account registrati (creata/gestita dal server)
.github/workflows/   → deploy automatico su hosting Aruba via FTP (GitHub Actions)
LEGGIMI.txt          → istruzioni pratiche di messa online, passo passo
```

`index.html` funziona da solo, anche aperto in locale con un doppio clic:
in quel caso l'editor di disegno funziona normalmente, ma tutto ciò che
richiede il server (account, libreria condivisa, cronologia versioni) resta
disabilitato finché non è servito da un vero server web.

---

## 2. L'editor (index.html)

Un solo file, senza framework: HTML + CSS + JavaScript "vanilla", più
alcune librerie esterne caricate da CDN (ImageTracer.js per la
vettorizzazione delle immagini, jsPDF per l'export PDF, JSZip per i font
caricati in blocco, opzionalmente le API di Google per Drive).

### Strumenti di disegno
Selezione, penna (poligonale), linea, rettangolo, ellisse, curva/arco,
matita a mano libera, testo. Tutte le forme — comprese le immagini
vettorizzate e le linee a punti — si possono **spostare, ridimensionare e
ruotare**.

### Ruoli e colori (convenzione di settore)
Ogni tratto ha un ruolo che ne determina colore e stile di stampa:
- **Disegno** — nero, continuo
- **Taglio** — rosso, continuo
- **Piega** — blu, tratteggiato
- **Fustella speciale** — verde, punto-linea
- **Incollaggio/aletta** — marrone, puntinato

L'importazione di un SVG riconosce automaticamente il ruolo dal colore
usato nel file originale.

### Organizzazione del disegno
- **Livelli**: mostra/nascondi, blocca, riordina, con contenuto separato
  per ciascuno; ogni forma appartiene a un livello.
- **Ordine di sovrapposizione**: dentro allo stesso livello, ogni forma
  può essere portata avanti/indietro o in primo/secondo piano, come nei
  programmi di disegno classici.
- **Schede multiple**: più disegni aperti contemporaneamente, ognuno con
  il proprio foglio, storico annulla/ripeti e libreria.

### Precisione
Righelli, griglia con calamita, quotature in tempo reale durante il
disegno e quotature permanenti dell'intero pezzo, unità di misura
selezionabile (mm/cm/m/pollici), verifica automatica dei percorsi aperti
e saldatura dei punti vicini.

### Importazione
- **SVG esistente**: parser completo (archi, curve, trasformazioni),
  fusione automatica dei doppioni tecnici tipici dei software CAD
  (contorno + riempimento pieno sovrapposti → tiene solo il contorno),
  centraggio o — su richiesta — riposizionamento in alto a sinistra con
  adattamento automatico del foglio e scala percentuale, il tutto chiesto
  subito dopo aver scelto il file.
- **Immagine di riferimento** (JPG/PNG): vettorizzazione automatica,
  regolazioni colore (luminosità, contrasto, saturazione, scala di
  grigi, seppia, negativo, tonalità, sfocatura), completamente
  interattiva (sposta/ruota/ridimensiona) come qualunque altra forma.
- **Google Drive** (opzionale, richiede credenziali Google proprie: vedi
  LEGGIMI.txt): importa SVG o file progetto .json in sola lettura.

### Esportazione
- **SVG** pronto per taglio/stampa, raggruppato per ruolo con legenda colori.
- **PDF** in scala reale 1:1.
- **PDF multi-pagina**: per disegni più grandi del foglio, li divide su
  una griglia di fogli dello stesso formato (A3/A4/A5 o personalizzato),
  con margine di sovrapposizione e riferimenti di posizione per
  riassemblarli dopo la stampa.
- **Stampa di prova** su carta comune, scalata per stare nel formato
  scelto, prima dell'esportazione finale in scala reale.

### Salvataggio
Due sistemi indipendenti, con scopi diversi:
- **💾 Salva** (locale) — salva subito una copia nel `localStorage` del
  browser, più un salvataggio automatico ad ogni modifica. Resta solo su
  questo browser/computer: utile per non perdere il lavoro chiudendo per
  sbaglio la scheda, ma non è condiviso tra dispositivi.
- **☁ Libreria** (server) — richiede un account (vedi sotto), salva sul
  server ed è raggiungibile da qualunque dispositivo.

C'è anche l'esportazione/importazione di un file progetto `.json` da
scaricare e riaprire manualmente, indipendente da entrambi i sistemi sopra.

---

## 3. Account utente e libreria condivisa (backend PHP)

Nessun database: tutto è composto da file `.json` sul server, gestiti
dalle pagine PHP in `api/`. Le password non sono mai salvate in chiaro
(sempre come hash).

### Account
Registrazione (nome utente + email + password), accesso, disconnessione,
recupero password via email con link a tempo (1 ora). Le sessioni usano i
cookie standard di PHP.

### Libreria progetti
Ogni progetto salvato ha un proprietario. Al momento del salvataggio si
può scegliere se tenerlo **privato** (visibile solo a chi l'ha creato)
oppure **condividerlo nella libreria comune** (visibile e apribile da
tutti gli utenti registrati). Solo il proprietario può modificare o
eliminare un proprio progetto, anche se condiviso; aprire un progetto
condiviso altrui e poi salvarlo crea automaticamente una copia personale,
senza toccare l'originale.

### Cronologia versioni
Ogni salvataggio successivo al primo conserva lo stato precedente. Dalla
finestra "Libreria progetti", il pulsante "Versioni" di ogni riga apre
l'elenco delle versioni passate, da cui si può:
- **aprire** una versione in una scheda separata, senza toccare quella
  salvata;
- **ripristinare** una versione come stato attuale (solo il proprietario),
  operazione a sua volta annullabile perché anche lo stato di prima del
  ripristino viene conservato in cronologia.

Vengono tenute al massimo le ultime 30 versioni per progetto (regolabile
in `api/config.php`).

---

## 4. Struttura del backend (api/)

```
config.php           → costanti di configurazione (percorsi, limiti, chiave API opzionale)
common.php            → funzioni condivise: sessioni, CORS, utenti, cronologia versioni
register.php           → creazione account
login.php / logout.php → accesso e disconnessione
me.php                  → utente attualmente collegato (usato dal frontend all'avvio)
forgot_password.php     → invio email di recupero password
reset_password.php      → reimpostazione password tramite il link ricevuto
save.php                → salva un progetto (nuovo o esistente), crea uno snapshot se sovrascrive
list.php                → elenca i progetti dell'utente + quelli condivisi da tutti
load.php                → apre un progetto (proprio o condiviso)
delete.php              → elimina un progetto (solo proprietario)
versions.php            → elenca la cronologia versioni di un progetto
load_version.php        → apre una versione storica specifica (sola lettura)
restore_version.php     → ripristina una versione storica come stato attuale (solo proprietario)
```

Ogni progetto è un file `.json` in `library/`, ogni versione storica un
file in `library/versions/<id>/`, ogni account una voce in
`users/index.json`. Entrambe le cartelle sono protette da accesso diretto
via URL (file `.htaccess` con `Require all denied`) e vengono create
automaticamente al primo utilizzo se non esistono già.

---

## 5. Mettere il progetto online

Istruzioni dettagliate passo-passo, comprese la protezione opzionale con
chiave API e la configurazione di Google Drive, sono in **LEGGIMI.txt**.

In sintesi:
1. Carica tutto il contenuto di questo pacchetto sul server, mantenendo
   la struttura delle cartelle.
2. Verifica che `library/` e `users/` siano scrivibili dal server web.
3. Apri il sito dal suo indirizzo web reale (non aprendo il file in
   locale): solo così funzionano account e libreria.

Il workflow incluso in `.github/workflows/` pubblica automaticamente su
hosting Aruba via FTP ad ogni push, escludendo sempre `library/**` e
`users/**` dal deploy: un aggiornamento del codice non cancella mai
progetti o account già presenti sul server.

---

## 6. Note per chi continua lo sviluppo

- Tutto il JavaScript dell'editor vive in un unico blocco `<script>` di
  `index.html`: niente moduli, niente build step, si modifica e si
  ricarica direttamente nel browser.
- Le coordinate interne sono **sempre in millimetri**; l'unità di misura
  scelta dall'utente (cm/m/pollici) è solo una conversione applicata in
  lettura/scrittura dei campi numerici.
- Le librerie esterne sono caricate da CDN con URL e versione fissati
  nell'`<head>` del file: se una smette di rispondere, è lì che va
  corretto il collegamento.
- Il backend non richiede build, composer o dipendenze: sono file PHP
  puri, pensati per girare su hosting condiviso "base" senza configurazioni
  particolari (a parte, idealmente, l'estensione `mbstring`, gestita
  comunque con un ripiego se assente).
