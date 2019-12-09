<?php

namespace Tests\Integration\Events\UserEvent;

use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Repositories\AdminUserRepository;
use Tests\Integration\Events\UserEvent\Admin\Concerns\HelpsTestBasicUserEvents;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserDowngradedTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use HelpsTestBasicUserEvents;

    public function testWithUserLoggedIn()
    {
        $userEventLogId = UserEventLogTypes::USER_DOWNGRADED_ID;
        $action = 'downgraded';
        $this->assertBasicUserEvent($userEventLogId, $action, function ($user) {
            /** @var AdminUserRepository $repo */
            $repo = app(AdminUserRepository::class);
            $repo->downgrade($user->id);
        });
    }
}
