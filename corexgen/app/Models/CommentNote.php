<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Comments Notes table model handle all filters, observers, evenets, relatioships
 */
class CommentNote extends Model
{
    use HasFactory;

    const table = 'comments_notes';

    protected $table = self::table;

    protected $fillable = ['company_id', 'user_id', 'comment', 'commentable_id', 'commentable_type'];



    /**
     * commentable relations with comment notes table
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * company relations with comment notes table
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    /**
     * user relations with comment notes table
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
