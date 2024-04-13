<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotNull;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSucces()
    {
        $this->post('/api/users/register', [
            'username' => 'admin',
            'password' => 'admins',
            'name' => 'administrator'
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'username' => 'admin',
                    'name' => 'administrator'
                ]
            ]);
    }
    public function testRegisterFailed()
    {
        $this->post('/api/users/register', [
            'username' => '',
            'password' => '',
            'name' => ''
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => [
                        'The username field is required.'
                    ],
                    'password' => [
                        'The password field is required.'
                    ],
                    'name' => [
                        'The name field is required.'
                    ],
                ]
            ]);
    }

    public function testLoginRequire()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => '',
            'password' => '',
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => [
                        'The username field is required.'
                    ],
                    'password' => [
                        'The password field is required.'
                    ],
                ]
            ]);
    }
    public function testProfileSucces()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/profile', [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'admin',
                    'name' => 'administrator',
                ]
            ]);
    }
    public function testProfileInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/profile', [
            'Authorization' => 'tests'
        ])
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Unauthorized.'
                    ],
                ]
            ]);
    }
    public function testProfileUnauthorize()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/profile')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Unauthorized.'
                    ],
                ]
            ]);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'admin')->first();
        $this->post(
            '/api/users/update',
            [
                'name' => 'anonimus',
                'password_confirm' => 'admins',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'admin',
                    'name' => 'anonimus',
                ]
            ]);
        $newUser = User::where('username', 'admin')->first();
        self::assertNotEquals($oldUser->name, $newUser->name);
    }
    public function testUpdateNameFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->post(
            '/api/users/update',
            [
                'name' =>  'anonimus',
                'password_confirm' => 'admins1',

            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'password_confirm' => [
                        "Password Wrong."
                    ]
                ]
            ]);
    }
    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post(
            '/api/users/updatePassword',
            [
                'old_password' => 'admins',
                'new_password' => 'admin123',
                'repeat_password' => 'admin123',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'admin',
                    'name' => 'administrator',
                ]
            ]);
    }
    public function testUpdatePasswordRepeatFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->post(
            '/api/users/updatePassword',
            [
                'old_password' => 'admins',
                'new_password' => 'admin123',
                'repeat_password' => 'admin123s',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'repeat_password' => [
                        "The repeat password field must match new password."
                    ]
                ]
            ]);
    }
    public function testUpdatePasswordNotMatch()
    {
        $this->seed([UserSeeder::class]);
        $this->post(
            '/api/users/updatePassword',
            [
                'old_password' => 'adminsss',
                'new_password' => 'admin123',
                'repeat_password' => 'admin123',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'old_password' => [
                        "Password Wrong."
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->get(
            '/api/users/logout',
            headers: [
                'Authorization' => 'test'
            ]
        )->assertStatus(200)
            ->assertJson([
                'data' =>  true
            ]);

        $user = User::where('username', 'admin')->first();
        self::assertNull($user->token);
    }
    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->get(
            '/api/users/logout',
            headers: [
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
}
