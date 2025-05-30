<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function observations_attribute_handles_long_strings_and_persists_correctly()
    {
        // 1. Create dependent models
        $role = Role::create([
            'name' => 'Test Role',
            'description' => 'A role for testing purposes',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'dni' => '12345678', // Must be unique
            'password' => Hash::make('password'),
            'role_id' => $role->id,
        ]);

        $client = Client::create([
            'name' => 'Test Client',
            'email' => 'client@example.com', // Must be unique
            'password' => Hash::make('password'),
            'type' => 'persona',
            'identity_document' => '123456789', // Must be unique with type_document
            'type_document' => 'DNI',
            'phone' => '123456789',
        ]);

        $address = Address::create([
            'client_id' => $client->id,
            'address_full' => '123 Test Street',
            'district' => 'Test District',
            'province' => 'Test Province',
            'department' => 'Test Department',
            'postal_code' => '12345',
            'reference' => 'Near the test park',
            'is_main' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'address_id' => $address->id,
            'subtotal' => 100.00,
            'shipping_cost' => 10.00,
            'discount' => 5.00,
            'status' => 'pendiente',
        ]);

        // 2. Define the long string for observations
        $longObservationString = "This is a very long observation string.\n" .
            str_repeat("Lorem ipsum dolor sit amet, consectetur adipiscing elit. ", 50) . "\n" .
            "Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.\n" .
            str_repeat("Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ", 50) . "\n" .
            "Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";

        // 3. Create a Payment model instance
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'Efectivo',
            'payment_code' => 'PAYMENT_CODE_123',
            'document_type' => 'Ticket',
            'observations' => $longObservationString,
        ]);

        // 4. Retrieve the model from the database
        $retrievedPayment = Payment::find($payment->id);

        // 5. Assert that the retrieved observations attribute is a string
        $this->assertIsString($retrievedPayment->observations);

        // 6. Assert that the retrieved observations attribute matches the original long string value
        $this->assertEquals($longObservationString, $retrievedPayment->observations);
    }
}
