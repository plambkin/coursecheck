<!-- resources/views/subscribers.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Subscribers List</h1>

        @if ($subscribers)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber['email'] }}</td>
                            <td>{{ $subscriber['fields']['fname'] ?? 'N/A' }}</td>
                            <td>{{ $subscriber['fields']['lname'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No subscribers available.</p>
        @endif
    </div>
@endsection
