<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function add($modal, $data)
    {
        return $modal->comments()->create([
            'company_id' => Auth::user()->company_id,
            'user_id' => Auth::id(),
            'comment' => $data['comment'],
        ]);

    }
}