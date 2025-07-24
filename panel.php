<?php
session_start();
$password = 'admin123';

// Login logic
if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['password'] ?? '') === $password) {
        $_SESSION['logged_in'] = true;
        header("Location: panel.php");
        exit;
    }
    echo '<form method="POST" style="max-width:300px;margin:100px auto;text-align:center;">
        <h2>Login Panel</h2>
        <input type="password" name="password" placeholder="Enter password" style="width:100%;padding:10px;margin:10px 0;">
        <button type="submit" style="padding:10px 20px;">Login</button>
    </form>';
    exit;
}

$configFile = "config.json";
$config = json_decode(file_get_contents($configFile), true);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $type = $_POST['type'];
    $index = intval($_POST['index'] ?? -1);

    if ($_POST['action'] === 'add') {
        $url = trim($_POST['url']);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $config[$type][] = $url;
        }
    }

    if ($_POST['action'] === 'edit' && isset($config[$type][$index])) {
        $url = trim($_POST['url']);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $config[$type][$index] = $url;
        }
    }

    if ($_POST['action'] === 'delete' && isset($config[$type][$index])) {
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
    <title>Redirect Panel</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f5f7fa;
            padding: 20px;
            max-width: 800px;
            margin: auto;
        }
        h1 {
            text-align: center;
        }
        .section {
            background: white;
            padding: 20px;
            margin: 30px 0;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background: #f1f3f5;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        form.inline {
            display: inline;
        }
        input[type="text"] {
            padding: 6px;
            width: 60%;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            padding: 6px 12px;
            border: none;
            background: #1976d2;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        button.delete {
            background: #d32f2f;
        }
        button.edit {
            background: #f9a825;
        }
        form.add {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<h1>🔗 Redirect Manager Panel</h1>

<?php foreach (['apple', 'other'] as $type): ?>
    <div class="section">
        <h2><?= ucfirst($type) ?> Links</h2>
        <ul>
            <?php foreach ($config[$type] as $i => $url): ?>
                <li>
                    <span><?= htmlspecialchars($url) ?></span>
                    <div class="actions">
                        <!-- Edit Form -->
                        <form class="inline" method="POST">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="type" value="<?= $type ?>">
                            <input type="hidden" name="index" value="<?= $i ?>">
                            <input type="text" name="url" value="<?= htmlspecialchars($url) ?>">
                            <button class="edit">Save</button>
                        </form>

                        <!-- Delete Form -->
                        <form class="inline" method="POST" onsubmit="return confirm('Delete this link?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="type" value="<?= $type ?>">
                            <input type="hidden" name="index" value="<?= $i ?>">
                            <button class="delete">Delete</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Add Form -->
        <form class="add" method="POST">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="type" value="<?= $type ?>">
            <input type="text" name="url" placeholder="https://example.com" required>
            <button>Add</button>
        </form>
    </div>
<?php endforeach; ?>

</body>
</html>
