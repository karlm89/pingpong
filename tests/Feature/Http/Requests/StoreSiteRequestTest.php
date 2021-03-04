<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\StoreSiteRequest;
// use App\Rules\ValidProtocol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreSiteRequestTest extends TestCase
{
    public function test_request_has_correct_rules()
    {
        $request = new StoreSiteRequest;
        $rules = [
            'name' => ['required', 'string'],
            'url' => ['required', 'string']
        ];

        $this->assertEquals($rules, $request->rules());
    }

    public function test_users_are_authorized()
    {
        $request = new StoreSiteRequest;

        $this->assertTrue($request->authorize());
    }
}
