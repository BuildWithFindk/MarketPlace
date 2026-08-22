<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logout</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont,
                'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 40px 32px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            text-align: center;
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            background: #FEE2E2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }
        p {
            font-size: 15px;
            color: #6B7280;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        .user-box {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }
        .avatar {
            width: 40px;
            height: 40px;
            background: #E5E7EB;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #6B7280;
            font-size: 16px;
            flex-shrink: 0;
        }
        .user-name {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }
        .user-email {
            font-size: 12px;
            color: #9CA3AF;
            margin-top: 2px;
        }
        .btn-row {
            display: flex;
            gap: 12px;
        }
        .btn-cancel {
            flex: 1;
            padding: 13px;
            background: #fff;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .btn-cancel:hover { background: #F9FAFB; }
        .btn-logout {
            flex: 1;
            padding: 13px;
            background: #DC2626;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            width: 100%;
            box-shadow: 0 4px 12px rgba(220,38,38,0.35);
        }
        .btn-logout:hover { background: #B91C1C; }
        .back-link {
            display: block;
            margin-top: 20px;
            font-size: 13px;
            color: #9CA3AF;
            text-decoration: none;
        }
        .back-link:hover { color: #6B7280; }
    </style>
</head>
<body>
    <div class="card">

        <div class="icon-wrap">
            <svg width="36" height="36" fill="none"
                 stroke="#DC2626" stroke-width="2"
                 stroke-linecap="round"
                 stroke-linejoin="round"
                 viewBox="0 0 24 24">
                <path d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                <path d="M13 20v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </div>

        <h1>Log Out</h1>

        <p>
            Do you really want to log out of
            <strong style="color:#374151;">
                {{ config('app.name') }}
            </strong>?
        </p>

        @auth
        <div class="user-box">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->first_name ?? auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <div class="user-name">
                    {{ auth()->user()->first_name ?? auth()->user()->name }}
                </div>
                <div class="user-email">
                    {{ auth()->user()->email }}
                </div>
            </div>
        </div>
        @endauth

        <div class="btn-row">
            <a href="{{ url()->previous() ?? route('home') }}"
               class="btn-cancel">
               No, Cancel
            </a>
            <form method="POST"
                  action="{{ route('logout') }}"
                  style="flex:1; margin:0;">
                @csrf
                <button type="submit"
                        class="btn-logout">
                    Yes, Log Out
                </button>
            </form>
        </div>

        <a href="{{ route('home') }}" class="back-link">
            ← Back to home
        </a>

    </div>
</body>
</html>
