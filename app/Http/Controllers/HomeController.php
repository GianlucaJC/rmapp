<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Mostra la homepage pubblica dell'applicazione.
     */
    public function index(): View
    {
        // Per ora, questa funzione restituisce semplicemente la vista 'home'.
        // In futuro, potremmo passare dati a questa vista dal database.
        return view('home');
    }

    /**
     * Mostra la pagina di selezione dei servizi Cassa Edile / Edilcassa.
     */
    public function serviziIndex(): View
    {
        return view('servizi.index');
    }

    /**
     * Mostra l'elenco delle prestazioni della Cassa Edile.
     */
    public function cassaEdile(): View
    {
        // In futuro, questi dati verranno dal database.
        $prestazioniCassaEdile = [
            ['nome' => 'Pagamento GNF', 'icona' => 'bi-cash-stack', 'descrizione' => 'Erogazione del trattamento economico per ferie e gratifica natalizia maturate nel periodo ottobre-marzo e aprile-settembre.'],
            ['nome' => 'Pagamento APE', 'icona' => 'bi-person-workspace', 'descrizione' => 'Un premio per l\'Anzianità Professionale Edile, riconosciuto agli operai che vantano almeno 2.100 ore in un biennio.'],
            ['nome' => 'Regolamento APE – CCNL Allegato C Comma 4', 'icona' => 'bi-file-earmark-ruled', 'descrizione' => 'Diritto al cumulo delle ore di lavoro per operai che si trasferiscono in altre circoscrizioni territoriali, ai fini della prestazione APE.'],
            ['nome' => 'Indennità per Carenza Malattia', 'icona' => 'bi-bandaid', 'descrizione' => 'Un\'indennità per i primi 3 giorni di malattia (carenza) per eventi di durata superiore a 5 giorni.'],
            ['nome' => 'Borse di Studio', 'icona' => 'bi-mortarboard-fill', 'descrizione' => 'Assegnazione di borse di studio per i figli dei lavoratori iscritti che frequentano scuole medie superiori o università.'],
            ['nome' => 'Assistenza allo Studio', 'icona' => 'bi-book-half', 'descrizione' => 'Contributo per l\'acquisto di libri di testo per i figli dei lavoratori che frequentano la scuola media inferiore e superiore.'],
            ['nome' => 'Premio Giovani', 'icona' => 'bi-award-fill', 'descrizione' => 'Un premio per i giovani che entrano per la prima volta nel settore edile, al raggiungimento di 300 ore di lavoro.'],
            ['nome' => 'Fondo Prepensionamento', 'icona' => 'bi-hourglass-bottom', 'descrizione' => 'Incentivo all\'esodo per i lavoratori prossimi alla pensione, per favorire il ricambio generazionale nel settore.'],
            ['nome' => 'Pacchetti Vacanze e Soggiorni', 'icona' => 'bi-suitcase-lg-fill', 'descrizione' => 'Offerte di pacchetti vacanze e soggiorni estivi a condizioni agevolate per i lavoratori e le loro famiglie.'],
            ['nome' => 'Fondi Pensione', 'icona' => 'bi-piggy-bank-fill', 'descrizione' => 'Informazioni e supporto per l\'adesione ai fondi di pensione complementare del settore, come PREVEDI.'],
            ['nome' => 'Fondo SANEDIL', 'icona' => 'bi-heart-pulse-fill', 'descrizione' => 'Il Fondo Sanitario Nazionale che offre un piano di prestazioni sanitarie integrative per i lavoratori e i loro familiari.'],
            ['nome' => 'Indennizzo per la Donazione del Midollo Osseo', 'icona' => 'bi-droplet-half', 'descrizione' => 'Un contributo economico per i lavoratori che si sottopongono alla donazione di midollo osseo.'],
            ['nome' => 'Indennizzo per la Donazione del Sangue', 'icona' => 'bi-droplet-fill', 'descrizione' => 'Un contributo economico per i lavoratori che effettuano donazioni di sangue, per ogni donazione.'],
            ['nome' => 'Assistenza per Alcolismo, HIV, Tossicodipendenza', 'icona' => 'bi-people-fill', 'descrizione' => 'Sostegno economico e assistenza per lavoratori affetti da gravi patologie o dipendenze che richiedono cure specifiche.'],
            ['nome' => 'Supporti e protesi acustiche', 'icona' => 'bi-ear-fill', 'descrizione' => 'Contributo per l\'acquisto di apparecchi acustici per il lavoratore o i familiari a carico.'],
            ['nome' => 'Indennità per Malattia superiore al 180° giorno', 'icona' => 'bi-calendar-plus-fill', 'descrizione' => 'Un\'indennità per i periodi di malattia che superano i 180 giorni nell\'anno solare, fino a un massimo di ulteriori 90 giorni.'],
            ['nome' => 'Assistenza ai Familiari Portatori di Handicap', 'icona' => 'bi-person-wheelchair', 'descrizione' => 'Contributo economico per l\'assistenza a familiari a carico con handicap grave riconosciuto ai sensi della L. 104/92.'],
            ['nome' => 'Assegno Funerario per lavoratore e familiari', 'icona' => 'bi-flower1', 'descrizione' => 'Un contributo per le spese funerarie in caso di decesso del lavoratore, del coniuge o dei figli a carico.'],
            ['nome' => 'Invalidità Totale da Malattia dei Familiari', 'icona' => 'bi-person-exclamation', 'descrizione' => 'Sussidio una tantum in caso di invalidità totale e permanente (100%) di un familiare a carico.'],
            ['nome' => 'Ricovero per Infortunio professionale', 'icona' => 'bi-hospital-fill', 'descrizione' => 'Indennità giornaliera in caso di ricovero ospedaliero a seguito di infortunio sul lavoro.'],
            ['nome' => 'Invalidità permanente per Infortunio professionale', 'icona' => 'bi-person-slash', 'descrizione' => 'Indennizzo proporzionale al grado di invalidità permanente (dal 6%) a seguito di infortunio sul lavoro.'],
            ['nome' => 'Decesso per Infortunio professionale', 'icona' => 'bi-person-x-fill', 'descrizione' => 'Indennizzo ai familiari beneficiari in caso di decesso del lavoratore per infortunio sul lavoro.'],
            ['nome' => 'Ricovero per Infortunio extraprofessionale', 'icona' => 'bi-first-aid-fill', 'descrizione' => 'Indennità giornaliera in caso di ricovero ospedaliero a seguito di infortunio avvenuto fuori dall\'orario di lavoro.'],
            ['nome' => 'Invalidità permanente per Infortunio extraprofessionale', 'icona' => 'bi-person-slash', 'descrizione' => 'Indennizzo in caso di invalidità permanente (superiore al 25%) a seguito di infortunio extraprofessionale.'],
            ['nome' => 'Decesso per Infortunio extraprofessionale', 'icona' => 'bi-person-x-fill', 'descrizione' => 'Indennizzo ai familiari beneficiari in caso di decesso del lavoratore per infortunio extraprofessionale.'],
            ['nome' => 'Decesso del lavoratore per malattia', 'icona' => 'bi-person-x-fill', 'descrizione' => 'Indennizzo ai familiari beneficiari in caso di decesso del lavoratore per malattia comune.'],
        ];

        return view('servizi.cassa-edile', compact('prestazioniCassaEdile'));
    }

    /**
     * Mostra l'elenco delle prestazioni della Edilcassa.
     */
    public function edilcassa(): View
    {
        // In futuro, questi dati verranno dal database.
        $prestazioniEdilcassa = [
            ['nome' => 'FERIE E GRATIFICA NATALIZIA', 'icona' => 'bi-cash-stack', 'descrizione' => 'Erogazione accantonamenti per ferie e gratifica natalizia.'],
            ['nome' => 'INTEGRAZIONE ALL’INDENNITÀ DI MALATTIA', 'icona' => 'bi-bandaid-fill', 'descrizione' => 'Integrazione del trattamento economico fornito dall\'INPS durante i periodi di malattia.'],
            ['nome' => 'INTEGRAZIONE ALL’INDENNITÀ DI INFORTUNIO O MALATTIA PROFESSIONALE', 'icona' => 'bi-hospital', 'descrizione' => 'Integrazione del trattamento economico fornito dall\'INAIL per infortuni o malattie professionali.'],
            ['nome' => 'NORME COMUNI A MALATTIA E INFORTUNIO', 'icona' => 'bi-file-earmark-medical-fill', 'descrizione' => 'Regolamenti e disposizioni comuni per la gestione delle pratiche di malattia e infortunio.'],
            ['nome' => 'ANZIANITÀ PROFESSIONALE EDILE (A.P.E.)', 'icona' => 'bi-person-workspace', 'descrizione' => 'Premio annuale per i lavoratori con una determinata anzianità nel settore edile.'],
            ['nome' => 'fondo sanedil', 'icona' => 'bi-heart-pulse-fill', 'descrizione' => 'Accesso alle prestazioni sanitarie integrative offerte dal fondo sanitario nazionale Sanedil.'],
        ];

        return view('servizi.edilcassa', compact('prestazioniEdilcassa'));
    }
}
