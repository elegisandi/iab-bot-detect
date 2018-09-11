<?php

namespace elegisandi\IABBotDetect\Commands;

use Illuminate\Console\Command;

/**
 * Class RefreshIABList
 * @package elegisandi\IABBotDetect\Commands
 */
class RefreshIABList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iab:refresh-list {--overwrite}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes IAB valid browser and bot lists.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            app('iab')->initialize($this->option('overwrite'));

            $this->info('IAB lists and cache are now updated.');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}