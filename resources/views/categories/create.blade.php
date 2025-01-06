@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
<div class="container">
    <h1>Create New Category</h1>

    <form action="{{ route('categories.store') }}" method="POST">
        @csrf

        <!-- Category Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Category Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <!-- Slug -->
        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug') }}" required>
        </div>

        <!-- Parent Category -->
        <div class="mb-3">
            <label for="parent_id" class="form-label">Parent Category</label>
            <select name="parent_id" id="parent_id" class="form-select">
                <option value="">No Parent</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Create Category</button>
    </form>
</div>
@endsection
