<?php

namespace App\Models;

use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;


/**
 * Product and Services table model handle all filters, observers, evenets, relatioships
 */
class ProductsServices extends Model implements Auditable
{
    use HasFactory;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    use HasCustomFields;

    const table = 'products_services';

    protected $table = self::table;

    protected $fillable = [
        'type',
        'title',
        'slug',
        'description',
        'rate',
        'unit',
        'status',
        'created_by',
        'updated_by',
        'company_id',
        'cgt_id',
        'tax_id',
    ];



    /**
     * company relations with product services table
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    /**
     * updated by user relations with product services table
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    /**
     * create by user relations with product services table
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    /**
     * category relations with product services table
     */
    public function category()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'cgt_id');
    }


    /**
     * tax relations with product services table
     */
    public function tax()
    {
        return $this->belongsTo(CategoryGroupTag::class, 'tax_id');
    }


    /**
     * boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($products) {
            $products->status = $products->status ?? CRM_STATUS_TYPES['PRODUCTS_SERVICES']['STATUS']['ACTIVE'];
            $products->slug = Str::slug($products->title, '-');
            $products->created_by = $products->created_by ?? Auth::id();
            $products->updated_by = $products->updated_by ?? Auth::id();
            $products->company_id = $products->company_id ?? Auth::user()->company_id;
        });

    }

}
