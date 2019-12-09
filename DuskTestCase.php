<?php

namespace Tests;

use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Laravel\Dusk\Browser;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Make sure that web server is running.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        static $once;
        if ($once === null) {
            $once = true;
            $this->browse(function (Browser $browser) {
                $browser->visit('/');
                $this->assertNotEquals($browser->driver->getPageSource(),
                    '<html xmlns="http://www.w3.org/1999/xhtml"><head></head><body></body></html>',
                    'Web server not running on ' . config('app.url') . PHP_EOL .
                    'Use `php artisan serve --port=80` to run it first.');
            });
        }
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        // Use the following to use custom Chrome Driver:
        // static::useChromedriver($chromeDriverPath);

        static::startChromeDriver();
    }

    protected static $serverProcess;


    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--no-sandbox',
            '--window-size=1920,1080',
            '--disable-dev-shm-usage',
        ]);


        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )->setCapability('acceptInsecureCerts', true)
        );
    }
}
