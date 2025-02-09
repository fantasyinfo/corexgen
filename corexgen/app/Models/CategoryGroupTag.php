<?php

namespace App\Models;

use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

/**
 * Category Group Tags table model handle all filters, observers, evenets, relatioships
 */
class CategoryGroupTag extends Model
{
    use HasFactory;

    const table = 'category_group_tag';

    protected $table = self::table;

    protected $fillable =
        ['name', 'color', 'type', 'relation_type', 'status', 'company_id'];

    /**
     * clients relations with category group table table
     */
    public function clients()
    {
        return $this->hasMany(CRMClients::class, 'cgt_id');
    }

    /**
     * leads group relations with category group table table
     */
    public function leadsGroups()
    {
        return $this->hasMany(CRMLeads::class, 'group_id');
    }

    /**
     * leads sources relations with category group table table
     */
    public function leadsSources()
    {
        return $this->hasMany(CRMLeads::class, 'source_id');
    }

    /**
     * leads status relations with category group table table
     */
    public function leadsStatus()
    {
        return $this->hasMany(CRMLeads::class, 'status_id');
    }

    /**
     * product category relations with category group table table
     */
    public function productsCategory()
    {
        return $this->hasMany(CRMLeads::class, 'cgt_id');
    }

    /**
     * product tax relations with category group table table
     */
    public function productsTax()
    {
        return $this->hasMany(CRMLeads::class, 'tax_id');
    }

    /**
     * tasks relations with category group table table
     */
    public function tasks()
    {
        return $this->hasMany(Tasks::class, 'status_id');
    }

    /**
     * boot method 
     */
    protected static function boot()
    {
        parent::boot();

        // Add a global scope to filter by status = 'active'
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('status', 'active');
        });

        static::creating(function ($cgt) {
            // Set default values
            $cgt->created_at = now();
            $cgt->updated_at = now();

        });
    }
}
