<?php
	define('HOSTNAME','localhost');
	define('USERNAME','root');
	define('PASSWORD','');
	define('DATABASENAME','moki');
	//define('domain', 'https://www.000webhost.com');
	define('DOMAIN','localhost/Myserver');
	$conn= mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASENAME);
	mysqli_query($conn, "SET NAMES 'utf8'");
?>