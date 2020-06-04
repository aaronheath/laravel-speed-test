<?php

namespace Tests;

use Heath\BandwidthCheck\BandwidthCheck;
use Heath\BandwidthCheck\BandwidthCheckJob;
use Illuminate\Support\Facades\Log;
use Mockery as m;
use TiMacDonald\Log\LogFake;

class BandwidthCheckJobTest extends TestCase
{
    /**
     * @test
     */
    public function job_performs_bandwidth_check()
    {
        Log::swap(new LogFake);

        $mock = m::mock(BandwidthCheck::class);

        $mock->shouldReceive('run')->once();

        (new BandwidthCheckJob)->handle($mock);

        Log::assertLogged('debug', function($message) {
            return str_contains($message, 'Starting.');
        });

        Log::assertLogged('debug', function($message) {
            return str_contains($message, 'Completed.');
        });
    }
}
