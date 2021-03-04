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

        // Make sure we are on the right site URL
        $this->assertEquals(route('sites.show', $site), url()->current());
    }

    public function test_only_auth_users_can_create_sites()
    {
        // Make a post request
        $response = $this
            ->followingRedirects()
            ->post(route('sites.store'), [
                'name' => 'Google',
                'url' => 'https://google.com',
        ]);

        // Make sure no site exsists in the database
        $this->assertDatabaseCount('sites', 0);

        $response->assertSeeText('Log in');
        $this->assertEquals(route('login'), url()->current());
    }

    public function test_all_required_fields_are_present()
    {
        // Create a user
        $user = User::factory()->create();

        // Make a post request
        $response = $this
            ->actingAs($user)
            ->post(route('sites.store'), [
                'name' => '',
                'url' => '',
        ]);

        // Make sure no site exsists in the database
        $this->assertDatabaseCount('sites', 0);

        $response->assertSessionHasErrors(['name', 'url']);
    }
}
