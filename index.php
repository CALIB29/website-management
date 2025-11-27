<?php
// Mobile-first landing page for SRC Website Management (data-driven)
include 'database.php';

function column_exists($conn, $table, $column) {
    $t = $conn->real_escape_string($table);
    $c = $conn->real_escape_string($column);
    $res = $conn->query("SHOW COLUMNS FROM `{$t}` LIKE '{$c}'");
    $exists = ($res && $res->num_rows > 0);
    if ($res) $res->free();
    return $exists;
}
function table_exists($conn, $table) {
    $t = $conn->real_escape_string($table);
    $res = $conn->query("SHOW TABLES LIKE '{$t}'");
    $exists = ($res && $res->num_rows > 0);
    if ($res) $res->free();
    return $exists;
}

// counts (safe queries)
$managedSites = 0; $activeAdmins = 0; $uptimePercent = 99;
if ($r = @$conn->query("SELECT COUNT(*) AS cnt FROM websites")) { $row = $r->fetch_assoc(); $managedSites = (int)($row['cnt'] ?? 0); $r->free(); }
if ($r = @$conn->query("SELECT COUNT(*) AS cnt FROM admins")) { $row = $r->fetch_assoc(); $activeAdmins = (int)($row['cnt'] ?? 0); $r->free(); }

// uptime best-effort
if (column_exists($conn,'websites','uptime')) {
    if ($r = @$conn->query("SELECT AVG(uptime) AS avg_u FROM websites WHERE uptime IS NOT NULL")) {
        $row = $r->fetch_assoc(); if ($row && $row['avg_u'] !== null) $uptimePercent = round((float)$row['avg_u'],0); $r->free();
    }
} else {
    $uptimePercent = $managedSites ? 99 : 100;
}

// snapshot selection
$snapshot = 'images/dashboard-snapshot.png';
$candidates = ['screenshot_path','screenshot','thumbnail','thumb','image'];
foreach ($candidates as $col) {
    if (column_exists($conn,'websites',$col)) {
        $stmt = $conn->prepare("SELECT {$col} AS path FROM websites WHERE {$col} IS NOT NULL AND {$col} != '' LIMIT 1");
        if ($stmt) { $stmt->execute(); $res = $stmt->get_result(); if ($row = $res->fetch_assoc()) { $p = trim($row['path'] ?? ''); if ($p!=='') { $snapshot = $p; } } $stmt->close(); }
        break;
    }
}

