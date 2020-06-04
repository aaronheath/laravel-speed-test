<?php

namespace Heath\BandwidthCheck;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class BandwidthCheckCommand extends Command
{
    protected $signature = 'bandwidth-check:run';
    protected $description = 'Performs bandwidth test and reports results to remote party';

    public function handle(BandwidthCheck $bandwidthCheck)
    {
        $this->info('Bandwidth Check');

        if(!$bandwidthCheck->isEnabled()) {
            $this->error('Unable to perform check as this feature is not enabled.');

            return 1;
        }

        $this->line('... running');

        $results = $bandwidthCheck->run();

        $this->info('Results');

        $this->table(
            ['Path', 'Filesize (MB)', 'Speed (Mbps)', 'Duration (sec)'],
            [
                [
                    Arr::get($results, 'download.size'),
                    Arr::get($results, 'download.speed'),
                    Arr::get($results, 'download.duration'),
                ]
            ]
        );
    }
}
