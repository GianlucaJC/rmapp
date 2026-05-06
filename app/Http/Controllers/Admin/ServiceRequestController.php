<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ServiceRequestStatusUpdateMail; // Import the Mailable
use App\Models\ServiceRequest;
use GuzzleHttp\Client; // Import Guzzle Client
use GuzzleHttp\Exception\RequestException; // Import Guzzle Exception
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Facades\Storage; // Import Storage facade
use Illuminate\Support\Facades\Mail; // Import Mail facade
use App\Notifications\ServiceRequestUpdated;

class ServiceRequestController extends Controller
{
    /**
     * Process DataTables AJAX requests for service requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServiceRequestsData(Request $request)
    {
        if ($request->ajax()) {
            $currentUser = session('admin_user');
            $data = ServiceRequest::with('user');

            // Se non è un superadmin, mostra solo le richieste assegnate a lui/lei
            if ($currentUser && !$currentUser['superadmin']) {
                $data->where('id_funzionario', $currentUser['id']);
            }

            // Apply filters
            if ($request->filled('status')) {
                $data->where('status', $request->status);
            }
            if ($request->filled('service_type')) {
                $data->where('service_type', $request->service_type);
            }
            if ($request->filled('id_funzionario') && $currentUser && $currentUser['superadmin']) {
                $data->where('id_funzionario', $request->id_funzionario);
            }

            try {
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('user_name', function(ServiceRequest $serviceRequest) {
                        return $serviceRequest->user ? $serviceRequest->user->name : 'N/A';
                    })
                    ->addColumn('created_at', function(ServiceRequest $serviceRequest) {
                        return $serviceRequest->created_at->format('d/m/Y H:i');
                    })
                    ->addColumn('actions', function(ServiceRequest $serviceRequest) {
                        $detailUrl = route('admin.service-requests.show', $serviceRequest->id);
                        return '<a href="'.$detailUrl.'" class="btn btn-info btn-sm me-1"><i class="bi bi-eye"></i> Dettagli</a>';
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            } catch (\Exception $e) {
                \Log::error('DataTables AJAX Error: ' . $e->getMessage(), ['exception' => $e]);
                return response()->json(['error' => 'Si è verificato un errore interno durante il recupero dei dati. Controlla i log del server per i dettagli.'], 500);
            }
        }
    }

    /**
     * Show the details of a specific service request.
     *
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\View\View
     */
    public function show(ServiceRequest $serviceRequest)
    {
        return view('admin.service_requests.show', compact('serviceRequest'));
    }

    /**
     * Update the status and admin notes of a service request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'status' => 'required|string|in:Inviata,Richiesta integrazione,In attesa documenti,Conclusa,Rifiutata',
            'admin_notes' => 'nullable|string',
        ]);

        $oldStatus = $serviceRequest->status;

        $serviceRequest->status = $request->input('status');
        $serviceRequest->admin_notes = $request->input('admin_notes');
        $serviceRequest->save();

        // Invia notifiche (email e push) se lo stato è cambiato e l'utente esiste
        if ($oldStatus !== $serviceRequest->status && $serviceRequest->user) {
            // Invia la notifica PUSH
            try {
                $serviceRequest->user->notify(new ServiceRequestUpdated($serviceRequest));
            } catch (\Exception $e) {
                // Logga l'errore della notifica push ma non bloccare il flusso principale
                Log::error('Errore nell\'invio della notifica push per SR ' . $serviceRequest->id . ': ' . $e->getMessage());
            }

            // Invia la notifica EMAIL se l'utente ha un indirizzo email
            if ($serviceRequest->user->email) {
                $apiUrl = 'https://www.filleaoffice.it:8013/auth_mail/api_send_mail.php';
                $client = new Client();

                try {
                    $userMailable = new ServiceRequestStatusUpdateMail($serviceRequest);
                    $userSubject = $userMailable->envelope()->subject;
                    // Renderizza la vista Blade in una stringa HTML
                    $userBody = view($userMailable->content()->view, [
                        'serviceRequest' => $userMailable->serviceRequest,
                    ])->render();

                    // Ricodifica il corpo dell'email in ISO-8859-1 per compatibilità con l'API esterna
                    $userBody = mb_convert_encoding($userBody, 'ISO-8859-1', 'UTF-8');

                    $responseUser = $client->post($apiUrl, [
                        'form_params' => [
                            'to' => $serviceRequest->user->email,
                            'subject' => $userSubject,
                            'message' => $userBody,
                            'from' => "LazioAPP",
                        ]
                    ]);

                    $resultUser = json_decode($responseUser->getBody()->getContents(), true);

                    if (!isset($resultUser['status']) || $resultUser['status'] !== 'success') {
                        throw new \Exception('Errore nell\'invio dell\'email di notifica all\'utente: ' . ($resultUser['message'] ?? 'Errore sconosciuto dall\'API.'));
                    }
                } catch (RequestException $e) {
                    $errorMessage = $e->getMessage();
                    if ($e->hasResponse()) {
                        $errorMessage .= ' - Risposta API: ' . $e->getResponse()->getBody()->getContents();
                    }
                    Log::error('Errore Guzzle nell\'invio email di stato tramite API per SR ' . $serviceRequest->id . ': ' . $errorMessage);
                    return redirect()->back()->with('error', 'Richiesta aggiornata, ma l\'email di notifica all\'utente non è stata inviata a causa di un errore di comunicazione: ' . $errorMessage);
                } catch (\Exception $e) {
                    Log::error('Errore logico nell\'invio email di stato tramite API per SR ' . $serviceRequest->id . ': ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Richiesta aggiornata, ma l\'email di notifica all\'utente non è stata inviata a causa di un errore interno: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('admin.service-requests.show', $serviceRequest->id)
                         ->with('success', 'Richiesta di servizio aggiornata con successo.');
    }

    /**
     * Assign a service request to an admin. (Superadmin only)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request, ServiceRequest $serviceRequest)
    {
        // Solo i superadmin possono riassegnare
        if (!session('admin_user.superadmin')) {
            return response()->json(['success' => false, 'message' => 'Azione non autorizzata.'], 403);
        }

        $request->validate([
            'id_funzionario' => 'nullable|numeric',
        ]);

        $funzionarioId = $request->input('id_funzionario');

        // Opzionale: Controlla se l'ID del funzionario è valido rispetto alla lista statica
        if ($funzionarioId) {
            $admins = collect(config('admins.users', []));
            if (!$admins->contains('id', $funzionarioId)) {
                return response()->json(['success' => false, 'message' => 'Funzionario non valido.'], 422);
            }
        }

        $serviceRequest->id_funzionario = $funzionarioId ?: null;
        $serviceRequest->save();

        return response()->json(['success' => true, 'message' => 'Pratica riassegnata con successo.']);
    }

    /**
     * Download an uploaded document for a service request.
     *
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @param  string  $filePath
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadDocument(ServiceRequest $serviceRequest, string $filePath)
    {
        // Ensure the file path is part of the service request's uploaded documents
        $uploadedDocuments = $serviceRequest->uploaded_documents ?? [];
        $foundDocument = null;

        foreach ($uploadedDocuments as $doc) {
            if ($doc['path'] === $filePath) {
                $foundDocument = $doc;
                break;
            }
        }

        if (!$foundDocument) {
            return redirect()->back()->with('error', 'Documento non trovato o non associato a questa richiesta.');
        }

        if (!Storage::disk('private')->exists($filePath)) {
            return redirect()->back()->with('error', 'Il file non esiste nel sistema di archiviazione.');
        }

        return Storage::disk('private')->download($filePath, $foundDocument['original_name'] ?? basename($filePath));
    }
}