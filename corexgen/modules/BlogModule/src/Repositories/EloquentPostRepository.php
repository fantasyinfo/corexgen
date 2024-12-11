<?php

namespace Modules\BlogModule\Repositories;

use Modules\BlogModule\Models\Post;

class EloquentPostRepository implements PostRepository
{
    public function all()
    {
        return Post::where('status', 'published')
            ->latest()
            ->paginate(10);
    }

    public function find($id)
    {
        return Post::where('status', 'published')
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Post::create($data);
    }

    public function update($id, array $data)
    {
        $post = $this->find($id);
        $post->update($data);
        return $post;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}
