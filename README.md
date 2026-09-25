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

### Guida integrata
Il pulsante **?** in alto (o il tasto **F1**) apre la guida completa del
programma: ricerca a testo libero su tutti gli argomenti (ignora maiuscole e
accenti, trova anche sinonimi, evidenzia le parole trovate) e menu a tendina
per capitolo (Per iniziare, Disegnare, Modificare, Fustella e scatole,
Anteprima 3D, Livelli, Salvare ed esportare, Riferimento). Include le
scorciatoie da tastiera, i problemi frequenti e le novità versione per versione.

### Generazione automatica scatole
Menu File → "📦 Genera scatola…": inserendo Larghezza, Profondità e Altezza
(mm), genera automaticamente la fustella completa in una nuova scheda —
**scatola americana (RSC)**, il classico cartone a 4 pannelli con alette
sopra e sotto, oppure **astuccio con coperchio a incastro**, con alette a
polvere sui lati e aletta a incastro davanti/dietro. L'aletta di
incollaggio si può impostare a mano o lasciare calcolare automaticamente.
Le pieghe delle fustelle generate sono già impostate "a monte", 90°, così
l'anteprima 3D le chiude subito correttamente.
La zona di incollaggio dell'aletta è un poligono con ruolo **Incollaggio**
(rientrato di 1,5 mm dai bordi). Ogni forma chiusa con ruolo Incollaggio e
senza un riempimento scelto viene mostrata **a schermo** con un tratteggio
diagonale; esportazioni SVG/PDF, stampa di prova e anteprima di taglio sono
costruite dai dati delle forme, dove il riempimento resta "nessuno", quindi il
tratteggio non compare mai in stampa (resta solo il contorno della zona).

### Verso e angolo delle pieghe
Selezionando una linea con ruolo **Piega**, il pannello proprietà permette
di scegliere il verso — **a monte** (esterno) o **a valle** (interno) — e
l'**angolo di piegatura** (90° = angolo retto di una parete, 180° = piega
completa su se stessa). Sul disegno compare a metà di ogni tratto una
freccia a V che indica il verso, con l'angolo accanto.

### Simmetria (menu Strumenti → "⇅ Simmetria…")
Rende il disegno, o la selezione, simmetrico rispetto a un asse orizzontale
(sopra/sotto) o verticale (sinistra/destra). Si sceglie il lato da tenere come
riferimento; l'altro viene corretto. Due modalità:
- **Correggi il lato opposto**: ogni punto del lato da correggere viene portato
  nella posizione speculare esatta del punto corrispondente del lato tenuto,
  se ce n'è uno entro la tolleranza (predefinita 3 mm); i punti vicini
  all'asse vengono messi esattamente sull'asse. I punti senza corrispondente
  restano come sono (possono essere un'asimmetria voluta) e vengono cerchiati
  in arancione.
- **Rifai il lato opposto**: le forme interamente dal lato da rifare vengono
  sostituite dalla copia speculare di quelle del lato tenuto; quelle a cavallo
  dell'asse vengono corrette punto per punto.
L'asse (proposto al centro del disegno: in un disegno "quasi" simmetrico
conviene verificarlo) è mostrato sul disegno con la zona che verrà corretta;
un riepilogo dice in anticipo cosa cambierà. Dopo l'applicazione si
controlla il risultato (cerchi verdi) e si conferma o annulla.

### Esportazione per Silhouette Cameo (menu Stampa → "✂ Esporta per Silhouette Cameo (DXF)…")
Crea file DXF (formato R12, il più compatibile) in millimetri reali, che
Silhouette Studio apre anche nell'edizione gratuita Basic con le linee già
pronte per il taglio (gli SVG richiedono la Designer Edition). Contiene solo
le linee tecniche dei livelli visibili: Taglio e Fustella speciale (livello
TAGLIO, rosso). Per le pieghe si sceglie:
- **tratteggiate** nel file del taglio (predefinito): taglio a tratti, con
  lunghezza del tratto e del ponte regolabili (1,5 mm + 1,5 mm), pronte da
  tagliare e da piegare a mano;
