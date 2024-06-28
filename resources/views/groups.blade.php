<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Groups</title>
</head>
<body>
    <h1>Groups</h1>
    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif
    <ul>
        @foreach ($groups as $group)
            <li>
                {{ $group['name'] }} ({{ $group['id'] }})
                <a href="{{ route('subscribers', ['groupId' => $group['id']]) }}">View Subscribers</a>
            </li>
        @endforeach
    </ul>
    <h2>Add Subscriber</h2>
    <form action="{{ route('createSubscriber') }}" method="POST">
        @csrf
        <label for="group_id">Group ID:</label>
        <input type="text" id="group_id" name="group_id" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Add Subscriber</button>
    </form>
</body>
</html>
