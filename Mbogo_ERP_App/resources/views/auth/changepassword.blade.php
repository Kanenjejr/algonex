{{-- resources/views/auth/change-password.blade.php --}}
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>MBOGO INFO APP+ — Change Password</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('icon1.png') }}" />

    <style>
        /* KEEP STYLES CONSISTENT WITH login.blade.php (trimmed/adjusted) */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #071024;
            color: #fff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .bg {
            position: fixed;
            inset: 0;
            background-image: url("{{ asset('Erp.jpeg') }}");
            background-size: cover;
            background-position: center center;
            z-index: -2;
            filter: brightness(0.45) saturate(0.9);
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(180deg, rgba(2, 6, 23, 0.35), rgba(2, 6, 23, 0.6));
            z-index: -1;
        }

        .wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            width: 100%;
            max-width: 960px;
            display: flex;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(2, 6, 23, 0.65);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.04);
            transform: translateY(6px);
            animation: pop 450ms ease both;
        }

        @keyframes pop {
            from {
                opacity: 0;
                transform: translateY(16px) scale(.995);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .visual {
            flex: 1.05;
            min-width: 300px;
            padding: 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 14px;
            color: #fff;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.08), rgba(0, 0, 0, 0.04));
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.04);
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.45);
        }

        .logo img {
            max-width: 56px;
            max-height: 56px;
            display: block;
        }

        .visual h1 {
            font-size: 26px;
            margin: 0;
            letter-spacing: 0.2px;
        }

        .visual p {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.45;
            font-size: 15px;
            max-width: 460px;
        }

        .form-panel {
            flex: 0.95;
            min-width: 300px;
            padding: 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
        }

        .form-panel h2 {
            margin: 0;
            color: #eaf9ff;
            font-size: 20px;
        }

        .form-panel small {
            color: rgba(255, 255, 255, 0.64);
        }

        .form-group {
            margin-top: 10px;
            position: relative;
        }

        input.form-input,
        textarea.form-input {
            width: 100%;
            padding: 12px 44px 12px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(255, 255, 255, 0.02);
            color: #eaf9ff;
            font-size: 15px;
            outline: none;
            transition: box-shadow .12s, border-color .12s;
        }

        input.form-input::placeholder {
            color: rgba(255, 255, 255, 0.45);
        }

        input.form-input:focus {
            border-color: rgba(0, 180, 255, 0.6);
            box-shadow: 0 6px 18px rgba(0, 180, 255, 0.06);
        }

        .btn {
            display: inline-block;
            padding: 11px 14px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
        }

        .btn-primary {
            background: linear-gradient(90deg, #0ea5ff, #0fc8d6);
            color: #002031;
            box-shadow: 0 8px 30px rgba(13, 162, 236, 0.14);
            width: 100%;
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #eaf9ff;
            padding: 8px 10px;
            border-radius: 10px;
        }

        .sr-only {
            position: absolute !important;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .pw-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.6);
            padding: 6px;
            border-radius: 8px;
        }

        /* Strength bar */
        .strength {
            height: 10px;
            width: 100%;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 8px;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .strength>i {
            display: block;
            height: 100%;
            width: 0%;
            transition: width .2s ease, background-color .2s ease;
            border-radius: 10px;
        }

        .strength-text {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 6px;
        }

        .error {
            color: #ffb4b4;
            font-size: 13px;
            margin-top: 8px;
        }

        @media (max-width:880px) {
            .card {
                flex-direction: column;
                max-width: 760px;
            }

            .visual {
                order: -1;
                text-align: center;
                align-items: center;
                padding: 22px;
            }

            .form-panel {
                padding: 22px;
            }

            input.form-input {
                padding-right: 40px;
            }
        }
    </style>
</head>

<body>
    @include('sweetalert::alert')
    <div class="bg" aria-hidden="true"></div>
    <div class="overlay" aria-hidden="true"></div>

    <div class="wrap">
        <div class="card" role="main" aria-labelledby="changePwHeading">
            <!-- Visual -->
            <div class="visual" aria-hidden="false">
                <div class="logo-wrap">
                    <div class="logo" aria-hidden="true">
                        <img src="{{ asset('icon1.png') }}" alt="ERP icon">
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:18px;">MBOGO INFO APP+</div>
                        <div style="color:rgba(255,255,255,0.62); font-size:13px;">Account settings</div>
                    </div>
                </div>

                <h1 id="changePwHeading">Change your password</h1>
                <p>For safety, enter your current password, then choose a new strong password and confirm it.</p>
                <div class="foot">Built & maintained by Eng. Kivuyo</div>
            </div>

            <!-- Form -->
            <div class="form-panel" aria-label="Change password form">
                <h2>Update Password</h2>
                <small>Current password is required</small>

                {{-- Replace route name as appropriate --}}
                <form id="changePwForm" method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <div class="form-group" style="margin-top:12px;">
                        <label for="current_password" class="sr-only">Current password</label>
                        <input id="current_password" name="current_password" type="password"
                            autocomplete="current-password" placeholder="Current password" required class="form-input"
                            aria-describedby="currentHelp">
                        <button type="button" class="pw-toggle" data-target="current_password"
                            title="Show/Hide">👁️</button>
                        <div id="currentHelp" class="strength-text" style="margin-top:6px;color:rgba(255,255,255,0.6);">
                            Enter your current password to proceed.</div>
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <label for="new_password" class="sr-only">New password</label>
                        <input id="new_password" name="new_password" type="password" autocomplete="new-password"
                            placeholder="New password" required class="form-input" aria-describedby="strengthHelp">
                        <button type="button" class="pw-toggle" data-target="new_password"
                            title="Show/Hide">👁️</button>

                        <div class="strength" aria-hidden="false" id="strengthBar" role="progressbar" aria-valuemin="0"
                            aria-valuemax="100" aria-valuenow="0">
                            <i id="strengthFill" style="width:0%"></i>
                        </div>
                        <div id="strengthHelp" class="strength-text">Password strength: <span id="strengthText">—</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <label for="new_password_confirmation" class="sr-only">Confirm new password</label>
                        <input id="new_password_confirmation" name="new_password_confirmation" type="password"
                            autocomplete="new-password" placeholder="Confirm new password" required class="form-input">
                        <button type="button" class="pw-toggle" data-target="new_password_confirmation"
                            title="Show/Hide">👁️</button>
                        <div id="matchHelp" class="strength-text" style="margin-top:6px;">Must match the new password
                            exactly.</div>
                    </div>

                    <div id="formError" class="error" role="alert" aria-live="polite" style="display:none;"></div>

                    <div style="margin-top:16px;">
                        <button class="btn btn-primary" type="submit" id="submitBtn" disabled>Change password</button>
                    </div>
                </form>

                <div style="margin-top:14px;" class="foot">© 2020 - {{ date('Y') }} — Eng. Kivuyo</div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            // Elements
            const current = document.getElementById('current_password');
            const newPwd = document.getElementById('new_password');
            const conf = document.getElementById('new_password_confirmation');
            const submitBtn = document.getElementById('submitBtn');
            const formError = document.getElementById('formError');

            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            const strengthBar = document.getElementById('strengthBar');

            // pw toggle buttons
            document.querySelectorAll('.pw-toggle').forEach(btn => {
                btn.addEventListener('click', () => {
                    const t = document.getElementById(btn.dataset.target);
                    if (!t) return;
                    t.type = (t.type === 'password') ? 'text' : 'password';
                    btn.textContent = (t.type === 'password') ? '👁️' : '🙈';
                });
            });

            // Password strength scoring (simple, deterministic)
            function scorePassword(pw) {
                // score: 0..4
                let score = 0;
                if (!pw) return score;
                if (pw.length >= 8) score++;
                if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
                if (/\d/.test(pw)) score++;
                if (/[^A-Za-z0-9]/.test(pw)) score++;
                // additional: length bonus
                if (pw.length >= 12) score = Math.min(4, score + 1);
                return Math.min(4, score);
            }

            function strengthToStyle(score) {
                // returns {pct, text, color}
                switch (score) {
                    case 0:
                        return {
                            pct: 0, text: 'Too short', color: '#ff4d4f'
                        };
                    case 1:
                        return {
                            pct: 25, text: 'Weak', color: '#ff7a45'
                        };
                    case 2:
                        return {
                            pct: 50, text: 'Fair', color: '#ffd666'
                        };
                    case 3:
                        return {
                            pct: 75, text: 'Good', color: '#73d13d'
                        };
                    case 4:
                        return {
                            pct: 100, text: 'Strong', color: '#36b37e'
                        };
                    default:
                        return {
                            pct: 0, text: '—', color: '#ff4d4f'
                        };
                }
            }

            function updateStrengthUI() {
                const pw = newPwd.value;
                const s = scorePassword(pw);
                const st = strengthToStyle(s);
                strengthFill.style.width = st.pct + '%';
                strengthFill.style.backgroundColor = st.color;
                strengthText.textContent = st.text;
                strengthBar.setAttribute('aria-valuenow', st.pct);
                return s;
            }

            function checkMatch() {
                if (!conf.value && !newPwd.value) return null;
                return newPwd.value === conf.value;
            }

            function validateForm() { // returns {ok: boolean, message: string}
                if (!current.value.trim()) return {
                    ok: false,
                    message: 'Enter your current password.'
                };
                if (!newPwd.value) return {
                    ok: false,
                    message: 'Enter a new password.'
                };
                const s = updateStrengthUI();
                // require at least 'Fair' (score >= 2) — adjust as you like
                if (s < 2) return {
                    ok: false,
                    message: 'Choose a stronger password (length, mixed case, numbers/symbols).'
                };
                if (newPwd.value !== conf.value) return {
                    ok: false,
                    message: 'New password and confirmation do not match.'
                };
                // disallow same as current (optional)
                if (current.value && current.value === newPwd.value) return {
                    ok: false,
                    message: 'New password must be different from current password.'
                };
                return {
                    ok: true,
                    message: ''
                };
            }

            // enable/disable submit based on live checks
            function refreshSubmitState() {
                const v = validateForm();
                if (v.ok) {
                    submitBtn.disabled = false;
                    formError.style.display = 'none';
                    formError.textContent = '';
                } else {
                    submitBtn.disabled = true;
                    formError.style.display = v.message ? 'block' : 'none';
                    formError.textContent = v.message || '';
                }
            }

            // wire events
            newPwd.addEventListener('input', () => {
                updateStrengthUI();
                refreshSubmitState();
            });
            conf.addEventListener('input', () => {
                refreshSubmitState();
            });
            current.addEventListener('input', () => {
                refreshSubmitState();
            });

            // on submit: final client-side validation + UI
            document.getElementById('changePwForm').addEventListener('submit', function(e) {
                const v = validateForm();
                if (!v.ok) {
                    e.preventDefault();
                    formError.style.display = 'block';
                    formError.textContent = v.message;
                    // subtle card shake
                    const c = document.querySelector('.card');
                    if (c && c.animate) {
                        c.animate([{
                            transform: 'translateX(0)'
                        }, {
                            transform: 'translateX(-8px)'
                        }, {
                            transform: 'translateX(8px)'
                        }, {
                            transform: 'translateX(0)'
                        }], {
                            duration: 260,
                            easing: 'ease-in-out'
                        });
                    }
                    return false;
                }

                // disable button to prevent double submit, show spinner text
                submitBtn.disabled = true;
                submitBtn.textContent = 'Updating...';

                // allow form to submit to server
            });
            // initialize UI on load
            updateStrengthUI();
            refreshSubmitState();
        })();
    </script>
    <!-- Custom context menu: allows native menu on links/images/inputs; encrypted view available on page areas -->
    <style>
        #custom-cmenu {
            position: fixed;
            display: none;
            z-index: 2147483647;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
            padding: 6px 0;
            min-width: 240px;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            font-size: 14px;
            color: #111;
        }

        #custom-cmenu .item {
            padding: 10px 16px;
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
        }

        #custom-cmenu .item:hover {
            background: #f5f7fa;
        }

        #custom-cmenu .sep {
            height: 1px;
            background: #eee;
            margin: 6px 0;
        }

        #custom-cmenu .hint {
            font-size: 12px;
            color: #666;
            padding: 6px 14px;
        }
    </style>

    <div id="custom-cmenu" aria-hidden="true" role="menu">
        <div class="item" data-action="view-encrypted">View page source</div>
        <div class="item" data-action="inspect">Inspect</div>
        <div class="sep"></div>
        <div class="hint">Tip: press F12 or Ctrl+Shift+I to open DevTools</div>
    </div>

    <script>
        (function() {
            var cmenu = document.getElementById('custom-cmenu');

            function openEncryptedView() {
                var path = window.location.pathname || '/';
                var search = window.location.search || '';
                var url = path + (search || '') + (search ? '&' : '?') + '__encrypt_view=1';
                // Open in new tab and do not allow the new tab to access this opener
                window.open(url, '_blank', 'noopener');
                hideMenu();
            }

            function openBrowserSource() {
                try {
                    // Most browsers support view-source: scheme
                    window.open('view-source:' + window.location.href, '_blank', 'noopener');
                } catch (e) {
                    // Fallback: open normal page in a new tab
                    window.open(window.location.href, '_blank', 'noopener');
                }
                hideMenu();
            }

            function hideMenu() {
                cmenu.style.display = 'none';
                cmenu.setAttribute('aria-hidden', 'true');
            }

            function showMenu(x, y) {
                var w = window.innerWidth || document.documentElement.clientWidth;
                var h = window.innerHeight || document.documentElement.clientHeight;
                var menuW = 280;
                var menuH = 160;
                if (x + menuW > w) x = Math.max(8, w - menuW - 8);
                if (y + menuH > h) y = Math.max(8, h - menuH - 8);
                cmenu.style.left = x + 'px';
                cmenu.style.top = y + 'px';
                cmenu.style.display = 'block';
                cmenu.setAttribute('aria-hidden', 'false');
            }

            // Show custom menu on right-click only for page areas — allow native menu for links/images/inputs,
            // or when modifier keys are held (Shift or Ctrl)
            document.addEventListener('contextmenu', function(e) {
                var tgt = e.target;

                // Allow native menu for links, images, and form fields
                if (
                    tgt.closest('a') ||
                    tgt.closest('img') ||
                    tgt.closest('input, textarea, select') ||
                    tgt.isContentEditable ||
                    e.shiftKey || e.ctrlKey // user wants native behavior
                ) {
                    return; // don't block native context menu
                }

                // For all other elements, show our custom menu
                e.preventDefault();
                showMenu(e.clientX, e.clientY);
            }, false);

            // Hide on mouse down outside the menu
            document.addEventListener('mousedown', function(e) {
                if (!e.target.closest('#custom-cmenu')) hideMenu();
            }, false);

            // Keep DevTools available via keyboard (do NOT intercept F12 or Ctrl+Shift+I).
            // Intercept Ctrl+U (View Source) to open encrypted wrapper instead.
            window.addEventListener('keydown', function(e) {
                // Ctrl+U or Cmd+U
                if ((e.ctrlKey && e.key.toLowerCase() === 'u') || (e.metaKey && e.key.toLowerCase() === 'u')) {
                    e.preventDefault();
                    openEncryptedView();
                    return;
                }
                // Do not block F12 or Ctrl+Shift+I — allow normal DevTools opening.
            }, false);

            // Handle clicks on our custom menu
            cmenu.addEventListener('click', function(e) {
                var item = e.target.closest('.item');
                if (!item) return;
                var action = item.getAttribute('data-action');

                if (action === 'view-encrypted') {
                    openEncryptedView();
                } else if (action === 'view-browser-source') {
                    openBrowserSource();
                } else if (action === 'inspect') {
                    // We cannot programmatically open DevTools. Provide instructions.
                    alert(
                        'To inspect elements and open DevTools, press F12 or Ctrl+Shift+I (Cmd+Opt+I on Mac).'
                    );
                    hideMenu();
                } else {
                    hideMenu();
                }
            });

            // Hide the menu when window loses focus or is resized
            window.addEventListener('resize', hideMenu);
            window.addEventListener('blur', hideMenu);
        })();
    </script>
</body>

</html>
