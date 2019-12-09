<?php

namespace Tests\Integration\Product;

use MotionArray\Models\Download;
use MotionArray\Models\Product;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\User;
use Tests\Concerns\SubscriptionTestsHelper;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

/**
 * Admins are using different url than paid/free users.
 * to avoid creating payout data for downloading product.
 *
 * In this test, we're ensuring it doesn't generate payout data and
 * no one else can use this endpoint except admins.
 */
class DownloadPackageByAdminTest extends TestCase
{

    use RefreshAndSeedDatabase;
    use SubscriptionTestsHelper;

    public function setUp()
    {
        parent::setUp();

        $this->setUpSubscriptionTests();
    }

    public function test_guests_cannot_reach_download_for_review_endpoint()
    {
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);
        $request = $this->get('/mabackend/products/'.$product->id.'/download');
        $request->assertStatus(302);
        $request->assertSee(url('/mabackend/login'));
    }

    public function test_free_users_cannot_reach_download_for_review_endpoint()
    {
        $user = factory(User::class)->create([
            'confirmed' => 1,
        ]);
        $this->assertFalse($user->isPayingMember());

        $product = factory(Product::class)->create([
            'free' => 1,
        ]);
        $request = $this->actingAs($user)
            ->get('/mabackend/products/'.$product->id.'/download');
        $request->assertStatus(302);
        $request->assertSee(url('/mabackend/login'));
    }

    public function test_paid_users_cannot_reach_download_for_review_endpoint()
    {
        $user = $this->createUserWithSubscription(null, Plans::CYCLE_MONTHLY);
        $this->assertTrue($user->isPayingMember());

        $product = factory(Product::class)->create([
            'free' => 1,
        ]);
        $request = $this->actingAs($user)
            ->get('/mabackend/products/'.$product->id.'/download');
        $request->assertStatus(302);
        $request->assertSee(url('/mabackend/login'));
    }

    public function test_if_admin_downloads_a_product_it_doesnt_generate_payout_data() {
        /** @var User $user */
        $user = factory(User::class)->create();
        $user->roles()->sync([Roles::ADMIN_ID]);
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());

        $downloadCount = $this->getDownloadCount($user);
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);
        $request = $this->actingAs($user)
            ->get('/mabackend/products/'.$product->id.'/download');
        $request->assertStatus(302);
        $request->assertDontSee(url('/mabackend/login'));
        $request->assertDontSee(url('/'));
        $request->assertSee($this->getCloudFrontDomain());
        $this->assertEquals($downloadCount, $this->getDownloadCount($user));

    }

    private function getDownloadCount(User $user): int
    {
        return Download::where('user_id', $user->id)
            ->count();
    }

    private function getCloudFrontDomain(): string
    {
        return config('aws.packages_cdn');
    }
}
