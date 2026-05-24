<?php
require_once(__DIR__ . '/../scripts/validator.php');

function front_get($request) {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $errors = [];
    if (!empty($_COOKIE['errors'])) {
        $errors = json_decode($_COOKIE['errors'], true);
        setcookie('errors', '', time() - 3600, '/');
    }

    $values = [];
    if (!empty($_COOKIE['values'])) {
        $values = json_decode($_COOKIE['values'], true);
        setcookie('values', '', time() - 3600, '/');
    } elseif (!empty($_SESSION['login']) && !empty($_SESSION['user_id'])) {
        require_once(__DIR__ . '/../scripts/db.php');
        try {
            $stmt = $db->prepare("SELECT full_name, phone, email, message FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $dbUser = $stmt->fetch();
            
            if ($dbUser) {
                $values['fullName'] = $dbUser['full_name'];
                $values['phone'] = $dbUser['phone'];
                $values['email'] = $dbUser['email'];
                $values['message'] = $dbUser['message'];
            }
        } catch (PDOException $e) {
            error_log("Ошибка автозаполнения: " . $e->getMessage());
        }
    }

    $success = $_COOKIE['success'] ?? false;
    if ($success) {
        setcookie('success', '', time() - 3600, '/');
    }

    $template_data = array(
        'errors'    => $errors,
        'values'    => $values,
        'success'   => $success,
        'gen_login' => $_COOKIE['login'] ?? '',
        'gen_pass'  => $_COOKIE['pass'] ?? ''
    );

    if ($template_data['gen_login']) setcookie('login', '', time() - 3600, '/');
    if ($template_data['gen_pass']) setcookie('pass', '', time() - 3600, '/');

    return array(
        'headers' => array('Content-Type' => 'text/html; charset=utf-8'),
        'entity'  => theme('page', $template_data)
    );
}

function front_post($request) {
    return process_form_submission($request, 'register');
}

function front_put($request) {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        die(json_encode(['status' => 'error', 'errors' => ['auth' => 'Требуется авторизация']]));
    }
    return process_form_submission($request, 'update', $request['user_id']);
}

function process_form_submission($request, $mode, $targetUserId = null) {
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $data = !empty($request['put']) ? $request['put'] : $request['post'];

    if (!isset($data['csrf_token']) || $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die(json_encode(['status' => 'error', 'errors' => ['csrf' => 'Ошибка безопасности']]));
    }

    $errors = validate_form_array($data);
    if (!empty($errors)) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            die(json_encode(['status' => 'error', 'errors' => $errors]));
        }

        setcookie('errors', json_encode($errors), 0, '/');
        return redirect('/fullstack-webproject/');
    }

    require(__DIR__ . '/../scripts/db.php');
    
    $generatedLogin = '';
    $generatedPass = '';

    try {

        if ($mode === 'register') {
            $generatedLogin = 'u' . substr(bin2hex(random_bytes(4)), 0, 6);
            $generatedPass = substr(bin2hex(random_bytes(4)), 0, 8);
            $hash = password_hash($generatedPass, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users 
                (full_name, phone, email, message, consent, login, password_hash) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
                
            $stmt->execute([
                $data['fullName'], 
                $data['phone'], 
                $data['email'], 
                $data['message'],
                1, // consent по умолчанию 
                $generatedLogin, 
                $hash
            ]);
            
            $_SESSION['user_id'] = $db->lastInsertId();
            $_SESSION['login'] = $generatedLogin;
        } elseif ($mode === 'update' && $targetUserId) {
	    $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, email = ?, message = ? WHERE id = ?");
            $stmt->execute([
                $data['fullName'], 
                $data['phone'], 
                $data['email'], 
                $data['message'], 
                $targetUserId
            ]);
        }
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        if ($is_ajax) {
            die(json_encode(['status' => 'error', 'errors' => ['db' => $e->getMessage()]]));
        }

        return redirect('/fullstack-webproject/');
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'mode' => $mode,
            'login' => $generatedLogin,
            'password' => $generatedPass
        ]);
        exit; 
    }
    
    setcookie('success', '1', 0, '/');
    return redirect('/fullstack-webproject/');
}
