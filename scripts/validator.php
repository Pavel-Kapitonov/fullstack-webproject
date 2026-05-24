<?php
function validate_form_array($data) {
    $errors = array();

    $name = $data['fullName'] ?? '';        
    $tel = $data['phone'] ?? '';      
    $email = $data['email'] ?? '';
    $message = $data['message'] ?? '';
    $consent = isset($data['consent']) && ($data['consent'] === true || $data['consent'] === 'on' || $data['consent'] === '1');

    if (empty($name) || !preg_match('/^[a-zA-Zа-яёА-ЯЁ\s\-]+$/u', $name)) {
        $errors['fullName'] = "Можно только буквы, пробелы и дефис";
    }

    if (empty($tel) || !preg_match('/^\+?[0-9]{11}$/', $tel)) {
        $errors['phone'] = "Введите 11 цифр вашего номера (РФ)";
    }

    if (empty($email) || !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errors['email'] = "Некорректный email";
    }

    if (empty($message) || !preg_match('/^[a-zA-Zа-яёА-ЯЁ0-9\s\.,\-\!\?]+$/u', $message)) {
        $errors['message'] = "В комментарии разрешены буквы, цифры, пробелы и знаки .,-!?";
    }

    if (!$consent) {
        $errors['consent'] = "Необходимо согласиться с соглашением";
    }

    return $errors;
}
