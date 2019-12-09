<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Repositories\AdminUserRepository;
use Tests\Integration\Events\UserEvent\Admin\Concerns\HelpsTestBasicUserEvents;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserChangedSubscriptionToMonthlyTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use HelpsTestBasicUserEvents;

    public function testWithUserLoggedIn()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.' .
            '@TODO abstract, encapsulate, and mock subscription services to allow this test to run'
        );

        $userEventLogId = UserEventLogTypes::USER_CHANGED_SUBSCRIPTION_TO_MONTHLY_ID;
        $action = 'downgraded';
        $this->assertBasicUserEvent($userEventLogId, $action, function ($user) {
            /** @var AdminUserRepository $repo */
            $repo = app(AdminUserRepository::class);
            $repo->changeSubscriptionToMonthly($user);
        });
    }
}