// features
$features = ['Site previews & automation'];
if (table_exists($conn,'login_audit')||table_exists($conn,'unlock_audit')) $features[]='Login & unlock audit';
if (table_exists($conn,'login_attempts')) $features[]='Per-account throttling';
if (table_exists($conn,'admins')) $features[]='Role-based access';
if (table_exists($conn,'websites')) $features[]='Bulk export & thumbnails';
$features = array_values(array_slice(array_unique($features),0,6));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SRC — Mobile App Style Landing</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Mobile-app first landing tweaks */
    :root{--primary:#0b9fb0;--bg:#fbfeff;--muted:#6a8486}
    body{margin:0;background:var(--bg);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;color:#072b33;-webkit-font-smoothing:antialiased}
    .lp-wrap{max-width:420px;margin:0 auto;padding:18px 16px 96px}
    .lp-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
    .brand{display:flex;align-items:center;gap:10px}
    .brand-logo{height:36px;width:auto}
    .brand-title{font-weight:700;font-size:16px;margin:0;color:#072b33}
    .nav-login{font-size:14px;color:var(--primary);text-decoration:none;padding:8px}
    .hero{display:flex;flex-direction:column;align-items:center;text-align:center;gap:12px;padding:8px 0 18px}
    .hero-title{font-size:22px;margin:0;line-height:1.05}
    .hero-sub{color:var(--muted);font-size:14px;margin:0 0 6px}
    .hero-ctas{display:flex;gap:10px;width:100%}
    .btn{flex:1;padding:12px;border-radius:12px;border:none;background:var(--primary);color:#fff;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 8px 20px rgba(11,159,176,0.12)}
    .btn.ghost{background:#fff;border:1px solid rgba(7,50,58,0.06);color:#072b33;box-shadow:none}
    /* phone mock */
    .phone-mock{width:220px;height:440px;background:linear-gradient(180deg,#fff,#f6fdff);border-radius:34px;box-shadow:0 16px 40px rgba(2,12,27,0.12);overflow:hidden;position:relative;border:6px solid #0f2730}
    .phone-notch{height:26px;background:transparent;display:flex;align-items:center;justify-content:center}
    .phone-screen{height:100%;display:flex;flex-direction:column}
    .phone-snap{width:100%;height:100%;object-fit:cover;display:block}
    .lp-stats{display:flex;gap:8px;margin:14px 0}
    .stat{flex:1;background:#fff;padding:10px;border-radius:12px;text-align:center;box-shadow:0 6px 18px rgba(2,12,27,0.06)}
    .stat .num{font-weight:800;font-size:16px}
    .stat .lbl{font-size:12px;color:var(--muted);margin-top:4px}
    .features-list{margin:14px 0;display:flex;flex-direction:column;gap:10px}
    .feature{display:flex;gap:12px;align-items:flex-start;background:#fff;padding:12px;border-radius:12px;box-shadow:0 6px 18px rgba(2,12,27,0.04)}
    .feature .ico{width:40px;height:40;border-radius:10px;background:linear-gradient(180deg,#e6f8f8,#fff);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:18px}
    .feature .body{flex:1}
    .feature h4{margin:0;font-size:14px}
    .feature p{margin:6px 0 0;font-size:12px;color:var(--muted)}
    .badges{display:flex;gap:8px;margin-top:8px;justify-content:center}
    .badge{height:36px;padding:6px 12px;border-radius:8px;background:#fff;display:inline-flex;align-items:center;gap:10px;box-shadow:0 6px 18px rgba(2,12,27,0.04);font-size:13px}
    /* sticky bottom CTA */
    .bottom-cta{position:fixed;left:0;right:0;bottom:0;background:linear-gradient(90deg,rgba(11,159,176,0.08),rgba(11,159,176,0.02));padding:10px 16px;box-shadow:0 -6px 18px rgba(2,12,27,0.06);display:flex;gap:10px;align-items:center;justify-content:space-between}
    .bottom-cta .open-btn{flex:1;padding:12px;border-radius:12px;background:var(--primary);color:#fff;text-align:center;font-weight:700}
    .bottom-cta .txt{font-size:13px;color:#04282b;margin-right:12px}
    /* small animations */
    .fade-up{transform:translateY(12px);opacity:0;transition:all .48s cubic-bezier(.2,.9,.3,1)}
    .fade-up.visible{transform:none;opacity:1}
    @media (min-width:560px){ .lp-wrap{margin-top:22px} }
  </style>
</head>
<body>
  <div class="lp-wrap">
    <header class="lp-header fade-up" data-delay="0">
      <div class="brand">
        <img src="images/src_logo.png" class="brand-logo" alt="SRC">
        <div class="brand-title">SRC Website Management</div>
      </div>
      <a class="nav-login" href="login.php">Sign in</a>
    </header>

    <section class="hero fade-up" data-delay="50">
      <h1 class="hero-title">Manage websites and monitor.</h1>
      <p class="hero-sub">Centralized site previews, audits and safe unlock workflows — designed for mobile-first teams.</p>

      <div style="display:flex;gap:10px;width:100%;justify-content:center;margin-top:8px" class="hero-ctas">
        <a href="login.php" class="btn"><i class="fas fa-play"></i> Get Started</a>
        <a href="download.php" class="btn ghost"><i class="fas fa-download"></i> Download App</a>
      </div>

      <div style="margin-top:14px;display:flex;justify-content:center">
        <div class="phone-mock" aria-hidden="true">
          <div class="phone-notch"></div>
          <div class="phone-screen">
            <img src="<?php echo htmlspecialchars($snapshot); ?>" class="phone-snap" alt="dashboard snapshot">
          </div>
        </div>
      </div>
    </section>

    <div class="lp-stats fade-up" data-delay="120">
      <div class="stat">
        <div class="num" data-count="<?php echo (int)$managedSites; ?>"><?php echo (int)$managedSites; ?></div>
        <div class="lbl">Managed Sites</div>
      </div>
      <div class="stat">
        <div class="num" data-count="<?php echo (int)$activeAdmins; ?>"><?php echo (int)$activeAdmins; ?></div>
        <div class="lbl">Admins</div>
      </div>
      <div class="stat">
        <div class="num" data-count="<?php echo (int)$uptimePercent; ?>"><?php echo (int)$uptimePercent; ?>%</div>
        <div class="lbl">Uptime</div>
      </div>
    </div>

    <section class="features-list fade-up" data-delay="180">
      <?php foreach ($features as $f): ?>
        <div class="feature">
          <div class="ico"><i class="fas fa-check-circle"></i></div>
          <div class="body">
            <h4><?php echo htmlspecialchars($f); ?></h4>
            <p>Available in this installation.</p>
          </div>
        </div>
      <?php endforeach; ?>
    </section>

  </div>


  <script src="script.js"></script>
  <script>
    // simple reveal and counters for mobile landing
    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('.fade-up').forEach((el, i)=>{
        setTimeout(()=>el.classList.add('visible'), (parseInt(el.getAttribute('data-delay')||0)+i*40));
      });

      // counters: short animation
      document.querySelectorAll('.num[data-count]').forEach(el=>{
        const target = parseInt(el.getAttribute('data-count')||0,10);
        if (!target) return;
        const step = Math.max(1, Math.floor(target/40));
        let cur = 0;
        const iv = setInterval(()=>{ cur+=step; if (cur>=target){ el.textContent=target; clearInterval(iv);} else el.textContent=cur; }, 20);
      });
    });
  </script>
</body>
</html>