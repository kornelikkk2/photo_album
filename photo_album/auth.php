<?php
session_start(); // Начало сессии PHP
require 'config.php'; // Подключение файла конфигурации для доступа к базе данных

$error = ''; // Переменная для хранения сообщений об ошибках
$success = ''; // Переменная для хранения сообщений об успешных операциях

// Обработка формы при отправке
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action']; // Получение действия (login или register) из формы
    $username = $mysqli->real_escape_string($_POST['username']); // Экранирование имени пользователя для безопасного использования в SQL-запросе
    $password = $_POST['password']; // Получение пароля из формы

    if ($action == 'register') {
        // Хэширование пароля
        $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Хэширование пароля с использованием алгоритма BCRYPT

        // Проверка на существование пользователя
        $result = $mysqli->query("SELECT id FROM users WHERE username='$username'"); // SQL-запрос для проверки существования пользователя

        if ($result->num_rows > 0) {
            $error = "Username already exists!"; // Установка сообщения об ошибке, если пользователь с таким именем уже существует
        } else {
            // Вставка нового пользователя
            $mysqli->query("INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')"); // SQL-запрос для вставки нового пользователя
            $success = "Registration successful!"; // Установка сообщения об успешной регистрации
        }
    } elseif ($action == 'login') {
        // Проверка учетных данных
        $result = $mysqli->query("SELECT * FROM users WHERE username='$username'"); // SQL-запрос для получения данных пользователя
        $user = $result->fetch_assoc(); // Получение данных пользователя из результата запроса

        if ($user && password_verify($password, $user['password'])) { // Проверка пароля пользователя
            $_SESSION['user_id'] = $user['id']; // Сохранение ID пользователя в сессии
            header("Location: album.php"); // Перенаправление на страницу альбома
            exit(); // Прекращение выполнения скрипта
        } else {
            $error = "Invalid username or password"; // Установка сообщения об ошибке, если имя пользователя или пароль неверны
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> <!-- Подключение CSS-файла -->
    <title>Auth</title> <!-- Заголовок страницы -->
</head>

<body>
    <div class="container">
        <h2>Authentication</h2>
        <?php if ($error) : ?> <!-- Проверка наличия сообщения об ошибке -->
            <div class="error"><?= $error ?></div> <!-- Отображение сообщения об ошибке -->
        <?php endif; ?>
        <?php if ($success) : ?> <!-- Проверка наличия сообщения об успешной операции -->
            <div class="success"><?= $success ?></div> <!-- Отображение сообщения об успешной операции -->
        <?php endif; ?>
        <!-- Форма авторизации и регистрации -->
        <form method="post">
            <input type="text" name="username" placeholder="Username" required> <!-- Поле для ввода имени пользователя -->
            <input type="password" name="password" placeholder="Password" required> <!-- Поле для ввода пароля -->
            <button type="submit" name="action" value="login">Login</button> <!-- Кнопка для входа -->
            <button type="submit" name="action" value="register">Register</button> <!-- Кнопка для регистрации -->
        </form>
    </div>
</body>

</html>