- **nel file del taglio come linee continue** (livello PIEGA, blu): in Studio
  vanno selezionate e assegnate alla cordonatura o a una pressione ridotta;
- **in un file separato** "_pieghe.dxf", da passare in un secondo momento
  (Studio apre ogni file per conto suo: vanno posizionate con le coordinate);
- **non esportate**.
Curve, archi, ellissi e angoli arrotondati vengono approssimati con segmenti.
In Studio: File → Apri, selezionare tutto, verificare nel pannello Invia che le
linee siano su "Taglio" e controllare le misure prima di tagliare. Non
supporta il Print & Cut (stampa della grafica con i segni di registrazione).

### Importazione da PDF (pulsante ⇪ in alto)
Oltre agli SVG si possono importare i PDF, tipicamente modelli di scatole
scaricati online: le linee della pagina vengono lette in scala reale (1 punto
PDF = 25,4/72 mm) con pdf.js (Mozilla, licenza Apache 2.0, caricato da CDN solo
al primo uso). Una finestra mostra i gruppi di linee trovati, per colore e
tratteggio, e per ognuno si sceglie il ruolo (Taglio, Piega, Fustella speciale,
Incollaggio, Disegno o Ignora); proposta iniziale: linea continua più lunga =
taglio, tratteggiate = pieghe, riempimenti = grafica. Con più pagine si sceglie
quale importare. Le curve diventano spezzate fitte (circa 0,8 mm per segmento)
così che i contorni siano linee vere, usabili dal 3D. Testi e immagini del PDF
non vengono importati. Poi si prosegue come per un SVG (aggiungi/sostituisci,
scala, posizione) e si salva in libreria come sempre.

### Individuazione automatica di taglio e pieghe da un'immagine
Nella scheda Immagine, spuntando "Individua automaticamente le linee di taglio
e di piega", il pulsante cerca nell'immagine (scansione o schermata di una
fustella) le linee e le trasforma in linee vere, già unite tra loro: continue
nere/grigie/rosse = Taglio, continue blu/verdi = Piega, tratteggiate di
qualunque colore = Piega (i trattini vengono riuniti); testi e macchie
vengono scartati. Procedimento: classificazione dei pixel per colore,
assottigliamento (Zhang-Suen), tracciamento dello scheletro, semplificazione
(Ramer-Douglas-Peucker), raggruppamento dei trattini allineati, unione delle
estremità vicine. Le misure seguono le dimensioni dell'immagine sul foglio.

### Dividi in segmenti, Stacca
- **Dividi in segmenti** (pulsante nella barra a sinistra, sotto il cestino, o
  tasto destro): ogni lato di poligoni, rettangoli e linee a più tratti
  diventa una linea a sé, con stesso ruolo e stile, unita alle altre nei punti.
- **Stacca**: cliccando su un punto di una linea selezionata compare accanto
  il pulsante "✂ Stacca" (o "🔗 Riattacca"); il punto staccato si può
  trascinare lungo il segmento a cui era unito o su altri punti e linee, e se
  lo si lascia su un altro punto si riattacca alla nuova unione.

### Grafica nell'anteprima 3D
La grafica dei livelli di disegno viene riportata sui pannelli dell'anteprima
3D, sul lato stampato (esterno), e li segue mentre si piegano, con luce e
ombre. È grafica tutto ciò che non ha ruolo Taglio, Piega, Fustella speciale
o Incollaggio, sui livelli visibili (anche testo e immagini). La casella
"Grafica" accanto al cursore la mostra o la nasconde.

