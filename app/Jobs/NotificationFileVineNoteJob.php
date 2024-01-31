<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log as Logging;
use App\Models\Tenant;
use App\Services\FilevineService;
use Illuminate\Support\Carbon;

class NotificationFileVineNoteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tenant_id;
    private $fv_projectid;
    private $fv_clientid;
    private $fv_note_body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tenant_id, $fv_projectid, $fv_clientid, $fv_note_body)
    {
        $this->tenant_id = $tenant_id;
        $this->fv_projectid = $fv_projectid;
        $this->fv_clientid = $fv_clientid;
        $this->fv_note_body = $fv_note_body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $tenant_details = Tenant::where('id', $this->tenant_id)->first();
            $apiurl = config('services.fv.default_api_base_url');
            if (isset($tenant_details->fv_api_base_url) and !empty($tenant_details->fv_api_base_url)) {
                $apiurl = $tenant_details->fv_api_base_url;
            }
            $filevine_api = new FilevineService($apiurl, "", $this->tenant_id);

            $params = [
                'projectId' => ['native' => $this->fv_projectid, 'partner' => null],
                'body' => $this->fv_note_body,
                'authorId' => ['native' => $this->fv_clientid, 'partner' => null]
            ];

            $resp = $filevine_api->createNote($params);
            Logging::warning("Create Note");
            Logging::warning($resp);

        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }
}
