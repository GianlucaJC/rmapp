<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct()
    {
        // Middleware per verificare il ruolo di superadmin per tutti i metodi in questo controller
        $this->middleware(function ($request, $next) {
            if (!session('admin_user.superadmin')) {
                abort(403, 'Azione non autorizzata.');
            }
            return $next($request);
        });
    }

    /**
     * Mostra un elenco delle news.
     */
    public function index()
    {
        $newsItems = News::orderBy('created_at', 'desc')->get();
        return view('admin.news.index', compact('newsItems'));
    }

    /**
     * Mostra il form per creare una nuova news.
     */
    public function create()
    {
        return view('admin.news.create');
    }

    /**
     * Salva una nuova news nel database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_suspended' => 'nullable|boolean',
        ]);

        $validated['is_suspended'] = $request->has('is_suspended');

        News::create($validated);

        return redirect()->route('admin.news.index')->with('success', 'News creata con successo.');
    }

    /**
     * Mostra il form per modificare una news esistente.
     */
    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    /**
     * Aggiorna una news esistente nel database.
     */
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_suspended' => 'nullable|boolean',
        ]);

        $validated['is_suspended'] = $request->has('is_suspended');

        $news->update($validated);

        return redirect()->route('admin.news.index')->with('success', 'News aggiornata con successo.');
    }

    /**
     * Rimuove una news dal database.
     */
    public function destroy(News $news)
    {
        $news->delete();
        return redirect()->route('admin.news.index')->with('success', 'News eliminata con successo.');
    }
}