<?php
// Core functions for website security analysis

function get_website_url_by_id($conn, $id) {
    $stmt = $conn->prepare("SELECT url FROM websites WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['url'];
    }
    return null;
}

function analyze_url($url) {
    $result = [
        'ssl' => null,
        'headers' => null,
        'error' => null
    ];

    try {
        $result['ssl'] = get_ssl_info($url);
        $result['headers'] = get_security_headers($url);
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
    }

    return $result;
}

function get_ssl_info($url) {
    $parsed_url = parse_url($url);
    $host = $parsed_url['host'] ?? null;
    if (!$host) {
        return ['error' => 'Invalid URL provided.'];
    }

    $stream_context = stream_context_create([
        "ssl" => [
            "capture_peer_cert" => true,
            "verify_peer" => false, // We check validity manually
            "verify_peer_name" => false
        ]
    ]);

    $client = @stream_socket_client("ssl://{$host}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $stream_context);

    if (!$client) {
        return ['error' => "Couldn't connect to {$host}:443. This site may not have SSL."];
    }

    $params = stream_context_get_params($client);
    $cert_resource = $params["options"]["ssl"]["peer_certificate"] ?? null;

    if (!$cert_resource) {
        return ['error' => 'Could not retrieve SSL certificate.'];
    }

    $cert_info = openssl_x509_parse($cert_resource);

    $valid_from = date('Y-m-d H:i:s', $cert_info['validFrom_time_t']);
    $valid_to = date('Y-m-d H:i:s', $cert_info['validTo_time_t']);
    $is_expired = time() > $cert_info['validTo_time_t'];

    return [
        'issuer' => $cert_info['issuer']['O'] ?? 'N/A',
        'valid_from' => $valid_from,
        'valid_to' => $valid_to,
        'is_expired' => $is_expired,
        'subject' => $cert_info['subject']['CN'] ?? 'N/A'
    ];
}

function get_security_headers($url) {
    $headers_to_check = [
        'Content-Security-Policy' => false,
        'Strict-Transport-Security' => false,
        'X-Frame-Options' => false,
        'X-Content-Type-Options' => false,
        'Referrer-Policy' => false,
        'Permissions-Policy' => false
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return ['error' => 'Could not fetch headers from the URL.'];
    }

    $header_lines = explode("\r\n", $response);
    foreach ($header_lines as $line) {
        foreach ($headers_to_check as $key => $value) {
            if (stripos($line, $key . ':') === 0) {
                $headers_to_check[$key] = true;
            }
        }
    }

    return $headers_to_check;
}

function get_recommendations($analysis) {
    $recommendations = [];

    // SSL Recommendations
    if (isset($analysis['ssl']) && !isset($analysis['ssl']['error'])) {
        if ($analysis['ssl']['is_expired']) {
            $recommendations[] = [
                'priority' => 'High',
                'title' => 'Renew SSL Certificate',
                'description' => 'Your SSL certificate has expired. This will cause browsers to show security warnings to your visitors. Renew your certificate immediately to secure your site and maintain visitor trust.'
            ];
        } else {
            $valid_to = new DateTime($analysis['ssl']['valid_to']);
            $days_left = (new DateTime())->diff($valid_to)->format('%a');
            if ($days_left < 30) {
                $recommendations[] = [
                    'priority' => 'Medium',
                    'title' => 'Renew SSL Certificate Soon',
                    'description' => "Your SSL certificate will expire in {$days_left} days. It is recommended to renew it soon to avoid any service interruption."
                ];
            }
        }
    }

    // Security Header Recommendations
    if (isset($analysis['headers']) && !isset($analysis['headers']['error'])) {
        $header_info = [
            'Content-Security-Policy' => [
                'priority' => 'High',
                'title' => 'Implement Content-Security-Policy (CSP)',
                'description' => 'CSP helps prevent cross-site scripting (XSS) and other code injection attacks by specifying which dynamic resources are allowed to load. Example: `Content-Security-Policy: script-src \'self\';`'
            ],
            'Strict-Transport-Security' => [
                'priority' => 'High',
                'title' => 'Implement HTTP Strict-Transport-Security (HSTS)',
                'description' => 'HSTS tells browsers to only connect to your site using HTTPS, which helps prevent protocol downgrade attacks. Example: `Strict-Transport-Security: max-age=31536000; includeSubDomains`'
            ],
            'X-Frame-Options' => [
                'priority' => 'Medium',
                'title' => 'Implement X-Frame-Options',
                'description' => 'This header protects your site from being embedded in iframes on other sites, which can prevent clickjacking attacks. Example: `X-Frame-Options: SAMEORIGIN`'
            ],
            'X-Content-Type-Options' => [
                'priority' => 'Low',
                'title' => 'Implement X-Content-Type-Options',
                'description' => 'This header prevents browsers from MIME-sniffing a response away from the declared content-type, which can help prevent certain types of attacks. Example: `X-Content-Type-Options: nosniff`'
            ],
            'Referrer-Policy' => [
                'priority' => 'Low',
                'title' => 'Implement Referrer-Policy',
                'description' => 'This header controls how much referrer information is sent with requests, which can enhance user privacy. Example: `Referrer-Policy: strict-origin-when-cross-origin`'
            ],
            'Permissions-Policy' => [
                'priority' => 'Medium',
                'title' => 'Implement Permissions-Policy',
                'description' => 'This header allows you to control which browser features (like camera, microphone, geolocation) can be used on your site. Example: `Permissions-Policy: geolocation=(), camera=()`'
            ]
        ];

        foreach ($analysis['headers'] as $header => $is_present) {
            if (!$is_present && isset($header_info[$header])) {
                $recommendations[] = $header_info[$header];
            }
        }
    }

    return $recommendations;
}

?>
