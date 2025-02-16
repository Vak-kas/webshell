<?php
$servername = "localhost";
$username = "root";
$password = "11111111";
$dbname = "webshell";

$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

?>