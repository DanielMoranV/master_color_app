<?php

namespace App\Http\Controllers\ClientAuth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ClientRegisterController extends Controller
{
    /**
     * Display the client registration view.
     */
    public function create(): Response
    {
        return Inertia::render('client/auth/Register');
    }

    /**
     * Handle an incoming client registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type' => 'required|in:persona,empresa',
            'identity_document' => 'required|numeric',
            'type_document' => 'required|in:DNI,RUC,CE,PASAPORTE',
            'phone' => 'required|string|max:255',
        ]);

        $client = Client::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'identity_document' => $request->identity_document,
            'type_document' => $request->type_document,
            'phone' => $request->phone,
        ]);

        event(new Registered($client));

        Auth::guard('client')->login($client);

        return redirect(route('client.dashboard'));
    }
}
