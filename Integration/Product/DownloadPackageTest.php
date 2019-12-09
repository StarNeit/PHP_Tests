<?php

namespace Tests\Integration\Product;

use Mockery;
use MotionArray\Models\Product;
use MotionArray\Models\StaticData\Plans;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Models\StaticData\ProductStatuses;
use MotionArray\Models\User;
use ReCaptcha\Response;
use Tests\Concerns\SubscriptionTestsHelper;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;

class DownloadPackageTest extends TestCase
{
    use RefreshAndSeedDatabase;
    use SubscriptionTestsHelper;

    public function setUp()
    {
        parent::setUp();

        $this->setUpSubscriptionTests();
    }

    public function test_guests_cannot_download_a_package_even_if_its_free()
    {
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);

//        // ReCaptcha fail scenario
//        $request = $this->get('/account/download/'.$product->id);
//        //user has redirected back because of captcha
//        $request->assertStatus(302);
//        $request->assertSee(url('/'));

        $this->mockRecaptcha();
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);
        $request = $this->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        $request->assertSee(url('/'));
    }

    public function test_not_confirmed_users_cannot_download_free_product()
    {
        $user = factory(User::class)->create([
            'confirmed' => 0,
        ]);
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);

        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
        $this->assertFalse($user->isConfirmed());

//        // ReCaptcha fail scenario
//        $request = $this->actingAs($user)
//            ->get('/account/download/'.$product->id);
//        //user has redirected back because of captcha
//        $request->assertStatus(302);
//        $request->assertSee(url('/'));

        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        // user is not confirmed their email, so can't download
        $request->assertStatus(302);
        $request->assertSee(url('/'));
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
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
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

