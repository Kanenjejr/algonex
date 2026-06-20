<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
        }

        .header img {
            height: 60px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th {
            background: #f4f4f4;
        }

        th, td {
            padding: 5px;
            text-align: left;
        }
    </style>
</head>

<body>

<div class="header">
    <img src="{{ public_path('Mbogo Logo.png') }}">
    <h3>MBOGO ERP SYSTEM</h3>
    <p>Customer Report</p>
</div>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Account</th>
            <th>Phone</th>
            <th>TIN</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @foreach($customers as $c)
        <tr>
            <td>{{ $c->customer_code }}</td>
            <td>{{ $c->customer_name }}</td>
            <td>{{ $c->account_code }} - {{ $c->account_name }}</td>
            <td>{{ $c->phone }}</td>
            <td>{{ $c->tin_number }}</td>
            <td>{{ $c->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>