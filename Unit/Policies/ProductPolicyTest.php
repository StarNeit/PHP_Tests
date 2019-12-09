<?php

namespace Tests\Unit\Policies;

use MotionArray\Models\Product;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\User;
use MotionArray\Policies\ProductPolicy;
use Tests\Concerns\SubscriptionTestsHelper;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use SubscriptionTestsHelper;

    public function setUp()
    {
        parent::setUp();
        $this->setUpSubscriptionTests();
    }

    public function test_not_confirmed_users_cannot_download_free_product()
    {
        $user = factory(User::class)->create([
            'confirmed' => 0,
        ]);
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);

        $this->assertFalse($user->isPayingMember());
        $this->assertFalse($user->can(ProductPolicy::downloadPackage, $product));
    }

    public function test_confirmed_users_can_download_free_product()
    {
        $user = factory(User::class)->create([
            'confirmed' => 1,
        ]);
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);

        $this->assertFalse($user->isPayingMember());
        $this->assertTrue($user->can(ProductPolicy::downloadPackage, $product));
    }

    public function test_confirmed_users_cannot_download_non_free_products()
    {
        $user = factory(User::class)->create([
            'confirmed' => 1,
        ]);
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);

        $this->assertFalse($user->isPayingMember());
        $this->assertFalse($user->can(ProductPolicy::downloadPackage, $product));
    }

    public function test_paid_users_can_download_non_free_products()
    {
        $user = $this->createUserWithSubscription(null, Plans::CYCLE_MONTHLY);
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);

        $this->assertTrue($user->isPayingMember());
        $this->assertTrue($user->can(ProductPolicy::downloadPackage, $product));
    }

    public function test_paid_users_can_download_free_products()
    {
        $user = $this->createUserWithSubscription(null, Plans::CYCLE_MONTHLY);
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);

        $this->assertTrue($user->isPayingMember());
        $this->assertTrue($user->can(ProductPolicy::downloadPackage, $product));
    }

    public function test_users_can_download_their_products()
    {
        $user = factory(User::class)->create([
            'confirmed' => 0,
        ]);
        $product = factory(Product::class)->create([
            'free' => 0,
            'seller_id' => $user->id,
        ]);

        $this->assertFalse($user->isPayingMember());
        $this->assertTrue($user->can(ProductPolicy::downloadPackage, $product));
    }
}
