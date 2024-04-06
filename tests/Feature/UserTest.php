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

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'admin')->first();
        $this->post(
            '/api/users/update',
            [
                'password' => 'baru',
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
        $newUser = User::where('username', 'admin')->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }
    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'admin')->first();
        $this->post(
            '/api/users/update',
            [
                'name' => 'anonimus',
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
                'name' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Molestiae voluptas fugit quaerat cumque possimus eligendi exercitationem deleniti? Perspiciatis laborum ullam, quis consequatur quasi possimus labore vitae animi consequuntur delectus ipsa in fugiat eum obcaecati. Modi optio illum itaque, ab obcaecati voluptate similique, suscipit maiores, neque perspiciatis tenetur cum. Reprehenderit porro quos ratione expedita distinctio ex inventore ipsam, quasi nobis magni voluptatum enim, autem qui officia iusto eum officiis numquam itaque! Fuga, libero nulla. Quisquam a dignissimos eaque autem, ullam unde eligendi tenetur cumque inventore, esse ipsam assumenda, qui deleniti. A fugiat totam soluta, nostrum natus laborum magnam amet nam assumenda.',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'name' => [
                        'The name field must not be greater than 100 characters.'
                    ],
                ]
            ]);
    }
    public function testUpdatePasswordFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->post(
            '/api/users/update',
            [
                'password' => 'Lorem ipsum dolor, sit amet consectetur adipisicing elit. Molestiae voluptas fugit quaerat cumque possimus eligendi exercitationem deleniti? Perspiciatis laborum ullam, quis consequatur quasi possimus labore vitae animi consequuntur delectus ipsa in fugiat eum obcaecati. Modi optio illum itaque, ab obcaecati voluptate similique, suscipit maiores, neque perspiciatis tenetur cum. Reprehenderit porro quos ratione expedita distinctio ex inventore ipsam, quasi nobis magni voluptatum enim, autem qui officia iusto eum officiis numquam itaque! Fuga, libero nulla. Quisquam a dignissimos eaque autem, ullam unde eligendi tenetur cumque inventore, esse ipsam assumenda, qui deleniti. A fugiat totam soluta, nostrum natus laborum magnam amet nam assumenda.',
            ],
            [
                'Authorization' => 'test'
            ]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'password' => [
                        'The password field must not be greater than 50 characters.'
                    ],
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
