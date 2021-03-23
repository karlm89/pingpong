<?php

namespace Tests\Feature\Jobs;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CheckWebsiteTest extends TestCase
{

    use RefreshDatabase;

    public function test_it_properly_checks_a_website()
    {
        $user = User::factory()->create();
        $site = $user->site()->save(Site::factory()->make());

        $this->assertEquals(0, $site->checks()->count());

        $job = new CheckWebsite($site);
        $job->handle();
        
        $check = $site->checks()->first();
        
        $this->assertEquals(200, $check->response_status);
        $this->assertNotNull($check->response_content);
        $this->assertTrue($check->elapased_time > 1);
    }
}
