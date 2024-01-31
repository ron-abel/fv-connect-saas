<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFileUploadConfiguration extends Model
{
    use HasFactory;
    protected $table = "client_file_upload_configurations";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'project_type_id',
        'project_type_name',
        'is_enable_file_uploads',
        'is_defined_organization_scheme',
        'choice',
        'handle_files_action',
        'target_section_id',
        'target_section_name',
        'target_field_id',
        'target_field_name',
        'hashtag',
    ];

    public static $config_options = [
        '1' => 'Upload to Project Root Folder',
        '2' => 'Static Section - Attach to Doc Field',
        '3' => 'Create Collection Item - Attach to Doc Field'
    ];
}
