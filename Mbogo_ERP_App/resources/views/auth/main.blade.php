<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MBOGO INFO APP+ — Departments</title>
    <link rel="shortcut icon" href="{{ asset('icon1.png') }}">
    <script src="{{ asset('js/module-icons.js') }}"></script>

    <style>
        :root {
            --sidebar-start: #1e293b;
            --sidebar-end: #334155;
            --header-start: #1e3a8a;
            --header-end: #f59e0b;
            --bg: #eef2f7;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* ===== HEADER ===== */
        .topbar {
            height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            background: linear-gradient(135deg, #1d2538 0%, #9fb3bb 100%);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1200;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .15);
        }

        .left-head {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 30px;
            cursor: pointer;
            display: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand img {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            padding: 4px;
            background: white;
        }

        .brand-text strong {
            display: block;
            font-size: 18px;
        }

        .brand-text span {
            font-size: 12px;
            opacity: .9;
        }

        .profile-btn {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
        }

        /* ===== LAYOUT ===== */
        .layout {
            display: flex;
            min-height: 100vh;
            padding-top: 78px;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--sidebar-start), var(--sidebar-end));
            color: white;
            padding: 20px 15px;
            position: fixed;
            top: 78px;
            left: 0;
            bottom: 0;
            z-index: 1100;
            /* IMPORTANT FIX */
            overflow-y: auto;
            transition: left .3s ease;
        }

        .sidebar h3 {
            margin: 0 0 18px;
            font-size: 15px;
            opacity: .9;
            letter-spacing: 1px;
        }

        .nav-item {
            margin-bottom: 12px;
        }

        .nav-btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            background: rgba(255, 255, 255, .08);
            color: white;
            font-size: 15px;
            font-weight: 600;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, .16);
        }

        .dropdown {
            display: none;
            margin-top: 8px;
            padding-left: 10px;
        }

        .dropdown a {
            display: block;
            color: #e2e8f0;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 10px;
            margin: 5px 0;
            background: rgba(255, 255, 255, .04);
        }

        .dropdown a:hover {
            background: rgba(255, 255, 255, .12);
        }

        /* ===== MAIN CONTENT ===== */
        .content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            position: relative;
            z-index: 1;
        }

        .hero {
            background: white;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .06);
        }

        .hero h1 {
            margin: 0 0 15px;
            font-size: 42px;
            color: #1e3a8a;
            line-height: 1.2;
        }

        .hero p {
            color: #64748b;
            line-height: 1.8;
            max-width: 700px;
        }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 20px;
            color: #64748b;
            margin-left: 280px;
        }

        /* ===== MOBILE ===== */
        @media(max-width:768px) {
            .menu-toggle {
                display: block;
            }

            .sidebar {
                left: -100%;
                width: 260px;
                z-index: 2000;
                /* MAKE IT ABOVE CONTENT */
            }

            .sidebar.active {
                left: 0;
            }

            .content {
                margin-left: 0;
                padding: 20px;
            }

            footer {
                margin-left: 0;
            }

            .hero h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="left-head">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <div class="brand">
                <img src="{{ asset('icon1.png') }}" alt="Logo">
                <div class="brand-text">
                    <strong>MBOGO INFO APP+</strong>
                    <span>Integrated Enterprise Platform</span>
                </div>
            </div>
        </div>

        <a href="{{ route('profile') }}" class="profile-btn">Profile</a>
    </div>

    <div class="layout">
        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <h3>DEPARTMENTS</h3>

            <div class="nav-item">
                <button class="nav-btn" onclick="toggleDropdown('financeDrop')">
                    Finance & Administration ▼
                </button>
                <div class="dropdown" id="financeDrop">
                    <a href="{{ route('business-admin') }}">Open Department</a>
                    <a href="{{ route('requisition') }}">Requisition</a>
                    <a href="{{ route('reporting') }}">Reports</a>
                </div>
            </div>

            <div class="nav-item">
                <button class="nav-btn" onclick="toggleDropdown('productionDrop')">
                    Production Department ▼
                </button>
                <div class="dropdown" id="productionDrop">
                    <a href="{{ route('manufacturing') }}">Open Department</a>
                    <a href="{{ route('requisition') }}">Requisition</a>
                    <a href="{{ route('reporting') }}">Reports</a>
                </div>
            </div>

            <div class="nav-item">
                <button class="nav-btn" onclick="toggleDropdown('businessDrop')">
                    Business Development ▼
                </button>
                <div class="dropdown" id="businessDrop">
                    <a href="{{ route('sales-marketing') }}">Open Department</a>
                    <a href="{{ route('requisition') }}">Requisition</a>
                    <a href="{{ route('reporting') }}">Reports</a>
                </div>
            </div>
        </aside>

        <!-- MAIN -->
        <main class="content">
            <div class="hero">
                <h1>Welcome to Mbogo Info Hub Departments</h1>
                <p>
                    This centralized departmental workspace unifies finance, production,
                    business development, requisition workflows, and reporting into one
                    intelligent enterprise environment for smooth daily operations,
                    fast approvals, and executive-level visibility.
                </p>
            </div>
        </main>
    </div>

    <footer>© 2020 - {{ date('Y') }} — Eng. Kivuyo</footer>

    <script>
        function toggleDropdown(id) {
            const el = document.getElementById(id);
            el.style.display = el.style.display === 'block' ? 'none' : 'block';
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
    </script>

</body>

</html>
