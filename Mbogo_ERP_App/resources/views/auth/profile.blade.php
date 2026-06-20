{{-- resources/views/auth/profile.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>MBOGO INFO APP+ — Profile</title>
  <link rel="shortcut icon" href="{{ asset('icon1.png') }}" />
  <style>
    * { box-sizing: border-box; margin:0; padding:0; }
    html,body { height:100%; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
      background: #071024;
      color: #fff;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }
    .bg {
      position: fixed; inset: 0;
      background-image: url("{{ asset('Mbogo_Back.jpeg') }}");
      background-size: cover; background-position: center center;
      z-index: -2; filter: brightness(0.45) saturate(0.9);
    }
    .overlay { position: fixed; inset: 0; background: linear-gradient(180deg, rgba(2,6,23,0.35), rgba(2,6,23,0.6)); z-index: -1; }
    .wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }

    .card {
      width:100%; max-width:900px; border-radius:14px; overflow:hidden; display:flex;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border: 1px solid rgba(255,255,255,0.04); box-shadow: 0 20px 50px rgba(2,6,23,0.65);
    }
    .left { flex: 0.95; padding:28px; background: linear-gradient(180deg, rgba(0,0,0,0.04), rgba(0,0,0,0.02)); }
    .right { width:320px; padding:28px; display:flex; flex-direction:column; gap:12px; align-items:center; justify-content:center; }

    .avatar {
      width:110px; height:110px; border-radius:14px; overflow:hidden; background:rgba(255,255,255,0.03);
      display:flex; align-items:center; justify-content:center; font-size:32px; color:rgba(255,255,255,0.9);
    }

    h2 { margin:0; color:#eaf9ff; font-size:20px; }
    .muted { color: rgba(255,255,255,0.66); font-size:14px; }

    .grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:18px; }
    .field { background: rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.03); padding:12px; border-radius:10px; }
    .label { font-size:12px; color: rgba(255,255,255,0.6); margin-bottom:6px; }
    .value { font-size:15px; color:#eaf9ff; }

    .btn { display:inline-block; padding:10px 14px; border-radius:10px; border:none; cursor:pointer; font-weight:600; font-size:14px; text-decoration:none; text-align:center; }
    .btn-primary { background: linear-gradient(90deg,#0ea5ff,#0fc8d6); color:#002031; box-shadow:0 8px 30px rgba(13,162,236,0.14); }
    .btn-ghost { background:transparent; border:1px solid rgba(255,255,255,0.06); color:#eaf9ff; }

    .meta { margin-top:10px; color: rgba(255,255,255,0.66); font-size:13px; }

    @media (max-width:880px) {
      .card { flex-direction:column; }
      .right { width:100%; order:-1; }
      .grid { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>
  @include('sweetalert::alert')
  <div class="bg" aria-hidden="true"></div>
  <div class="overlay" aria-hidden="true"></div>

  <div class="wrap">
    <div class="card" role="main" aria-labelledby="profileHeading">
      <div class="left" aria-labelledby="profileHeading">
        <h2 id="profileHeading">My Profile</h2>
        <div class="muted">Overview of your account details</div>

        <div class="grid">
          <div class="field">
            <div class="label">Full name</div>
            <div class="value">{{ $user->name ?? '-' }}</div>
          </div>
          <div class="field">
            <div class="label">Username</div>
            <div class="value">{{ $user->username ?? '-' }}</div>
          </div>

          <div class="field">
            <div class="label">Email</div>
            <div class="value">{{ $user->email ?? '-' }}</div>
          </div>
          <div class="field">
            <div class="label">Phone</div>
            <div class="value">{{ $user->phone_No ?? '-' }}</div>
          </div>

          <div class="field">
            <div class="label">Gender</div>
            <div class="value">{{ $user->gender ?? '-' }}</div>
          </div>
          <div class="field">
            <div class="label">Role</div>
            <div class="value">{{ $user->role ?? '-' }}</div>
          </div>

          <div class="field">
            <div class="label">Status</div>
            <div class="value">{{ $user->status ?? '-' }}</div>
          </div>
          <div class="field">
            <div class="label">Member since</div>
            <div class="value">{{ optional($user->created_at)->format('M d, Y') ?? '-' }}</div>
          </div>
        </div>

        <h3 style="margin-top:20px; color:#eaf9ff;">Company / Work Point</h3>
        <div class="grid" style="margin-top:8px;">
          <div class="field">
            <div class="label">Company</div>
            <div class="value">{{ optional($company)->company_name ?? '-' }}</div>
            <div class="meta">{{ optional($company)->location ? optional($company)->location . ' • ' . (optional($company)->status ?? '-') : '' }}</div>
          </div>

          <div class="field">
            <div class="label">Work point</div>
            <div class="value">{{ optional($workPoint)->work_name ?? '-' }}</div>
            <div class="meta">{{ optional($workPoint)->location ? optional($workPoint)->location . ' • ' . (optional($workPoint)->status ?? '-') : '' }}</div>
          </div>

          <div class="field">
            <div class="label">Company phone</div>
            <div class="value">{{ optional($company)->phone_No ?? '-' }}</div>
          </div>

          <div class="field">
            <div class="label">Work point phone</div>
            <div class="value">{{ optional($workPoint)->phone_No ?? '-' }}</div>
          </div>
        </div>

      </div>

      <div class="right" aria-hidden="false">
        <div class="avatar" aria-hidden="true">
          @if($user->image)
            <img src="{{ asset('storage/' . $user->image) }}" alt="avatar" style="width:100%; height:100%; object-fit:cover; border-radius:10px;">
          @else
            {{ strtoupper(substr($user->name ?? ($user->username ?? 'U'), 0, 1)) }}
          @endif
        </div>

        <div style="width:100%; display:flex; flex-direction:column; gap:8px;">
          <a href="javascript:history.back()" class="btn btn-ghost" role="button" aria-label="Go back">← Back</a>
          <a href="{{ route('change-password') }}" class="btn btn-primary" role="button">Change password</a>
          <a href="{{ route('profile.edit') }}" class="btn btn-ghost">Edit profile</a>
        </div>
        <div class="meta" style="margin-top:16px; text-align:center;">
          <div>Logged in as <strong>{{ $user->username }}</strong></div>
          <div style="margin-top:8px;">© 2020 - {{ date('Y') }} — Eng. Kivuyo</div>
        </div>
      </div>
    </div>
  </div>
    <!-- Custom context menu: allows native menu on links/images/inputs; encrypted view available on page areas -->
<style>
  #custom-cmenu {
    position: fixed;
    display: none;
    z-index: 2147483647;
    background: #ffffff;
    border-radius: 6px;
    box-shadow: 0 8px 30px rgba(0,0,0,.12);
    padding: 6px 0;
    min-width: 240px;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    font-size: 14px;
    color: #111;
  }
  #custom-cmenu .item { padding: 10px 16px; cursor: pointer; user-select: none; white-space: nowrap; }
  #custom-cmenu .item:hover { background: #f5f7fa; }
  #custom-cmenu .sep { height: 1px; background: #eee; margin: 6px 0; }
  #custom-cmenu .hint { font-size: 12px; color: #666; padding: 6px 14px; }
</style>

<div id="custom-cmenu" aria-hidden="true" role="menu">
  <div class="item" data-action="view-encrypted">View page source</div>
  <div class="item" data-action="inspect">Inspect</div>
  <div class="sep"></div>
  <div class="hint">Tip: press F12 or Ctrl+Shift+I to open DevTools</div>
</div>

<script>
(function(){
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
    cmenu.setAttribute('aria-hidden','true');
  }

  function showMenu(x,y) {
    var w = window.innerWidth || document.documentElement.clientWidth;
    var h = window.innerHeight || document.documentElement.clientHeight;
    var menuW = 280;
    var menuH = 160;
    if (x + menuW > w) x = Math.max(8, w - menuW - 8);
    if (y + menuH > h) y = Math.max(8, h - menuH - 8);
    cmenu.style.left = x + 'px';
    cmenu.style.top  = y + 'px';
    cmenu.style.display = 'block';
    cmenu.setAttribute('aria-hidden','false');
  }

  // Show custom menu on right-click only for page areas — allow native menu for links/images/inputs,
  // or when modifier keys are held (Shift or Ctrl)
  document.addEventListener('contextmenu', function(e){
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
  document.addEventListener('mousedown', function(e){
    if (!e.target.closest('#custom-cmenu')) hideMenu();
  }, false);

  // Keep DevTools available via keyboard (do NOT intercept F12 or Ctrl+Shift+I).
  // Intercept Ctrl+U (View Source) to open encrypted wrapper instead.
  window.addEventListener('keydown', function(e){
    // Ctrl+U or Cmd+U
    if ((e.ctrlKey && e.key.toLowerCase() === 'u') || (e.metaKey && e.key.toLowerCase() === 'u')) {
      e.preventDefault();
      openEncryptedView();
      return;
    }
    // Do not block F12 or Ctrl+Shift+I — allow normal DevTools opening.
  }, false);

  // Handle clicks on our custom menu
  cmenu.addEventListener('click', function(e){
    var item = e.target.closest('.item');
    if (!item) return;
    var action = item.getAttribute('data-action');

    if (action === 'view-encrypted') {
      openEncryptedView();
    } else if (action === 'view-browser-source') {
      openBrowserSource();
    } else if (action === 'inspect') {
      // We cannot programmatically open DevTools. Provide instructions.
      alert('To inspect elements and open DevTools, press F12 or Ctrl+Shift+I (Cmd+Opt+I on Mac).');
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
