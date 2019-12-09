<?php

namespace Tests\Integration\Events\UserEvent\Admin;

use Illuminate\Support\Carbon;
use MotionArray\Models\StaticData\UserEventLogTypes;
use MotionArray\Models\User;
use MotionArray\Models\UserEventLog;
use MotionArray\Repositories\AdminUserRepository;
use Tests\Integration\Events\UserEvent\Admin\Concerns\HelpsTestBasicUserEvents;
use Tests\TestCase;
use Tests\Support\RefreshAndSeedDatabase;

class UserDisabledByReachingDownloadLimitTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use HelpsTestBasicUserEvents;

    public function testBasic()
    {
        $userEventLogId = UserEventLogTypes::USER_DISABLED_BY_REACHING_DOWNLOAD_LIMIT_ID;

        $user = factory(User::class)->create();

        $repo = app(AdminUserRepository::class);
        $repo->setDisabledByReachingDownloadLimit($user);

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
        $expected = "User: {$userDescription} disabled by reaching download limit";

        $this->assertEquals($expected, $userEventLog->userEventMessage());
    }
}
