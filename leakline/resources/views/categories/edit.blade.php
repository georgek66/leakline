<h1>Edit Category</h1>

<form method="POST" action="{{ route('categories.update', $category->id) }}">
    @csrf
    @method('PUT')

    <label>Category Name:</label><br>
    <input type="text" name="name" value="{{ $category->name }}" required><br><br>

    <button type="submit">Update</button>
</form>

<br>
<a href="{{ route('categories.index') }}">⬅ Back to list</a>

