<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ContactTest extends TestCase
{
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post(
            '/api/contacts/createStored',
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@mail.com',
                'phone' => '01231231231',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(201)
            ->assertJson([
                'data' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john@mail.com',
                    'phone' => '01231231231',
                ]
            ]);
    }
    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->post(
            '/api/contacts/createStored',
            [
                'first_name' => '',
                'last_name' => 'Doe',
                'email' => 'johnmail.com',
                'phone' => '01231231231',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'first_name' => [
                        'The first name field is required.'
                    ],
                    'email' => [
                        'The email field must be a valid email address.'
                    ],
                ]
            ]);
    }
    public function testCreateUnauthorize()
    {
        $this->seed([UserSeeder::class]);
        $this->post(
            '/api/contacts/createStored',
            [
                'first_name' => '',
                'last_name' => 'Doe',
                'email' => 'johnmail.com',
                'phone' => '01231231231',
            ],
            [
                'Authorization' => 'tests'
            ]
        )->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Unauthorized.'
                    ],
                ]
            ]);
    }
    public function testGetContactSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            '/api/contacts/' . $contact->id,
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'johnmail.com',
                    'phone' => '01231231231',
                ]
            ]);
    }
    public function testGetContactNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            '/api/contacts/' . ($contact->id + 1),
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "Not Found"
                    ]
                ]
            ]);
    }
    public function testGetContactFromOtherUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->get(
            '/api/contacts/' . ($contact->id),
            [
                'Authorization' => 'test1'
            ]
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "Not Found"
                    ]
                ]
            ]);
    }

    public function testUpdateContactSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->post(
            '/api/contacts/updateStored',
            [
                'id' => $contact->id,
                'first_name' => 'Andrew',
                'last_name' => 'Tate',
                'email' => 'andrew@mail.com',
                'phone' => '012312312321',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'first_name' => 'Andrew',
                    'last_name' => 'Tate',
                    'email' => 'andrew@mail.com',
                    'phone' => '012312312321',
                ]
            ]);
    }
    public function testUpdateContactFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->post(
            '/api/contacts/updateStored',
            [
                // 'id' =>  '',
                'first_name' => 'Andrew',
                'last_name' => 'Tate',
                'email' => 'andrew@mail.com',
                'phone' => '012312312321',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'id' => [
                        "The id field is required."
                    ]
                ]
            ]);
    }
    public function testDeleteContactSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->post(
            '/api/contacts/deleteStored',
            [
                'id' => $contact->id,
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
    public function testDeleteContactFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $this->post(
            '/api/contacts/deleteStored',
            [
                // 'id' =>  '',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'id' => [
                        "The id field is required."
                    ]
                ]
            ]);
    }
    public function testDeleteContactNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();
        $this->post(
            '/api/contacts/deleteStored',
            [
                'id' => ($contact->id + 1)
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "Not Found"
                    ]
                ]
            ]);
    }
    public function testDeleteContactFromOtherUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();
        $this->post(
            '/api/contacts/deleteStored',
            [
                'id' =>  $contact->id,
            ],
            [
                'Authorization' => 'test1'
            ]
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        "Not Found"
                    ]
                ]
            ]);
    }

    public function testSearchByFirstName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get(
            "/api/contacts?name=first",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->json();
        self::assertEquals(10, count($response["data"]));
    }
    public function testSearchByFirstLastName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get(
            "/api/contacts?name=last",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->json();
        self::assertEquals(10, count($response["data"]));
    }
    public function testSearchByEmail()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get(
            "/api/contacts?email=test",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->json();
        self::assertEquals(10, count($response["data"]));
    }
    public function testSearchByPhone()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get(
            "/api/contacts?phone=0123123",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->json();
        self::assertEquals(10, count($response["data"]));
    }
    public function testSearchNotFound()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get(
            "/api/contacts?phone=0123123222",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->json();
        self::assertEquals(0, count($response["data"]));
    }
    public function testSearchPagination()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $response = $this->get(
            "/api/contacts?perPage=5&page=2",
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->json();
        self::assertEquals(5, count($response["data"]));
        self::assertEquals(20, $response["total"]);
        self::assertEquals(2, $response["current_page"]);
    }
}
