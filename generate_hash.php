<?php
$password_to_hash = 'Admin123'; // <--- REPLACE THIS with the actual password you want to use
$hashed_password = password_hash($password_to_hash, PASSWORD_BCRYPT);
echo "Original Password: " . $password_to_hash . "<br>";
echo "Hashed Password: " . $hashed_password . "<br>";
?>