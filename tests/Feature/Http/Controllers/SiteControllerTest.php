<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Site;
use App\Models\User;
use App\Notifications\SiteAdded;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_can_be_created_and_sends_notification()
    {
        Notification::fake();
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

        // Make sure notifications are sent
        Notification::assertSentTo($user, SiteAdded::class);
    }

    public function test_only_auth_users_can_create_sites()
    {
        Notification::fake();

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
        
        Notification::assertNothingSent();
    }

    public function test_it_redirects_a_user_if_they_try_to_add_a_duplicate()
    {
        Notification::fake();

        // Create a user
        $user = User::factory()->create();

        $site = $user->sites()->save(Site::factory()->make());

        // Make a post request
        $response = $this
            ->actingAs($user)
            ->post(route('sites.store'), [
                'name' => 'Google 2',
                'url' => $site->url,
        ]);

        // Make sure no site exsists in the database

        $response->assertRedirect(route('sites.show', $site));
        $response->assertSessionHasErrors(['url']);

        Notification::assertNothingSent();

        $this->assertDatabaseCount('sites', 1);
    }

    public function test_all_required_fields_are_present()
    {
        Notification::fake();

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

        Notification::assertNothingSent();
    }
}
