<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <title>Register Page</title>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center py-4" style="min-height: 100vh;">
        <div class="border rounded px-5 pt-3 py-5 w-100" style="max-width: 450px; height: fit-content;">
            <h2 class="text-center mt-3">Register</h1>
            @if(session('error'))
                <div style="color: red;" class="text-center">
                    {{ session('error') }}
                </div>
            @endif
            <form action="{{ url('/register') }}" method="post" class="mt-4 w-100">
                @csrf
                <div class="form-group mb-3">
                    <label for="username">Username</label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" name="username" id="username">
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}" name="password" id="password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="repassword">RePassword</label>
                    <input type="password" class="form-control @error('repassword') is-invalid @enderror" value="{{ old('repassword') }}" name="repassword" id="repassword">
                    @error('repassword')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="email">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" name="email" id="email">
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="phone">Phone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" name="phone" id="phone">
                    @error('phone')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-2">Register</button>
                <div class="mt-3 text-center">
                    You have an account ? <a href="{{ url('/login') }}">Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
