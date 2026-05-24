<?php
header('Content-Type: text/html; charset=UTF-8');

session_start();

if (!empty($_SESSION['login'])) {
    header('Location: /fullstack-webproject/');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = !empty($_COOKIE['login_error']) ? $_COOKIE['login_error'] : '';
if ($errors) {
    setcookie('login_error', '', time() - 3600, '/'); 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в личный кабинет</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        .myform {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            background: #1a1a1a;
            border-radius: 8px;
            color: #fff;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            font-family: sans-serif;
        }
        .myform h2 {
            margin-bottom: 25px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #bbb;
            font-size: 0.9em;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #2b2b2b;
            border: 1px solid #444;
            border-radius: 4px;
            color: #fff;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            border-color: #fff;
            outline: none;
        }
        .knopka {
            width: 100%;
            padding: 12px;
            background: #fff;
            color: #000;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .knopka:hover {
            background: #ccc;
        }
        .error-box {
            color: #ff4d4d;
            border: 1px solid #ff4d4d;
            background: rgba(255, 77, 77, 0.1);
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: left;
            font-size: 0.9em;
        }
        .myform a {
            color: #fff;
            text-decoration: underline;
        }
        .back-link {
            margin-top: 20px;
            display: block;
            font-size: 0.9em;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="myform">
        <h2>Вход в кабинет</h2>
        
        <?php if ($errors): ?>
            <div class="error-box">
                <?= htmlspecialchars($errors) ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="login">Логин:</label>
                <input id="login" name="login" type="text" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="pass">Пароль:</label>
                <input id="pass" name="pass" type="password" required autocomplete="current-password">
            </div>
            
            <button class="knopka" type="submit">Войти</button>
        </form>
	<a href="/fullstack-webproject/" class="back-link">Назад</a>
    </div>
</body>
</html>
<?php
} else {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        exit('Ошибка безопасности: токен невалиден');
    }

    $login = trim($_POST['login'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if (empty($login) || empty($pass)) {
        setcookie('login_error', 'Заполните все поля', 0, '/');
        header('Location: login.php');
        exit;
    }

    require_once __DIR__ . '/../scripts/db.php';

    try {
        $stmt = $db->prepare("SELECT id, login, password_hash FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            $_SESSION['login'] = $user['login'];
            $_SESSION['user_id'] = $user['id']; 
            
            session_regenerate_id(true); 

            header('Location: /fullstack-webproject/');
            exit;
        } else {
            setcookie('login_error', 'Неверный логин или пароль', 0, '/');
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Ошибка авторизации: " . $e->getMessage());
        exit('Ошибка сервера, попробуйте позже.');
    }
}
