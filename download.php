<?php
$file_path = 'apk/Web+Management-0_5_debug.apk';
$file_name = basename($file_path);
$file_size = file_exists($file_path) ? round(filesize($file_path) / 1024 / 1024, 2) . ' MB' : 'N/A';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Download SRC Web Management App</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root{--primary:#0b9fb0;--bg:#fbfeff;--muted:#6a8486}
    body{margin:0;background:var(--bg);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;color:#072b33;-webkit-font-smoothing:antialiased;display:flex;align-items:center;justify-content:center;min-height:100vh}
    .download-wrap{max-width:420px;margin:0 auto;padding:18px 16px;text-align:center}
    .brand-logo{height:80px;width:auto;margin-bottom:24px}
    .download-title{font-size:24px;margin:0 0 12px;line-height:1.2;color:#072b33}
    .download-sub{color:var(--muted);font-size:15px;margin:0 0 24px}
    .btn{display:inline-flex;padding:14px 28px;border-radius:14px;border:none;background:var(--primary);color:#fff;font-weight:700;text-decoration:none;align-items:center;justify-content:center;gap:10px;box-shadow:0 8px 20px rgba(11,159,176,0.15);font-size:16px;transition:all .2s ease}
    .btn:hover{transform:translateY(-2px);box-shadow:0 12px 24px rgba(11,159,176,0.2)}
    .file-info{font-size:13px;color:var(--muted);margin-top:16px}
  </style>
</head>
<body>
  <div class="download-wrap">
    <img src="images/src_logo.png" class="brand-logo" alt="SRC Logo">
    <h1 class="download-title">Download SRC Web Management</h1>
    <p class="download-sub">Get the latest version of our Android application for seamless website management on the go.</p>
    <a href="<?php echo htmlspecialchars($file_path); ?>" class="btn" download>
      <i class="fas fa-download"></i> Download APK
    </a>
    <p class="file-info">Version 0.5 (Debug) &bull; Size: <?php echo $file_size; ?></p>
    <p style="margin-top: 24px;"><a href="index.php" style="font-size:14px; color: var(--primary); text-decoration:none;"><i class="fas fa-arrow-left"></i> Back to Home</a></p>
  </div>
</body>
</html>
