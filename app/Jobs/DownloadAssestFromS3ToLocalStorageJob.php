<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Storage;
use App\Exceptions\FileDownloadException;

class DownloadAssestFromS3ToLocalStorageJob extends Job
{
    protected $tenantName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $tenantName)
    {
        $this->tenantName = $tenantName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (Storage::disk('local')->exists($this->tenantName)) {
            Storage::disk('local')->delete($this->tenantName);
        }

        Storage::disk('local')->makeDirectory($this->tenantName);

        $allFiles = Storage::disk('s3')->allFiles($this->tenantName.'/assets/scss');

        if (count($allFiles) > 0) {
            foreach ($allFiles as $key => $file) {
                $sourcePath = str_replace($this->tenantName, '', $file);
                if (Storage::disk('local')->exists($file)) {
                    // Delete existing one
                    Storage::disk('local')->delete($file);
                }
                if (!Storage::disk('local')->put($file, Storage::disk('s3')->get($file))) {
                    throw new FileDownloadException(
                        trans('messages.custom_error_message.ERROR_WHILE_DOWNLOADING_FILES_FROM_S3_TO_LOCAL'),
                        config('constants.error_codes.ERROR_WHILE_DOWNLOADING_FILES_FROM_S3_TO_LOCAL')
                    );
                }
            }
        } else {
            throw new FileDownloadException(
                trans('messages.custom_error_message.NO_FILES_FOUND_TO_DOWNLOAD'),
                config('constants.error_codes.NO_FILES_FOUND_TO_DOWNLOAD')
            );
        }
    }
}
