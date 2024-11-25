@extends('layout.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Blog Posts</h1>
        <a href="{{ route('blog.posts.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">
            Create Post
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 border-b">Title</th>
                    <th class="px-6 py-3 border-b">Status</th>
                    <th class="px-6 py-3 border-b">Published</th>
                    <th class="px-6 py-3 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                <tr>
                    <td class="px-6 py-4 border-b">{{ $post->title }}</td>
                    <td class="px-6 py-4 border-b">{{ ucfirst($post->status) }}</td>
                    <td class="px-6 py-4 border-b">{{ $post->published_at?->format('Y-m-d') ?? 'Not published' }}</td>
                    <td class="px-6 py-4 border-b">
                        <a href="{{ route('blog.posts.edit', $post) }}" class="text-blue-500">Edit</a>
                        <form action="{{ route('blog.posts.destroy', $post) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 ml-4">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
 
    </div>
</div>
@endsection