### Ordine di piegatura
Ogni piega ha un numero di **passo** (pannello proprietà → "Ordine di
piegatura", anche su più pieghe selezionate insieme, con − e +). Nella
simulazione 3D le pieghe si chiudono per passi, dal numero più basso; quelle
con lo stesso numero si chiudono insieme — così una linguetta non si chiude
prima delle alette che deve coprire. Sul disegno il passo compare accanto
all'angolo ("90° · passo 2") e nella finestra 3D accanto al cursore ("Passo 2
di 3"); ogni passo dell'animazione accelera e rallenta per conto suo. Le
scatole generate hanno già un ordine sensato: scatola americana 1 tubo,
2 alette corte, 3 alette lunghe (ora una piega per ogni aletta); astuccio
1 tubo, 2 alette antipolvere, 3 aletta posteriore, 4 linguetta di chiusura.

**Ordine automatico**: nella finestra dell'anteprima 3D ("Ordine automatico
delle pieghe" → "Applica e simula", per tutte le pieghe) o nel pannello
proprietà di una piega ("Ordina": per le pieghe selezionate, o per tutte se
ne è selezionata una sola) si può assegnare l'ordine con un criterio:
dal pannello principale verso l'esterno (profondità nell'albero di
piegatura, come si piega davvero una scatola), da sinistra a destra, da
destra a sinistra, dall'alto in basso, dal basso in alto, dal centro verso
l'esterno, oppure tutte insieme (azzera). Le pieghe alla stessa posizione o
distanza (entro 2 mm) finiscono nello stesso passo. Ctrl+Z annulla.

**Sequenza di piegatura** (menu Strumenti, pulsante "📋 Sequenza…" nella
finestra 3D, o "📋 Modifica la sequenza…" nel pannello di una piega): elenco
dei passi nell'ordine in cui avvengono, ciascuno con le sue pieghe descritte
(orientamento, lunghezza, posizione, verso e angolo). Si riordina
trascinando: una piega su un altro passo la sposta lì, nello spazio tra due
passi crea un passo nuovo; l'intestazione di un passo lo sposta tutto, o lo
unisce a un altro passo se rilasciata sopra. L'elenco è **pezzo per pezzo**: ogni tratto
di piega tra due pannelli è una voce a sé, anche quando una sola linea
attraversa più pannelli; spostando un solo tratto la linea viene divisa
automaticamente nei suoi tratti. Le pieghe non usate nel 3D compaiono in grigio. In alternativa le frecce ↑ ↓
sulle pieghe e sui passi. Passando col mouse su una piega la si vede
evidenziata sul disegno; "Simula nel 3D" mostra subito il risultato. Ogni
modifica si annulla con Ctrl+Z.

