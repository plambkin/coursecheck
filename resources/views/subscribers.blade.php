<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscribers</title>
</head>
<body>
    <h1>Subscribers</h1>
    <ul>
        @foreach ($subscribers as $subscriber)
            <li>{{ $subscriber['email'] }}</li>
        @endforeach
    </ul>
    <a href="{{ route('groups') }}">Back to Groups</a>
</body>
</html>
