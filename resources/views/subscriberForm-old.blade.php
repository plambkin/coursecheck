<!-- resources/views/subscriberForm.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Subscriber</title>
</head>
<body>
    <h1>Get Subscriber Information</h1>

    <!-- Display validation errors, if any -->
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- The form to input email and country -->
    <form action="{{ url('/get-subscriber') }}" method="POST">
        @csrf
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div>
            <label for="country">Country:</label>
            <input type="text" id="country" name="country" required>
        </div>

        <div>
            <button type="submit">Submit</button>
        </div>
    </form>
</body>
</html>
