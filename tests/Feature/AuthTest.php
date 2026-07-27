<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('регистрирует нового пользователя и возвращает токен', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);

    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});

it('не регистрирует пользователя с занятым email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors('email');
});

it('логинит пользователя с верными данными', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@example.com',
        'password' => 'secret123',
    ]);

    $response->assertOk()->assertJsonStructure(['access_token']);
});

it('отклоняет вход с неверным паролем', function () {
    User::factory()->create([
        'email' => 'login2@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login2@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnprocessable();
});
