<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Project User table model handle all filters, observers, evenets, relatioships
 */
class ProjectUser extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    const table = 'project_user';

    protected $table = self::table;

    protected $fillable = ['project_id', 'user_id','company_id'];
}
