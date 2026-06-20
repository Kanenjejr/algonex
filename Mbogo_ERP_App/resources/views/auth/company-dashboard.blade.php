<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>MBOGO INFO APP+ — Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --sidebar-start: #1e293b;
            --sidebar-end: #334155;
            --bg: #f3f4f6;
            --text: #1e293b;
            --muted: #64748b;
        }

        /* reset */
        * {
            box-sizing: border-box;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* topbar */
        .topbar {
            height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: linear-gradient(135deg, #1d2538 0%, #3b82f6 100%);
            color: white;
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
            gap: 12px;
        }

        .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            display: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand img {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: white;
            padding: 2px;
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
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            position: relative;
        }

        .profile-btn img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid #fff;
            object-fit: cover;
        }

        .profile-dropdown {
            display: none;
            position: absolute;
            top: 44px;
            right: 0;
            background: white;
            color: var(--text);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
            min-width: 160px;
        }

        .profile-dropdown a {
            display: block;
            padding: 10px 14px;
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
        }

        .profile-dropdown a:hover {
            background: #f3f4f6;
        }

        /* layout */
        .layout {
            display: flex;
            min-height: 100vh;
            padding-top: 78px;
        }

        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #1e293b, #334155);
            color: white;
            padding: 20px 15px;
            position: fixed;
            top: 78px;
            left: 0;
            bottom: 0;
            overflow-y: auto;
        }

        .sidebar h3 {
            margin: 0 0 18px;
            font-size: 15px;
        }

        .nav-item {
            margin-bottom: 12px;
        }

        .nav-btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            justify-content: space-between;
            cursor: pointer;
            background: rgba(255, 255, 255, .08);
            color: white;
            font-size: 14px;
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
            display: flex;
            align-items: center;
            gap: 8px;
            color: #e2e8f0;
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 8px;
            margin: 4px 0;
            background: rgba(255, 255, 255, .04);
        }

        .dropdown a:hover {
            background: rgba(255, 255, 255, .12);
        }

        /* content */
        .content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-start;
        }

        /* company cards */
        .company-cards {
            flex: 2;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
        }

        .card {
            border-radius: 12px;
            min-height: 70px;
            padding: 10px;
            color: white;
            box-shadow: 0 3px 8px rgba(0, 0, 0, .08);
            font-size: 12px;
            transition: transform .2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .MGL001 {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6, #64748b);
        }

        .NCL001 {
            background: linear-gradient(135deg, #1f7a2f, #d97706);
        }

        .NFL001 {
            background: linear-gradient(135deg, #166534, #ca8a04, #312e81);
        }

        .BAN001 {
            background: linear-gradient(135deg, #3730a3, #9ca3af);
        }

        .NIL001 {
            background: linear-gradient(135deg, #c2410c, #f59e0b);
        }

        /* news panel BELOW CARDS, right side */
        .company-news {
            flex: 1;
            background: white;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, .08);
            max-height: 300px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-self: flex-start;
            position: sticky;
            bottom: 20px;
            cursor: pointer;
        }

        .company-news h2 {
            color: #1e293b;
            margin: 0 0 10px;
            font-size: 16px;
        }

        .news-container {
            flex: 1;
            position: relative;
        }

        .news-item {
            margin-bottom: 10px;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.3;
        }

        /* top hero */
        .hero {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .06);
            margin-bottom: 20px;
            width: 100%;
        }

        .hero h1 {
            margin: 0 0 10px;
            font-size: 28px;
            color: #1e3a8a;
        }

        .hero p {
            color: #64748b;
            font-size: 14px;
        }

        /* footer */
        footer {
            text-align: center;
            padding: 15px;
            color: #64748b;
            margin-left: 260px;
        }

        /* mobile */
        @media(max-width:768px) {
            .menu-toggle {
                display: block;
            }

            .sidebar {
                left: -100%;
                width: 240px;
                transition: .3s;
            }

            .sidebar.active {
                left: 0;
            }

            .content {
                margin-left: 0;
                padding: 10px;
                flex-direction: column;
            }

            footer {
                margin-left: 0;
            }

            .company-cards,
            .company-news {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="left-head">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <div class="brand">
                <img src="{{ asset('icon1.png') }}">
                <div class="brand-text">
                    <strong>MBOGO INFO HUB</strong>
                    <span>Integrated Enterprise Platform</span>
                </div>
            </div>
        </div>

        <div class="profile-btn" onclick="toggleProfile()">
            {{ Auth::user()->name }}
            <img src="{{ Auth::user()->image ? asset('storage/' . Auth::user()->image) : asset('icon1.png') }}">
            <div class="profile-dropdown" id="profileDropdown">
                <a href="{{ route('profile') }}">Change Profile</a>
                <a href="{{ route('logout') }}">Logout</a>
            </div>
        </div>
    </div>

    <div class="layout">

        <aside class="sidebar" id="sidebar">
            <h3>DEPARTMENTS</h3>
            @foreach ($departments as $dep)
                <div class="nav-item">
                    <button class="nav-btn"
                        onclick="toggleDropdown('{{ \Illuminate\Support\Str::slug($dep['name']) }}')">
                        {{ $dep['name'] }}
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="dropdown" id="{{ \Illuminate\Support\Str::slug($dep['name']) }}">
                        @foreach ($dep['sections'] as $section)
                            @can($section['permission'])
                                @if (!empty($section['route']) && \Illuminate\Support\Facades\Route::has($section['route']))
                                    <a href="{{ route($section['route']) }}">
                                        <i class="fa-solid {{ $section['icon'] }}"></i>
                                        {{ $section['name'] }}
                                    </a>
                                @else
                                    <a href="javascript:void(0)"
                                        title="Route not found: {{ $section['route'] ?? 'empty route' }}">
                                        <i class="fa-solid {{ $section['icon'] }}"></i>
                                        {{ $section['name'] }}
                                    </a>
                                @endif
                            @endcan
                        @endforeach
                    </div>
                </div>
            @endforeach
        </aside>

        <main class="content">

            <!-- COMPANY CARDS -->
            <div class="company-cards">
                @foreach ($companies as $company)
                    @php $codeClass = preg_replace('/[^A-Za-z0-9]/', '', strtoupper(trim($company['code']))); @endphp
                    <div class="card {{ $codeClass }}">
                        <h4>{{ $company['name'] }}</h4>
                        <span>{{ $company['code'] }}</span>
                    </div>
                @endforeach
            </div>

            <!-- COMPANY NEWS RIGHT BELOW CARDS -->
            <div class="company-news" id="newsPanel">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                    <h2 style="margin:0;">Company News</h2>
                    <a href="{{ route('news.index') }}"
                        style="font-size:12px; font-weight:700; text-decoration:none; color:#1e3a8a;">
                        View All
                    </a>
                </div>

                <div class="news-container" id="newsContainer">
                    @forelse($news as $item)
                        <div class="news-item">
                            <strong>{{ $item->title }}</strong>
                            <p>{{ $item->content }}</p>
                            <small>
                                {{ optional($item->created_at)->format('d-M-Y') }}
                                @if ($item->publish_at)
                                    | Publish: {{ $item->publish_at->format('d-M-Y') }}
                                @endif
                            </small>
                            <hr style="border-color:#e2e8f0;">
                        </div>
                    @empty
                        <div class="news-item">
                            <p>No active news available.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>

    </div>

    <footer>© {{ date('Y') }} Mbogo Mining and General Supply Limited</footer>

    <script>
        function toggleDropdown(id) {
            const el = document.getElementById(id);
            el.style.display = el.style.display === 'block' ? 'none' : 'block';
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        function toggleProfile() {
            const el = document.getElementById('profileDropdown');
            el.style.display = el.style.display === 'block' ? 'none' : 'block';
        }

        // NEWS AUTO SCROLL LOOP
        const newsContainer = document.getElementById('newsContainer');
        const newsPanel = document.getElementById('newsPanel');
        let scrollPos = 0;
        let speed = 0.6;
        let scrolling = true;

        function animateNews() {
            if (scrolling && newsContainer.scrollHeight > newsPanel.clientHeight) {
                scrollPos += speed;
                if (scrollPos >= newsContainer.scrollHeight) scrollPos = 0; // loop
                newsContainer.style.transform = `translateY(-${scrollPos}px)`;
            }
            requestAnimationFrame(animateNews);
        }

        // pause scroll on hover
        newsPanel.addEventListener('mouseenter', () => scrolling = false);
        newsPanel.addEventListener('mouseleave', () => scrolling = true);

        animateNews();
    </script>

</body>

</html>
