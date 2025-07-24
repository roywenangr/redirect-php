<?php
session_start();
$password = "admin123";

// Simple login
if (!isset($_SESSION['logged_in'])) {
    if (isset($_POST['password']) && $_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
    } else {
        echo '<form method="POST"><input type="password" name="password" placeholder="Password"><button>Login</button></form>';
        exit;
    }
}

$configFile = "config.json";
$config = json_decode(file_get_contents($configFile), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $type = $_POST['type'];
    if ($_POST['action'] === 'add') {
        $url = trim($_POST['url']);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $config[$type][] = $url;
        }
    } elseif ($_POST['action'] === 'delete') {
        $index = intval($_POST['index']);
        unset($config[$type][$index]);
        $config[$type] = array_values($config[$type]);
    }
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    header("Location: panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Redirect Manager Panel</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: auto; padding: 20px; }
        h2 { border-bottom: 1px solid #ccc; }
        input[type="text"] { width: 80%; padding: 5px; }
        button { padding: 5px 10px; margin-left: 10px; }
        form.inline { display: inline; }
        .section { margin-bottom: 30px; }
    </style>
</head>
<body>
    <h1>Redirect Manager Panel</h1>

    <?php foreach (['apple', 'other'] as $type): ?>
        <div class="section">
            <h2><?= ucfirst($type) ?> Links</h2>
            <ul>
                <?php foreach ($config[$type] as $i => $url): ?>
                    <li>
                        <?= htmlspecialchars($url) ?>
                        <form class="inline" method="POST">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="type" value="<?= $type ?>">
                            <input type="hidden" name="index" value="<?= $i ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="type" value="<?= $type ?>">
                <input type="text" name="url" placeholder="https://example.com" required>
                <button type="submit">Add</button>
            </form>
        </div>
    <?php endforeach; ?>
</body>
</html>
