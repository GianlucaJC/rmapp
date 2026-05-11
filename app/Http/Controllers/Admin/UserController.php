<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Importa il modello User
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Hash; // Per hashare la password

class UserController extends Controller
{
    /**
     * Mostra la pagina di gestione degli utenti.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * Processa le richieste AJAX di DataTables per gli utenti.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersData(Request $request)
    {
        if ($request->ajax()) {
            // Seleziona tutti i campi necessari per la tabella e per la modale
            $data = User::select(['id', 'name', 'last_name', 'email', 'codice_fiscale', 'phone_number', 'contract_type', 'job_title', 'created_at']);
            // Aggiungi la relazione user_id per poterla usare nel render della colonna user_name
            $data->addSelect(['id as user_id']);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function(User $user) {
                    $editUrl = route('admin.users.edit', $user->id);
                    $actions = '<a href="'.$editUrl.'" class="btn btn-info btn-sm me-1"><i class="bi bi-pencil"></i> Modifica</a>';

                    if ($user->phone_number) {
                        $phoneNumber = preg_replace('/\D/', '', $user->phone_number);
                        if (strlen($phoneNumber) === 10) {
                            $phoneNumber = '39' . $phoneNumber;
                        } elseif (str_starts_with($phoneNumber, '0039')) {
                            $phoneNumber = substr($phoneNumber, 2);
                        }
                        $message = "Gentile {$user->name}, la contattiamo dagli uffici FILLEA CGIL.";
                        $encodedMessage = urlencode($message);
                        $whatsappUrl = "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
                        $actions .= ' <a href="'.$whatsappUrl.'" target="_blank" class="btn btn-success btn-sm ms-1" title="Invia messaggio WhatsApp"><i class="bi bi-whatsapp"></i></a>';
                    }

                    return $actions;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }

    /**
     * Mostra il form per la modifica di un utente specifico.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Aggiorna i dati di un utente specifico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'last_name' => 'required|string|max:255',
            'codice_fiscale' => 'required|string|size:16|unique:users,codice_fiscale,' . $user->id,
            'phone_number' => 'required|string|max:20',
            'contract_type' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->last_name = $request->last_name;
        $user->codice_fiscale = strtoupper($request->codice_fiscale);
        $user->phone_number = $request->phone_number;
        $user->contract_type = $request->contract_type;
        $user->job_title = $request->job_title;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Utente aggiornato con successo.');
    }

    /**
     * Get user details for modal display.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserDetails(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => [
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'codice_fiscale' => $user->codice_fiscale,
                'phone_number' => $user->phone_number,
                'contract_type' => $user->contract_type,
                'job_title' => $user->job_title,
                'created_at' => $user->created_at->format('d/m/Y H:i'), // Format here for display
            ]
        ]);
    }
}