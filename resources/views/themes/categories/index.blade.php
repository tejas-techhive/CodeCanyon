@extends('layouts.app')
@section('title', 'Category List')

@section('content')
<div class="container">
    <h1>Themes Category List</h1>

    <a href="{{ route('theme-categories.create') }}" class="btn btn-primary mb-3">Add New Category</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Actions</th> <!-- Action Buttons -->
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr id="category-{{ $category->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>
                        @if ($category->flat === 'no')
                            <!-- Button to call API -->
                            <button 
                                class="btn btn-sm btn-info call-api-btn" 
                                data-category-id="{{ $category->id }}"
                            >
                                Call API
                            </button>
                        @endif

                        <a href="{{ route('theme-categories.edit', $category->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No categories found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 

<script>
    $(document).ready(function() {

        let base_url = "{{ url('/')}}";

        $('.call-api-btn').on('click', function(event) {
            event.preventDefault();

            const button = $(this); 
            const categoryId = button.data('category-id'); 

            button.prop('disabled', true).text('Loading...');
            $.ajax({
                url: base_url + '/api/theme/category-popular-items/' + categoryId,
                method: 'GET',
                data: { category_id: categoryId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('API call was successful');

                        // Update the flat column value to 'yes'
                        const flatStatus = button.closest('tr').find('.flat-status');
                        flatStatus.text('yes');

                        // Remove the button after success
                        button.remove();
                    } else {
                        alert('API call failed: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + xhr.responseText);
                },
                complete: function() {
                    // Re-enable the button
                    button.prop('disabled', false).text('Call API');
                }
            });
        });
    });
</script>
@endsection
