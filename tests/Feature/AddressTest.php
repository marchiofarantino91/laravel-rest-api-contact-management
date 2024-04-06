<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\AddressSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->post(
            '/api/contacts/address/createStored',
            [
                "id_contact" => intval($contact->id),
                'province' => 'test',
                'city' => 'test',
                'street' => 'test',
                'country' => 'test',
                'postal_code' => '12345',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(201)->assertJson([
            'data' => [
                'province' => 'test',
                'city' => 'test',
                'street' => 'test',
                'country' => 'test',
                'postal_code' => '12345',
            ]
        ]);
    }
    public function testCreateError()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->post(
            '/api/contacts/address/createStored',
            [
                "id_contact" => intval($contact->id),
                'province' => 'test',
                'city' => 'test',
                'street' => 'test',
                // 'country' => '',
                'postal_code' => '12345',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)->assertJson([
            'errors' => [
                'country' => [
                    "The country field is required."
                ]
            ]
        ]);
    }
    public function testCreateContactNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->post(
            '/api/contacts/address/createStored',
            [
                "id_contact" => intval($contact->id) + 1,
                'province' => 'test',
                'city' => 'test',
                'street' => 'test',
                'country' => 'test',
                'postal_code' => '12345',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    "Not Found"
                ]
            ]
        ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->get(
            "/api/contacts/address/detail?id_contact=" . $address->contact_id . '&id_address=' . $address->id,
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => [
                'province' => 'test',
                'city' => 'test',
                'street' => 'test',
                'country' => 'test',
                'postal_code' => '12345',
            ]
        ]);
    }
    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->get(
            "/api/contacts/address/detail?id_contact=" . $address->contact_id + 1 . '&id_address=' . $address->id,
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    "Not Found"
                ]
            ]
        ]);
    }
    public function testGetError()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->get(
            "/api/contacts/address/detail?id_contact=" . $address->contact_id,
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)->assertJson([
            'errors' => [
                'message' => [
                    "id_contact or id_address required."
                ]
            ]
        ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->post(
            '/api/contacts/address/updateStored',
            [
                "id_contact" => intval($address->contact_id),
                "id_address" => intval($address->id),
                'province' => 'update',
                'city' => 'update',
                'street' => 'update',
                'country' => 'update',
                'postal_code' => '222222',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => [
                'province' => 'update',
                'city' => 'update',
                'street' => 'update',
                'country' => 'update',
                'postal_code' => '222222',
            ]
        ]);
    }
    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->post(
            '/api/contacts/address/updateStored',
            [
                "id_contact" => intval($address->contact_id),
                "id_address" => intval($address->id),
                'province' => 'update',
                'city' => 'update',
                'street' => 'update',
                // 'country' => '',
                'postal_code' => '222222',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)->assertJson([
            'errors' => [
                "country" => [
                    "The country field is required."
                ]
            ]
        ]);
    }
    public function testUpdateAddressIDRequired()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->post(
            '/api/contacts/address/updateStored',
            [
                "id_contact" => intval($address->contact_id),
                // "id_address" => intval($address->id) + 1,
                'province' => 'update',
                'city' => 'update',
                'street' => 'update',
                'country' => 'sss',
                'postal_code' => '222222',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)->assertJson([
            'errors' => [
                "id_address" => [
                    "The id address field is required."
                ]
            ]
        ]);
    }
    public function testUpdateAddressNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->post(
            '/api/contacts/address/updateStored',
            [
                "id_contact" => intval($address->contact_id),
                "id_address" => intval($address->id) + 1,
                'province' => 'update',
                'city' => 'update',
                'street' => 'update',
                'country' => 'sss',
                'postal_code' => '222222',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(404)->assertJson([
            'errors' => [
                "message" => [
                    "Not Found"
                ]
            ]
        ]);
    }
    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->post(
            '/api/contacts/address/deleteStored',
            [
                "id_contact" => intval($address->contact_id),
                "id_address" => intval($address->id),
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                "message" => [
                    "Data Deleted"
                ]
            ]);
    }
    public function testDeleteFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();
        $this->post(
            '/api/contacts/address/deleteStored',
            [
                // "id_contact" => intval($address->contact_id),
                // "id_address" => intval($address->id),
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    "id_contact" => [
                        "The id contact field is required."
                    ],
                    "id_address" => [
                        "The id address field is required."
                    ]
                ]
            ]);
    }
    public function testListSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            "/api/contacts/address/list?id_contact=" . $contact->id,
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)->assertJson([
            'data' => [
                [
                    'province' => 'test',
                    'city' => 'test',
                    'street' => 'test',
                    'country' => 'test',
                    'postal_code' => '12345',
                ]
            ]
        ]);
    }
    public function testListFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            "/api/contacts/address/list?id_contact=" . $contact->id + 1,
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(404)->assertJson([
            'errors' => [
                'message' => [
                    "Not Found"
                ]
            ]
        ]);
    }
}
