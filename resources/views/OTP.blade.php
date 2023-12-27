<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <title>Enter OTP</title>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="border rounded mt-3 w-100 py-3 pb-2" style="max-width: 400px; height: fit-content;">
            <h4 class="text-center mt-3">Enter your OTP</h4>
            @if(session('error'))
                <div style="color: red;" class="text-center">
                    {{ session('error') }}
                </div>
            @endif
            <form action="{{ url('/otp') }}" method="post" class="mt-4 px-3 pb-2">
                @csrf
                <div class="form-group mb-3">
                    <input type="text" class="form-control" id="otp" name="otp">
                </div>
                @if(session('otp'))
                    <input type="hidden" name="otp_created" id="otp_created" value="{{ session('otp') }}" />
                @endif
                @if(session('email'))
                    <input type="hidden" name="email" id="email" value="{{ session('email') }}" />
                @endif
                <button type="submit" class="btn btn-success w-100 mt-2">Send</button>
                <p class="text-center mt-3" id="message-otp">OTP will expire after <span id="counter" class="text-danger"></span> seconds.</p>
            </form>
        </div>
    </div>

<script>
    let countdown = 120;
    let counter = document.getElementById("counter");
    counter.innerHTML = countdown;

    let id = setInterval(() => {
        countdown--;
        if(countdown >= 0){
            counter.innerHTML = countdown;
        }
        if(countdown == -1){
            clearInterval(id);
            document.getElementById("message-otp").innerHTML = "OTP has been expired. Please <a href='/register'>register</a> account again"
            let otp_created = document.getElementById('otp_created').value;
            // console.log("OTP", otp_created);
            let csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let data = {
                otp: otp_created,
                _token: csrfToken
            }
            fetch('/unactived-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            })
            .then(res => res.json())
            .then(json => console.log(json))
            .catch(e => console.log(e))
        }
    }, 1000);
</script>
</body>
</html>