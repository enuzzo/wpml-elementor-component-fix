# WPML + componenti Elementor V4: guida italiana

**Netmilk — WPML Elementor Component Fix** è un adattatore temporaneo per i testi degli override espliciti dei componenti V4 che, nel formato `escaped-html`, possono mancare nell'export WPML. Riutilizzabile su siti e lingue diversi: non contiene ID, domini o contenuti di clienti.

## Installazione e prova

1. Scarica lo ZIP installabile dalla [release](https://github.com/enuzzo/wpml-elementor-component-fix/releases/latest), non lo ZIP dei sorgenti GitHub.
2. WordPress → Plugin → Aggiungi nuovo → Carica plugin, poi attivalo.
3. Parti da una pagina sorgente in bozza con due istanze e testi differenti impostati come override espliciti.
4. Dashboard di traduzione WPML → assegna al traduttore locale → genera nuovi lavori → esporta XLIFF 1.2.
5. Controlla che siano presenti i testi attesi; traduci solo i target, preservando source, ID e markup; importa con WPML.
6. Verifica la pagina tradotta pubblica, inclusi testi, link e responsive. Non correggere direttamente la destinazione con Elementor.

Non servono ATE o traduzione automatica. Gli export vecchi non acquisiscono i campi mancanti. I valori ereditati dal componente master, senza override nell'istanza, restano fuori dal perimetro.

## Rimozione

Il controllo interno lascia lavorare il gestore nativo quando estrazione e import di testo semplice e HTML superano entrambe le prove. Non disattiva né elimina automaticamente il plugin.

Dopo un aggiornamento ufficiale, disattivalo in un ambiente di prova e verifica un nuovo ciclo completo sui campi reali. Se export, import e resa funzionano, puoi eliminarlo: non salva dati propri. Se il gestore nativo resta incompleto, i lavori futuri possono nuovamente omettere i testi.

## Stato e limiti

La release pubblica 1.0.1 è verificata con test sintetici del contratto e una matrice CI PHP. Non equivale a una certificazione del runtime WordPress/WPML/Elementor, e il suo ciclo live completo resta da verificare.

[README completo e FAQ](../README.md) · [Funzionamento tecnico](architecture.md) · [Segnala un problema](https://github.com/enuzzo/wpml-elementor-component-fix/issues/new/choose)
