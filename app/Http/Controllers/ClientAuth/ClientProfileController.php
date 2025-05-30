<?php

namespace App\Http\Controllers\ClientAuth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ClientProfileController extends Controller
{
    /**
     * Display the client's profile form.
     */
    public function edit(Request $request)
    {
        return Inertia::render('client/Profile', [
            'auth' => [
                'user' => $request->user('client'),
            ],
        ]);
    }

    /**
     * Update the client's profile information.
     */
    public function update(Request $request)
    {
        $client = $request->user('client');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(Client::class)->ignore($client->id)],
            'type' => ['required', 'string', 'in:persona,empresa'],
            'type_document' => ['required', 'string', 'in:DNI,RUC,CE,PASAPORTE'],
            'identity_document' => ['required', 'string', 'max:20'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $client->fill($validated);
        $client->save();

        return redirect()->route('client.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the client's password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:client'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user('client')->update([
            'password' => $validated['password'],
        ]);

        return redirect()->route('client.profile')->with('success', 'Password updated successfully.');
    }
}
