<!-- resources/views/subscriberForm.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Get Subscriber Information</h1>

        <!-- Display validation errors, if any -->
        @if ($errors->any())
            <div class="alert alert-danger">
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
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" id="country" name="country" required placeholder="Enter your country">
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@endsection
