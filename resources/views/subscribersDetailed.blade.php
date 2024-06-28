<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detailed Subscribers</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Detailed Subscribers</h1>
    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Starting Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subscribers as $subscriber)
                <tr>
                    <td>{{ $subscriber['email'] ?? 'N/A' }}</td>
                    <td>{{ $subscriber['fName'] ?? 'N/A' }}</td>
                    <td>{{ $subscriber['lName'] ?? 'N/A' }}</td>
                    <td>{{ $subscriber['startingDate'] ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('groups') }}">Back to Groups</a>
</body>
</html>
