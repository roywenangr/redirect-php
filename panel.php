<?php
session_start();
$panelPassword = "admin123"; // Ganti sesuai keinginan

// === AUTH ===
if (isset($_POST['password'])) {
    if ($_POST['password'] === $panelPassword) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Password salah!";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: panel.php");
    exit;
}
if (!($_SESSION['logged_in'] ?? false)) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Panel</title>
    <style>
        body { font-family: sans-serif; background: #f9f9f9; text-align: center; margin-top: 100px; }
        form { display: inline-block; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input[type="password"] { padding: 10px; width: 200px; }
        button { padding: 10px 20px; }
    </style>
</head>
<body>
    <form method="post">
        <h2>Panel Login</h2>
        <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
<?php
exit;
}

// === LOAD DATA ===
$configFile = "config.json";
$config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : ['apple'=>[],'other'=>[]];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && in_array($_POST['action'], ['add', 'edit', 'delete'])) {
        $type = $_POST['type'];
        if ($_POST['action'] === 'add') {
            $config[$type][] = $_POST['url'];
        } elseif ($_POST['action'] === 'edit') {
            $index = $_POST['index'];
            $config[$type][$index] = $_POST['url'];
        } elseif ($_POST['action'] === 'delete') {
            $index = $_POST['index'];
            array_splice($config[$type], $index, 1);
        }
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        header("Location: panel.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirect Panel</title>
    <style>
        body { font-family: sans-serif; background: #f3f3f3; padding: 20px; }
        h2 { margin-top: 40px; }
        form, table { background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 40px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        input[type="url"], select { padding: 8px; width: 80%; }
        button { padding: 8px 14px; margin-left: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        a.logout { float: right; color: red; text-decoration: none; }
    </style>
</head>
<body>
    <h1>Redirect Control Panel <a href="?logout=1" class="logout">Logout</a></h1>

    <h2>Tambah Link Baru</h2>
    <form method="post">
        <input type="url" name="url" placeholder="https://example.com" required>
        <select name="type">
            <option value="apple">Apple</option>
            <option value="other">Other</option>
        </select>
        <input type="hidden" name="action" value="add">
        <button type="submit">Tambah</button>
    </form>

    <?php foreach (['apple', 'other'] as $type): ?>
        <h2>Daftar Redirect (<?= ucfirst($type) ?>)</h2>
        <form method="post">
            <table>
                <thead>
                    <tr><th>#</th><th>URL</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($config[$type] as $i => $url): ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="url" name="url" value="<?= htmlspecialchars($url) ?>" required style="width:70%">
                                    <input type="hidden" name="index" value="<?= $i ?>">
                                    <input type="hidden" name="type" value="<?= $type ?>">
                                    <input type="hidden" name="action" value="edit">
                                    <button type="submit">Simpan</button>
                                </form>
                            </td>
                            <td>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="index" value="<?= $i ?>">
                                    <input type="hidden" name="type" value="<?= $type ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" style="color:red">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </form>
    <?php endforeach; ?>

    <h2>Traffic Log</h2>
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>IP</th>
                <th>Device</th>
                <th>User-Agent</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $logPath = __DIR__ . '/clicklog.txt';
            if (file_exists($logPath)) {
                $lines = array_reverse(file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
                foreach ($lines as $line) {
                    [$time, $ip, $ua, $device] = explode('|', $line);
                    echo "<tr>
                        <td>" . htmlspecialchars($time) . "</td>
                        <td>" . htmlspecialchars($ip) . "</td>
                        <td>" . htmlspecialchars($device) . "</td>
                        <td style='word-break:break-word; max-width:400px'>" . htmlspecialchars($ua) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Belum ada log ditemukan.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>
