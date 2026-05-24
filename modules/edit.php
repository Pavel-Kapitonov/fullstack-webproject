<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../scripts/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === АВТОРИЗАЦИЯ ===
$auth_success = false;

if (empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_header('/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
        list($user, $pw) = explode(':', base64_decode($matches[1]), 2);
        $_SERVER['PHP_AUTH_USER'] = $user;
        $_SERVER['PHP_AUTH_PW'] = $pw;
    }
}

if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    try {
        $stmt = $db->prepare("SELECT password_hash FROM admins WHERE login = ?");
        $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
            $auth_success = true;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        exit('Ошибка авторизации. Проверьте подключение к БД.');
    }
}

if (!$auth_success) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

// === ПОЛУЧЕНИЕ ID ===
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID пользователя не указан");
}

// === ОБРАБОТКА ФОРМЫ РЕДАКТИРОВАНИЯ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        exit('Ошибка безопасности: CSRF-токен невалиден');
    }
    
    try {
        $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, email = ?, message = ?, login = ? WHERE id = ?");
        $stmt->execute([
            $_POST['full_name'], 
            $_POST['phone'], 
            $_POST['email'], 
            $_POST['message'], 
            $_POST['login'], 
            $id
        ]);

        header('Location: /fullstack-webproject/modules/admin.php?success=edited');
        exit;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        exit('Ошибка при сохранении данных. Попробуйте позже.');
    }
}

// === ЗАГРУЗКА ТЕКУЩИХ ДАННЫХ ===
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Пользователь не найден.");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование заявки #<?= (int)$id ?></title>
    <style>
        .form-edit { max-width: 500px; margin: 40px auto; font-family: sans-serif; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #f9f9f9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="tel"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        textarea { resize: vertical; min-height: 100px; }
        .btn-submit { background: #337ab7; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .btn-submit:hover { background: #286090; }
        .cancel-link { margin-left: 15px; color: #d9534f; text-decoration: none; }
        .cancel-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="form-edit">
        <h2>Редактирование заявки #<?= (int)$id ?></h2>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
            <div class="form-group">
                <label>ФИО:</label>
                <input name="full_name" type="text" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Телефон:</label>
                <input name="phone" type="tel" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input name="email" type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Авто-Логин:</label>
                <input name="login" type="text" value="<?= htmlspecialchars($user['login'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Сообщение/Био:</label>
                <textarea name="message"><?= htmlspecialchars($user['message'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn-submit">Сохранить изменения</button>
            <a href="/fullstack-webproject/modules/admin.php" class="cancel-link">Отмена</a>
        </form>
    </div>
</body>
</html>
