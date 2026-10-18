<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patchJson('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '08123456789',
            ]);

        $response->assertOk()->assertJson(['success' => true]);

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('08123456789', $user->phone);
    }

    public function test_profile_update_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patchJson('/profile', [
                'name' => $user->name,
                'email' => 'taken@example.com',
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }
}
