<?php

namespace Heath\BandwidthCheck;

use Heath\ClassLogger\ClassLogger;
use Heath\OauthClient\OauthClient;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class BandwidthCheck
{
    use ClassLogger;

    protected $url;
    protected $enabled;
    protected $client;
    protected $oauthClient;

    public function __construct()
    {
        $this->client = app(Client::class);
        $this->oauthClient = app(OauthClient::class);

        $this->enabled = config('bandwidth-check.enabled');
        $this->url = config('bandwidth-check.report_url');
    }

    public function run()
    {
        if(!$this->isEnabled()) {
            return;
        }

        $results = $this->results($this->performTest());

        $this->logResults($results);

        $this->report($results);

        return $results;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    protected function performTest()
    {
        $loop = 0;
        $hasValidResult = false;

        do {
            if($loop >= 5) {
                throw new \Exception('Exeeded speed test attempts');
            }

            $loop++;

            $result = exec('speedtest --format=json');

            if($result) {
                $json = json_decode($result);

                if(property_exists($json, 'download')) {
                    $hasValidResult = true;
                }
            }
        } while(! $hasValidResult);

        return $json;
    }

    protected function results($rawResults)
    {
        $downloadSpeed = round($rawResults->download->bandwidth * 0.000008, 3);

        return [
            'hostname' => gethostname(),
            'download' => [
                'duration' => $rawResults->download->elapsed / 1000, // Seconds
                'url' => $rawResults->result->url,
                'size' => round($rawResults->download->bytes / 1024 / 1024), // MB
                'speed' => $downloadSpeed, // Mbps
            ],
        ];
    }

    protected function report($results)
    {
        $this->client
            ->post(
                $this->uri(),
                $this->options($results)
            );
    }

    protected function uri()
    {
        return $this->url;
    }

    protected function options($results)
    {
        return $this->oauthClient->withToken([
            'json' => $results,
        ]);
    }

    protected function logResults($results)
    {
        $msg = sprintf(
            'Download speed was %sMbps (%s sec) for %sMB file.',
            Arr::get($results, 'download.speed'),
            Arr::get($results, 'download.duration'),
            Arr::get($results, 'download.size')
        );

        $this->logInfo($msg);
    }
}
