<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log as Logging;

use App\Services\FilevineService;

class HandleKillTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $service;
    private $noteId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(FilevineService $service, $noteId)
    {
        $this->service = $service;
        $this->noteId = $noteId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $res = $this->service->deleteTask($this->noteId);
            Logging::warning("FileVine Unassign/Delete Task Note ID: " . $this->noteId);

            // sleep to avoid the too many FV API calls.
            sleep(0.5);

        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
