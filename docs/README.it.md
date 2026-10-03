# WPML + componenti Elementor V4: guida italiana

**Netmilk — WPML Elementor Component Fix** è un adattatore temporaneo per i testi degli override espliciti dei componenti V4 che, nel formato `escaped-html`, possono mancare nell'export WPML. Riutilizzabile su siti e lingue diversi: non contiene ID, domini o contenuti di clienti.

La release pubblica è **1.0.5**. Conserva le correzioni della 1.0.4, che gestisce anche le etichette delle opzioni select delegando a WPML e preservando i valori tecnici. I test locali mantengono distinte le etichette duplicate anche dopo il riordinamento. Nei primi XLIFF reali `"0"` è omesso, ma entrambi i cicli live ne confermano la conservazione nei dati e nel rendering. Elementor elimina l’opzione vuota già al salvataggio della sorgente: nel test live persistono quattro righe, mentre le fixture locali ne contengono cinque.

Conserva il comportamento della 1.0.3, che adatta in memoria il tipo `escaped-html` dei testi originari di heading e paragraph esposti dal master, delegando estrazione e import al gestore nativo WPML. Conserva inoltre la correzione dei nomi dei moduli introdotta dalla candidata 1.0.2. Identità e import restano nativi; le configurazioni sconosciute o già gestite non vengono sostituite. I controlli interni su testo e HTML lasciano lavorare WPML quando il supporto nativo funziona. La correzione dei nomi dei moduli resta un controllo di registrazione. Le candidate 1.0.2 e 1.0.3 non sono state pubblicate separatamente; i loro ZIP rimangono congelati. [Diagnosi del master e limiti](master-origin-compatibility.md).

## Installazione e prova

1. Scarica lo ZIP installabile dalla [release](https://github.com/enuzzo/wpml-elementor-component-fix/releases/latest), non lo ZIP dei sorgenti GitHub.
2. WordPress → Plugin → Aggiungi nuovo → Carica plugin, poi attivalo.
3. Parti da una pagina sorgente in bozza con due istanze e testi differenti impostati come override espliciti.
4. Dashboard di traduzione WPML → assegna al traduttore locale → genera nuovi lavori → esporta XLIFF 1.2.
5. Controlla che siano presenti i testi attesi; traduci solo i target, preservando source, ID e markup; importa con WPML.
6. Verifica la pagina tradotta pubblica, inclusi testi, link e responsive. Non correggere direttamente la destinazione con Elementor.

Non servono ATE o traduzione automatica. Gli export vecchi non acquisiscono i campi mancanti. Per i master, il plugin tratta soltanto il formato dei testi originari diretti di heading/paragraph: non risolve automaticamente tutta l'eredità, i riferimenti annidati o i valori null e non crea override nelle pagine.

## Rimozione

Il controllo interno lascia lavorare il gestore nativo quando estrazione e import di testo semplice e HTML superano entrambe le prove. Non disattiva né elimina automaticamente il plugin.

Dopo un aggiornamento ufficiale, disattivalo in un ambiente di prova e verifica un nuovo ciclo completo sui campi reali. Se export, import e resa funzionano, puoi eliminarlo: non salva dati propri. Se il gestore nativo resta incompleto, i lavori futuri possono nuovamente omettere i testi.

## Stato e limiti

La 1.0.4 supera 44 scenari sintetici; la CI verifica PHP 7.4, 8.1, 8.3 e 8.5. Separatamente, due cicli reali con nuovi lavori WPML hanno superato export XLIFF, import nativo e confronti del rendering inglese, francese e tedesco. Il secondo ciclo verifica modifiche a testo, HTML, link e nomi dei moduli, più riordinamento delle opzioni con identità stabili. I dati salvati non contengono ID o alias temporanei. Le otto pagine pubbliche di controllo rispondono 200 e mantengono i nomi dei moduli della baseline. Un overflow grafico rilevato è stato riprodotto anche col vecchio adattatore: non risulta introdotto dalla 1.0.4.

Il registro delle proprietà dei master tradotti resta assente nella fixture, ma i casi di eredità e annidamento collaudati funzionano senza sincronizzazioni aggiuntive. Non equivale al supporto generale di ogni binding o flusso di modifica del componente. Restano le omissioni native dei testi vuoti e di `"0"` nei master. La select conserva `"0"` nei dati e nel rendering, pur senza unità XLIFF live; l’opzione vuota viene rimossa da Elementor prima di WPML. Consulta la [matrice anonima con versioni e limiti](releases/1.0.4.md).

Lo ZIP ufficiale è identico al candidato collaudato, checksum incluso. Il readme interno conserva la vecchia dicitura “candidate” per non alterare quei byte: note di release e documentazione GitHub riportano l’accettazione definitiva. Prima di sostituire un addon locale, leggere le istruzioni del sito e verificare le traduzioni prima dell’aggiornamento, dopo l’attivazione e dopo ogni import nativo. Su un altro stack occorre ripetere la prova.

Un audit successivo ha rilevato testi numerici ereditati mancanti in alcuni master tradotti in passato, con origine `html-v3` mentre la sorgente usa già `escaped-html`. La 1.0.4 non migra quei dati all’attivazione. Il successivo [recupero tramite nuovo lavoro WPML del master sorgente](legacy-master-recovery.md) ha ripristinato il titolo risolto e i numeri nelle pagine inglese e francese, senza modifiche dirette alle destinazioni. L’XLIFF ometteva il numero `"1"`: la sua presenza non è quindi un requisito assoluto per il recupero. Le prove successive documentano letture risolte e HTML anonimo; il tipo esatto nel dato grezzo salvato non è stato verificato indipendentemente in questa sessione. Sugli altri master servono autorizzazione del sito, controllo dell’export, import nativo e confronto di dati salvati/rendering. La suite di sviluppo aggiunge questo limite come 45° scenario sintetico; runtime e ZIP 1.0.4 restano invariati.

[README completo e FAQ](../README.md) · [Funzionamento tecnico](architecture.md) · [Segnala un problema](https://github.com/enuzzo/wpml-elementor-component-fix/issues/new/choose)

## Classi globali — 1.0.5

La 1.0.5 aggiunge un fallback per le classi globali rimaste irrisolte durante il rendering dei kit tradotti. Verifica il collegamento WPML con il kit sorgente e recupera soltanto nomi non dichiarati nel kit tradotto, senza cambiare metadati, CSS o traduzioni. Editor e anteprima sono esclusi. I quattro filtri di traduzione restano invariati.

La suite comprende 63 scenari sintetici. Lo ZIP esatto ha superato il collaudo registrato: 24 pagine anonime senza classi irrisolte, 12 pagine a larghezze desktop/mobile e sei nuovi lavori WPML completati tramite import nativo, preservando gli 84 segmenti già tradotti. Gli hash dei metadati visibili via REST coincidono per tutti i 40 oggetti confrontati. Non sono stati inviati moduli reali né modificati testi sorgente in questo ciclo. La [matrice della release](releases/1.0.5.md) riporta checksum e limiti; il readme nello ZIP conserva la dicitura precedente per mantenere gli stessi byte collaudati.
