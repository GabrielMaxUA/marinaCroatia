@extends('layouts.app')

@section('title', 'Login - Marina Croatia')

@section('content')
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card" style="width: 100%; max-width: 400px;">
        <div class="card-header">
            <h2 style="text-align: center; margin: 0;">Marina Croatia Login</h2>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember Me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>
@endsection