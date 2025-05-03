-- Создание базы данных
CREATE DATABASE IF NOT EXISTS photo_album;

-- Использование базы данных
USE photo_album;

-- Создание таблицы пользователей
CREATE TABLE IF NOT EXISTS users
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Создание таблицы фотографий
CREATE TABLE IF NOT EXISTS photos
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    path VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    FOREIGN KEY(user_id) REFERENCES users(id)
);