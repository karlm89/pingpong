<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_can_be_created()
    {
        $this->withoutExceptionHandling();

        // Create a user
        $user = User::factory()->create();

        // Make a post request
        $response = $this
            ->followingRedirects()
            ->actingAs($user)
            ->post(route('sites.store'), [
                'name' => 'Google',
                'url' => 'https://google.com',
        ]);

        // Make sure site exsists in the database
        $site = Site::first();
        $this->assertEquals('Google', $site->name);
        $this->assertDatabaseCount('sites', 1);
        // $this->assertEquals(1, Site::count());
        $this->assertEquals('https://google.com', $site->url );
        $this->assertNull($site->is_online);
        $this->assertEquals($user->id, $site->user->id);

        // See sites name on page
        $response->assertSeeText('Google');
    }

    public function test_only_auth_users_can_create_sites()
    {
        $this->withoutExceptionHandling();

        // Make a post request
        $response = $this
            ->followingRedirects()
            ->post(route('sites.store'),[
                'name' => 'Google',
                'url' => 'https://google.com',
        ]);

        // Make sure no site exsists in the database
        $site = Site::first();
        $this->assertDatabaseCount('sites', 0);

        $response->assertSeeText('Login');
        $this->assertEquals(route('login'), url()->current());
    }
}
