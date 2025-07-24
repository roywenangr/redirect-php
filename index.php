<?php
$config = json_decode(file_get_contents("config.json"), true);
$counterFile = "counter.json";
$counter = file_exists($counterFile) ? json_decode(file_get_contents($counterFile), true) : ['apple' => 0, 'other' => 0];

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

// === BLOCKER: IP COUNTRY FIRST ===
$ipinfoToken = ''; // Optional IPInfo.io token
$infoUrl = "http://ipinfo.io/{$ip}/json" . ($ipinfoToken ? "?token={$ipinfoToken}" : '');
$ipInfo = @json_decode(@file_get_contents($infoUrl), true);
$org = $ipInfo['org'] ?? '';
$country = $ipInfo['country'] ?? '';

$blockedCountries = ['RU', 'CN', 'KP', 'IR']; // Contoh negara yang diblokir
if (in_array($country, $blockedCountries)) {
    redirectToOther("Access denied: country blocked");
}

// === BLOCKER: VPN / HOSTING ===
$vpnKeywords = ['DigitalOcean', 'Amazon', 'OVH', 'Google', 'Hetzner', 'Microsoft', 'VPN', 'Hostinger', 'Alibaba'];
foreach ($vpnKeywords as $kw) {
    if (stripos($org, $kw) !== false) {
        redirectToOther("Access denied: VPN/hosting detected");
    }
}

// === BLOCKER: BOT ===
$botKeywords = ['bot', 'crawl', 'spider', 'slurp', 'fetch', 'scan', 'curl', 'wget', 'python', 'scrapy'];
foreach ($botKeywords as $bot) {
    if (stripos($userAgent, $bot) !== false) {
        redirectToOther("Access denied: Bot user-agent");
    }
}

// === USER AGENT VALIDATION ===
$deviceType = preg_match('/iPhone|iPad|iPod|Macintosh|Mac OS X/i', $userAgent) ? 'apple' : 'other';

// === LOGGING ===
$logFile = __DIR__ . '/clicklog.txt';
$timestamp = date('Y-m-d H:i:s');
$logEntry = "$timestamp|$ip|$country|$userAgent|$deviceType" . PHP_EOL;
file_put_contents($logFile, $logEntry, FILE_APPEND);

// === REDIRECT ===
$urls = $config[$deviceType] ?? [];
$index = $counter[$deviceType] ?? 0;
$redirectUrl = $urls[$index] ?? $urls[0] ?? null;

if ($redirectUrl) {
    $counter[$deviceType] = ($index + 1) % count($urls);
    file_put_contents($counterFile, json_encode($counter));
    header("Location: $redirectUrl", true, 302);
    exit;
} else {
    http_response_code(500);
    echo "No redirect URL defined.";
    exit;
}

// === FUNCTION ===
function redirectToOther($reason = '') {
    global $config, $counterFile, $counter, $logFile, $ip, $userAgent, $country;
    $urls = $config['other'] ?? [];
    $index = $counter['other'] ?? 0;
    $redirectUrl = $urls[$index] ?? $urls[0] ?? null;

    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "$timestamp|$ip|$country|$userAgent|BLOCKED: $reason" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    if ($redirectUrl) {
        $counter['other'] = ($index + 1) % count($urls);
        file_put_contents($counterFile, json_encode($counter));
        header("Location: $redirectUrl", true, 302);
        exit;
    } else {
        http_response_code(403);
        echo "Access denied.";
        exit;
    }
}
