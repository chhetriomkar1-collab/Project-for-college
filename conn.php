<?php
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "project",
    3307
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>