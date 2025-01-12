<?php

namespace App\Models\CRM;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Template table model handle all filters, observers, evenets, relatioships
 */
class CRMTemplates extends Model
{
    use HasFactory;

    const table = 'templates';

    protected $table = self::table;

    protected $fillable = [
        'title',
        'template_details',
        'type',
        'company_id',
        'created_by'
    ];


    /**
     * company relations with template table
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    /**
     * create by user relations with template table
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}
