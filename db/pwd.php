<?php
$password1 = password_hash('Savvytonny@apeli9', PASSWORD_BCRYPT);
$password2 = password_hash('Savvytonny@apeli9', PASSWORD_BCRYPT);
$password3 = password_hash('Savvytonny@apeli9', PASSWORD_BCRYPT);

echo "Admin1: $password1\n";
echo "DeptHead1: $password2\n";
echo "User1: $password3\n";
?>
