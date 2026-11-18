<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\DuskTestCase;

/**
 * Smoke test: memastikan server, ChromeDriver, dan database Dusk siap.
 */
#[Group('smoke')]
class ExampleTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_landing_page_dapat_diakses(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('AbisLulus')
                ->shot('smoke/landing-page');
        });
    }
}
