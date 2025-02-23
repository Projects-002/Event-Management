<?php
$servername = "localhost";
$username = "root";
$password = "22092209";
$database = "event_calendar";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
