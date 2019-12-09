<?php

namespace Tests\Integration\Services\FeatureFlag;

use MotionArray\Models\StaticData\FeatureFlags;
use MotionArray\Models\User;
use MotionArray\Repositories\FeatureFlagRepository;
use MotionArray\Services\FeatureFlag\FeatureFlagService;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class FeatureFlagServiceTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function testGloballyDisabled()
    {
        config()->set('feature-flags.global_state.STOCK_PHOTOS', false);

        $user = factory(User::class)->create();
        $service = app(FeatureFlagService::class);
        $repo = app(FeatureFlagRepository::class);

        $case = 'feature globally disabled and user disabled';
        $this->assertFalse($repo->checkUserHasFeatureFlag($user, FeatureFlags::STOCK_PHOTOS), $case);
        $this->assertFalse($service->check(FeatureFlags::STOCK_PHOTOS, $user), $case);
        $this->assertFalse($user->can('feature', FeatureFlags::STOCK_PHOTOS), $case);

        $repo->setUserFeatureFlag($user, FeatureFlags::STOCK_PHOTOS, true);

        $this->assertDatabaseHas('feature_flag_users', [
            'user_id' => $user->id,
            'feature_flag_id' => FeatureFlags::STOCK_PHOTOS_ID
        ]);
        $case = 'feature globally disabled and user enabled, set by slug';

        $this->assertTrue($repo->checkUserHasFeatureFlag($user, FeatureFlags::STOCK_PHOTOS), $case);
        $this->assertTrue($service->check(FeatureFlags::STOCK_PHOTOS, $user), $case);
        $this->assertTrue($user->can('feature', FeatureFlags::STOCK_PHOTOS), $case);

        $repo->setUserFeatureFlag($user, FeatureFlags::STOCK_PHOTOS_ID, false);

        $case = 'feature globally disabled and user disabled, set by id';
        $this->assertFalse($repo->checkUserHasFeatureFlag($user, FeatureFlags::STOCK_PHOTOS), $case);
        $this->assertFalse($service->check(FeatureFlags::STOCK_PHOTOS, $user), $case);
        $this->assertFalse($user->can('feature', FeatureFlags::STOCK_PHOTOS), $case);
    }

    public function testGloballyEnabled()
    {
        config()->set('feature-flags.global_state.STOCK_PHOTOS', true);

        $user = factory(User::class)->create();
        $service = app(FeatureFlagService::class);
        $repo = app(FeatureFlagRepository::class);

        $case = 'feature globally enabled and user disabled';
        $this->assertFalse($repo->checkUserHasFeatureFlag($user, FeatureFlags::STOCK_PHOTOS), $case);
        $this->assertTrue($service->check(FeatureFlags::STOCK_PHOTOS, $user), $case);
        $this->assertTrue($user->can('feature', FeatureFlags::STOCK_PHOTOS), $case);

        $repo->setUserFeatureFlag($user, FeatureFlags::STOCK_PHOTOS, true);

        $this->assertDatabaseHas('feature_flag_users', [
            'user_id' => $user->id,
            'feature_flag_id' => FeatureFlags::STOCK_PHOTOS_ID
        ]);
        $case = 'feature globally enabled and user enabled, set by slug';

        $this->assertTrue($repo->checkUserHasFeatureFlag($user, FeatureFlags::STOCK_PHOTOS), $case);
        $this->assertTrue($service->check(FeatureFlags::STOCK_PHOTOS, $user), $case);
        $this->assertTrue($user->can('feature', FeatureFlags::STOCK_PHOTOS), $case);

        $repo->setUserFeatureFlag($user, FeatureFlags::STOCK_PHOTOS_ID, false);

        $case = 'feature globally enabled and user disabled, set by id';
        $this->assertFalse($repo->checkUserHasFeatureFlag($user, FeatureFlags::STOCK_PHOTOS), $case);
        $this->assertTrue($service->check(FeatureFlags::STOCK_PHOTOS, $user), $case);
        $this->assertTrue($user->can('feature', FeatureFlags::STOCK_PHOTOS), $case);
    }
}