### Anteprima 3D piegata (menu Strumenti)
Ricava i pannelli dai contorni di **taglio** chiusi, divisi dalle linee di
**piega**, e li piega in 3D usando verso e angolo di ogni piega (quelle
senza verso impostato sono considerate a monte, 90°). "▶ Simula piegatura"
anima il passaggio da foglio piatto a scatola chiusa; il cursore permette di
fermarsi a qualunque punto intermedio. Trascina per ruotare, rotella per lo
zoom. Lato chiaro = lato stampato (esterno), color cartone = interno.
**Controllo del disegno**: aprendo l'anteprima 3D, sotto il modello compare
l'elenco di tutto ciò che non è stato possibile usare, con il motivo —
pieghe che non arrivano al contorno (con la distanza mancante in mm), pieghe
sul bordo esterno, contorni di taglio non chiusi, pannelli non collegati da
nessuna piega, elementi su livelli nascosti o di tipo non gestito, linee
curve approssimate. "Mostra sul disegno" chiude la finestra, seleziona gli
elementi e li cerchia in rosso (i cerchi spariscono alla prima modifica).
Le pieghe che si fermano a meno di 5 mm da un'altra linea si possono
allungare automaticamente fino al contorno con un clic.
Con "Mostra sul disegno" (o riducendo a icona l'anteprima 3D) gli errori
restano evidenziati sul disegno e il controllo viene rifatto dopo ogni
modifica: le linee coinvolte hanno un alone rosso (errore) o arancione
(avviso) con il numero del problema, le estremità libere lampeggiano e la
distanza mancante è disegnata con la scritta "manca X mm". Una barra in alto
dice quanti problemi restano, permette di passare dall'uno all'altro (◀ ▶,
la vista si sposta sul problema) e diventa verde quando è tutto a posto;
"Riprendi anteprima 3D" torna al modello aggiornato.
**Correzione automatica con revisione**: per ogni problema che si può
sistemare da solo compare "🔧 Correggi" (nell'elenco della finestra 3D e
nella barra in alto; "Correggi tutti" li applica in un colpo). Le correzioni
possibili sono: unire un'estremità all'estremità vicina di un'altra linea
(entro 5 mm), prolungare una linea che si ferma poco prima della successiva,
chiudere un contorno i cui capi distano meno di 10 mm, e assegnare il ruolo
Taglio a una linea che prosegue il contorno ma ha un altro ruolo. Dopo la
correzione i punti modificati sono cerchiati di verde e la barra chiede
**✓ Conferma** o **↶ Annulla** (che riporta il disegno esattamente
com'era); qualunque altra modifica la conferma implicitamente.
**Pieghe bloccate dal contorno**: se una piega tocca correttamente le altre
linee ma non viene usata perché il contorno di taglio intorno è aperto da
un'altra parte, non viene più segnalata come errore della piega: compare in
arancione, in fondo all'elenco, con l'indicazione dei problemi del contorno
che la bloccano ("➜ Vai al problema n") e, se questi sono correggibili, con
"🔧 Correggi" che sistema direttamente il contorno. La barra in alto separa
errori veri, pieghe bloccate e numero di problemi correggibili in automatico.
**Tagli voluti**: una linea di taglio aperta che non ha niente da unire lì
vicino (taglio di scarico, asola, taglio dritto) non è un errore: compare solo
come informazione. Se una piega resta bloccata e nessuna causa è correggibile
in automatico, il controllo fa lampeggiare le estremità libere di taglio più
vicine alla piega, cioè dove il contorno probabilmente si interrompe.
**Pieghe sovrapposte al bordo**: se una piega (per esempio disegnata a L)
ha all'inizio o alla fine un tratto sopra il taglio del bordo esterno, il
controllo dice dove e quanto è lungo, e la correzione lo toglie; se la piega
coincide tutta con il bordo, la correzione la elimina.
**Linee chiuse su se stesse**: un segmento dritto marcato come "chiuso" (come
faceva "Chiudi tutti i percorsi aperti" nelle versioni precedenti) sembra
normale ma non ha più estremità; il controllo lo segnala e lo riapre con un
clic. "Chiudi tutti i percorsi aperti" ora chiude solo contorni veri (almeno 3
punti, non pieghe né archi, con entrambe le estremità libere). Nel 3D i pannelli non
collegati da nessuna piega sono colorati di rosso.
Limiti attuali: i tratti curvi sono approssimati con segmenti dritti, i fori
non vengono ritagliati dal pannello nella vista 3D, e le pareti che si
compenetrano (se gli angoli non sono coerenti) vengono mostrate così come
risultano, senza correzioni automatiche.


### Lingua dell'interfaccia
Selettore in alto nella barra degli strumenti: Italiano (predefinito), English,
Deutsch, Español, 中文, Français. Traduce menu, pulsanti, tooltip, pannello
proprietà, libreria e messaggi. La scelta viene ricordata tra una sessione e
l'altra. I nomi che l'utente sceglie liberamente (progetti, cartelle,
livelli, testo nel disegno) non vengono mai tradotti automaticamente.


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

### Descrizione e istruzioni del progetto
Quando non c'è nessun oggetto selezionato, il pannello proprietà mostra la
scheda descrittiva del progetto: tipo di scatola, carta/materiale
consigliato, istruzioni di stampa e taglio, e i passaggi di montaggio/
incollaggio passo dopo passo (con riordino e eliminazione). Utile per
documentare la scatola per te stesso o per chi dovrà stamparla e montarla;
viene salvata insieme al resto del progetto (libreria, file .json, copie).

### Organizzazione del disegno
- **Unisci punti**: selezionando un'area (rettangolo o lazo con Alt), i
  punti liberi al suo interno — estremità di linee aperte che non toccano
  nient'altro — vengono numerati 1, 2, …, n sul disegno. Con il pulsante
  "🔗 Unisci punti…" nel pannello proprietà o dal menu tasto destro si
  apre una finestra con le coppie "Da → a" (già proposte: sempre la coppia più
  vicina, e le altre solo se a distanza simile — le estremità lontane non
  vengono proposte; si possono modificare, invertire con ⇄, aggiungere a mano).
  Funziona a qualunque distanza: basta selezionare le linee (clic e Shift+clic). Due modalità:
  **Sposta** (il punto di partenza raggiunge quello di arrivo, che resta
  fermo) o **Collega con un nuovo tratto**. Facoltativamente le linee con lo
  stesso ruolo e livello vengono fuse in un unico percorso, e una linea che
  si richiude su se stessa diventa un percorso chiuso. Le catene (un punto
  sia di partenza sia di arrivo) vengono segnalate e bloccate.
- **Guide di allineamento**: spostando un oggetto (o una selezione), i suoi
  bordi e il suo centro vengono confrontati con quelli degli altri oggetti e
  del foglio; quando si allineano compare una linea guida (continua per i
  bordi, tratteggiata per i centri; azzurra se l'allineamento è con il foglio).
  Con il magnete acceso l'oggetto si aggancia all'allineamento quando è a meno
  di 6 pixel; tenendo premuto **Alt** lo si sposta liberamente.
- **Livelli**: si rinominano con il pulsante ✎ accanto al nome, con un doppio
  clic sul nome o dal tasto destro sul livello; Invio conferma, Esc annulla.
  L'elenco funziona come un accordion: il clic sul titolo di un livello apre
  l'elenco dei suoi oggetti (e lo rende attivo) chiudendo gli altri; un nuovo
  clic lo richiude. La freccia ▸/▾ indica lo stato, il numero accanto al nome
  quanti oggetti contiene. Il livello appena creato è quello aperto.
- **Magnete sulle linee**: oltre ai vertici, un punto si aggancia al punto
  più vicino di qualsiasi linea (tratti, archi, lati di rettangoli ed
  ellissi). Serve ad esempio per far partire una piega esattamente sul
  contorno di taglio. I vertici hanno la precedenza sulle linee.
- **Archi**: lo strumento Arco (clic su inizio, fine e un punto di passaggio)
  crea una linea vera, con i punti sulla circonferenza abbastanza fitti da
  scostarsi al massimo 0,05 mm dalla curva: prende colore e tratteggio del
  ruolo, le estremità si agganciano col magnete e si collegano agli altri
  punti, e vale per anteprima 3D, operazioni booleane, Unisci/Scollega.
  Mostra solo le maniglie delle estremità: trascinandole (o spostando una
  linea collegata) l'arco viene ricalcolato e resta un arco. Le quote
  indicano lunghezza e raggio. Gli archi creati con le versioni precedenti
  vengono convertiti automaticamente all'apertura del progetto.
- **Scollega / Ricollega**: i punti che coincidono sono uniti (spostandone
  uno si trascina l'altro). Un punto si può scollegare restando dov'è: dal
  tasto destro direttamente sulla sua maniglia, oppure con "⛓ Scollega" accanto
  al punto nel pannello coordinate. Con "⛓ Scollega tutti i vertici" (pannello
  proprietà o tasto destro sulla forma) si scollegano in un colpo tutti i punti
  della forma o delle forme selezionate: spostandole, le linee collegate restano
  ferme. I punti scollegati hanno la maniglia viola tratteggiata, contano come
  punti liberi (quindi compaiono anche in "Unisci punti") e si ricollegano con
  "🔗 Ricollega" o unendoli di nuovo.
- **Operazioni booleane** (con 2 o più forme selezionate, dal pannello
  proprietà o dal tasto destro): ⊕ Unione, ⊖ Sottrai (toglie dalla forma più
  in basso quelle che le stanno sopra), ⊗ Intersezione, ⊘ Esclusione. Valgono
  per percorsi chiusi, rettangoli ed ellissi (anche ruotati; le curve vengono
  campionate); le linee aperte vengono ignorate con un avviso. Il risultato
  prende stile e livello della forma più in basso; i fori interni diventano
  linee chiuse separate (per la fustella sono tagli interni; se la forma è
  riempita, il foro compare come contorno). Usa la libreria polygon-clipping
  (MIT, inclusa nel file: vedi LICENSE-polygon-clipping.md).
- **Finestre riducibili a icona**: le finestre di lavoro (Unisci punti,
  Anteprima 3D, Genera scatola, Libreria, Cronologia, Salda punti, Stampa…)
  hanno il pulsante ▁ nell'intestazione: la finestra si riduce nella barra
  in basso, si modifica il disegno e poi la si riprende con un clic, com'era
  rimasta (l'Anteprima 3D si ricalcola sul disegno aggiornato). La ✕
  nella barra la chiude. Conferme, richieste di testo e accesso non sono
  riducibili, perché aspettano una risposta immediata.
- **Cronologia annulla/ripeti visuale**: il pulsante 🕐 in alto (accanto ad
  Annulla/Ripeti) apre l'elenco di tutti gli stati passati del disegno
  (fino a 300), con orario e numero di forme — clic su uno qualsiasi per
  tornarci direttamente, senza premere Annulla passo per passo.
- **Libreria componenti** (scheda "Componenti" nel pannello laterale):
  pezzi pronti da inserire con un clic al centro del foglio — aletta
  standard, foro europeo, maniglia, linguetta, gancio — che si aggiungono
  come forme normali, modificabili come qualunque altra. Sotto ai
  predefiniti, qualunque progetto salvato come **condiviso** (libreria
  comune) compare automaticamente anche qui, pronto da inserire allo
  stesso modo — un modo pratico per costruire una libreria di pezzi
  ricorrenti senza doverli ridisegnare ogni volta.
- **Selezione avanzata**: oltre al rettangolo di selezione c'è la selezione
  a **lazo** (percorso libero): pulsante nella barra degli strumenti subito
  sotto la freccia, tasto **Q** (V torna alla freccia), oppure tenendo premuto
  **Alt** durante il trascinamento sull'area vuota con la freccia. Dal menu tasto destro su un oggetto:
  **"Seleziona stesso colore"** e **"Seleziona stesso ruolo"** selezionano
  tutte le forme visibili che condividono quella caratteristica.
- **Verifica sovrapposizioni e duplicati** (menu Strumenti): individua forme
  duplicate (stesso tracciato, anche percorso in verso opposto) e tratti di
  taglio che si sovrappongono anche solo parzialmente — le forme coinvolte
  vengono selezionate automaticamente.
- **Allinea e distribuisci**: con 2+ forme selezionate, il pannello proprietà
  mostra i comandi per allineare i bordi (sinistra/destra/centro orizzontale,
  alto/basso/centro verticale) e, con 3+ forme, distribuirle uniformemente.
- **Mirror (rifletti)**: ribalta specularmente in orizzontale o verticale la
  selezione (una o più forme), attorno al centro del suo ingombro complessivo.
  Per immagini e tracciati vettorizzati ribalta anche il contenuto visivo,
  non solo la posizione.
- **Offset / parallelo**: per una forma a punti selezionata, crea una copia
  parallela spostata di una distanza esatta (positiva = verso l'esterno,
  negativa = verso l'interno) — utile per alette, margini, abbondanze.
- **Input numerico durante il disegno**: disegnando una linea, digitare dei
  numeri ne impone la lunghezza esatta nell'unità di misura corrente,
  lasciando comunque libero il mouse per puntare la direzione — Invio
  conferma subito.

- **Livelli**: mostra/nascondi, blocca, riordina, con contenuto separato
  per ciascuno; ogni forma appartiene a un livello.
- **Ordine di sovrapposizione**: dentro allo stesso livello, ogni forma
  può essere portata avanti/indietro o in primo/secondo piano, come nei
  programmi di disegno classici.
- **Menu contestuale (tasto destro)** su un oggetto: copia, duplica,
  elimina, ordine di sovrapposizione, blocca/sblocca; sull'area vuota del
  foglio, se c'è qualcosa negli appunti, incolla esattamente nel punto
  cliccato.
- **Schede multiple**: più disegni aperti contemporaneamente, ognuno con
  il proprio foglio, storico annulla/ripeti e libreria.

### Precisione
Righelli, griglia con calamita — passo regolabile **millimetrico o
centimetrico** — quotature in tempo reale durante il disegno e quotature
permanenti dell'intero pezzo, unità di misura selezionabile (mm/cm/m/
pollici), verifica automatica dei percorsi aperti e saldatura dei punti
vicini. Attivando l'opzione "punti" (●) nella barra di stato, oltre agli
estremi liberi/collegati vengono segnati anche gli **angoli** tra i
tratti che si incontrano in ogni vertice — sia tra due segmenti dello
stesso percorso, sia tra due forme diverse i cui estremi coincidono —
con la relativa quotazione in gradi.

Doppio clic su un tratto (strumento Selezione) apre un piccolo editor
**inline** sul posto, con lunghezza e curvatura modificabili subito,
senza cercare il punto corrispondente nella barra laterale; da lì si può
anche aggiungere un vertice esattamente dove si è cliccato. Nella barra
laterale, ogni punto ha inoltre una ✕ per eliminarlo direttamente (con
conferma, e un minimo di 2 punti per percorso).

Il **foglio** può avere uno sfondo di qualunque colore, o essere
completamente trasparente (utile per materiali colorati o per
sovrapporre il disegno ad altro): si imposta dalla topbar, ed è salvato
per singolo progetto.

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
  server ed è raggiungibile da qualunque dispositivo. Se il progetto è
  già collegato alla libreria (perché l'hai aperto da lì, o già salvato
  in precedenza), un clic lo sovrascrive **subito**, senza aprire nessuna
  finestra. La finestra completa (nome, cartella, condivisione) compare
  solo la prima volta, oppure richiamandola apposta dal menu File →
  "Salva in libreria (server)" — utile per salvare con un altro nome o
  in un'altra cartella senza toccare l'originale.

C'è anche l'esportazione/importazione di un file progetto `.json` da
scaricare e riaprire manualmente, indipendente da entrambi i sistemi sopra.

---

## 3. Account utente e libreria condivisa (backend PHP)

Nessun database: tutto è composto da file `.json` sul server, gestiti
dalle pagine PHP in `api/`. Le password non sono mai salvate in chiaro
(sempre come hash).

### Account
Registrazione (nome utente + email + password), accesso, disconnessione,
recupero password via email con link a tempo (1 ora). L'accesso usa un
token firmato (JWT) anziché sessioni/cookie: il server verifica solo la
firma del token ad ogni richiesta, senza bisogno di conservare nulla in
memoria; un logout esplicito resta comunque efficace da subito, anche
prima della scadenza naturale del token (vedi LEGGIMI.txt per i dettagli
e l'avviso importante sull'header "Authorization" su hosting Apache).

### Libreria progetti
Ogni progetto salvato ha un proprietario. Al momento del salvataggio si
può scegliere se tenerlo **privato** (visibile solo a chi l'ha creato)
oppure **condividerlo nella libreria comune** (visibile e apribile da
tutti gli utenti registrati). Solo il proprietario può modificare o
eliminare un proprio progetto, anche se condiviso; aprire un progetto
condiviso altrui e poi salvarlo crea automaticamente una copia personale,
senza toccare l'originale.

La libreria è organizzata in **cartelle** (anche annidate), con
un'interfaccia in stile Esplora Risorse: albero delle cartelle a
sinistra, elenco con nome/data/dimensione a destra. Si possono creare,
rinominare ed eliminare cartelle, e **trascinare** (drag & drop) un
progetto o un'intera cartella su un'altra per spostarli. È disponibile
anche un **menu contestuale** (tasto destro) con copia/taglia/incolla,
rinomina ed elimina — copiare o tagliare una cartella porta con sé tutto
il suo contenuto, sottocartelle comprese.

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

- **Guida**: i contenuti sono nell'array `GUIDE_TOPICS` in `index.html`
  (titolo, capitolo, parole chiave per la ricerca, testo HTML; i collegamenti
  tra argomenti si scrivono `<a data-topic="id">`). Va aggiornata insieme a
  ogni modifica del programma, compresi `GUIDE_VERSION` e l'argomento
  "Novità".

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
