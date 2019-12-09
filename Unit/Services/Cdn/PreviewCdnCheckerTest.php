<?php

namespace Tests\Unit\Services\Cdn;

use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use MotionArray\Services\Cdn\PreviewCdnChecker;
use MotionArray\Services\GeoIpReader;
use Tests\TestCase;

class PreviewCdnCheckerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCdnActiveAndCountryValid()
    {
        $validCountry = 'valid_country';
        $invalidCountry = 'invalid_country';
        config([
            'aws.previews_use_cdn' => true,
            'aws.previews_cdn_exceptions' => [$invalidCountry]
        ]);

        $testIp = '111.111.111.111';

        $this->mockTestCase($testIp, $validCountry);
        $checker = app(PreviewCdnChecker::class);

        $this->assertEquals(true, $checker->shouldUseCDN());
    }

    public function testCdnNotActive()
    {
        config(['aws.previews_use_cdn' => false]);
        $checker = app(PreviewCdnChecker::class);

        $this->assertEquals(false, $checker->shouldUseCDN());
    }

    public function testCdnActiveAndCountryInvalid()
    {
        $invalidCountry = 'invalid_country';
        config([
            'aws.previews_use_cdn' => true,
            'aws.previews_cdn_exceptions' => [$invalidCountry]
        ]);

        $testIp = '111.111.111.111';

        $this->mockTestCase($testIp, $invalidCountry);
        $checker = app(PreviewCdnChecker::class);

        $this->assertEquals(false, $checker->shouldUseCDN());
    }

    protected function mockTestCase($ip, $country)
    {
        app()->bind(Request::class, function () use ($ip) {
            return $this->mockRequestIp($ip);
        });

        app()->bind(GeoIpReader::class, function () use ($ip, $country) {
            return $this->mockGeoIp($ip, $country);
        });
    }

    protected function mockGeoIp($ip, $country)
    {
        $mock = Mockery::mock(GeoIpReader::class);
        $result = new Fluent();
        $result->country = (new Fluent);
        $result->country->name = $country;

        $mock->shouldReceive('city')
            ->with($ip)
            ->andReturn($result);

        return $mock;
    }

    protected function mockRequestIp($ip)
    {
        $mock = Mockery::mock(Request::class);
        $mock->shouldReceive('ip')
            ->andReturn($ip);

        return $mock;
    }
}
