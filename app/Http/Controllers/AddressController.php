<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    
    /**
     * Get addresses for the authenticated client.
     */
    public function clientAddresses()
    {
        $client = Auth::guard('client')->user();
        $addresses = Address::where('client_id', $client->id)->get();
        
        return Inertia::render('client/Addresses', [
            'addresses' => $addresses
        ]);
    }
    
    /**
     * Store a new address for the authenticated client.
     */
    public function clientAddressStore(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);
        
        // If this is the default address, unset any other default address
        if ($request->is_default) {
            Address::where('client_id', $client->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $address = new Address();
        $address->client_id = $client->id;
        $address->address = $request->address;
        $address->city = $request->city;
        $address->state = $request->state;
        $address->postal_code = $request->postal_code;
        $address->country = $request->country;
        $address->is_default = $request->is_default ?? false;
        $address->save();
        
        return redirect()->route('client.addresses')
            ->with('success', 'Address added successfully!');
    }
    
    /**
     * Update an address for the authenticated client.
     */
    public function clientAddressUpdate(Request $request, string $id)
    {
        $client = Auth::guard('client')->user();
        
        $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:255',
            'is_default' => 'boolean',
        ]);
        
        $address = Address::where('client_id', $client->id)->findOrFail($id);
        
        // If this is the default address, unset any other default address
        if ($request->is_default && !$address->is_default) {
            Address::where('client_id', $client->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $address->address = $request->address;
        $address->city = $request->city;
        $address->state = $request->state;
        $address->postal_code = $request->postal_code;
        $address->country = $request->country;
        $address->is_default = $request->is_default ?? false;
        $address->save();
        
        return redirect()->route('client.addresses')
            ->with('success', 'Address updated successfully!');
    }
    
    /**
     * Delete an address for the authenticated client.
     */
    public function clientAddressDestroy(string $id)
    {
        $client = Auth::guard('client')->user();
        $address = Address::where('client_id', $client->id)->findOrFail($id);
        
        // Check if this is the only address or if it's the default address
        $addressCount = Address::where('client_id', $client->id)->count();
        
        if ($addressCount <= 1) {
            return redirect()->route('client.addresses')
                ->with('error', 'You must have at least one address.');
        }
        
        // If deleting the default address, set another address as default
        if ($address->is_default) {
            $newDefault = Address::where('client_id', $client->id)
                ->where('id', '!=', $id)
                ->first();
                
            if ($newDefault) {
                $newDefault->is_default = true;
                $newDefault->save();
            }
        }
        
        $address->delete();
        
        return redirect()->route('client.addresses')
            ->with('success', 'Address deleted successfully!');
    }
}
