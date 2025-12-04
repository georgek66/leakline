<h1>Create Category</h1>

<form method="POST" action="{{ route('categories.store') }}">
    @csrf

    <label>Category Name:</label><br>
    <input type="text" name="name" required><br><br>

    <button type="submit">Save</button>
</form>

<br>
<a href="{{ route('categories.index') }}">⬅ Back to list</a>


