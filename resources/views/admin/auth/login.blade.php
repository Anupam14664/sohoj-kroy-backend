<!DOCTYPE html>
<html lang="en">
<head>
    @php
        use App\Models\GeneralSetting;
        $generalSettings = GeneralSetting::first();
    @endphp
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #4f46e5, #9333ea);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .wrapper {
            background: #fff;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .logo img {
            display: block;
            margin: 0 auto 1rem;
            height: 60px;
        }

        .name {
            font-size: 1.8rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1.5rem;
            color: #4f46e5;
        }

        .alert {
            margin-bottom: 1rem;
            color: #e11d48;
            font-size: 0.95rem;
            text-align: center;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.8rem;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 0.75rem 0.25rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: transparent;
            transition: 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .form-group label {
            position: absolute;
            top: 50%;
            left: 0.75rem;
            transform: translateY(-50%);
            color: #888;
            background: #fff;
            padding: 0 0.25rem;
            transition: 0.3s;
            pointer-events: none;
        }

        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label {
            top: 0;
            font-size: 0.75rem;
            color: #4f46e5;
        }

        .btn {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #4f46e5, #9333ea);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: linear-gradient(135deg, #4338ca, #7e22ce);
        }

        @media (max-width: 480px) {
            .wrapper {
                margin: 0 1rem;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="logo">
        @if(isset($generalSettings->logo))
            <img src="{{ asset('storage/'.$generalSettings->logo) }}" alt="Logo" height="50px">
        @endif
    </div>

    <div class="name">Admin Login</div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div class="form-group">
            <input type="email" name="email" id="email" placeholder=" " required>
            <label for="email">Email Address</label>
        </div>

        <div class="form-group">
            <input type="password" name="password" id="password" placeholder=" " required>
            <label for="password">Password</label>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>
</div>

</body>
</html>
