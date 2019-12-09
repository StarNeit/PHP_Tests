<?php

namespace Tests\Integration\Http\Controllers\API\v2\AdobePanel;

use MotionArray\Models\User;
use MotionArray\Models\StaticData\Roles;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class MeControllerTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function test_user_detail_endpoint()
    {
        $user = $this->createAUser();
        $request = $this->actingAs($user, 'api')->get('/adobe-panel/api/me');
        $request->assertStatus(201);
        $request->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email
            ],
        ]);
    }

    private function createAUser()
    {
        $user = factory(User::class)->create();
        $user->roles()->attach(Roles::CUSTOMER_ID);

        return $user;
    }
}
