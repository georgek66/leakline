<h1>Categories</h1>

<a href="{{ route('categories.create') }}">➕ Add New Category</a>
<br><br>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Created At</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>

    @foreach($categories as $category)
        <tr>
            <td>{{ $category->id }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->created_at }}</td>
            <td>
                <a href="{{ route('categories.edit', $category->id) }}">Edit</a>
            </td>
            <td>
                <form action="{{ route('categories.destroy', $category->id) }}"
                      method="POST"
                      onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach

</table>
