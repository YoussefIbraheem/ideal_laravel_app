<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_are_rate_limited_after_10_requests_per_minute()
    {
        // Test that an unauthenticated user gets rate limited after 10 requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api');
            $response->assertStatus(200);
        }

        // 11th request should be rate limited
        $response = $this->getJson('/api');
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function authenticated_users_have_higher_rate_limit()
    {
        $user = User::factory()->create();

        // Test that an authenticated user can make 60 requests per minute
        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($user)->getJson('/api/tasks');
            $response->assertStatus(200);
        }

        // 61st request should be rate limited
        $response = $this->actingAs($user)->getJson('/api/tasks');
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function rate_limit_is_per_ip_for_unauthenticated_users()
    {
        // First IP makes 10 requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])
                ->getJson('/api');
            $response->assertStatus(200);
        }

        // Different IP can still make requests
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.2'])
            ->getJson('/api');
        $response->assertStatus(200);
    }
}
