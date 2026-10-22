<?php
$host = "localhost";
$dbname = "transportadora";
$user = "root";
$pass = "";

try {
    $con = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => $e->getMessage()]);
    exit;
}