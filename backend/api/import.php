<?php
require_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();

$sql = file_get_contents(__DIR__ . '/../sql/database.sql');

$sql = preg_replace('/CREATE DATABASE.*?;/s', '', $sql);
$sql = preg_replace('/USE.*?;/s', '', $sql);

try {
    $db->exec($sql);
    echo json_encode(["message" => "Baza uspesno importovana!"]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}