<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentNote extends Model
{
    use HasFactory;

    const table = 'comments_notes';

    protected $table = self::table;

    protected $fillable = ['company_id', 'user_id', 'comment', 'commentable_id', 'commentable_type'];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
