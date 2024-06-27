<?php
include 'config.php';

// Reinsertar los usuarios con password_hash
$password_admin = password_hash('admin123', PASSWORD_DEFAULT);
$password_operator = password_hash('operator123', PASSWORD_DEFAULT);

$conn->query("DELETE FROM Users");

$query = $conn->prepare("INSERT INTO Users (username, password, role) VALUES (?, ?, ?)");
$query->bind_param("sss", $username, $password, $role);

$username = 'admin';
$password = $password_admin;
$role = 'admin';
$query->execute();

$username = 'operator';
$password = $password_operator;
$role = 'operator';
$query->execute();

echo "Usuarios reinsertados con contraseñas encriptadas correctamente.";
?>
