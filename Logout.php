<?php
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		session_start();
		require("Config.php");
		if (!isset($_POST['token'])) {
			$code = '1002';
			$message = 'Parameter is not enought.';
		} else {
			$conn = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASENAME);
			if ($conn == null) {
				$code = '1001';
				$message = 'Can not connect to DB.';
			} else {
				require("utils.php");
				$token = $_POST['token'];
				$user_id =  getUserByToken($token);
				$sql = "SELECT token FROM user WHERE token = '$token'";
				$result = mysqli_query($conn, $sql);
				if (mysqli_num_rows($result) == 0) {
					$code = '9998';
					$message = 'Token is invalid.';
				} else {
					$sql_update = "UPDATE user SET token = '' WHERE id = '$user_id'";
					mysqli_query($conn, $sql_update);

					session_destroy();
					$code = "1000";
					$message = "OK";
				}
			}
		}
	} else {
		$code = '9997';
		$message = 'Method is invalid';
	}

	$array = array(
		'code' => $code,
		'message' => $message);
	$ouput = json_encode($array);
	echo $ouput;
?>