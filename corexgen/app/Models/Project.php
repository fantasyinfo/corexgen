<?php

namespace App\Models;



use App\Models\Attachments;
use App\Models\CommentNote;
use App\Models\Company;
use App\Models\CRM\CRMClients;
use App\Models\User;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Contracts\Auditable;

class Project extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use HasCustomFields;
    use SoftDeletes;

    const table = 'projects';

    protected $table = self::table;

    protected $fillable = [
        'title',
        'description',
        'billing_type',
        'start_date',
        'due_date',
        'deadline',
        'estimated_hours',
        'time_spent',
        'status',
        'client_id',
        'company_id',
        'one_time_cost',
        'per_hour_cost',
        'progress'
    ];

  

    /**
     * Relationships
     */

    // Belongs to company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function client()
    {
        return $this->belongsTo(CRMClients::class, 'client_id');
    }

    // Many-to-many relationship for multiple assignees
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')
            ->withTimestamps()
            ->withPivot('company_id');
    }


    public function comments()
    {
        return $this->morphMany(CommentNote::class, 'commentable')
            ->with('user:id,name,profile_photo_path') // Eager load only needed user fields
            ->latest('created_at');
    }

    public function attachments()
    {
        return $this->morphMany(Attachments::class, 'attachable')->latest('created_at');

    }


    /**
     * Model boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();


        // Add a global scope to filter by status = 'active'
        // static::addGlobalScope('active', function (Builder $builder) {
        //     $builder->where('projects.status', CRM_STATUS_TYPES['PROJECTS']['STATUS']['ACTIVE']);
        // });


        static::creating(function ($project) {
            $project->status = $project->status ?? CRM_STATUS_TYPES['PROJECTS']['STATUS']['ACTIVE'];

            if (Auth::check()) {
                $project->company_id = $project->company_id ?? Auth::user()->company_id;
           
            } else {
                $project->company_id = $project->company_id ?? null;
        
            }
        });
    }
}
