<?php

namespace Tests;

use Heath\BandwidthCheck\BandwidthCheck;
use Mockery as m;

class BandwidthCheckCommandTest extends TestCase
{
    /**
     * @test
     */
    public function can_perform_speed_test_via_cli()
    {
        $mockResult = [
            'download' => [
                'url' => 'https://example.com/download/url',
                'duration' => 10.3, // Seconds
                'size' => 50, // MB
                'speed' => 195.2, // Mbps
            ],
        ];

        $mock = m::mock(BandwidthCheck::class);

        $mock->shouldReceive('isEnabled')
            ->once()
            ->andReturnTrue();

        $mock->shouldReceive('run')
            ->once()
            ->andReturn($mockResult);

        app()->instance(BandwidthCheck::class, $mock);

        $this->artisan('bandwidth-check:run')
            ->expectsOutput('Bandwidth Check')
            ->expectsOutput('... running')
            ->expectsOutput('Results')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function will_output_error_if_performance_of_tests_is_not_enabled()
    {
        config()->set(['bandwidth-check.enabled' => false]);

        $this->artisan('bandwidth-check:run')
            ->expectsOutput('Bandwidth Check')
            ->expectsOutput('Unable to perform check as this feature is not enabled.')
            ->assertExitCode(1);
    }
}