//        // ReCaptcha fail scenario
//        $request = $this->actingAs($user)
//            ->get('/account/download/'.$product->id);
//        $request->assertStatus(302);
//        //user has redirected back because of captcha
//        $request->assertSee(url('/'));

        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        $request->assertDontSee(url('/')); //it should redirect to amazon
        $request->assertSee($this->getS3Domain()); //it should redirect to s3 BECAUSE user is not paying member
        $this->assertDatabaseHas('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_confirmed_users_cannot_download_non_free_products()
    {
        $user = factory(User::class)->create([
            'confirmed' => 1,
        ]);
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);

        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
        $this->assertFalse($user->isPayingMember());

//        // ReCaptcha fail scenario
//        $request = $this->actingAs($user)
//            ->get('/account/download/'.$product->id);
//        //user has redirected back because of captcha
//        $request->assertStatus(302);

        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        $request->assertSee(url('/'));
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_paid_users_can_download_non_free_products()
    {
        $user = $this->createUserWithSubscription(null, Plans::CYCLE_MONTHLY);
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);

        $this->assertTrue($user->isPayingMember());
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

//        // ReCaptcha fail scenario
//        $request = $this->actingAs($user)
//            ->get('/account/download/'.$product->id);
//        $request->assertStatus(302);
//        //user has redirected back because of captcha
//        $request->assertSee(url('/'));

        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id.'?g-recaptcha-response=token');
        $request->assertStatus(302);
        $request->assertDontSee(url('/')); //it should redirect to cloudfront
        $request->assertSee($this->getCloudFrontDomain()); //it should redirect to cloudfront BECAUSE user is paying member
        $this->assertDatabaseHas('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_paid_users_can_download_free_products()
    {
        $user = $this->createUserWithSubscription(null, Plans::CYCLE_MONTHLY);
        $product = factory(Product::class)->create([
            'free' => 1,
        ]);

        $this->assertTrue($user->isPayingMember());
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

//        // ReCaptcha fail scenario
//        $request = $this->actingAs($user)
//            ->get('/account/download/'.$product->id);
//        $request->assertStatus(302);
//        //user has redirected back because of captcha
//        $request->assertSee(url('/'));

        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        $request->assertDontSee(url('/')); //it should redirect to cloudfront
        $request->assertSee($this->getCloudFrontDomain()); //it should redirect to cloudfront BECAUSE user is paying member
        $this->assertDatabaseHas('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_paid_users_cannot_download_not_published_products()
    {
        $user = $this->createUserWithSubscription(null, Plans::CYCLE_MONTHLY);
        $product = factory(Product::class)->create([
            'free' => 1,
            'product_status_id' => ProductStatuses::UNPUBLISHED_ID,
        ]);

//        // ReCaptcha fail scenario
//        $this->assertTrue($user->isPayingMember());
//        $request = $this->actingAs($user)
//            ->get('/account/download/'.$product->id);
//        //user has redirected back because of captcha
//        $request->assertStatus(302);

        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        $request->assertSee(url('/')); //it should redirect to homepage, because product is not active
        $request->assertDontSee($this->getCloudFrontDomain());
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
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

//        // ReCaptcha fail scenario
//        $this->assertFalse($user->isPayingMember());
//        $request = $this->actingAs($user)
//            ->get('/account/download/'.$product->id);
//        //user has redirected back because of captcha
//        $request->assertStatus(302);

        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        $request->assertDontSee(url('/')); //it should redirect to s3
        $request->assertSee($this->getS3Domain()); //it should redirect to s3 BECAUSE user is paying member
        $this->assertDatabaseHas('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_admins_can_download_non_free_products_and_it_wont_affect_payouts()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $user->roles()->attach(Roles::ADMIN_ID);
        $user->refresh();
        $this->assertTrue($user->isAdmin());
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);

        $this->assertFalse($user->isPayingMember());
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        $request->assertDontSee(url('/')); //it should redirect to cloudfront
        $request->assertSee($this->getCloudFrontDomain()); //it should redirect to cloudfront BECAUSE user is ADMIN
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_users_cannot_download_same_product_immediately()
    {
        $user = $this->createUserWithSubscription(null, Plans::CYCLE_MONTHLY);
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);

        $this->assertTrue($user->isPayingMember());
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

//        // ReCaptcha fail scenario
//        $request = $this->actingAs($user)
//            ->get('/account/download/'.$product->id);
//        //user has redirected back because of captcha
//        $request->assertStatus(302);
//        $request->assertSee(url('/'));

        // first download
        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        //it should redirect to cloudfront
        $request->assertDontSee(url('/'));
        //it should redirect to cloudfront BECAUSE user is paying member
        $request->assertSee($this->getCloudFrontDomain());
        $this->assertDatabaseHas('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        sleep(1);

        // second download
        $request = $this->actingAs($user)
            ->get('/account/download/'.$product->id);
        $request->assertStatus(302);
        // it should be back own page not cloudfront.
        $request->assertSee(url('/account/download/' . $product->id));
    }

    public function test_users_can_download_same_product_after_time_limit()
    {
        $intervalTime = 6;
        $user = $this->createUserWithSubscription(null, Plans::CYCLE_MONTHLY);
        $product = factory(Product::class)->create([
            'free' => 0,
        ]);

        $this->assertTrue($user->isPayingMember());
        $this->assertDatabaseMissing('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        // first download
//        // ReCaptcha fail scenario
//        $request = $this->actingAs($user)
//            ->get('/account/download/' . $product->id);
//        //user has redirected back because of captcha
//        $request->assertStatus(302);
//        $request->assertSee(url('/'));

        $this->mockRecaptcha();
        $request = $this->actingAs($user)
            ->get('/account/download/' . $product->id);
        $request->assertStatus(302);
        //it should redirect to cloudfront
        $request->assertDontSee(url('/'));
        //it should redirect to cloudfront BECAUSE user is paying member
        $request->assertSee($this->getCloudFrontDomain());
        $this->assertDatabaseHas('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        sleep($intervalTime);

        // second download
        $request = $this->actingAs($user)
            ->get('/account/download/' . $product->id);
        $request->assertStatus(302);
        //it should redirect to cloudfront
        $request->assertDontSee(url('/'));
        //it should redirect to cloudfront BECAUSE user is paying member
        $request->assertSee($this->getCloudFrontDomain());
        $this->assertDatabaseHas('downloads', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    private function getS3Domain(): string
    {
        return config('aws.packages_bucket').'.s3.amazonaws.com';
    }

    private function getCloudFrontDomain(): string
    {
        return config('aws.packages_cdn');
    }

    private function mockRecaptcha()
    {
        app()->bind(\ReCaptcha\ReCaptcha::class, function () {
            $mockResponse = Mockery::mock(\ReCaptcha\Response::class);
            $mockResponse->shouldReceive('isSuccess')
                ->andReturn(true);

            $mock = Mockery::mock(\ReCaptcha\ReCaptcha::class);
            $mock->shouldReceive('verify')
                ->andReturn($mockResponse);

            return $mock;
        });
    }
}
