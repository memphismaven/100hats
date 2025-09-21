<?php
$host = $_SERVER['HTTP_HOST'] ?? 'unknown';

$db_host = '';
$db_user = '';
$db_pass = '';
$db_name = 'danad2do';

if ($host === 'localhost') {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
} else {
    $db_host = '100hatscom.ipowermysql.com';
    $db_user = 'rpt';
    $db_pass = '100H@tsStr0ngPwd!';
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>