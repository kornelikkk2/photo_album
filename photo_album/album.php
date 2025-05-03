<?php
session_start(); // Начало сессии
if (!isset($_SESSION['user_id'])) { // Проверка, авторизован ли пользователь
    header("Location: auth.php"); // Перенаправление на страницу авторизации, если пользователь не авторизован
    exit(); // Прекращение выполнения скрипта
}

require 'config.php'; // Подключение файла конфигурации для подключения к базе данных

$user_id = $_SESSION['user_id']; // Получение ID пользователя из сессии

// Обработка загрузки фотографии
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["photo"])) { // Проверка, что запрос POST и файл был отправлен
    $caption = $mysqli->real_escape_string($_POST['caption']); // Экранирование строки для безопасного использования в SQL-запросе
    $target_dir = "uploads/"; // Директория для загрузки фотографий
    $target_file = $target_dir . basename($_FILES["photo"]["name"]); // Полный путь к файлу
    $uploadOk = 1; // Флаг успешности загрузки
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // Получение расширения файла

    // Проверка на изображение
    $check = getimagesize($_FILES["photo"]["tmp_name"]); // Проверка, является ли файл изображением
    if ($check !== false) {
        $uploadOk = 1; // Файл является изображением
    } else {
        echo "File is not an image."; // Сообщение об ошибке
        $uploadOk = 0; // Файл не является изображением
    }

    // Проверка на существование файла
    if (file_exists($target_file)) {
        echo "Sorry, file already exists."; // Сообщение об ошибке
        $uploadOk = 0; // Файл уже существует
    }

    // Проверка размера файла
    if ($_FILES["photo"]["size"] > 5000000) { // Максимальный размер файла 5MB
        echo "Sorry, your file is too large."; // Сообщение об ошибке
        $uploadOk = 0; // Файл слишком большой
    }

    // Разрешенные форматы файлов
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed."; // Сообщение об ошибке
        $uploadOk = 0; // Неразрешенный формат файла
    }

    // Проверка на ошибки загрузки
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded."; // Сообщение об ошибке
    } else {
        // Перемещение файла и сохранение информации в БД
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $stmt = $mysqli->prepare("INSERT INTO photos (user_id, path, caption) VALUES (?, ?, ?)"); // Подготовка SQL-запроса
            $stmt->bind_param("iss", $user_id, $target_file, $caption); // Связывание параметров запроса
            $stmt->execute(); // Выполнение запроса
            $stmt->close(); // Закрытие запроса
            header("Location: album.php"); // Перенаправление на страницу альбома
            exit(); // Прекращение выполнения скрипта
        } else {
            echo "Sorry, there was an error uploading your file."; // Сообщение об ошибке
        }
    }
}

// Обработка удаления фотографии
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_photo'])) {
    $photo_id = $_POST['photo_id']; // Получение ID фотографии из запроса
    $photo_path = $_POST['photo_path']; // Получение пути к файлу из запроса

    if (file_exists($photo_path)) { // Проверка, существует ли файл
        unlink($photo_path); // Удаление файла
    }

    $stmt = $mysqli->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?"); // Подготовка SQL-запроса для удаления записи из БД
    $stmt->bind_param("ii", $photo_id, $user_id); // Связывание параметров запроса
    $stmt->execute(); // Выполнение запроса
    $stmt->close(); // Закрытие запроса
    header("Location: album.php"); // Перенаправление на страницу альбома
    exit(); // Прекращение выполнения скрипта
}

// Обработка редактирования подписи
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_caption'])) {
    $photo_id = $_POST['photo_id']; // Получение ID фотографии из запроса
    $new_caption = $mysqli->real_escape_string($_POST['new_caption']); // Экранирование новой подписи для безопасного использования в SQL-запросе

    $stmt = $mysqli->prepare("UPDATE photos SET caption = ? WHERE id = ? AND user_id = ?"); // Подготовка SQL-запроса для обновления подписи
    $stmt->bind_param("sii", $new_caption, $photo_id, $user_id); // Связывание параметров запроса
    $stmt->execute(); // Выполнение запроса
    $stmt->close(); // Закрытие запроса
    header("Location: album.php"); // Перенаправление на страницу альбома
    exit(); // Прекращение выполнения скрипта
}

// Получение фотографий пользователя
$photos = $mysqli->query("SELECT * FROM photos WHERE user_id = $user_id"); // Выполнение SQL-запроса для получения всех фотографий пользователя
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"> <!-- Подключение CSS-файла -->
    <title>My Album</title> <!-- Заголовок страницы -->
</head>

<body>
    <div class="container">
        <h2>My Album</h2>
        <a href="view_all.php">View All Photos</a> | <a href="logout.php">Logout</a> <!-- Ссылки для просмотра всех фотографий и выхода из системы -->

        <!-- Форма для загрузки фотографии -->
        <form action="album.php" method="post" enctype="multipart/form-data">
            <input type="file" name="photo" required> <!-- Поле для выбора файла -->
            <input type="text" name="caption" placeholder="Caption" required> <!-- Поле для ввода подписи -->
            <button type="submit">Upload Photo</button> <!-- Кнопка для загрузки фотографии -->
        </form>

        <div class="album">
            <?php while ($photo = $photos->fetch_assoc()) : ?> <!-- Цикл для отображения всех фотографий пользователя -->
                <div class="photo">
                    <img src="<?= htmlspecialchars($photo['path']) ?>" alt="Photo"> <!-- Отображение фотографии -->
                    <p><?= htmlspecialchars($photo['caption']) ?></p> <!-- Отображение подписи к фотографии -->
                    <!-- Форма для удаления фотографии -->
                    <form action="album.php" method="post">
                        <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>"> <!-- Скрытое поле с ID фотографии -->
                        <input type="hidden" name="photo_path" value="<?= htmlspecialchars($photo['path']) ?>"> <!-- Скрытое поле с путем к файлу -->
                        <button type="submit" name="delete_photo">Delete</button> <!-- Кнопка для удаления фотографии -->
                    </form>
                    <!-- Форма для редактирования подписи -->
                    <form action="album.php" method="post">
                        <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>"> <!-- Скрытое поле с ID фотографии -->
                        <input type="text" name="new_caption" placeholder="New Caption" required> <!-- Поле для ввода новой подписи -->
                        <button type="submit" name="edit_caption">Edit Caption</button> <!-- Кнопка для редактирования подписи -->
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>

</html>