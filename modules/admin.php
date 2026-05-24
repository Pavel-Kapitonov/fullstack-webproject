<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../scripts/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === АВТОРИЗАЦИЯ ЧЕРЕЗ БД ===
$auth_success = false;

if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    try {
        // Ищем в новой таблице admins хэш пароля по введенному логину
        $stmt = $db->prepare("SELECT password_hash FROM admins WHERE login = ?");
        $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Если логин найден и пароль совпадает с хэшем
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
    print('<h1>401 Требуется авторизация</h1><p>Доступ только для администратора.</p>');
    exit();
}


// === УДАЛЕНИЕ ПОЛЬЗОВАТЕЛЯ ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('HTTP/1.1 403 Forbidden');
        exit('Ошибка безопасности: CSRF-токен невалиден');
    }

    $del_id = (int)$_POST['delete_id'];
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$del_id]);
        
        header('Location: /fullstack-webproject/modules/admin.php?success=deleted'); 
        exit();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        exit('Произошла системная ошибка при удалении.');
    }
}

// === ПОЛУЧЕНИЕ ДАННЫХ ===
try {
    $users = $db->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Ошибка загрузки данных.');
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .btn-link { background: none; border: none; color: #d9534f; text-decoration: underline; cursor: pointer; padding: 0; font-size: inherit; }
        .btn-link:hover { color: #c9302c; }
        .edit-link { color: #337ab7; }
        .success-msg { color: #2ecc71; font-weight: bold; padding: 10px; border: 1px solid #2ecc71; background: rgba(46, 204, 113, 0.1); border-radius: 5px; width: max-content; }
    </style>
</head>
<body>
    <h1>Панель администратора</h1>
    <p>Вы вошли как: <strong><?= htmlspecialchars($_SERVER['PHP_AUTH_USER']) ?></strong></p>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
        <div class="success-msg">Заявка успешно удалена!</div>
        <br>
    <?php endif; ?>

    <h3>Список заявок:</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>ФИО</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Сообщение</th>
            <th>Авто-Логин</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= (int)($u['id'] ?? 0) ?></td>
            <td><?= htmlspecialchars($u['fullName'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['message'] ?? '') ?></td>
            <td><?= htmlspecialchars($u['login'] ?? '') ?></td>
            <td>
                <a href="/fullstack-webproject/modules/edit.php?id=<?= $u['id'] ?? 0 ?>" class="edit-link">Редактировать</a> | 
                
                <form action="/fullstack-webproject/modules/admin.php" method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="delete_id" value="<?= $u['id'] ?? 0 ?>">
                    <button type="submit" class="btn-link" onclick="return confirm('Вы уверены, что хотите удалить эту заявку?')">
                        Удалить
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="/fullstack-webproject/">Вернуться на сайт</a></p>
</body>
</html>
