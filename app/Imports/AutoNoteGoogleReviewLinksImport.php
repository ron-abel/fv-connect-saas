<?php

namespace App\Imports;

use App\Models\AutoNoteGoogleReviewCities;
use App\Models\AutoNoteGoogleReviewLinks;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class AutoNoteGoogleReviewLinksImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsFailures, SkipsErrors;

    /**
     * @var null
     */
    private $cur_tenant_id;
    private $count = 0;
    private $duplicate = 0;
    private $duplicateZips = [];

    public function __construct($tenant_id = null)
    {
        $this->cur_tenant_id = $tenant_id;
        if($this->cur_tenant_id == null) return false;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $google_review_added = AutoNoteGoogleReviewLinks::firstOrCreate([
            'tenant_id' => $this->cur_tenant_id,
            'review_link' => $row['review_link'],
        ], [
            'is_default' => $row['default'],
            'description' => $row['description'] ?? null,
        ]);

        if (isset($google_review_added->id)) {
            $current_date = date('Y-m-d H:i:s');
            $cityAutoReview = AutoNoteGoogleReviewCities::where([
                'auto_note_google_review_link_id' => $google_review_added->id,
                'zip_code' => $row['zip_code'],
            ])->first();
            if (!isset($cityAutoReview) || empty($cityAutoReview)) {
                AutoNoteGoogleReviewCities::create([
                    'auto_note_google_review_link_id' => $google_review_added->id,
                    'zip_code' => $row['zip_code'],
                    'created_at' => $current_date,
                    'updated_at' => $current_date
                ]);
                $this->count++;
            }else{
                $this->duplicateZips[] = $row['zip_code'];
                $this->duplicate++;
            }
        }

        return $google_review_added;
    }

    public function rules(): array
    {
        return [
            'zip_code' => 'required',
            'review_link' => 'required',
            'default' => 'required|boolean',
        ];
    }

    public function getTotalImported()
    {
        return $this->count;
    }

    public function getTotalDuplicate()
    {
        return $this->duplicate;
    }

    public function getTotalDuplicateZips()
    {
        return $this->duplicateZips;
    }
}
