<?php

namespace App\Models\CRM;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CRMProposals extends Model
{
    use HasFactory;


    const table = 'proposals';

    protected $table = self::table;

    protected $fillable = [
        '_prefix',
        '_id',
        'title',
        'url',
        'value',
        'details',
        'typable_type',
        'typable_id',
        'creating_date',
        'valid_date',
        'accepted_details',
        'status',
        'template_id',
        'assign_to',
        'company_id',
    ];

    protected $casts = [
        'accepted_details' => 'array'
    ];


    public function typable()
    {
        return $this->morphTo();
    }

    public function template()
    {
        return $this->belongsTo(CRMTemplates::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }
}
