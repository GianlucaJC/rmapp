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
            ['nome' => 'Pagamento GNF', 'icona' => 'bi-cash-stack'],
            ['nome' => 'Pagamento APE', 'icona' => 'bi-person-workspace'],
            ['nome' => 'Regolamento APE – CCNL Allegato C Comma 4', 'icona' => 'bi-file-earmark-ruled'],
            ['nome' => 'Indennità per Carenza Malattia', 'icona' => 'bi-bandaid'],
            ['nome' => 'Borse di Studio', 'icona' => 'bi-mortarboard-fill'],
            ['nome' => 'Assistenza allo Studio', 'icona' => 'bi-book-half'],
            ['nome' => 'Premio Giovani', 'icona' => 'bi-award-fill'],
            ['nome' => 'Fondo Prepensionamento', 'icona' => 'bi-hourglass-bottom'],
            ['nome' => 'Pacchetti Vacanze e Soggiorni', 'icona' => 'bi-suitcase-lg-fill'],
            ['nome' => 'Fondi Pensione', 'icona' => 'bi-piggy-bank-fill'],
            ['nome' => 'Fondo SANEDIL', 'icona' => 'bi-heart-pulse-fill'],
            ['nome' => 'Indennizzo per la Donazione del Midollo Osseo', 'icona' => 'bi-droplet-half'],
            ['nome' => 'Indennizzo per la Donazione del Sangue', 'icona' => 'bi-droplet-fill'],
            ['nome' => 'Assistenza per Alcolismo, HIV, Tossicodipendenza', 'icona' => 'bi-people-fill'],
            ['nome' => 'Supporti e protesi acustiche', 'icona' => 'bi-ear-fill'],
            ['nome' => 'Indennità per Malattia superiore al 180° giorno', 'icona' => 'bi-calendar-plus-fill'],
            ['nome' => 'Assistenza ai Familiari Portatori di Handicap', 'icona' => 'bi-person-wheelchair'],
            ['nome' => 'Assegno Funerario per lavoratore e familiari', 'icona' => 'bi-flower1'],
            ['nome' => 'Invalidità Totale da Malattia dei Familiari', 'icona' => 'bi-person-exclamation'],
            ['nome' => 'Ricovero per Infortunio professionale', 'icona' => 'bi-hospital-fill'],
            ['nome' => 'Invalidità permanente per Infortunio professionale', 'icona' => 'bi-person-slash'],
            ['nome' => 'Decesso per Infortunio professionale', 'icona' => 'bi-person-x-fill'],
            ['nome' => 'Ricovero per Infortunio extraprofessionale', 'icona' => 'bi-first-aid-fill'],
            ['nome' => 'Invalidità permanente per Infortunio extraprofessionale', 'icona' => 'bi-person-slash'],
            ['nome' => 'Decesso per Infortunio extraprofessionale', 'icona' => 'bi-person-x-fill'],
            ['nome' => 'Decesso del lavoratore per malattia', 'icona' => 'bi-person-x-fill'],
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
            ['nome' => 'FERIE E GRATIFICA NATALIZIA', 'icona' => 'bi-cash-stack'],
            ['nome' => 'INTEGRAZIONE ALL’INDENNITÀ DI MALATTIA', 'icona' => 'bi-bandaid-fill'],
            ['nome' => 'INTEGRAZIONE ALL’INDENNITÀ DI INFORTUNIO O MALATTIA PROFESSIONALE', 'icona' => 'bi-hospital'],
            ['nome' => 'NORME COMUNI A MALATTIA E INFORTUNIO', 'icona' => 'bi-file-earmark-medical-fill'],
            ['nome' => 'ANZIANITÀ PROFESSIONALE EDILE (A.P.E.)', 'icona' => 'bi-person-workspace'],
            ['nome' => 'fondo sanedil', 'icona' => 'bi-heart-pulse-fill'],
        ];

        return view('servizi.edilcassa', compact('prestazioniEdilcassa'));
    }
}
