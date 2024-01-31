<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use File;
use Illuminate\Support\Facades\Log as Logging;
use App\Models\DbBackup;

class JobHandleDbBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {

            $backup_path = public_path() . "/backup";
            if (!file_exists($backup_path)) {
                mkdir($backup_path, 0777, true);
            }

            $backup_path = $backup_path . "/";

            // Delete File Older Than DB_BACKUP_SAVE_DATE
            $older_day = env('DB_BACKUP_SAVE_DATE') ? env('DB_BACKUP_SAVE_DATE') : 100;
            DbBackup::whereDate('created_at', '<=', now()->subDays($older_day))->delete();
            $files = glob($backup_path . "*");
            $now   = time();
            foreach ($files as $file) {
                if (is_file($file)) {
                    if ($now - filemtime($file) >= 60 * 60 * 24 * $older_day) {
                        unlink($file);
                    }
                }
            }

            // Create SQL Backup File
            $today_date = Carbon::now()->format("Y-m-d-H-i-s");
            $filename =  $today_date . ".backup.sql";
            $command = "mysqldump --user=" . env('DB_USERNAME') . " --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . " > " . $backup_path . $filename;
            $returnVar = NULL;
            $output  = NULL;
            exec($command, $output, $returnVar);

            // Create ZIP
            $zip_file_name = $today_date . ".backup.zip";
            $zip_file = $backup_path . $zip_file_name;
            $zip = new \ZipArchive();
            $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $zip->addFile($backup_path . $filename, $filename);
            $zip->close();

            // Create a Entry on db_backups Table
            $db_backup = new DbBackup;
            $db_backup->file_name = $zip_file_name;
            $db_backup->save();
            unlink($backup_path . $filename);
        } catch (\Exception $e) {
            Logging::warning($e->getMessage());
        }
    }
}
