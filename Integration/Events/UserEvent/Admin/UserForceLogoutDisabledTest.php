<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Repositories\AdminUserRepository;
use Tests\Integration\Events\UserEvent\Admin\Concerns\HelpsTestBasicUserEvents;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserForceLogoutDisabledTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use HelpsTestBasicUserEvents;

    public function testWithUserLoggedIn()
    {
        $userEventLogId = UserEventLogTypes::USER_FORCE_LOGOUT_DISABLED_ID;
        $action = 'force logout disabled';
        $this->assertBasicUserEvent($userEventLogId, $action, function ($user) {
            /** @var AdminUserRepository $repo */
            $repo = app(AdminUserRepository::class);
            $repo->cancelForceLogOut($user->id);
        });
    }
}
