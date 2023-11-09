<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'email' => 'hally@mail.com',
            'password' => 'admin',
            'username' => 'hally'
        ])
            ->assertStatus(201)
            ->assertJson([
                "data" => [
                    'email' => 'hally@mail.com',
                    'username' => 'hally'
                ]
            ]);
    }
    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            'email' => '',
            'password' => '',
            'username' => ''
        ])
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "email" => [
                        "The email field is required."
                    ],
                    "password" => [
                        "The password field is required."
                    ],
                    "username" => [
                        "The username field is required."
                    ]
                ]
            ]);
    }
    public function testRegisterAlreadyExist()
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'email' => 'hally@mail.com',
            'password' => 'admin',
            'username' => 'hally'
        ])
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "username" => [
                        "username already registered"
                    ],
                ]
            ]);
    }


    public function testLoginSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->post("/api/users/login", [
            "email" => "admin@mail.com",
            "password" => "admin"
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "email" => "admin@mail.com",
                    "username" => "admin"
                ]
            ]);

        $user = User::where("username", "admin")->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->post("/api/users/login", [
            "email" => "admin@gmail.com",
            "password" => "admin"
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed(UserSeeder::class);
        $this->post("/api/users/login", [
            "email" => "admin@mail.com",
            "password" => "ngademin"
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed(UserSeeder::class);
        $this->get("/api/users/current", [
            "Authorization" => "test"
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    'email' => "admin@mail.com",
                    "username" => "admin"
                ]
            ]);
    }

    public function testGetUnauthorize()
    {
        $this->seed(UserSeeder::class);
        $this->get("/api/users/current", [
            "Authorization" => ""
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed(UserSeeder::class);
        $this->get("/api/users/current", [
            "Authorization" => "salah"
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ]);
    }

    public function testUpdateEmailSuccess()
    {
        $this->seed(UserSeeder::class);
        $oldUser = User::where("email", "admin@mail.com")->first();
        $this->patch("/api/users/current", [
            "email" => "kurnia@mail.com"
        ], [
            "Authorization" => "test"
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "email" => "kurnia@mail.com",
                    "username" => "admin"
                ]
            ]);

        $newUser = User::where("email", "kurnia@mail.com")->first();
        self::assertNotEquals($oldUser->email, $newUser->email);
    }
    public function testUpdatePasswordSuccess()
    {
        $this->seed(UserSeeder::class);
        $oldUser = User::where("email", "admin@mail.com")->first();
        $this->patch("/api/users/current", [
            "password" => Hash::make("ngademin")
        ], [
            "Authorization" => "test"
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "email" => "admin@mail.com",
                    "username" => "admin"
                ]
            ]);

        $newUser = User::where("email", "admin@mail.com")->first();
        self::assertNotEquals($oldUser->password, $newUser->password);
    }
    public function testUpdateUsernameSuccess()
    {
        $this->seed(UserSeeder::class);
        $oldUser = User::where("email", "admin@mail.com")->first();
        $this->patch("/api/users/current", [
            "username" => "hally"
        ], [
            "Authorization" => "test"
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    "email" => "admin@mail.com",
                    "username" => "hally"
                ]
            ]);

        $newUser = User::where("email", "admin@mail.com")->first();
        self::assertNotEquals($oldUser->username, $newUser->username);
    }
    public function testUpdateFailed()
    {
        $this->seed(UserSeeder::class);
        $this->patch("/api/users/current", [
            "username" => "loremloremloremloremloremloremloremloremloremloremloremloremloremloremloremloremloremloremloremloremlorem"
        ], [
            "Authorization" => "test"
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "username" => [
                        "The username field must not be greater than 100 characters."
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed(UserSeeder::class);

        $this->delete(uri: "/api/users/logout", headers: [
            "Authorization" => "test"
        ])->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);

        $user = User::where("username", "admin")->first();
        self::assertNull($user->token);
    }
    public function testLogoutFailed()
    {
        $this->seed(UserSeeder::class);

        $this->delete(uri: "/api/users/logout", headers: [
            "Authorization" => "salah"
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ]);
    }
}

