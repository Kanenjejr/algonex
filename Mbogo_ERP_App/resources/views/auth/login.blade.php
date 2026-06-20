{{-- resources/views/auth/login.blade.php --}}
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>MBOGO INFO APP+ — Login</title>
    <link rel="shortcut icon" href="{{ asset('icon1.png') }}" />

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;

            /* More blue background with image visible */
            background:
                linear-gradient(rgba(10, 45, 95, 0.65), rgba(30, 90, 160, 0.55)),
                url("{{ asset('Mbogo_Back.jpeg') }}") center/cover no-repeat;
        }

        .wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            padding: 20px;
        }

        /* Transparent logo area */
        .logo-circle {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -65px;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: none;
            border: none;
            z-index: 2;
        }

        .logo-circle img {
            width: 130px;
            height: 130px;
            object-fit: contain;
            background: transparent;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(6px);
            border-radius: 18px;
            padding: 90px 30px 35px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .25);
        }

        .title {
            text-align: center;
            color: #0b2f6b;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #444;
            font-size: 14px;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            transition: .2s;
        }

        .form-input:focus {
            border-color: #0b2f6b;
            box-shadow: 0 0 0 4px rgba(11, 47, 107, .12);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #0b2f6b, #3b82f6);
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: .2s;
        }

        .btn-login:hover {
            opacity: .95;
            transform: translateY(-1px);
        }

        .footer {
            margin-top: 18px;
            text-align: center;
            color: #555;
            font-size: 13px;
        }

        @media(max-width:500px) {
            .login-card {
                padding: 85px 20px 25px;
            }

            .logo-circle {
                width: 110px;
                height: 110px;
                top: -55px;
            }

            .logo-circle img {
                width: 110px;
                height: 110px;
            }
        }
    </style>
</head>

<body>
    @include('sweetalert::alert')

    <div class="wrapper">
        <div class="logo-circle">
            <img src="{{ asset('icon1.png') }}" alt="Mbogo-Logo">
        </div>

        <div class="login-card">
            <div class="title">MBOGO INFO HUB</div>
            <div class="subtitle">
                Mbogo Mining and General Supply Limited
            </div>

            <form method="POST" action="{{ route('auth') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <input type="text" name="username" id="username" class="form-input" placeholder="Enter Username"
                        required>
                </div>

                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-input"
                        placeholder="Enter Password" required>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    Login
                </button>
            </form>

            <div class="footer">
                © 2020 - {{ date('Y') }} | Mbogo ERP System
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Signing in...';
        });
    </script>
</body>

</html>
