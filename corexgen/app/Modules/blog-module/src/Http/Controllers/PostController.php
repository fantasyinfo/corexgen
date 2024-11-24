<?php

namespace Modules\BlogModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\BlogModule\Http\Requests\PostRequest;
use Modules\BlogModule\Repositories\PostRepository;

class PostController extends Controller
{
    protected $posts;

    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
        $this->middleware('can:manage-blog');
    }

    public function index()
    {
        $posts = $this->posts->all();
        return view('blog-module::posts.index', compact('posts'));
    }

    public function create()
    {
        return view('blog-module::posts.create');
    }

    public function store(PostRequest $request)
    {
        $this->posts->create($request->validated());
        return redirect()->route('blog.posts.index')
            ->with('success', 'Post created successfully');
    }

    public function edit($id)
    {
        $post = $this->posts->find($id);
        return view('blog-module::posts.edit', compact('post'));
    }

    public function update(PostRequest $request, $id)
    {
        $this->posts->update($id, $request->validated());
        return redirect()->route('blog.posts.index')
            ->with('success', 'Post updated successfully');
    }

    public function destroy($id)
    {
        $this->posts->delete($id);
        return redirect()->route('blog.posts.index')
            ->with('success', 'Post deleted successfully');
    }
}
