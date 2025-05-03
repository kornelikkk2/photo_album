<?php
$mysqli = new mysqli("localhost", "root", "", "photo_album");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
