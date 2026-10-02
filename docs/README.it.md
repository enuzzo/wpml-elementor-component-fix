# WPML + componenti Elementor V4: guida italiana

**Netmilk — WPML Elementor Component Fix** è un adattatore temporaneo per i testi degli override espliciti dei componenti V4 che, nel formato `escaped-html`, possono mancare nell'export WPML. Riutilizzabile su siti e lingue diversi: non contiene ID, domini o contenuti di clienti.

Il checkout prepara una **candidata 1.0.3 non pubblicata**, che adatta in memoria il tipo `escaped-html` dei testi originari di heading e paragraph esposti dal master, delegando estrazione e import al gestore nativo WPML. Conserva inoltre la correzione dei nomi dei moduli introdotta dalla candidata 1.0.2. Identità e import restano nativi; le configurazioni sconosciute o già gestite non vengono sostituite. I controlli interni su testo e HTML lasciano lavorare WPML quando il supporto nativo funziona. La correzione dei nomi dei moduli resta un controllo di registrazione. La release pubblica resta **1.0.1** e lo ZIP 1.0.2 rimane congelato. [Diagnosi del master e limiti](master-origin-compatibility.md).

## Installazione e prova

1. Scarica lo ZIP installabile dalla [release](https://github.com/enuzzo/wpml-elementor-component-fix/releases/latest), non lo ZIP dei sorgenti GitHub.
2. WordPress → Plugin → Aggiungi nuovo → Carica plugin, poi attivalo.
3. Parti da una pagina sorgente in bozza con due istanze e testi differenti impostati come override espliciti.
4. Dashboard di traduzione WPML → assegna al traduttore locale → genera nuovi lavori → esporta XLIFF 1.2.
5. Controlla che siano presenti i testi attesi; traduci solo i target, preservando source, ID e markup; importa con WPML.
6. Verifica la pagina tradotta pubblica, inclusi testi, link e responsive. Non correggere direttamente la destinazione con Elementor.

Non servono ATE o traduzione automatica. Gli export vecchi non acquisiscono i campi mancanti. Per i master, la candidata tratta soltanto il formato dei testi originari diretti di heading/paragraph: non risolve automaticamente tutta l'eredità, i riferimenti annidati o i valori null e non crea override nelle pagine.

## Rimozione

Il controllo interno lascia lavorare il gestore nativo quando estrazione e import di testo semplice e HTML superano entrambe le prove. Non disattiva né elimina automaticamente il plugin.

Dopo un aggiornamento ufficiale, disattivalo in un ambiente di prova e verifica un nuovo ciclo completo sui campi reali. Se export, import e resa funzionano, puoi eliminarlo: non salva dati propri. Se il gestore nativo resta incompleto, i lavori futuri possono nuovamente omettere i testi.

## Stato e limiti

La release pubblica 1.0.1 è verificata con test sintetici del contratto e una matrice CI PHP. Non equivale a una certificazione del runtime WordPress/WPML/Elementor, e il suo ciclo live completo resta da verificare.

La candidata 1.0.3 porta i test sintetici a 32 scenari. Il nuovo export di master sintetici ha mostrato soltanto il titolo documento sia con 1.0.2 sia col vecchio adattatore: è una lacuna preesistente riportata, non una regressione dimostrata. Prima di accettare la 1.0.3 servono nuovi job del master e delle pagine, import WPML, verifica del registro delle proprietà e rendering ereditato. I binding null/inoltrati restano intatti: possono rappresentare un’eredità valida, che questa candidata non risolve autonomamente. Restano inoltre le omissioni native dei testi vuoti e della stringa `"0"` nei master. Prima di sostituire un addon locale per i nomi dei moduli, controllarne il comportamento e seguire le istruzioni del progetto del sito.

[README completo e FAQ](../README.md) · [Funzionamento tecnico](architecture.md) · [Segnala un problema](https://github.com/enuzzo/wpml-elementor-component-fix/issues/new/choose)
