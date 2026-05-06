<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     * Middleware 'auth' ensures only logged-in users can access these methods.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'endpoint' => 'required|url',
            'keys.p256dh' => 'required',
            'keys.auth' => 'required',
        ]);

        $user = Auth::user();
        $user->updatePushSubscription($request->endpoint, $request->keys['p256dh'], $request->keys['auth']);

        return response()->json(['success' => true], 201);
    }

    /**
     * Delete the specified push subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $this->validate($request, ['endpoint' => 'required|url']);

        $user = Auth::user();
        $user->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true]);
    }
}