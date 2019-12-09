<?php

namespace Tests\Integration\Events\UserEvent;

use Illuminate\Support\Carbon;
use MotionArray\Events\UserEvent\UserDowngradedByPaymentFailure;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use MotionArray\Support\UserEvents\UserEventLogger;
use Tests\Integration\Events\UserEvent\Admin\Concerns\HelpsTestBasicUserEvents;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserDowngradedByPaymentFailureTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use HelpsTestBasicUserEvents;

    public function testWithUserLoggedIn()
    {

        $userEventLogId = UserEventLogTypes::USER_DOWNGRADED_BY_PAYMENT_FAILURE_ID;

        $user = factory(User::class)->create();

        //@TODO test actual calling code in MotionArray\Cashier\WebhookController
        /** @var UserEventLogger $repo */
        $repo = app(UserEventLogger::class);
        $repo->log(new UserDowngradedByPaymentFailure($user->id));

        /** @var UserEventLog $userEventLog */
        $userEventLog = UserEventLog::query()
            ->where('user_event_log_type_id', $userEventLogId)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $expected = [];

        $this->assertInstanceOf(UserEventLog::class, $userEventLog, 'user event log not created in db');
        $this->assertEquals($expected, $userEventLog->data);
        $this->assertInstanceOf(Carbon::class, $userEventLog->created_at);

        $userDescription = "'{$user->full_name}' (email: '{$user->email}', id: '{$user->id}')";
        $expected = "User: {$userDescription} downgraded by payment failure";

        $this->assertEquals($expected, $userEventLog->userEventMessage());


        $this->markTestIncomplete(
            'This test is not complete see @TODO above'
        );
    }
}
