@extends('layout.auth')
@section('content')

    <h2 class="text-center mb-4">Create an Account</h2>
    <form action="{{ route('company.register') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="cname" class="form-label">Company Name</label>
            <input type="text" name="cname" id="cname" class="form-control" placeholder="Enter your company name"
                required value="{{ old('cname') }}">
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Enter your name" required
                value="{{ old('name') }}">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required
                value="{{ old('email') }}">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="tel" name="phone" id="phone" class="form-control" placeholder="Enter your phone" required
                value="{{ old('phone') }}">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password"
                required>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                placeholder="Confirm your password" required>
        </div>
        <div class="mb-3">
            <label for="choose_plan" class="form-label">Choose Plan</label>
            <select class="form-select" required name="plan_id">
                @if ($plans)
                    @foreach ($plans as $item)
                        <option value="{{ $item->id }}" {{ request('plan') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <p>Already have an account? <a href="/login" class="text-primary">Login here</a></p>
    </div>

@endsection
