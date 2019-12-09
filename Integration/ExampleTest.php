<?php

namespace Tests\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ExampleTest extends TestCase
{
    /**
     * An integration test example.
     * Note that these do not actually send a request to the server, and use inner Laravel
     * @return void
     */
    public function testBasicIntegration()
    {
        $response = $this->get('/browse');
        // $response->dump(); // Use to see the response for debugging purposes.
        $response->assertSee("Motion Array");
        $response->assertStatus(200);
    }
}
