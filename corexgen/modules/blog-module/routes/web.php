<?php

use Modules\BlogModule\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::resource('blog/posts', PostController::class)->names([
        'index' => 'blog.posts.index',
        'create' => 'blog.posts.create',
        'store' => 'blog.posts.store',
        'edit' => 'blog.posts.edit',
        'update' => 'blog.posts.update',
        'destroy' => 'blog.posts.destroy'
    ]);
});
