<?php

namespace App\Models\CRM;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMTemplates extends Model
{
    use HasFactory;

    const table = 'templates';

    protected $table = self::table;

    protected $fillable = [
        'title',
        'template_details',
        'type',
        'user_id',
        'company_id',
        'created_by'
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }


}
