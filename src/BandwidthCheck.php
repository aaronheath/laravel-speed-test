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
    protected $downloadSize; // MB
    protected $downloadUrl;

    public function __construct()
    {
        $this->client = app(Client::class);
        $this->oauthClient = app(OauthClient::class);

        $this->enabled = config('bandwidth-check.enabled');
        $this->url = config('bandwidth-check.report_url');
        $this->downloadSize = config('bandwidth-check.download_filesize');
        $this->downloadUrl = config('bandwidth-check.download_url');
    }

    public function run()
    {
        if(!$this->isEnabled()) {
            return;
        }

        $results = $this->results($this->download());

        $this->logResults($results);

        $this->report($results);

        return $results;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    protected function download()
    {
        $start = microtime(true);

        if(config('app.env') == 'testing') {
            usleep(2000000);
        }

        $this->client->get($this->downloadUrl);

        $end = microtime(true);

        return round($end - $start, 3);
    }

    protected function results($downloadDuration)
    {
        $downloadSpeed = $this->toMbps($this->downloadSize, $downloadDuration);

        return [
            'download' => [
                'duration' => $downloadDuration, // Seconds
                'url' => $this->downloadUrl,
                'size' => $this->downloadSize, // MB
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

    protected function toMbps($size, $duration)
    {
        return round(8 * ($size / $duration), 3);
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
            $this->downloadSize
        );

        $this->logInfo($msg);
    }
}
