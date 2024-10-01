<!-- resources/views/subscriberDetails.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Subscriber Details</h1>
        
        @if ($subscriber)
            <table class="table">
                <tr>
                    <th>Email</th>
                    <td>{{ $subscriber['email'] }}</td>
                </tr>
                <tr>
                    <th>First Name</th>
                    <td>{{ $subscriber['fName'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Last Name</th>
                    <td>{{ $subscriber['lName'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Country</th>
                    <td>{{ $country ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Current Start Date</th>
                    <td>{{ $subscriber['startingDate'] ?? 'N/A' }}</td>
                </tr>
            </table>

            <!-- Form to update the start date -->
            <form action="{{ route('subscriber.updateStartDate') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="start_date">Select a new start date:</label>
                    <div>
                        @if (isset($subscriber['possible_start_dates']) && is_array($subscriber['possible_start_dates']))
                            @foreach ($subscriber['possible_start_dates'] as $date)
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="start_date" 
                                        id="start_date_{{ $loop->index }}" 
                                        value="{{ $date }}" 
                                        {{ $loop->first ? 'checked' : '' }}>
                                    <label class="form-check-label" for="start_date_{{ $loop->index }}">
                                        {{ $date }}
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <p>No possible start dates available.</p>
                        @endif
                    </div>
                </div>

                <!-- Hidden fields to include email and country if needed -->
                <input type="hidden" name="email" value="{{ $subscriber['email'] }}">
                <input type="hidden" name="country" value="{{ $country ?? 'N/A' }}">

                <!-- Submit button -->
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Start Date</button>
                </div>
            </form>
        @else
            <p>No subscriber details available.</p>
        @endif

        <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Back</a>
    </div>
@endsection
