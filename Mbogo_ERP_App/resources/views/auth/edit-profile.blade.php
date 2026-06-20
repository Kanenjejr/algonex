{{-- resources/views/auth/edit-profile.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>MBOGO INFO APP+ — Edit Profile</title>
  <link rel="shortcut icon" href="{{ asset('icon1.png') }}" />
  <style>
    /* Reuse styling pattern from your other views (kept minimal here) */
    * { box-sizing:border-box; margin:0; padding:0; }
    html,body { height:100%; }
    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background:#071024; color:#fff; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; }
    .wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
    .card { width:100%; max-width:900px; border-radius:14px; padding:28px; background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border:1px solid rgba(255,255,255,0.04); box-shadow:0 20px 50px rgba(2,6,23,0.65); }
    h2 { margin:0 0 8px 0; color:#eaf9ff; }
    .muted { color: rgba(255,255,255,0.66); font-size:14px; }
    .form-row { display:flex; gap:12px; margin-top:12px; }
    .col { flex:1; }
    .form-group { margin-top:8px; position:relative; }
    input.form-input, select.form-input { width:100%; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,0.06); background:rgba(255,255,255,0.02); color:#eaf9ff; outline:none; }
    label { display:block; font-size:13px; color: rgba(255,255,255,0.7); margin-bottom:6px; }
    .avatar-preview { width:120px; height:120px; border-radius:8px; overflow:hidden; background:rgba(255,255,255,0.03); display:flex; align-items:center; justify-content:center; font-size:36px; color:#fff; }
    .btn { padding:10px 14px; border-radius:10px; border:none; cursor:pointer; font-weight:600; font-size:14px; }
    .btn-primary { background: linear-gradient(90deg,#0ea5ff,#0fc8d6); color:#002031; }
    .btn-ghost { background:transparent; border:1px solid rgba(255,255,255,0.06); color:#eaf9ff; }
    .hint { font-size:13px; color: rgba(255,255,255,0.64); margin-top:6px; }
    .error { color:#ffb4b4; font-size:13px; margin-top:6px; }
    @media (max-width:880px) { .form-row { flex-direction:column; } .avatar-preview { margin-bottom:10px; } }
  </style>
</head>
<body>
  @include('sweetalert::alert')
  <div class="wrap">
    <div class="card">
      <h2>Edit Profile</h2>
      <div class="muted">Update your personal details and profile picture</div>

      {{-- show validation errors --}}
      @if ($errors->any())
        <div class="error" style="margin-top:12px;">
          <strong>There were some problems with your input:</strong>
          <ul style="margin-top:8px;">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="margin-top:14px;">
        @csrf
        @method('PUT')
        <div class="form-row">
          <div class="col">
            <div class="form-group">
              <label for="name">Full name</label>
              <input id="name" name="name" type="text" class="form-input" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
              <label for="username">Username</label>
              <input id="username" name="username" type="text" class="form-input" value="{{ old('username', $user->username) }}" required>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" class="form-input" value="{{ old('email', $user->email) }}">
            </div>

            <div class="form-row" style="margin-top:8px;">
              <div class="col">
                <div class="form-group">
                  <label for="phone_No">Phone</label>
                  <input id="phone_No" name="phone_No" type="text" class="form-input" value="{{ old('phone_No', $user->phone_No) }}">
                </div>
              </div>
              <div class="col">
                <div class="form-group">
                  <label for="gender">Gender</label>
                  <select id="gender" name="gender" class="form-input"disabled>
                    <option value="">Select</option>
                    <option value="Male" {{ old('gender', $user->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender', $user->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="company_id">Company</label>
              <select id="company_id" name="company_id" class="form-input"disabled>
                <option value="">-- none --</option>
                @foreach($companies as $c)
                  <option value="{{ $c->id }}" {{ (old('company_id', $user->company_id) == $c->id) ? 'selected' : '' }}>
                    {{ $c->company_name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="work_point_id">Work point</label>
              <select id="work_point_id" name="work_point_id" class="form-input"disabled>
                <option value="">-- none --</option>
                @foreach($workPoints as $w)
                  <option value="{{ $w->id }}" {{ (old('work_point_id', $user->work_point_id) == $w->id) ? 'selected' : '' }}>
                    {{ $w->work_name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div style="width:200px; display:flex; flex-direction:column; gap:12px; align-items:center; justify-content:flex-start;">
            <div class="avatar-preview" id="avatarPreview">
              @if($user->image)
                <img src="{{ asset('storage/' . $user->image) }}" alt="avatar" style="width:100%; height:100%; object-fit:cover;">
              @else
                {{ strtoupper(substr($user->name ?? ($user->username ?? 'U'), 0, 1)) }}
              @endif
            </div>

            <div style="width:100%;">
              <label for="Image">Profile image</label>
              <input id="Image" name="Image" type="file" accept="image/*" class="form-input" style="padding:8px;">
              <div class="hint">Images are stored in <code>public/storage</code>. Max 4MB.</div>
            </div>

            <div style="width:100%; display:flex; gap:8px;">
              <a href="{{ route('profile') }}" class="btn btn-ghost" style="flex:1; text-align:center;">Cancel</a>
              <button type="submit" class="btn btn-primary" style="flex:1;">Save</button>
            </div>
          </div>
        </div>

      </form>
    </div>
  </div>

  <script>
    // client-side preview for convenience
    document.getElementById('Image').addEventListener('change', function(ev){
      const [file] = ev.target.files;
      if (!file) return;
      const img = document.createElement('img');
      img.style.width = '100%';
      img.style.height = '100%';
      img.style.objectFit = 'cover';
      const preview = document.getElementById('avatarPreview');
      const reader = new FileReader();
      reader.onload = function(e){
        preview.innerHTML = '';
        img.src = e.target.result;
        preview.appendChild(img);
      }
      reader.readAsDataURL(file);
    });
  </script>
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
