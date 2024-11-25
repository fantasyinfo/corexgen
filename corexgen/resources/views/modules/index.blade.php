@extends('layout.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-6">Module Management</h1>

        <!-- Upload Form -->
        <form action="{{ route('crm.modules.create') }}" 
              method="POST" 
              enctype="multipart/form-data"
              class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="module">
                    Upload New Module (ZIP)
                </label>
                <input type="file" 
                       name="module" 
                       id="module" 
                       accept=".zip"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('module')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Upload Module
            </button>
        </form>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Modules List -->
    <div class="bg-white shadow-md rounded-lg">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-6 py-3 border-b">Name</th>
                    <th class="px-6 py-3 border-b">Version</th>
                    <th class="px-6 py-3 border-b">Description</th>
                    <th class="px-6 py-3 border-b">Status</th>
                    <th class="px-6 py-3 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($modules as $module)
                <tr>
                    <td class="px-6 py-4 border-b">{{ $module->name }}</td>
                    <td class="px-6 py-4 border-b">{{ $module->version }}</td>
                    <td class="px-6 py-4 border-b">{{ $module->description }}</td>
                    <td class="px-6 py-4 border-b">{{ ucfirst($module->status) }}</td>
                    <td class="px-6 py-4 border-b">
                        <form action="{{ route('crm.modules.destroy', $module->name) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500" onclick="return confirm('Are you sure you want to uninstall this module?')">
                                Uninstall
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection