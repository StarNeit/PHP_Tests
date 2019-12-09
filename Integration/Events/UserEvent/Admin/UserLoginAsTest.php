<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Repositories\AdminUserRepository;
use Tests\Integration\Events\UserEvent\Admin\Concerns\HelpsTestBasicUserEvents;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserLoginAsTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use HelpsTestBasicUserEvents;

    public function testWithUserLoggedIn()
    {
        $userEventLogId = UserEventLogTypes::USER_LOGIN_AS_ID;
        $action = 'login as';
        $this->assertBasicUserEvent($userEventLogId, $action, function ($user) {
            /** @var AdminUserRepository $repo */
            $repo = app(AdminUserRepository::class);
            $repo->loginAs($user->id);
        });
    }
}
