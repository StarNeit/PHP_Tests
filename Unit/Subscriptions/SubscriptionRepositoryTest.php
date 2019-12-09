<?php

namespace Tests\Unit\Subscriptions;

use MotionArray\Models\Plan;
use MotionArray\Models\StaticData\PaymentGateways;
use MotionArray\Models\StaticData\SubscriptionStatuses;
use MotionArray\Models\Subscription;
use MotionArray\Models\User;
use MotionArray\Repositories\SubscriptionRepository;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class SubscriptionRepositoryTest extends TestCase
{
    use RefreshAndSeedDatabase;

    public function test_create_subscription()
    {
        /** @var SubscriptionRepository $subscriptionService */
        $subscriptionService = app(SubscriptionRepository::class);

        $user = factory(User::class)->create();
        $subscription = $subscriptionService->createSubscription(
            $user,
            Plan::first(),
            PaymentGateways::STRIPE_ID,
            $providerCustomerId = str_random(),
            $providerSubscriptionId = str_random(),
            'buyer@motionarray.com',
            SubscriptionStatuses::STATUS_ACTIVE_ID,
            now(),
            now()->addMonth()
        );

        $this->assertInstanceOf(Subscription::class, $subscription);

        $activeSubscriptions = Subscription::active()->get();
        $this->assertCount(1, $activeSubscriptions);

        $subscriptionService->createSubscription(
            $user,
            Plan::first(),
            PaymentGateways::STRIPE_ID,
            $providerCustomerId = str_random(),
            $providerSubscriptionId = str_random(),
            'buyer@motionarray.com',
            SubscriptionStatuses::STATUS_ACTIVE_ID,
            now()->addMonth(1),
            now()->addMonth(2)
        );

        $activeSubscriptions = Subscription::active()->get();
        $this->assertCount(1, $activeSubscriptions);

    }

    public function test_user_active_subscription()
    {
        /** @var SubscriptionRepository $subscriptionService */
        $subscriptionService = app(SubscriptionRepository::class);

        $user = factory(User::class)->create();
        $subscription = $subscriptionService->createSubscription(
            $user,
            Plan::first(),
            PaymentGateways::STRIPE_ID,
            $providerCustomerId = str_random(),
            $providerSubscriptionId = str_random(),
            'buyer@motionarray.com',
            SubscriptionStatuses::STATUS_ACTIVE_ID,
            now(),
            now()->addMonth()
        );

        $subscription->refresh();
        $user->refresh();
        $this->assertEquals($subscription->toArray(), $user->activeSubscription->toArray());

        $subscription2 = $subscriptionService->createSubscription(
            $user,
            Plan::first(),
            PaymentGateways::STRIPE_ID,
            $providerCustomerId = str_random(),
            $providerSubscriptionId = str_random(),
            'buyer@motionarray.com',
            SubscriptionStatuses::STATUS_ACTIVE_ID,
            now()->addMonth(1),
            now()->addMonth(2)
        );
        $user->refresh();
        $this->assertEquals($subscription->toArray(), $user->activeSubscription->toArray(), "subscription2 is not user's active subscription");
    }
}
