<?php

namespace Tests;

use GuzzleHttp\Client;
use Heath\BandwidthCheck\BandwidthCheck;
use Heath\OauthClient\OauthClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Mockery as m;
use TiMacDonald\Log\LogFake;

class BandwidthCheckTest extends TestCase
{
    /**
     * @test
     */
    public function pings_changi_with_token()
    {
        Log::swap(new LogFake);

        $guzzle = m::mock(Client::class);

        $guzzle->shouldReceive('get')
            ->once()
            ->with('https://example.com/50MB.file');

        $externalCallback = function ($options) {
            $duration = Arr::get($options, 'json.download.duration');
            $speed = Arr::get($options, 'json.download.speed');
            $size = Arr::get($options, 'json.download.size');
            $url = Arr::get($options, 'json.download.url');

            if ($duration < 1.99 || $duration > 2.5) {
                return false;
            }

            if ($speed < 199 || $speed > 300) {
                return false;
            }

            if ($size != 50) {
                return false;
            }

            if ($url != 'https://example.com/50MB.file') {
                return false;
            }

            return true;
        };

        $guzzle->shouldReceive('post')
            ->once()
            ->with('https://example.com/api/bandwidth-check', m::on($externalCallback));

        app()->instance(Client::class, $guzzle);
        app()->instance(OauthClient::class, $this->mockOauthClient());

        $response = (new BandwidthCheck)->run();

        // Validate response

        $duration = Arr::get($response, 'download.duration');
        $speed = Arr::get($response, 'download.speed');
        $size = Arr::get($response, 'download.size');
        $url = Arr::get($response, 'download.url');

        $this->assertTrue($duration >= 1.99 && $duration < 2.5);
        $this->assertTrue($speed >= 199 && $speed <= 300);
        $this->assertEquals(50, $size);
        $this->assertEquals('https://example.com/50MB.file', $url);

        collect([
            'Download speed was',
            'Mbps (',
            ' sec) for ',
            ' file.'
        ])
            ->each(function($needle) {
                Log::assertLogged('info', function ($message, $context) use ($needle) {
                    return str_contains($message, $needle);
                });
            });
    }

    /**
     * @test
     */
    public function obeys_instruction_not_to_speed_test()
    {
        config(['bandwidth-check.enabled' => false]);

        $guzzle = m::mock(Client::class);

        $guzzle->shouldNotReceive('post');
        $guzzle->shouldNotReceive('get');

        app()->instance(Client::class, $guzzle);
        app()->instance(OauthClient::class, $this->mockOauthClient(false));

        (new BandwidthCheck)->run();
    }

    protected function mockOauthClient($shouldOccur = true)
    {
        if($shouldOccur) {
            return (new OauthClient)->seedAccessToken();
        }

        $oauthClient = m::mock(OauthClient::class);
        $oauthClient->shouldNotReceive('withToken');

        return $oauthClient;
    }
}
