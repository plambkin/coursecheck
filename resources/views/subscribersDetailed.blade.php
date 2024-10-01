<!-- resources/views/subscribersDetailed.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Detailed Subscriber List</h1>

        @if ($subscribers)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Start Date</th>
                        <th>Date Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber['email'] }}</td>
                            <td>{{ $subscriber['fields']['fname'] ?? 'N/A' }}</td>
                            <td>{{ $subscriber['fields']['lname'] ?? 'N/A' }}</td>
                            <td>{{ $subscriber['fields']['start_date'] ?? 'N/A' }}</td>
                            <td>{{ $subscriber['date_updated'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No detailed subscriber data available.</p>
        @endif
    </div>
@endsection
