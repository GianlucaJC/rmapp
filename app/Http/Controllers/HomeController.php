<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use App\Models\ServiceRequest; // Import the ServiceRequest model
use App\Models\News;
use GuzzleHttp\Exception\RequestException;
use App\Mail\ServiceRequestWorkerResubmittedMail; // Import the new Mailable
use App\Mail\ServiceRequestAdminMail;
use App\Mail\ServiceRequestUserMail;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HomeController extends Controller
{
    // Helper function to clean HTML for description_completa
    private function cleanHtmlDescription($html) {
        $html = preg_replace('/<a[^>]*>(.*?)<\/a>/is', '$1', $html); // Remove <a> tags but keep content
        $html = preg_replace('/style="[^"]*"/i', '', $html);        // Remove style attributes
        $allowed_tags = '<b><strong><em><i><u><ul><li><p><h4><br>';
        return strip_tags($html, $allowed_tags);
    }

    /**
     * Mostra la homepage pubblica dell'applicazione.
     */
    public function index(): View    
    {
        // Per ora, questa funzione restituisce semplicemente la vista 'home'.
        // Se l'utente è autenticato, recupera le sue richieste di servizio.
        $serviceRequests = collect(); // Inizializza come collezione vuota
        $newsItems = collect(); // Inizializza come collezione vuota

        $isUser = auth()->check();
        $isAdmin = session()->has('admin_logged_in');

        if ($isUser) {
            $user = auth()->user();
            $serviceRequests = ServiceRequest::where('user_id', $user->id)
                                            ->orderBy('updated_at', 'desc')
                                            ->get();

            // Aggiunge un URL di dettaglio o un trigger per modale a ogni richiesta
            foreach ($serviceRequests as $request) {
                // Gestione speciale per l'analisi busta paga, che apre una modale
                if ($request->service_type === 'Consulenza' && $request->service_name === 'Analisi Busta Paga') {
                    $request->busta_paga_trigger = true; // Aggiungo un flag per il frontend
                    continue; // Prossima iterazione, non serve un detail_url per questo tipo
                }

                $routeName = '';
                // Mappa per gestire dinamicamente i tipi di servizio e associarli alle rotte corrette
                $serviceTypeMap = [
                    'Cassa Edile' => 'servizi.cassa-edile',
                    'Edilcassa' => 'servizi.edilcassa',
                    'Cassa Edile Latina' => 'servizi.cassa-edile-latina',
                    'Cassa Edile Rieti' => 'servizi.cassa-edile-rieti',
                    'Cassa Edile Viterbo' => 'servizi.cassa-edile-viterbo',
                    'Cassa Edile Frosinone' => 'servizi.cassa-edile-frosinone',
                ];

                if (isset($serviceTypeMap[$request->service_type])) {
                    $routeName = $serviceTypeMap[$request->service_type];
                }

                if ($routeName && !empty($request->service_name)) {
                    $fragment = Str::slug($request->service_name);
                    $request->detail_url = route($routeName) . '#' . $fragment;
                }
            }
        }

        // Recupera le news attive da mostrare nella dashboard per utenti e admin
        if ($isUser || $isAdmin) {
            $today = Carbon::today();
            $newsItems = News::where('is_suspended', false)
                ->where(function ($query) use ($today) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', $today);
                })
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', $today);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }


        return view('home', compact('serviceRequests', 'newsItems'));
    }

    /**
     * Mostra la pagina di selezione dei servizi Cassa Edile / Edilcassa.
     */
    public function serviziIndex(): View
    {
        return view('servizi.index');
    }

    private function cassaEdileData()
    {
        // In futuro, questi dati verranno dal database.
        // Add 'service_type' to each service for tracking
        return [
            ['nome' => 'Pagamento FERIE E GRATIFICA NATALIZIA', 'icona' => 'bi-cash-stack', 'descrizione' => 'Erogazione del trattamento economico per ferie e gratifica natalizia.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile gestisce l’accantonamento degli importi versati dall’impresa per i propri dipendenti a titolo di <strong>Gratifica Natalizia</strong> e <strong>Ferie (GNF)</strong>, provvedendo alla liquidazione delle quote accantonate con due pagamenti semestrali:</p><ul><li style="list-style-type: none"><ul><li><p><strong>Trattamento economico per ferie</strong>: l’equivalente delle somme accantonate <strong>dal 1° ottobre al 31 marzo</strong> viene corrisposto nel corso del mese di luglio;</p></li><li><p><strong>Gratifica natalizia</strong>: l’equivalente delle somme accantonate <strong>dal 1° aprile al 30 settembre</strong> viene corrisposto nel corso del mese di dicembre.</p></li></ul></li></ul><p>Il pagamento della prestazione avviene in automatico sulla base degli accantonamenti mensili previsti a carico dell’impresa e del lavoratore (con riferimento a quanto calcolato in busta paga) e in riscontro dell’avvenuto versamento delle somme dovute effettuato dall’impresa.'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'ANZIANITÀ PROFESSIONALE EDILE (A.P.E.)', 'icona' => 'bi-person-workspace', 'descrizione' => 'Un premio per l\'Anzianità Professionale Edile.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>Ogni anno la Cassa Edile liquida ai lavoratori iscritti <strong>in automatico</strong> e di regola nel mese di <strong>MAGGIO</strong> un Premio di professionalità disciplinato dal CCNL vigente collegato all’anzianità lavorativa che l’operaio matura nel settore edile.<br><br>Alla copertura degli oneri derivanti dalla disciplina contrattualmente disposta dell’<strong>Anzianità Professionale Edile (A.P.E.)</strong> si provvede con un contributo a carico dei datori di lavoro.<br><br>A seguito del versamento da parte delle imprese del contributo, calcolato sugli elementi della retribuzione per tutte le ore di lavoro effettivamente prestate e sul trattamento economico per le festività, all’operaio che in un biennio abbia maturato l’A.P.E., anche in più circoscrizioni territoriali, le Casse Edili corrispondono, nell’anno successivo – ciascuna per la propria competenza – la prestazione prevista.<br><br>Per aver diritto alla prestazione il lavoratore deve far valere nel biennio precedente all’erogazione almeno 2100 ore denunciate e versate presso la Cassa Edile di Roma o altre Casse. Ogni biennio scade il 30 settembre dell’anno precedente quello dell’erogazione.<br><br>La prestazione è stabilita secondo importi crescenti, in relazione al numero delle erogazioni del Premio A.P.E. percepite negli anni dal singolo operaio e calcolata moltiplicando i coefficienti prestabiliti per il numero di ore di lavoro ordinario effettivamente prestate, denunciate e versate alla Cassa Edile per il secondo anno del biennio (l’anno base per la liquidazione è il secondo del biennio).<br><br>Le erogazioni di importi a titolo di Anzianità Professionale Edile sono <strong>soggette a ritenuta IRPEF</strong> (la Cassa Edile emette pertanto la certificazione CU da considerare in fase di dichiarazione dei redditi).</p><br>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI'],
            ['nome' => 'Regolamento APE – CCNL Allegato C Comma 4', 'icona' => 'bi-file-earmark-ruled', 'descrizione' => 'Cumulo delle ore di lavoro per operai che si trasferiscono.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>Nel caso di morte o di invalidità permanente assoluta al lavoro di operai che abbiano percepito almeno una volta la prestazione o comunque abbiano maturato il requisito per l’ottenimento dell’A.P.E. e per i quali nel biennio precedente l’evento siano stati effettuati gli accantonamenti relativi a Gratifica Natalizia e Ferie, la Cassa Edile, su richiesta dell’operaio o degli aventi causa (eredi), eroga una prestazione <strong>pari a 300 volte la retribuzione oraria minima contrattuale</strong> costituita dal minimo di paga base spettante all’operaio stesso al momento dell’evento.</p><br>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Indennità per Carenza Malattia', 'icona' => 'bi-bandaid', 'descrizione' => 'Indennità per i primi 3 giorni di malattia.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile corrisponde un’indennità giornaliera definita “per carenza”, fissata in <strong>€ 36,00</strong> per i primi tre giorni, a persona, nel singolo anno solare e per un solo evento di malattia di durata non superiore a 6 giorni di calendario (1° gennaio – 31 dicembre), entro un limite massimo di <strong>€ 108,00</strong>.<br><br>Per avere diritto alla prestazione il lavoratore <strong>alla data dell’evento sanitario</strong> deve essere in forza con un’impresa iscritta e regolare alla BNI (avendo effettuato denunce e versamenti dei contributi dovuti) ed aver accantonato, nel corso dei 24 mesi precedenti, un numero minimo di 2100 ore di lavoro ordinario.<br><br>Per l’attivazione della richiesta della prestazione, oltre alla domanda del lavoratore è necessario che l’impresa comunichi l’evento tramite Denuncia MUT, allegando contestualmente la certificazione medica attestante la durata della malattia.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI'],
            ['nome' => 'Borse di Studio', 'icona' => 'bi-mortarboard-fill', 'descrizione' => 'Assegnazione di borse di studio per i figli dei lavoratori.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile organizza annualmente per gli studenti figli dei lavoratori iscritti fiscalmente a carico e/o conviventi e i lavoratori studenti, che frequentino con profitto le scuole degli ordini e gradi indicati e che non risultino ripetenti o promossi con debito formativo né fuori corso e non aggiudicatari di altri premi analoghi nello stesso anno scolastico/accademico di afferenza, un concorso per l’assegnazione di una serie precisata di <strong>Borse di Studio</strong>.<br><br>Le modalità, il numero dei premi, i requisiti di partecipazione, i criteri di conferimento e la documentazione da presentare saranno resi noti nel Bando di Partecipazione, che sarà pubblicato sul sito della Cassa Edile orientativamente nel mese di <strong>GENNAIO</strong>.</p><br>'), 'service_type' => 'Cassa Edile', 'documentazione_richiesta' => [], 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'], // Nessun documento richiesto per l'invio iniziale
            ['nome' => 'Assistenza allo Studio', 'icona' => 'bi-book-half', 'descrizione' => 'Contributo per l\'acquisto di libri di testo.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile riconosce ai lavoratori iscritti che abbiano figli frequentanti <strong>Scuole Secondarie di secondo grado</strong>, non fruitori di Borsa di Studio erogata dalla Cassa Edile di Roma per il precedente anno scolastico, un contributo economico “<strong>pro-capite</strong>” allo studio di <strong>€ 155,00</strong> (l’importo è soggetto a ritenuta IRPEF).<br><br>L’attivazione della richiesta della prestazione da parte del lavoratore può avvenire <strong>dal 1° Febbraio e fino al 30 Giugno.<br><br>Per avere diritto alla prestazione il lavoratore nel corso dei 12 mesi precedenti la data del <strong>1° febbraio</strong> di ogni anno scolastico deve aver accantonato un numero minimo di 950 ore di lavoro ordinario.<br><br></p></li></ul></li></ul><br>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Premio Giovani', 'icona' => 'bi-award-fill', 'descrizione' => 'Premio per i giovani che entrano per la prima volta nel settore.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile di Roma riconosce&nbsp; ai giovani di età anagrafica compresa tra i <strong>15° e i 25° anni di età, dopo aver maturato per la prima volta </strong>la prestazione per Anzianità Professionale Edile presso la Cassa Edile di Roma, ma non aver mai percepito il premio per Anzianità Professionale Edile da altre province,&nbsp; <strong>per una sola volta</strong> <strong>un premio di €500,00</strong> (l’importo è soggetto a ritenuta IRPEF)</p><p>&nbsp;</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Fondo Prepensionamento', 'icona' => 'bi-hourglass-bottom', 'descrizione' => 'Incentivo all\'esodo per i lavoratori prossimi alla pensione.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>In via sperimentale, <b>dal 01/10/2023 al 31/12/2026</b> l’operaio iscritto alla Cassa Edile di Roma che abbia cessato il rapporto di lavoro con l’azienda, provata la sussistenza dei requisiti richiesti, potrà chiedere, <b>a titolo di accompagnamento al diritto alla pensione,</b> una delle prestazioni più avanti indicate.</p><p><b>Requisiti:</b><br><u>Requisito Lavoratore</u>: Essere un lavoratore dipendente titolare di un rapporto di lavoro subordinato in stato di disoccupazione per licenziamento o risoluzione consensuale che diano accesso alla <b>Nuova Assicurazione Sociale per l’Impiego (NASpI)</b>.<br><u>Requisito Cassa Edile</u>: Aver maturato almeno <b>2.100 ore</b> coperte da contribuzione valide ai fini APE nei 24 mesi precedenti la data di cessazione del rapporto di lavoro (sono valide anche le eventuali ore dichiarate in altre Casse Edili).</p><h2><strong>Procedimento</strong></h2><p><strong>Prima fase: fino a tutto il periodo di NASpI</strong><br>• Compilazione <b>DOMANDA</b> (Mod.1)</p><p><b>Documentazione da esibire:</b><br><em>Il lavoratore deve farsi assistere da un Patronato.</em><br>• Ecocert o specifica certificazione INPS per pensione anticipata;<br>• ipotesi data presunta di pensionamento;<br>• stima ipotetica della NASpI spettante;<br>• documento di liquidazione prestazione NASpI da parte dell’INPS.</p><p><b>Prestazione prevista</b> (l’opzione deve essere indicata espressamente dal lavoratore sul Mod.1):</p><p><b>Per il periodo di NASpI</b>, il lavoratore potrà fruire dell’<b>integrazione al 100%</b> dal mese di inizio della decurtazione – décalage in poi (bimestralmente), fino all’importo massimo mensile riconosciuto, previa presentazione di idonea documentazione attestante l’avvenuta liquidazione della prestazione da parte dell’INPS (copia bonifico) nel bimestre di riferimento.</p><p><strong>Seconda fase: terminato il periodo di NASpI</strong><br>• Compilazione AUTOCERTIFICAZIONE (Mod.4)</p><p>* L’apposita autocertificazione sul completo utilizzo di tutto il periodo di NASpI deve essere presentata prima dell’effettiva erogazione delle altre prestazioni.</p><p><b>Documentazione da esibire:</b><br><em>Il lavoratore deve farsi assistere da un Patronato.</em><br>• Modello C2, rilasciato dal Centro per l’Impiego (elenco storico dei rapporti di lavoro effettuati);<br>• <b>Trimestralmente, per documentare lo stato di disoccupazione</b>: Modello C2, rilasciato dal Centro per l’Impiego (elenco storico dei rapporti di lavoro effettuati) + autocertificazione sullo stato di disoccupazione (Mod.4);<br>• <u><b>Solo per la CONTRIBUZIONE VOLONTARIA</b></u>: <b>Autorizzazione dell’INPS</b>, in accoglimento della relativa domanda, alla prosecuzione della contribuzione volontaria; copia <b>bollettini INPS da pagare</b> (per conoscere l’importo da erogare); trimestralmente, copia del precedente <b>bollettino INPS pagato</b>.</p><p><strong>Prestazioni previste</strong> (sono quelle scelte e indicate espressamente dal lavoratore sul Mod.1):</p><p><b>Concluso il periodo di NASpI</b>, la Cassa Edile, in base alla scelta del lavoratore, provvederà a:</p><ul><li>nel caso di <b>INTEGRAZIONE AL REDDITO</b>, erogare il massimale medio netto mensile della Cassa Integrazione Guadagni ordinaria, in vigore alla data di presentazione della domanda (con riferimento agli importi dei trattamenti di integrazione salariale riportati nella relativa tabella INPS).</li></ul><p><b>Requisito anagrafico</b>: Per calcolare il raggiungimento dell’età minima per ottenere la pensione, vanno sommati gli anni dell’<b>età anagrafica</b> del lavoratore alla <b>fine del periodo della NASpI</b> più i <b>mesi di integrazione al reddito</b> (max 36).</p><ul><li>nel caso di <b>CONTRIBUZIONE VOLONTARIA</b>, pagare l’importo desunto dai bollettini trimestrali forniti dall’INPS al lavoratore (pagamento trimestrale anticipato).</li></ul><p><b>N.B.:</b> Tutte le prestazioni (Integrazione NASpI, Integrazione al reddito, Contribuzione volontaria) verranno assoggettate dalla Cassa Edile <b>a ritenuta fiscale</b>, calcolata sulle aliquote IRPEF a scaglioni. La Cassa Edile tratterrà la ritenuta e la verserà all’Agenzia delle Entrate. L’anno successivo emetterà la relativa CU, che il lavoratore dovrà esibire in sede di Dichiarazione dei redditi.</p>'), 'service_type' => 'Cassa Edile', 'documentazione_richiesta' => [], 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'], // Nessun documento richiesto per l'invio iniziale
            ['nome' => 'Pacchetti Vacanze e Soggiorni', 'icona' => 'bi-suitcase-lg-fill', 'descrizione' => 'Offerte di pacchetti vacanze a condizioni agevolate.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile, nell’ambito delle attività turistico-ricreative, organizza annualmente per i lavoratori iscritti e i loro familiari (coniuge/convivente e figli) fiscalmente a carico un concorso per la selezione ai <strong>Pacchetti Vacanze e Soggiorni</strong> previsti in luoghi di <strong>mare</strong> o <strong>montagna</strong> esclusivamente prescelti.</p><br><p>Le modalità, i requisiti di partecipazione, i criteri di conferimento e la documentazione da presentare saranno resi noti nel Bando di Partecipazione, che sarà pubblicato sul sito della Cassa Edile orientativamente nel mese di <strong>MAGGIO</strong>.</p><br>'), 'service_type' => 'Cassa Edile', 'documentazione_richiesta' => [], 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'], // Nessun documento richiesto per l'invio iniziale
            ['nome' => 'Fondi Pensione', 'icona' => 'bi-piggy-bank-fill', 'descrizione' => 'Supporto per l\'adesione ai fondi di pensione complementare.', 'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile gestisce anche le pratiche di previdenza complementare del settore edile svolgendo una funzione di collegamento tra i lavoratori iscritti ed il <strong>Fondo Prevedi</strong> in rapporto a tre tipologie di servizi:</p><ul><li style="list-style-type: none"><ul><li>consegna e ritiro di documenti e modulistica;</li><li><p>registrazione e modifica dei dati personali dei lavoratori relativamente alla gestione del Fondo;</p></li><li><p>raccolta e trasferimento al Fondo di documenti e modulistica (es.: adesione, anticipazione, riscatto, ecc.).</p></li></ul></li></ul><p>La guida informativa e operativa del Fondo Prevedi è consultabile via web (https://www.prevedi.it/guida-al-fondo-pensione.php).</p><p>&nbsp;</p><p>Allo stesso tempo, provvede alla trasmissione al Fondapi (Fondo Nazionale Pensione complementare per i lavoratori delle piccole e medie imprese), la cui guida informativa e operativa è consultabile via web all’indirizzo https://www.fondapi.it, dei flussi contributivi corrispondenti alle preferenze di adesione esplicita o tacita dei lavoratori versati dalle imprese.</p>'), 'service_type' => 'Cassa Edile', 'documentazione_richiesta' => [], 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'], // Nessun documento richiesto per l'invio iniziale

            ['nome' => 'Indennizzo per la Donazione del Midollo Osseo', 'icona' => 'bi-droplet-half', 'descrizione' => 'Contributo economico per donazione di midollo osseo.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore dell’operaio iscritto una prestazione per la donazione del midollo osseo distinta in due ipotesi di importo forfettario:</p><ul><li style="list-style-type: none"><ul><li><strong>€ 52, 00</strong> per prelievo di sangue ai fini della Tipizzazione HLA;</li><li><p><strong>€ 129,00</strong> se, successivamente, viene richiesta la donazione di midollo osseo.</p></li></ul></li></ul><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>. <p>Per avere diritto alla prestazione il lavoratore <strong>alla data dell’evento</strong> (con riferimento al prelievo e/o alla donazione) deve essere in forza con un’impresa iscritta e regolare presso la BNI, ovvero abbia effettuato denunce e versamenti dovuti, ed aver accantonato, nel corso dei 12 mesi precedenti, un numero minimo di 950 ore di lavoro ordinario.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Indennizzo per la Donazione del Sangue', 'icona' => 'bi-droplet-fill', 'descrizione' => 'Contributo economico per ogni donazione di sangue.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore dell’operaio iscritto e dei familiari (coniuge/convivente e/o figli) fiscalmente a carico una prestazione per la donazione del sangue fissata in un importo forfettario di <strong>€ 26,00</strong>.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Per avere diritto alla prestazione il lavoratore <strong>alla data dell’evento</strong> (con riferimento al prelievo) deve essere in forza con un’impresa iscritta e regolare presso la BNI, ovvero abbia effettuato denunce e versamenti dovuti, ed aver accantonato, nel corso dei 12 mesi precedenti, un numero minimo di 950 ore di lavoro ordinario.</p><p>La <strong>documentazione necessaria</strong> è la seguente:</p><ul><li style="list-style-type: none"><ul><li><p>Certificato del Centro trasfusionale dove è stato effettuato il prelievo di sangue con i relativi dati anagrafici ed il domicilio;</p></li><li><p>Sottoscrizione del consenso al trattamento dei dati personali anche dei familiari.</p></li></ul></li></ul>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Assistenza per Alcolismo, HIV, Tossicodipendenza', 'icona' => 'bi-people-fill', 'descrizione' => 'Sostegno per lavoratori affetti da gravi patologie o dipendenze.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore del lavoratore iscritto e dei familiari (coniuge/convivente e figli) fiscalmente a carico nei casi di Alcolismo – HIV – Tossicodipendenza un rimborso spese per assistenza e cure presso Centri specializzati fino ad un importo massimo di <strong>€ 1.800,00</strong>.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>. </p><p>Per avere diritto alla prestazione il lavoratore <strong>alla data della presentazione della domanda</strong> deve essere in forza con un’impresa iscritta e regolare alla BNI, ovvero abbia effettuato denunce e versamenti dovuti, ed aver accantonato, nel corso dei 12 mesi precedenti, un numero minimo di 950 ore di lavoro ordinario.</p><p>La <strong>documentazione necessaria</strong> è la seguente:</p><ul><li style="list-style-type: none"><ul><li><p>Certificato medico rilasciato dall’Azienda Sanitaria attestante la situazione clinica;</p></li><li><p>Certificato rilasciato dal Centro di recupero interessato attestante la terapia in atto;</p></li><li><p>Dichiarazione dell’impresa attestante l’esistenza del rapporto di lavoro alla data dell’evento (riferito alla data di emissione del certificato dell’Azienda Sanitaria);</p></li><li><p>Ricevute o fatture per le spese sostenute presso il Centro di recupero;</p></li><li>Attestazione della Situazione di Famiglia;</li><li><p>Sottoscrizione del consenso al trattamento dei dati personali anche dei familiari.</p></li></ul></li></ul>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Supporti e protesi acustiche', 'icona' => 'bi-ear-fill', 'descrizione' => 'Contributo per l\'acquisto di apparecchi acustici.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore dei lavoratori iscritti e dei familiari (coniuge/convivente e figli) fiscalmente a carico un rimborso pari al <strong>50%</strong> delle spese sostenute nel biennio per un importo massimo erogabile di <strong>€ 1.800,00</strong> per nucleo familiare.</p><p>La data di inizio del biennio sarà determinata dalla data della <strong>prima autorizzazione</strong> da parte della Cassa Edile.</p><p>La richiesta della prestazione deve essere attivata<strong> entro il 31 dicembre 2025</strong>.</p><p>Per avere diritto alla prestazione il lavoratore <strong>alla data della presentazione della domanda</strong> deve essere in forza con un’impresa iscritta e regolare alla BNI, ovvero abbia effettuato denunce e versamenti dovuti, ed aver accantonato, nel corso dei 12 mesi precedenti, un numero minimo di 950 ore di lavoro ordinario.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Indennità per Malattia superiore al 180° giorno', 'icona' => 'bi-calendar-plus-fill', 'descrizione' => 'Indennità per malattia oltre i 180 giorni.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede un’indennità giornaliera di <strong>€ 21,00</strong> da corrispondere al lavoratore iscritto per gli eventi di malattia superiore al 180° giorno nel corso dello stesso anno solare (1° gennaio – 31 dicembre) in coincidenza con le giornate lavorative, fino al 365° giorno di calendario.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Per avere diritto alla prestazione il lavoratore <strong>alla data dell’evento sanitario</strong> deve essere in forza con un’impresa iscritta e regolare alla BNI, ovvero abbia effettuato denunce e versamenti dovuti.</p><p>Per l’attivazione della richiesta della prestazione, oltre alla domanda del lavoratore è necessario che l’impresa comunichi l’evento tramite <strong>Denuncia MUT</strong>, allegando contestualmente la certificazione medica attestante la durata della malattia.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Assistenza ai Familiari Portatori di Handicap', 'icona' => 'bi-person-wheelchair', 'descrizione' => 'Contributo per assistenza a familiari con handicap grave.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per l’anno di afferenza <strong>2025</strong>, prevede a favore dei lavoratori iscritti che abbiano un familiare (coniuge/convivente o figli) fiscalmente a carico riconosciuto portatore di handicap una prestazione economica annua distinta in due importi differenti in base al grado di inabilità:</p><ul><li style="list-style-type: none"><ul><li><p><strong>€ 1.300,00</strong> con riferimento alla <strong>Categoria B</strong>: sono incluse le situazioni di handicap che prevedono solo lo svolgimento delle funzioni vitali di base;</p></li><li><p><strong>€ 1.800,00</strong> con riferimento alla <strong>Categoria A</strong>: sono comprese le situazioni di handicap che rendono impossibile l’autonomo svolgimento delle funzioni vitali di base.</p></li></ul></li></ul><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Per avere diritto alla prestazione il lavoratore <strong>alla data della presentazione della domanda</strong> deve essere in forza con un’impresa iscritta e regolare alla BNI, ovvero abbia effettuato denunce e versamenti dovuti, ed aver accantonato, nel corso dei 12 mesi precedenti, un numero minimo di 950 ore di lavoro ordinario.</p><p>La <strong>documentazione necessaria</strong> è la seguente:</p><ul><li style="list-style-type: none"><ul><li><p>Certificato medico rilasciato dall’Azienda Sanitaria attestante la diagnosi;</p></li><li>Verbale d’invalidità civile (meglio noto come “certificato d’invalidità”) o di handicap (indicato come “L.104/92”) specificante la diagnosi;</li><li><p>Certificato del Centro/Istituto di Riabilitazione interessato attestante la terapia in atto;</p></li><li><p>Dichiarazione dell’impresa attestante l’esistenza del rapporto di lavoro alla data della presentazione della domanda;</p></li><li>Attestazione della Situazione di Famiglia;</li><li><p>Sottoscrizione del consenso al trattamento dei dati personali anche dei familiari.</p></li></ul></li></ul><p><strong>N.B.</strong>: L’accoglimento della richiesta di prestazione è subordinato all’accertamento del medico fiduciario della Cassa Edile.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Assegno Funerario per lavoratore e familiari', 'icona' => 'bi-flower1', 'descrizione' => 'Contributo per spese funerarie.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede in caso di decesso dei lavoratori iscritti, solo nei casi determinati da infortunio extraprofessionale o malattia extra professionale, e in qualunque modo dei familiari (coniuge/convivente o figli) fiscalmente a carico una prestazione economica fissata in un importo pari a <strong>€ 1.549,00</strong>, da erogarsi una sola volta.</p><p>Nel caso di decesso di un familiare fiscalmente a carico l’importo è <strong>soggetto a ritenuta IRPEF</strong>.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p><span>Per avere diritto alla prestazione</span>:</p><ul><li><ul><li><p><strong>In caso di decesso del lavoratore</strong>: il lavoratore deve essere in forza con un’impresa iscritta e regolare alla BNI, ovvero abbia effettuato denunce e versamenti dovuti, alla data dell’evento (data del decesso);</p></li><li><p><strong>In caso di decesso di un familiare fiscalmente a carico</strong>: il lavoratore deve essere in forza con un’impresa iscritta e regolare alla BNI, ovvero abbia effettuato denunce e versamenti dovuti, alla data dell’evento (data del decesso) ed aver accantonato, nel corso dei 12 mesi precedenti, un numero minimo di 950 ore di lavoro ordinario.</p></li></ul></li></ul>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Invalidità Totale da Malattia dei Familiari', 'icona' => 'bi-person-exclamation', 'descrizione' => 'Sussidio per invalidità totale di un familiare.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore del coniuge/convivente e dei figli fiscalmente a carico dei lavoratori iscritti una prestazione economica sui punti percentuali di invalidità per eventi morbosi provati da calcolarsi in rapporto al punteggio INAIL e con termini di calcolo differenti in base alle due tipologie di beneficiari:</p><ul><li style="list-style-type: none"><ul><li><p>Per il coniuge/convivente: <strong>€ 51,65</strong> X grado di invalidità non inferiore al 45%, fino ad un importo massimo di <strong>€ 5.165,00</strong>;</p></li><li><p>Per i figli: <strong>€ 36,15</strong> X grado di invalidità non inferiore al 45%, fino ad un importo massimo di <strong>€ 3.615,00</strong>.</p></li></ul></li></ul><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Per avere diritto alla prestazione il lavoratore <strong>alla data dell’evento sanitario</strong> deve essere in forza con un’impresa iscritta e regolare alla BNI, ovvero abbia effettuato denunce e versamenti dovuti, ed aver accantonato, nel corso dei 12 mesi precedenti, un numero minimo di 950 ore di lavoro ordinario.</p><p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Ricovero per Infortunio professionale', 'icona' => 'bi-hospital-fill', 'descrizione' => 'Indennità per ricovero da infortunio sul lavoro.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore dei lavoratori iscritti nel caso di ricovero a seguito di infortunio professionale l’erogazione di un’indennità giornaliera pari a <strong>€ 52,00</strong>, per un massimo di 90 giorni (escluso il giorno di dimissione) nell’arco dell’anno solare (1° gennaio – 31 dicembre).</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Il diritto alla prestazione è acquisito nel momento della presentazione della domanda.</p><p>Se in quel momento l’impresa non è in regola con il versamento di percentuali e contributi e il lavoratore risulti essere iscritto in base alla documentazione presente presso gli uffici della Cassa, avrà diritto a richiedere e ottenere per una sola volta la prestazione richiesta.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Invalidità permanente per Infortunio professionale', 'icona' => 'bi-person-slash', 'descrizione' => 'Indennizzo per invalidità permanente da infortunio sul lavoro.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore dei lavoratori iscritti nel caso di infortunio professionale l’erogazione di una prestazione economica sui punti percentuali di invalidità fino ad un importo massimo di <strong>€ 35.800,00</strong>, da calcolarsi con riferimento al punteggio INAIL nel modo seguente: <strong>€ 360,00 </strong>per grado di invalidità, con l’applicazione di una franchigia assoluta prevista del 3% nei casi in cui il grado di invalidità sia inferiore al 12%.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Il diritto alla prestazione è acquisito nel momento della presentazione della domanda.</p><p>Se in quel momento l’impresa non è in regola con il versamento di percentuali e contributi e il lavoratore risulti essere iscritto in base alla documentazione presente presso gli uffici della Cassa, avrà diritto a richiedere e ottenere per una sola volta la prestazione richiesta.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Decesso per Infortunio professionale', 'icona' => 'bi-person-x-fill', 'descrizione' => 'Indennizzo ai familiari per decesso da infortunio sul lavoro.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore degli aventi causa (eredi) del lavoratore iscritto deceduto a seguito di infortunio professionale l’erogazione di una prestazione economica fissata in un importo forfettario pari a <strong>€ 39.800,00</strong>.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Il diritto alla prestazione è acquisito nel momento della presentazione della domanda.</p><p>Se in quel momento l’impresa non è in regola con il versamento di percentuali e contributi e il lavoratore risulti essere iscritto in base alla documentazione presente presso gli uffici della Cassa, avrà diritto a ottenere la prestazione richiesta.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Ricovero per Infortunio extraprofessionale', 'icona' => 'bi-first-aid-fill', 'descrizione' => 'Indennità per ricovero da infortunio extraprofessionale.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore dei lavoratori iscritti nel caso di ricovero a seguito di infortunio extraprofessionale l’erogazione di un’indennità giornaliera pari a <strong>€ 44,00</strong>, per un massimo di 90 giorni (escluso il giorno di dimissione), anche non consecutivi.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Il diritto alla prestazione è acquisito nel momento della presentazione della domanda.</p><p>Se in quel momento l’impresa non è in regola con il versamento di percentuali e contributi e il lavoratore risulti essere iscritto in base alla documentazione presente presso gli uffici della Cassa, avrà diritto a richiedere e ottenere per una sola volta la prestazione richiesta.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Invalidità permanente per Infortunio extraprofessionale', 'icona' => 'bi-person-slash', 'descrizione' => 'Indennizzo per invalidità permanente da infortunio extraprofessionale.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore dei lavoratori iscritti nel caso di infortunio extraprofessionale l’erogazione di una prestazione economica sui punti percentuali di invalidità fino ad un importo massimo di <strong>€ 23.900,00</strong>, da calcolarsi con riferimento al punteggio INAIL nel modo seguente: <strong>€ 240,00</strong> per grado di invalidità, con l’applicazione di una franchigia assoluta prevista del 3% nei casi in cui il grado di invalidità sia inferiore al 21%.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.<p>Il diritto alla prestazione è acquisito nel momento della presentazione della domanda.</p><p>Se in quel momento l’impresa non è in regola con il versamento di percentuali e contributi e il lavoratore risulti essere iscritto in base alla documentazione presente presso gli uffici della Cassa, avrà diritto a richiedere e ottenere per una sola volta la prestazione richiesta.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Decesso per Infortunio extraprofessionale', 'icona' => 'bi-person-x-fill', 'descrizione' => 'Indennizzo ai familiari per decesso da infortunio extraprofessionale.', 'descrizione_completa' => $this->cleanHtmlDescription('<p><h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore degli aventi causa (eredi) del lavoratore iscritto deceduto a seguito di infortunio extraprofessionale una prestazione economica fissata in un importo forfettario pari a <strong>€ 11.900,00</strong>.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Il diritto alla prestazione è acquisito nel momento della presentazione della domanda.</p><p>Se in quel momento l’impresa non è in regola con il versamento di percentuali e contributi e il lavoratore risulti essere iscritto in base alla documentazione presente presso gli uffici della Cassa, avrà diritto a ottenere la prestazione richiesta.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
            ['nome' => 'Decesso del lavoratore per malattia', 'icona' => 'bi-person-x-fill', 'descrizione' => 'Indennizzo ai familiari per decesso per malattia.', 'descrizione_completa' => $this->cleanHtmlDescription('<h4><span style="color: #f72525"><strong>* Trattasi di prestazione accordata di anno in anno in coincidenza delle disponibilità di bilancio.</strong></span></h4><p>La Cassa Edile, anche per gli eventi sanitari dal 1° ottobre 2024 al 30 settembre 2025, prevede a favore degli aventi causa (eredi) del lavoratore iscritto deceduto per malattia una prestazione economica fissata in un importo forfettario pari a <strong>€ 5.165,00</strong>.</p><p>La richiesta della prestazione deve essere attivata <strong>entro il 31 dicembre 2025</strong>.</p><p>Per avere diritto alla prestazione il lavoratore <strong>alla data dell’evento</strong> (data del decesso) deve essere in forza con un’impresa iscritta e regolare alla BNI, ovvero che abbia effettuato denunce e versamenti dovuti.</p>'), 'service_type' => 'Cassa Edile', 'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica'],
        ];
    }

    /**
     * Mostra l'elenco delle prestazioni della Cassa Edile.
     */
    public function cassaEdile(): View
    {
        $prestazioniCassaEdile = $this->cassaEdileData();

        // Aggiunge uno 'slug' per l'ID HTML a ogni prestazione per permettere l'ancoraggio
        foreach ($prestazioniCassaEdile as &$prestazione) {
            $prestazione['slug'] = Str::slug($prestazione['nome']);
        }
        unset($prestazione); // Rompe il riferimento dell'ultima iterazione

        // Check for existing service requests for the authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            // Get the latest request for each service for this user and service type
            $latestRequests = ServiceRequest::where('user_id', $user->id)
                                            ->where('service_type', 'Cassa Edile')
                                            ->orderBy('updated_at', 'desc')
                                            ->get()
                                            ->unique('service_name') // Gets the first occurrence which is the latest
                                            ->keyBy('service_name');

            foreach ($prestazioniCassaEdile as &$prestazione) {
                if (isset($latestRequests[$prestazione['nome']])) {
                    $request = $latestRequests[$prestazione['nome']];
                    $prestazione['current_status'] = $request->status;
                    $prestazione['request_date'] = $request->updated_at->format('d/m/Y');
                    $prestazione['active_request'] = $request; // Pass the full request object
                }
            }
            unset($prestazione); // Break the reference
        }

        return view('servizi.cassa-edile', compact('prestazioniCassaEdile'));
    }

    private function edilcassaData()
    {
        // In futuro, questi dati verranno dal database.
        return [
            [
                'nome' => 'FERIE E GRATIFICA NATALIZIA', 
                'icona' => 'bi-cash-stack', 
                'descrizione' => 'Accantonamento per ferie e gratifica natalizia.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa contribuisce a pagare parte di quanto spetta ai lavoratori ad essa iscritti per le loro ferie e gratifica natalizia.</p><p>Nella fattispecie paga :</p><ul><li>l’ 8,50% di quanto spetta loro per le ferie</li><li>il 10% di quanto spetta loro per la gratifica natalizia</li></ul><p>Per un totale, quindi, complessivo del 18,50%, calcolato sugli elementi della retribuzione per tutte le ore di lavoro normale contrattuale effettivamente prestate, e sul trattamento economico per le festività.</p><p>Gli importi di tale percentuale, vanno accantonati da parte delle imprese, presso l’Edilcassa, secondo quanto stabilito localmente dalle organizzazioni territoriali aderenti alle associazioni nazionali contraenti.</p>'),
                'service_type' => 'Edilcassa', 
                'is_actionable' => true, 
                'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'Questa è una prestazione automatica gestita tramite gli accantonamenti versati dall\'impresa. Non è richiesta un\'azione da parte dell\'utente.', 'inputs' => []]]
            ],
            [
                'nome' => 'INTEGRAZIONE ALL’INDENNITÀ DI MALATTIA', 
                'icona' => 'bi-bandaid-fill', 
                'descrizione' => 'Integrazione del trattamento economico durante i periodi di malattia.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa contribuisce a pagare parte di quanto spetta ai lavoratori ad essa iscritti, per i periodi in cui essi si assentano dal luogo di lavoro per motivi di salute.</p><p>Durante tali periodi infatti, le imprese (entro i limiti della conservazione del posto di lavoro), sono tenute a pagare mensilmente, agli operai non in prova, una somma giornaliera.</p><p>Tale somma giornaliera, corrisponde all’importo che risulta moltiplicando le quote orarie della retribuzione per il numero di ore che risulta dividendo per 6 le ore dell’orario contrattuale settimanale, durante l’assenza per malattia.</p><p>Tale trattamento economico giornaliero, viene pagato dalle imprese agli operai per 6 giorni alla settimana, escluse le festività.</p>'),
                'service_type' => 'Edilcassa', 
                'is_actionable' => true, 
                'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'Per questo servizio, la richiesta viene gestita senza l\'invio di documentazione iniziale. Sarai contattato per eventuali integrazioni.', 'inputs' => []]]
            ],
            [
                'nome' => 'INTEGRAZIONE ALL’INDENNITÀ DI INFORTUNIO O MALATTIA PROFESSIONALE', 
                'icona' => 'bi-hospital', 
                'descrizione' => 'Integrazione del trattamento economico per infortuni o malattie professionali.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’ Edilcassa contribuisce a pagare parte di quanto spetta ai lavoratori ad essa iscritti, per i periodi in cui essi si assentano dal luogo di lavoro a causa di infortuni o malattie professionali.</p><p>Durante tali periodi infatti, le imprese (entro i limiti della conservazione del posto di lavoro) sono tenute a pagare mensilmente, agli operai non in prova, una somma giornaliera.</p>'),
                'service_type' => 'Edilcassa', 
                'is_actionable' => true, 
                'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'Per questo servizio, la richiesta viene gestita senza l\'invio di documentazione iniziale. Sarai contattato per eventuali integrazioni.', 'inputs' => []]]
            ],
            [
                'nome' => 'ANZIANITÀ PROFESSIONALE EDILE (A.P.E.)', 
                'icona' => 'bi-person-workspace', 
                'descrizione' => 'Contributo per i lavoratori che hanno maturato l\'anzianità professionale edile.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa paga un contributo a quei lavoratori ad essa iscritti, che abbiano maturato (anche in più province) l’anzianità professionale edile; ossia a quei lavoratori che, entro il 30 settembre del biennio precedente, abbiano accantonato 2.100 ore di lavoro.</p><p>Al fine di tale requisito vengono contate le ore di lavoro ordinario, di assenza per malattia/infortunio indennizzate, permessi sindacali e ore di formazione.</p><p>L’Edilcassa paga tale contributo in occasione del 1° maggio dell’anno successivo a ciascun biennio.</p>'),
                'service_type' => 'Edilcassa', 
                'is_actionable' => true, 
                'testo_bottone' => 'Procedi per VERIFICARE I REQUISITI',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'Questa è una prestazione automatica basata sulle ore accantonate. Non è richiesta un\'azione da parte dell\'utente.', 'inputs' => []]]
            ],
            [
                'nome' => 'SUSSIDIO MATRIMONIALE', 'icona' => 'bi-gem', 'descrizione' => 'Contributo una tantum di € 1.000 in occasione del matrimonio.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Ai lavoratori che si trovino nelle condizioni generali del presente regolamento, la Edilcassa del Lazio corrisponde un contributo in occasione del matrimonio pari ad <strong>€ 1.000, una tantum</strong>. La domanda per ottenere la prestazione redatta su apposito modello predisposto dall’Edilcassa del Lazio, deve essere presentata entro 180 giorni alla data in cui è stato contratto il matrimonio.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'certificato_matrimonio', 'label' => 'Certificato di matrimonio (se in lingua straniera, traduzione giurata)', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'CONGEDO MATERNITÀ', 'icona' => 'bi-person-heart', 'descrizione' => 'Contributo di € 200 mensili per i 5 mesi di astensione obbligatoria.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga, alle lavoratrici ad essa iscritte, un contributo di <strong>€ 200 mensili</strong> per i cinque mesi di astensione obbligatoria dal lavoro per maternità; sono quindi esclusi eventuali periodi di assenza anticipata dal lavoro.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'certificato_medico_asl', 'label' => 'Certificato medico ASL (gravidanza e data sospensione)', 'type' => 'file', 'required' => true], ['name' => 'dichiarazione_impresa', 'label' => 'Dichiarazione impresa (attestante rapporto di lavoro)', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'CONGEDO PARENTALE PATERNITÀ', 'icona' => 'bi-person-arms-up', 'descrizione' => 'Contributo di € 700 per congedo parentale facoltativo superiore a tre mesi.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga un contributo complessivo pari a <strong>€ 700</strong> riconosciuto ai lavoratori che usufruiscono del congedo parentale facoltativo per un periodo superiore a tre mesi anche continuativo.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'certificato_nascita', 'label' => 'Certificato di nascita del figlio', 'type' => 'file', 'required' => true], ['name' => 'domanda_congedo_cedolini', 'label' => 'Copia domanda congedo parentale e cedolini del periodo', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'ASSEGNO DI NATALITÀ', 'icona' => 'bi-gift-fill', 'descrizione' => 'Contributo annuale per i primi tre anni di vita del figlio.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Ai lavoratori che si trovino nelle condizioni generali del presente regolamento, la Edilcassa del Lazio corrisponde un contributo, in occasione della nascita di figli, così determinato:</p><ul><li>Per il 1° figlio <strong>€ 300,00</strong> annuale per i primi tre anni di vita;</li><li>Per il 2° figlio <strong>€ 400,00</strong> annuale per i primi tre anni di vita;</li><li>Dal 3° figlio <strong>€ 500,00</strong> annuale per i primi tre anni di vita.</li></ul><p>Se il figlio non fosse a totale carico del lavoratore, il contributo potrà essere riparametrato.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta. Per gli anni successivi al primo, la domanda va presentata entro 180gg dal compleanno.', 'inputs' => [['name' => 'certificato_nascita', 'label' => 'Certificato di nascita del figlio (da presentare entro 180 giorni dalla nascita)', 'type' => 'file', 'required' => true], ['name' => 'modello_730_cu', 'label' => 'Copia della prima pagina del modello 730 e/o CU', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'CONTRIBUTO ACQUISTO E RISTRUTTURAZIONE PRIMA CASA', 'icona' => 'bi-house-heart-fill', 'descrizione' => 'Contributo una tantum per finanziamento acquisto o ristrutturazione prima casa.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga un contributo una tantum, ai lavoratori che hanno sottoscritto il primo finanziamento per acquisto, costruzione o ristrutturazione prima casa con le seguenti specifiche:</p><ul><li><strong>€ 1.000</strong> se mutuo/finanziamento da € 17.000 a € 25.000;</li><li><strong>€ 1.500</strong> se mutuo/finanziamento da € 25.001 a € 50.000;</li><li><strong>€ 2.000</strong> se mutuo/finanziamento superiore a € 50.000.</li></ul><p>La casa deve essere situata nel Lazio. Il lavoratore deve avere iscrizione da tre anni continuativi alla Edilcassa del Lazio e diritto all’erogazione APE nel biennio antecedente. La domanda va presentata entro 180 giorni dalla contrazione del mutuo/finanziamento.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'documentazione_mutuo', 'label' => 'Documentazione mutuo/finanziamento', 'type' => 'file', 'required' => true], ['name' => 'pagamento_prima_rata', 'label' => 'Dimostrazione pagamento prima rata', 'type' => 'file', 'required' => true], ['name' => 'proprieta_immobile', 'label' => 'Dimostrazione proprietà immobile (per ristrutturazione)', 'type' => 'file', 'required' => false]]]]
            ],
            [
                'nome' => 'CONTRIBUTO AFFITTO GIOVANI COPPIE', 'icona' => 'bi-key-fill', 'descrizione' => 'Contributo una tantum di € 1.000 per spese di locazione a giovani coppie.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga una tantum un contributo di <strong>€ 1.000</strong> per spese di locazione/affitto a giovani coppie con entrambi i componenti con meno di 40 anni dalla data della richiesta della prestazione.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'certificato_matrimonio_unione', 'label' => 'Certificato di matrimonio o unione civile (< 10 anni)', 'type' => 'file', 'required' => true], ['name' => 'contratto_affitto', 'label' => 'Contratto di affitto registrato', 'type' => 'file', 'required' => true], ['name' => 'certificato_residenza', 'label' => 'Certificato di residenza', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'CARENZA MALATTIA', 'icona' => 'bi-bandaid-fill', 'descrizione' => 'Contributo di € 50 al giorno per i primi 3 giorni di malattia.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio prevede un contributo ai lavoratori iscritti, di <strong>€ 50,00</strong>, per ogni giorno lavorativo per i primi tre giorni di assenza dal lavoro per motivi di salute. Tale prestazione viene fornita per un unico evento nell’anno edile (ott.-sett) relativamente a periodi di malattia non superiori ai sei giorni di calendario.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'certificato_malattia', 'label' => 'Certificato di malattia (da presentare entro 90gg)', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'CONTRIBUTO ANNUALE PORTATORE HANDICAP', 'icona' => 'bi-person-wheelchair', 'descrizione' => 'Contributo annuale fino a € 1.800 per familiari con handicap.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>A ciascun nucleo familiare residente in Italia, in cui vi sia un componente minore, debitamente certificato, l’Edilcassa del Lazio eroga un contributo annuale sino ad un massimo di <strong>€ 1.800</strong>. Viene inoltre riconosciuto un contributo fisso pari a <strong>€ 80 lordi</strong> per ciascuna giornata nella quale il lavoratore si assenta dal lavoro se il familiare (coniuge o figlio a carico), portatore di handicap grave, è sottoposto a visita medica (massimo 5 visite annue).</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'verbale_commissione_medica', 'label' => 'Copia verbale commissione medica attestante disabilità', 'type' => 'file', 'required' => true], ['name' => 'certificato_terapia', 'label' => 'Certificato del centro medico/riabilitazione (ove necessario)', 'type' => 'file', 'required' => false], ['name' => 'certificato_invalidita', 'label' => 'Certificato di commissione di prima istanza', 'type' => 'file', 'required' => true], ['name' => 'modello_730_cu', 'label' => 'Copia prima pagina Mod. 730 o CU', 'type' => 'file', 'required' => true], ['name' => 'dichiarazione_medico_visite', 'label' => 'Solo per visite: Dichiarazione medico accompagnamento familiare', 'type' => 'file', 'required' => false]]]]
            ],
            [
                'nome' => 'PRESTAZIONI SANITARIE', 'icona' => 'bi-heart-pulse-fill', 'descrizione' => 'Contributo straordinario a integrazione dei rimborsi Sanedil.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Le prestazioni sanitarie sono di competenza del Sanedil. In via sperimentale la Edilcassa del Lazio erogherà ai lavoratori e ai loro familiari, un contributo straordinario alle spese mediche rimborsate dal Sanedil, sino alla concorrenza di quanto effettivamente speso e comunque sino all\'importo massimo di <strong>€ 2.000 per nucleo familiare/anno</strong>. Tale integrazione straordinaria verrà erogata nei limiti dello specifico stanziamento annuale.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'domanda_sanedil_rimborso', 'label' => 'Copia domanda presentata al Sanedil e relativo rimborso', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'MALATTIE PROFESSIONALI', 'icona' => 'bi-file-earmark-medical-fill', 'descrizione' => 'Contributo fino a € 150 per spese di certificazione medica.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Al Lavoratore che avanza richieste all’INAIL per il riconoscimento della malattia professionale, l’Edilcassa del Lazio, nel caso del riconoscimento, erogherà un contributo massimo di <strong>€ 150 all’anno</strong> per le spese sostenute per la certificazione medica. Per aver diritto alla prestazione il lavoratore deve aver percepito almeno un’erogazione del premio APE.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'certificato_medico_legale', 'label' => 'Certificato medico rilasciato dal medico legale', 'type' => 'file', 'required' => true], ['name' => 'fattura_pagamento', 'label' => 'Fattura in originale o copia conforme', 'type' => 'file', 'required' => true], ['name' => 'ricevuta_domanda_inail', 'label' => 'Ricevuta inoltro domanda all’INAIL', 'type' => 'file', 'required' => true], ['name' => 'riconoscimento_inail', 'label' => 'Riconoscimento della malattia professionale da parte dell’INAIL', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'ASSEGNO FUNERARIO', 'icona' => 'bi-flower1', 'descrizione' => 'Contributo per decesso del lavoratore o di un familiare a carico.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga, in caso di decesso del lavoratore (ad esclusione della causa di infortunio professionale) un contributo di <strong>€ 1.500,00</strong> agli eredi (coniuge e figli) e di <strong>€ 1.000,00</strong> nel caso di decesso del familiare (coniuge e figli a carico).</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'certificato_morte', 'label' => 'Certificato di morte (da presentare entro 180gg)', 'type' => 'file', 'required' => true], ['name' => 'modello_730_cu', 'label' => 'Copia prima pagina Mod. 730 o CU', 'type' => 'file', 'required' => true], ['name' => 'atto_notorio_eredi', 'label' => 'Atto notorio con indicazione degli eredi', 'type' => 'file', 'required' => true], ['name' => 'delega_erede', 'label' => 'Delega a favore di uno degli eredi (nel caso di più eredi)', 'type' => 'file', 'required' => false], ['name' => 'autorizzazione_giudice', 'label' => 'Autorizzazione del giudice tutelare (nel caso di eredi minorenni)', 'type' => 'file', 'required' => false]]]]
            ],
            [
                'nome' => 'PREMIO GIOVANI', 'icona' => 'bi-award-fill', 'descrizione' => 'Premio una tantum di € 1.000 per lavoratori neo assunti fino a 25 anni.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio ai lavoratori neo assunti di età sino a 25 anni compiuti e che abbiano un’iscrizione almeno di un anno lavorativo consecutivo e un minimo 900 ore, eroga un premio una tantum di <strong>€ 1.000</strong>. La domanda va presentata entro e non oltre 180 giorni dal compimento del primo anno di iscrizione all’Edilcassa.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'Per questo servizio, la richiesta viene gestita senza l\'invio di documentazione iniziale. Sarai contattato per eventuali integrazioni.', 'inputs' => []]]
            ],
            [
                'nome' => 'SOGGIORNI ESTIVI', 'icona' => 'bi-suitcase-lg-fill', 'descrizione' => 'Programmi di attività turistiche, culturali e ricreative.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Annualmente la Edilcassa del Lazio organizza, per i lavoratori ad essa aderenti, programmi di attività turistiche - culturali - ricreative. I lavoratori interessati devono compilare il bando di partecipazione che viene pubblicato sul sito Edilcassa ed inviarlo con le modalità ed entro i termini stabiliti nel bando stesso.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => false, 'testo_bottone' => 'Consulta il bando',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'Per partecipare è necessario compilare il bando di partecipazione che viene pubblicato sul sito Edilcassa.', 'inputs' => []]]
            ],
            [
                'nome' => 'SUSSIDIO FREQUENZA ASILO NIDO', 'icona' => 'bi-teddy-fill', 'descrizione' => 'Rimborso fino a € 700 annui per spese di asilo nido.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga per i figli dei lavoratori di età compresa da 0 a 3 anni e che frequentano un asilo nido pubblico o convenzionato un importo annuo fino a <strong>€ 700</strong>, a titolo di rimborso quota iscrizione e quota ISEE. La richiesta dovrà essere presentata nel corso dell’anno educativo.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'ricevute_spese_nido', 'label' => 'Copia ricevute spese sostenute (iscrizione e rette)', 'type' => 'file', 'required' => true], ['name' => 'modello_730_cu', 'label' => 'Copia prima pagina Mod. 730 o CU', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'SUSSIDIO MENSA SCOLASTICA', 'icona' => 'bi-apple', 'descrizione' => 'Rimborso fino a € 200 annui per servizio mensa scolastica.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga ai lavoratori, per ogni figlio fiscalmente a carico che frequenta la scuola materna, elementare e media, un importo annuo fino a <strong>€ 200</strong> a titolo di rimborso quota servizio mensa. La richiesta deve essere presentata nel corso dell’anno scolastico.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'ricevute_mensa', 'label' => 'Copia ricevute fiscali del servizio mensa', 'type' => 'file', 'required' => true], ['name' => 'modello_730_cu', 'label' => 'Copia prima pagina Mod. 730 o CU', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'SUSSIDIO TRASPORTO SCOLASTICO', 'icona' => 'bi-bus-front-fill', 'descrizione' => 'Sussidio fino a € 200 per trasporto scolastico.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga per ogni figlio fiscalmente a carico, iscritto a un istituto scolastico di scuola media superiore e fruitore di servizi di trasporto pubblico e/o scolastico, un sussidio per un importo fino a <strong>€ 100</strong> in caso di trasporto urbano, <strong>€ 200</strong> in caso di trasporto extraurbano. La richiesta deve essere presentata nel corso dell’anno scolastico.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'documento_trasporto', 'label' => 'Copia documento trasporto e relative ricevute', 'type' => 'file', 'required' => true], ['name' => 'modello_730_cu', 'label' => 'Copia prima pagina Mod. 730', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'BONUS PC / TABLET', 'icona' => 'bi-laptop-fill', 'descrizione' => 'Contributo una tantum di € 200 per acquisto PC/Tablet.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga una tantum a famiglie con figli frequentanti istituti scuola inferiore e superiore un contributo pari a <strong>€ 200</strong> per l’acquisto di tablet/PC. Requisito: ISEE anno precedente alla richiesta inferiore a € 18.000. La richiesta deve essere presentata nel corso dell’anno scolastico.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'fattura_acquisto', 'label' => 'Fattura di acquisto', 'type' => 'file', 'required' => true], ['name' => 'modello_730_cu', 'label' => 'Copia prima pagina Mod. 730', 'type' => 'file', 'required' => true], ['name' => 'attestazione_frequenza', 'label' => 'Attestazione frequenza istituto scolastico', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'CENTRI ESTIVI', 'icona' => 'bi-sun-fill', 'descrizione' => 'Contributo fino a € 250 per figlio per frequenza centri estivi.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’Edilcassa del Lazio eroga annualmente un importo fino a <strong>€ 250</strong> per ogni figlio a carico di età massima 12 anni che frequenta un centro estivo.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => true, 'testo_bottone' => 'PROCEDI PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [['name' => 'ricevuta_centro_estivo', 'label' => 'Copia ricevuta fiscale con nominativo e periodo', 'type' => 'file', 'required' => true], ['name' => 'modello_730_cu', 'label' => 'Copia prima pagina Mod. 730', 'type' => 'file', 'required' => true]]]]
            ],
            [
                'nome' => 'SOSTEGNO STUDIO FIGLI OPERAI DECEDUTI', 'icona' => 'bi-book-half', 'descrizione' => 'Sostegno allo studio per figli di operai deceduti sul lavoro.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Per i figli di lavoratori operai edili deceduti in seguito ad infortunio sul lavoro (dal 1° Gennaio 2021). Il beneficio è corrisposto sotto forma di sostegno allo studio (<strong>€ 1.000 mensili</strong>), a decorrere dall’iscrizione al 1° anno delle scuole secondarie di secondo grado fino alla laurea. Il diritto è riconosciuto a far data dal 1° gennaio 2026.</p><h4>Nota</h4><p>La domanda va presentata al Sanedil utilizzando l’apposita modulistica. Questa è una prestazione informativa.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => false, 'testo_bottone' => 'Informativa',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'La domanda va presentata al Sanedil. Questa è una prestazione informativa e non è possibile procedere da questo portale.', 'inputs' => []]]
            ],
            [
                'nome' => 'PRESTAZIONE STRAORDINARIA GRAVI PATOLOGIE', 'icona' => 'bi-heartbreak-fill', 'descrizione' => 'Prestazione per aspettativa non retribuita in caso di gravi patologie.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Per l\'operaio che ha superato il periodo di comporto per malattia e richiede un\'aspettativa non retribuita (max 6 mesi) per gravi patologie (oncologiche, cardiovascolari, autoimmuni invalidanti) con invalidità >= 50%. La prestazione è pari al massimale Naspi e relativo riscatto contributivo, con decorrenza 1° gennaio 2026.</p><h4>Nota</h4><p>La richiesta va presentata alla Cassa Edile/Edilcassa con apposita modulistica. Questa è una prestazione informativa.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => false, 'testo_bottone' => 'Informativa',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'La richiesta va presentata alla Cassa Edile/Edilcassa con apposita modulistica. Questa è una prestazione informativa e non è possibile procedere da questo portale.', 'inputs' => []]]
            ],
            [
                'nome' => 'CONTRIBUTO STRAORDINARIO SOSTEGNO CASA', 'icona' => 'bi-house-door-fill', 'descrizione' => 'Contributo una tantum di € 500 per canoni di locazione o rate di mutuo.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Per l\'operaio edile regolarmente denunciato. Beneficio una tantum nel biennio 2026/2027 di importo pari a <strong>500 euro</strong> a copertura di canoni di locazione e/o rate di mutuo.</p><h4>Nota</h4><p>La richiesta va presentata alla Cassa Edile/Edilcassa con apposita modulistica. Questa è una prestazione informativa.</p>'),
                'service_type' => 'Edilcassa', 'is_actionable' => false, 'testo_bottone' => 'Informativa',
                'documentazione_richiesta' => [['type' => 'info', 'description' => 'La richiesta va presentata alla Cassa Edile/Edilcassa con apposita modulistica. Questa è una prestazione informativa e non è possibile procedere da questo portale.', 'inputs' => []]]
            ],
        ];
    }

    /**
     * Mostra l'elenco delle prestazioni della Edilcassa.
     */
    public function edilcassa(): View
    {
        $prestazioniEdilcassa = $this->edilcassaData();

        // Aggiunge uno 'slug' per l'ID HTML a ogni prestazione per permettere l'ancoraggio
        foreach ($prestazioniEdilcassa as &$prestazione) {
            $prestazione['slug'] = Str::slug($prestazione['nome']);
        }
        unset($prestazione); // Rompe il riferimento dell'ultima iterazione

        // Check for existing service requests for the authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            // Get the latest request for each service for this user and service type
            $latestRequests = ServiceRequest::where('user_id', $user->id)
                                            ->where('service_type', 'Edilcassa')
                                            ->orderBy('updated_at', 'desc')
                                            ->get()
                                            ->unique('service_name') // Gets the first occurrence which is the latest
                                            ->keyBy('service_name');

            foreach ($prestazioniEdilcassa as &$prestazione) {
                if (isset($latestRequests[$prestazione['nome']])) {
                    $request = $latestRequests[$prestazione['nome']];
                    $prestazione['current_status'] = $request->status;
                    $prestazione['request_date'] = $request->updated_at->format('d/m/Y');
                    $prestazione['active_request'] = $request; // Pass the full request object
                }
            }
            unset($prestazione); // Break the reference
        }

        return view('servizi.edilcassa', compact('prestazioniEdilcassa'));
    }

    private function cassaEdileLatinaData()
    {
        // In futuro, questi dati verranno dal database.
        return [
            [
                'nome' => 'Gratifica ferie e natalizia',
                'icona' => 'bi-cash-stack',
                'descrizione' => 'Trattamento economico per ferie e gratifica natalizia.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Il datore di lavoro deve provvedere all’accantonamento presso Cassa Edile di Latina degli importi relativi al trattamento economico per ferie (8,50%) e gratifica natalizia (10%) dei suoi lavoratori dipendenti. Il trattamento per ferie viene versato annualmente a luglio, la gratifica natalizia a dicembre. Spetta anche durante assenza per malattia, infortunio o malattia professionale.</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'is_actionable' => false, // Automatic
            ],
            [
                'nome' => 'Anzianità Professionale Edile (A.P.E.)',
                'icona' => 'bi-person-workspace',
                'descrizione' => 'Premio per l\'Anzianità Professionale Edile maturata.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’operaio matura il diritto all’Anzianità Professionale Edile (A.P.E.) quando in un biennio (che scade il 30 settembre) accumula almeno 2.100 ore (lavoro, malattia, infortunio, congedi). L\'erogazione avviene da parte della Cassa Edile di Latina in occasione del 1° maggio. L\'importo aumenta con l\'anzianità di iscrizione.</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI',
                'is_actionable' => false, // Automatic
            ],
            [
                'nome' => 'Buoni Libro per Scuole Medie Inferiori',
                'icona' => 'bi-book',
                'descrizione' => 'Assegno di € 150,00 (o € 250,00) per i figli iscritti alla scuola media.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile eroga ai figli dei lavoratori iscritti alla scuola media dell’obbligo (I, II e III media) un assegno di <strong>€ 150,00</strong>. Per i promossi con la media dell’otto o superiore l’importo dell’assegno sarà di <strong>€ 250,00</strong> (al netto delle ritenute).</p><p><strong>Requisiti:</strong> Il lavoratore deve aver maturato 900 ore in 12 mesi o 1800 ore in 24 mesi alla data del 30 settembre precedente.</p><p><strong>Scadenza:</strong> Le domande vanno presentate improrogabilmente entro il 15 Dicembre.</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Borse di Studio per Scuole Medie Superiori',
                'icona' => 'bi-mortarboard-fill',
                'descrizione' => 'Borsa di studio di € 200,00 (o € 350,00) per scuole superiori.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Ai figli dei lavoratori iscritti che frequentano scuole medie superiori statali, regionali o parificate, la Cassa Edile eroga una Borsa di Studio di <strong>€ 200,00</strong>. Per i promossi con la media dell’otto o superiore l’importo dell’assegno sarà di <strong>€ 350,00</strong> (al netto delle ritenute). Sono esclusi i respinti e i rimandati.</p><p><strong>Requisiti:</strong> Il lavoratore deve aver maturato 900 ore in 12 mesi o 1800 ore in 24 mesi alla data del 30 settembre precedente.</p><p><strong>Scadenza:</strong> Le domande vanno presentate improrogabilmente entro il 15 Dicembre.</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Borse di Studio Universitarie',
                'icona' => 'bi-mortarboard-fill',
                'descrizione' => 'Borsa di studio di € 750,00 per studenti universitari.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>A favore dei figli dei lavoratori che entro il compimento del 26° anno di età, risultino iscritti a corsi di laurea in regola con il piano di studi e con i crediti formativi, la Cassa Edile eroga Borse di Studio dell’importo di <strong>€ 750,00</strong>.</p><p><strong>Requisiti:</strong> Il lavoratore deve aver maturato 900 ore in 12 mesi o 1800 ore in 24 mesi alla data del 30 settembre precedente.</p><p><strong>Scadenza:</strong> La domanda va presentata improrogabilmente entro il 28 Febbraio.</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Premio di Inserimento nel Settore Edile',
                'icona' => 'bi-award-fill',
                'descrizione' => 'Premio una tantum di € 400 per i giovani lavoratori.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Ai lavoratori che iniziano, per la prima volta, la loro attività in edilizia e che, entro il compimento del 23° anno d’età possono vantare una permanenza nel settore di almeno 600 ore di accantonamento presso la Cassa Edile di Latina, verrà erogato un premio una tantum di <strong>€ 400</strong>.</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Viaggi studio per i figli dei lavoratori',
                'icona' => 'bi-airplane-engines-fill',
                'descrizione' => 'Viaggi studio all\'estero per perfezionare le lingue.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Per i figli dei lavoratori verranno organizzati nei periodi estivi viaggi studio all’Estero per perfezionare le lingue della comunità Europea. Le modalità verranno decise, annualmente, dalla Commissione Assistenze.</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Informativa',
                'is_actionable' => false,
            ],
            [
                'nome' => 'Assegno Funerario',
                'icona' => 'bi-flower1',
                'descrizione' => 'Assegno di € 1.500 (lavoratore) o € 1.000 (familiare).',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile corrisponde un assegno di <strong>€ 1.500,00</strong> in caso di decesso del lavoratore, o di <strong>€ 1.000,00</strong> in caso di decesso di un familiare (figli entro il compimento del 18° anno o 26° se studente).</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Soggiorni Estivi e Progetto Erasmus',
                'icona' => 'bi-suitcase-lg-fill',
                'descrizione' => 'Soggiorni estivi e progetti Erasmus.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Informazioni non ancora disponibili. Le modalità verranno definite annualmente.</p>'),
                'service_type' => 'Cassa Edile Latina',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'is_actionable' => false,
            ],
        ];
    }

    /**
     * Mostra l'elenco delle prestazioni della Cassa Edile di Latina.
     */
    public function cassaEdileLatina(): View
    {
        $prestazioniCassaEdileLatina = $this->cassaEdileLatinaData();

        // Aggiunge uno 'slug' per l'ID HTML a ogni prestazione per permettere l'ancoraggio
        foreach ($prestazioniCassaEdileLatina as &$prestazione) {
            $prestazione['slug'] = Str::slug($prestazione['nome']);
        }
        unset($prestazione); // Rompe il riferimento dell'ultima iterazione

        // Check for existing service requests for the authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            // Get the latest request for each service for this user and service type
            $latestRequests = ServiceRequest::where('user_id', $user->id)
                                            ->where('service_type', 'Cassa Edile Latina')
                                            ->orderBy('updated_at', 'desc')
                                            ->get()
                                            ->unique('service_name') // Gets the first occurrence which is the latest
                                            ->keyBy('service_name');

            foreach ($prestazioniCassaEdileLatina as &$prestazione) {
                if (isset($latestRequests[$prestazione['nome']])) {
                    $request = $latestRequests[$prestazione['nome']];
                    $prestazione['current_status'] = $request->status;
                    $prestazione['request_date'] = $request->updated_at->format('d/m/Y');
                    $prestazione['active_request'] = $request; // Pass the full request object
                }
            }
            unset($prestazione); // Break the reference
        }

        return view('servizi.cassa-edile-latina', compact('prestazioniCassaEdileLatina'));
    }

    private function cassaEdileRietiData()
    {
        // In futuro, questi dati verranno dal database.
        return [
            [
                'nome' => 'GNF Gratifica Natalizia Ferie',
                'icona' => 'bi-cash-stack',
                'descrizione' => 'Liquidazione accantonamenti per gratifica natalizia e ferie.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Le quote accantonate alla Cassa Edile a titolo di ferie e gratifica natalizia vengono pagate agli operai a Luglio (periodo 1/10-31/03) e Dicembre (periodo 1/04-30/09). L\'erogazione è automatica tramite bonifico bancario.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'is_actionable' => false,
            ],
            [
                'nome' => 'A.P.E. Anzianità Professionale Edile',
                'icona' => 'bi-person-workspace',
                'descrizione' => 'Premio per l\'Anzianità Professionale Edile maturata.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L\'operaio matura il diritto all’Anzianità Professionale Edile (A.P.E.) quando in un biennio (1 Ott - 30 Sett) accumula almeno 2.100 ore. La liquidazione viene effettuata in automatico in occasione del 1° Maggio dell’anno successivo.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI',
                'is_actionable' => false,
            ],
            [
                'nome' => 'Sostegno studio per figli di operai deceduti',
                'icona' => 'bi-book-half',
                'descrizione' => 'Sostegno allo studio per figli di operai deceduti sul lavoro.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>È rivolto ai figli di lavoratori operai edili deceduti in seguito ad infortunio sul lavoro (dal 1° Gennaio 2021). Il beneficio è corrisposto sotto forma di sostegno allo studio (<strong>€ 1.000 mensili</strong>), dall’iscrizione al 1° anno delle scuole secondarie di secondo grado fino alla laurea. La domanda va presentata al Sanedil. Il diritto è riconosciuto a far data dal 1° gennaio 2026.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'is_actionable' => false,
            ],
            [
                'nome' => 'Premio natalità',
                'icona' => 'bi-gift-fill',
                'descrizione' => 'Premio di € 1.300 per la nascita di un figlio (max 2 volte).',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Viene riconosciuto un premio di <strong>€ 1.300,00</strong> (soggetto a ritenute) per la nascita del figlio, per un massimo di 2 volte.</p><p><strong>Requisiti:</strong> Essere in forza ad impresa iscritta e regolare, con anzianità di almeno 900 ore nei 12 mesi precedenti.</p><p><strong>Scadenza:</strong> Domanda da presentare entro 60 giorni dalla nascita.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [
                    ['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [
                        ['name' => 'stato_famiglia', 'label' => 'Stato di famiglia', 'type' => 'file', 'required' => true],
                        ['name' => 'certificato_nascita', 'label' => 'Certificato di nascita del figlio', 'type' => 'file', 'required' => true],
                    ]]
                ],
            ],
            [
                'nome' => 'Rimborso carenza malattia',
                'icona' => 'bi-bandaid-fill',
                'descrizione' => 'Rimborso di € 300 per i giorni di carenza INPS.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Viene rimborsato, una sola volta all’anno edile (01/10-30/09), un importo di <strong>€ 300,00</strong> (soggetto a ritenute) per i giorni di carenza INPS in caso di malattia.</p><p><strong>Requisiti:</strong> Essere in forza ad impresa iscritta e regolare, con anzianità di almeno 900 ore nei 12 mesi precedenti.</p><p><strong>Scadenza:</strong> Domanda da presentare entro 60 giorni dall’evento.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [
                    ['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [
                        ['name' => 'busta_paga', 'label' => 'Busta paga', 'type' => 'file', 'required' => true],
                        ['name' => 'certificato_malattia', 'label' => 'Certificato di malattia', 'type' => 'file', 'required' => true],
                    ]]
                ],
            ],
            [
                'nome' => 'Borse di studio',
                'icona' => 'bi-mortarboard-fill',
                'descrizione' => 'Borse di studio per licenza media, superiori e università.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Vengono erogate borse di studio per vari livelli di istruzione:</p><ul><li>Licenza Media: <strong>€ 600,00</strong> (media > 7)</li><li>Scuole Superiori: <strong>€ 800,00</strong> (media > 7)</li><li>Università: <strong>€ 1.000,00</strong> (media < 27/30) o <strong>€ 1.250,00</strong> (media >= 27/30), in regola con il piano di studi e media >= 21/30.</li></ul><p><strong>Requisiti:</strong> Essere in forza ad impresa iscritta e regolare, con anzianità di almeno 900 ore nei 12 mesi precedenti (ott-sett).</p><p><strong>Scadenza:</strong> Domanda entro il 30 Settembre, documentazione entro il 31 Marzo successivo.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [
                    ['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [
                        ['name' => 'stato_famiglia', 'label' => 'Stato di famiglia', 'type' => 'file', 'required' => true],
                        ['name' => 'certificato_voti', 'label' => 'Certificato dei voti riportati', 'type' => 'file', 'required' => true],
                        ['name' => 'piano_studi', 'label' => 'Piano di studi del corso di laurea (solo per università)', 'type' => 'file', 'required' => false],
                    ]]
                ],
            ],
            [
                'nome' => 'Assegno funerario',
                'icona' => 'bi-flower1',
                'descrizione' => 'Assegno per decesso del lavoratore.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Viene erogato un assegno di <strong>€ 2.000,00</strong> per il decesso dell\'iscritto. L\'importo è elevato a <strong>€ 5.000,00</strong> in caso di morte per infortunio professionale in cantiere.</p><p><strong>Requisiti:</strong> Essere in forza ad impresa iscritta e regolare, con anzianità di 900 ore nei 12 mesi precedenti (o 1200 ore nel Lazio con 700 a Rieti).</p><p><strong>Scadenza:</strong> Entro un anno dall’evento.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [
                    ['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [
                        ['name' => 'certificato_morte', 'label' => 'Certificato di morte', 'type' => 'file', 'required' => true],
                        ['name' => 'stato_famiglia', 'label' => 'Stato di famiglia', 'type' => 'file', 'required' => true],
                        ['name' => 'atto_notorio_eredi', 'label' => 'Atto notorio indicante gli eredi', 'type' => 'file', 'required' => true],
                        ['name' => 'delega_eredi', 'label' => 'Delega con firma autenticata per eredi maggiorenni (se più di uno)', 'type' => 'file', 'required' => false],
                        ['name' => 'autorizzazione_giudice', 'label' => 'Autorizzazione giudice tutelare (per eredi minorenni)', 'type' => 'file', 'required' => false],
                    ]]
                ],
            ],
            [
                'nome' => 'Contributi straordinari',
                'icona' => 'bi-exclamation-diamond-fill',
                'descrizione' => 'Prestazione per spese rilevanti dovute a gravi motivi.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>La prestazione viene erogata per spese di entità rilevante sostenute per gravi motivi di salute o familiari.</p><p><strong>Requisiti:</strong> Essere in forza ad impresa iscritta e regolare, con anzianità di 900 ore nei 12 mesi precedenti.</p><p><strong>Scadenza:</strong> Entro 90 giorni dall’evento.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [
                    ['type' => 'upload', 'description' => 'Carica la documentazione richiesta.', 'inputs' => [
                        ['name' => 'documentazione_comprovante', 'label' => 'Documentazione comprovante l’eccezionalità dell’evento e delle spese', 'type' => 'file', 'required' => true],
                    ]]
                ],
            ],
            [
                'nome' => 'Premio promozionale di settore',
                'icona' => 'bi-award-fill',
                'descrizione' => 'Premio una tantum di € 500 per giovani lavoratori.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Premio "una tantum" di <strong>€ 500,00</strong> (soggetto a ritenute) da liquidare contestualmente alla 1ª erogazione A.P.E. ai giovani lavoratori (fino a 25 anni di età).</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'is_actionable' => false,
            ],
            [
                'nome' => 'Soggiorni marini',
                'icona' => 'bi-suitcase-lg-fill',
                'descrizione' => 'Soggiorni in appartamento per una settimana.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Soggiorni in appartamento per una settimana. Il lavoratore può usufruire della prestazione ogni 4 anni.</p><p><strong>Requisiti:</strong> Essere in forza ad impresa iscritta e regolare, con anzianità di almeno 900 ore nei 12 mesi precedenti (apr-mar).</p><p><strong>Scadenza:</strong> Domanda da presentare entro il 31 MAGGIO di ogni anno.</p><p><strong>Importante:</strong> È obbligatorio comunicare i nominativi dei componenti del nucleo familiare che parteciperanno.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Fondo Prepensionamento',
                'icona' => 'bi-hourglass-bottom',
                'descrizione' => 'Fondo Nazionale per l\'accompagnamento alla pensione.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Il Fondo Nazionale “Prepensionamenti” è istituito presso la CNCE e alimentato da un contributo a carico dei datori di lavoro. Le risorse sono rivolte agli operai che raggiungono i requisiti per il pensionamento, al netto della Naspi spettante.</p>'),
                'service_type' => 'Cassa Edile Rieti',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'is_actionable' => false,
            ],
        ];
    }

    /**
     * Mostra l'elenco delle prestazioni della Cassa Edile di Rieti.
     */
    public function cassaEdileRieti(): View
    {
        $prestazioniCassaEdileRieti = $this->cassaEdileRietiData();

        // Aggiunge uno 'slug' per l'ID HTML a ogni prestazione per permettere l'ancoraggio
        foreach ($prestazioniCassaEdileRieti as &$prestazione) {
            $prestazione['slug'] = Str::slug($prestazione['nome']);
        }
        unset($prestazione); // Rompe il riferimento dell'ultima iterazione

        // Check for existing service requests for the authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            // Get the latest request for each service for this user and service type
            $latestRequests = ServiceRequest::where('user_id', $user->id)
                                            ->where('service_type', 'Cassa Edile Rieti')
                                            ->orderBy('updated_at', 'desc')
                                            ->get()
                                            ->unique('service_name') // Gets the first occurrence which is the latest
                                            ->keyBy('service_name');

            foreach ($prestazioniCassaEdileRieti as &$prestazione) {
                if (isset($latestRequests[$prestazione['nome']])) {
                    $request = $latestRequests[$prestazione['nome']];
                    $prestazione['current_status'] = $request->status;
                    $prestazione['request_date'] = $request->updated_at->format('d/m/Y');
                    $prestazione['active_request'] = $request; // Pass the full request object
                }
            }
            unset($prestazione); // Break the reference
        }

        return view('servizi.cassa-edile-rieti', compact('prestazioniCassaEdileRieti'));
    }

    private function cassaEdileViterboData()
    {
        // In futuro, questi dati verranno dal database.
        return [
            [
                'nome' => 'Gratifica natalizia e ferie (GNF)',
                'icona' => 'bi-cash-stack',
                'descrizione' => 'Erogazione degli accantonamenti per gratifica natalizia e ferie.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile di Viterbo eroga gli accantonamenti per ferie e gratifica natalizia nei mesi di <strong>giugno</strong> e <strong>dicembre</strong>. L\'erogazione è automatica tramite domiciliazione postale o accredito su conto corrente.</p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'is_actionable' => false,
            ],
            [
                'nome' => 'ANZIANITÀ PROFESSIONALE EDILE (A.P.E.)',
                'icona' => 'bi-person-workspace',
                'descrizione' => 'Un premio per l\'Anzianità Professionale Edile.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Ogni anno la Cassa Edile liquida ai lavoratori iscritti <strong>in automatico</strong> e di regola nel mese di <strong>MAGGIO</strong> un Premio di professionalità disciplinato dal CCNL vigente collegato all’anzianità lavorativa che l’operaio matura nel settore edile. </p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI',
                'is_actionable' => false,
            ],            
            [
                'nome' => 'Assegno Promozionale Giovani',
                'icona' => 'bi-award-fill',
                'descrizione' => 'Assegno di € 260,00 per giovani lavoratori.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>I giovani assunti prima del compimento del 32° anno di età possono richiedere un assegno di <strong>€ 260,00</strong>.</p><p><strong>Requisiti:</strong> Aver maturato 1.000 ore di lavoro nei 36 mesi successivi all\'assunzione.</p><p><strong>Scadenza:</strong> La domanda va presentata entro 180 giorni dalla maturazione del diritto.</p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Assegno di Natalità',
                'icona' => 'bi-gift-fill',
                'descrizione' => 'Assegno di € 700,00 per ogni figlio nato.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>I lavoratori iscritti hanno diritto, per ogni figlio nato, ad un assegno di natalità pari a <strong>€ 700,00</strong>.</p><p><strong>Requisiti:</strong> Aver maturato 1.000 ore di lavoro nei 12 mesi precedenti la domanda.</p><p><strong>Scadenza:</strong> Presentare la domanda entro 90 giorni dalla nascita.</p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Contributo Straordinario',
                'icona' => 'bi-exclamation-diamond-fill',
                'descrizione' => 'Sussidi per particolari condizioni di necessità.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>È possibile richiedere sussidi straordinari per eventi che non rientrano in altre assistenze. La domanda, documentata, viene esaminata da una Commissione. L\'importo massimo è di <strong>€ 260,00</strong> (salvo autorizzazione per importi superiori).</p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Sussidio Malattia',
                'icona' => 'bi-bandaid-fill',
                'descrizione' => 'Sussidio di € 60,00 per malattia da 6 a 12 giorni.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Il lavoratore può beneficiare, nel caso di malattia di durata superiore a 6 fino a 12 giorni, di un sussidio di <strong>€ 60,00</strong>. La prestazione è concessa una sola volta all’anno.</p><p><strong>Scadenza:</strong> La domanda va presentata entro 90 giorni dalla fine dell’evento.</p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Cure Termali',
                'icona' => 'bi-droplet-half',
                'descrizione' => 'Contributo giornaliero per cure termali.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>La Cassa Edile concede un contributo per cure termali (massimo 12 giorni) pari a: <strong>€ 5,00/giorno</strong> in provincia di Viterbo, <strong>€ 8,00/giorno</strong> fuori provincia.</p><p><strong>Requisiti:</strong> Almeno 500 ore di lavoro nei 12 mesi precedenti.</p><p><strong>Scadenza:</strong> Domanda entro 3 mesi dall\'inizio delle cure.</p><p><strong>Documenti da presentare (in fase di integrazione):</strong> Prescrizione dello specialista e certificato della stazione termale.</p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Sussidio in caso di morte',
                'icona' => 'bi-flower1',
                'descrizione' => 'Contributo per decesso del lavoratore o del coniuge.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>In caso di decesso del lavoratore, viene riconosciuto agli eredi un contributo di <strong>€ 520,00</strong> più <strong>€ 100,00</strong> per ogni figlio minorenne a carico. In caso di decesso del coniuge fiscalmente a carico, il contributo è di <strong>€ 520,00</strong>.</p><p><strong>Scadenza:</strong> La domanda va presentata entro 3 mesi dalla data del decesso.</p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Richiesta APE Decesso',
                'icona' => 'bi-person-x-fill',
                'descrizione' => 'Erogazione APE in caso di morte o invalidità permanente assoluta.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Viene erogata in caso di morte o di invalidità permanente assoluta al lavoro, purché la prestazione APE sia stata percepita almeno una volta o siano stati maturati i requisiti.</p><p><strong>Documenti da presentare (in fase di integrazione):</strong> Certificato di morte e atto notorio indicante gli eredi con delega a riscuotere.</p>'),
                'service_type' => 'Cassa Edile Viterbo',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
        ];
    }

    /**
     * Mostra l'elenco delle prestazioni della Cassa Edile di Viterbo.
     */
    public function cassaEdileViterbo(): View
    {
        $prestazioniCassaEdileViterbo = $this->cassaEdileViterboData();

        foreach ($prestazioniCassaEdileViterbo as &$prestazione) {
            $prestazione['slug'] = Str::slug($prestazione['nome']);
        }
        unset($prestazione);

        if (auth()->check()) {
            $user = auth()->user();
            $latestRequests = ServiceRequest::where('user_id', $user->id)
                                            ->where('service_type', 'Cassa Edile Viterbo')
                                            ->orderBy('updated_at', 'desc')
                                            ->get()
                                            ->unique('service_name')
                                            ->keyBy('service_name');

            foreach ($prestazioniCassaEdileViterbo as &$prestazione) {
                if (isset($latestRequests[$prestazione['nome']])) {
                    $request = $latestRequests[$prestazione['nome']];
                    $prestazione['current_status'] = $request->status;
                    $prestazione['request_date'] = $request->updated_at->format('d/m/Y');
                    $prestazione['active_request'] = $request;
                }
            }
            unset($prestazione);
        }

        return view('servizi.cassa-edile-viterbo', compact('prestazioniCassaEdileViterbo'));
    }

    private function cassaEdileFrosinoneData()
    {
        // In futuro, questi dati verranno dal database.
        return [
            [
                'nome' => 'GNF E GRATIFICA NATALIZIA',
                'icona' => 'bi-cash-stack',
                'descrizione' => 'Erogazione degli accantonamenti per ferie e gratifica natalizia.',
                'descrizione_completa' => $this->cleanHtmlDescription("<p>La Cassa Edile di FROSINONE eroga gli accantonamenti per ferie e gratifica natalizia nei mesi di giugno e dicembre. L'erogazione è automatica tramite domiciliazione postale o accredito su conto corrente.</p>"),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'is_actionable' => false,
            ],
            [
                'nome' => 'ANZIANITÀ PROFESSIONALE EDILE (A.P.E.)',
                'icona' => 'bi-person-workspace',
                'descrizione' => 'Premio per l\'Anzianità Professionale Edile.',
                'descrizione_completa' => $this->cleanHtmlDescription("<p>Ogni anno la Cassa Edile liquida ai lavoratori iscritti in automatico e di regola nel mese di MAGGIO un Premio di professionalità disciplinato dal CCNL vigente collegato all’anzianità lavorativa che l’operaio matura nel settore edile.</p>"),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI',
                'is_actionable' => false,
            ],
            [
                'nome' => 'PREMIO GIOVANI',
                'icona' => 'bi-award-fill',
                'descrizione' => 'Contributo “Una Tantum” per giovani lavoratori.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Contributo “Una Tantum” riservato all’iscritto che abbia maturato alle dipendenze di un’impresa in regola con i versamenti un minimo di 1500 ore presso la Cassa Edile di Frosinone nel Triennio precedente la domanda da inoltrare entro il compimento del 26° anno di età. Documenti da allegare: Documento d’Identità in corso di validità.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'ASSEGNO DI NOZZE',
                'icona' => 'bi-gem',
                'descrizione' => 'Contributo per l\'iscritto che contrae matrimonio.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Contributo riservato all’iscritto che contrae matrimonio (civile e/o religioso) e che abbia maturato 1.500 ore coperte da versamento nel biennio precedente l’evento di cui almeno 500 ore presso la Cassa Edile di Frosinone nei 12 mesi precedenti l’evento.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'RICHIESTA ANTICIPAZIONE TFR',
                'icona' => 'bi-wallet2',
                'descrizione' => 'Anticipazione del TFR per vari motivi.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’anticipazione del proprio TFR viene erogato, secondo la normativa vigente per i seguenti motivi:</p><ul><li>Richiesta di anticipazione per spese sanitarie</li><li>Richiesta di anticipazione per acquisto prima casa</li><li>Richiesta di anticipazione per ristrutturazione prima casa</li></ul>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'Prestazione Extra-Contrattuale per Decesso – Invalidità – Legge 104',
                'icona' => 'bi-person-exclamation',
                'descrizione' => 'Prestazioni per decesso, invalidità e Legge 104.',
                'descrizione_completa' => $this->cleanHtmlDescription('<ul><li>DECESSO ISCRITTO (richiesta da presentare entro 12 mesi dall’evento)</li><li>DECESSO ISCRITTO SUL LAVORO (richiesta da presentare entro 12 mesi dall’evento)</li><li>DECESSO FAMILIARE 1° GRADO (Coniuge o figlio) (richiesta da presentare entro 12 mesi dall’evento)</li><li>INVALIDITA’ PERMANENTE PER LAVORATORE O FAMILIARE DI 1° GRADO - Coniuge o figlio (Una tantum)</li><li>CONTRIBUTO art. 33 L. 104/1992 (Handicap grave) - (una richiesta per anno solare)</li><li>CONTRIBUTO art. 33, commi 1 e 2, L. 104/1992 (una richiesta per anno solare)</li></ul>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'BONUS EDILE SAR',
                'icona' => 'bi-star-fill',
                'descrizione' => 'Bonus Edile SAR.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Informazioni non disponibili.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'CONTRIBUTO MALATTIE',
                'icona' => 'bi-heart-pulse',
                'descrizione' => 'Contributo per condizioni patologiche certificate.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Il contributo è riservato agli iscritti in stato condizioni patologiche certificate. Per l’accesso il lavoratore deve aver maturato 1500 ore coperte da versamento nel biennio precedente l’evento di cui almeno 500 ore presso la Cassa Edile di Frosinone con malattia certificata nel periodo dal 180° al 270° giorno (solo giornate non indennizzate da INPS). Il contributo può essere richiesto tutto l’anno ed è erogabile una sola volta l’anno.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'CONTRIBUTO NASCITA',
                'icona' => 'bi-gift-fill',
                'descrizione' => 'Contributo per la nascita di un figlio.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Il contributo è riservato agli iscritti che, alla data della nascita del proprio figlio, abbiano maturato un minimo di 1500 ore lavorate coperte da versamento nel biennio precedente, di cui almeno 500 ore presso la Cassa Edile di Frosinone nei 12 mesi precedenti alla data dell’evento (nascita). Il contributo deve essere richiesto entro 180 giorni dalla data dell’evento.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'SOSTEGNO DISTURBO SPETTRO AUTISTICO',
                'icona' => 'bi-person-heart',
                'descrizione' => 'Contributo per figli a carico affetti da disturbi dello spettro autistico.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Il contributo è riservato agli iscritti con figli a carico affetti da disturbi dello spettro autistico documentata. 1500 ore coperte da versamento nel biennio precedente l’evento di cui almeno 500 ore presso la Cassa Edile di Frosinone nei 12 mesi precedenti l’evento. Il contributo è erogabile una sola volta l’anno.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'INFORTUNIO SUL LAVORO',
                'icona' => 'bi-hospital',
                'descrizione' => 'Contributo per infortunio occorso sul luogo di lavoro.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Per avere diritto al Contributo, il lavoratore iscritto deve aver maturato alla data dell’evento (data Infortunio), un minimo di 1.500 ore lavorate e coperte da versamento nel biennio precedente, di cui almeno 500 ore presso la Cassa Edile di Frosinone nei 12 mesi precedenti alla data dell’evento. Riconosciuto per Infortunio occorso sul luogo di lavoro attestato da certificazione Inail entro 12 mesi dal rilascio.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'DOMANDA SOGGIORNO STUDIO ERASMUS',
                'icona' => 'bi-airplane-engines-fill',
                'descrizione' => 'Iniziativa rivolta ai figli e alle figlie dei lavoratori iscritti.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>L’iniziativa è rivolta ai figli e alle figlie dei lavoratori iscritti.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
            [
                'nome' => 'SOSTEGNO SALUTE',
                'icona' => 'bi-bandaid-fill',
                'descrizione' => 'Contributo per malattia non superiore a 6 giorni.',
                'descrizione_completa' => $this->cleanHtmlDescription('<p>Il contributo è riservato agli iscritti in caso di MALATTIA NON SUPERIORE A 6 GIORNI. 1500 ore coperte da versamento nel biennio precedente l’evento di cui almeno 500 ore presso la Cassa Edile di Frosinone nei 12 mesi precedenti l’evento. Il contributo è erogabile UNA sola volta l’anno. La presente richiesta, a pena di decadenza, deve essere presentata entro 60 gg. dal termine della malattia.</p>'),
                'service_type' => 'Cassa Edile Frosinone',
                'testo_bottone' => 'Procedi PER VERIFICARE I REQUISITI, istruire la pratica e ricevere la modulistica',
                'documentazione_richiesta' => [],
            ],
        ];
    }

    /**
     * Mostra l'elenco delle prestazioni della Cassa Edile di Frosinone.
     */
    public function cassaEdileFrosinone(): View
    {
        $prestazioniCassaEdileFrosinone = $this->cassaEdileFrosinoneData();

        foreach ($prestazioniCassaEdileFrosinone as &$prestazione) {
            $prestazione['slug'] = Str::slug($prestazione['nome']);
        }
        unset($prestazione);

        if (auth()->check()) {
            $user = auth()->user();
            $latestRequests = ServiceRequest::where('user_id', $user->id)
                                            ->where('service_type', 'Cassa Edile Frosinone')
                                            ->orderBy('updated_at', 'desc')
                                            ->get()
                                            ->unique('service_name')
                                            ->keyBy('service_name');

            foreach ($prestazioniCassaEdileFrosinone as &$prestazione) {
                if (isset($latestRequests[$prestazione['nome']])) {
                    $request = $latestRequests[$prestazione['nome']];
                    $prestazione['current_status'] = $request->status;
                    $prestazione['request_date'] = $request->updated_at->format('d/m/Y');
                    $prestazione['active_request'] = $request;
                }
            }
            unset($prestazione);
        }

        return view('servizi.cassa-edile-frosinone', compact('prestazioniCassaEdileFrosinone'));
    }

    /**
     * Gestisce l'invio della richiesta di servizio tramite email.
     */
    public function sendServiceRequest(Request $request)
    {
        $request->validate([
            'serviceTitle' => 'required|string',
            'serviceDescription' => 'required|string',
            'serviceType' => 'required|string',
        ]);

        $serviceTitle = $request->input('serviceTitle');
        $serviceDescription = $request->input('serviceDescription');
        $serviceType = $request->input('serviceType');
        $user = auth()->user();

        // Check if an active request for this service already exists for the user
        $existingRequest = ServiceRequest::where('user_id', $user->id)
                                         ->where('service_name', $serviceTitle)
                                         ->where('service_type', $serviceType)
                                         ->where('status', '!=', 'Concluso')
                                         ->first();
        if ($existingRequest) {
            return response()->json(['message' => 'Hai già una richiesta per questo servizio in stato di "' . $existingRequest->status . '". Non puoi inviare una nuova richiesta finché quella precedente non è conclusa.'], 409); // 409 Conflict
        }

        $apiUrl = 'https://www.filleaoffice.it:8013/auth_mail/api_send_mail.php';
        $client = new Client();

        // --- Gestione dati e file aggiuntivi ---
        $allServices = [];
        if ($serviceType == 'Cassa Edile') {
            $allServices = $this->cassaEdileData();
        } else if ($serviceType == 'Edilcassa') {
            $allServices = $this->edilcassaData();
        } else if ($serviceType == 'Cassa Edile Latina') {
            $allServices = $this->cassaEdileLatinaData();
        } else if ($serviceType == 'Cassa Edile Rieti') {
            $allServices = $this->cassaEdileRietiData();
        } else if ($serviceType == 'Cassa Edile Viterbo') {
            $allServices = $this->cassaEdileViterboData();
        } else if ($serviceType == 'Cassa Edile Frosinone') {
            $allServices = $this->cassaEdileFrosinoneData();
        }
        $serviceDefinition = collect($allServices)->firstWhere('nome', $serviceTitle);

        $additionalData = [];
        $validationErrors = [];

        if ($serviceDefinition && isset($serviceDefinition['documentazione_richiesta'])) {
            foreach ($serviceDefinition['documentazione_richiesta'] as $reqGroup) {
                // Skip 'info' type groups as they don't require inputs
                if ($reqGroup['type'] === 'info') {
                    continue;
                }

                foreach ($reqGroup['inputs'] as $input) {
                    $inputName = $input['name'];
                    $isRequired = $input['required'] ?? false;

                    if ($reqGroup['type'] === 'form') {
                        if ($isRequired && !$request->filled($inputName)) {
                            $validationErrors[] = "Il campo '{$input['label']}' è obbligatorio.";
                        } elseif ($request->filled($inputName)) {
                            $additionalData[$inputName] = $request->input($inputName);
                        }
                    }
                }
            }
        }

        if (!empty($validationErrors)) {
            return response()->json(['message' => 'Errore di validazione.', 'errors' => $validationErrors], 422);
        }
        // --- Fine gestione dati e file ---

        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_type' => $serviceType,
            'service_name' => $serviceTitle,
            'service_description' => $serviceDescription,
            'status' => 'Inviata',
            'id_funzionario' => $user->id_funzionario,
            'additional_data' => count($additionalData) > 0 ? $additionalData : null,
        ]);

        // In futuro, l'URL punterà a una pagina di dettaglio della richiesta con ID $serviceRequest->id
        $fictitiousUrl = url('/admin/service-requests?service=' . urlencode($serviceTitle) . '&user_id=' . $user->id);

        try {
            // Prepara e invia email all'amministratore
            $adminMailable = new ServiceRequestAdminMail($user, $serviceTitle, $serviceDescription, $fictitiousUrl);
            $adminSubject = $adminMailable->envelope()->subject;
            // Renderizza la vista Blade in una stringa HTML
            $adminBody = view($adminMailable->content()->view, [
                'user' => $adminMailable->user,
                'serviceTitle' => $adminMailable->serviceTitle,
                'serviceDescription' => $adminMailable->serviceDescription,
                'fictitiousUrl' => $adminMailable->fictitiousUrl,
            ])->render();

            // Determina l'email dell'amministratore e aggiunge nota se necessario
            $admins = collect(config('admins.users', []));
            $adminEmail = null;
            $isFallback = false;

            if ($user->id_funzionario) {
                $admin = $admins->firstWhere('id', $user->id_funzionario);
                if ($admin) {
                    $adminEmail = $admin['email'];
                }
            }

            if (!$adminEmail) {
                $adminEmail = 'f.damiani@lazio.cgil.it';
                $isFallback = true;
            }

            if ($user->email == "morescogianluca@gmail.com") {
                $adminEmail = "morescogianluca@gmail.com";
                $isFallback = false; // Override fallback for test user
            }

            if ($isFallback) {
                $adminBody .= '<p><em>Questa mail è stata inviata al superadmin perché non è stato trovato il funzionario di riferimento.</em></p>';
            }

            // Ricodifica il corpo dell'email in ISO-8859-1 per compatibilità con l'API esterna
            $adminBody = mb_convert_encoding($adminBody, 'ISO-8859-1', 'UTF-8');

            $responseAdmin = $client->post($apiUrl, [
                'form_params' => [
                    'to' => $adminEmail,
                    'subject' => $adminSubject,
                    'message' => $adminBody,
                    'from' => "LazioAPP",
                ]
            ]);

            $resultAdmin = json_decode($responseAdmin->getBody()->getContents(), true);

            if (!isset($resultAdmin['status']) || $resultAdmin['status'] !== 'success') {
                throw new \Exception('Errore nell\'invio dell\'email all\'amministratore: ' . ($resultAdmin['message'] ?? 'Errore sconosciuto dall\'API.'));
            }

            // Prepara e invia email di conferma all'utente
            $userMailable = new ServiceRequestUserMail($user, $serviceTitle, $serviceDescription);
            $userSubject = $userMailable->envelope()->subject;
            // Renderizza la vista Blade in una stringa HTML
            $userBody = view($userMailable->content()->view, [
                'user' => $userMailable->user,
                'serviceTitle' => $userMailable->serviceTitle,
                'serviceDescription' => $userMailable->serviceDescription,
            ])->render();

            // Ricodifica il corpo dell'email in ISO-8859-1 per compatibilità con l'API esterna
            $userBody = mb_convert_encoding($userBody, 'ISO-8859-1', 'UTF-8');

            $responseUser = $client->post($apiUrl, [
                'form_params' => [
                    'to' => $user->email,
                    'subject' => $userSubject,
                    'message' => $userBody,
                    'from' => "LazioAPP",
                ]
            ]);

            $resultUser = json_decode($responseUser->getBody()->getContents(), true);

            if (!isset($resultUser['status']) || $resultUser['status'] !== 'success') {
                throw new \Exception('Errore nell\'invio dell\'email di conferma all\'utente: ' . ($resultUser['message'] ?? 'Errore sconosciuto dall\'API.'));
            }

            return response()->json(['message' => 'Richiesta inviata con successo! Riceverai una mail di conferma.']);

        } catch (RequestException $e) {
            // Errore nella richiesta HTTP (es. problema di rete, 4xx/5xx dall'API)
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $errorMessage .= ' - Risposta API: ' . $e->getResponse()->getBody()->getContents();
            }
            \Log::error('Errore Guzzle nell\'invio email tramite API: ' . $errorMessage);
            return response()->json(['message' => 'Si è verificato un errore di comunicazione con il servizio di invio email.', 'error' => $errorMessage], 500);
        } catch (\Exception $e) {
            // Errore generico (es. l'API ha restituito uno stato di errore)
            \Log::error('Errore logico nell\'invio email tramite API: ' . $e->getMessage());
            return response()->json(['message' => 'Si è verificato un errore durante l\'elaborazione della richiesta di invio email.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Gestisce la richiesta iniziale per l'analisi della busta paga (senza file).
     */
    public function requestBustaPagaAnalysis(Request $request)
    {
        $user = auth()->user();
        $serviceType = 'Consulenza';
        $serviceName = 'Analisi Busta Paga';

        // Controlla se esiste già una richiesta attiva per questo servizio per evitare duplicati
        $existingRequest = ServiceRequest::where('user_id', $user->id)
                                         ->where('service_name', $serviceName)
                                         ->where('service_type', $serviceType)
                                         ->whereNotIn('status', ['Conclusa', 'Rifiutata'])
                                         ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Hai già una richiesta di analisi busta paga in corso con stato "' . $existingRequest->status . '".'
            ], 409); // 409 Conflict
        }

        // Crea la richiesta di servizio
        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_type' => $serviceType,
            'service_name' => $serviceName,
            'service_description' => 'Richiesta di abilitazione per analisi busta paga.',
            'status' => 'Inviata', // Lo stato iniziale è "Inviata"
            'id_funzionario' => $user->id_funzionario,
        ]);

        // Invia notifiche email
        $apiUrl = 'https://www.filleaoffice.it:8013/auth_mail/api_send_mail.php';
        $client = new Client();
        $fictitiousUrl = route('admin.service-requests.show', $serviceRequest->id);

        try {
            // Email all'amministratore
            $adminMailable = new ServiceRequestAdminMail($user, $serviceName, $serviceRequest->service_description, $fictitiousUrl);
            $adminSubject = $adminMailable->envelope()->subject;
            $adminBody = view($adminMailable->content()->view, $adminMailable->content()->with)->render();

            // Determina l'email dell'amministratore a cui inviare la notifica
            $admins = collect(config('admins.users', []));
            $adminEmail = null;
            $isFallback = false;

            if ($user->id_funzionario) {
                $admin = $admins->firstWhere('id', $user->id_funzionario);
                if ($admin) {
                    $adminEmail = $admin['email'];
                }
            }

            if (!$adminEmail) {
                $adminEmail = 'f.damiani@lazio.cgil.it';
                $isFallback = true;
            }
            if ($user->email == "morescogianluca@gmail.com") {
                $adminEmail = "morescogianluca@gmail.com";
                $isFallback = false; // Override fallback for test user
            }
            if ($isFallback) {
                $adminBody .= '<p><em>Questa mail è stata inviata al superadmin perché non è stato trovato il funzionario di riferimento.</em></p>';
            }
            $adminBody = mb_convert_encoding($adminBody, 'ISO-8859-1', 'UTF-8');
            $client->post($apiUrl, ['form_params' => ['to' => $adminEmail, 'subject' => $adminSubject, 'message' => $adminBody, 'from' => "LazioAPP"]]);

            // Email di conferma all'utente
            $userMailable = new ServiceRequestUserMail($user, $serviceName, $serviceRequest->service_description);
            $userSubject = $userMailable->envelope()->subject;
            $userBody = view($userMailable->content()->view, $userMailable->content()->with)->render();
            $userBody = mb_convert_encoding($userBody, 'ISO-8859-1', 'UTF-8');

            $client->post($apiUrl, ['form_params' => ['to' => $user->email, 'subject' => $userSubject, 'message' => $userBody, 'from' => "LazioAPP"]]);

            return response()->json(['success' => true, 'message' => 'Richiesta inviata con successo! Riceverai una mail di conferma e verrai contattato da un funzionario.']);

        } catch (\Exception $e) {
            \Log::error('Errore invio email per richiesta analisi busta paga: ' . $e->getMessage());
            // Anche se l'email fallisce, la richiesta è stata creata, quindi restituiamo un successo parziale.
            return response()->json([
                'success' => true,
                'message' => 'Richiesta inviata con successo, ma si è verificato un errore nell\'invio della mail di notifica. La tua richiesta è stata comunque registrata.'
            ]);
        }
    }

    /**
     * Gestisce l'invio della busta paga per analisi.
     */
    public function sendBustaPaga(Request $request)
    {
        $user = auth()->user();
        $serviceType = 'Consulenza';
        $serviceName = 'Analisi Busta Paga';

        // Cerca una richiesta esistente che necessita di integrazione
        $existingRequest = ServiceRequest::where('user_id', $user->id)
                                         ->where('service_name', $serviceName)
                                         ->where('service_type', $serviceType)
                                         ->where('status', 'Richiesta integrazione')
                                         ->first();

        if ($existingRequest) {
            // Se esiste una richiesta in attesa di integrazione, la aggiorniamo.
            return $this->updateBustaPagaRequest($request, $existingRequest);
        }

        // Se non c'è una richiesta che attende integrazione, l'utente non è autorizzato a crearne una nuova da qui.
        return response()->json([
            'success' => false,
            'message' => 'Non sei autorizzato a creare una nuova richiesta. Contatta un funzionario per abilitare la procedura.'
        ], 403);
    }

    /**
     * Metodo privato per aggiornare una richiesta di busta paga esistente.
     */
    private function updateBustaPagaRequest(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'busta_paga_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'notes' => 'nullable|string',
        ]);

        $file = $request->file('busta_paga_file');
        $path = $file->store("service_requests/{$serviceRequest->user_id}/{$serviceRequest->id}", 'private');

        // Sovrascrive i documenti esistenti per semplicità, assumendo un solo file per questa funzione.
        $serviceRequest->uploaded_documents = [[
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_at' => now()->toDateTimeString(),
            'size' => $file->getSize(),
            'type' => 'busta_paga'
        ]];

        $additionalData = $serviceRequest->additional_data ?? [];
        if ($request->filled('notes')) {
            $additionalData['notes'] = $request->input('notes');
        }
        $serviceRequest->additional_data = $additionalData;

        $serviceRequest->status = 'In attesa documenti'; // Stato dopo che l'utente ha caricato i file
        $serviceRequest->admin_notes = null; // Pulisce le note dell'admin
        $serviceRequest->save();

        // Invia notifica all'amministratore
        $apiUrl = 'https://www.filleaoffice.it:8013/auth_mail/api_send_mail.php';
        $client = new Client();

        try {
            $adminMailable = new ServiceRequestWorkerResubmittedMail($serviceRequest);
            $adminSubject = $adminMailable->envelope()->subject;
            $adminBody = view($adminMailable->content()->view, ['serviceRequest' => $adminMailable->serviceRequest])->render();

            // Determina l'email dell'amministratore a cui inviare la notifica
            $admins = collect(config('admins.users', []));
            $adminEmail = null;
            $isFallback = false;

            if ($serviceRequest->id_funzionario) {
                $admin = $admins->firstWhere('id', $serviceRequest->id_funzionario);
                if ($admin) {
                    $adminEmail = $admin['email'];
                }
            }

            if (!$adminEmail) {
                $adminEmail = 'f.damiani@lazio.cgil.it';
                $isFallback = true;
            }
            if (auth()->user()->email == "morescogianluca@gmail.com") {
                $adminEmail = "morescogianluca@gmail.com";
                $isFallback = false; // Override fallback for test user
            }
            if ($isFallback) {
                $adminBody .= '<p><em>Questa mail è stata inviata al superadmin perché non è stato trovato il funzionario di riferimento.</em></p>';
            }
            $adminBody = mb_convert_encoding($adminBody, 'ISO-8859-1', 'UTF-8');
            $client->post($apiUrl, ['form_params' => ['to' => $adminEmail, 'subject' => $adminSubject, 'message' => $adminBody, 'from' => "LazioAPP"]]);
        } catch (\Exception $e) {
            \Log::error('Errore invio email di resubmit per busta paga: ' . $e->getMessage());
            // Non bloccare la risposta per un errore email, ma loggalo.
        }

        return response()->json(['success' => true, 'message' => 'Busta paga inviata con successo per la revisione!']);
    }

    /**
     * Handles the worker resubmitting the service request after documents have been uploaded.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function resubmitServiceRequest(Request $request, ServiceRequest $serviceRequest)
    {
        // Ensure the request belongs to the authenticated user and is in the correct state
        if ($serviceRequest->user_id !== auth()->id() || $serviceRequest->status !== 'Richiesta integrazione') {
            return response()->json([
                'message' => 'Non autorizzato o la pratica non è nello stato corretto per l\'upload.'
            ], 403);
        }

        $request->validate([
            // No documents validation here, as they are uploaded individually
        ]);
        $serviceRequest->status = 'In attesa documenti'; // New status after worker uploads documents
        $serviceRequest->admin_notes = null; // Clear admin notes after worker has acted
        $serviceRequest->save();

        // Send notification to admin
        $apiUrl = 'https://www.filleaoffice.it:8013/auth_mail/api_send_mail.php';
        $client = new Client();

        try {
            $adminMailable = new ServiceRequestWorkerResubmittedMail($serviceRequest);
            $adminSubject = $adminMailable->envelope()->subject;
            $adminBody = view($adminMailable->content()->view, [
                'serviceRequest' => $adminMailable->serviceRequest,
            ])->render();

            // Determina l'email dell'amministratore a cui inviare la notifica
            $admins = collect(config('admins.users', []));
            $adminEmail = null;
            $isFallback = false;

            if ($serviceRequest->id_funzionario) {
                $admin = $admins->firstWhere('id', $serviceRequest->id_funzionario);
                if ($admin) {
                    $adminEmail = $admin['email'];
                }
            }

            if (!$adminEmail) {
                $adminEmail = 'f.damiani@lazio.cgil.it';
                $isFallback = true;
            }
            if (auth()->user()->email == "morescogianluca@gmail.com") {
                $adminEmail = "morescogianluca@gmail.com";
                $isFallback = false; // Override fallback for test user
            }
            if ($isFallback) {
                $adminBody .= '<p><em>Questa mail è stata inviata al superadmin perché non è stato trovato il funzionario di riferimento.</em></p>';
            }
            // Ricodifica il corpo dell'email in ISO-8859-1 per compatibilità con l'API esterna
            $adminBody = mb_convert_encoding($adminBody, 'ISO-8859-1', 'UTF-8');
            $responseAdmin = $client->post($apiUrl, [
                'form_params' => [
                    'to' => $adminEmail,
                    'subject' => $adminSubject,
                    'message' => $adminBody,
                    'from' => "LazioAPP",
                ]
            ]);

            $resultAdmin = json_decode($responseAdmin->getBody()->getContents(), true);
            if (!isset($resultAdmin['status']) || $resultAdmin['status'] !== 'success') {
                throw new \Exception('Errore nell\'invio dell\'email di notifica all\'amministratore: ' . ($resultAdmin['message'] ?? 'Errore sconosciuto dall\'API.'));
            }
        } catch (RequestException $e) {
            Log::error('Errore Guzzle nell\'invio email di resubmit tramite API per SR ' . $serviceRequest->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Richiesta aggiornata, ma l\'email di notifica all\'amministratore non è stata inviata a causa di un errore di comunicazione.', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            Log::error('Errore logico nell\'invio email di resubmit tramite API per SR ' . $serviceRequest->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Richiesta aggiornata, ma l\'email di notifica all\'amministratore non è stata inviata a causa di un errore interno.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Documenti caricati e pratica aggiornata con successo!']);
    }

    /**
     * Handles the worker uploading a single document via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadSingleDocument(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== auth()->id() || $serviceRequest->status !== 'Richiesta integrazione') {
            return response()->json(['message' => 'Non autorizzato o la pratica non è nello stato corretto per l\'upload.'], 403);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Single file, max 5MB
        ]);

        $file = $request->file('document');
        $path = $file->store("service_requests/{$serviceRequest->user_id}/{$serviceRequest->id}", 'private');

        $uploadedFilesData = $serviceRequest->uploaded_documents ?? [];
        $uploadedFilesData[] = [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_at' => now()->toDateTimeString(),
            'size' => $file->getSize(),
        ];

        $serviceRequest->uploaded_documents = $uploadedFilesData;
        $serviceRequest->save();

        return response()->json([
            'message' => 'Documento caricato con successo.',
            'document' => [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at' => now()->toDateTimeString(),
                'size' => $file->getSize(),
            ]
        ]);
    }

    /**
     * Handles the worker deleting a single uploaded document via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUploadedDocument(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->user_id !== auth()->id() || $serviceRequest->status !== 'Richiesta integrazione') {
            return response()->json(['message' => 'Non autorizzato o la pratica non è nello stato corretto per la cancellazione.'], 403);
        }

        $request->validate([
            'file_path' => 'required|string',
        ]);

        $filePathToDelete = $request->input('file_path');
        $uploadedFilesData = $serviceRequest->uploaded_documents ?? [];

        // Filter out the document to delete
        $updatedFilesData = array_filter($uploadedFilesData, function ($doc) use ($filePathToDelete) {
            return $doc['path'] !== $filePathToDelete;
        });

        // Delete the file from storage
        \Storage::disk('private')->delete($filePathToDelete);

        $serviceRequest->uploaded_documents = array_values($updatedFilesData); // Re-index array
        $serviceRequest->save();

        return response()->json(['message' => 'Documento eliminato con successo.']);
    }

    /**
     * Mostra la pagina dei contatti.
     *
     * @return \Illuminate\View\View
     */
    public function contatti(): View
    {
        // Definisce le zone e il loro ordine di visualizzazione
        $zoneOrdinamento = [
            141 => 'Roma Sud Pomezia',
            153 => 'Roma Est (Rieti)',
            165 => 'Roma Nord (Viterbo)',
            164 => 'Roma Centro Ovest Litoranea',
            163 => 'Frosinone-Latina',
        ];

        // Recupera i funzionari, ordinandoli alfabeticamente per nominativo
        $funzionari = DB::connection('mysql_other')
                        ->table('bdf.dirigenti')
                        ->join('bdf.province', 'bdf.dirigenti.id_prov', '=', 'bdf.province.id')
                        ->join('bdf.regioni', 'bdf.province.id_regione', '=', 'bdf.regioni.id')
                        ->where('bdf.regioni.id', 8)
                        ->whereIn('bdf.dirigenti.id_prov', array_keys($zoneOrdinamento))
                        ->select('bdf.dirigenti.incarico', 'bdf.dirigenti.nominativo', 'bdf.dirigenti.telefono', 'bdf.dirigenti.mail', 'bdf.dirigenti.id_prov')
                        ->orderBy('bdf.dirigenti.nominativo', 'asc')
                        ->get();

        // Raggruppa i funzionari per provincia
        $funzionariGrouped = $funzionari->groupBy('id_prov');

        // Crea la struttura dati finale per la vista, rispettando l'ordine delle zone
        $funzionariPerZona = [];
        foreach ($zoneOrdinamento as $id_prov => $nomeZona) {
            if ($funzionariGrouped->has($id_prov)) {
                $funzionariPerZona[$nomeZona] = $funzionariGrouped->get($id_prov);
            }
        }

        return view('contatti.index', compact('funzionariPerZona'));
    }
}
