<?php

namespace Tests;

use Heath\BandwidthCheck\BandwidthCheckServiceProvider;
use Heath\OauthClient\OauthClientServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            BandwidthCheckServiceProvider::class,
            OauthClientServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'bandwidth-check',
            require __DIR__.'/../src/config.php'
        );

        config()->set(
            'oauth-client',
            require __DIR__.'/../vendor/aaronheath/oauth-client/src/config.php'
        );
    }
}
