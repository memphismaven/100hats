<?php
$conn = new mysqli('100hatscom.ipowermysql.com', 'rpt', '100H@tsStr0ngPwd!', 'danad2do');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✅ Connected successfully!";
$conn->close();
?>
