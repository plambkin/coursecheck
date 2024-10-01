<!-- resources/views/groups.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>MailerLite Groups</h1>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($groups)
            <ul class="list-group">
                @foreach ($groups as $group)
                    <li class="list-group-item">
                        {{ $group['name'] }} ({{ $group['total'] }} subscribers)
                    </li>
                @endforeach
            </ul>
        @else
            <p>No groups available.</p>
        @endif
    </div>
@endsection
