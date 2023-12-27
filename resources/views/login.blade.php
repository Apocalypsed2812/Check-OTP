<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Login Page</title>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="border rounded px-5 pt-3 py-5 w-100" style="max-width: 400px; height: fit-content">
            <h2 class="mt-4 text-center title-red">Login</h2>
            @if(session('error'))
                <div style="color: red;" class="text-center">
                    {{ session('error') }}
                </div>
            @endif
            <form action="{{ url('/login') }}" method="post" class="mt-4 w-100 mx-auto">
                @csrf
                <div class="form-group mb-3">
                    <label for="username">Username</label>
                    <input type="text" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" id="username" name="username">
                    @error('username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="password">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" value="{{ old('password') }}" id="password" name="password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <button type="submit" class="btn btn-success w-100 mt-3">Login</button>
                <div class="mt-3 text-center">
                    Don't have account ? <a href="{{ url('/register') }}">Register</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>