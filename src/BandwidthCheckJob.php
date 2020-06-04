<?php

namespace Heath\BandwidthCheck;

use Heath\ClassLogger\ClassLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class BandwidthCheckJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, ClassLogger;

    public function handle(BandwidthCheck $bandwidthCheck)
    {
        $this->log('Starting.');

        $bandwidthCheck->run();

        $this->log('Completed.');
    }
}
