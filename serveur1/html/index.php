<html>
	<title>Tp3 page 1</title>
	<h1>Mon site nginx numero 1 :D !!</h1>

	<?php 
	$host = 'mariadb';
	$user = 'root';
	$pass = 'rootpassword';
	$conn = new mysqli($host, $user, $pass);

	if ($conn->connect_error) {
		die("La connexion a échoué: " . $conn->connect_error);
	} 
	echo "Connexion réussie à MariaDB!";
	?>


</html>
