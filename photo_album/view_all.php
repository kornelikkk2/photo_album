<?php
session_start(); // Начало сессии PHP

// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php"); // Перенаправление на страницу авторизации, если пользователь не авторизован
    exit(); // Прекращение выполнения скрипта
}

require 'config.php'; // Подключение файла конфигурации для доступа к базе данных

// Получение всех фотографий из базы данных
$photos = $mysqli->query("SELECT photos.*, users.username FROM photos JOIN users ON photos.user_id = users.id"); // Запрос к базе данных на получение всех фотографий с именами пользователей
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8"> <!-- Установка кодировки символов -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Обеспечение адаптивности страницы на разных устройствах -->
    <link rel="stylesheet" href="style.css"> <!-- Подключение CSS-файла -->
    <title>All Photos</title> <!-- Заголовок страницы -->
</head>

<body>
    <div class="container">
        <h2>All Photos</h2> <!-- Заголовок страницы -->
        <a href="album.php">My Album</a> | <a href="logout.php">Logout</a> <!-- Ссылки на альбом пользователя и выход из аккаунта -->
        <div class="album">
            <?php while ($photo = $photos->fetch_assoc()) : ?> <!-- Цикл для вывода всех фотографий из базы данных -->
                <div class="photo">
                    <img src="<?= htmlspecialchars($photo['path']) ?>" alt="Photo"> <!-- Отображение изображения -->
                    <p><?= htmlspecialchars($photo['caption']) ?></p> <!-- Отображение подписи к фотографии -->
                    <p>Uploaded by <?= htmlspecialchars($photo['username']) ?></p> <!-- Отображение имени пользователя, загрузившего фотографию -->
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>

</html>