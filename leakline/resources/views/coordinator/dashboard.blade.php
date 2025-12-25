<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coordinator Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f5f5f5; padding:20px; }
        .cards { display:grid; grid-template-columns: repeat(4,1fr); gap:15px; margin-bottom:20px; }
        .card { background:white; padding:15px; border-radius:6px; }
        table { width:100%; border-collapse:collapse; background:white; }
        th, td { padding:10px; border-bottom:1px solid #ddd; text-align:left; }
        th { background:#eee; }
        .btn { padding:5px 10px; background:#2563eb; color:white; border-radius:4px; text-decoration:none; }
    </style>
</head>
<body>

<h2>Coordinator Dashboard</h2>

<div class="cards">
    <div class="card">Open Incidents<br><strong>12</strong></div>
    <div class="card">Assigned<br><strong>8</strong></div>
    <div class="card">In Progress<br><strong>5</strong></div>
    <div class="card">Overdue<br><strong>2</strong></div>
</div>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Category</th>
        <th>Severity</th>
        <th>Status</th>
        <th>Created</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>#1021</td>
        <td>Leak</td>
        <td>High</td>
        <td>Open</td>
        <td>2 hours ago</td>
        <td><a class="btn" href="#">Assign</a></td>
    </tr>
    <tr>
        <td>#1019</td>
        <td>Maintenance</td>
        <td>Medium</td>
        <td>Assigned</td>
        <td>Yesterday</td>
        <td><a class="btn" href="#">View</a></td>
    </tr>
    </tbody>
</table>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
</body>
</html>
