<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::registerBrowserMacros();
    }

    /**
     * Macro Browser bersama untuk seluruh tim.
     */
    protected static function registerBrowserMacros(): void
    {
        if (Browser::hasMacro('shot')) {
            return;
        }

        // Matikan validasi HTML5 (atribut "required", type="email", dll.) di semua
        // form pada halaman saat ini, supaya test validasi negatif bisa submit
        // dan memeriksa pesan error dari server. Panggil SETELAH visit().
        Browser::macro('disableHtmlValidation', function () {
            /** @var Browser $this */
            $this->script("document.querySelectorAll('form').forEach(function (f) { f.noValidate = true; });");

            return $this;
        });

        // Screenshot dengan nama rapi, boleh memakai subfolder anggota:
        // $browser->shot('Fakhri/TC.AdminProfile.001-step4')
        // → tests/Browser/screenshots/Fakhri/TC.AdminProfile.001-step4.png
        Browser::macro('shot', function (string $name) {
            /** @var Browser $this */
            $segments = array_filter(
                explode('/', str_replace('\\', '/', $name)),
                fn (string $part) => $part !== '' && $part !== '.' && $part !== '..'
            );

            $clean = implode('/', array_map(
                fn (string $part) => trim(preg_replace('/[^A-Za-z0-9._-]+/', '-', $part), '-'),
                $segments
            ));

            return $this->screenshot($clean !== '' ? $clean : 'screenshot');
        });
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
