<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    use HasFactory;

    const table = 'import_histories';

    protected $table = self::table;

    protected $fillable = [
        'company_id',
        'user_id',
        'is_tenant',
        'file_name',
        'import_type',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'failed_rows_details',
        'status',
        'error_message',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'is_tenant' => 'boolean',
        'failed_rows_details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
