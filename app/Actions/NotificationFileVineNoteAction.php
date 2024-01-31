<?php

namespace App\Actions;

use App\Jobs\NotificationFileVineNoteJob;
use Illuminate\Support\Facades\Log as Logging;

class NotificationFileVineNoteAction
{

    /**
     * Create Note to Filevine
     */
    public function createNote($tenant_id, $fv_projectid, $fv_clientid, $fv_note_body)
    {
        try {
            $job_delay_time = now()->addSeconds(10);
            $job = (new NotificationFileVineNoteJob($tenant_id, $fv_projectid, $fv_clientid, $fv_note_body))->delay($job_delay_time);
            $jId = app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
        } catch (\Exception $ex) {
            Logging::warning($ex->getMessage());
        }
    }
}
