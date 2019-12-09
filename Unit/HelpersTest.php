<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class HelpersTest extends TestCase
{
    public function testIsMotionArayDomain()
    {
        unset($_SERVER['HTTP_HOST']);
        $this->assertTrue(isMotionArrayDomain(), 'Should return true if no Host header available');

        $_SERVER['HTTP_HOST'] = 'non.existent.website';
        $this->assertFalse(isMotionArrayDomain(), 'Should return false for invalid Host');

        $_SERVER['HTTP_HOST'] = 'motionarray.com';
        $this->assertTrue(isMotionArrayDomain(), 'Should return true for motionarray.com');
        $_SERVER['HTTP_HOST'] = 'www.motionarray.com';
        $this->assertTrue(isMotionArrayDomain(), 'Should return true for www.motionarray.com');

        Config::set('app.host', 'www.myexampledomain.temp');
        $_SERVER['HTTP_HOST'] = 'www.myexampledomain.temp';
        $this->assertTrue(isMotionArrayDomain(), 'Should return true of host matches APP_HOST environment var');

    }

    public function testFormatMoney()
    {
        $map = [
            '10000.005' => '$10,000.00',
            '10000.006' => '$10,000.01',
            '1.99' => '$1.99',
            '7000000.10000001' => '$7,000,000.10'
        ];

        foreach ($map as $value => $expected) {
            $this->assertEquals($expected, formatMoney($value));
        }
    }

    public function testEnv()
    {
        $key = 'TESTING_ENV_SET_WITHOUT_DEFAULT';
        $value = 'test env value set with no default';
        putenv($key . '=' . $value);
        $this->assertEquals($value, _env($key));

        $key = 'TESTING_ENV_SET_WITH_DEFAULT';
        $value = 'test env value set with default';
        putenv($key . '=' . $value);
        $this->assertEquals($value, _env($key, 'unused default value'));

        // env not assigned but default provided
        $key = 'TESTING_NOT_SET_ENV_KEY_WITH_DEFAULT';
        $defaultValue = 'test default value';
        $this->assertEquals($defaultValue, _env($key, $defaultValue));

        // env not assigned and default not provided
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Environment variable '{$key}' not found.");
        _env($key);
    }
}
