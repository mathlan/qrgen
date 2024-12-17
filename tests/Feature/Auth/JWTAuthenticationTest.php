<?php

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('JWT Authentication', function () {
    // Utiliser RefreshDatabase pour réinitialiser la base de données entre les tests
    uses(RefreshDatabase::class);

    it('can register a new user', function () {
        // Simuler les événements pour vérifier l'événement Registered
        Event::fake();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message']);

        // Vérifier que l'utilisateur a été créé
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user'
        ]);

        // Vérifier que l'événement Registered a été déclenché
        Event::assertDispatched(Registered::class);
    });

    it('prevents registration with invalid data', function () {
        $invalidUserData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/register', $invalidUserData);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    });

    it('can login with valid credentials', function () {
        // Créer un utilisateur
        $user = User::create([
            'name' => 'Login User',
            'email' => 'login@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        $credentials = [
            'email' => 'login@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
    });

    it('prevents login with invalid credentials', function () {
        // Créer un utilisateur
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        $invalidCredentials = [
            'email' => 'existing@example.com',
            'password' => 'WrongPassword'
        ];

        $response = $this->postJson('/api/login', $invalidCredentials);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Unauthorized']);
    });

    it('can generate email verification link', function () {
        // Créer un utilisateur
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        // Générer un token JWT
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/verification-notification');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'verification_url',
            'status'
        ]);
    });

    it('can verify email with correct hash', function () {
        // Créer un utilisateur
        $user = User::create([
            'name' => 'Email Verify',
            'email' => 'emailverify@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        // Générer le hash de vérification
        $hash = sha1($user->email);

        $response = $this->getJson("/api/verify-email/{$user->id}/{$hash}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Email vérifié avec succès',
            'status' => 'verified'
        ]);
    });

    it('prevents email verification with incorrect hash', function () {
        // Créer un utilisateur
        $user = User::create([
            'name' => 'Email Verify',
            'email' => 'emailverify@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        // Générer un hash incorrect
        $incorrectHash = sha1('wrong-email');

        $response = $this->getJson("/api/verify-email/{$user->id}/{$incorrectHash}");

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Lien de vérification invalide',
            'status' => 'invalid_verification_link'
        ]);
    });

    it('can logout successfully', function () {
        // Créer un utilisateur
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        // Générer un token JWT
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Successfully logged out']);
    });
});
