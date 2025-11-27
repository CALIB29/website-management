<?php
include 'database.php';
include 'analyzer.php';

$website_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($website_id === 0) {
    die('Invalid website ID.');
}

$url = get_website_url_by_id($conn, $website_id);
if (!$url) {
    die('Website not found.');
}

$analysis = analyze_url($url);
$recommendations = get_recommendations($analysis);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Security Analysis Report</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root{--primary:#0b9fb0;--bg:#f0f4f5;--muted:#6a8486;--red:#e74c3c;--green:#2ecc71}
    body{margin:0;background:var(--bg);font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;color:#072b33;-webkit-font-smoothing:antialiased}
    .report-wrap{max-width:800px;margin:24px auto;padding:24px;background:#fff;border-radius:16px;box-shadow:0 8px 24px rgba(2,12,27,0.08)}
    .report-header{border-bottom:1px solid #e0e0e0;padding-bottom:16px;margin-bottom:24px}
    .report-title{font-size:24px;margin:0;color:#072b33}
    .report-url{font-size:16px;color:var(--primary);word-break:break-all}
    .section-title{font-size:20px;margin:24px 0 16px;color:#072b33}
    .info-grid{display:grid;grid-template-columns:200px 1fr;gap:12px;align-items:center}
    .info-grid .label{font-weight:600;color:var(--muted)}
    .info-grid .value{font-size:15px}
    .status-icon{margin-right:8px}
    .status-ok{color:var(--green)}
    .status-bad{color:var(--red)}
    .back-link{display:inline-block;margin-top:24px;color:var(--primary);text-decoration:none;font-weight:600}
    .error-box{background:#fffbe6;border:1px solid #ffe58f;padding:16px;border-radius:8px;color:#d46b08}
    .recommendation-card{background:#f9f9f9;border-left:4px solid;border-radius:4px;padding:16px;margin-bottom:16px}
    .recommendation-card.priority-High{border-left-color:var(--red)}
    .recommendation-card.priority-Medium{border-left-color:#f39c12}
    .recommendation-card.priority-Low{border-left-color:#3498db}
    .recommendation-title{font-size:16px;font-weight:600;margin:0 0 8px}
    .recommendation-description{font-size:14px;color:var(--muted);margin:0}
    .no-issues{background-color:#e8f5e9;color:#2e7d32;padding:16px;border-radius:8px;text-align:center;font-weight:600}
    .skeleton-loader { display: none; }
    .skeleton-loader.visible { display: block; }
    .skeleton { background-color: #e0e0e0; border-radius: 4px; position: relative; overflow: hidden; }
    .skeleton.text { height: 1em; }
    .skeleton.title { height: 24px; width: 60%; margin-bottom: 16px; }
    .skeleton.line { height: 16px; width: 100%; margin-bottom: 8px; }
    .skeleton.recommendation { height: 80px; width: 100%; margin-bottom: 16px; }
    .skeleton::after { content: ''; position: absolute; top: 0; left: -150%; width: 150%; height: 100%; background: linear-gradient(90deg, rgba(255,255,255,0) 0, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%); animation: shimmer 1.5s infinite; }
    @keyframes shimmer { 100% { left: 150%; } }
    .report-content{opacity:0;transition:opacity .5s ease-in-out}
    .report-content.visible{opacity:1}

    @media (max-width: 768px) {
        .report-wrap {
            margin: 12px;
            padding: 16px;
        }
        .report-title {
            font-size: 20px;
        }
        .section-title {
            font-size: 18px;
        }
        .info-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .info-grid .label {
            font-weight: 600;
            color: var(--muted);
        }
        .info-grid .value {
            margin-bottom: 12px; /* Add space between stacked items */
        }
    }
  </style>
</head>
<body>
  <div class="report-wrap skeleton-loader visible">
    <div class="report-header">
      <div class="skeleton title"></div>
      <div class="skeleton line"></div>
    </div>
    <section>
      <div class="skeleton title" style="width: 40%;"></div>
      <div class="skeleton line"></div>
      <div class="skeleton line"></div>
    </section>
    <section>
      <div class="skeleton title" style="width: 50%;"></div>
      <div class="skeleton line"></div>
      <div class="skeleton line"></div>
    </section>
    <section>
        <div class="skeleton title" style="width: 60%;"></div>
        <div class="skeleton recommendation"></div>
        <div class="skeleton recommendation"></div>
    </section>
  </div>

  <div class="report-wrap report-content">
    <div class="report-header">
      <h1 class="report-title">Security Analysis Report</h1>
      <p class="report-url"><a href="<?php echo htmlspecialchars($url); ?>" target="_blank"><?php echo htmlspecialchars($url); ?></a></p>
    </div>

    <?php if ($analysis['error']): ?>
      <div class="error-box">
        <strong><i class="fas fa-exclamation-triangle"></i> Analysis Failed:</strong> <?php echo htmlspecialchars($analysis['error']); ?>
      </div>
    <?php else: ?>
      <section>
        <h2 class="section-title">SSL Certificate</h2>
        <?php if (isset($analysis['ssl']['error'])): ?>
          <div class="error-box"><strong><i class="fas fa-exclamation-triangle"></i> SSL Error:</strong> <?php echo htmlspecialchars($analysis['ssl']['error']); ?></div>
        <?php else:
            $is_expired = $analysis['ssl']['is_expired'];
            $valid_to = new DateTime($analysis['ssl']['valid_to']);
            $days_left = (new DateTime())->diff($valid_to)->format('%a');
        ?>
          <div class="info-grid">
            <div class="label">Status</div>
            <div class="value <?php echo $is_expired ? 'status-bad' : 'status-ok'; ?>">
              <i class="fas <?php echo $is_expired ? 'fa-times-circle' : 'fa-check-circle'; ?> status-icon"></i>
              <?php echo $is_expired ? 'Expired' : 'Valid'; ?>
            </div>
            <div class="label">Issuer</div>
            <div class="value"><?php echo htmlspecialchars($analysis['ssl']['issuer']); ?></div>
            <div class="label">Subject</div>
            <div class="value"><?php echo htmlspecialchars($analysis['ssl']['subject']); ?></div>
            <div class="label">Expires On</div>
            <div class="value"><?php echo $valid_to->format('F j, Y'); ?> (<?php echo $days_left; ?> days left)</div>
          </div>
        <?php endif; ?>
      </section>

      <section>
        <h2 class="section-title">Security Headers</h2>
        <div class="info-grid">
          <?php foreach ($analysis['headers'] as $header => $is_present): ?>
            <div class="label"><?php echo htmlspecialchars($header); ?></div>
            <div class="value <?php echo $is_present ? 'status-ok' : 'status-bad'; ?>">
              <i class="fas <?php echo $is_present ? 'fa-check-circle' : 'fa-times-circle'; ?> status-icon"></i>
              <?php echo $is_present ? 'Present' : 'Missing'; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section>
        <h2 class="section-title">Recommendations</h2>
        <?php if (empty($recommendations)): ?>
            <div class="no-issues">
                <i class="fas fa-check-circle status-icon"></i> No immediate security issues found. Great job!
            </div>
        <?php else: ?>
            <?php foreach ($recommendations as $rec): ?>
                <div class="recommendation-card priority-<?php echo htmlspecialchars($rec['priority']); ?>">
                    <h3 class="recommendation-title"><i class="fas fa-exclamation-circle status-icon"></i> <?php echo htmlspecialchars($rec['title']); ?></h3>
                    <p class="recommendation-description"><?php echo htmlspecialchars($rec['description']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const skeletonLoader = document.querySelector('.skeleton-loader');
        const content = document.querySelector('.report-content');

        // This script runs after the PHP has finished and the page is loaded
        // We hide the skeleton and show the real content
        skeletonLoader.classList.remove('visible');
        skeletonLoader.style.display = 'none';
        content.classList.add('visible');
    });
  </script>
</body>
</html>
