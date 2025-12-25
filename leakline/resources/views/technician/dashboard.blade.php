<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Technician Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f5f5f5; padding:20px; }
        .cards { display:grid; grid-template-columns: repeat(3,1fr); gap:15px; margin-bottom:20px; }
        .card { background:white; padding:15px; border-radius:6px; }
        table { width:100%; border-collapse:collapse; background:white; }
        th, td { padding:10px; border-bottom:1px solid #ddd; }
        th { background:#eee; }
        .btn { padding:5px 10px; border-radius:4px; text-decoration:none; color:white; }
        .start { background:#16a34a; }
        .done { background:#2563eb; }
    </style>
</head>
<body>

<h2>Technician Dashboard</h2>

<div class="cards">
    <div class="card">My Jobs<br><strong>6</strong></div>
    <div class="card">In Progress<br><strong>2</strong></div>
    <div class="card">Completed Today<br><strong>1</strong></div>
</div>

<table>
    <thead>
    <tr>
        <th>Work Order</th>
        <th>Status</th>
        <th>Priority</th>
        <th>Due</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>#WO-88</td>
        <td>Assigned</td>
        <td>High</td>
        <td>Today</td>
        <td><a class="btn start" href="#">Start</a></td>
    </tr>
    <tr>
        <td>#WO-85</td>
        <td>In Progress</td>
        <td>Medium</td>
        <td>Tomorrow</td>
        <td><a class="btn done" href="#">Complete</a></td>
    </tr>
    </tbody>
</table>
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
</body>
</html>
