<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log as Logging;

use App\Models\Version;

class HandleAppVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'version:update {--ver=}{--title=}{--note=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Customize App Version from Command Line';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $ver = $this->option('ver');
            $title = $this->option('title');
            $note = $this->option('note');

            if (empty($ver) || empty($title) || empty($note)) {
                echo "Required Parameter or Value Missing";
                return;
            }

            if (!in_array($ver, ['major', 'minor', 'patch'])) {
                echo "Version is Invalid! On Version Add major or minor or patch!";
                return;
            }

            if ($ver == 'major') {
                Artisan::call('version:major');
            } else if ($ver == 'minor') {
                Artisan::call('version:minor');
            } else if ($ver == 'patch') {
                Artisan::call('version:patch');
            }

            $pragmarx = new \PragmaRX\Version\Package\Version();

            $version = new Version;
            $version->version_name = $title;
            $version->description = $note;
            $version->major = $pragmarx->major();
            $version->minor = $pragmarx->minor();
            $version->patch = $pragmarx->patch();
            $version->full  = $pragmarx->format('footer-version');
            $version->save();

            echo "Version Update Successfully! Current Version is: " . $pragmarx->format('footer-version');

            return 0;
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
