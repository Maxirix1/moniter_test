<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
$serverName = "192.168.0.100";
$database = "smart_queue";
$username = "smartqueue";
$password = "Admin1234";

try {
    $conn = new PDO("sqlsrv:server=$serverName,1433;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Success";
    // echo"404 ERROR | Connect SUCCESS";
} catch (PDOException $e) {
    // echo "Connection failed: " . $e->getMessage();
    header('Location: /PHP/server/error');
}
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(E_ALL);
// $serverName = "49.0.65.19";
// $database = "smart_queue";
// $username = "test";
// $password = "admin1234";

// try {
//     $conn = new PDO("sqlsrv:server=$serverName,1433;Database=$database", $username, $password);
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//     // echo "Success";
//     // echo"404 ERROR | Connect SUCCESS";
// } catch (PDOException $e) {
//     // echo "Connection failed: " . $e->getMessage();
//     header('Location: /PHP/server/error');
// }
?>