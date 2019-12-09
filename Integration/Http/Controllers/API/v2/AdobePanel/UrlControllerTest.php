<?php

namespace Tests\Integration\Http\Controllers\API\v2\AdobePanel;

use Illuminate\Support\Carbon;
use MotionArray\Models\User;
use MotionArray\Models\StaticData\Roles;
use MotionArray\Services\AdobePanel\AdobePanelUrlService;
use Tests\Support\RefreshAndSeedDatabase;
use Tests\TestCase;
use App;
use Mockery;

class UrlControllerTest extends TestCase
{
    use RefreshAndSeedDatabase;

    /**
     * @var Mockery\Mock
     */
    protected $mockedAdobePanelService;

    public function test_site_urls_endpoint()
    {
        $appUrl = config('app.url');
        $registerUrl = route('pricing');
        $tosUrl = route('terms-of-service');
        $adobeUrls = [
            'register' => "{$registerUrl}",
            'details' => "{$appUrl}/account/details",
            'collections' => "{$appUrl}/account/collections",
            'upgrade' => "{$appUrl}/account/upgrade",
            'contact' => "{$appUrl}/contact",
            'terms_of_service' => $tosUrl
        ];

        $request = $this->get('/adobe-panel/api/site-urls');
        $request->assertStatus(200);
        $request->assertExactJson($adobeUrls);
    }

    public function test_signed_urls_endpoint()
    {
        $user = $this->createAUser();

        $this->mockAdobePanelUrlService();
        $this->addExpectedUrl('signed-details', $user);
        $this->addExpectedUrl('signed-upgrade', $user);
        $this->addExpectedUrl('signed-collections', $user);
        $this->addExpectedUrl('signed-requests', $user);

        $request = $this->actingAs($user, 'api')->get('/adobe-panel/api/signed-urls');
        $request->assertStatus(200);
        $actual = $request->json();
        $actualExpiry = Carbon::parse($actual['expireDate']['date']);

        $this->assertTrue($actualExpiry->gt(Carbon::now()));

        $expected = [
            'details' => 'signed-details--signed-by-mock',
            'upgrade' => 'signed-upgrade--signed-by-mock',
            'collections' => 'signed-collections--signed-by-mock',
            'requests' => 'signed-requests--signed-by-mock',
        ];
        $this->assertEquals($expected, $actual['urls']);
    }

    public function test_details_page_by_signed_url()
    {
        $user = $this->createAUser();
        $urls = $this->getSignedUrls($user);
        $url = $urls['details'];

        $this->get($url)
            ->assertStatus(200)
            ->assertViewIs('site.account.details')
            ->assertSeeText('Personal Details');

        $this->assertAuthenticatedAs($user);
    }

    public function test_upgrade_page_by_signed_url()
    {
        $user = $this->createAUser();
        $urls = $this->getSignedUrls($user);
        $url = $urls['upgrade'];

        $this->get($url)
            ->assertStatus(200)
            ->assertViewIs('site.account.upgrade')
            ->assertSeeText('Upgrade Now &amp; Get Unlimited Downloads');

        $this->assertAuthenticatedAs($user);
    }

    public function test_collections_page_by_signed_url()
    {
        $user = $this->createAUser();
        $this->createCollection($user->id);
        $urls = $this->getSignedUrls($user);
        $url = $urls['collections'];

        $this->get($url)
            ->assertStatus(200)
            ->assertViewIs('site.account.collections')
            ->assertSeeText('My Collections');

        $this->assertAuthenticatedAs($user);
    }

    private function createCollection($userId)
    {
        $product = factory(\MotionArray\Models\User::class)->create();
        $collection = factory(\MotionArray\Models\Collection::class)->create(
            ['user_id' => $userId]
        );
        $product->collections()->save($collection);
    }

    private function getSignedUrls($user)
    {
        $urlNames = [
            'details',
            'upgrade',
            'collections',
            'requests'
        ];
        $routeNames = [];
        foreach ($urlNames as $urlName) {
            $routeName = "signed-{$urlName}";
            $routeNames[$urlName] = $routeName;
        }
        $data = app(AdobePanelUrlService::class)->signedUrls($user, $routeNames);

        return $data['urls'];
    }

    private function createAUser()
    {
        $user = factory(User::class)->create();
        $user->roles()->attach(Roles::CUSTOMER_ID);

        return $user;
    }

    private function mockAdobePanelUrlService()
    {
        $this->mockedAdobePanelService = Mockery::mock(AdobePanelUrlService::class)->makePartial()->shouldAllowMockingProtectedMethods();

        App::bind(AdobePanelUrlService::class, function () {
            return $this->mockedAdobePanelService;
        });
    }

    private function addExpectedUrl(string $routeName, User $user)
    {
        $this->mockedAdobePanelService->shouldReceive('createSignedUrl')
            ->withArgs(function (string $providedRouteName, Carbon $expireDate, array $arguments) use (
                $user,
                $routeName
            ) {
                if (!$expireDate->gt(Carbon::now())) {
                    return false;
                }
                $this->assertEquals($routeName, $providedRouteName);
                $this->assertEquals(['user' => $user->id], $arguments);
                return true;
            })
            ->once()
            ->andReturn($routeName . '--signed-by-mock');
    }
}
