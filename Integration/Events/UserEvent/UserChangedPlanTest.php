<?php

namespace Tests\Integration\Events\UserEvent;

use Illuminate\Support\Carbon;
use MotionArray\Events\UserEvent\UserChangedPlan;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use MotionArray\Support\UserEvents\UserEventLogger;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserChangedPlanTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function testWithUserLoggedIn()
    {
        $user = factory(User::class)->create();

        $newPlanId = Plans::MONTHLY_UNLIMITED_2018_ID;
        $plan = (new Plans)->findOrFail($newPlanId);

        /** @var UserEventLogger $logger */
        $logger = app(UserEventLogger::class);
        $logger->log(new UserChangedPlan($user->id, $newPlanId));

        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where('user_event_log_type_id', UserEventLogTypes::USER_CHANGED_PLAN_ID)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $expected = [
            'new_plan' => array_only($plan, [
                'id',
                'billing_id',
                'name',
                'cycle',
            ])
        ];
        $this->assertInstanceOf(UserEventLog::class, $userEventLog, 'user event log not created in db');
        $this->assertEquals($expected, $userEventLog->data);
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $expected = "User: {$userDescription} changed plan to: '{$plan['name']}' (billing_id: '{$plan['billing_id']}', name: '{$plan['name']}', cycle: '{$plan['cycle']}')";
        $this->assertEquals($expected, $userEventLog->userEventMessage());
    }
}
