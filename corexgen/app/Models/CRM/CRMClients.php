<?php

namespace App\Models\CRM;

use App\Models\Address;
use App\Models\Attachments;
use App\Models\CategoryGroupTag;
use App\Models\CommentNote;
use App\Models\Company;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;

class CRMClients extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use HasCustomFields;
    use SoftDeletes;

    const table = 'clients';

    protected $table = self::table;

    protected $fillable = [
        'type',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'company_name',
        'email',
        'primary_email',
        'primary_phone',
        'phone',
        'social_media',
        'cgt_id',
        'details',
        'tags',
        'birthdate',
        'company_id',
        'status',
        'created_by',
        'updated_by',
    ];


    protected $casts = [
        'email' => 'array',
        'phone' => 'array',
        'social_media' => 'array',
        'tags' => 'array',
    ];

    public function addresses()
    {
        return $this->belongsToMany(
            Address::class,
            'client_addresses',
            'client_id',
            'address_id',
            'id',
            'id'
        )
            ->withPivot('type')
            ->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function categoryGroupTag()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'cgt_id');
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

    public  function getActiveClientsStats()
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $thisMonthCount = self::where('status',  CRM_STATUS_TYPES['CLIENTS']['STATUS']['ACTIVE'])
            ->where('company_id', Auth::user()->company_id)
            ->whereBetween('created_at', [$currentMonth, now()])
            ->count();

        $lastMonthCount = self::where('status',  CRM_STATUS_TYPES['CLIENTS']['STATUS']['ACTIVE'])
            ->where('company_id', Auth::user()->company_id)
            ->whereBetween('created_at', [$lastMonth, $currentMonth])
            ->count();

        $percentageChange = $lastMonthCount > 0
            ? (($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100
            : 100; // 100% increase if no projects last month

        return [
            'current_month' => $thisMonthCount,
            'last_month' => $lastMonthCount,
            'percentage_change' => round($percentageChange, 2),
            'trend' => $percentageChange >= 0 ? 'up' : 'down',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            // Set default values using null coalescing
            $client->status = $client->status ?? CRM_STATUS_TYPES['CLIENTS']['STATUS']['ACTIVE'];
            $client->uuid = (string) Str::uuid();
            if (Auth::check()) {
                $client->company_id = $client->company_id ?? Auth::user()->company_id;
                $client->created_by = $client->created_by ?? Auth::id();
                $client->updated_by = $client->updated_by ?? Auth::id();
            } else {
                // Handle cases where Auth is not available (e.g., background jobs)
                $client->company_id = $client->company_id ?? null;
                $client->created_by = $client->created_by ?? null;
                $client->updated_by = $client->updated_by ?? null;
            }
        });

        static::saving(function ($client) {
            if (is_array($client->email) && count($client->email) > 0) {
                $client->primary_email = $client->email[0];
            }
            if (is_array($client->phone) && count($client->phone) > 0) {
                $client->primary_phone = $client->phone[0];
            }
        });
    }


}
