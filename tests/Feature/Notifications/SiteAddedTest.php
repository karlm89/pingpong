<?php

namespace Tests\Feature\Notifications;

use App\Models\Site;
use Tests\TestCase;
use App\Models\User;
use App\Notifications\SiteAdded;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SiteAddedTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_the_correct_message()
    {
        $user = User::factory()->create();
        $site = $user->sites()->save(Site::factory()->make());

        $notification = new SiteAdded($site);
        $message = $notification->toMail($user);

        $this->assertEquals('New Site Added to Your Account', $message->subject);
        $this->assertEquals("Hello {$user->name},", $message->introLines[0]);
        $this->assertEquals("We are just informing you that the site, {$site->url} was added to your account.", $message->introLines[1]);
        $this->assertEquals("See Site", $message->actionText);
        $this->assertEquals(route('sites.show', $site), $message->actionUrl);
    }

    public function test_it_only_send_via_mail()
    {
        $user = User::factory()->create();
        $site = $user->sites()->save(Site::factory()->make());
        $notification = new SiteAdded($site);

        $this->assertEquals(['mail'], $notification->via($user));
    }
}
